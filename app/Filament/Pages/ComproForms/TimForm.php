<?php

namespace App\Filament\Pages\ComproForms;

use App\Filament\Pages\ComproForms\Concerns\WebpFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class TimForm
{
    use WebpFileUpload;

    public static function schema(): array
    {
        return [
            Section::make('Hero')
                ->schema([
                    TextInput::make('hero.judul')->label('Judul')->maxLength(255)->required(),
                    Textarea::make('hero.deskripsi')->label('Deskripsi')->maxLength(500)->required(),
                ]),

            Section::make('Team Grid')
                ->schema([
                    Repeater::make('team-grid.daftar')
                        ->label('Daftar Anggota Tim')
                        ->schema([
                            \Filament\Forms\Components\Hidden::make('id')
                                ->default(fn () => (string) \Illuminate\Support\Str::uuid()),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->maxLength(100)
                                ->required()
                                ->live(onBlur: true),
                            TextInput::make('role')
                                ->label('Role/Jabatan')
                                ->maxLength(100)
                                ->required(),
                            \Filament\Forms\Components\Select::make('parent_id')
                                ->label('Atasan (Kosongkan jika Pimpinan Tertinggi)')
                                ->options(function ($get, $component) {
                                    $repeater = $component->getContainer()->getParentComponent();
                                    $daftar = $repeater->getState() ?? [];
                                    if (!is_array($daftar)) {
                                        return [];
                                    }
                                    
                                    $path = $component->getStatePath();
                                    $parts = explode('.', $path);
                                    $currentRepeaterKey = $parts[count($parts) - 2];
                                    
                                    $options = [];
                                    foreach ($daftar as $key => $item) {
                                        $itemId = $item['id'] ?? (string)$key;
                                        // Jangan masukkan baris diri sendiri berdasarkan repeater key
                                        if ((string)$key !== (string)$currentRepeaterKey && !empty($item['nama'])) {
                                            $options[$itemId] = $item['nama'] . (empty($item['role']) ? '' : ' (' . $item['role'] . ')');
                                        }
                                    }
                                    return $options;
                                })
                                ->searchable()
                                ->nullable(),
                            self::makeImageUpload('foto')
                                ->label('Foto'),
                        ])
                        ->maxItems(100)
                        ->reorderable(false) // Disable reorder to keep tree logic simple, order depends on parent
                        ->collapsible()
                        ->itemLabel(fn(array $state) => ($state['nama'] ?? 'Anggota Baru') . (!empty($state['role']) ? ' - ' . $state['role'] : '')),
                ]),
        ];
    }
}
