<?php

namespace App\Filament\Widgets;

use App\Models\Assessment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssessmentStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = null;

    public ?string $tahunPeriode = null;

    protected function getStats(): array
    {
        $tahunList = Assessment::select('tahun_periode')
            ->distinct()
            ->orderByDesc('tahun_periode')
            ->pluck('tahun_periode');

        if (! $this->tahunPeriode && $tahunList->isNotEmpty()) {
            $this->tahunPeriode = (string) $tahunList->first();
        }

        $query = Assessment::query();
        if ($this->tahunPeriode) {
            $query->where('tahun_periode', $this->tahunPeriode);
        }

        $total        = (clone $query)->count();
        $draft        = (clone $query)->where('status', 'draft')->count();
        $submitted    = (clone $query)->where('status', 'submitted')->count();
        $reviewing    = (clone $query)->where('status', 'reviewing')->count();
        $validated    = (clone $query)->where('status', 'validated')->count();
        $avgSkor      = (clone $query)->whereNotNull('total_skor_akhir')->avg('total_skor_akhir');

        return [
            Stat::make('Total Assessment', $total)
                ->description('Periode ' . ($this->tahunPeriode ?? 'semua'))
                ->color('primary'),

            Stat::make('Menunggu / Sedang Review', $submitted + $reviewing)
                ->description("Submitted: {$submitted} | Reviewing: {$reviewing}")
                ->color('warning'),

            Stat::make('Selesai Divalidasi', $validated)
                ->description('Draft: ' . $draft)
                ->color('success'),

            Stat::make('Rata-rata Skor Akhir', $avgSkor ? number_format($avgSkor, 1) : '-')
                ->description('Dari assessment yang sudah divalidasi')
                ->color('info'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
