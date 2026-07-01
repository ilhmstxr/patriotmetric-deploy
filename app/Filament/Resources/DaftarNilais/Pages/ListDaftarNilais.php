<?php

namespace App\Filament\Resources\DaftarNilais\Pages;

use App\Filament\Resources\DaftarNilaiResource;
use Filament\Resources\Pages\ListRecords;

class ListDaftarNilais extends ListRecords
{
    protected static string $resource = DaftarNilaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Read-only list, no header actions needed.
        ];
    }
}
