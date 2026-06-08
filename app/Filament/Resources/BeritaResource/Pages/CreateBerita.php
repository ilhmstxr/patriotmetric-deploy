<?php

namespace App\Filament\Resources\BeritaResource\Pages;

use App\Filament\Resources\BeritaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBerita extends CreateRecord
{
    protected static string $resource = BeritaResource::class;
    // Logika pemindahan gambar dari temp ke folder final
    // sudah ditangani oleh Berita model observer (static::created hook)
    // di app/Models/Berita.php — tidak perlu duplikasi di sini.
}
