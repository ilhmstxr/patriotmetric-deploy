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
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Storage;

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
            TextInput::make('slug')
                ->label('Slug')
                ->maxLength(350)
                ->unique(ignoreRecord: true)
                ->helperText('Otomatis dibuat dari judul jika dikosongkan'),
            Textarea::make('excerpt')
                ->label('Ringkasan')
                ->maxLength(500)
                ->helperText('Otomatis dibuat dari konten jika dikosongkan'),
            RichEditor::make('konten')
                ->label('Konten Lengkap')
                ->required()
                ->columnSpanFull()
                ->fileAttachmentsDisk('cms')
                ->fileAttachmentsVisibility('public')
                ->saveUploadedFileAttachmentUsing(function (TemporaryUploadedFile $file, ?Berita $record) {
                    $mimeType = $file->getMimeType();
                    $realPath = $file->getRealPath();

                    // Load image according to its mime type
                    switch ($mimeType) {
                        case 'image/jpeg':
                            $image = imagecreatefromjpeg($realPath);
                            break;
                        case 'image/png':
                            $image = imagecreatefrompng($realPath);
                            if ($image) {
                                imagesavealpha($image, true);
                            }
                            break;
                        case 'image/gif':
                            $image = imagecreatefromgif($realPath);
                            break;
                        case 'image/webp':
                            $image = imagecreatefromwebp($realPath);
                            break;
                        default:
                            throw new \InvalidArgumentException("Format gambar tidak didukung: {$mimeType}");
                    }

                    if (!$image) {
                        throw new \Exception("Gagal memproses gambar.");
                    }

                    // Generate a unique filename with .webp extension
                    $filename = uniqid('berita_content_') . '_' . time() . '.webp';
                    
                    // If we have a record (i.e. we are editing), we save it to 'berita/{id}'
                    // If we don't have a record (i.e. creating), we save to 'berita/temp' first
                    $dir = $record ? "berita/{$record->id}" : 'berita/temp';
                    $path = "{$dir}/{$filename}";

                    $tempPath = sys_get_temp_dir() . '/' . $filename;
                    imagewebp($image, $tempPath, config('image.webp_quality', 80));
                    imagedestroy($image);

                    Storage::disk('cms')->put($path, file_get_contents($tempPath));
                    unlink($tempPath);

                    return $path;
                }),
            FileUpload::make('gambar')
                ->label('Gambar Utama')
                ->image()
                ->disk('cms')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                ->maxSize(2048)
                ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, ?Berita $record) {
                    $mimeType = $file->getMimeType();
                    $realPath = $file->getRealPath();

                    // Load image according to its mime type
                    switch ($mimeType) {
                        case 'image/jpeg':
                            $image = imagecreatefromjpeg($realPath);
                            break;
                        case 'image/png':
                            $image = imagecreatefrompng($realPath);
                            if ($image) {
                                imagesavealpha($image, true);
                            }
                            break;
                        case 'image/gif':
                            $image = imagecreatefromgif($realPath);
                            break;
                        case 'image/webp':
                            $image = imagecreatefromwebp($realPath);
                            break;
                        default:
                            throw new \InvalidArgumentException("Format gambar tidak didukung: {$mimeType}");
                    }

                    if (!$image) {
                        throw new \Exception("Gagal memproses gambar.");
                    }

                    // Generate a unique filename with .webp extension
                    $filename = uniqid('berita_') . '_' . time() . '.webp';
                    
                    // If we have a record (i.e. we are editing), we save it to 'berita/{id}'
                    // If we don't have a record (i.e. creating), we save to 'berita/temp' first
                    $dir = $record ? "berita/{$record->id}" : 'berita/temp';
                    $path = "{$dir}/{$filename}";

                    $tempPath = sys_get_temp_dir() . '/' . $filename;
                    imagewebp($image, $tempPath, config('image.webp_quality', 80));
                    imagedestroy($image);

                    Storage::disk('cms')->put($path, file_get_contents($tempPath));
                    unlink($tempPath);

                    return $path;
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
                ImageColumn::make('gambar')->disk('cms')->label('Gambar')->width(80)->height(50),
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
            'index' => Pages\ListBeritas::route('/'),
            'create' => Pages\CreateBerita::route('/create'),
            'edit' => Pages\EditBerita::route('/{record}/edit'),
        ];
    }
}
