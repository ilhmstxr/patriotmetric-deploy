<?php

namespace App\Filament\Widgets;

use App\Models\Berita;
use App\Models\ComproContent;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ComproStatsWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalBerita     = Berita::count();
        $publishedBerita = Berita::where('is_published', true)->count();
        $draftBerita     = $totalBerita - $publishedBerita;

        $totalComproSections = ComproContent::distinct('section')->count('section');
        $totalComproKeys     = ComproContent::count();

        return [
            Stat::make('Berita Published', $publishedBerita)
                ->description("Draft: {$draftBerita} | Total: {$totalBerita}")
                ->color('success'),

            Stat::make('Konten Compro (Key)', $totalComproKeys)
                ->description("Dari {$totalComproSections} section halaman")
                ->color('info'),
        ];
    }
}
