<?php

namespace App\Filament\Resources\Penugasans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Reviewer;

class PenugasansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('tahun_periode')
                    ->label('Periode')
                    ->sortable(),
                TextColumn::make('institusi.nama_institusi')
                    ->label('Nama Instansi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'UNVERIFIED'  => 'danger',
                        'ACTIVE'      => 'gray',
                        'IN_PROGRESS' => 'info',
                        'SUBMITTED'   => 'warning',
                        'GRADED'      => 'success',
                        'VALIDATING'  => 'info',
                        'FINALIZED'   => 'warning',
                        'PUBLISHED'   => 'success',
                        default       => 'gray',
                    })
                    ->sortable(),
                
                // Reviewer 1
                SelectColumn::make('reviewer_1_id')
                    ->label('Reviewer 1')
                    ->options(function ($record) {
                        if (!$record) {
                            return Reviewer::query()->pluck('nama_lengkap', 'id')->toArray();
                        }
                        $exclude = array_filter([$record->reviewer_2_id, $record->reviewer_3_id]);
                        return Reviewer::query()
                            ->whereNotIn('id', $exclude)
                            ->pluck('nama_lengkap', 'id')
                            ->toArray();
                    })
                    ->placeholder('Pilih R1'),

                // Reviewer 2
                SelectColumn::make('reviewer_2_id')
                    ->label('Reviewer 2')
                    ->options(function ($record) {
                        if (!$record) {
                            return Reviewer::query()->pluck('nama_lengkap', 'id')->toArray();
                        }
                        $exclude = array_filter([$record->reviewer_1_id, $record->reviewer_3_id]);
                        return Reviewer::query()
                            ->whereNotIn('id', $exclude)
                            ->pluck('nama_lengkap', 'id')
                            ->toArray();
                    })
                    ->placeholder('Pilih R2'),

                // Reviewer 3
                SelectColumn::make('reviewer_3_id')
                    ->label('Reviewer 3')
                    ->options(function ($record) {
                        if (!$record) {
                            return Reviewer::query()->pluck('nama_lengkap', 'id')->toArray();
                        }
                        $exclude = array_filter([$record->reviewer_1_id, $record->reviewer_2_id]);
                        return Reviewer::query()
                            ->whereNotIn('id', $exclude)
                            ->pluck('nama_lengkap', 'id')
                            ->toArray();
                    })
                    ->placeholder('Pilih R3'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => '/admin/penugasan-detail/' . $record->id)
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => '/admin/penugasan-detail/' . $record->id)
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Kirim ke Validasi semua yang GRADED sekaligus
                    BulkAction::make('validateAll')
                        ->label('Kirim ke Validasi')
                        ->icon('heroicon-o-check-circle')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Semua Peserta Terpilih ke Validasi?')
                        ->modalDescription('Hanya peserta dengan status GRADED yang akan dikirim ke status VALIDATING.')
                        ->action(function (Collection $records): void {
                            $records->where('status', 'GRADED')
                                ->each(fn ($record) => $record->update(['status' => 'VALIDATING']));
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
