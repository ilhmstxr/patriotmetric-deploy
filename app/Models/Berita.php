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

        static::updating(function (Berita $berita) {
            if ($berita->isDirty('gambar')) {
                $oldGambar = $berita->getOriginal('gambar');
                // Hapus gambar lama jika ada dan bukan dari temp
                if ($oldGambar && Storage::disk('cms')->exists($oldGambar) && !str_starts_with($oldGambar, 'berita/temp/')) {
                    Storage::disk('cms')->delete($oldGambar);
                }
            }

            if ($berita->isDirty('konten')) {
                $oldContent = $berita->getOriginal('konten');
                $newContent = $berita->konten;
                // Ekstrak semua nama file di dalam tag gambar
                $pattern    = '/berita\/([a-zA-Z0-9_.%-]+)/';

                if ($oldContent && preg_match_all($pattern, $oldContent, $oldMatches)) {
                    $oldImages = array_unique($oldMatches[1]);
                    preg_match_all($pattern, $newContent ?? '', $newMatches);
                    $newImages = array_unique($newMatches[1] ?? []);

                    foreach (array_diff($oldImages, $newImages) as $filename) {
                        $filePath = "berita/{$filename}";
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
