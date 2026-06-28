<?php

namespace App\Http\Controllers;

use App\DTO\PenugasanDTO\BaselineDTO;
use App\DTO\PenugasanDTO\JawabanDTO;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\DTO\PenugasanDTO\PenugasanDTO;
use App\DTO\PenugasanDTO\QuestionDTO;
use App\Http\Requests\BaselinePesertaRequest;
use App\Services\PenugasanService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PenugasanController extends Controller
{
    use ApiResponse;

    protected $penugasanService;

    public function __construct(PenugasanService $penugasanService)
    {
        $this->penugasanService = $penugasanService;
    }

    private function getValidatedPenugasan(string $mode)
    {
        $userId = AuthController::getAuthPeserta();

        if (!$userId) {
            throw new \Exception("Unauthorized: Silakan login terlebih dahulu.", 401);
        }

        $authDto = new PenugasanDTO(['user_id' => (int) $userId]);

        return $this->penugasanService->validate($authDto, $mode);
    }

    private function getErrorCode(\Throwable $e)
    {
        $code = $e->getCode();
        return (is_numeric($code) && $code >= 400 && $code < 600) ? $code : 500;
    }

    private function getAuthDTO(): PenugasanDTO
    {
        $userId = AuthController::getAuthPeserta();
        return new PenugasanDTO($userId);
    }

    public function storeBaseline(BaselinePesertaRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $penugasan = $this->getValidatedPenugasan(PenugasanService::MODE_WRITE);

            $dto = new BaselineDTO((int) $penugasan->user_id, $validatedData);

            $result = $this->penugasanService->upsertBaseline($dto);

            return $this->successResponse(null, 'Data baseline berhasil disimpan', 200);
        } catch (\Throwable $e) {
            $status = $e->getCode() == 403 ? 403 : 500;
            return $this->errorResponse($e->getMessage(), $status);
        }
    }

    public function getAllQuestions($penugasanId = null)
    {
        try {
            $penugasan = $this->getValidatedPenugasan(PenugasanService::MODE_ANY);

            $data = $this->penugasanService->getAllQuestionsWithAnswers($penugasan);

            return $this->successResponse($data, 'Data berhasil diambil', 200);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("GET_QUESTIONS_ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    public function getProfilePeserta($pesertaId)
    {
        try {
            $penugasan = $this->getValidatedPenugasan(PenugasanService::MODE_ANY);

            $profile = $this->penugasanService->getProfilePeserta($pesertaId);

            return $this->successResponse($profile, 'Data berhasil diambil', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), $this->getErrorCode($th));
        }
    }

    public function saveJawaban(Request $request)
    {
        try {
            $penugasan = $this->getValidatedPenugasan(PenugasanService::MODE_WRITE);

            $validated = $request->validate([
                'pertanyaan_id' => 'required|integer',
                'jawaban_id'    => 'nullable|integer',
                'jawaban_teks'  => 'nullable|string',
                'tautan_bukti'  => 'nullable|string',
                'note_reviewer' => 'nullable|string',
            ]);

            $dto = new JawabanDTO($penugasan->id, $validated);

            $result = $this->penugasanService->storeJawaban($dto);

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
            $penugasan = $this->getValidatedPenugasan(PenugasanService::MODE_WRITE);

            $this->penugasanService->lockAssessment($penugasan);

            return $this->successResponse(null, 'Seluruh penugasan telah dikunci (Final Lock)', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    public function previewResults(Request $request)
    {
        try {
            $penugasan = $this->getValidatedPenugasan(PenugasanService::MODE_READ);

            $previewData = $this->penugasanService->calculateTotalPreview($penugasan);

            return $this->successResponse($previewData, 'Estimasi skor total berhasil dihitung', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    public function getProgress(Request $request)
    {
        try {
            $dto = $this->getAuthDTO();

            $progress = $this->penugasanService->getCurrentProgress($dto);

            return $this->successResponse($progress, 'Data progres pengisian berhasil diambil', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function getQuestionsVersion(Request $request)
    {
        try {
            $penugasan = $this->getValidatedPenugasan(PenugasanService::MODE_ANY);
            $data = $this->penugasanService->getQuestionsVersion($penugasan);
            return $this->successResponse($data, 'OK', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    public function getHasil(Request $request)
    {
        try {
            $userId = AuthController::getAuthPeserta();
            $hasilData = $this->penugasanService->getHasilData($userId);

            return $this->successResponse($hasilData, 'Data hasil berhasil diambil', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), $this->getErrorCode($e));
        }
    }

    public function saveDraft(Request $request)
    {
        try {
            $penugasan = $this->getValidatedPenugasan(PenugasanService::MODE_WRITE);

            $validated = $request->validate([
                'answers' => 'required|array',
                'answers.*' => 'required|array',
                'answers.*.pertanyaan_id' => 'required|integer',
                'answers.*.jawaban_id' => 'nullable|integer',
                'answers.*.jawaban_teks' => 'nullable|string',
                'answers.*.tautan_bukti' => 'nullable|url',
            ]);

            $result = $this->penugasanService->saveDraftBatch($penugasan, $validated['answers']);

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
                'note_reviewer' => $jawaban->note_reviewer,
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
                $prefix = strtoupper(substr(trim($kategoriName), 0, 2));
                $bobot = match ($prefix) {
                    'A.' => 20.0,
                    'B.' => 30.0,
                    'C.' => 50.0,
                    default => 0.0,
                };

                $rubrikData[$kategoriName] = [
                    'kategori' => $kategoriName,
                    'pertanyaan_count' => 0,
                    'bobot_maksimal' => 0,
                    'bobot_persentase' => $bobot,
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

