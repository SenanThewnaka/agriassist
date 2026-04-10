<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AnalysisService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = (string)config('services.analysis.url', 'http://localhost:5055');
    }

    /**
     * Send multiple images to the analysis engine for processing with optional context.
     * @param string[] $imagePaths
     */
    public function predictMany(array $imagePaths, array $context = []): array
    {
        try {
            $request = Http::asMultipart();

            // Pass locale and JSON context
            $request->attach('lang', app()->getLocale());
            if (!empty($context)) {
                $request->attach('context', json_encode($context));
            }

            foreach ($imagePaths as $index => $path) {
                $imageFile = Storage::disk('public')->path($path);
                $request->attach(
                    "images[]", file_get_contents($imageFile), basename($imageFile)
                );
            }

            $response = $request->post("{$this->baseUrl}/predict?lang=" . app()->getLocale());

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("Analysis Engine Error: " . $response->body());
        }
        catch (\Exception $e) {
            Log::error("Analysis Engine Exception: " . $e->getMessage());
        }

        return [
            'error' => true,
            'message' => 'The analysis engine is temporarily offline. Please try again later.'
        ];
    }

    /**
     * Translate text using the AI engine.
     */
    public function translateText(string $text, string $targetLang = 'en'): string
    {
        if (empty($text) || $targetLang === 'en') {
            return $text;
        }

        $cacheKey = 'translation_' . md5($text . $targetLang);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addDays(7), function () use ($text, $targetLang) {
            try {
                $prompt = "Translate this agricultural text or status to ";
                $prompt .= ($targetLang === 'si' ? 'Sinhala' : ($targetLang === 'ta' ? 'Tamil' : 'English'));
                $prompt .= ". Return ONLY the translated text: " . $text;

                $response = Http::post("{$this->baseUrl}/translate", [
                    'text' => $prompt,
                    'lang' => $targetLang
                ]);

                if ($response->successful()) {
                    return $response->json('translated') ?? $text;
                }
            } catch (\Exception $e) {
                Log::error("Translation Engine Exception: " . $e->getMessage());
            }

            return $text;
        });
    }

    /**
     * Request a cultivation roadmap for a custom crop from the AI engine.
     */
    public function generateCropPlan(string $cropName, string $locale = 'en', ?string $varietyName = null): array
    {
        try {
            $varietyName = $varietyName ?? 'Local Variety';
            $prompt = "Generate a professional cultivation roadmap for a crop variety in Sri Lanka.\n";
            $prompt .= "Crop: {$cropName}\nVariety: {$varietyName}\n\n";
            $prompt .= "Return ONLY a JSON object with this exact structure:\n";
            $prompt .= "{\n";
            $prompt .= "  \"crop\": \"{$cropName}\",\n";
            $prompt .= "  \"variety\": \"{$varietyName}\",\n";
            $prompt .= "  \"growth_days\": 90,\n";
            $prompt .= "  \"yield_per_acre_kg\": 5000,\n";
            $prompt .= "  \"seed_per_acre_kg\": 5,\n";
            $prompt .= "  \"base_market_price_per_kg\": 150,\n";
            $prompt .= "  \"stages\": [\n";
            $prompt .= "    { \"name\": \"Stage Name\", \"days_from_start\": 0, \"icon\": \"sprout\", \"advice\": \"Short advice\", \"description\": \"Detailed instructions\" }\n";
            $prompt .= "  ]\n";
            $prompt .= "}\n";
            $prompt .= "Include 5-7 logical growth stages. Icons must be Lucide-compatible (e.g., tractor, sprout, droplets, flower, sun, shopping-basket).";

            $response = Http::post("{$this->baseUrl}/generate-plan", [
                'prompt' => $prompt,
                'lang' => $locale
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => true, 'message' => 'AI engine returned an error.'];
        }
        catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }
}