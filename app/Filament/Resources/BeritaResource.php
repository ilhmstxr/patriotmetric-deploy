<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BeritaResource\Pages;
use App\Models\Berita;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class BeritaResource extends Resource
{
    protected static ?string $model = Berita::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static \UnitEnum|string|null $navigationGroup = 'CMS Compro';

    protected static ?string $navigationLabel = 'Berita';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('judul')
                ->label('Judul')
                ->required()
                ->maxLength(300),
            RichEditor::make('konten')
                ->label('Konten Lengkap')
                ->required()
                ->columnSpanFull()
                ->fileAttachmentsDisk('cms')
                ->fileAttachmentsDirectory('berita/temp')
                ->fileAttachmentsVisibility('public'),
            FileUpload::make('gambar')
                ->label('Gambar Utama')
                ->image()
                ->disk('cms')
                ->visibility('public')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                ->maxSize(5120)
                ->imagePreviewHeight('200')
                ->helperText('Format: JPG, PNG, WebP, GIF. Maks 5MB.')
                ->saveUploadedFileUsing(function ($file, $record) {
                    $filename = Str::random(40) . '.webp';
                    // If record exists (edit), save directly to berita/{id}/
                    // If creating, save to temp — model hook will move it
                    $dir  = $record ? "berita/{$record->id}" : 'berita/temp';
                    $path = $dir . '/' . $filename;

                    if ($record) {
                        Storage::disk('cms')->makeDirectory($dir);
                    }

                    $encoded = Image::read($file)->toWebp(85);
                    Storage::disk('cms')->put($path, (string) $encoded);

                    return $path;
                })
                ->deleteUploadedFileUsing(function ($file) {
                    // $file is the stored path relative to disk root
                    if ($file && Storage::disk('cms')->exists($file)) {
                        Storage::disk('cms')->delete($file);
                    }
                }),
            DatePicker::make('tanggal')
                ->label('Tanggal')
                ->required()
                ->default(now()),
            Toggle::make('is_published')
                ->label('Publikasikan')
                ->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('gambar')
                    ->disk('cms')
                    ->label('Gambar')
                    ->width(80)
                    ->height(50)
                    ->defaultImageUrl(fn () => null),
                TextColumn::make('judul')->label('Judul')->searchable()->limit(50),
                TextColumn::make('tanggal')->label('Tanggal')->date('j M Y')->sortable(),
                IconColumn::make('is_published')->label('Published')->boolean(),
                TextColumn::make('updated_at')->label('Diperbarui')->since()->sortable(),
            ])
            ->defaultSort('tanggal', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBeritas::route('/'),
            'create' => Pages\CreateBerita::route('/create'),
            'edit'   => Pages\EditBerita::route('/{record}/edit'),
        ];
    }
}
