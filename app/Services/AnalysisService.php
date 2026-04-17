<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AnalysisService
 * 
 * Orchestrates communication with the high-performance Python analysis engine.
 * Handles AI-driven cultivation planning, soil analysis, and image processing.
 */
class AnalysisService
{
    public const STATUS_KEY = 'crop_planner_status:%s';
    public const GENERATION_LOCK_KEY = 'crop_gen_lock:%s:%s';

    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.analysis.url', 'http://localhost:5056');
    }

    /**
     * Generate a complete cultivation plan with automatic retry logic for transient engine failures.
     */
    public function generateCropPlanWithRetries(string $crop, string $locale = 'en', ?string $variety = null): array
    {
        return retry(3, fn() => $this->generateCropPlan($crop, $locale, $variety), 2000);
    }

    /**
     * Request a cultivation roadmap for a specific crop and variety from the AI engine.
     */
    public function generateCropPlan(string $cropName, string $locale = 'en', ?string $varietyName = null): array
    {
        try {
            $response = Http::timeout(120)->post("{$this->baseUrl}/generate-plan", [
                'prompt' => $this->buildGenerationPrompt($cropName, $varietyName ?? 'Local Variety'),
                'lang'   => $locale
            ]);

            if ($response->successful()) {
                return $this->validateEngineResponse($response->json());
            }

            if ($response->status() === 422) {
                return $response->json();
            }

            throw new \RuntimeException("AI engine returned non-successful status: " . $response->status());

        } catch (\Exception $e) {
            Log::error("Crop Plan Engine Communication Failure", ['error' => $e->getMessage()]);
            throw new \RuntimeException("Intelligence engine unreachable or returned an invalid payload.");
        }
    }

    /**
     * Build a high-precision prompt to ensure strictly formatted JSON roadmaps.
     */
    private function buildGenerationPrompt(string $crop, string $variety): string
    {
        return <<<PROMPT
            First, verify if '{$crop}' is a legitimate agricultural crop. 
            If invalid, return: {"error": true, "message": "Invalid crop requested."}.
            
            Otherwise, generate a professional cultivation roadmap for Sri Lanka.
            Crop: {$crop}
            Variety: {$variety}

            Response MUST be a JSON object with this structure:
            {
              "crop": "{$crop}",
              "crop_si": "Sinhala Name",
              "crop_ta": "Tamil Name",
              "variety": "{$variety}",
              "variety_si": "Sinhala Name",
              "variety_ta": "Tamil Name",
              "growth_days": 90,
              "yield_per_acre_kg": 5000,
              "seed_per_acre_kg": 5,
              "base_market_price_per_kg": 150,
              "stages": [
                {
                  "name": "Stage Name",
                  "days_from_start": 0,
                  "icon": "sprout",
                  "advice": "Short professional advice",
                  "description": "Detailed instructions",
                  "urea_kg": 0, "tsp_kg": 0, "mop_kg": 0
                }
              ]
            }
            Include 5-7 growth stages. Ensure fertilizer amounts are provided.
            PROMPT;
    }

    private function validateEngineResponse(array $data): array
    {
        if (!isset($data['crop'], $data['stages'])) {
            throw new \RuntimeException("Incomplete payload received from intelligence engine.");
        }
        return $data;
    }
}
