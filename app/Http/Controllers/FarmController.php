<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FarmController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'farm_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'farm_size' => 'nullable|string',
            'district' => 'nullable|string',
            'soil_type' => 'nullable|string',
            'irrigation_source' => 'nullable|string',
        ]);

        $farm = new Farm($validated);
        $farm->farmer_id = Auth::id();
        $farm->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Farm registered successfully.',
                'farm' => $farm
            ]);
        }

        return back()->with('status', 'Farm registered successfully.');
    }

    public function update(Request $request, Farm $farm)
    {
        if ($farm->farmer_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'farm_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'farm_size' => 'nullable|string',
            'district' => 'nullable|string',
            'soil_type' => 'nullable|string',
            'irrigation_source' => 'nullable|string',
        ]);

        $farm->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Farm intelligence updated.',
                'farm' => $farm
            ]);
        }

        return back()->with('status', 'Farm updated.');
    }

    public function destroy(Farm $farm)
    {
        if ($farm->farmer_id !== Auth::id()) {
            abort(403);
        }

        $farm->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'Farm removed.');
    }

    /**
     * Proxy request to Nominatim to protect user IP address.
     */
    public function proxyGeocode(Request $request)
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');

        if (!$lat || !$lon) return response()->json(['error' => 'Missing coordinates'], 400);

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'User-Agent' => 'AgriAssist/1.0'
        ])->get("https://nominatim.openstreetmap.org/reverse", [
            'lat' => $lat,
            'lon' => $lon,
            'format' => 'json',
            'accept-language' => 'en'
        ]);

        return response()->json($response->json());
    }

    /**
     * Proxy request to Photon to protect user search patterns.
     */
    public function proxySearch(Request $request)
    {
        $q = $request->query('q');
        if (!$q) return response()->json([]);

        $bbox = "79.5,5.9,81.9,9.9"; // Sri Lanka Only
        $response = \Illuminate\Support\Facades\Http::get("https://photon.komoot.io/api/", [
            'q' => $q,
            'limit' => 5,
            'bbox' => $bbox
        ]);

        return response()->json($response->json());
    }
}
