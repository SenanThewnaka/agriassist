<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\CropSeason;
use Illuminate\Http\Request;
class CropSeasonController extends Controller {
    public function index() { return response()->json(CropSeason::all()); }
    public function store(Request $request) {
        $validated = $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'crop_name' => 'required|string',
            'crop_variety' => 'nullable|string',
            'planting_date' => 'nullable|date',
            'expected_harvest_date' => 'nullable|date',
            'crop_stage' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);
        return response()->json(CropSeason::create($validated), 201);
    }
    public function show(CropSeason $cropSeason) { return response()->json($cropSeason); }
    public function update(Request $request, CropSeason $cropSeason) {
        $validated = $request->validate([
            'crop_name' => 'nullable|string',
            'crop_variety' => 'nullable|string',
            'planting_date' => 'nullable|date',
            'expected_harvest_date' => 'nullable|date',
            'crop_stage' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);
        $cropSeason->update($validated);
        return response()->json($cropSeason);
    }
    public function destroy(CropSeason $cropSeason) {
        $cropSeason->delete();
        return response()->json(null, 204);
    }
}