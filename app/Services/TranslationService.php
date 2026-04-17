<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TranslationService
 * 
 * Orchestrates multi-tier translation strategies.
 * Prioritizes Google Cloud Translation API with AI-engine failover.
 */
class TranslationService
{
    private string $baseUrl;
    private ?string $googleKey;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.analysis.url', 'http://localhost:5056');
        $this->googleKey = config('services.google_translate.key');
    }

    /**
     * Translate text to target language with caching and fallback.
     */
    public function translate(string $text, string $targetLang = 'en'): string
    {
        if ($this->shouldSkipTranslation($text, $targetLang)) {
            return $text;
        }

        $cacheKey = 'translation_' . md5($text . $targetLang);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($text, $targetLang) {
            return $this->resolveTranslation($text, $targetLang);
        });
    }

    private function shouldSkipTranslation(string $text, string $lang): bool
    {
        return empty(trim($text)) || $lang === 'en';
    }

    private function resolveTranslation(string $text, string $lang): string
    {
        // Tier 1: Google Cloud Translation API
        if ($this->googleKey) {
            $googleResult = $this->callGoogleTranslate($text, $lang);
            if ($googleResult) {
                return $googleResult;
            }
        }

        // Tier 2: Local AI Engine Fallback
        return $this->callAiFallback($text, $lang) ?? $text;
    }

    private function callGoogleTranslate(string $text, string $lang): ?string
    {
        try {
            $response = Http::post("https://translation.googleapis.com/language/translate/v2?key={$this->googleKey}", [
                'q' => [$text],
                'target' => $lang
            ]);

            if ($response->successful()) {
                $data = $response->json('data.translations.0');
                return isset($data['translatedText']) ? html_entity_decode($data['translatedText']) : null;
            }
        } catch (\Exception $e) {
            Log::error("Google Translate API failed", ['message' => $e->getMessage()]);
        }

        return null;
    }

    private function callAiFallback(string $text, string $lang): ?string
    {
        try {
            $response = Http::post("{$this->baseUrl}/translate", [
                'text' => $text,
                'lang' => $lang
            ]);

            return $response->successful() ? $response->json('translated') : null;
        } catch (\Exception $e) {
            Log::error("AI Translation fallback failed", ['message' => $e->getMessage()]);
        }

        return null;
    }
}
