<?php

namespace App\Filament\Widgets;

use App\Models\SubmissionTimeline;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DynamicStateMonitorWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = '60s';

    // Memaksa widget untuk membagi ruang menjadi 4 kolom
    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $timeline = SubmissionTimeline::orderByDesc('tahun_periode')->first();
        $now = Carbon::now();

        // Fallback jika tidak ada data timeline sama sekali
        if (! $timeline) {
            return [
                Stat::make('Status Penugasan', 'OFFLINE')
                    ->description('Tidak ada timeline aktif dalam sistem.')
                    ->color('gray'),
            ];
        }

        $state = $this->resolveTimelineState($timeline, $now);

        // Helper untuk format tanggal agar seragam dan DRY
        $formatDate = fn($date) => $date ? $date->translatedFormat('d M Y H:i') : 'Belum diatur';

        return [
            // 1. Status Pengerjaan (Paling Kiri)
            Stat::make('Status Pengerjaan', $state['status'])
                ->description($state['description'])
                ->color($state['color']),

            // 2. Tahap Pengisian (Mulai)
            Stat::make('Mulai Penugasan', $formatDate($timeline->opens_at))
                ->description('Tahap Pengisian Rubrik Dimulai')
                ->color('success'),

            // 3. Deadline Pengisian
            Stat::make('Batas Akhir (Deadline)', $formatDate($timeline->closes_at))
                ->description('Batas Akhir Pengisian Rubrik')
                ->color('danger'),

            // 4. Pengumuman Hasil (Paling Kanan)
            Stat::make('Pengumuman Hasil', $formatDate($timeline->results_published_at))
                ->description('Tahap Pengumuman Hasil Dimulai')
                ->color('info'),
        ];
    }

    /**
     * Memisahkan logika penentuan status agar fungsi getStats() tetap bersih.
     */
    private function resolveTimelineState(SubmissionTimeline $timeline, Carbon $now): array
    {
        $opensAt = $timeline->opens_at;
        $closesAt = $timeline->closes_at;
        $resultsPublishedAt = $timeline->results_published_at;

        if ($timeline->is_locked) {
            return ['status' => 'LOCKED', 'color' => 'danger', 'description' => 'Timeline dikunci admin.'];
        }

        if ($opensAt && $now->lt($opensAt)) {
            return ['status' => 'BELUM DIBUKA', 'color' => 'gray', 'description' => 'Menunggu waktu buka.'];
        }

        if ($closesAt && $now->between($opensAt, $closesAt)) {
            return ['status' => 'PENGERJAAN', 'color' => 'success', 'description' => 'Tahap pengisian aktif.'];
        }

        if ($closesAt && $resultsPublishedAt && $now->between($closesAt, $resultsPublishedAt)) {
            return ['status' => 'PENINJAUAN', 'color' => 'warning', 'description' => 'Peninjauan oleh reviewer.'];
        }

        if ($resultsPublishedAt && $now->greaterThanOrEqualTo($resultsPublishedAt)) {
            return ['status' => 'PENGUMUMAN', 'color' => 'info', 'description' => 'Hasil telah dipublikasikan.'];
        }

        return ['status' => 'UNKNOWN', 'color' => 'gray', 'description' => 'Status tidak terdefinisi.'];
    }
}
