<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\CropVariety;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CropPlannerController extends Controller
{
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

        $seasonalAdvice = $this->getSeasonalAdvice($variety, $plantingDate);

        return view('crops.planner', [
            'crops' => Crop::all(),
            'result' => [
                'crop' => $variety->crop->name,
                'variety' => $variety->variety_name,
                'growth_days' => $variety->growth_days,
                'planting_date' => $plantingDate->toDateString(),
                'estimated_harvest' => $harvestDate->toDateString(),
                'seasonal_advice' => $seasonalAdvice
            ]
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

        return response()->json([
            'crop' => $variety->crop->name,
            'variety' => $variety->variety_name,
            'growth_days' => $variety->growth_days,
            'planting_date' => $plantingDate->toDateString(),
            'estimated_harvest' => $harvestDate->toDateString(),
        ]);
    }

    private function getSeasonalAdvice($variety, $plantingDate)
    {
        $month = $plantingDate->month;

        // Maha Season: September – October planting
        // Yala Season: April – May planting

        $isMaha = ($month >= 9 && $month <= 10);
        $isYala = ($month >= 4 && $month <= 5);

        if ($variety->season === 'both') {
            return null;
        }

        if ($variety->season === 'maha' && !$isMaha) {
            return "This crop variety is typically planted during the Maha season (September – October).";
        }

        if ($variety->season === 'yala' && !$isYala) {
            return "This crop variety is typically planted during the Yala season (April – May).";
        }

        return null;
    }
}