<?php

namespace App\Filament\Pages\ComproForms;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class PanduanForm
{
    public static function schema(): array
    {
        return [
            Section::make('Hero')
                ->schema([
                    TextInput::make('hero.judul')->label('Judul')->maxLength(255)->required(),
                    Textarea::make('hero.deskripsi')->label('Deskripsi')->maxLength(500)->required(),
                    TextInput::make('hero.tombol_teks')->label('Teks Tombol Pedoman')->maxLength(100)->required(),
                    TextInput::make('hero.tombol_link')->label('Link Pedoman')->url()->maxLength(255)->required(),
                ]),

            Section::make('Persyaratan Sistem')
                ->schema([
                    Repeater::make('persyaratan.daftar')
                        ->label('Daftar Persyaratan')
                        ->schema([
                            TextInput::make('icon')->label('Icon (Lucide icon name)')->maxLength(50)->required(),
                            TextInput::make('judul')->label('Judul')->maxLength(150)->required(),
                            Textarea::make('deskripsi')->label('Deskripsi')->maxLength(500)->required(),
                        ])
                        ->maxItems(20)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn(array $state) => $state['judul'] ?? 'Persyaratan Baru'),
                ]),

            Section::make('Panduan Langkah')
                ->schema([
                    TextInput::make('panduan-langkah.judul')->label('Judul Section')->maxLength(255)->required(),
                    Repeater::make('panduan-langkah.daftar')
                        ->label('Daftar Langkah')
                        ->schema([
                            TextInput::make('nomor')->label('Nomor')->maxLength(10)->required(),
                            TextInput::make('judul')->label('Judul')->maxLength(200)->required(),
                            Textarea::make('deskripsi')->label('Deskripsi')->maxLength(1000)->required(),
                        ])
                        ->maxItems(50)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn(array $state) => ($state['nomor'] ?? '') . '. ' . ($state['judul'] ?? 'Langkah Baru')),
                ]),

            Section::make('Catatan Teknis')
                ->schema([
                    TextInput::make('catatan.judul')->label('Judul Section')->maxLength(255)->required(),
                    Repeater::make('catatan.daftar')
                        ->label('Daftar Catatan')
                        ->schema([
                            Select::make('tipe')
                                ->label('Tipe')
                                ->options([
                                    'info' => 'Info',
                                    'warning' => 'Peringatan',
                                    'tip' => 'Tips',
                                ])
                                ->required(),
                            TextInput::make('judul')->label('Judul')->maxLength(200)->required(),
                            Textarea::make('deskripsi')->label('Deskripsi')->maxLength(1000)->required(),
                        ])
                        ->maxItems(20)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn(array $state) => ($state['tipe'] ?? '') . ': ' . ($state['judul'] ?? 'Catatan Baru')),
                ]),
        ];
    }
}
