<?php

namespace App\Filament\Widgets;

use App\Models\Assessment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $tahunList    = Assessment::select('tahun_periode')->distinct()->orderByDesc('tahun_periode')->pluck('tahun_periode');
        $tahunTerkini = $tahunList->first();

        $totalInstitusi  = User::where('role', 'PESERTA')->count();
        $totalAssessment = Assessment::when($tahunTerkini, fn($q) => $q->where('tahun_periode', $tahunTerkini))->count();
        $totalReviewer   = \App\Models\Reviewer::count();

        return [
            Stat::make('Total Institusi Peserta', $totalInstitusi)
                ->color('primary'),
            Stat::make('Assessment ' . ($tahunTerkini ?? 'Semua'), $totalAssessment)
                ->description('Periode terkini')
                ->color('warning'),
            Stat::make('Total Reviewer', $totalReviewer)
                ->color('info'),
        ];
    }
}
