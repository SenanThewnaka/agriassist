<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    /**
     * Store and sanitize multiple uploaded images.
     * @param array<UploadedFile> $files
     * @return string[] Array of stored paths
     */
    public function uploadMany(array $files): array
    {
        $paths = [];
        foreach ($files as $file) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            // Store on public disk (mapped to public/storage)
            $paths[] = $file->storeAs('diagnoses', $filename, 'public');
        }

        return $paths;
    }

    /**
     * Get the public URL for the image path.
     */
    public function getUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}