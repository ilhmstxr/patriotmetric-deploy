<?php

namespace App\Services;

use App\Repositories\PenugasanRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use App\DTO\PenugasanDTO\PenugasanDTO;
use App\DTO\PenugasanDTO\BaselineDTO;
use App\DTO\PenugasanDTO\JawabanDTO;
use App\Repositories\PertanyaanRepository;
use Illuminate\Support\Str;

/**
 * @property \App\Repositories\PenugasanRepository $repository
 */
class PenugasanService extends BaseService
{
    protected $pertanyaanRepository;
    protected $timelineRepository;

    public function __construct(
        PenugasanRepository $penugasanRepository,
        PertanyaanRepository $pertanyaanRepository,
        \App\Repositories\TimelineRepository $timelineRepository
    ) {
        // Parent constructor mengikat PenugasanRepository sebagai repository utama
        parent::__construct($penugasanRepository);
        $this->pertanyaanRepository = $pertanyaanRepository;
        $this->timelineRepository = $timelineRepository;
    }

    const MODE_READ = 'read';
    const MODE_WRITE = 'write';
    const MODE_ANY = 'any';

    // DONE
    private function ensureUserIsActive($userId)
    {
        if (!$this->repository->isUserActive($userId)) {
            throw new Exception("Akses Ditolak: Akun Anda tidak aktif atau ditangguhkan.", 403);
        }
    }

    // DONE
    public function validate(PenugasanDTO $dto, string $mode)
    {
        // 1. Pastikan User Aktif
        $this->ensureUserIsActive($dto->userId);

        // 2. Ambil Sesi Penugasan Aktif
        $penugasan = $this->repository->findActivePenugasanByUserId($dto->userId);
        if (!$penugasan) {
            throw new Exception("Sesi penugasan aktif tidak ditemukan untuk tahun berjalan.", 404);
        }

        // 3. Dispatcher Mode
        return match ($mode) {
            self::MODE_WRITE => $this->guardUpdateAccess($penugasan),
            self::MODE_READ => $this->guardReadAccess($penugasan),
            self::MODE_ANY => $penugasan,
            default => throw new Exception("Mode validasi tidak dikenali.", 500),
        };
    }

    // DONE
    private function guardUpdateAccess($penugasan)
    {
        if (!in_array($penugasan->status, ['ACTIVE', 'IN_PROGRESS'])) {
            throw new Exception("Gagal: Penugasan berstatus {$penugasan->status} tidak dapat diubah.", 403);
        }

        $timelineCheck = $this->timelineRepository->canSubmit($penugasan->tahun_periode);
        if (!$timelineCheck['allowed']) {
            throw new Exception("Gagal: " . $timelineCheck['reason'], 403);
        }

        return $penugasan;
    }

    // DONE
    private function guardReadAccess($penugasan)
    {
        // Izinkan pembacaan jika sudah dikirim atau dinilai
        if (!in_array($penugasan->status, ['SUBMITTED', 'GRADED', 'PUBLISHED'])) {
            throw new Exception("Gagal: Hasil penugasan belum tersedia untuk dilihat.", 403);
        }
        return $penugasan;
    }

    // CHECK
    public function upsertBaseline(BaselineDTO $dto)
    {
        $penugasan = $this->repository->findActivePenugasanByUserId($dto->userId);
        if (!$penugasan)
            throw new Exception("Sesi penugasan aktif tidak ditemukan.", 404);

        return DB::transaction(function () use ($dto, $penugasan) {

            // 1. DATA HYDRATION: Ambil data lama
            $existingIdentitas = $this->repository->findIdentitasByPenugasanId($penugasan->id);
            $existingInstitusi = $this->repository->findInstitusiById($penugasan->institution_id);

            // 2. Update Institusi
            $this->repository->updateInstitusi($penugasan->institution_id, [
                'nama_institusi' => $dto->namaInstitusi ?? $existingInstitusi?->nama_institusi,
                'jenis_institusi' => $dto->jenisInstitusi ?? $existingInstitusi?->jenis_institusi
            ]);

            // 3. Proses File Upload
            $finalDocumentsJson = $existingIdentitas?->legal_documents ?? [];

            if (!$existingIdentitas && !empty($dto->legalDocuments)) {
                // Eksekusi pemindahan file ke Storage
                $documentPaths = $this->processLegalDocuments($dto->legalDocuments, $penugasan);
                $finalDocumentsJson = $documentPaths;
            }

            // 4. Update Identitas
            $identitasData = [
                'penugasan_id' => $penugasan->id,
                'jml_mahasiswa' => $dto->jmlMhs ?? $existingIdentitas?->jml_mahasiswa ?? 0,
                'jml_dosen' => $dto->jmlDosen ?? $existingIdentitas?->jml_dosen ?? 0,
                'jml_tendik' => $dto->jmlTendik ?? $existingIdentitas?->jml_tendik ?? 0,
                'jml_prodi' => $dto->jmlProdi ?? $existingIdentitas?->jml_prodi ?? 0,
                'jml_ukm' => $dto->jmlUkm ?? $existingIdentitas?->jml_ukm ?? 0,
                'jml_fakultas' => $dto->jmlFakultas ?? $existingIdentitas?->jml_fakultas ?? 0,
                'visi' => $dto->visi ?? $existingIdentitas?->visi ?? null,
                'misi' => $dto->misi ?? $existingIdentitas?->misi ?? null,
                'legal_documents' => $finalDocumentsJson,
                'is_verified' => $existingIdentitas?->is_verified ?? false,
            ];

            $identitas = $this->repository->upsertIdentitas($penugasan->id, $identitasData);

            // 5. Update Agama secara Selektif
            if (!empty($dto->dataAgama)) {
                foreach ($dto->dataAgama as $namaAgama => $jumlah) {
                    $allowedAgama = ['islam', 'kristen', 'katolik', 'hindu', 'buddha', 'konghucu'];
                    if (in_array(strtolower($namaAgama), $allowedAgama)) {
                        $this->repository->upsertAgama($identitas->id, strtolower($namaAgama), $jumlah);
                    }
                }
            }

            return $identitas;
        });
    }

