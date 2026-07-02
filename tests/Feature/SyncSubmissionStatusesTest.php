<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Penugasan;
use App\Models\SubmissionTimeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class SyncSubmissionStatusesTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_publish_transitions_submitted_penugasans_when_results_published_at_passes(): void
    {
        $tahun = '2026';

        SubmissionTimeline::create([
            'tahun_periode' => $tahun,
            'opens_at' => Carbon::parse('2026-01-01'),
            'closes_at' => Carbon::parse('2026-03-01'),
            'validation_at' => Carbon::parse('2026-04-01'),
            'results_published_at' => Carbon::parse('2026-06-01'),
            'is_locked' => false,
        ]);

        $submittedPenugasan = Penugasan::factory()->create([
            'tahun_periode' => $tahun,
            'status' => 'SUBMITTED',
        ]);

        $gradedPenugasan = Penugasan::factory()->create([
            'tahun_periode' => $tahun,
            'status' => 'GRADED',
        ]);

        $finalizedPenugasan = Penugasan::factory()->create([
            'tahun_periode' => $tahun,
            'status' => 'FINALIZED',
        ]);

        // Simulate time after results_published_at
        Carbon::setTestNow(Carbon::parse('2026-06-15'));

        $this->artisan('app:sync-submission-statuses')
            ->assertExitCode(0);

        // Both SUBMITTED and GRADED should become VALIDATING
        $this->assertDatabaseHas('penugasans', [
            'id' => $submittedPenugasan->id,
            'status' => 'VALIDATING',
        ]);

        $this->assertDatabaseHas('penugasans', [
            'id' => $gradedPenugasan->id,
            'status' => 'VALIDATING',
        ]);

        // FINALIZED should become PUBLISHED
        $this->assertDatabaseHas('penugasans', [
            'id' => $finalizedPenugasan->id,
            'status' => 'PUBLISHED',
        ]);

        Carbon::setTestNow();
    }

    public function test_auto_publish_does_not_affect_penugasans_before_results_published_at(): void
    {
        $tahun = '2026';

        SubmissionTimeline::create([
            'tahun_periode' => $tahun,
            'opens_at' => Carbon::parse('2026-01-01'),
            'closes_at' => Carbon::parse('2026-03-01'),
            'validation_at' => Carbon::parse('2026-05-20'),
            'results_published_at' => Carbon::parse('2026-06-01'),
            'is_locked' => false,
        ]);

        $submittedPenugasan = Penugasan::factory()->create([
            'tahun_periode' => $tahun,
            'status' => 'SUBMITTED',
        ]);

        // Simulate time BEFORE results_published_at
        Carbon::setTestNow(Carbon::parse('2026-05-15'));

        $this->artisan('app:sync-submission-statuses')
            ->assertExitCode(0);

        // SUBMITTED should stay SUBMITTED (not yet time to publish)
        $this->assertDatabaseHas('penugasans', [
            'id' => $submittedPenugasan->id,
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
            'validation_at' => Carbon::now()->subMonths(2),
            'results_published_at' => Carbon::now()->subDay(),
            'is_locked' => false,
        ]);

        $user = User::factory()->create([
            'role' => 'PESERTA',
            'status' => 'ACTIVE',
        ]);

        $penugasan = Penugasan::factory()->create([
            'user_id' => $user->id,
            'tahun_periode' => $tahun,
            'status' => 'FINALIZED',
        ]);

        $token = $user->createToken('test')->plainTextToken;

        // Run status sync to transition SUBMITTED to PUBLISHED since timeline results_published_at has passed
        $this->artisan('app:sync-submission-statuses');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/penugasan/peserta/hasil');

        $response->assertOk();
        $response->assertJsonPath('data.is_published', true);
    }
}
