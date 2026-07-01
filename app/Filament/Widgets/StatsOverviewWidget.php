<?php

namespace App\Filament\Widgets;

use App\Models\Penugasan;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $tahunList    = Penugasan::select('tahun_periode')->distinct()->orderByDesc('tahun_periode')->pluck('tahun_periode');
        $tahunTerkini = $tahunList->first();

        $totalInstitusi  = User::where('role', 'PESERTA')->count();
        $totalPenugasan = Penugasan::when($tahunTerkini, fn($q) => $q->where('tahun_periode', $tahunTerkini))->count();
        $totalReviewer   = \App\Models\Reviewer::count();

        return [
            Stat::make('Total Institusi Peserta', $totalInstitusi)
                ->color('primary'),
            Stat::make('Penugasan ' . ($tahunTerkini ?? 'Semua'), $totalPenugasan)
                ->description('Periode terkini')
                ->color('warning'),
            Stat::make('Total Reviewer', $totalReviewer)
                ->color('info'),
        ];
    }
}
