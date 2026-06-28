<?php

namespace App\Repositories;

use App\Models\Kategori;
use App\Models\Penugasan;
use App\Models\ResponPenugasan;
use App\Models\Pertanyaan;

class ReviewRepository extends BaseRepository
{
    public function __construct(Penugasan $model)
    {
        parent::__construct($model);
    }

    public function assignReviewerToSubmissions($reviewerId, array $submissionIds)
    {
        return $this->model->whereIn('id', $submissionIds)->update([
            'reviewer_id' => $reviewerId
        ]);
    }

    public function getAssignedSubmissionsWithUser($reviewerId)
    {
        return $this->model->with('user')->where('reviewer_id', $reviewerId)->get();
    }

    public function getAnswerBySubmissionAndQuestion($submissionId, $questionId)
    {
        return ResponPenugasan::where('penugasan_id', $submissionId)
            ->where('pertanyaan_id', $questionId)
            ->first();
    }

    public function hasUnverifiedAnswers($submissionId)
    {
        // Completeness Check (Zero-Gap Validation): Pengecekan silang antara total pertanyaan vs total jawaban yang sudah diberikan manual_score
        $totalQuestions = Pertanyaan::count();

        $verifiedAnswers = ResponPenugasan::where('penugasan_id', $submissionId)
            ->whereNotNull('manual_score')
            ->count();

        // Mengembalikan true jika ada indikator/pertanyaan yang tertinggal (belum diverifikasi dgn manual score)
        return $verifiedAnswers < $totalQuestions;
    }

    public function sumVerifiedScore($submissionId)
    {
        return ResponPenugasan::where('penugasan_id', $submissionId)
            ->sum('manual_score'); // Or whatever the valid fallback is
    }

    public function updateStatus($id, $status, $calculatedTotal)
    {
        $penugasan = $this->model->findOrFail($id);
        $penugasan->update([
            'status' => $status,
            'final_score' => $calculatedTotal,
            // (opsional: Timestamping jika direquire)
            'reviewed_at' => now(),
        ]);
        return $penugasan;
    }

    public function publishStatus($submissionId)
    {
        $penugasan = $this->model->findOrFail($submissionId);
        $penugasan->update([
            'status' => 'PUBLISHED'
        ]);
        return $penugasan;
    }

    public function getVerifiedAnswers($submissionId)
    {
        return ResponPenugasan::where('penugasan_id', $submissionId)
            ->where('skor_validasi_reviewer', '>', 0)
            ->get();
    }

    public function getAllWithProgress($submissionId)
    {
        return Kategori::withCount([
            'pertanyaans as questions_count',
            'jawabans as answers_count' => function ($query) use ($submissionId) {
                $query->where('penugasan_id', $submissionId)
                    ->whereNotNull('skor_validasi_reviewer');
            }
        ])->get();
    }

    public function getByCategoryWithExistingAnswers($categoryId, $submissionId)
    {
        return Pertanyaan::where('category_id', $categoryId)
            ->with(['jawaban' => function ($q) use ($submissionId) {
                $q->where('penugasan_id', $submissionId);
            }])
            ->get();
    }

    public function getWithReviewerContext($subId, $catId)
    {
        return ResponPenugasan::where('penugasan_id', $subId)
            ->whereHas('pertanyaan', function ($q) use ($catId) {
                $q->where('category_id', $catId);
            })
            ->with('pertanyaan') // Include question options/guideline
            ->get();
    }

    public function updateReviewData($submissionId, array $verifications)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($submissionId, $verifications) {
            foreach ($verifications as $ver) {
                // Sesuai dokumen "AssessmentAnswer::where('id', $id)->update(['reviewer_scale' => $scale, 'manual_score' => $score, 'verified_at' => now()])"
                // Mengubah existing answer dari tabel respon_penugasan
                ResponPenugasan::where('id', $ver['id'])
                    ->where('penugasan_id', $submissionId) // Mengunci untuk re-verify penugasan yg tepat
                    ->update([
                        'reviewer_scale' => $ver['scale_choice'],
                        'manual_score' => $ver['manual_score'],
                        // Memastikan fallback/alias jika score validation yg utama di sistem ini disamakan:
                        'skor_validasi_reviewer' => $ver['manual_score'],
                        'verified_at' => now(), // Audit Trail
                    ]);
            }
        });
    }

    public function getAnswersByCategory($submissionId, $categoryId)
    {
        return ResponPenugasan::where('penugasan_id', $submissionId)
            ->whereHas('pertanyaan', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })->get();
    }
}
