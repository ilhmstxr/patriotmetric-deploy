<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\PenugasanRepository;
use App\Repositories\TimelineRepository;
use Illuminate\Support\Carbon;

class SyncSubmissionStatuses extends Command
{
    protected $signature = 'app:sync-submission-statuses';
    protected $description = 'Synchronize penugasan statuses based on submission timelines';

    protected $penugasanRepository;
    protected $timelineRepository;

    public function __construct(
        PenugasanRepository $penugasanRepository,
        TimelineRepository $timelineRepository
    ) {
        parent::__construct();
        $this->penugasanRepository = $penugasanRepository;
        $this->timelineRepository = $timelineRepository;
    }

    public function handle()
    {
        $this->info('Starting status synchronization...');

        $timelines = $this->timelineRepository->getAllTimelines();
        $now = Carbon::now();

        foreach ($timelines as $timeline) {
            $this->comment("Processing timeline for year: {$timeline->tahun_periode}");

            // 1. Auto-Lock (Transition to SUBMITTED)
            if (($timeline->closes_at && $now->gt($timeline->closes_at)) || $timeline->is_locked) {
                $affected = $this->penugasanRepository->batchUpdateStatusByYear(
                    $timeline->tahun_periode, 
                    ['ACTIVE', 'IN_PROGRESS'], 
                    'SUBMITTED'
                );
                
                if ($affected > 0) {
                    $this->info("Locked {$affected} submissions to SUBMITTED for year {$timeline->tahun_periode}.");
                }
            }

            // 2. Auto-Publish (Transition to PUBLISHED)
            if ($timeline->results_published_at && $now->gt($timeline->results_published_at)) {
                $affected = $this->penugasanRepository->batchUpdateStatusByYear(
                    $timeline->tahun_periode, 
                    ['GRADED'], 
                    'PUBLISHED'
                );

                if ($affected > 0) {
                    $this->info("Published {$affected} submissions to PUBLISHED for year {$timeline->tahun_periode}.");
                }
            }
        }

        $this->info('Synchronization completed.');
    }
}
