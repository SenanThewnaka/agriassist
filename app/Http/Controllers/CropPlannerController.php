<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\CropVariety;
use App\Services\AnalysisService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CropPlannerController extends Controller
{
    protected AnalysisService $analysisService;

    public function __construct(AnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    // Sri Lanka district → dominant soil type lookup
    // Sources: DOA Sri Lanka Soil Survey, NSDI Soil Map
    private array $districtSoilMap = [
        'Colombo' => ['type' => 'alluvial', 'label' => 'Alluvial'],
        'Gampaha' => ['type' => 'sandy_loam', 'label' => 'Sandy Loam'],
        'Kalutara' => ['type' => 'red_yellow_podzolic', 'label' => 'Red Yellow Podzolic'],
        'Kandy' => ['type' => 'red_yellow_podzolic', 'label' => 'Red Yellow Podzolic'],
        'Matale' => ['type' => 'red_yellow_podzolic', 'label' => 'Red Yellow Podzolic'],
        'Nuwara Eliya' => ['type' => 'red_yellow_podzolic', 'label' => 'Red Yellow Podzolic'],
        'Galle' => ['type' => 'red_yellow_podzolic', 'label' => 'Red Yellow Podzolic'],
        'Matara' => ['type' => 'red_yellow_podzolic', 'label' => 'Red Yellow Podzolic'],
        'Hambantota' => ['type' => 'reddish_brown_earth', 'label' => 'Reddish Brown Earth'],
        'Jaffna' => ['type' => 'sandy', 'label' => 'Sandy / Calcic'],
        'Kilinochchi' => ['type' => 'sandy_loam', 'label' => 'Sandy Loam'],
        'Mannar' => ['type' => 'sandy', 'label' => 'Sandy'],
        'Vavuniya' => ['type' => 'sandy_loam', 'label' => 'Sandy Loam'],
        'Mullaitivu' => ['type' => 'lateritic', 'label' => 'Lateritic'],
        'Batticaloa' => ['type' => 'alluvial', 'label' => 'Alluvial'],
        'Ampara' => ['type' => 'alluvial', 'label' => 'Alluvial'],
        'Trincomalee' => ['type' => 'alluvial', 'label' => 'Alluvial'],
        'Kurunegala' => ['type' => 'lateritic', 'label' => 'Lateritic'],
        'Puttalam' => ['type' => 'sandy_loam', 'label' => 'Sandy Loam'],
        'Anuradhapura' => ['type' => 'reddish_brown_earth', 'label' => 'Reddish Brown Earth'],
        'Polonnaruwa' => ['type' => 'reddish_brown_earth', 'label' => 'Reddish Brown Earth'],
        'Badulla' => ['type' => 'red_yellow_podzolic', 'label' => 'Red Yellow Podzolic'],
        'Monaragala' => ['type' => 'reddish_brown_earth', 'label' => 'Reddish Brown Earth'],
        'Ratnapura' => ['type' => 'red_yellow_podzolic', 'label' => 'Red Yellow Podzolic'],
        'Kegalle' => ['type' => 'red_yellow_podzolic', 'label' => 'Red Yellow Podzolic'],
    ];

    // Soil type key → common crop variety soil name mappings
    private array $soilNameMap = [
        'alluvial' => 'Alluvial',
        'sandy_loam' => 'Sandy Loam',
        'sandy' => 'Sandy',
        'lateritic' => 'Lateritic',
        'red_yellow_podzolic' => 'Red Yellow Podzolic',
        'reddish_brown_earth' => 'Lateritic', // similar characteristics
        'clay_loam' => 'Clay Loam',
        'clay' => 'Clay',
    ];

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Views
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public function index()
    {
        $crops = Crop::all();
        return view('crops.planner', compact('crops'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'crop_variety_id' => 'required|exists:crop_varieties,id',
            'planting_date' => 'required|date',
        ]);

        $variety = CropVariety::with('crop')->find($request->crop_variety_id);
        $plantingDate = Carbon::parse($request->planting_date);
        $harvestDate = $plantingDate->copy()->addDays($variety->growth_days);
        $stages = $this->calculateStages($variety, $plantingDate);

        return view('crops.planner', [
            'crops' => Crop::all(),
            'result' => [
                'crop' => $variety->crop->name,
                'variety' => $variety->variety_name,
                'growth_days' => $variety->growth_days,
                'planting_date' => $plantingDate->toDateString(),
                'estimated_harvest' => $harvestDate->toDateString(),
                'stages' => $stages
            ]
        ]);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // API Endpoints
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public function apiCalculate(Request $request)
    {
        $request->validate([
            'crop_variety_id' => 'required',
            'planting_date' => 'required|date',
            'custom_crop_name' => 'nullable|string|max:100',
            'custom_variety_name' => 'nullable|string|max:100',
            'manual_crop_id' => 'nullable|exists:crops,id',
        ]);

        $plantingDate = Carbon::parse($request->planting_date);
        $locale = $request->input('lang', $request->input('locale', app()->getLocale()));
        app()->setLocale($locale);

        if ($request->crop_variety_id === 'other') {
            $cropName = $request->custom_crop_name;
            $cropSi = null;
            $cropTa = null;

            // If it's a known crop but custom variety
            if ($request->has('manual_crop_id') && $request->manual_crop_id) {
                $crop = Crop::find($request->manual_crop_id);
                if ($crop) {
                    $cropName = $crop->name;
                    $cropSi = $crop->name_si;
                    $cropTa = $crop->name_ta;
                }
            }

            $aiData = $this->analysisService->generateCropPlan(
                $cropName ?? 'Dragon Fruit',
                $locale,
                $request->custom_variety_name
            );

            if (isset($aiData['error'])) {
                return response()->json(['error' => $aiData['message']], 503);
            }

            $growthDays = $aiData['growth_days'] ?? 90;
            $harvestDate = $plantingDate->copy()->addDays($growthDays);

            return response()->json([
                'crop' => $cropName ?? $request->custom_crop_name,
                'crop_si' => $cropSi,
                'crop_ta' => $cropTa,
                'variety' => $request->custom_variety_name ?? 'AI Optimized Variety',
                'growth_days' => $growthDays,
                'planting_date' => $plantingDate->toDateString(),
                'formatted_planting_date' => $plantingDate->format('j F, Y'),
                'estimated_harvest' => $harvestDate->toDateString(),
                'formatted_harvest_date' => $harvestDate->format('j F, Y'),
                'stages' => $aiData['stages'],
                'is_ai' => true
            ]);
        }

        $variety = CropVariety::with(['crop', 'stages'])->find($request->crop_variety_id);
        if (!$variety) {
            return response()->json(['error' => 'Crop variety not found.'], 404);
        }

        $harvestDate = $plantingDate->copy()->addDays($variety->growth_days);
        $stages = $this->calculateStages($variety, $plantingDate);

        return response()->json([
            'crop' => $variety->crop->name,
            'crop_si' => $variety->crop->name_si,
            'crop_ta' => $variety->crop->name_ta,
            'variety' => $variety->variety_name,
            'variety_si' => $variety->variety_name_si,
            'variety_ta' => $variety->variety_name_ta,
            'growth_days' => $variety->growth_days,
            'planting_date' => $plantingDate->toDateString(),
            'formatted_planting_date' => $plantingDate->format('j F, Y'),
            'estimated_harvest' => $harvestDate->toDateString(),
            'formatted_harvest_date' => $harvestDate->format('j F, Y'),
            'stages' => $stages,
        ]);
    }

    /**
     * Reverse geocode lat/lon → district → soil type
     * Uses Nominatim (free, no key) with a built-in district lookup table.
     */
    public function getSoilType(Request $request)
    {
        $request->validate(['lat' => 'required|numeric', 'lon' => 'required|numeric']);

        $lat = $request->lat;
        $lon = $request->lon;

        // Reverse geocode via Nominatim
        $district = $this->reverseGeocodeDistrict($lat, $lon);

        $soilInfo = $this->districtSoilMap[$district]
            ?? ['type' => 'sandy_loam', 'label' => 'Sandy Loam'];

        return response()->json([
            'district' => $district ?? 'Unknown',
            'soil_type' => $soilInfo['type'],
            'soil_type_label' => __($soilInfo['label']),
            'all_soil_types' => array_map(fn($label) => __($label), array_values(array_unique(array_column($this->districtSoilMap, 'label')))),
        ]);
    }

    /**
     * Look up soil type directly by district name (no GPS needed)
     */
    public function getSoilByDistrict(Request $request)
    {
        $district = $request->query('district', '');
        $districts = array_keys($this->districtSoilMap);

        // Return full list when no district given
        if (!$district) {
            return response()->json(['districts' => $districts]);
        }

        $soilInfo = $this->districtSoilMap[$district]
            ?? ['type' => 'sandy_loam', 'label' => 'Sandy Loam'];

        return response()->json([
            'district' => $district,
            'soil_type' => $soilInfo['type'],
            'soil_type_label' => __($soilInfo['label']),
        ]);
    }

    /**
     * Score and return crop suggestions based on soil + month + weather
     */
    public function getSmartSuggestions(Request $request)
    {
        $request->validate([
            'soil_type' => 'required|string',
            'month' => 'required|integer|min:1|max:12',
            'temperature' => 'nullable|numeric',
            'locale' => 'nullable|string'
        ]);

        $locale = $request->input('locale', app()->getLocale());
        app()->setLocale($locale);

        $soilType = strtolower($request->soil_type);
        $month = (int)$request->month;
        $temperature = $request->temperature ? (float)$request->temperature : 28.0;
        $soilLabel = $this->soilNameMap[$soilType] ?? 'Sandy Loam';

        $isMaha = ($month >= 9 && $month <= 11);
        $isYala = ($month >= 3 && $month <= 5);

        // Fetch all varieties with their crop
        $varieties = CropVariety::with('crop')->get();

        $scored = [];
        foreach ($varieties as $variety) {
            // Season relevance (0–30 pts)
            $seasonScore = 0;
            if ($variety->season === 'both') {
                $seasonScore = 25;
            }
            elseif ($variety->season === 'maha' && $isMaha) {
                $seasonScore = 30;
            }
            elseif ($variety->season === 'yala' && $isYala) {
                $seasonScore = 30;
            }
            elseif ($variety->season === 'maha' || $variety->season === 'yala') {
                $seasonScore = 5; // off-season but still possible
            }

            // Soil compatibility (0–35 pts)
            $soilScore = 0;
            $soilTypes = $variety->soil_types;
            if (is_string($soilTypes)) {
                $soilTypes = json_decode($soilTypes, true) ?? [];
            }
            $soilTypes = $soilTypes ?? [];

            if (in_array($soilLabel, $soilTypes)) {
                $soilScore = 35;
            }
            elseif (count($soilTypes) > 0) {
                $soilScore = 10; // can adapt
            }

            // Temperature match (0–25 pts)
            $tempScore = 0;
            if ($variety->min_temp !== null && $variety->max_temp !== null) {
                if ($temperature >= $variety->min_temp && $temperature <= $variety->max_temp) {
                    $tempScore = 25;
                }
                elseif ($temperature >= ($variety->min_temp - 3) && $temperature <= ($variety->max_temp + 3)) {
                    $tempScore = 15; // marginal
                }
            }
            else {
                $tempScore = 15; // unknown = neutral
            }

            // Ideal month bonus (0–10 pts)
            $idealMonths = $variety->crop->ideal_months ?? [];
            if (is_string($idealMonths)) {
                $idealMonths = json_decode($idealMonths, true) ?? [];
            }
            $monthScore = in_array($month, (array)$idealMonths) ? 10 : 0;

            $totalScore = $seasonScore + $soilScore + $tempScore + $monthScore;

            // Avoid duplicate crop names (keep highest scored variety per crop)
            $cropName = $variety->crop->name;
            if (!isset($scored[$cropName]) || $scored[$cropName]['score'] < $totalScore) {
                $scored[$cropName] = [
                    'crop_id' => $variety->crop->id,
                    'variety_id' => $variety->id,
                    'crop_name' => $cropName,
                    'crop_name_si' => $variety->crop->name_si,
                    'crop_name_ta' => $variety->crop->name_ta,
                    'variety_name' => $variety->variety_name,
                    'variety_name_si' => $variety->variety_name_si,
                    'variety_name_ta' => $variety->variety_name_ta,
                    'category' => $variety->crop->category,
                    'growth_days' => $variety->growth_days,
                    'season' => $variety->season,
                    'water_requirement' => $variety->water_requirement,
                    'soil_types' => $soilTypes,
                    'score' => $totalScore,
                    'score_breakdown' => compact('seasonScore', 'soilScore', 'tempScore', 'monthScore'),
                ];
            }
        }

        // Sort by score descending, return top 8
        usort($scored, fn($a, $b) => $b['score'] - $a['score']);
        $top = array_slice(array_values($scored), 0, 8);

        // Normalize scores to 0-100
        $maxRaw = 100; // max possible
        foreach ($top as &$item) {
            $item['suitability'] = min(100, round(($item['score'] / $maxRaw) * 100));
        }

        return response()->json(['suggestions' => $top, 'soil_label' => $soilLabel]);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Private Helpers
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    private function reverseGeocodeDistrict(float $lat, float $lon): ?string
    {
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&zoom=8&email=contact@agriassist.app";

        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: AgriAssist-SriLanka/1.0 (contact@agriassist.app)\r\n",
                'timeout' => 5,
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        if (!$response)
            return null;

        $data = json_decode($response, true);
        $address = $data['address'] ?? [];

        // Nominatim returns various levels — try county/state_district/region
        return $address['county']
            ?? $address['state_district']
            ?? $address['region']
            ?? null;
    }

    private function calculateStages($variety, $plantingDate): array
    {
        $totalDays = $variety->growth_days;

        // Try to get stages from database first
        if ($variety->stages()->count() > 0) {
            return $variety->stages->map(function ($stage) use ($plantingDate) {
                $date = $plantingDate->copy()->addDays($stage->days_offset);
                return [
                    'name' => $stage->name,
                    'name_si' => $stage->name_si,
                    'name_ta' => $stage->name_ta,
                    'date' => $date->toDateString(),
                    'formatted_date' => $date->format('j F, Y'),
                    'icon' => $stage->icon,
                    'advice' => $stage->advice,
                    'advice_si' => $stage->advice_si,
                    'advice_ta' => $stage->advice_ta,
                    'description' => $stage->description,
                    'description_si' => $stage->description_si,
                    'description_ta' => $stage->description_ta,
                    'days_from_start' => $stage->days_offset,
                ];
            })->toArray();
        }

        // Fallback to defaults
        $stagesData = [
            [
                'name' => 'Land Preparation',
                'name_si' => 'කුඹුරු සකස් කිරීම',
                'name_ta' => 'நிலம் தயாரித்தல்',
                'days_offset' => -7,
                'icon' => 'tractor',
                'advice' => 'Clear field boundaries and check irrigation canals. Apply basal fertilizer if required by soil type.',
                'advice_si' => 'කුඹුරු නියරවල් ශුද්ධ පවිත්‍ර කර වාරි මාර්ග පද්ධති පරීක්ෂා කරන්න. පාංශු වර්ගයට අනුව මූලික පොහොර යොදන්න.',
                'advice_ta' => 'வயல் வரப்புகளை சுத்தப்படுத்தி நீர்ப்பாசனக் கால்வாய்களைச் சரிபார்க்கவும். மண் வகைக்குத் தேவையான அடிப்படை உரத்தைப் பயன்படுத்துங்கள்.'
            ],
            [
                'name' => 'Sowing / Seedling',
                'name_si' => 'බීජ වැපිරීම / පැළ සිටුවීම',
                'name_ta' => 'விதைத்தல் / நாற்று நடுதல்',
                'days_offset' => 0,
                'icon' => 'sprout',
                'advice' => 'Optimal time for direct sowing or transplanting nurseries. Ensure standing water is at 2-3cm.',
                'advice_si' => 'ඍජුව බීජ වැපිරීමට හෝ පැළ සිටුවීමට සුදුසුම කාලයයි. ජලය සෙ.මී. 2-3 මට්ටමේ තබා ගන්න.',
                'advice_ta' => 'நேரடி விதைப்பு அல்லது நாற்று நடுவதற்கு உகந்த நேரம். நீர் மட்டம் 2-3 செ.மீ ஆக இருப்பதை உறுதி செய்யவும்.'
            ],
            [
                'name' => 'Vegetative Phase',
                'name_si' => 'වර්ධන අවධිය',
                'name_ta' => 'வளர்ச்சி நிலை',
                'days_offset' => round($totalDays * 0.35),
                'icon' => 'trending-up',
                'advice' => 'High nitrogen requirement. Apply first top dressing (Urea). Control weeds thoroughly.',
                'advice_si' => 'වැඩි නයිට්‍රජන් ප්‍රමාණයක් අවශ්‍ය වේ. පළමු මතුපිට පොහොර (යූරියා) යොදන්න. වල් පැලෑටි හොඳින් පාලනය කරන්න.',
                'advice_ta' => 'அதிக நைட்ரஜன் தேவை. முதல் மேலுரத்தைப் (யூரியா) பயன்படுத்துங்கள். களைகளை முழுமையாகக் கட்டுப்படுத்தவும்.'
            ],
            [
                'name' => 'Flowering / Booting',
                'name_si' => 'මල් පිපීම / කරල් පීදීම',
                'name_ta' => 'பூக்கும் / கருக்கட்டும் நிலை',
                'days_offset' => round($totalDays * 0.70),
                'icon' => 'flower-2',
                'advice' => 'Critical moisture phase. Do not let the field dry. Apply final top dressing of Potassium (MOP).',
                'advice_si' => 'තෙතමනය ඉතා වැදගත් අවධියකි. කුඹුර වියළීමට ඉඩ නොදෙන්න. අවසාන මතුපිට පොහොර (MOP) යොදන්න.',
                'advice_ta' => 'முக்கியமான ஈரப்பதம் தேவைப்படும் நிலை. வயலை காய விடாதீர்கள். பொட்டாசியம் (MOP) மேலுரத்தைப் பயன்படுத்துங்கள்.'
            ],
            [
                'name' => 'Ripening Phase',
                'name_si' => 'පැසෙන අවධිය',
                'name_ta' => 'முதிர்ச்சி நிலை',
                'days_offset' => round($totalDays * 0.90),
                'icon' => 'sun',
                'advice' => 'Gradually reduce water levels. Watch for birds and grain-sucking insects.',
                'advice_si' => 'ක්‍රමයෙන් ජල මට්ටම අඩු කරන්න. කුරුල්ලන්ගෙන් සහ යුෂ උරා බොන කෘමීන්ගෙන් ආරක්ෂා කර ගන්න.',
                'advice_ta' => 'படிப்படியாக நீர் மட்டத்தைக் குறைக்கவும். பறவைகள் மற்றும் சாறு உறிஞ்சும் பூச்சிகளைக் கவனிக்கவும்.'
            ],
            [
                'name' => 'Harvest',
                'name_si' => 'අස්වනු නෙලීම',
                'name_ta' => 'அறுவடை',
                'days_offset' => $totalDays,
                'icon' => 'shopping-basket',
                'advice' => 'Harvest when 85-90% of grains are straw-colored. Dry properly to below 14% moisture.',
                'advice_si' => 'කරල් වලින් 85-90% ක් පමණ පිදුරු පැහැයට හැරුණු පසු අස්වනු නෙලන්න. තෙතමනය 14% ට වඩා අඩුවන සේ වියළා ගන්න.',
                'advice_ta' => '85-90% தானியங்கள் வைக்கோல் நிறமாக மாறும்போது அறுவடை செய்யவும். 14% ஈரப்பதத்திற்குக் குறைவாக நன்கு உலர்த்தவும்.'
            ],
        ];

        return array_map(function ($stage) use ($plantingDate) {
            $date = $plantingDate->copy()->addDays($stage['days_offset']);
            return array_merge($stage, [
                'date' => $date->toDateString(),
                'formatted_date' => $date->format('j F, Y'),
                'days_from_start' => $stage['days_offset'],
            ]);
        }, $stagesData);
    }
}