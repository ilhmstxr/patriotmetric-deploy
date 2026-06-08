<?php
use App\Models\Berita;
use Illuminate\Support\Facades\Storage;

echo "--- CHECKING BERITA IMAGES ---\n";
foreach (Berita::orderBy('id', 'desc')->take(10)->get() as $b) {
    echo "ID: " . $b->id . "\n";
    echo "Title: " . $b->judul . "\n";
    echo "Gambar DB Path: " . $b->gambar . "\n";
    
    if ($b->gambar) {
        $exists = Storage::disk('cms')->exists($b->gambar);
        echo "Exists on disk (exact path)? " . ($exists ? "YES" : "NO") . "\n";
        
        $gambarPath = str_starts_with($b->gambar, 'assets/') ? substr($b->gambar, 7) : $b->gambar;
        $existsStrip = Storage::disk('cms')->exists($gambarPath);
        echo "Exists on disk (stripped path)? " . ($existsStrip ? "YES" : "NO") . "\n";
        
        echo "URL: " . Storage::disk('cms')->url($gambarPath) . "\n";
    } else {
        echo "No image.\n";
    }
    echo "---------------------------\n";
}
