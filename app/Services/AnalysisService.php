<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service: AnalysisService
 * 
 * Orchestrates communication with the high-performance Python analysis engine.
 * Handles AI-driven cultivation planning, soil telemetry analysis, and 
 * computer vision-based biological risk assessment.
 */
class AnalysisService
{
    public const STATUS_KEY = 'crop_planner_status:%s';
    public const GENERATION_LOCK_KEY = 'crop_gen_lock:%s:%s';

    private readonly string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.analysis.url', 'http://localhost:5056');
    }

    /**
     * Triggers asynchronous cultivation roadmap generation with retry logic.
     * 
     * @param string $crop
     * @param string $locale
     * @param string|null $variety
     * @return array<string, mixed>
     * @throws \RuntimeException If the engine fails to produce a valid payload.
     */
    public function generateCropPlanWithRetries(string $crop, string $locale = 'en', ?string $variety = null): array
    {
        return retry(3, fn() => $this->generateCropPlan($crop, $locale, $variety), 2000);
    }

    /**
     * Executes the primary roadmap generation request.
     * 
     * @param string $cropName
     * @param string $locale
     * @param string|null $varietyName
     * @return array<string, mixed>
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

            throw new \RuntimeException("Intelligence engine returned non-terminal failure: " . $response->status());

        } catch (\Exception $e) {
            Log::error("Intelligence Engine Communication Failure", [
                'endpoint'  => '/generate-plan',
                'exception' => $e->getMessage()
            ]);
            throw new \RuntimeException("Intelligence engine unreachable or returned an invalid payload.");
        }
    }

    /**
     * Constructs a high-precision multi-stage prompt for the roadmap engine.
     * 
     * @param string $crop
     * @param string $variety
     * @return string
     */
    private function buildGenerationPrompt(string $crop, string $variety): string
    {
        return <<<PROMPT
            You are a Senior Agricultural Scientist in Sri Lanka.
            First, verify if '{$crop}' is a legitimate agricultural crop. 
            If invalid, return: {"error": true, "message": "Invalid crop requested."}.
            
            Otherwise, generate a HIGHLY DETAILED, granular cultivation roadmap for Sri Lanka.
            Crop: {$crop}
            Variety: {$variety}

            CRITICAL INSTRUCTIONS:
            - Granularity: Provide 8-12 distinct growth stages (approx. weekly or bi-weekly).
            - Stage Content: Each stage MUST have comprehensive advice and specific instructions.
            - Fertilizer: Include specific dosage for Urea, TSP, and MOP. ALSO include an 'organic_alternative' (e.g., compost, neem) in the description.
            - Water Management: In the 'advice', specify if the plant needs heavy, moderate, or light watering during that specific week.
            - Pest Control: Explicitly mention which pests or diseases to monitor for in each specific stage.

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
                  "name": "Stage Name (e.g., Week 1: Seedling Care)",
                  "days_from_start": 0,
                  "icon": "sprout",
                  "advice": "Extensive professional advice (min 20 words)",
                  "description": "Step-by-step detailed instructions including organic options and water management (min 40 words)",
                  "urea_kg": 0, "tsp_kg": 0, "mop_kg": 0
                }
              ]
            }
            Return ONLY the raw JSON. No markdown, no pre-text.
            PROMPT;
    }

    /**
     * Validates that the engine payload adheres to the required contract.
     * 
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function validateEngineResponse(array $data): array
    {
        if (!isset($data['crop'], $data['stages'])) {
            throw new \RuntimeException("Incomplete payload received from intelligence engine.");
        }
        return $data;
    }

    /**
     * Extracts chemical and geological metrics from a soil test report document.
     * 
     * @param array<int, \Illuminate\Http\UploadedFile> $imageFiles
     * @return array<string, mixed>
     */
    public function analyzeSoil(array $imageFiles): array
    {
        try {
            $request = Http::timeout(60)->asMultipart();
            
            foreach ($imageFiles as $file) {
                $request->attach(
                    'images[]', 
                    file_get_contents($file->getRealPath()), 
                    $file->getClientOriginalName()
                );
            }

            $response = $request->post("{$this->baseUrl}/analyze-soil");

            if ($response->successful()) {
                return $response->json();
            }

            throw new \RuntimeException("Soil analysis failure: " . $response->status());
        } catch (\Exception $e) {
            Log::error("Soil Analysis Service Failure", ['exception' => $e->getMessage()]);
            throw new \RuntimeException("Soil analysis service unreachable.");
        }
    }

    /**
     * Predicts biological risk factors (pests/swarms) based on meteorological telemetry.
     * 
     * @param string $crop
     * @param array<int, array<string, mixed>> $weatherData
     * @param string $district
     * @return array<int, array<string, mixed>>
     */
    public function predictPests(string $crop, array $weatherData, string $district): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/predict-pests", [
                'crop'     => $crop,
                'weather'  => $weatherData,
                'district' => $district
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            Log::error("Pest Prediction Service Failure", ['exception' => $e->getMessage()]);
            return [];
        }
    }
}
