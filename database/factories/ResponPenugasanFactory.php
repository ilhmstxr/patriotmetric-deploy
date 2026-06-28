<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Penugasan;
use App\Models\ResponPenugasan;
use App\Models\Pertanyaan;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResponPenugasan>
 */
class ResponPenugasanFactory extends Factory
{
    protected $model = ResponPenugasan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'penugasan_id' => Penugasan::factory(),
            'pertanyaan_id' => Pertanyaan::factory(),
            'jawaban_teks' => $this->faker->paragraph(),
            'tautan_bukti_drive' => $this->faker->url(),
            'skor_sistem' => $this->faker->randomFloat(2, 0, 100),
            'skor_validasi_reviewer' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
