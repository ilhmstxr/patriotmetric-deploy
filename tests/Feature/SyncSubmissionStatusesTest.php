<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Assessment;
use App\Models\SubmissionTimeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class SyncSubmissionStatusesTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_publish_transitions_submitted_assessments_when_results_published_at_passes(): void
    {
        $tahun = '2026';

        SubmissionTimeline::create([
            'tahun_periode' => $tahun,
            'opens_at' => Carbon::parse('2026-01-01'),
            'closes_at' => Carbon::parse('2026-03-01'),
            'results_published_at' => Carbon::parse('2026-06-01'),
            'is_locked' => false,
        ]);

        $submittedAssessment = Assessment::factory()->create([
            'tahun_periode' => $tahun,
            'status' => 'SUBMITTED',
        ]);

        $gradedAssessment = Assessment::factory()->create([
            'tahun_periode' => $tahun,
            'status' => 'GRADED',
        ]);

        // Simulate time after results_published_at
        Carbon::setTestNow(Carbon::parse('2026-06-15'));

        $this->artisan('app:sync-submission-statuses')
            ->assertExitCode(0);

        // Both SUBMITTED and GRADED should become PUBLISHED
        $this->assertDatabaseHas('assessments', [
            'id' => $submittedAssessment->id,
            'status' => 'PUBLISHED',
        ]);

        $this->assertDatabaseHas('assessments', [
            'id' => $gradedAssessment->id,
            'status' => 'PUBLISHED',
        ]);

        Carbon::setTestNow();
    }

    public function test_auto_publish_does_not_affect_assessments_before_results_published_at(): void
    {
        $tahun = '2026';

        SubmissionTimeline::create([
            'tahun_periode' => $tahun,
            'opens_at' => Carbon::parse('2026-01-01'),
            'closes_at' => Carbon::parse('2026-03-01'),
            'results_published_at' => Carbon::parse('2026-06-01'),
            'is_locked' => false,
        ]);

        $submittedAssessment = Assessment::factory()->create([
            'tahun_periode' => $tahun,
            'status' => 'SUBMITTED',
        ]);

        // Simulate time BEFORE results_published_at
        Carbon::setTestNow(Carbon::parse('2026-05-15'));

        $this->artisan('app:sync-submission-statuses')
            ->assertExitCode(0);

        // SUBMITTED should stay SUBMITTED (not yet time to publish)
        $this->assertDatabaseHas('assessments', [
            'id' => $submittedAssessment->id,
            'status' => 'SUBMITTED',
        ]);

        Carbon::setTestNow();
    }

    public function test_hasil_shows_published_when_timeline_has_passed_results_published_at(): void
    {
        $tahun = (string) date('Y');

        $timeline = SubmissionTimeline::create([
            'tahun_periode' => $tahun,
            'opens_at' => Carbon::now()->subMonths(6),
            'closes_at' => Carbon::now()->subMonths(3),
            'results_published_at' => Carbon::now()->subDay(),
            'is_locked' => false,
        ]);

        $user = User::factory()->create([
            'role' => 'PESERTA',
            'status' => 'ACTIVE',
        ]);

        $assessment = Assessment::factory()->create([
            'user_id' => $user->id,
            'tahun_periode' => $tahun,
            'status' => 'SUBMITTED',
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/assessment/peserta/hasil');

        // Even though status is SUBMITTED in DB, since results_published_at has passed,
        // the response should treat it as published (no disclaimer)
        $response->assertOk();
        $response->assertJsonPath('data.is_published', true);
    }
}