    // DONE
    private function processLegalDocuments(array $documents, $penugasan): array
    {
        $paths = [];
        $safeFolderName = Str::slug($penugasan->institusi?->nama_institusi ?? 'unknown') . '-' . $penugasan->tahun_periode;
        $directoryPath = 'lampiran-peserta/' . $safeFolderName;

        foreach ($documents as $key => $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFileName = time() . '_' . Str::slug($originalName) . '.' . $file->getClientOriginalExtension();

            $storedPath = $file->storeAs($directoryPath, $safeFileName, 'public');
            $paths[$key] = '/storage/' . $storedPath;
        }

        return $paths;
    }

    /**
     * 1. Ambil Semua Pertanyaan & Jawaban (Single Form)
     */
    public function getAllQuestionsWithAnswers($penugasan)
    {
        $questions = $this->pertanyaanRepository->getAllQuestionsWithExistingAnswers($penugasan);

        if ($questions->isEmpty()) {
            throw new Exception("Master data pertanyaan belum dikonfigurasi oleh Admin.", 404);
        }

        $timelineCheck = $this->timelineRepository->canSubmit($penugasan->tahun_periode);
        $isEditEnabled = $timelineCheck['allowed'];
        $lockReason = $timelineCheck['reason'] ?? null;

        // Override: jika status sudah SUBMITTED/GRADED/PUBLISHED, selalu lock
        if (in_array($penugasan->status, ['SUBMITTED', 'GRADED', 'PUBLISHED'])) {
            $isEditEnabled = false;
            $lockReason = match ($penugasan->status) {
                'SUBMITTED' => 'Formulir dikunci karena data sudah disubmit dan sedang menunggu review.',
                'GRADED'    => 'Formulir dikunci karena penilaian sudah selesai dilakukan.',
                'PUBLISHED' => 'Formulir dikunci karena hasil sudah dipublikasikan.',
                default     => 'Formulir dikunci.',
            };
        }

        return [
            'penugasan_id' => $penugasan->id,
            'status' => $penugasan->status,
            'is_edit_enabled' => $isEditEnabled,
            'lock_reason'     => $lockReason,
            'questions'       => $questions,
            'profil'          => $this->getProfilData($penugasan),
        ];
    }

    /**
     * Mengambil data profil identitas peserta beserta jumlah agama aktif.
     * Digunakan untuk kalkulasi formula preview di sisi frontend.
     */
    private function getProfilData($penugasan): array
    {
        $identitas = $this->repository->getIdentitasWithAgama($penugasan->id);

        return [
            'jml_mahasiswa' => $identitas?->jml_mahasiswa ?? 0,
            'jml_dosen' => $identitas?->jml_dosen ?? 0,
            'jml_tendik' => $identitas?->jml_tendik ?? 0,
            'jml_prodi' => $identitas?->jml_prodi ?? 0,
            'jml_ukm' => $identitas?->jml_ukm ?? 0,
            'jml_fakultas' => $identitas?->jml_fakultas ?? 0,
            'jml_ormawa' => $identitas?->jml_ormawa ?? 0,
            'jml_agama_aktif' => $identitas
                ? $identitas->agamas->where('jumlah', '>', 0)->count()
                : 0,
        ];
    }

