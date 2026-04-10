<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\CropSeason;
use App\Models\CropTask;
use App\Models\Farm;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CropPlannerController extends Controller
{
    // Sri Lanka district → dominant soil type lookup
    // Sources: DOA Sri Lanka Soil Survey, NSDI Soil Map
    private array $districtSoilMap = [
        'Colombo' => ['type' => 'Alluvial', 'label' => 'Alluvial Soils'],
        'Gampaha' => ['type' => 'Regosols', 'label' => 'Regosols (Sandy)'],
        'Kalutara' => ['type' => 'Red-Yellow Podzolic', 'label' => 'Red-Yellow Podzolic Soils'],
        'Kandy' => ['type' => 'Reddish Brown Latosolic', 'label' => 'Reddish Brown Latosolic Soils'],
        'Matale' => ['type' => 'Reddish Brown Latosolic', 'label' => 'Reddish Brown Latosolic Soils'],
        'Nuwara Eliya' => ['type' => 'Red-Yellow Podzolic', 'label' => 'Red-Yellow Podzolic Soils'],
        'Galle' => ['type' => 'Red-Yellow Podzolic', 'label' => 'Red-Yellow Podzolic Soils'],
        'Matara' => ['type' => 'Red-Yellow Podzolic', 'label' => 'Red-Yellow Podzolic Soils'],
        'Hambantota' => ['type' => 'Reddish Brown Earths', 'label' => 'Reddish Brown Earths'],
        'Jaffna' => ['type' => 'Calcic Latosols', 'label' => 'Calcic Latosols'],
        'Kilinochchi' => ['type' => 'Red-Yellow Latosols', 'label' => 'Red-Yellow Latosols'],
        'Mannar' => ['type' => 'Red-Yellow Latosols', 'label' => 'Red-Yellow Latosols'],
        'Vavuniya' => ['type' => 'Red-Yellow Latosols', 'label' => 'Red-Yellow Latosols'],
        'Mullaitivu' => ['type' => 'Red-Yellow Latosols', 'label' => 'Red-Yellow Latosols'],
        'Batticaloa' => ['type' => 'Alluvial', 'label' => 'Alluvial Soils'],
        'Ampara' => ['type' => 'Alluvial', 'label' => 'Alluvial Soils'],
        'Trincomalee' => ['type' => 'Alluvial', 'label' => 'Alluvial Soils'],
        'Kurunegala' => ['type' => 'Reddish Brown Earths', 'label' => 'Reddish Brown Earths'],
        'Puttalam' => ['type' => 'Regosols', 'label' => 'Regosols (Sandy)'],
        'Anuradhapura' => ['type' => 'Reddish Brown Earths', 'label' => 'Reddish Brown Earths'],
        'Polonnaruwa' => ['type' => 'Reddish Brown Earths', 'label' => 'Reddish Brown Earths'],
        'Badulla' => ['type' => 'Red-Yellow Podzolic', 'label' => 'Red-Yellow Podzolic Soils'],
        'Monaragala' => ['type' => 'Reddish Brown Earths', 'label' => 'Reddish Brown Earths'],
        'Ratnapura' => ['type' => 'Red-Yellow Podzolic', 'label' => 'Red-Yellow Podzolic Soils'],
        'Kegalle' => ['type' => 'Red-Yellow Podzolic', 'label' => 'Red-Yellow Podzolic Soils'],
    ];

    // Soil type key → common crop variety soil name mappings
    private array $soilNameMap = [
        'Reddish Brown Earths' => 'Reddish Brown Earth',
        'Low Humic Gley' => 'Alluvial', // paddy behavior
        'Non-Calcic Brown' => 'Reddish Brown Earth', 
        'Red-Yellow Podzolic' => 'Red Yellow Podzolic',
        'Red-Yellow Latosols' => 'Sandy Loam',
        'Calcic Latosols' => 'Sandy',
        'Alluvial' => 'Alluvial',
        'Solodized Solonetz' => 'Sandy',
        'Regosols' => 'Sandy',
        'Grumusols' => 'Black Soil',
        'Immature Brown Loams' => 'Lateritic',
        'Bog & Half-Bog' => 'Black Soil',
        'Reddish Brown Latosolic' => 'Reddish Brown Earth',
        'Rendzina' => 'Reddish Brown Earth',
        'Coastal Sands' => 'Sandy',
    ];

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Views
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public function index()
    {
        $userFarms = auth()->check() ? auth()->user()->farms : collect();
        return view('crops.planner', [
            'crops' => Crop::all(),
            'userFarms' => $userFarms
        ]);
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

    public function savePlan(Request $request)
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'crop' => 'required|string',
            'variety' => 'required|string',
            'planting_date' => 'required|date',
            'estimated_harvest' => 'required|date',
            'stages' => 'required|array',
            'stages.*.name' => 'required|string',
            'stages.*.date' => 'required|date',
            'stages.*.advice' => 'required|string',
        ]);

        $season = CropSeason::create([
            'farm_id' => $request->farm_id,
            'crop_name' => $request->crop,
            'crop_variety' => $request->variety,
            'planting_date' => $request->planting_date,
            'expected_harvest_date' => $request->estimated_harvest,
            'crop_stage' => 'planned',
            'notes' => 'Plan generated by AgriAssist AI'
        ]);

        foreach ($request->stages as $stage) {
            CropTask::create([
                'crop_season_id' => $season->id,
                'task_name' => $stage['name'],
                'description' => $stage['advice'],
                'stage' => $stage['name'], // since we changed stage to string
                'due_date' => $stage['date'],
                'completed' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cultivation roadmap saved successfully.',
            'season_id' => $season->id
        ]);
    }

    public function toggleTask(\App\Models\CropTask $task)
    {
        // Security check: ensure user owns this task
        if ($task->cropSeason->farm->farmer_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->completed = !$task->completed;
        $task->save();

        return response()->json([
            'success' => true,
            'completed' => $task->completed
        ]);
    }

    public function apiCalculate(Request $request)
    {
        $request->validate([
            'crop_variety_id' => 'required|exists:crop_varieties,id',
            'planting_date' => 'required|date',
        ]);

        $variety = CropVariety::with('crop')->find($request->crop_variety_id);
        $plantingDate = Carbon::parse($request->planting_date);
        $harvestDate = $plantingDate->copy()->addDays($variety->growth_days);
        $stages = $this->calculateStages($variety, $plantingDate);

        return response()->json([
            'crop' => $variety->crop->name,
            'variety' => $variety->variety_name,
            'growth_days' => $variety->growth_days,
            'planting_date' => $plantingDate->toDateString(),
            'estimated_harvest' => $harvestDate->toDateString(),
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
            'soil_type_label' => $soilInfo['label'],
            'all_soil_types' => array_values(array_unique(array_column($this->districtSoilMap, 'label'))),
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
            'soil_type_label' => $soilInfo['label'],
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
        ]);

        $soilType = $request->soil_type;
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
                $soilTypes = json_decode($soilTypes, true);
            }
            $soilTypes = is_array($soilTypes) ? $soilTypes : [];

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
            $idealMonths = $variety->crop->ideal_months;
            if (is_string($idealMonths)) {
                $idealMonths = json_decode($idealMonths, true);
            }
            $idealMonths = is_array($idealMonths) ? $idealMonths : [];
            
            $monthScore = in_array($month, $idealMonths) ? 10 : 0;

            $totalScore = $seasonScore + $soilScore + $tempScore + $monthScore;

            // Avoid duplicate crop names (keep highest scored variety per crop)
            $cropName = $variety->crop->name;
            if (!isset($scored[$cropName]) || $scored[$cropName]['score'] < $totalScore) {
                $scored[$cropName] = [
                    'crop_id' => $variety->crop->id,
                    'variety_id' => $variety->id,
                    'crop_name' => $cropName,
                    'variety_name' => $variety->variety_name,
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
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&zoom=8";

        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: AgriAssist-SriLanka/1.0\r\n",
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

        $stagesData = [
            ['name' => 'Land Preparation', 'days_offset' => -7, 'icon' => 'tractor', 'advice' => 'Clear field boundaries and check irrigation canals. Apply basal fertilizer if required by soil type.'],
            ['name' => 'Sowing / Seedling', 'days_offset' => 0, 'icon' => 'sprout', 'advice' => 'Optimal time for direct sowing or transplanting nurseries. Ensure standing water is at 2-3cm.'],
            ['name' => 'Vegetative Phase', 'days_offset' => round($totalDays * 0.35), 'icon' => 'trending-up', 'advice' => 'High nitrogen requirement. Apply first top dressing (Urea). Control weeds thoroughly.'],
            ['name' => 'Flowering / Booting', 'days_offset' => round($totalDays * 0.70), 'icon' => 'flower-2', 'advice' => 'Critical moisture phase. Do not let the field dry. Apply final top dressing of Potassium (MOP).'],
            ['name' => 'Ripening Phase', 'days_offset' => round($totalDays * 0.90), 'icon' => 'sun', 'advice' => 'Gradually reduce water levels. Watch for birds and grain-sucking insects.'],
            ['name' => 'Harvest', 'days_offset' => $totalDays, 'icon' => 'shopping-basket', 'advice' => 'Harvest when 85-90% of grains are straw-colored. Dry properly to below 14% moisture.'],
        ];

        return array_map(function ($stage) use ($plantingDate) {
            $date = $plantingDate->copy()->addDays($stage['days_offset']);
            return [
                'name' => __($stage['name']),
                'date' => $date->toDateString(),
                'formatted_date' => $date->format('M d, Y'),
                'icon' => $stage['icon'],
                'advice' => __($stage['advice']),
                'days_from_start' => $stage['days_offset'],
            ];
        }, $stagesData);
    }
}