<?php

namespace App\Filament\Widgets;

use App\Models\Reviewer;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalPeserta   = User::where('role', 'PESERTA')->count();
        $activePeserta  = User::where('role', 'PESERTA')->where('status', 'ACTIVE')->count();
        $pendingPeserta = User::where('role', 'PESERTA')
            ->whereNull('email_verified_at')
            ->count();
        $totalReviewer  = Reviewer::count();

        return [
            Stat::make('Total Peserta', $totalPeserta)
                ->description("Aktif: {$activePeserta}")
                ->color('primary'),

            Stat::make('Belum Verifikasi Email', $pendingPeserta)
                ->description('Peserta yang belum verifikasi')
                ->color($pendingPeserta > 0 ? 'warning' : 'success'),

            Stat::make('Total Reviewer', $totalReviewer)
                ->description('Reviewer terdaftar')
                ->color('info'),
        ];
    }
}
