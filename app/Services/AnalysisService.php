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
     * Send multiple images to the analysis engine for processing.
     * @param string[] $imagePaths
     */
    public function predictMany(array $imagePaths): array
    {
        try {
            $request = Http::asMultipart();

            // Pass the current selected UI locale to the processing engine
            $request->attach('lang', app()->getLocale());

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
}