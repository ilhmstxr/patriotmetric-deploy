<?php

namespace App\Filament\Pages\ComproForms;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class PanduanForm
{
    use Concerns\WebpFileUpload;

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

            Section::make('Panduan Menjadi Peserta')
                ->schema([
                    Textarea::make('panduan.deskripsi')->label('Deskripsi Pengantar')->maxLength(1000)->required(),
                    Repeater::make('panduan.daftar')
                        ->label('Daftar Langkah')
                        ->schema([
                            TextInput::make('judul')->label('Judul Langkah')->maxLength(200)->required(),
                            Textarea::make('deskripsi')->label('Deskripsi')->maxLength(2000)->required(),
                            self::makeImageUpload('gambar')
                                ->label('Screenshot / Gambar')
                                ->required(),
                        ])
                        ->maxItems(50)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn(array $state) => $state['judul'] ?? 'Langkah Baru'),
                ]),

            Section::make('Pedoman')
                ->schema([
                    FileUpload::make('pedoman.file')
                        ->label('File Pedoman (PDF)')
                        ->disk('cms')
                        ->directory('documents')
                        ->visibility('public')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(20480) // 20 MB max
                        ->required(),
                ]),
        ];
    }
}
