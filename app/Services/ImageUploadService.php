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

        // COMPLIANCE: Strip EXIF metadata using PHP GD to prevent data leakage to AI providers
        $this->stripMetadata($tempPath, $extension);

        return Storage::disk('public')->putFileAs('diagnoses', $file, $filename);
    }

    /**
     * Re-save image using GD to strip all metadata (EXIF, GPS, etc.)
     */
    protected function stripMetadata(string $path, string $extension): void
    {
        try {
            $img = null;
            $ext = strtolower($extension);

            if ($ext === 'jpg' || $ext === 'jpeg') {
                $img = @imagecreatefromjpeg($path);
                if ($img) {
                    imagejpeg($img, $path, 90); // 90% quality
                }
            } elseif ($ext === 'png') {
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
            // If stripping fails, we still allow the upload but log the warning
            \Illuminate\Support\Facades\Log::warning("Metadata stripping failed for {$path}: " . $e->getMessage());
        }
    }
}
