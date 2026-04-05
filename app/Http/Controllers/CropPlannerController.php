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
    // Standardized to match the frontend <option value="..."> exactly.
    private array $districtSoilMap = [
        'Colombo' => ['type' => 'Alluvial Soils', 'label' => 'Alluvial Soils'],
        'Gampaha' => ['type' => 'Regosols', 'label' => 'Regosols (Sandy)'],
        'Kalutara' => ['type' => 'Red-Yellow Podzolic Soils', 'label' => 'Red-Yellow Podzolic Soils'],
        'Kandy' => ['type' => 'Reddish Brown Latosolic Soils', 'label' => 'Reddish Brown Latosolic Soils'],
        'Matale' => ['type' => 'Reddish Brown Latosolic Soils', 'label' => 'Reddish Brown Latosolic Soils'],
        'Nuwara Eliya' => ['type' => 'Red-Yellow Podzolic Soils', 'label' => 'Red-Yellow Podzolic Soils'],
        'Galle' => ['type' => 'Red-Yellow Podzolic Soils', 'label' => 'Red-Yellow Podzolic Soils'],
        'Matara' => ['type' => 'Red-Yellow Podzolic Soils', 'label' => 'Red-Yellow Podzolic Soils'],
        'Hambantota' => ['type' => 'Reddish Brown Earths', 'label' => 'Reddish Brown Earths'],
        'Jaffna' => ['type' => 'Calcic Latosols', 'label' => 'Calcic Latosols'],
        'Kilinochchi' => ['type' => 'Red-Yellow Latosols', 'label' => 'Red-Yellow Latosols'],
        'Mannar' => ['type' => 'Red-Yellow Latosols', 'label' => 'Red-Yellow Latosols'],
        'Vavuniya' => ['type' => 'Red-Yellow Latosols', 'label' => 'Red-Yellow Latosols'],
        'Mullaitivu' => ['type' => 'Red-Yellow Latosols', 'label' => 'Red-Yellow Latosols'],
        'Batticaloa' => ['type' => 'Alluvial Soils', 'label' => 'Alluvial Soils'],
        'Ampara' => ['type' => 'Alluvial Soils', 'label' => 'Alluvial Soils'],
        'Trincomalee' => ['type' => 'Alluvial Soils', 'label' => 'Alluvial Soils'],
        'Kurunegala' => ['type' => 'Reddish Brown Earths', 'label' => 'Reddish Brown Earths'],
        'Puttalam' => ['type' => 'Regosols', 'label' => 'Regosols (Sandy)'],
        'Anuradhapura' => ['type' => 'Reddish Brown Earths', 'label' => 'Reddish Brown Earths'],
        'Polonnaruwa' => ['type' => 'Reddish Brown Earths', 'label' => 'Reddish Brown Earths'],
        'Badulla' => ['type' => 'Red-Yellow Podzolic Soils', 'label' => 'Red-Yellow Podzolic Soils'],
        'Monaragala' => ['type' => 'Reddish Brown Earths', 'label' => 'Reddish Brown Earths'],
        'Ratnapura' => ['type' => 'Red-Yellow Podzolic Soils', 'label' => 'Red-Yellow Podzolic Soils'],
        'Kegalle' => ['type' => 'Red-Yellow Podzolic Soils', 'label' => 'Red-Yellow Podzolic Soils'],
    ];

    /**
     * Look up soil type directly by district name
     */
    public function getSoilByDistrict(Request $request)
    {
        $district = $request->query('district', '');
        
        // Match the district exactly or find it in the keys
        $soilInfo = $this->districtSoilMap[$district] ?? null;

        // If not found, try a case-insensitive search
        if (!$soilInfo) {
            foreach ($this->districtSoilMap as $key => $value) {
                if (strtolower($key) === strtolower($district)) {
                    $soilInfo = $value;
                    break;
                }
            }
        }

        // Final Fallback if still not found
        if (!$soilInfo) {
            $soilInfo = ['type' => 'Reddish Brown Earths', 'label' => 'Reddish Brown Earths'];
        }

        return response()->json([
            'district' => $district,
            'soil_type' => $soilInfo['type'],
            'label' => $soilInfo['label']
        ]);
    }

    public function index()
    {
        return view('crops.planner', ['crops' => Crop::all()]);
    }

    public function calculate(Request $request)
    {
        $variety = CropVariety::with('crop')->find($request->crop_variety_id);
        $plantingDate = Carbon::parse($request->planting_date);
        $stages = $this->calculateStages($variety, $plantingDate);

        return view('crops.planner', [
            'crops' => Crop::all(),
            'result' => [
                'crop' => $variety->crop->name,
                'variety' => $variety->variety_name,
                'growth_days' => $variety->growth_days,
                'planting_date' => $plantingDate->toDateString(),
                'stages' => $stages
            ]
        ]);
    }

    private function calculateStages($variety, $startDate)
    {
        $stages = $variety->stages()->orderBy('days_offset')->get();
        return $stages->map(function($stage) use ($startDate) {
            $date = $startDate->copy()->addDays($stage->days_offset);
            return array_merge($stage->toArray(), [
                'date' => $date->toDateString(),
                'formatted_date' => $date->format('j F, Y')
            ]);
        });
    }
}
