<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Handles communication with the Python analysis engine for crop planning,
 * soil analysis, and risk assessment.
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
     * Generates a crop plan roadmap, with retries on failure.
     *
     * @param string $crop
     * @param string $locale
     * @param string|null $variety
     * @return array<string, mixed>
     */
    public function generateCropPlanWithRetries(string $crop, string $locale = 'en', ?string $variety = null): array
    {
        return retry(3, fn() => $this->generateCropPlan($crop, $locale, $variety), 2000);
    }

    /**
     * Performs generation request for a crop plan.
     *
     * @param string $cropName
     * @param string $locale
     * @param string|null $varietyName
     * @return array<string, mixed>
     */
    public function generateCropPlan(string $cropName, string $locale = 'en', ?string $varietyName = null): array
    {
        if (empty(trim($cropName))) {
            throw new \RuntimeException("Crop name is required for roadmap generation.");
        }

        try {
            $response = Http::timeout(120)->post("{$this->baseUrl}/generate-plan", [
                'prompt' => $this->buildGenerationPrompt($cropName, $varietyName ?? 'Local Variety'),
                'lang'   => $locale
            ]);

            if ($response->successful()) {
                $data = $this->validateEngineResponse($response->json());
        // Log warning if the AI returns Rice when another crop was requested
                if (stripos($cropName, 'rice') === false && stripos($data['crop'], 'rice') !== false) {
                    Log::warning("AI Engine Hallucination Detected", [
                        'requested_crop' => $cropName,
                        'returned_crop' => $data['crop']
                    ]);
                }

                return $data;
            }

            if ($response->status() === 422) {
                return $response->json();
            }

            throw new \RuntimeException("Intelligence engine returned non-terminal failure: " . $response->status());

        } catch (\Exception $e) {
            Log::error("Intelligence Engine Communication Failure", [
                'endpoint'  => '/generate-plan',
                'crop'      => $cropName,
                'variety'   => $varietyName,
                'exception' => $e->getMessage()
            ]);
            throw new \RuntimeException("Intelligence engine unreachable or returned an invalid payload.");
        }
    }

    /**
     * Builds the prompt for the crop plan generation engine.
     *
     * @param string $crop
     * @param string $variety
     * @return string
     */
    private function buildGenerationPrompt(string $crop, string $variety): string
    {
        return <<<PROMPT
            You are a Senior Agricultural Scientist in Sri Lanka.
            TASK: Generate a granular cultivation roadmap for the specific crop and variety requested.
            
            CROP: '{$crop}'
            VARIETY: '{$variety}'

            CRITICAL: 
            1. Verify if '{$crop}' is a legitimate agricultural crop. If invalid, return ONLY: {"error": true, "message": "Invalid crop requested."}.
            2. You MUST generate data specifically for '{$crop}'. Do NOT return generic data or data for 'Rice' unless '{$crop}' is actually Rice.
            3. Localization: For EVERY field, provide the Sinhala and Tamil translations. DO NOT use placeholders like "Sinhala Name". Provide REAL translations.

            Response MUST be a JSON object with this structure (DO NOT use placeholder values, use REAL data for {$crop}):
            {
              "crop": "{$crop}",
              "crop_si": "Real Sinhala name for {$crop}",
              "crop_ta": "Real Tamil name for {$crop}",
              "variety": "{$variety}",
              "variety_si": "Real Sinhala name for {$variety}",
              "variety_ta": "Real Tamil name for {$variety}",
              "growth_days": (Integer total days),
              "yield_per_acre_kg": (Integer kg),
              "seed_per_acre_kg": (Float kg),
              "base_market_price_per_kg": (Integer LKR),
              "stages": [
                {
                  "name": "Stage name in English",
                  "name_si": "Stage name in Sinhala",
                  "name_ta": "Stage name in Tamil",
                  "days_from_start": (Integer),
                  "icon": "Lucide icon name (sprout, flower, droplets, sun, tractor, shopping-bag)",
                  "advice": "Extensive professional advice in English",
                  "advice_si": "Advice in Sinhala",
                  "advice_ta": "Advice in Tamil",
                  "description": "Detailed instructions in English including organic options",
                  "description_si": "Instructions in Sinhala",
                  "description_ta": "Instructions in Tamil",
                  "urea_kg": (Float), "tsp_kg": (Float), "mop_kg": (Float)
                }
              ]
            }
            Return ONLY the raw JSON. No markdown, no pre-text.
            PROMPT;
    }

    /**
     * Validates the structure and content of the AI engine's response.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function validateEngineResponse(array $data): array
    {
        if (isset($data['error']) && $data['error'] === true) {
            throw new \RuntimeException($data['message'] ?? "Intelligence engine rejected the request.");
        }

        if (!isset($data['crop'], $data['stages'])) {
            throw new \RuntimeException("Incomplete payload received from intelligence engine.");
        }
        return $data;
    }

    /**
     * Analyzes soil test reports from uploaded images.
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
     * Predicts pest risks based on weather conditions.
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

    // Fetch variety suggestions for a specific crop name based on soil type.
    public function suggestVarieties(string $cropName, string $soilType): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/suggest-varieties", [
                'crop_name' => $cropName,
                'soil_type' => $soilType
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            Log::error("Variety Suggestion Service Failure", ['exception' => $e->getMessage()]);
            return [];
        }
    }

    // Recommend an optimal planting date based on 14-day weather and crop requirements.
    public function recommendPlantingDate(string $crop, array $weather, string $soil): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/recommend-date", [
                'crop'    => $crop,
                'weather' => $weather,
                'soil'    => $soil
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['recommended_date' => now()->toDateString(), 'reason' => 'AI engine currently unavailable. using today.'];
        } catch (\Exception $e) {
            Log::error("Date Recommendation Service Failure", ['exception' => $e->getMessage()]);
            return ['recommended_date' => now()->toDateString(), 'reason' => 'Connection error with AI.'];
        }
    }
}