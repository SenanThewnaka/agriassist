<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\SoilReport;
use App\Services\AnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FarmController extends Controller
{
    public function __construct(
        private AnalysisService $analysisService
    ) {}

    // Handle soil report upload and AI parsing.
    public function uploadSoilReport(Request $request, Farm $farm): JsonResponse
    {
        $request->validate([
            'report' => 'required|image|max:10240', // 10MB limit
        ]);

        if ($farm->farmer_id !== Auth::id()) {
            abort(403);
        }

        try {
            $file = $request->file('report');
            $path = $file->store('soil_reports', 'public');

            // Call AI engine to analyze the report
            $analysis = $this->analysisService.analyzeSoil([$file]);

            if (isset($analysis['error'])) {
                Storage::disk('public')->delete($path);
                return response()->json(['success' => false, 'message' => $analysis['error']], 422);
            }

            // Create report record
            $report = SoilReport::create([
                'farm_id'        => $farm->id,
                'file_path'      => $path,
                'extracted_data' => $analysis,
                'analyzed_at'    => now(),
            ]);

            // Automatically update farm soil type if extracted
            if (isset($analysis['soil_type'])) {
                $farm->update(['soil_type' => $analysis['soil_type']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Soil report analyzed successfully. Farm profile updated.',
                'data'    => $analysis
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process soil report: ' . $e->getMessage()
            ], 500);
        }
    }

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

    // Proxy request to Nominatim to protect user IP address.
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

    // Proxy request to Photon to protect user search patterns.
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
