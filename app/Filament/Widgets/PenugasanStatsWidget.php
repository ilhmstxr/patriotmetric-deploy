<?php

namespace App\Filament\Widgets;

use App\Models\Penugasan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PenugasanStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = null;

    public ?string $tahunPeriode = null;

    protected function getStats(): array
    {
        $tahunList = Penugasan::select('tahun_periode')
            ->distinct()
            ->orderByDesc('tahun_periode')
            ->pluck('tahun_periode');

        if (! $this->tahunPeriode && $tahunList->isNotEmpty()) {
            $this->tahunPeriode = (string) $tahunList->first();
        }

        $query = Penugasan::query();
        if ($this->tahunPeriode) {
            $query->where('tahun_periode', $this->tahunPeriode);
        }

        $total        = (clone $query)->count();
        $unverified   = (clone $query)->where('status', 'UNVERIFIED')->count();
        $draft        = (clone $query)->whereIn('status', ['ACTIVE', 'IN_PROGRESS'])->count();
        $submitted    = (clone $query)->where('status', 'SUBMITTED')->count();
        $graded       = (clone $query)->where('status', 'GRADED')->count();
        $published    = (clone $query)->where('status', 'PUBLISHED')->count();
        $avgSkor      = (clone $query)->whereNotNull('total_skor_akhir')->avg('total_skor_akhir');

        return [
            Stat::make('Total Penugasan', $total)
                ->description('Periode ' . ($this->tahunPeriode ?? 'semua'))
                ->color('primary'),

            Stat::make('Menunggu Review', $submitted)
                ->description("Unverified: {$unverified} | Draft: {$draft}")
                ->color('warning'),

            Stat::make('Selesai Penilaian', $graded + $published)
                ->description("Graded: {$graded} | Published: {$published}")
                ->color('success'),

            Stat::make('Rata-rata Skor Akhir', $avgSkor ? number_format($avgSkor, 1) : '-')
                ->description('Dari penugasan yang sudah divalidasi')
                ->color('info'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
