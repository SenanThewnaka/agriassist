<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Farm;
use Illuminate\Http\Request;
class FarmController extends Controller {
    public function index() { return response()->json(Farm::all()); }
    public function store(Request $request) {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:users,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'farm_name' => 'required|string',
            'soil_type' => 'nullable|string',
            'farm_size' => 'nullable|string',
            'irrigation_source' => 'nullable|string',
            'elevation' => 'nullable|numeric',
            'district' => 'nullable|string'
        ]);
        return response()->json(Farm::create($validated), 201);
    }
    public function show(Farm $farm) { return response()->json($farm); }
    public function update(Request $request, Farm $farm) {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'farm_name' => 'nullable|string',
            'soil_type' => 'nullable|string',
            'farm_size' => 'nullable|string',
            'irrigation_source' => 'nullable|string',
            'elevation' => 'nullable|numeric',
            'district' => 'nullable|string'
        ]);
        $farm->update($validated);
        return response()->json($farm);
    }
    public function destroy(Farm $farm) {
        $farm->delete();
        return response()->json(null, 204);
    }
}