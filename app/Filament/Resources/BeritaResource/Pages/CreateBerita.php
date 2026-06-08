<?php

namespace App\Filament\Resources\BeritaResource\Pages;

use App\Filament\Resources\BeritaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateBerita extends CreateRecord
{
    protected static string $resource = BeritaResource::class;

    /**
     * Setelah record berita berhasil dibuat, pindahkan gambar
     * dari direktori temp (berita/temp) ke direktori final (berita/{id}).
     * Lakukan hal yang sama untuk gambar yang ada di dalam konten (rich editor).
     */
    protected function afterCreate(): void
    {
        $record = $this->record;

        // ── 1. Pindahkan gambar utama (thumbnail) ──────────────────────────────
        if ($record->gambar && str_starts_with($record->gambar, 'berita/temp/')) {
            $oldPath  = $record->gambar;
            $filename = basename($oldPath);
            $newPath  = "berita/{$record->id}/{$filename}";

            if (Storage::disk('cms')->exists($oldPath)) {
                Storage::disk('cms')->move($oldPath, $newPath);
                $record->update(['gambar' => $newPath]);
            }
        }

        // ── 2. Pindahkan gambar di dalam konten (rich editor attachments) ───────
        if ($record->konten && str_contains($record->konten, 'berita/temp/')) {
            $kontenBaru = $record->konten;
            $files = Storage::disk('cms')->files('berita/temp');

            foreach ($files as $filePath) {
                $filename = basename($filePath);
                $newPath  = "berita/{$record->id}/{$filename}";

                if (str_contains($kontenBaru, $filename)) {
                    Storage::disk('cms')->move($filePath, $newPath);
                    $kontenBaru = str_replace(
                        "berita/temp/{$filename}",
                        $newPath,
                        $kontenBaru
                    );
                }
            }

            if ($kontenBaru !== $record->konten) {
                $record->update(['konten' => $kontenBaru]);
            }
        }
    }
}
