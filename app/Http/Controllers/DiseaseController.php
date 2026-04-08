<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Models\Farm;
use App\Services\AnalysisService;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DiseaseController extends Controller
{
    protected AnalysisService $analysisService;
    protected ImageUploadService $uploadService;

    public function __construct(AnalysisService $analysisService, ImageUploadService $uploadService)
    {
        $this->analysisService = $analysisService;
        $this->uploadService = $uploadService;
    }

    /**
     * Display the landing page.
     */
    public function index(): \Illuminate\View\View
    {
        return view('home');
    }

    /**
     * Display the detection page.
     */
    public function detect(): \Illuminate\View\View
    {
        return view('detect');
    }

    /**
     * Get diagnosis partial HTML for AJAX.
     */
    public function getDiagnosisHtml(Diagnosis $diagnosis): \Illuminate\Http\JsonResponse
    {
        $locale = app()->getLocale();
        
        if ($locale !== 'en') {
            $diagnosis->disease = $this->analysisService->translateText($diagnosis->disease, $locale);
            $diagnosis->treatment = $this->analysisService->translateText($diagnosis->treatment, $locale);
            if ($diagnosis->severity) {
                $diagnosis->severity = $this->analysisService->translateText($diagnosis->severity, $locale);
            }
            if ($diagnosis->spread_risk) {
                $diagnosis->spread_risk = $this->analysisService->translateText($diagnosis->spread_risk, $locale);
            }
        }

        return response()->json([
            'html' => view('partials.diagnosis_result', compact('diagnosis'))->render()
        ]);
    }

    /**
     * Handle plant images upload and analysis.
     */
    public function analyze(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\View\View|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'farm_id' => 'nullable|exists:farms,id',
        ]);

        $paths = $this->uploadService->uploadMany($request->file('images'));

        // Prepare Context Intelligence
        $context = [];
        if ($request->farm_id) {
            $farm = Farm::find($request->farm_id);
            if ($farm && $farm->farmer_id === auth()->id()) {
                $context = [
                    'farm_name' => $farm->farm_name,
                    'district' => $farm->district,
                    'soil_type' => $farm->soil_type,
                    'latitude' => $farm->latitude,
                    'longitude' => $farm->longitude,
                ];
            }
        }

        // Call analysis engine with all samples and context
        $prediction = $this->analysisService->predictMany($paths, $context);

        if (isset($prediction['error'])) {
            if ($request->ajax()) {
                return response()->json(['error' => $prediction['message']], 400);
            }
            return back()->with('error', $prediction['message']);
        }

        // Save to database
        $diagnosis = Diagnosis::create([
            'user_id' => auth()->id(),
            'farm_id' => $request->farm_id,
            'image_paths' => $paths,
            'disease' => $prediction['disease'],
            'confidence' => $prediction['confidence'],
            'severity' => $prediction['severity'] ?? null,
            'spread_risk' => $prediction['spread_risk'] ?? null,
            'engine_tier' => $prediction['engine_tier'] ?? null,
            'treatment' => $prediction['treatment'] ?? 'No treatment recommended.',
        ]);

        $locale = app()->getLocale();
        if ($locale !== 'en') {
            $diagnosis->disease = $this->analysisService->translateText($diagnosis->disease, $locale);
            $diagnosis->treatment = $this->analysisService->translateText($diagnosis->treatment, $locale);
            if ($diagnosis->severity) {
                $diagnosis->severity = $this->analysisService->translateText($diagnosis->severity, $locale);
            }
            if ($diagnosis->spread_risk) {
                $diagnosis->spread_risk = $this->analysisService->translateText($diagnosis->spread_risk, $locale);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'id' => $diagnosis->id,
                'html' => view('partials.diagnosis_result', compact('diagnosis'))->render()
            ]);
        }

        return view('result', compact('diagnosis'));
    }
}