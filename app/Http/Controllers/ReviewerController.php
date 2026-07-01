<?php

namespace App\Http\Controllers;

use App\Services\PenugasanService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewerController extends Controller
{
    use ApiResponse;

    protected $penugasanService;
    protected $reviewerRepository;
    protected $penugasanRepository;

    public function __construct(
        PenugasanService $penugasanService,
        \App\Repositories\ReviewerRepository $reviewerRepository,
        \App\Repositories\PenugasanRepository $penugasanRepository
    ) {
        $this->penugasanService = $penugasanService;
        $this->reviewerRepository = $reviewerRepository;
        $this->penugasanRepository = $penugasanRepository;
    }

    private function getErrorCode(\Throwable $e)
    {
        $code = $e->getCode();
        return (is_numeric($code) && $code >= 400 && $code < 600) ? $code : 500;
    }

    /**
     * Endpoint: GET /api/reviewer/tasks
     * Mengambil daftar tugas (plottingan) institusi yang harus dinilai.
     */
    public function getAssignedTasks(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user || strtolower($user->role) !== 'reviewer') {
                throw new \Exception("Unauthorized: Akses khusus untuk Reviewer.", 403);
            }
            
            $reviewer = $this->reviewerRepository->findByUserId($user->id);
            if (!$reviewer) {
                throw new \Exception("Profil Reviewer tidak ditemukan.", 404);
            }
            $reviewerId = $reviewer->id;

            // Eksekusi Service
            $result = $this->penugasanService->getAssignedReviews($reviewerId);

            return $this->successResponse($result, 'Daftar plottingan tugas berhasil diambil.', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    public function getDetailTasks(Request $request, $pesertaId)
    {
        try {
            $user = $request->user();
            if (!$user || strtolower($user->role) !== 'reviewer') {
                throw new \Exception("Unauthorized: Akses khusus untuk Reviewer.", 403);
            }
            
            $reviewer = $this->reviewerRepository->findByUserId($user->id);
            if (!$reviewer) {
                throw new \Exception("Profil Reviewer tidak ditemukan.", 404);
            }
            $reviewerId = $reviewer->id;

            // Eksekusi Service
            $result = $this->penugasanService->getDetailReviewTasks($reviewerId, (int) $pesertaId);

            return $this->successResponse($result, 'Daftar plottingan tugas berhasil diambil.', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    /**
     * Endpoint: POST /api/penugasan/reviewer/tasks/{pesertaId}/save-scores
     * Menyimpan skor validasi reviewer per indikator dan mengupdate rekap skor JSON.
     * Body: { scores: { pertanyaan_id: skor, ... }, notes: { pertanyaan_id: catatan, ... } }
     */
    public function saveScores(Request $request, $pesertaId)
    {
        try {
            $user = $request->user();
            if (!$user || strtolower($user->role) !== 'reviewer') {
                throw new \Exception("Unauthorized: Akses khusus untuk Reviewer.", 403);
            }

            $penugasan = $this->penugasanRepository->find($pesertaId);
            if (!$penugasan || !in_array($penugasan->status, ['SUBMITTED', 'IN_PROGRESS', 'GRADED'])) {
                 throw new \Exception("Penugasan tidak ditemukan atau tidak dapat dinilai.", 404);
            }

            $scores = $request->input('scores', []);
            $notes  = $request->input('notes', []);

            $this->penugasanService->saveReviewerScores($penugasan, $scores, $notes);

            return $this->successResponse([], 'Skor berhasil disimpan dan rekap diperbarui.');
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    /**
     * Endpoint: POST /api/penugasan/reviewer/tasks/{pesertaId}/finalize
     * Memfinalisasi penilaian: set status GRADED dan lock rekap skor akhir.
     */
    public function finalizeReview(Request $request, $pesertaId)
    {
        try {
            $user = $request->user();
            if (!$user || strtolower($user->role) !== 'reviewer') {
                throw new \Exception("Unauthorized: Akses khusus untuk Reviewer.", 403);
            }

            $penugasan = $this->penugasanRepository->find($pesertaId);
            if (!$penugasan || !in_array($penugasan->status, ['SUBMITTED', 'IN_PROGRESS'])) {
                 throw new \Exception("Penugasan tidak ditemukan atau tidak dapat difinalisasi.", 404);
            }

            $this->penugasanService->finalizeReview($penugasan);

            return $this->successResponse([], 'Penilaian berhasil difinalisasi. Status peserta berubah menjadi GRADED.');
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }
}
