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
        'note_reviewer',
        'reviewer_grades_json'
    ];

    protected function casts(): array
    {
        return [
            'jawaban_teks' => 'array',
            'reviewer_grades_json' => 'array',
        ];
    }

    public function penugasan()
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
