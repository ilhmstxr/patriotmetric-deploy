<?php

namespace App\DTO\PenugasanDTO;

class QuestionDTO
{
    public readonly int $penugasanId;

    public function __construct(int $penugasanId)
    {
        $this->penugasanId = $penugasanId;
    }
}
