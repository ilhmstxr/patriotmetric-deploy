<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FixBeritaGambar extends Command
{
    protected $signature   = 'berita:fix-gambar {--dry-run : Tampilkan perubahan tanpa eksekusi}';
    protected $description = 'Pindahkan gambar berita dari berita/temp ke berita/{id} dan update path di DB';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $disk   = Storage::disk('cms');

        $beritas = DB::table('beritas')->get(['id', 'judul', 'gambar', 'konten']);
        $fixed   = 0;

        foreach ($beritas as $berita) {
            $gambarUpdate  = null;
            $kontenUpdate  = null;

            // ── Fix gambar utama yang masih di berita/temp/ ──────────────────
            if ($berita->gambar && str_starts_with($berita->gambar, 'berita/temp/')) {
                $filename = basename($berita->gambar);
                $newPath  = "berita/{$berita->id}/{$filename}";

                if ($disk->exists($berita->gambar)) {
                    if (!$dryRun) {
                        $disk->makeDirectory("berita/{$berita->id}");
                        $disk->move($berita->gambar, $newPath);
                    }
                    $gambarUpdate = $newPath;
                    $this->line("[ID:{$berita->id}] gambar: {$berita->gambar} → {$newPath}");
                } else {
                    $this->warn("[ID:{$berita->id}] File tidak ditemukan: {$berita->gambar}");
                    // Hapus path yang tidak valid dari DB
                    if (!$dryRun) $gambarUpdate = null;
                }
            }

            // ── Fix gambar di dalam konten ───────────────────────────────────
            if ($berita->konten && str_contains($berita->konten, 'berita/temp/')) {
                $content = $berita->konten;
                $folder  = "berita/{$berita->id}";

                if (preg_match_all('/berita\/temp\/([a-zA-Z0-9_.%-]+)/', $content, $matches)) {
                    foreach (array_unique($matches[1]) as $fn) {
                        $oldPath = "berita/temp/{$fn}";
                        $newPath = "{$folder}/{$fn}";

                        if ($disk->exists($oldPath)) {
                            if (!$dryRun) {
                                $disk->makeDirectory($folder);
                                $disk->move($oldPath, $newPath);
                            }
                            $content = str_replace("berita/temp/{$fn}", $newPath, $content);
                            $this->line("[ID:{$berita->id}] konten img: {$fn} → {$newPath}");
                        }
                    }
                }

                if ($content !== $berita->konten) {
                    $kontenUpdate = $content;
                }
            }

            // ── Update DB ────────────────────────────────────────────────────
            if ($gambarUpdate !== null || $kontenUpdate !== null) {
                $update = [];
                if ($gambarUpdate !== null) $update['gambar'] = $gambarUpdate;
                if ($kontenUpdate  !== null) $update['konten'] = $kontenUpdate;

                if (!$dryRun) {
                    DB::table('beritas')->where('id', $berita->id)->update($update);
                }
                $fixed++;
            }
        }

        $mode = $dryRun ? ' [DRY RUN]' : '';
        $this->info("\n✅{$mode} Selesai. {$fixed} berita diupdate.");

        return self::SUCCESS;
    }
}
