<?php

namespace Tests\Feature;

use App\Models\PengaturanCms;
use App\Models\SubmissionTimeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationTimelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed active period setting
        PengaturanCms::create([
            'key' => 'active_period',
            'value' => date('Y'),
        ]);
    }

    /**
     * Helper to get register payload
     */
    protected function getRegisterPayload(): array
    {
        return [
            'nama_pt' => 'PT Test ' . Str::random(5),
            'jenis_pt' => 'PTN',
            'nama_pic' => 'Budi',
            'no_hp_pic' => '08123456789',
            'jabatan_pic' => 'Rektor',
            'email' => 'test_' . Str::random(5) . '@upnjatim.ac.id',
            'password' => 'Password123',
            'password_confirmation' => 'Password123'
        ];
    }

    public function test_registration_fails_when_no_timeline_exists(): void
    {
        // GET /daftar should return 404
        $this->get('/daftar')->assertStatus(404);

        // POST /api/auth/register should return 404
        $response = $this->postJson('/api/auth/register', $this->getRegisterPayload());
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Pendaftaran belum dibuka untuk periode saat ini.'
        ]);
    }

    public function test_registration_fails_when_timeline_opens_in_the_future(): void
    {
        SubmissionTimeline::create([
            'tahun_periode' => date('Y'),
            'opens_at' => now()->addDays(5),
            'closes_at' => now()->addDays(10),
            'is_locked' => false,
        ]);

        // GET /daftar should return 404
        $this->get('/daftar')->assertStatus(404);

        // POST /api/auth/register should return 404
        $response = $this->postJson('/api/auth/register', $this->getRegisterPayload());
        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
        $this->assertStringContainsString('Pendaftaran belum dibuka', $response->json('message'));
    }

    public function test_registration_fails_when_timeline_is_closed(): void
    {
        SubmissionTimeline::create([
            'tahun_periode' => date('Y'),
            'opens_at' => now()->subDays(10),
            'closes_at' => now()->subDays(2),
            'is_locked' => false,
        ]);

        // GET /daftar should still return 200 because opens_at has passed (mode 1: "tanggal hari ini sama dengan atau telah melewati tanggal dibuka")
        $this->get('/daftar')->assertStatus(200);

        $response = $this->postJson('/api/auth/register', $this->getRegisterPayload());
        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
        $this->assertStringContainsString('Pendaftaran sudah ditutup', $response->json('message'));
    }

    public function test_registration_fails_when_timeline_is_locked(): void
    {
        SubmissionTimeline::create([
            'tahun_periode' => date('Y'),
            'opens_at' => now()->subDays(2),
            'closes_at' => now()->addDays(5),
            'is_locked' => true,
            'note' => 'Dikunci sementara',
        ]);

        // GET /daftar should still return 200 because opens_at has passed
        $this->get('/daftar')->assertStatus(200);

        $response = $this->postJson('/api/auth/register', $this->getRegisterPayload());
        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
        $this->assertStringContainsString('Pendaftaran saat ini dikunci oleh admin: Dikunci sementara', $response->json('message'));
    }

    public function test_registration_succeeds_when_timeline_is_open(): void
    {
        SubmissionTimeline::create([
            'tahun_periode' => date('Y'),
            'opens_at' => now()->subDays(1),
            'closes_at' => now()->addDays(5),
            'is_locked' => false,
        ]);

        // GET /daftar should return 200
        $this->get('/daftar')->assertStatus(200);

        $response = $this->postJson('/api/auth/register', $this->getRegisterPayload());
        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
    }
}
