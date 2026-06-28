<?php

namespace Database\Factories;

use App\Models\Institusi;
use App\Models\User;
use App\Models\Penugasan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Penugasan>
 */
class PenugasanFactory extends Factory
{
    protected $model = Penugasan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['UNVERIFIED', 'ACTIVE', 'IN_PROGRESS', 'SUBMITTED', 'GRADED', 'PUBLISHED']);

        return [
            'institution_id' => Institusi::factory(),
            'user_id' => User::factory(),
            'nama_pic' => $this->faker->name(),
            'jabatan_pic' => $this->faker->jobTitle(),
            'no_hp_pic' => $this->faker->phoneNumber(),
            'tahun_periode' => $this->faker->year(),
            'status' => $status,
            'reviewer_id' => in_array($status, ['GRADED', 'IN_PROGRESS', 'SUBMITTED', 'PUBLISHED']) ? User::factory() : null,
            'total_skor_sistem' => $this->faker->randomFloat(2, 0, 100),
            'total_skor_akhir' => in_array($status, ['GRADED', 'PUBLISHED']) ? $this->faker->randomFloat(2, 0, 100) : 0,
        ];
    }
}
