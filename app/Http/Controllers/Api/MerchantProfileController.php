<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\MerchantProfile;
use Illuminate\Http\Request;
class MerchantProfileController extends Controller {
    public function index() { return response()->json(MerchantProfile::all()); }
    public function store(Request $request) {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'store_name' => 'required|string',
            'store_logo' => 'nullable|string',
            'description' => 'nullable|string',
            'store_location' => 'nullable|string',
            'phone' => 'nullable|string',
            'website' => 'nullable|string',
            'delivery_available' => 'boolean'
        ]);
        return response()->json(MerchantProfile::create($validated), 201);
    }
    public function show(MerchantProfile $merchantProfile) { return response()->json($merchantProfile); }
    public function update(Request $request, MerchantProfile $merchantProfile) {
        $validated = $request->validate([
            'store_name' => 'nullable|string',
            'store_logo' => 'nullable|string',
            'description' => 'nullable|string',
            'store_location' => 'nullable|string',
            'phone' => 'nullable|string',
            'website' => 'nullable|string',
            'delivery_available' => 'boolean'
        ]);
        $merchantProfile->update($validated);
        return response()->json($merchantProfile);
    }
    public function destroy(MerchantProfile $merchantProfile) {
        $merchantProfile->delete();
        return response()->json(null, 204);
    }
}