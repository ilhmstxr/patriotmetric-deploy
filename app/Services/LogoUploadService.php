<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class LogoUploadService
{
    /**
     * Upload logo (image) and convert to WebP.
     *
     * @param UploadedFile $logo
     * @param string $directoryPath
     * @return string
     */
    public function uploadAndConvert(UploadedFile $logo, string $directoryPath): string
    {
        $extension = strtolower($logo->getClientOriginalExtension());
        $logoName = time() . '_logo.webp';
        
        $sourcePath = $logo->getRealPath();
        $image = null;
        
        if ($extension === 'jpeg' || $extension === 'jpg') {
            $image = @imagecreatefromjpeg($sourcePath);
        } elseif ($extension === 'png') {
            $image = @imagecreatefrompng($sourcePath);
            if ($image) {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }
        }
        
        $logoPath = $directoryPath . '/' . $logoName;
        $absolutePath = storage_path('app/public/' . $directoryPath);
        
        if (!file_exists($absolutePath)) {
            mkdir($absolutePath, 0755, true);
        }
        
        if ($image && function_exists('imagewebp')) {
            imagewebp($image, storage_path('app/public/' . $logoPath), 80);
            imagedestroy($image);
            return '/storage/' . $logoPath;
        } else {
            // Fallback to original
            $fallbackName = time() . '_logo.' . $extension;
            $fallbackPath = $logo->storeAs($directoryPath, $fallbackName, 'public');
            return '/storage/' . $fallbackPath;
        }
    }
}
