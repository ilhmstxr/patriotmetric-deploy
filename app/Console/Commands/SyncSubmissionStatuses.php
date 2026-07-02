<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncSubmissionStatuses extends Command
{
    protected $signature = 'app:sync-submission-statuses';
    protected $description = 'Sincronize penugasan statuses based on submission timelines';

    protected $penugasanRepository;
    protected $timelineRepository;

    public function __construct(
        \App\Repositories\PenugasanRepository $penugasanRepository,
        \App\Repositories\TimelineRepository $timelineRepository
    ) {
        parent::__construct();
        $this->penugasanRepository = $penugasanRepository;
        $this->timelineRepository = $timelineRepository;
    }

    public function handle()
    {
        $this->info('Starting status synchronization...');

        $timelines = $this->timelineRepository->getAllTimelines();
        $now = \Illuminate\Support\Carbon::now();

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

            // 2. Auto-Validate (Transition to VALIDATING)
            if ($timeline->validation_at && $now->gt($timeline->validation_at)) {
                // Transition SUBMITTED and GRADED to VALIDATING
                $affectedValidating = $this->penugasanRepository->batchUpdateStatusByYear(
                    $timeline->tahun_periode,
                    ['SUBMITTED', 'GRADED'],
                    'VALIDATING'
                );

                if ($affectedValidating > 0) {
                    $this->info("Transitioned {$affectedValidating} submissions to VALIDATING for year {$timeline->tahun_periode}.");
                }
            }

            // 3. Auto-Publish (Transition to PUBLISHED)
            if ($timeline->results_published_at && $now->gt($timeline->results_published_at)) {
                // Transition FINALIZED to PUBLISHED
                $affectedPublished = $this->penugasanRepository->batchUpdateStatusByYear(
                    $timeline->tahun_periode,
                    ['FINALIZED'],
                    'PUBLISHED'
                );

                if ($affectedPublished > 0) {
                    $this->info("Published {$affectedPublished} finalized submissions to PUBLISHED for year {$timeline->tahun_periode}.");
                }
            }
        }

        $this->info('Synchronization completed.');
    }
}