    // DONE
    public function getAllPertanyaan()
    {
        return $this->pertanyaanRepository->getPertanyaanWithOpsiJawaban();
    }

    public function getProfilePeserta(int $pesertaId)
    {
        return $this->repository->getProfilePeserta($pesertaId);
    }

    public function storeJawaban(JawabanDTO $dto)
    {
        // 1. Fetch info pertanyaan
        $pertanyaan = $this->pertanyaanRepository->findPertanyaanById($dto->pertanyaanId);
        if (!$pertanyaan)
            throw new Exception("Pertanyaan tidak ditemukan.", 404);

        // 2. Build Payload Dasar
        $payload = [
            'penugasan_id' => $dto->submissionId,
            'pertanyaan_id' => $dto->pertanyaanId,
        ];

        // 3. Jalankan Auto-Sync
        $syncData = $this->jawabanAutoSync($dto, $pertanyaan);
        $payload = array_merge($payload, $syncData);

        // 4. KONDISI 3 & 4: Tambahan Bukti dan Note
        $payload['tautan_bukti_drive'] = $dto->tautanBukti;
        $payload['note_reviewer'] = $dto->noteReviewer;

        // 5. Aturan skor
        $jawabanTeksFilled = false;
        if (isset($payload['jawaban_teks'])) {
            $teks = $payload['jawaban_teks'];
            if (is_array($teks)) {
                if (isset($teks['total_poin'])) {
                    $jawabanTeksFilled = true;
                } else {
                    $raw = $teks['raw_input'] ?? '';
                    $jawabanTeksFilled = trim((string) $raw) !== '';
                }
            } else {
                $jawabanTeksFilled = trim((string) $teks) !== '';
            }
        }
        $hasJawaban = !empty($payload['jawaban_id']) || $jawabanTeksFilled;

        $hasTautan = !empty($payload['tautan_bukti_drive']) && trim((string) $payload['tautan_bukti_drive']) !== '';
        $isOtomatis = ($pertanyaan->tipe ?? null) === 'otomatis_sistem';
        $needsBukti = !empty($pertanyaan->kebutuhan_bukti);
        if ((!$isOtomatis || $needsBukti) && (!$hasJawaban || !$hasTautan)) {
            $payload['skor_sistem'] = 0;
        }

        // 6. Delegasi ke Repository (Upsert)
        return $this->repository->upsertJawaban($payload);
    }

    /**
     * Mengembalikan token versi untuk validasi cache rubrik di frontend.
     */
    public function getQuestionsVersion($penugasan): array
    {
        $latestPertanyaan = $this->pertanyaanRepository->getLatestPertanyaanUpdate();
        $latestJawaban = $this->repository->getLatestJawabanUpdate($penugasan->id);

        $components = array_filter([$latestPertanyaan, $latestJawaban, $penugasan->updated_at]);
        $version = $components ? max($components) : null;

        return [
            'version' => $version ? (string) $version : null,
            'pertanyaan_updated_at' => $latestPertanyaan ? (string) $latestPertanyaan : null,
            'jawaban_updated_at' => $latestJawaban ? (string) $latestJawaban : null,
            'penugasan_status' => $penugasan->status,
        ];
    }

