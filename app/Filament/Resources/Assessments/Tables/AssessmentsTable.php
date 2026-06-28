<?php

namespace App\Filament\Resources\Assessments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextInputColumn;
use Illuminate\Database\Eloquent\Collection;

class AssessmentsTable
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
                TextColumn::make('nama_pic')
                    ->label('Nama PIC')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                ColumnGroup::make('Reviewer & Nilai')
                    ->columns([
                        // Reviewer 1
                        SelectColumn::make('reviewer_1_id')
                            ->label('Reviewer 1')
                            ->options(\App\Models\Reviewer::pluck('nama_lengkap', 'id'))
                            ->placeholder('Pilih R1'),
                        TextColumn::make('nilai_reviewer_1')
                            ->label('Nilai 1')
                            ->sortable(),

                        // Reviewer 2
                        SelectColumn::make('reviewer_2_id')
                            ->label('Reviewer 2')
                            ->options(\App\Models\Reviewer::pluck('nama_lengkap', 'id'))
                            ->placeholder('Pilih R2'),
                        TextColumn::make('nilai_reviewer_2')
                            ->label('Nilai 2')
                            ->sortable(),

                        // Reviewer 3
                        SelectColumn::make('reviewer_3_id')
                            ->label('Reviewer 3')
                            ->options(\App\Models\Reviewer::pluck('nama_lengkap', 'id'))
                            ->placeholder('Pilih R3'),
                        TextInputColumn::make('nilai_reviewer_3')
                            ->label('Nilai 3 (Input)')
                            ->type('number')
                            ->rules(['numeric', 'min:0']),
                    ]),

                TextColumn::make('nilai_rata_rata')
                    ->label('Rata-rata R1 & R2')
                    ->sortable(),

                TextColumn::make('total_skor_akhir')
                    ->label('Skor Akhir')
                    ->sortable()
                    ->color(function ($record) {
                        $threshold = (float) config('rubrik.reviewer_dispute_threshold', 100);
                        $diff = abs(($record->nilai_reviewer_1 ?? 0) - ($record->nilai_reviewer_2 ?? 0));
                        return $diff >= $threshold ? 'danger' : null;
                    })
                    ->extraAttributes(function ($record) {
                        $threshold = (float) config('rubrik.reviewer_dispute_threshold', 100);
                        $diff = abs(($record->nilai_reviewer_1 ?? 0) - ($record->nilai_reviewer_2 ?? 0));
                        if ($diff >= $threshold) {
                            return [
                                'style' => 'background-color: #fee2e2; color: #dc2626; font-weight: bold;',
                            ];
                        }
                        return [];
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'UNVERIFIED'  => 'danger',
                        'ACTIVE'      => 'gray',
                        'IN_PROGRESS' => 'info',
                        'SUBMITTED'   => 'warning',
                        'GRADED'      => 'success',
                        'PUBLISHED'   => 'success',
                        default       => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => '/admin/assessment-detail/' . $record->id)
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => '/admin/assessment-detail/' . $record->id)
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Publish semua yang GRADED sekaligus
                    BulkAction::make('publishAll')
                        ->label('Publish Nilai Terpilih')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Publish Nilai Semua Peserta Terpilih?')
                        ->modalDescription('Hanya peserta dengan status GRADED yang akan dipublikasikan.')
                        ->action(function (Collection $records): void {
                            $records->where('status', 'GRADED')
                                ->each(fn ($record) => $record->update(['status' => 'PUBLISHED']));
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
