<?php

namespace App\Http\Controllers;

use App\DTO\AssessmentDTO\BaselineDTO;
use App\DTO\AssessmentDTO\JawabanDTO;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\DTO\AssessmentDTO\AssessmentDTO;
use App\DTO\AssessmentDTO\QuestionDTO;
use App\Http\Requests\BaselinePesertaRequest;
use App\Services\AssessmentService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AssessmentController extends Controller
{
    use ApiResponse;

    protected $AssessmentService;

    public function __construct(AssessmentService $AssessmentService)
    {
        $this->AssessmentService = $AssessmentService;
    }


    private function getValidatedAssessment(string $mode)
    {
        $userId = AuthController::getAuthPeserta();

        if (!$userId) {
            throw new \Exception("Unauthorized: Silakan login terlebih dahulu.", 401);
        }

        $authDto = new AssessmentDTO(['user_id' => (int) $userId]);

        // Kita panggil fungsi validate di service yang bertindak sebagai dispatcher
        return $this->AssessmentService->validate($authDto, $mode);
    }

    private function getErrorCode(\Throwable $e)
    {
        $code = $e->getCode();
        // Pastikan code adalah HTTP status code yang valid
        return (is_numeric($code) && $code >= 400 && $code < 600) ? $code : 500;
    }

    private function getAuthDTO(): AssessmentDTO
    {
        $userId = AuthController::getAuthPeserta();
        return new AssessmentDTO($userId);
    }

    public function storeBaseline(BaselinePesertaRequest $request)
    // DONE
    {
        // profil
        try {
            $validatedData = $request->validated();
            $assessment = $this->getValidatedAssessment(AssessmentService::MODE_WRITE);

            $dto = new BaselineDTO((int) $assessment->user_id, $validatedData);

            // return $dto;
            // 2. Eksekusi Service (Orchestration)
            $result = $this->AssessmentService->upsertBaseline($dto);

            return $this->successResponse(null, 'Data baseline berhasil disimpan', 200);
        } catch (\Throwable $e) {
            $status = $e->getCode() == 403 ? 403 : 500;
            return $this->errorResponse($e->getMessage(), $status);
        }
    }

    /**
     * 1. Ambil Semua Pertanyaan (Single Form)
     * Menggantikan getQuestionsByCategory dan getSteps
     */
    public function getAllQuestions($assessmentId = null)
    {
        try {
            // Gunakan mode ANY agar status SUBMITTED tetap bisa melihat soal
            $assessment = $this->getValidatedAssessment(AssessmentService::MODE_ANY);

            $data = $this->AssessmentService->getAllQuestionsWithAnswers($assessment);

            return $this->successResponse($data, 'Data berhasil diambil', 200);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("GET_QUESTIONS_ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    public function getProfilePeserta($pesertaId)
    {
        try {
            $assessment = $this->getValidatedAssessment(AssessmentService::MODE_ANY);

            $profile = $this->AssessmentService->getProfilePeserta($pesertaId);

            return $this->successResponse($profile, 'Data berhasil diambil', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), $this->getErrorCode($th));
        }
    }

    public function saveJawaban(Request $request)
    {
        try {
            // 1. Otorisasi & Validasi Status via Helper (MODE_WRITE)
            // Jika status SUBMITTED/GRADED, akan otomatis lempar Exception 403
            $assessment = $this->getValidatedAssessment(AssessmentService::MODE_WRITE);

            $validated = $request->validate([
                'pertanyaan_id' => 'required|integer',
                'jawaban_id'    => 'nullable|integer',
                'jawaban_teks'  => 'nullable|string',
                'tautan_bukti'  => 'nullable|string',
                'note_reviewer' => 'nullable|string',
            ]);

            $dto = new JawabanDTO($assessment->id, $validated);

            // 3. Eksekusi Service
            $result = $this->AssessmentService->storeJawaban($dto);

            return $this->successResponse($result, 'Jawaban berhasil disimpan.', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    public function finalize(Request $request)
    {
        try {
            // PROTEKSI: Paksa harus mode WRITE
            $assessment = $this->getValidatedAssessment(AssessmentService::MODE_WRITE);

            $this->AssessmentService->lockAssessment($assessment);

            return $this->successResponse(null, 'Seluruh asesmen telah dikunci (Final Lock)', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }


    public function previewResults(Request $request)
    {
        try {
            // PROTEKSI: Paksa harus mode READ (Sudah Final)
            $assessment = $this->getValidatedAssessment(AssessmentService::MODE_READ);

            $previewData = $this->AssessmentService->calculateTotalPreview($assessment);

            return $this->successResponse($previewData, 'Estimasi skor total berhasil dihitung', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    /**
     * 5. Get Progress (Untuk Progress Bar)
     * Mengembalikan { total_questions: 40, answered_questions: 25 }
     */
    public function getProgress(Request $request)
    {
        try {
            $dto = $this->getAuthDTO();

            $progress = $this->AssessmentService->getCurrentProgress($dto);

            return $this->successResponse($progress, 'Data progres pengisian berhasil diambil', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * GET /api/assessment/peserta/questions/version
     * Lightweight endpoint untuk validasi cache rubrik di frontend (stale-while-revalidate).
     */
    public function getQuestionsVersion(Request $request)
    {
        try {
            $assessment = $this->getValidatedAssessment(AssessmentService::MODE_ANY);
            $data = $this->AssessmentService->getQuestionsVersion($assessment);
            return $this->successResponse($data, 'OK', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    /**
     * GET /api/assessment/peserta/hasil
     * Returns hasil (results) data for the dashboard with raw/validated scores
     */
    public function getHasil(Request $request)
    {
        try {
            $userId = AuthController::getAuthPeserta();
            $hasilData = $this->AssessmentService->getHasilData($userId);

            return $this->successResponse($hasilData, 'Data hasil berhasil diambil', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    /**
     * POST /api/assessment/peserta/save-draft
     * Save all answers in batch and update status to SUBMITTED
     */
    public function saveDraft(Request $request)
    {
        try {
            $assessment = $this->getValidatedAssessment(AssessmentService::MODE_WRITE);

            $validated = $request->validate([
                'answers' => 'required|array',
                'answers.*.pertanyaan_id' => 'required|integer',
                'answers.*.jawaban_id' => 'nullable|integer',
                'answers.*.jawaban_teks' => 'nullable|string',
                'answers.*.tautan_bukti' => 'nullable|url',
            ]);

            $result = $this->AssessmentService->saveDraftBatch($assessment, $validated['answers']);

            return $this->successResponse($result, 'Semua jawaban berhasil disimpan dan di-submit.', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    /**
     * GET /admin/api/assessment/{id}
     * Retrieve detailed assessment data for admin/reviewer view.
     */
    public function getAdminAssessmentDetail($id)
    {
        $assessment = \App\Models\Assessment::with(['institusi', 'identitas.agamas', 'jawabans.pertanyaan', 'jawabans.jawabanOpsi', 'user'])->findOrFail($id);
        $service = $this->AssessmentService;

        if ($assessment->reviewer_id) {
            try {
                $result = $service->getDetailReviewTasks($assessment->reviewer_id, $id);
                return response()->json(['success' => true, 'data' => $result]);
            } catch (\Throwable $e) {}
        }

        $allPertanyaan = app(\App\Repositories\PertanyaanRepository::class)->getPertanyaanWithOpsiJawaban();
        $identitas = $assessment->identitas;

        $jawabanMap = [];
        foreach ($assessment->jawabans as $jawaban) {
            $jawabanMap[$jawaban->pertanyaan_id] = [
                'jawaban_id' => $jawaban->jawaban_id,
                'jawaban_teks' => $jawaban->jawaban_teks,
                'tautan_bukti_drive' => $jawaban->tautan_bukti_drive,
                'skor_sistem' => $jawaban->skor_sistem,
                'skor_validasi_reviewer' => $jawaban->skor_validasi_reviewer,
                'opsi_dipilih' => $jawaban->jawabanOpsi ? [
                    'id' => $jawaban->jawabanOpsi->id,
                    'opsi_jawaban' => $jawaban->jawabanOpsi->opsi_jawaban,
                    'keterangan' => $jawaban->jawabanOpsi->keterangan,
                    'value' => $jawaban->jawabanOpsi->value,
                ] : null,
            ];
        }

        $rubrikData = [];
        foreach ($allPertanyaan as $pertanyaan) {
            $kategoriName = $pertanyaan->kategori->nama_kategori ?? 'Tanpa Kategori';
            if (!isset($rubrikData[$kategoriName])) {
                $rubrikData[$kategoriName] = [
                    'kategori' => $kategoriName,
                    'pertanyaan_count' => 0,
                    'bobot_maksimal' => 0,
                    'pertanyaan' => [],
                ];
            }
            $rubrikData[$kategoriName]['pertanyaan_count']++;
            $rubrikData[$kategoriName]['bobot_maksimal'] += 5;
            $rubrikData[$kategoriName]['pertanyaan'][] = [
                'id' => $pertanyaan->id,
                'kode_pertanyaan' => $pertanyaan->kode_pertanyaan,
                'teks_pertanyaan' => $pertanyaan->teks_pertanyaan,
                'kebutuhan_bukti' => $pertanyaan->kebutuhan_bukti,
                'tipe' => $pertanyaan->tipe,
                'opsi_jawaban' => $pertanyaan->OpsiJawaban->map(fn ($opsi) => [
                    'id' => $opsi->id,
                    'opsi_jawaban' => $opsi->opsi_jawaban,
                    'keterangan' => $opsi->keterangan,
                    'value' => $opsi->value,
                ])->toArray(),
                'jawaban_peserta' => $jawabanMap[$pertanyaan->id] ?? null,
            ];
        }

        return response()->json(['success' => true, 'data' => [
            'Assessment' => [
                'id' => $assessment->id,
                'status' => $assessment->status,
                'total_skor_sistem' => $assessment->total_skor_sistem,
                'total_skor_akhir' => $assessment->total_skor_akhir,
                'tahun_periode' => $assessment->tahun_periode,
            ],
            'institusi' => $assessment->institusi,
            'profil_peserta' => $identitas ? [
                'visi' => $identitas->visi,
                'misi' => $identitas->misi,
                'jml_fakultas' => $identitas->jml_fakultas,
                'jml_prodi' => $identitas->jml_prodi,
                'jml_dosen' => $identitas->jml_dosen,
                'jml_tendik' => $identitas->jml_tendik,
                'jml_mhs' => $identitas->jml_mahasiswa,
                'jml_ukm' => $identitas->jml_ukm,
                'jml_ormawa' => $identitas->jml_ormawa ?? 0,
                'berkas_pendukung' => $identitas->legal_documents,
                'agama' => $identitas->agamas->mapWithKeys(fn ($item) => [strtolower($item->agama) => $item->jumlah]),
            ] : null,
            'rubrik' => array_values($rubrikData),
            'nama_pic' => $assessment->nama_pic,
            'jabatan_pic' => $assessment->jabatan_pic,
            'no_hp_pic' => $assessment->no_hp_pic,
            'email_pic' => $assessment->user->email ?? null,
        ]]);
    }
}

