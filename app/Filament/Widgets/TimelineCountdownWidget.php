<?php

namespace App\Filament\Widgets;

use App\Models\SubmissionTimeline;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class TimelineCountdownWidget extends Widget
{
    protected string $view = 'filament.widgets.timeline-countdown-widget';
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '60s';

    public function getViewData(): array
    {
        $now      = Carbon::now();
        $timeline = SubmissionTimeline::orderByDesc('tahun_periode')->first();

        if (! $timeline) {
            return [
                'hasTimeline'     => false,
                'tahunPeriode'    => null,
                'isLocked'        => false,
                'opensAt'         => null,
                'closesAt'        => null,
                'resultsAt'       => null,
                'phase'           => 'none',
                'progressPercent' => 0,
                'daysLeft'        => null,
                'hoursLeft'       => null,
                'phaseLabel'      => 'Tidak ada timeline aktif',
                'note'            => null,
            ];
        }

        $phase           = 'none';
        $progressPercent = 0;
        $daysLeft        = null;
        $hoursLeft       = null;
        $phaseLabel      = '';

        if ($timeline->is_locked) {
            $phase      = 'locked';
            $phaseLabel = 'Submission dikunci admin';
        } elseif ($timeline->opens_at && $now->lt($timeline->opens_at)) {
            $phase           = 'upcoming';
            $phaseLabel      = 'Belum dibuka';
            $daysLeft        = (int) $now->diffInDays($timeline->opens_at, false);
            $hoursLeft       = (int) $now->diffInHours($timeline->opens_at, false);
            $progressPercent = 0;
        } elseif ($timeline->closes_at && $now->lt($timeline->closes_at)) {
            $phase      = 'open';
            $phaseLabel = 'Submission dibuka';
            $daysLeft   = (int) $now->diffInDays($timeline->closes_at, false);
            $hoursLeft  = (int) $now->diffInHours($timeline->closes_at, false);

            if ($timeline->opens_at) {
                $total           = $timeline->opens_at->diffInSeconds($timeline->closes_at);
                $elapsed         = $timeline->opens_at->diffInSeconds($now);
                $progressPercent = $total > 0 ? (int) min(100, ($elapsed / $total) * 100) : 0;
            }
        } elseif ($timeline->results_published_at && $now->lt($timeline->results_published_at)) {
            $phase      = 'closed';
            $phaseLabel = 'Submission ditutup, menunggu pengumuman';
            $daysLeft   = (int) $now->diffInDays($timeline->results_published_at, false);
        } else {
            $phase           = 'published';
            $phaseLabel      = 'Hasil sudah dipublikasikan';
            $progressPercent = 100;
        }

        return [
            'hasTimeline'     => true,
            'tahunPeriode'    => $timeline->tahun_periode,
            'isLocked'        => $timeline->is_locked,
            'opensAt'         => $timeline->opens_at,
            'closesAt'        => $timeline->closes_at,
            'resultsAt'       => $timeline->results_published_at,
            'phase'           => $phase,
            'progressPercent' => $progressPercent,
            'daysLeft'        => $daysLeft,
            'hoursLeft'       => $hoursLeft,
            'phaseLabel'      => $phaseLabel,
            'note'            => $timeline->note,
        ];
    }
}
