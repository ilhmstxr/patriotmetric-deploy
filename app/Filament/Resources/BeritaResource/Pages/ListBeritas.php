<?php

namespace App\Filament\Resources\BeritaResource\Pages;

use App\Filament\Resources\BeritaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use Filament\Actions\Action;
use App\Filament\Pages\ComproForms\BeritaForm;
use App\Services\ComproContentService;
use Filament\Notifications\Notification;

class ListBeritas extends ListRecords
{
    protected static string $resource = BeritaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('editHeader')
                ->label('Edit Header')
                ->icon('heroicon-o-pencil-square')
                ->color('amber')
                ->form(BeritaForm::schema())
                ->mountUsing(fn ($form) => $form->fill(
                    app(ComproContentService::class)->loadFormData('berita')
                ))
                ->action(function (array $data) {
                    try {
                        app(ComproContentService::class)->saveFormData('berita', $data);
                        Notification::make()
                            ->title('Header berita berhasil disimpan')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal menyimpan header berita')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
