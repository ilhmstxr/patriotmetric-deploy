<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponPenugasan extends Model
{
    /** @use HasFactory<\Database\Factories\ResponPenugasanFactory> */
    protected $table = 'respon_penugasans';
    use HasFactory;

    protected $fillable = [
        'penugasan_id',
        'pertanyaan_id',
        'jawaban_id',
        'jawaban_teks',
        'tautan_bukti_drive',
        'skor_sistem',
        'skor_validasi_reviewer',
        'note_reviewer'
    ];

    protected function casts(): array
    {
        return [
            'jawaban_teks' => 'array',
            'skor_validasi_reviewer' => 'array',
            'note_reviewer' => 'array',
        ];
    }

    public function getResolvedReviewerScoreAttribute()
    {
        $scores = $this->skor_validasi_reviewer;
        if (!is_array($scores)) {
            return $scores;
        }

        $r1 = isset($scores['r1']) ? (float)$scores['r1'] : null;
        $r2 = isset($scores['r2']) ? (float)$scores['r2'] : null;
        $r3 = isset($scores['r3']) ? (float)$scores['r3'] : null;

        if ($r3 !== null) {
            if ($r1 !== null && $r2 !== null) {
                $diff1 = abs($r1 - $r3);
                $diff2 = abs($r2 - $r3);
                if ($diff1 < $diff2) {
                    return $r1;
                } elseif ($diff2 < $diff1) {
                    return $r2;
                } else {
                    return $r1;
                }
            }
            return $r3;
        }

        if ($r1 !== null && $r2 !== null) {
            return round(($r1 + $r2) / 2, 2);
        }

        return $r1 ?? $r2 ?? null;
    }

    public function Penugasan()
    {
        return $this->belongsTo(Penugasan::class, 'penugasan_id');
    }

    public function jawabanOpsi()
    {
        return $this->belongsTo(OpsiJawaban::class, 'jawaban_id');
    }
    public function pertanyaan()
    {
        return $this->belongsTo(Pertanyaan::class, 'pertanyaan_id');
    }
}
