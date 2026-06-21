<?php

namespace App\Filament\Pages\ComproForms\Concerns;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait WebpFileUpload
{
    public static function makeImageUpload(string $name): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->disk('cms')
            ->directory('images')
            ->visibility('public')
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(5120)
            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                $image = self::createImageFromFile($file->getRealPath());

                $filename = uniqid('compro_') . '.webp';
                $path = 'images/' . $filename;

                $tempPath = sys_get_temp_dir() . '/' . $filename;
                \imagewebp($image, $tempPath, config('image.webp_quality', 80));
                \imagedestroy($image);

                Storage::disk('cms')->put($path, file_get_contents($tempPath));
                unlink($tempPath);

                return $path;
            });
    }

    private static function createImageFromFile(string $path)
    {
        $mimeType = mime_content_type($path);

        switch ($mimeType) {
            case 'image/jpeg':
                $img = \imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $img = \imagecreatefrompng($path);
                break;
            case 'image/gif':
                $img = \imagecreatefromgif($path);
                break;
            case 'image/webp':
                $img = \imagecreatefromwebp($path);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported image format: {$mimeType}");
        }

        // Convert palette/indexed images to truecolor (required for WebP output)
        if (\imageistruecolor($img) === false) {
            $truecolor = \imagecreatetruecolor(\imagesx($img), \imagesy($img));
            \imagealphablending($truecolor, false);
            \imagesavealpha($truecolor, true);
            $transparent = \imagecolorallocatealpha($truecolor, 0, 0, 0, 127);
            \imagefill($truecolor, 0, 0, $transparent);
            \imagecopy($truecolor, $img, 0, 0, 0, 0, \imagesx($img), \imagesy($img));
            \imagedestroy($img);
            $img = $truecolor;
        }

        return $img;
    }
}
