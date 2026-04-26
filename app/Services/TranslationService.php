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

    // Translate text to target language with caching and fallback.
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
        // Tier 1: Local AI Engine (Priority for Context & Professionalism)
        // AI understands agricultural terms like 'tillering' or 'panicle' better than standard APIs.
        try {
            $aiResult = $this->callAiFallback($text, $lang);
            if ($aiResult) {
                return $aiResult;
            }
        } catch (\Exception $e) {
            Log::warning("AI Translation Tier failed, falling back to Google", ['error' => $e->getMessage()]);
        }

        // Tier 2: Google Cloud Translation API (Fallback)
        if ($this->googleKey) {
            try {
                $googleResult = $this->callGoogleTranslate($text, $lang);
                if ($googleResult) {
                    return $googleResult;
                }
            } catch (\Exception $e) {
                Log::error("Google Translate Pipeline Failure", ['text' => substr($text, 0, 50)]);
            }
        }

        return $text;
    }

    private function callGoogleTranslate(string $text, string $lang): ?string
    {
        $response = Http::post("https://translation.googleapis.com/language/translate/v2?key={$this->googleKey}", [
            'q' => [$text],
            'target' => $lang
        ]);

        if ($response->successful()) {
            $data = $response->json('data.translations.0');
            return isset($data['translatedText']) ? html_entity_decode($data['translatedText'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
        }

        // Log specific failure reasons (like 429 Too Many Requests)
        Log::info("Google Translate API non-successful response", [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return null;
    }

    private function callAiFallback(string $text, string $lang): ?string
    {
        try {
            $response = Http::post("{$this->baseUrl}/translate", [
                'text' => $text,
                'lang' => $lang
            ]);

            if ($response->successful()) {
                return $response->json('translated');
            }
        } catch (\Exception $e) {
            Log::error("AI Translation fallback failed", ['message' => $e->getMessage()]);
        }

        return null;
    }
}
