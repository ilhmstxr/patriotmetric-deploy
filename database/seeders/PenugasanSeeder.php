<?php

namespace Database\Seeders;

use App\Models\Penugasan;
use App\Models\Institusi;
use App\Models\User;
use Illuminate\Database\Seeder;

class PenugasanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institusi = Institusi::first() ?? Institusi::factory()->create();
        $user = User::where('role', 'PESERTA')->first() ?? User::factory()->create(['role' => 'PESERTA']);

        Penugasan::factory()->create([
            'institution_id' => $institusi->id,
            'user_id' => $user->id,
            'tahun_periode' => date('Y'),
        ]);

        // Generate several random penugasans
        Penugasan::factory(5)->create();
    }
}