    /**
     * Private Helper: Sinkronisasi Dua Arah & Kalkulasi Skor Real-time
     */
    private function jawabanAutoSync(JawabanDTO $dto, $pertanyaan): array
    {
        $data = [];

        if ($pertanyaan->tipe === 'pilihan_ganda') {
            $data['jawaban_id'] = $dto->jawabanId;

            $opsi = $this->pertanyaanRepository->findOpsiById($dto->jawabanId);
            $teksKeterangan = $opsi ? $opsi->keterangan : null;

            $data['jawaban_teks'] = [
                'raw_input' => $teksKeterangan,
                'calculated_percentage' => null
            ];

            $data['skor_sistem'] = $opsi ? (int) $opsi->opsi_jawaban : 0;
        } else {
            $rawJawabanTeks = $dto->jawabanTeks ?? $dto->jawabanId;

            if (($pertanyaan->kode_pertanyaan ?? null) === 'B.13') {
                $decoded = is_string($rawJawabanTeks) ? json_decode($rawJawabanTeks, true) : $rawJawabanTeks;

                $data['jawaban_teks'] = is_array($decoded) ? $decoded : ['raw_input' => $rawJawabanTeks];

                $totalPoin = is_array($decoded) ? (float) ($decoded['total_poin'] ?? 0) : 0;
                $matchingOpsi = $this->pertanyaanRepository->findMatchingOpsiByValue(
                    $dto->pertanyaanId,
                    $totalPoin
                );

                $data['jawaban_id'] = $matchingOpsi ? $matchingOpsi->id : null;
                $data['skor_sistem'] = $matchingOpsi ? (int) $matchingOpsi->opsi_jawaban : 0;
                return $data;
            }

            $decodedJawaban = null;

            if (is_string($rawJawabanTeks)) {
                $decoded = json_decode($rawJawabanTeks, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $decodedJawaban = $decoded;
                } else {
                    $decodedJawaban = $rawJawabanTeks;
                }
            } elseif (is_array($rawJawabanTeks)) {
                $decodedJawaban = $rawJawabanTeks;
            } else {
                $decodedJawaban = (string) $rawJawabanTeks;
            }

            $normalizedJawaban = [
                'raw_input' => is_array($decodedJawaban)
                    ? ($decodedJawaban['raw_input'] ?? null)
                    : $decodedJawaban,

                'calculated_percentage' => is_array($decodedJawaban)
                    ? ($decodedJawaban['calculated_percentage'] ?? null)
                    : null,
            ];

            $data['jawaban_teks'] = $normalizedJawaban;

            $calculatedValue = $normalizedJawaban['calculated_percentage'] ?? $normalizedJawaban['raw_input'];

            if ($calculatedValue === null || $calculatedValue === '') {
                $data['jawaban_id'] = null;
                $data['skor_sistem'] = 0;
            } else {
                $matchingOpsi = $this->pertanyaanRepository->findMatchingOpsiByValue(
                    $dto->pertanyaanId,
                    $calculatedValue
                );

                $data['jawaban_id'] = $matchingOpsi ? $matchingOpsi->id : null;
                $data['skor_sistem'] = $matchingOpsi ? (int) $matchingOpsi->opsi_jawaban : 0;
            }
        }

        return $data;
    }

    public function getAssignedReviews(int $reviewerId)
    {
        $this->ensureUserIsActive($reviewerId);

        $penugasans = $this->repository->getAssignedPenugasansByReviewer($reviewerId);

        $summary = [
            'total_tugas' => $penugasans->count(),
            'menunggu_review' => $penugasans->where('status', 'SUBMITTED')->count(),
            'selesai_review' => $penugasans->whereIn('status', ['GRADED', 'PUBLISHED'])->count(),
            'yang_belum_direview' => $penugasans->whereIn('status', ['ACTIVE', 'IN_PROGRESS', 'SUBMITTED'])->values(),
            'daftar_asesmen' => $penugasans->values()
        ];

        return $summary;
    }

    /**
     * 3. Final Lock (Validasi dan Penguncian)
     */
    public function lockAssessment($penugasan)
    {
        $totalQuestions = $this->pertanyaanRepository->countTotalMandatoryQuestions();
        $answered = $this->repository->countValidAnswers($penugasan);

        if ($answered < $totalQuestions) {
            throw new Exception("Gagal Submit: Anda baru menjawab {$answered} dari {$totalQuestions} soal. Harap lengkapi semua jawaban.", 422);
        }

        return $this->repository->updateStatusPenugasan($penugasan->id, 'SUBMITTED');
    }

    /**
     * 4. Hitung Estimasi Total
     */
    public function calculateTotalPreview($penugasan)
    {
        $answers = $this->repository->getAllAnswersByPenugasan($penugasan->id);

        $totalEstimatedScore = 0;
        foreach ($answers as $answer) {
            if (is_numeric($answer->skor_sistem)) {
                $totalEstimatedScore += $answer->skor_sistem;
            }
        }

        return [
            'estimated_score' => $totalEstimatedScore,
            'label' => "Estimasi Skor Kasar",
            'details' => "Skor ini bersifat estimasi sebelum verifikasi Reviewer."
        ];
    }

    /**
     * 5. Ambil Progres Saat Ini
     */
    public function getCurrentProgress(PenugasanDTO $dto)
    {
        $penugasan = $this->repository->findActivePenugasanByUserId($dto->userId);
        if (!$penugasan)
            return ['percentage' => 0];

        $totalQuestions = $this->pertanyaanRepository->countTotalMandatoryQuestions();
        $answered = $this->repository->countValidAnswers($penugasan);

        return [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answered,
            'percentage' => $totalQuestions > 0 ? round(($answered / $totalQuestions) * 100) : 0,
            'is_completed' => $answered >= $totalQuestions
        ];
    }

