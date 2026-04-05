<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\FarmerProfile;
use Illuminate\Http\Request;
class FarmerProfileController extends Controller {
    public function index() { return response()->json(FarmerProfile::all()); }
    public function store(Request $request) {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'farm_size' => 'nullable|string',
            'farming_type' => 'nullable|string',
            'irrigation_type' => 'nullable|string',
            'experience_years' => 'nullable|integer',
            'main_crops' => 'nullable|string'
        ]);
        return response()->json(FarmerProfile::create($validated), 201);
    }
    public function show(FarmerProfile $farmerProfile) { return response()->json($farmerProfile); }
    public function update(Request $request, FarmerProfile $farmerProfile) {
        $validated = $request->validate([
            'farm_size' => 'nullable|string',
            'farming_type' => 'nullable|string',
            'irrigation_type' => 'nullable|string',
            'experience_years' => 'nullable|integer',
            'main_crops' => 'nullable|string'
        ]);
        $farmerProfile->update($validated);
        return response()->json($farmerProfile);
    }
    public function destroy(FarmerProfile $farmerProfile) {
        $farmerProfile->delete();
        return response()->json(null, 204);
    }
}