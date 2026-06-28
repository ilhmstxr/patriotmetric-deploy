<?php

namespace App\Filament\Resources\Penugasans\Pages;

use App\Filament\Resources\Penugasans\PenugasanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPenugasan extends ViewRecord
{
    protected static string $resource = PenugasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