    /**
     * Mengambil detail profil peserta + rubrik + jawaban untuk reviewer
     */
    public function getDetailReviewTasks(int $reviewerId, int $pesertaId)
    {
        $this->ensureUserIsActive($reviewerId);

        $penugasan = $this->repository->getDetailPenugasanByReviewer($reviewerId, $pesertaId);

        if (!$penugasan) {
            throw new Exception("Data penugasan tidak ditemukan atau Anda tidak memiliki hak akses.", 404);
        }

        $identitas = $penugasan->identitas;

        $roleIndex = null;
        if ($reviewerId == $penugasan->reviewer_1_id) {
            $roleIndex = '1';
        } elseif ($reviewerId == $penugasan->reviewer_2_id) {
            $roleIndex = '2';
        } elseif ($reviewerId == $penugasan->reviewer_3_id) {
            $roleIndex = '3';
        }

        $allPertanyaan = $this->pertanyaanRepository->getPertanyaanWithOpsiJawaban();

        $jawabanMap = [];
        if (in_array($penugasan->status, ['SUBMITTED', 'GRADED', 'PUBLISHED'])) {
            foreach ($penugasan->jawabans as $jawaban) {
                $grades = $jawaban->reviewer_grades_json;
                if (is_string($grades)) {
                    $grades = json_decode($grades, true);
                }

                $reviewerScore = null;
                $reviewerNote = null;
                if ($roleIndex && isset($grades[$roleIndex])) {
                    $reviewerScore = $grades[$roleIndex]['skor'] ?? null;
                    $reviewerNote = $grades[$roleIndex]['note'] ?? null;
                }

                $jawabanMap[$jawaban->pertanyaan_id] = [
                    'jawaban_id' => $jawaban->jawaban_id,
                    'jawaban_teks' => $this->formatJawabanTeksDisplay($jawaban->jawaban_teks),
                    'tautan_bukti_drive' => $jawaban->tautan_bukti_drive,
                    'skor_sistem' => $jawaban->skor_sistem,
                    'skor_validasi_reviewer' => $reviewerScore,
                    'note_reviewer' => $reviewerNote,
                    'opsi_dipilih' => $jawaban->jawabanOpsi ? [
                        'id' => $jawaban->jawabanOpsi->id,
                        'opsi_jawaban' => $jawaban->jawabanOpsi->opsi_jawaban,
                        'keterangan' => $jawaban->jawabanOpsi->keterangan,
                        'value' => $jawaban->jawabanOpsi->value,
                    ] : null,
                ];
            }
        }

        if ($penugasan->status === 'IN_PROGRESS') {
            $jawabanMap = [];
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
                'opsi_jawaban' => $pertanyaan->OpsiJawaban->map(function ($opsi) {
                    return [
                        'id'           => $opsi->id,
                        'opsi_jawaban' => $opsi->opsi_jawaban,
                        'keterangan'   => $opsi->keterangan,
                        'value'        => $opsi->value,
                    ];
                })->toArray(),
                'jawaban_peserta' => $jawabanMap[$pertanyaan->id] ?? null,
            ];
        }

