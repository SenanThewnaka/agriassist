<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\Farm;
use App\Jobs\GenerateCropPlanJob;
use App\Services\AnalysisService;
use App\Services\TranslationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

// Manages the cultivation planner lifecycle: soil mapping, variety suggestions, and AI-driven intelligence generation.
class CropPlannerController extends Controller
{
    private array $districtSoilMap = [
        'Anuradhapura' => ['type' => 'Reddish Brown Earths', 'suitability' => 'High'],
        'Polonnaruwa'  => ['type' => 'Reddish Brown Earths', 'suitability' => 'High'],
        'Kurunegala'   => ['type' => 'Reddish Brown Earths', 'suitability' => 'Medium'],
        'Matale'       => ['type' => 'Reddish Brown Earths', 'suitability' => 'Medium'],
        'Ampara'       => ['type' => 'Alluvial Soils', 'suitability' => 'High'],
        'Batticaloa'   => ['type' => 'Alluvial Soils', 'suitability' => 'High'],
        'Jaffna'       => ['type' => 'Regosols', 'suitability' => 'High'],
        'Kalutara'     => ['type' => 'Red-Yellow Podzolic Soils', 'suitability' => 'Medium'],
        'Colombo'      => ['type' => 'Red-Yellow Podzolic Soils', 'suitability' => 'Low'],
        'Gampaha'      => ['type' => 'Red-Yellow Podzolic Soils', 'suitability' => 'Medium'],
        'Kandy'        => ['type' => 'Red-Yellow Podzolic Soils', 'suitability' => 'High'],
        'Nuwara Eliya' => ['type' => 'Red-Yellow Podzolic Soils', 'suitability' => 'High'],
        'Badulla'      => ['type' => 'Red-Yellow Podzolic Soils', 'suitability' => 'High'],
        'Hambantota'   => ['type' => 'Reddish Brown Earths', 'suitability' => 'Medium'],
    ];

    public function __construct(
        private TranslationService $translationService,
        private AnalysisService $analysisService
    ) {}

    // Public API to get soil type by district name.
    public function getSoilByDistrict(Request $request): JsonResponse
    {
        $district = $request->query('district');
        if (!$district) {
            return response()->json(['error' => 'District required'], 400);
        }

        $soil = $this->districtSoilMap[$district] ?? ['type' => 'Alluvial', 'suitability' => 'Medium'];

        return response()->json([
            'soil_type' => $soil['type'],
            'suitability' => $soil['suitability']
        ]);
    }

    // Fetch variety suggestions for a custom crop name.
    public function apiSuggestVarieties(Request $request): JsonResponse
    {
        $request->validate([
            'crop_name' => 'required|string',
            'soil_type' => 'required|string',
        ]);

        $varieties = $this->analysisService->suggestVarieties($request->crop_name, $request->soil_type);

        return response()->json(['varieties' => $varieties]);
    }

    // Fetch AI recommendation for planting date based on weather.
    public function apiRecommendDate(Request $request): JsonResponse
    {
        $request->validate([
            'crop_variety_id'   => 'nullable',
            'custom_crop_name'  => 'nullable|string',
            'weather'           => 'required|array',
            'soil_type'         => 'required|string',
        ]);

        $variety = $this->resolveVariety($request);
        $cropName = $request->custom_crop_name ?? $variety?->crop->name ?? 'Unknown Crop';

        $recommendation = $this->analysisService->recommendPlantingDate($cropName, $request->weather, $request->soil_type);

        return response()->json($recommendation);
    }

    // View Entry Point
    public function index(): View
    {
        return view('crops.planner', [
            'crops'     => Crop::with('varieties')->orderBy('name')->get(),
            'userFarms' => auth()->check() ? auth()->user()->farms : collect()
        ]);
    }

    // Resolve soil classification via district mapping.
    public function getSoilType(Request $request): JsonResponse
    {
        $request->validate([
            'district' => 'required|string',
        ]);

        $soil = $this->districtSoilMap[$request->district] ?? ['type' => 'Alluvial', 'suitability' => 'Medium'];

        return response()->json(array_merge($soil, ['district' => $request->district]));
    }

