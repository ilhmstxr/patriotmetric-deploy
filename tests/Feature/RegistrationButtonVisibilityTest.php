<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SubmissionTimeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class RegistrationButtonVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_button_hidden_when_no_timeline_exists(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('invisible', false);
    }

    public function test_registration_button_hidden_when_opens_at_not_reached(): void
    {
        SubmissionTimeline::create([
            'tahun_periode' => date('Y'),
            'opens_at' => Carbon::now()->addDays(7),
            'closes_at' => Carbon::now()->addMonths(3),
            'is_locked' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('invisible', false);
    }

    public function test_registration_button_visible_when_timeline_is_open(): void
    {
        SubmissionTimeline::create([
            'tahun_periode' => date('Y'),
            'opens_at' => Carbon::now()->subDays(7),
            'closes_at' => Carbon::now()->addMonths(3),
            'is_locked' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        // The hero button should NOT have invisible class
        $response->assertDontSee('invisible', false);
    }

    public function test_registration_button_hidden_when_timeline_is_closed(): void
    {
        SubmissionTimeline::create([
            'tahun_periode' => date('Y'),
            'opens_at' => Carbon::now()->subMonths(3),
            'closes_at' => Carbon::now()->subDays(1),
            'is_locked' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('invisible', false);
    }

    public function test_registration_button_hidden_when_timeline_is_locked(): void
    {
        SubmissionTimeline::create([
            'tahun_periode' => date('Y'),
            'opens_at' => Carbon::now()->subDays(7),
            'closes_at' => Carbon::now()->addMonths(3),
            'is_locked' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('invisible', false);
    }
}
