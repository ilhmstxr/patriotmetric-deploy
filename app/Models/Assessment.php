<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    /** @use HasFactory<\Database\Factories\AssessmentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reviewer_id',
        'reviewer_1_id',
        'reviewer_2_id',
        'reviewer_3_id',
        'nilai_reviewer_1',
        'nilai_reviewer_2',
        'nilai_reviewer_3',
        'nilai_rata_rata',
        'institution_id',
        'tahun_periode',
        'status',
        'total_skor_sistem',
        'total_skor_akhir',
        'skor_rekap_json',
        'nama_pic',
        'jabatan_pic',
        'no_hp_pic'
    ];

    protected $casts = [
        'skor_rekap_json' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $nilai_1 = (float) ($model->nilai_reviewer_1 ?? 0);
            $nilai_2 = (float) ($model->nilai_reviewer_2 ?? 0);
            $model->nilai_rata_rata = round(($nilai_1 + $nilai_2) / 2, 2);

            $nilai_3 = (float) ($model->nilai_reviewer_3 ?? 0);
            if ($nilai_3 > 0) {
                $threshold = (float) config('rubrik.reviewer_dispute_threshold', 100);
                $isDispute = abs($nilai_1 - $nilai_2) >= $threshold;

                if ($isDispute) {
                    // Jika ada anomali/flag, nilai akhir ditentukan oleh Reviewer 3
                    $model->total_skor_akhir = $nilai_3;
                } else {
                    // Jika tidak ada anomali, pakai nilai R1 atau R2 yang paling mendekati R3
                    $diff1 = abs($nilai_1 - $nilai_3);
                    $diff2 = abs($nilai_2 - $nilai_3);

                    if ($diff1 < $diff2) {
                        $model->total_skor_akhir = $nilai_1;
                    } elseif ($diff2 < $diff1) {
                        $model->total_skor_akhir = $nilai_2;
                    } else {
                        // Jika selisihnya sama, ambil Reviewer 1
                        $model->total_skor_akhir = $nilai_1;
                    }
                }
            } else {
                $model->total_skor_akhir = $model->nilai_rata_rata;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Reviewer::class, 'reviewer_id');
    }

    public function reviewer1()
    {
        return $this->belongsTo(Reviewer::class, 'reviewer_1_id');
    }

    public function reviewer2()
    {
        return $this->belongsTo(Reviewer::class, 'reviewer_2_id');
    }

    public function reviewer3()
    {
        return $this->belongsTo(Reviewer::class, 'reviewer_3_id');
    }

    public function jawabans()
    {
        return $this->hasMany(ResponAssessment::class, 'assessment_id');
    }

    public function identitas()
    {
        return $this->hasOne(Identitas::class, 'Assessment_id');
    }

    public function institusi()
    {
        return $this->belongsTo(Institusi::class, 'institution_id');
    }
}
