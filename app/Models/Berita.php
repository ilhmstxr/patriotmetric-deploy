<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        'tanggal'      => 'date',
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

        // ── Helper func for moving temp files ──────────────────────────────
        $moveTempFiles = function (Berita $berita) {
            $needsSave = false;

            // 1. Move main header image
            if ($berita->gambar && str_starts_with($berita->gambar, 'berita/temp/')) {
                $oldPath  = $berita->gambar;
                $filename = basename($oldPath);
                $newPath  = "berita/{$berita->id}/{$filename}";

                if (Storage::disk('cms')->exists($oldPath)) {
                    Storage::disk('cms')->makeDirectory("berita/{$berita->id}");
                    Storage::disk('cms')->move($oldPath, $newPath);
                    $berita->setAttribute('gambar', $newPath);
                    $needsSave = true;
                }
            }

            // 2. Move RichEditor attachments
            if ($berita->konten && str_contains($berita->konten, 'berita/temp/')) {
                $content = $berita->konten;
                $folder  = "berita/{$berita->id}";

                if (preg_match_all('/berita\/temp\/([a-zA-Z0-9_.%-]+)/', $content, $matches)) {
                    foreach (array_unique($matches[1]) as $filename) {
                        $oldTempPath = "berita/temp/{$filename}";
                        $newPath     = "{$folder}/{$filename}";

                        if (Storage::disk('cms')->exists($oldTempPath)) {
                            Storage::disk('cms')->makeDirectory($folder);
                            Storage::disk('cms')->move($oldTempPath, $newPath);
                            $content = str_replace("berita/temp/{$filename}", $newPath, $content);
                        }
                    }
                }

                if ($content !== $berita->getOriginal('konten')) {
                    $berita->setAttribute('konten', $content);
                    $needsSave = true;
                }
            }

            if ($needsSave) {
                DB::table('beritas')
                    ->where('id', $berita->id)
                    ->update([
                        'gambar' => $berita->gambar,
                        'konten' => $berita->konten,
                    ]);
            }
        };

        static::created(function (Berita $berita) use ($moveTempFiles) {
            $moveTempFiles($berita);
        });

        static::updated(function (Berita $berita) use ($moveTempFiles) {
            $moveTempFiles($berita);
        });

        static::updating(function (Berita $berita) {
            if ($berita->isDirty('gambar')) {
                $oldGambar = $berita->getOriginal('gambar');
                if ($oldGambar && Storage::disk('cms')->exists($oldGambar) && !str_starts_with($oldGambar, 'berita/temp/')) {
                    Storage::disk('cms')->delete($oldGambar);
                }
            }

            if ($berita->isDirty('konten')) {
                $oldContent = $berita->getOriginal('konten');
                $newContent = $berita->konten;
                $pattern    = '/berita\/' . $berita->id . '\/([a-zA-Z0-9_.%-]+)/';

                if ($oldContent && preg_match_all($pattern, $oldContent, $oldMatches)) {
                    $oldImages = array_unique($oldMatches[1]);
                    preg_match_all($pattern, $newContent ?? '', $newMatches);
                    $newImages = array_unique($newMatches[1] ?? []);

                    foreach (array_diff($oldImages, $newImages) as $filename) {
                        $filePath = "berita/{$berita->id}/{$filename}";
                        if (Storage::disk('cms')->exists($filePath)) {
                            Storage::disk('cms')->delete($filePath);
                        }
                    }
                }
            }
        });

        static::deleted(function (Berita $berita) {
            $dir = "berita/{$berita->id}";
            if (Storage::disk('cms')->exists($dir)) {
                Storage::disk('cms')->deleteDirectory($dir);
            }
        });
    }
}
