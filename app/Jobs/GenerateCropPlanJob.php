<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Crop;
use App\Models\CropStage;
use App\Models\CropVariety;
use App\Services\AnalysisService;
use App\Services\TranslationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * GenerateCropPlanJob
 *
 * Handles asynchronous cultivation roadmap generation and "Learning Mode" persistence.
 */
class GenerateCropPlanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int Execution timeout */
    public int $timeout = 240;

    public function __construct(
        public string $cropName,
        public string $soilType,
        public string $locale,
        public int $userId,
        public string $jobId,
        public ?string $varietyName = null
    ) {}

    /**
     * Pipeline execution.
     */
    public function handle(AnalysisService $analysisService, TranslationService $translationService): void
    {
        $statusKey = sprintf(AnalysisService::STATUS_KEY, $this->jobId);
        $status = Cache::get($statusKey, []);
        
        $this->updateStatus($statusKey, array_merge($status, ['status' => 'processing']));

        try {
            // 1. Deduplication logic
            if ($existing = $this->findExistingVarietyWithStages()) {
                $this->completeJob($statusKey, $status, $existing);
                return;
            }

            // 2. Intelligence generation
            $aiResponse = $analysisService->generateCropPlanWithRetries(
                $this->cropName,
                $this->locale,
                $this->varietyName
            );

            if (empty($aiResponse['stages'])) {
                throw new \RuntimeException("Empty growth stage pipeline received.");
            }

            // 3. Transform and Persist
            $roadmap = $this->transformAiResponse($aiResponse, $status, $translationService);
            $this->persistToDatabase($aiResponse, $translationService);
            $this->completeJobWithData($statusKey, $status, $roadmap);

        } catch (Throwable $e) {
            $this->handleFailure($statusKey, $status, $e);
            throw $e;
        }
    }

    private function findExistingVarietyWithStages(): ?CropVariety
    {
        return CropVariety::where('variety_name', 'like', $this->varietyName ?? 'Local Variety')
            ->whereHas('crop', fn($q) => $q->where('name', 'like', $this->cropName))
            ->with('stages')
            ->first()
            ?->whereHas('stages')
            ->first();
    }

    private function transformAiResponse(array $data, array $status, TranslationService $translator): array
    {
        $pDate = Carbon::parse($status['planting_date'] ?? now());
        $lSize = (float)($status['land_size'] ?? 1.0);
        $lUnit = $status['land_unit'] ?? 'Acres';
        $acres = $this->convertToAcres($lSize, $lUnit);

        $stages = collect($data['stages'])->map(fn($s) => [
            'name'            => $s['name'],
            'name_si'         => $s['name_si'] ?? $translator->translate($s['name'], 'si'),
            'name_ta'         => $s['name_ta'] ?? $translator->translate($s['name'], 'ta'),
            'date'            => $pDate->copy()->addDays($s['days_from_start'] ?? 0)->toDateString(),
            'days_from_start' => $s['days_from_start'] ?? 0,
            'icon'            => $s['icon'] ?? 'sprout',
            'advice'          => $s['advice'],
            'advice_si'       => $s['advice_si'] ?? $translator->translate($s['advice'], 'si'),
            'advice_ta'       => $s['advice_ta'] ?? $translator->translate($s['advice'], 'ta'),
            'description'     => $s['description'] ?? '',
            'description_si'  => $translator->translate($s['description'] ?? '', 'si'),
            'description_ta'  => $translator->translate($s['description'] ?? '', 'ta'),
            'urea_kg'         => $s['urea_kg'] ?? 0,
            'tsp_kg'          => $s['tsp_kg'] ?? 0,
            'mop_kg'          => $s['mop_kg'] ?? 0,
        ]);

        return [
            'crop'            => $data['crop'],
            'crop_name_si'    => $data['crop_si'] ?? null,
            'crop_name_ta'    => $data['crop_ta'] ?? null,
            'variety'         => $data['variety'],
            'variety_name_si' => $data['variety_si'] ?? null,
            'variety_name_ta' => $data['variety_ta'] ?? null,
            'growth_days'     => $data['growth_days'],
            'planting_date'   => $pDate->toDateString(),
            'estimated_harvest' => $pDate->copy()->addDays($data['growth_days'])->toDateString(),
            'stages'          => $stages->toArray(),
            'estimates'       => [
                'seeds_kg'          => round(($data['seed_per_acre_kg'] ?? 2.0) * $acres, 1),
                'urea_kg'           => round($stages->sum('urea_kg') * $acres, 1),
                'tsp_kg'            => round($stages->sum('tsp_kg') * $acres, 1),
                'mop_kg'            => round($stages->sum('mop_kg') * $acres, 1),
                'expected_yield_kg' => round(($data['yield_per_acre_kg'] ?? 5000) * $acres, 0),
                'estimated_revenue' => round(($data['yield_per_acre_kg'] ?? 5000) * $acres * ($data['base_market_price_per_kg'] ?? 150), 0)
            ],
            'land_size_acres' => $acres,
            'is_generated'    => true
        ];
    }

    private function persistToDatabase(array $data, TranslationService $translator): void
    {
        DB::transaction(function () use ($data, $translator) {
            $crop = Crop::firstOrCreate(
                ['name' => $data['crop']],
                [
                    'name_si'      => $data['crop_si'] ?? $translator->translate($data['crop'], 'si'),
                    'name_ta'      => $data['crop_ta'] ?? $translator->translate($data['crop'], 'ta'),
                    'category'     => 'vegetable',
                    'ideal_months' => range(1, 12)
                ]
            );

            $variety = CropVariety::updateOrCreate(
                ['crop_id' => $crop->id, 'variety_name' => $data['variety']],
                [
                    'variety_name_si'          => $data['variety_si'] ?? $translator->translate($data['variety'], 'si'),
                    'variety_name_ta'          => $data['variety_ta'] ?? $translator->translate($data['variety'], 'ta'),
                    'growth_days'              => $data['growth_days'],
                    'season'                   => 'both',
                    'soil_types'               => $data['suitable_soil_types'] ?? [$this->soilType],
                    'yield_per_acre_kg'        => $data['yield_per_acre_kg'] ?? 5000,
                    'seed_per_acre_kg'         => $data['seed_per_acre_kg'] ?? 2,
                    'base_market_price_per_kg' => $data['base_market_price_per_kg'] ?? 150,
                ]
            );

            $variety->stages()->delete();

            foreach ($data['stages'] as $s) {
                CropStage::create([
                    'crop_variety_id'   => $variety->id,
                    'name'              => $s['name'],
                    'name_si'           => $s['name_si'] ?? $translator->translate($s['name'], 'si'),
                    'name_ta'           => $s['name_ta'] ?? $translator->translate($s['name'], 'ta'),
                    'days_offset'       => $s['days_from_start'] ?? 0,
                    'icon'              => $s['icon'] ?? 'sprout',
                    'advice'            => $s['advice'],
                    'advice_si'         => $s['advice_si'] ?? $translator->translate($s['advice'], 'si'),
                    'advice_ta'         => $s['advice_ta'] ?? $translator->translate($s['advice'], 'ta'),
                    'description'       => $s['description'] ?? '',
                    'description_si'    => $translator->translate($s['description'] ?? '', 'si'),
                    'description_ta'    => $translator->translate($s['description'] ?? '', 'ta'),
                    'urea_per_acre_kg'  => $s['urea_kg'] ?? 0,
                    'tsp_per_acre_kg'   => $s['tsp_kg'] ?? 0,
                    'mop_per_acre_kg'   => $s['mop_kg'] ?? 0,
                ]);
            }
        });
    }

    private function convertToAcres(float $size, string $unit): float
    {
        return match ($unit) {
            'Hectares' => $size * 2.47105,
            'Perches'  => $size / 160,
            default    => $size,
        };
    }

    private function updateStatus(string $key, array $data): void
    {
        Cache::put($key, $data, now()->addHours(1));
    }

    private function completeJob(string $key, array $status, CropVariety $variety): void
    {
        $this->updateStatus($key, array_merge($status, [
            'status'     => 'completed',
            'crop_id'    => $variety->crop_id,
            'variety_id' => $variety->id
        ]));
    }

    private function completeJobWithData(string $key, array $status, array $roadmap): void
    {
        $this->updateStatus($key, array_merge($status, [
            'status'      => 'completed',
            'full_result' => $roadmap
        ]));
    }

    private function handleFailure(string $key, array $status, Throwable $e): void
    {
        Log::error("Roadmap pipeline failed", [
            'job_id' => $this->jobId,
            'error'  => $e->getMessage()
        ]);

        $this->updateStatus($key, array_merge($status, [
            'status' => 'failed',
            'error'  => $e->getMessage()
        ]));
    }
}
