<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Berita extends Model
{
    protected $table = 'beritas';

    protected $fillable = [
        'judul',
        'slug',
        'excerpt',
        'konten',
        'gambar',
        'tanggal',
        'is_published',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public static function booted(): void
    {
        static::creating(function (Berita $berita) {
            if (empty($berita->slug)) {
                $berita->slug = Str::slug($berita->judul) . '-' . Str::random(5);
            }
        });

        static::saving(function (Berita $berita) {
            if (empty($berita->excerpt) && !empty($berita->konten)) {
                $berita->excerpt = Str::limit(strip_tags($berita->konten), 200);
            }
        });

        static::created(function (Berita $berita) {
            // 1. Move the main header image
            if ($berita->gambar && str_starts_with($berita->gambar, 'berita/temp/')) {
                $oldPath = $berita->gambar;
                $filename = basename($oldPath);
                $newPath = "berita/{$berita->id}/{$filename}";

                if (Storage::disk('cms')->exists($oldPath)) {
                    Storage::disk('cms')->makeDirectory("berita/{$berita->id}");
                    Storage::disk('cms')->move($oldPath, $newPath);
                    $berita->gambar = $newPath;
                }
            }

            // 2. Scan and translate RichEditor file attachments from berita/temp to berita/{id}
            if ($berita->konten) {
                $content = $berita->konten;
                if (preg_match_all('/berita\/temp\/([a-zA-Z0-9_.-]+)/', $content, $matches)) {
                    $filenames = array_unique($matches[1]);
                    $folder = "berita/{$berita->id}";

                    foreach ($filenames as $filename) {
                        $oldTempPath = "berita/temp/{$filename}";
                        $newPath = "{$folder}/{$filename}";

                        if (Storage::disk('cms')->exists($oldTempPath)) {
                            Storage::disk('cms')->makeDirectory($folder);
                            Storage::disk('cms')->move($oldTempPath, $newPath);
                            
                            $content = str_replace("berita/temp/{$filename}", "{$folder}/{$filename}", $content);
                        }
                    }
                    $berita->konten = $content;
                }
            }

            if ($berita->isDirty()) {
                $berita->saveQuietly();
            }
        });

        static::updating(function (Berita $berita) {
            // Clean up main header image
            if ($berita->isDirty('gambar')) {
                $oldGambar = $berita->getOriginal('gambar');
                if ($oldGambar && Storage::disk('cms')->exists($oldGambar)) {
                    Storage::disk('cms')->delete($oldGambar);
                }
            }

            // Clean up removed RichEditor attachments
            if ($berita->isDirty('konten')) {
                $oldContent = $berita->getOriginal('konten');
                $newContent = $berita->konten;

                // Find images in old content matching berita/{id}/filename
                $pattern = '/berita\/' . $berita->id . '\/([a-zA-Z0-9_.-]+)/';
                if ($oldContent && preg_match_all($pattern, $oldContent, $oldMatches)) {
                    $oldImages = array_unique($oldMatches[1]);

                    // Find images in new content
                    preg_match_all($pattern, $newContent ?? '', $newMatches);
                    $newImages = array_unique($newMatches[1] ?? []);

                    // Identify deleted images
                    $deletedImages = array_diff($oldImages, $newImages);

                    foreach ($deletedImages as $filename) {
                        $filePath = "berita/{$berita->id}/{$filename}";
                        if (Storage::disk('cms')->exists($filePath)) {
                            Storage::disk('cms')->delete($filePath);
                        }
                    }
                }
            }
        });

        static::deleted(function (Berita $berita) {
            if ($berita->gambar && Storage::disk('cms')->exists($berita->gambar)) {
                Storage::disk('cms')->delete($berita->gambar);
            }
            $dir = "berita/{$berita->id}";
            if (Storage::disk('cms')->exists($dir)) {
                Storage::disk('cms')->deleteDirectory($dir);
            }
        });
    }
}
