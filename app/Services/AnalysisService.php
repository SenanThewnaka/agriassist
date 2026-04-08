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
     * Translate a given text to a target language using the AI engine.
     */
    public function translateText(string $text, string $targetLang = 'en'): string
    {
        try {
            $response = Http::post("{$this->baseUrl}/translate", [
                'text' => $text,
                'lang' => $targetLang
            ]);

            if ($response->successful()) {
                return $response->json('translated') ?? $text;
            }
        }
        catch (\Exception $e) {
            Log::error("Translation Engine Exception: " . $e->getMessage());
        }

        return $text;
    }

    /**
     * Request a cultivation roadmap for a custom crop from the AI engine.
     */
    public function generateCropPlan(string $cropName, string $locale = 'en', ?string $varietyName = null): array
    {
        try {
            $response = Http::post("{$this->baseUrl}/generate-plan", [
                'crop_name' => $cropName,
                'variety_name' => $varietyName,
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