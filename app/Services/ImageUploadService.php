<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    /**
     * Upload multiple files and strip metadata for privacy.
     */
    public function uploadMany(array $files): array
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = $this->upload($file);
        }
        return $paths;
    }

    /**
     * Upload a single file and strip EXIF metadata (Privacy Shield).
     */
    public function upload(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $tempPath = $file->getRealPath();

        // COMPLIANCE: Strip EXIF metadata if tools are available
        $this->stripMetadata($tempPath, $extension);

        return Storage::disk('public')->putFileAs('diagnoses', $file, $filename);
    }

    /**
     * Re-save image using GD to strip all metadata (EXIF, GPS, etc.)
     */
    protected function stripMetadata(string $path, string $extension): void
    {
        // Safety check: Ensure GD extension is installed
        if (!function_exists('imagecreatefromjpeg') && !function_exists('imagecreatefrompng')) {
            \Illuminate\Support\Facades\Log::info("Privacy Shield: GD extension not found. Skipping metadata stripping.");
            return;
        }

        try {
            $img = null;
            $ext = strtolower($extension);

            if (($ext === 'jpg' || $ext === 'jpeg') && function_exists('imagecreatefromjpeg')) {
                $img = @imagecreatefromjpeg($path);
                if ($img) {
                    imagejpeg($img, $path, 90); 
                }
            } elseif ($ext === 'png' && function_exists('imagecreatefrompng')) {
                $img = @imagecreatefrompng($path);
                if ($img) {
                    imagealphablending($img, false);
                    imagesavealpha($img, true);
                    imagepng($img, $path);
                }
            }

            if ($img) {
                imagedestroy($img);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Metadata stripping failed for {$path}: " . $e->getMessage());
        }
    }
}