    // Rank varieties by environmental compatibility.
    public function getSmartSuggestions(Request $request): JsonResponse
    {
        $request->validate([
            'crop_id'   => 'nullable|exists:crops,id',
            'soil_type' => 'required|string',
        ]);

        $query = CropVariety::with('crop');

        if ($request->crop_id) {
            $query->where('crop_id', $request->crop_id);
        }

        $results = $query->get()
            ->map(fn($v) => [
                'crop_id'          => $v->crop_id,
                'crop_name'        => $v->crop->name,
                'crop_name_si'     => $v->crop->name_si,
                'crop_name_ta'     => $v->crop->name_ta,
                'variety_id'       => $v->id,
                'variety_name'     => $v->variety_name,
                'variety_name_si'  => $v->variety_name_si,
                'variety_name_ta'  => $v->variety_name_ta,
                'growth_days'      => $v->growth_days,
                'advantages'       => $v->notes,
                'advantages_si'    => $v->notes_si,
                'advantages_ta'    => $v->notes_ta,
                'price_per_kg_lkr' => $v->base_market_price_per_kg,
                'suitability'      => $this->calculateMatchScore($v, $request->soil_type)
            ])
            ->sortByDesc('suitability')
            ->values();

        // Limit to top 6 if no specific crop requested
        if (!$request->crop_id) {
            $results = $results->take(6);
        }

        return response()->json(['suggestions' => $results]);
    }

    // Orchestrate roadmap generation with "Learning Mode" failover.
    public function apiCalculate(Request $request): JsonResponse
    {
        // Normalize custom AI variety names to the expected format
        if ($request->crop_variety_id && !is_numeric($request->crop_variety_id) && $request->crop_variety_id !== 'other') {
            $request->merge([
                'custom_variety_name' => $request->crop_variety_id,
                'crop_variety_id' => 'other'
            ]);
        }

        $request->validate([
            'planting_date'     => 'required|date',
            'land_size'         => 'nullable|numeric|min:0.1',
            'land_unit'         => 'nullable|string|in:Acres,Hectares,Perches',
            'crop_variety_id'   => 'nullable',
            'custom_crop_name'  => 'nullable|string',
            'custom_variety_name' => 'nullable|string',
            'soil_type'         => 'required|string',
            'district'          => 'nullable|string',
        ]);

        $variety = $this->resolveVariety($request);

        // Fallback to database roadmap if stages already exist
        $hasStages = $variety?->stages()->exists();
        $isFresh   = $variety && $variety->ai_last_refreshed_at 
                     && $variety->ai_last_refreshed_at->greaterThanOrEqualTo(now()->subDays(30));

        if ($hasStages) {
            $this->ensureTranslations($variety);
            return response()->json([
                'result' => $this->buildRoadmapResponse($variety, Carbon::parse($request->planting_date), $request->land_size, $request->land_unit)
            ]);
        }

        // Trigger generation job if no stages are found
        return $this->dispatchGenerationJob($request, $variety);
    }

    // Poll async generation status.
    public function apiCheckStatus(string $jobId): JsonResponse
    {
        $status = Cache::get(sprintf(AnalysisService::STATUS_KEY, $jobId));

        if (!$status) {
            return response()->json(['status' => 'not_found'], 404);
        }

        // Fast-track: Cached Result
        if ($status['status'] === 'completed' && isset($status['full_result'])) {
            return response()->json(array_merge($status, ['result' => $status['full_result']]));
        }

        // Recovery: DB Hydration
        if ($status['status'] === 'completed' && isset($status['variety_id'])) {
            $variety = CropVariety::with(['crop', 'stages'])->find($status['variety_id']);
            
            if ($variety?->stages()->exists()) {
                $this->ensureTranslations($variety);
                $result = $this->buildRoadmapResponse(
                    $variety, 
                    Carbon::parse($status['planting_date'] ?? now()), 
                    $status['land_size'] ?? 1.0, 
                    $status['land_unit'] ?? 'Acres', 
                    true
                );
                return response()->json(array_merge($status, ['result' => $result]));
            }
        }

        return response()->json($status);
    }

    public function toggleTask(\App\Models\CropTask $task): JsonResponse
    {
        if ($task->cropSeason->farm->farmer_id !== auth()->id()) {
            abort(403);
        }

        $task->update(['completed' => !$task->completed]);

        return response()->json(['success' => true, 'completed' => $task->completed]);
    }