        return [
            'Assessment' => [
                'id' => $penugasan->id,
                'status' => $penugasan->status,
                'total_skor_sistem' => $penugasan->total_skor_sistem,
                'total_skor_akhir' => $penugasan->total_skor_akhir,
                'tahun_periode' => $penugasan->tahun_periode,
            ],
            'institusi' => $penugasan->institusi,
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
                'agama' => $identitas->agamas->mapWithKeys(function ($item) {
                    return [strtolower($item->agama) => $item->jumlah];
                })
            ] : null,
            'rubrik' => array_values($rubrikData),
            'nama_pic' => $penugasan->nama_pic,
            'jabatan_pic' => $penugasan->jabatan_pic,
            'no_hp_pic' => $penugasan->no_hp_pic,
            'email_pic' => $penugasan->user->email ?? null,
        ];
    }

    /**
     * Get hasil data for peserta dashboard.
     */
    public function getHasilData(int $userId)
    {
        $penugasan = $this->repository->findActivePenugasanByUserId($userId);
        if (!$penugasan) {
            throw new Exception("Sesi penugasan aktif tidak ditemukan.", 404);
        }

        $isPublished = $penugasan->status === 'PUBLISHED';

        $allCategories = $this->pertanyaanRepository->getAllCategoriesWithPertanyaans();

        $answers = $this->repository->getAllAnswersByPenugasan($penugasan->id)
            ->keyBy('pertanyaan_id');

        $categories = [];
        foreach ($allCategories as $cat) {
            $bobot = $this->getBobotKategori($cat->nama_kategori);
            $catData = [
                'name' => $cat->nama_kategori,
                'bobot' => $bobot,
                'score' => 0,
                'max' => 0,
                'capaian_skor' => 0,
                'items' => [],
            ];

            foreach ($cat->pertanyaans as $pertanyaan) {
                $answer = $answers->get($pertanyaan->id);

                $catData['max'] += 5;

                $displayScore = 0;
                if ($answer) {
                    if ($isPublished) {
                        $displayScore = (int) ($answer->skor_validasi_reviewer ?? $answer->skor_sistem ?? 0);
                    } else {
                        $displayScore = (int) ($answer->skor_sistem ?? 0);
                    }
                }
                $catData['score'] += $displayScore;

                $catData['items'][] = [
                    'no' => $pertanyaan->kode_pertanyaan,
                    'title' => $pertanyaan->teks_pertanyaan,
                    'score' => $displayScore,
                    'max' => 5,
                    'jawaban' => $answer ? ($this->formatJawabanTeksDisplay($answer->jawaban_teks) ?? ($answer->jawabanOpsi ? $answer->jawabanOpsi->keterangan : 'Belum diisi')) : 'Belum diisi',
                    'tautan' => $answer ? $answer->tautan_bukti_drive : null,
                    'catatan' => ($isPublished && $answer) ? $answer->note_reviewer : null,
                    'is_validated' => $isPublished && $answer && ($answer->skor_validasi_reviewer !== null),
                ];
            }

            if ($catData['max'] > 0) {
                $catData['capaian_skor'] = round(($catData['score'] / $catData['max']) * $bobot, 2);
            }

            $categories[] = $catData;
        }

        $totalScore = array_sum(array_column($categories, 'score'));
        $totalMax = array_sum(array_column($categories, 'max'));
        $totalCapaianSkor = round(array_sum(array_column($categories, 'capaian_skor')), 2);

        return [
            'status' => $penugasan->status,
            'is_published' => $isPublished,
            'is_validated' => $isPublished,
            'total_score' => $totalScore,
            'total_max' => $totalMax,
            'total_capaian_skor' => $totalCapaianSkor,
            'tahun_periode' => $penugasan->tahun_periode,
            'institusi' => $penugasan->institusi?->nama_institusi ?? '-',
            'categories' => array_values($categories),
        ];
    }

    private function getBobotKategori(string $namaKategori): float
    {
        $prefix = strtoupper(substr(trim($namaKategori), 0, 2));
        return match ($prefix) {
            'A.' => 20.0,
            'B.' => 30.0,
            'C.' => 50.0,
            default => 0.0,
        };
    }

    /**
     * Save all draft answers in batch
     */
    public function saveDraftBatch($penugasan, array $answers)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($penugasan, $answers) {
            foreach ($answers as $answer) {
                $dto = new JawabanDTO($penugasan->id, $answer);
                $pertanyaan = $this->pertanyaanRepository->findPertanyaanById($dto->pertanyaanId);
                if (!$pertanyaan)
                    continue;

                $payload = [
                    'penugasan_id' => $dto->submissionId,
                    'pertanyaan_id' => $dto->pertanyaanId,
                ];

                $syncData = $this->jawabanAutoSync($dto, $pertanyaan);
                $payload = array_merge($payload, $syncData);
                $payload['tautan_bukti_drive'] = $dto->tautanBukti;

                $jawabanTeksFilled = false;
                if (isset($payload['jawaban_teks'])) {
                    $teks = $payload['jawaban_teks'];
                    if (is_array($teks)) {
                        if (isset($teks['total_poin'])) {
                            $jawabanTeksFilled = true;
                        } else {
                            $raw = $teks['raw_input'] ?? '';
                            $jawabanTeksFilled = trim((string) $raw) !== '';
                        }
                    } else {
                        $jawabanTeksFilled = trim((string) $teks) !== '';
                    }
                }
                $hasJawaban = !empty($payload['jawaban_id']) || $jawabanTeksFilled;

                $hasTautan = !empty($payload['tautan_bukti_drive']) && trim((string) $payload['tautan_bukti_drive']) !== '';
                $isOtomatis = ($pertanyaan->tipe ?? null) === 'otomatis_sistem';
                $needsBukti = !empty($pertanyaan->kebutuhan_bukti);
                if ((!$isOtomatis || $needsBukti) && (!$hasJawaban || !$hasTautan)) {
                    $payload['skor_sistem'] = 0;
                }

                $this->repository->upsertJawaban($payload);
            }

            $this->repository->updateStatusPenugasan($penugasan->id, 'SUBMITTED');

            $this->recapAndSaveSkor($penugasan);

            return ['saved_count' => count($answers)];
        });
    }

    private function formatJawabanTeksDisplay($teks): ?string
    {
        if (empty($teks))
            return null;

        $decoded = is_string($teks) ? json_decode($teks, true) : $teks;

        if (is_array($decoded)) {
            $raw = $decoded['raw_input'] ?? '';
            $calc = $decoded['calculated_percentage'] ?? $decoded['calculated'] ?? '';

            if ($raw !== '' && $calc !== '') {
                return "{$raw} (Kalkulasi: {$calc}%)";
            }
            if ($raw !== '')
                return (string) $raw;
            if ($calc !== '')
                return (string) $calc;
        }

        return is_string($teks) ? $teks : json_encode($teks);
    }

    /**
     * Menghitung dan menyimpan rekap skor persen total & per-kategori ke kolom skor_rekap_json.
     */
    public function recapAndSaveSkor($penugasan): void
    {
        $penugasan->refresh();

        $allCategories = $this->pertanyaanRepository->getAllCategoriesWithPertanyaans();

        // 1. Update individual skor_validasi_reviewer
        $answersList = $this->repository->getAllAnswersByPenugasan($penugasan->id);
        foreach ($answersList as $ans) {
            $grades = $ans->reviewer_grades_json;
            if (is_string($grades)) {
                $grades = json_decode($grades, true);
            }
            $s1 = isset($grades['1']['skor']) && $grades['1']['skor'] !== '' ? (float) $grades['1']['skor'] : null;
            $s2 = isset($grades['2']['skor']) && $grades['2']['skor'] !== '' ? (float) $grades['2']['skor'] : null;

            if ($s1 !== null || $s2 !== null) {
                if ($s1 !== null && $s2 !== null) {
                    $valScore = ($s1 + $s2) / 2;
                } elseif ($s1 !== null) {
                    $valScore = $s1;
                } else {
                    $valScore = $s2;
                }
                $ans->update(['skor_validasi_reviewer' => $valScore]);
            }
        }

        // 2. Calculate Reviewer 1 & 2 total weighted scores
        $nilai_reviewer_1 = $this->calculateReviewerWeightedScore($penugasan, '1');
        $nilai_reviewer_2 = $this->calculateReviewerWeightedScore($penugasan, '2');
        $nilai_rata_rata = round(($nilai_reviewer_1 + $nilai_reviewer_2) / 2, 2);

        // 3. Set total_skor_akhir
        $nilai_reviewer_3 = (float) ($penugasan->nilai_reviewer_3 ?? 0);
        if ($nilai_reviewer_3 > 0) {
            $threshold = (float) config('rubrik.reviewer_dispute_threshold', 100);
            $isDispute = abs($nilai_reviewer_1 - $nilai_reviewer_2) >= $threshold;

            if ($isDispute) {
                $totalSkorAkhir = $nilai_reviewer_3;
            } else {
                $diff1 = abs($nilai_reviewer_1 - $nilai_reviewer_3);
                $diff2 = abs($nilai_reviewer_2 - $nilai_reviewer_3);

                if ($diff1 < $diff2) {
                    $totalSkorAkhir = $nilai_reviewer_1;
                } elseif ($diff2 < $diff1) {
                    $totalSkorAkhir = $nilai_reviewer_2;
                } else {
                    $totalSkorAkhir = $nilai_reviewer_1;
                }
            }
        } else {
            $totalSkorAkhir = $nilai_rata_rata;
        }

        $penugasan->update([
            'nilai_reviewer_1' => $nilai_reviewer_1,
            'nilai_reviewer_2' => $nilai_reviewer_2,
            'nilai_rata_rata'  => $nilai_rata_rata,
            'total_skor_akhir' => $totalSkorAkhir,
        ]);

        // 4. Calculate Rekap JSON
        $answers = $this->repository->getAllAnswersByPenugasan($penugasan->id)->keyBy('pertanyaan_id');
        $perKategori = [];
        $totalSkor = 0;
        $totalMax  = 0;

        foreach ($allCategories as $cat) {
            $bobot = $this->getBobotKategori($cat->nama_kategori);
            $catSkor = 0;
            $catMax  = 0;

            foreach ($cat->pertanyaans as $pertanyaan) {
                $catMax += 5;
                $answer = $answers->get($pertanyaan->id);
                if ($answer) {
                    $catSkor += $answer->skor_validasi_reviewer ?? $answer->skor_sistem ?? 0;
                }
            }

            $totalSkor += $catSkor;
            $totalMax  += $catMax;

            $prefix = strtoupper(substr(trim($cat->nama_kategori), 0, 2));
            $persenMentah    = $catMax > 0 ? round(($catSkor / $catMax) * 100, 2) : 0;
            $persenTertimbang = round(($persenMentah / 100) * $bobot, 2);

            $perKategori[$prefix] = [
                'nama'              => $cat->nama_kategori,
                'skor'              => $catSkor,
                'max'               => $catMax,
                'bobot'             => $bobot,
                'persen_mentah'     => $persenMentah,
                'persen_tertimbang' => $persenTertimbang,
            ];
        }

        $totalPersen = $totalMax > 0 ? round(($totalSkor / $totalMax) * 100, 2) : 0;

        $rekap = [
            'total_persen'  => $totalPersen,
            'total_skor'    => $totalSkor,
            'total_max'     => $totalMax,
            'per_kategori'  => $perKategori,
            'updated_at'    => now()->toIso8601String(),
        ];

        $this->repository->updateRekapSkor($penugasan->id, $rekap);
    }

    private function calculateReviewerWeightedScore($penugasan, string $roleIndex): float
    {
        $allCategories = $this->pertanyaanRepository->getAllCategoriesWithPertanyaans();
        $answers = $this->repository->getAllAnswersByPenugasan($penugasan->id)->keyBy('pertanyaan_id');

        $totalWeighted = 0;
        foreach ($allCategories as $cat) {
            $bobot = $this->getBobotKategori($cat->nama_kategori);
            $catSkor = 0;
            $catMax = 0;

            foreach ($cat->pertanyaans as $pertanyaan) {
                $catMax += 5;
                $answer = $answers->get($pertanyaan->id);
                $score = 0;
                if ($answer) {
                    $grades = $answer->reviewer_grades_json;
                    if (is_string($grades)) {
                        $grades = json_decode($grades, true);
                    }
                    $score = isset($grades[$roleIndex]['skor']) && $grades[$roleIndex]['skor'] !== ''
                        ? (float) $grades[$roleIndex]['skor']
                        : (float) ($answer->skor_sistem ?? 0);
                }
                $catSkor += $score;
            }

            $persenMentah = $catMax > 0 ? ($catSkor / $catMax) * 100 : 0;
            $persenTertimbang = ($persenMentah / 100) * $bobot;
            $totalWeighted += $persenTertimbang;
        }
        return round($totalWeighted, 2);
    }

    public function saveReviewerScores($penugasan, array $scores, array $notes, int $reviewerId)
    {
        return DB::transaction(function () use ($penugasan, $scores, $notes, $reviewerId) {
            $roleIndex = null;
            if ($reviewerId == $penugasan->reviewer_1_id) {
                $roleIndex = '1';
            } elseif ($reviewerId == $penugasan->reviewer_2_id) {
                $roleIndex = '2';
            } elseif ($reviewerId == $penugasan->reviewer_3_id) {
                $roleIndex = '3';
            }

            if (!$roleIndex) {
                throw new Exception("Reviewer tidak terasosiasi dengan penugasan ini.");
            }

            foreach ($scores as $pertanyaanId => $skor) {
                if ($skor === null || $skor === '') continue;

                $skor = min(5, max(0, (int) $skor));

                $jawaban = ResponPenugasan::firstOrCreate([
                    'penugasan_id' => $penugasan->id,
                    'pertanyaan_id' => (int) $pertanyaanId,
                ]);

                $grades = $jawaban->reviewer_grades_json ?? [];
                if (is_string($grades)) {
                    $grades = json_decode($grades, true);
                }

                $grades[$roleIndex] = [
                    'skor' => $skor,
                    'note' => $notes[$pertanyaanId] ?? null,
                ];

                $jawaban->update([
                    'reviewer_grades_json' => $grades,
                ]);
            }

            return $this->recapAndSaveSkor($penugasan);
        });
    }

    public function finalizeReview($penugasan, int $reviewerId)
    {
        $roleIndex = null;
        if ($reviewerId == $penugasan->reviewer_1_id) {
            $roleIndex = '1';
        } elseif ($reviewerId == $penugasan->reviewer_2_id) {
            $roleIndex = '2';
        } elseif ($reviewerId == $penugasan->reviewer_3_id) {
            $roleIndex = '3';
        }

        if ($roleIndex === '1' || $roleIndex === '2') {
            $totalPertanyaan = $this->pertanyaanRepository->countTotalMandatoryQuestions();
            $totalDinilai = $this->repository->countValidReviewerScoresForRole($penugasan->id, $roleIndex);

            if ($totalDinilai < $totalPertanyaan) {
                $belum = $totalPertanyaan - $totalDinilai;
                throw new Exception("Finalisasi gagal: Masih ada {$belum} indikator yang belum Anda beri skor.", 422);
            }

            $this->recapAndSaveSkor($penugasan);
            return true;
        }

        $this->repository->updateStatusPenugasan($penugasan->id, 'GRADED');
        return $this->recapAndSaveSkor($penugasan);
    }

    public function assignReviewer(int|string $penugasanId, int|string $reviewerId): bool
    {
        return $this->repository->update($penugasanId, ['reviewer_id' => $reviewerId]);
    }
}
