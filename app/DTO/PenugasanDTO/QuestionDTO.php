<?php

namespace App\DTO\PenugasanDTO;

class QuestionDTO
{
    // Menggunakan readonly agar data kebal terhadap modifikasi liar di tengah jalan
    public readonly int $penugasanId;

    public function __construct(int $penugasanId)
    {
        $this->penugasanId = $penugasanId;
    }
}