    public function savePlan(Request $request): JsonResponse
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'roadmap' => 'required|array',
        ]);

        $farm = Farm::findOrFail($request->farm_id);
        if ($farm->farmer_id !== auth()->id()) {
            abort(403);
        }

        try {
            $data = $request->roadmap;

            // Create Season
            $season = \App\Models\CropSeason::create([
                'farm_id'               => $farm->id,
                'crop_name'             => $data['crop'],
                'crop_name_si'          => $data['crop_name_si'] ?? null,
                'crop_name_ta'          => $data['crop_name_ta'] ?? null,
                'crop_variety'          => $data['variety'],
                'crop_variety_si'       => $data['variety_name_si'] ?? null,
                'crop_variety_ta'       => $data['variety_name_ta'] ?? null,
                'planting_date'         => $data['planting_date'],
                'expected_harvest_date' => $data['estimated_harvest'],
                'crop_stage'            => 'Initial',
                'health_score'          => $data['health_score'] ?? 100,
            ]);

            // Create Tasks from Stages
            foreach ($data['stages'] as $stage) {
                \App\Models\CropTask::create([
                    'crop_season_id' => $season->id,
                    'task_name'      => $stage['name'],
                    'task_name_si'   => $stage['name_si'] ?? null,
                    'task_name_ta'   => $stage['name_ta'] ?? null,
                    'description'    => $stage['advice'],
                    'description_si' => $stage['advice_si'] ?? null,
                    'description_ta' => $stage['advice_ta'] ?? null,
                    'due_date'       => $stage['date'],
                    'stage'          => $stage['name'],
                    'completed'      => false,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Plan saved successfully.']);
        } catch (\Exception $e) {
            Log::error("Failed to save crop plan", ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to save plan: ' . $e->getMessage()], 500);
        }
    }

    private function resolveVariety(Request $request): ?CropVariety
    {
        if ($request->crop_variety_id && $request->crop_variety_id !== 'other') {
            return CropVariety::with(['crop', 'stages'])->find($request->crop_variety_id);
        }

        if ($request->custom_crop_name && $request->custom_variety_name) {
            return CropVariety::where('variety_name', $request->custom_variety_name)
                ->whereHas('crop', fn($q) => $q->where('name', $request->custom_crop_name))
                ->with(['crop', 'stages'])
                ->first();
        }

        return null;
    }

    private function dispatchGenerationJob(Request $request, ?CropVariety $variety): JsonResponse
    {
        $cropName = $request->custom_crop_name ?? $variety?->crop->name;
        $varietyName = $request->custom_variety_name ?? $variety?->variety_name ?? 'Local Variety';

        if (!$cropName) {
            return response()->json(['error' => 'Input insufficient for intelligence generation.'], 422);
        }

        // Use direct soil_type from request if available (for custom flows), 
        // otherwise fallback to district map.
        $soilType = $request->soil_type ?? ($this->districtSoilMap[$request->district]['type'] ?? 'Alluvial');
        
        // UNIQUE Lock key: Include variety name to prevent collisions with other crops on same soil
        $lockKey = sprintf(AnalysisService::GENERATION_LOCK_KEY, Str::slug($cropName . '-' . $varietyName), $soilType);

        // Bypass cache for custom crops to ensure fresh AI run
        if (!$request->custom_crop_name && ($existingJobId = Cache::get($lockKey))) {
            return response()->json(['status' => 'processing', 'job_id' => $existingJobId]);
        }

        $jobId = (string) Str::uuid();
        Cache::put($lockKey, $jobId, now()->addMinutes(10));
        Cache::put(sprintf(AnalysisService::STATUS_KEY, $jobId), [
            'status'        => 'processing',
            'planting_date' => $request->planting_date,
            'land_size'     => $request->land_size,
            'land_unit'     => $request->land_unit,
        ], now()->addHours(1));

        GenerateCropPlanJob::dispatch(
            $cropName,
            $soilType,
            app()->getLocale(),
            auth()->id() ?? 0,
            $jobId,
            $varietyName
        );

        return response()->json(['status' => 'processing', 'job_id' => $jobId]);
    }

    private function buildRoadmapResponse(CropVariety $v, Carbon $pDate, $size = 1.0, $unit = 'Acres', bool $gen = false): array
    {
        $acres = $this->convertToAcres((float)($size ?? 1.0), (string)($unit ?? 'Acres'));
        
        // Find active health score if this is for an existing farm
        $healthScore = 100;
        if (request()->has('farm_id')) {
             $activeSeason = \App\Models\CropSeason::where('farm_id', request('farm_id'))
                ->where('crop_name', $v->crop->name)
                ->where('expected_harvest_date', '>=', now()->toDateString())
                ->latest()
                ->first();
             if ($activeSeason) $healthScore = $activeSeason->health_score;
        }

        $healthFactor = $healthScore / 100;

        // NEW: Fetch active pest alerts for this district and crop
        $district = request('district');
        $pestAlerts = [];
        if ($district) {
            $pestAlerts = \App\Models\PestAlert::where('district', $district)
                ->where(function($q) use ($v) {
                    $q->where('crop_name', 'like', '%' . $v->crop->name . '%')
                      ->orWhereNull('crop_name');
                })
                ->where('created_at', '>', now()->subDays(7))
                ->latest()
                ->get();
        }

        return [
            'crop'              => $v->crop->name,
            'crop_name_si'      => $v->crop->name_si,
            'crop_name_ta'      => $v->crop->name_ta,
            'variety'           => $v->variety_name,
            'variety_name_si'   => $v->variety_name_si,
            'variety_name_ta'   => $v->variety_name_ta,
            'growth_days'       => $v->growth_days,
            'planting_date'     => $pDate->toDateString(),
            'estimated_harvest' => $pDate->copy()->addDays($v->growth_days)->toDateString(),
            'stages'            => $this->calculateStages($v, $pDate),
            'health_score'      => $healthScore,
            'pest_alerts'       => $pestAlerts,
            'estimates'         => [
                'seeds_kg'          => round(($v->seed_per_acre_kg ?: 5) * $acres, 1),
                'urea_kg'           => round($v->stages->sum('urea_per_acre_kg') * $acres, 1),
                'tsp_kg'            => round($v->stages->sum('tsp_per_acre_kg') * $acres, 1),
                'mop_kg'            => round($v->stages->sum('mop_per_acre_kg') * $acres, 1),
                'expected_yield_kg' => round(($v->yield_per_acre_kg ?: 5000) * $acres * $healthFactor, 0),
                'estimated_revenue' => round(($v->yield_per_acre_kg ?: 5000) * $acres * ($v->base_market_price_per_kg ?: 150) * $healthFactor, 0),
            ],
            'land_size_acres'   => $acres,
            'is_generated'      => $gen
        ];
    }

    private function calculateStages(CropVariety $v, Carbon $pDate): array
    {
        return $v->stages->map(function($s) use ($pDate) {
            $updated = false;

            // Name
            if (empty($s->name_si)) {
                $s->name_si = $this->translationService->translate($s->name, 'si');
                $updated = true;
            }
            if (empty($s->name_ta)) {
                $s->name_ta = $this->translationService->translate($s->name, 'ta');
                $updated = true;
            }

            // Advice
            if (empty($s->advice_si)) {
                $s->advice_si = $this->translationService->translate($s->advice, 'si');
                $updated = true;
            }
            if (empty($s->advice_ta)) {
                $s->advice_ta = $this->translationService->translate($s->advice, 'ta');
                $updated = true;
            }

            // Description
            if (empty($s->description_si)) {
                $s->description_si = $this->translationService->translate($s->description ?? '', 'si');
                $updated = true;
            }
            if (empty($s->description_ta)) {
                $s->description_ta = $this->translationService->translate($s->description ?? '', 'ta');
                $updated = true;
            }

            if ($updated) $s->save();

            return [
                'name'            => $s->name,
                'name_si'         => $s->name_si,
                'name_ta'         => $s->name_ta,
                'date'            => $pDate->copy()->addDays($s->days_offset)->toDateString(),
                'days_from_start' => $s->days_offset,
                'icon'            => $s->icon,
                'advice'          => $s->advice,
                'advice_si'       => $s->advice_si,
                'advice_ta'       => $s->advice_ta,
                'description'     => $s->description,
                'description_si'  => $s->description_si,
                'description_ta'  => $s->description_ta,
                'urea_kg'         => $s->urea_per_acre_kg,
                'tsp_kg'          => $s->tsp_per_acre_kg,
                'mop_kg'          => $s->mop_per_acre_kg,
            ];
        })->toArray();
    }

    private function ensureTranslations(CropVariety $v): void
    {
        $updated = false;

        // Crop Translations
        if (empty($v->crop->name_si)) {
            $v->crop->update(['name_si' => $this->translationService->translate($v->crop->name, 'si')]);
            $updated = true;
        }
        if (empty($v->crop->name_ta)) {
            $v->crop->update(['name_ta' => $this->translationService->translate($v->crop->name, 'ta')]);
            $updated = true;
        }

        // Variety Translations
        if (empty($v->variety_name_si)) {
            $v->update(['variety_name_si' => $this->translationService->translate($v->variety_name, 'si')]);
            $updated = true;
        }
        if (empty($v->variety_name_ta)) {
            $v->update(['variety_name_ta' => $this->translationService->translate($v->variety_name, 'ta')]);
            $updated = true;
        }
        
        if ($updated) $v->load(['crop', 'stages']);
    }

    private function calculateMatchScore(CropVariety $v, string $soil): int
    {
        if (empty($v->soil_types)) return 50;

        $targetSoil = strtolower(trim($soil));
        foreach ($v->soil_types as $type) {
            $currentType = strtolower(trim($type));
            if (str_contains($currentType, $targetSoil) || str_contains($targetSoil, $currentType)) {
                return 100;
            }
        }

        return 60;
    }

    private function convertToAcres(float $size, string $unit): float
    {
        return match ($unit) {
            'Hectares' => $size * 2.47105,
            'Perches'  => $size / 160,
            default    => $size,
        };
    }
}