<?php

namespace App\Filament\Widgets;

use App\Models\Berita;
use App\Models\Institusi;
use App\Models\Reviewer;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TopMetricsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $currentYear = now()->year;

        // Total Institusi (filtered to current year based on created_at or penugasan period)
        $totalInstitusi = Institusi::whereYear('created_at', $currentYear)->count();

        // Total Peserta 2026 (Peserta role who has penugasan in 2026)
        $peserta2026Query = User::where('role', 'PESERTA')
            ->whereHas('penugasans', function ($q) {
                $q->where('tahun_periode', 2026);
            });

        $totalPeserta2026 = (clone $peserta2026Query)->count();
        $activePeserta2026 = (clone $peserta2026Query)->where('status', 'ACTIVE')->count();
        $pendingPeserta2026 = (clone $peserta2026Query)->whereNull('email_verified_at')->count();

        // Total Reviewer
        $totalReviewer = Reviewer::count();

        // Berita Published
        $totalBerita     = Berita::count();
        $publishedBerita = Berita::where('is_published', true)->count();
        $draftBerita     = $totalBerita - $publishedBerita;

        return [
            Stat::make('Total Reviewer', $totalReviewer)
                ->description('Reviewer terdaftar')
                ->color('info'),

            Stat::make('Total Peserta 2026', $totalPeserta2026)
                ->description("{$activePeserta2026} Aktif | {$pendingPeserta2026} Belum Verifikasi Email")
                ->color('primary'),

            Stat::make('Total Institusi', $totalInstitusi)
                ->description('Tahun berjalan (' . $currentYear . ')')
                ->color('success'),

            Stat::make('Berita Published', $publishedBerita)
                ->description("Draft: {$draftBerita} | Total: {$totalBerita}")
                ->color('success'),
        ];
    }
}
