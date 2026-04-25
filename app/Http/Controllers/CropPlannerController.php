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

/**
 * CropPlannerController
 * 
 * Manages the cultivation planner lifecycle: soil mapping, variety suggestions, 
 * and AI-driven intelligence generation.
 */
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
        private TranslationService $translationService
    ) {}

    /**
     * Public API to get soil type by district name.
     */
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

    /**
     * View Entry Point
     */
    public function index(): View
    {
        return view('crops.planner', [
            'crops'     => Crop::with('varieties')->get(),
            'userFarms' => auth()->check() ? auth()->user()->farms : collect()
        ]);
    }

    /**
     * Resolve soil classification via district mapping.
     */
    public function getSoilType(Request $request): JsonResponse
    {
        $request->validate([
            'district' => 'required|string',
        ]);

        $soil = $this->districtSoilMap[$request->district] ?? ['type' => 'Alluvial', 'suitability' => 'Medium'];

        return response()->json(array_merge($soil, ['district' => $request->district]));
    }

    /**
     * Rank varieties by environmental compatibility.
     */
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

    /**
     * Orchestrate roadmap generation with "Learning Mode" failover.
     */
    public function apiCalculate(Request $request): JsonResponse
    {
        $request->validate([
            'planting_date' => 'required|date',
            'land_size'     => 'nullable|numeric|min:0.1',
            'land_unit'     => 'nullable|string|in:Acres,Hectares,Perches',
        ]);

        $variety = $this->resolveVariety($request);

        // Logic Upgrade: Even if stages exist (predefined), we check if they are "fresh" 
        // (refreshed by AI in the last 30 days). If not, we trigger an AI upgrade.
        $isFresh = $variety && $variety->ai_last_refreshed_at && $variety->ai_last_refreshed_at->greaterThanOrEqualTo(now()->subDays(30));

        if (!$variety?->stages()->exists() || !$isFresh) {
            return $this->dispatchGenerationJob($request, $variety);
        }

        $this->ensureTranslations($variety);
        
        return response()->json(
            $this->buildRoadmapResponse($variety, Carbon::parse($request->planting_date), $request->land_size, $request->land_unit)
        );
    }

    /**
     * Poll async generation status.
     */
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
        if (!$cropName) {
            return response()->json(['error' => 'Input insufficient for intelligence generation.'], 422);
        }

        $soilType = $this->districtSoilMap[$request->district]['type'] ?? 'Alluvial';
        $lockKey = sprintf(AnalysisService::GENERATION_LOCK_KEY, Str::slug($cropName), $soilType);

        if ($existingJobId = Cache::get($lockKey)) {
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
            $request->custom_variety_name ?? $variety?->variety_name
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
        return $v->stages->map(fn($s) => [
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
        ])->toArray();
    }

    private function ensureTranslations(CropVariety $v): void
    {
        $updated = false;

        if (empty($v->crop->name_si)) {
            $v->crop->update(['name_si' => $this->translationService->translate($v->crop->name, 'si')]);
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
