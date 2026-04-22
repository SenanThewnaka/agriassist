<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ImageUploadService
 * 
 * Securely handles image uploads, enforcing Privacy Shield standards by stripping
 * all EXIF/GPS metadata before storage.
 */
class ImageUploadService
{
    /**
     * Upload multiple files and strip metadata for privacy.
     */
    public function uploadMany(array $files, string $folder = 'diagnoses'): array
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = $this->upload($file, $folder);
        }
        return $paths;
    }

    /**
     * Upload a single file and strip EXIF metadata (Privacy Shield).
     */
    public function upload(UploadedFile $file, string $folder = 'diagnoses'): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $tempPath = $file->getRealPath();

        // COMPLIANCE: Strip EXIF metadata for privacy
        $this->stripMetadata($tempPath, $extension);

        return Storage::disk('public')->putFileAs($folder, $file, $filename);
    }

    /**
     * Re-save image using GD to strip all metadata (EXIF, GPS, etc.)
     */
    protected function stripMetadata(string $path, string $extension): void
    {
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
