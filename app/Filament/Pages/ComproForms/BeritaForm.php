<?php

namespace App\Filament\Pages\ComproForms;

use App\Filament\Pages\ComproForms\Concerns\WebpFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class BeritaForm
{
    use WebpFileUpload;

    public static function schema(): array
    {
        return [
            Section::make('Hero')
                ->schema([
                    TextInput::make('hero.judul')->label('Judul')->maxLength(255)->required(),
                    Textarea::make('hero.deskripsi')->label('Deskripsi')->maxLength(500)->required(),
                    self::makeImageUpload('hero.background_image')
                        ->label('Background Image'),
                ]),
        ];
    }
}
