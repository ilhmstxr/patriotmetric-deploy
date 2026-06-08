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
        'tanggal'      => 'date',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public static function booted(): void
    {
        // ── Auto-generate slug & excerpt before saving ─────────────────────────
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

        // ── After create: move files from berita/temp → berita/{id} ───────────
        static::created(function (Berita $berita) {
            $needsSave = false;

            // 1. Move main header image
            if ($berita->gambar && str_starts_with($berita->gambar, 'berita/temp/')) {
                $oldPath  = $berita->gambar;
                $filename = basename($oldPath);
                $newPath  = "berita/{$berita->id}/{$filename}";

                if (Storage::disk('cms')->exists($oldPath)) {
                    Storage::disk('cms')->makeDirectory("berita/{$berita->id}");
                    Storage::disk('cms')->move($oldPath, $newPath);
                    // Update model attribute directly, not via save() yet
                    $berita->setAttribute('gambar', $newPath);
                    $needsSave = true;
                }
            }

            // 2. Move RichEditor attachments embedded in content
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

            // Save once with both updates using DB to avoid observer loop
            if ($needsSave) {
                \Illuminate\Support\Facades\DB::table('beritas')
                    ->where('id', $berita->id)
                    ->update([
                        'gambar' => $berita->gambar,
                        'konten' => $berita->konten,
                    ]);
            }
        });

        // ── Before update: clean up old/removed files ──────────────────────────
        static::updating(function (Berita $berita) {
            // Delete old main image if replaced
            if ($berita->isDirty('gambar')) {
                $oldGambar = $berita->getOriginal('gambar');
                if ($oldGambar && Storage::disk('cms')->exists($oldGambar)) {
                    Storage::disk('cms')->delete($oldGambar);
                }
            }

            // Delete removed rich editor attachments
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

        // ── After delete: remove all associated files ──────────────────────────
        static::deleted(function (Berita $berita) {
            $dir = "berita/{$berita->id}";
            if (Storage::disk('cms')->exists($dir)) {
                Storage::disk('cms')->deleteDirectory($dir);
            }
        });
    }
}
