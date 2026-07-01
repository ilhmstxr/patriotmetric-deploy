<?php

namespace App\Services;

use App\Repositories\PenugasanRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use App\DTO\PenugasanDTO\PenugasanDTO;
use App\DTO\PenugasanDTO\BaselineDTO;
use App\DTO\PenugasanDTO\JawabanDTO;
use App\Models\ResponPenugasan;
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
        PenugasanRepository $PenugasanRepository,
        PertanyaanRepository $pertanyaanRepository,
        \App\Repositories\TimelineRepository $timelineRepository
    ) {
        // Parent constructor mengikat PenugasanRepository sebagai repository utama
        parent::__construct($PenugasanRepository);
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

            // 2. Update Institusi (Gunakan Null-safe operator ?-> untuk mencegah crash)
            $this->repository->updateInstitusi($penugasan->institution_id, [
                'nama_institusi' => $dto->namaInstitusi ?? $existingInstitusi?->nama_institusi,
                'jenis_institusi' => $dto->jenisInstitusi ?? $existingInstitusi?->jenis_institusi
            ]);

            // 3. Proses File Upload (HANYA KETIKA CREATE)
            $finalDocumentsJson = $existingIdentitas?->legal_documents ?? [];

            // ZERO-GAP LOGIC: 
            // Hanya jalankan processLegalDocuments JIKA $existingIdentitas kosong (Create)
            // Jika ini Update, blok if ini akan dilewati dan file lama akan dipertahankan.
            if (!$existingIdentitas && !empty($dto->legalDocuments)) {
                // Eksekusi pemindahan file ke Storage
                $documentPaths = $this->processLegalDocuments($dto->legalDocuments, $penugasan);
                $finalDocumentsJson = $documentPaths;
            }

            // 4. Update Identitas (Gunakan Null-safe operator secara ketat)
            $identitasData = [
                'Penugasan_id' => $penugasan->id,
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

            // Pastikan repository menggunakan updateOrCreate di balik layar
            $identitas = $this->repository->upsertIdentitas($penugasan->id, $identitasData);

            // 5. Update Agama secara Selektif
            if (!empty($dto->dataAgama)) {
                foreach ($dto->dataAgama as $namaAgama => $jumlah) {
                    // Validasi Enum sebelum insert untuk mencegah SQL Error
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
            $lockReason = match($penugasan->status) {
                'SUBMITTED' => 'Formulir dikunci karena sudah mencapai batas waktu pengisian.',
                'GRADED'    => 'Formulir dikunci karena sudah mencapai batas waktu pengisian.',
                'PUBLISHED' => 'Nilai final yang sudah divalidasi reviewer sudah bisa dilihat.',
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
            // Jumlah jenis agama yang memiliki penganut > 0 (untuk B.20)
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
        // 1. Fetch info pertanyaan untuk Technical Interrogation
        $pertanyaan = $this->pertanyaanRepository->findPertanyaanById($dto->pertanyaanId);
        if (!$pertanyaan)
            throw new Exception("Pertanyaan tidak ditemukan.", 404);

        // 2. Build Payload Dasar
        $payload = [
            'penugasan_id' => $dto->submissionId,
            'pertanyaan_id' => $dto->pertanyaanId,
        ];

        // 3. Jalankan Auto-Sync (Merge hasil sync ke payload utama)
        $syncData = $this->jawabanAutoSync($dto, $pertanyaan);
        $payload = array_merge($payload, $syncData);

        // 4. KONDISI 3 & 4: Tambahan Bukti dan Note
        $payload['tautan_bukti_drive'] = $dto->tautanBukti;
        $payload['note_reviewer'] = $dto->noteReviewer;

        // 5. Aturan skor: hanya dihitung saat jawaban DAN tautan bukti keduanya terisi.
        //    Soal otomatis_sistem yang TIDAK punya kebutuhan_bukti dikecualikan.
        //    B.13 & C.10 menyimpan struktur JSON tanpa raw_input — cek total_poin sebagai indikator terisi.
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
     * Mengembalikan token versi (timestamp ISO) untuk validasi cache rubrik di frontend.
     * Frontend melakukan stale-while-revalidate: jika versi sama, tidak perlu refetch full data.
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
            // KONDISI 1: User kirim ID -> Ambil Teks & Skor
            $data['jawaban_id'] = $dto->jawabanId;

            $opsi = $this->pertanyaanRepository->findOpsiById($dto->jawabanId);
            $teksKeterangan = $opsi ? $opsi->keterangan : null;
            
            $data['jawaban_teks'] = [
                'raw_input' => $teksKeterangan,
                'calculated_percentage' => null
            ];
            
            $data['skor_sistem'] = $opsi ? (int) $opsi->opsi_jawaban : 0; // Score in DB is stored in opsi_jawaban
        } else {
            // KONDISI 2: User kirim Teks (Angka) -> Cari ID & Skor yang sesuai
            $rawJawabanTeks = $dto->jawabanTeks ?? $dto->jawabanId;

            // SPECIAL: B.13 & C.10 menyimpan JSON lengkap {lokal:{label,nilai,poin},...,total_poin}
            if (in_array($pertanyaan->kode_pertanyaan ?? null, ['B.13', 'C.10'])) {
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

            // 1. Defensive Parsing: Coba decode stringified JSON dari FE
            if (is_string($rawJawabanTeks)) {
                $decoded = json_decode($rawJawabanTeks, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $decodedJawaban = $decoded;
                } else {
                    $decodedJawaban = $rawJawabanTeks; // Fallback ke string murni
                }
            } elseif (is_array($rawJawabanTeks)) {
                $decodedJawaban = $rawJawabanTeks;
            } else {
                $decodedJawaban = (string) $rawJawabanTeks;
            }

            // 2. Normalisasi Struktur & Kalkulasi Skor
            $normalizedJawaban = [
                'raw_input' => is_array($decodedJawaban) 
                    ? ($decodedJawaban['raw_input'] ?? null) 
                    : $decodedJawaban, // Jika isian tunggal (string murni), taruh di sini
                    
                'calculated_percentage' => is_array($decodedJawaban) 
                    ? ($decodedJawaban['calculated_percentage'] ?? null) 
                    : null,
            ];

            $data['jawaban_teks'] = $normalizedJawaban;

            // Cek untuk kalkulasi skor otomatis
            // Prioritas: calculated_percentage (untuk pertanyaan dengan formula), fallback ke raw_input
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

        return $data; // WAJIB return agar bisa dipakai di storeJawaban
    }

    public function getAssignedReviews(int $reviewerId)
    {
        // 1. Pastikan akun Reviewer masih aktif (menggunakan fungsi yang sudah ada)
        $this->ensureUserIsActive($reviewerId);

        // 2. Tarik data dari Repository
        $penugasans = $this->repository->getAssignedPenugasansByReviewer($reviewerId);

        // 3. (Opsional) Transformasi data jika diperlukan sebelum dikirim ke Controller
        // Misalnya menghitung statistik ringan untuk dashboard Reviewer
        $summary = [
            'total_tugas' => $penugasans->count(),
            'menunggu_review' => $penugasans->where('status', 'SUBMITTED')->count(),
            'selesai_review' => $penugasans->whereIn('status', ['GRADED', 'PUBLISHED'])->count(),
            'yang_belum_direview' => $penugasans->whereIn('status', ['ACTIVE', 'IN_PROGRESS', 'SUBMITTED'])->values(),
            'daftar_penugasan' => $penugasans->values() // Reset key array
        ];

        return $summary;
    }

    /**
     * 3. Final Lock (Validasi dan Penguncian)
    DONE
     */
    public function lockPenugasan($penugasan)
    {

        $totalQuestions = $this->pertanyaanRepository->countTotalMandatoryQuestions();
        $answered = $this->repository->countValidAnswers($penugasan);

        if ($answered < $totalQuestions) {
            throw new Exception("Gagal Submit: Anda baru menjawab {$answered} dari {$totalQuestions} soal. Harap lengkapi semua jawaban.", 422);
        }

        return $this->repository->updateStatusPenugasan($penugasan->id, 'SUBMITTED');
    }

    /**
     * 4. Hitung Estimasi Total (Preview Keseluruhan Form)
     DONE
     */
    public function calculateTotalPreview($penugasan)
    {
        $answers = $this->repository->getAllAnswersByPenugasan($penugasan->id);

        $totalEstimatedScore = 0;
        foreach ($answers as $answer) {
            // TODO: Ganti dengan formula baku PatriotMetric (Normalisasi/Bobot)
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
     * 5. Ambil Progres Saat Ini (Untuk UI Progress Bar)
     CHECK
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
     * Hanya menampilkan jawaban ketika status SUBMITTED
     */
    public function getDetailReviewTasks(int $reviewerId, int $pesertaId)
    {
        // 1. Pastikan akun Reviewer masih aktif
        $this->ensureUserIsActive($reviewerId);

        // 2. Tarik data dari Repository (now includes jawabans with pertanyaan)
        $penugasan = $this->repository->getDetailPenugasanByReviewer($reviewerId, $pesertaId);

        if (!$penugasan) {
            throw new Exception("Data penugasan tidak ditemukan atau Anda tidak memiliki hak akses.", 404);
        }

        $reviewerKey = null;
        if ($reviewerId === $penugasan->reviewer_1_id) {
            $reviewerKey = 'r1';
        } elseif ($reviewerId === $penugasan->reviewer_2_id) {
            $reviewerKey = 'r2';
        } elseif ($reviewerId === $penugasan->reviewer_3_id) {
            $reviewerKey = 'r3';
        }

        $identitas = $penugasan->identitas;

        // 3. Get all pertanyaan with opsi jawaban (for reviewer guide)
        $allPertanyaan = $this->pertanyaanRepository->getPertanyaanWithOpsiJawaban();

        // 4. Build jawaban map (pertanyaan_id => jawaban data) from peserta's answers
        $jawabanMap = [];
        foreach ($penugasan->jawabans as $jawaban) {
            $skorVal = null;
            if (is_array($jawaban->skor_validasi_reviewer)) {
                $skorVal = $reviewerKey ? ($jawaban->skor_validasi_reviewer[$reviewerKey] ?? null) : null;
            } else {
                $skorVal = $jawaban->skor_validasi_reviewer;
            }

            $noteVal = null;
            if (is_array($jawaban->note_reviewer)) {
                $noteVal = $reviewerKey ? ($jawaban->note_reviewer[$reviewerKey] ?? null) : null;
            } else {
                $noteVal = $jawaban->note_reviewer;
            }

            $jawabanMap[$jawaban->pertanyaan_id] = [
                'jawaban_id' => $jawaban->jawaban_id,
                'jawaban_teks' => $this->formatJawabanTeksDisplay($jawaban->jawaban_teks),
                'tautan_bukti_drive' => $jawaban->tautan_bukti_drive,
                'skor_sistem' => $jawaban->skor_sistem,
                'skor_validasi_reviewer' => $skorVal,
                'note_reviewer' => $noteVal,
                'opsi_dipilih' => $jawaban->jawabanOpsi ? [
                    'id' => $jawaban->jawabanOpsi->id,
                    'opsi_jawaban' => $jawaban->jawabanOpsi->opsi_jawaban,
                    'keterangan' => $jawaban->jawabanOpsi->keterangan,
                    'value' => $jawaban->jawabanOpsi->value,
                ] : null,
            ];
        }

        // 5. Group pertanyaan by kategori with jawaban
        $rubrikData = [];
        foreach ($allPertanyaan as $pertanyaan) {
            $kategoriName = $pertanyaan->kategori->nama_kategori ?? 'Tanpa Kategori';

            if (!isset($rubrikData[$kategoriName])) {
                $rubrikData[$kategoriName] = [
                    'kategori' => $kategoriName,
                    'pertanyaan_count' => 0,
                    'bobot_maksimal' => 0,
                    'bobot_persentase' => $this->getBobotKategori($kategoriName),
                    'pertanyaan' => [],
                ];
            }

            $rubrikData[$kategoriName]['pertanyaan_count']++;
            $rubrikData[$kategoriName]['bobot_maksimal'] += 5; // Max 5 per rubrik

            $rubrikData[$kategoriName]['pertanyaan'][] = [
                'id' => $pertanyaan->id,
                'kode_pertanyaan' => $pertanyaan->kode_pertanyaan,
                'teks_pertanyaan' => $pertanyaan->teks_pertanyaan,
                'kebutuhan_bukti' => $pertanyaan->kebutuhan_bukti,
                'tipe' => $pertanyaan->tipe,
                // 'skor_maksimal' => $pertanyaan->skor_maksimal,
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
            'Penugasan' => [
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
     * - IN_PROGRESS/SUBMITTED/GRADED: tampilkan skor_sistem (draft, belum divalidasi)
     * - PUBLISHED: tampilkan skor_validasi_reviewer (final, sudah dipublikasikan admin)
     */
    public function getHasilData(int $userId)
    {
        $penugasan = $this->repository->findActivePenugasanByUserId($userId);
        if (!$penugasan) {
            throw new Exception("Sesi penugasan aktif tidak ditemukan.", 404);
        }

        // Tentukan apakah ini hasil final (PUBLISHED) atau draft
        $isPublished = $penugasan->status === 'PUBLISHED';

        // Get all categories with their questions
        $allCategories = $this->pertanyaanRepository->getAllCategoriesWithPertanyaans();

        // Get all existing answers for this penugasan
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
                    // PUBLISHED: pakai skor reviewer jika ada, fallback ke skor sistem
                    // Selain PUBLISHED: selalu pakai skor sistem (draft)
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
                    // Catatan reviewer hanya tampil jika sudah PUBLISHED
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

    /**
     * Bobot kategori (hardcoded sementara berdasarkan prefix nama kategori).
     * A. = 20%, B. = 30%, C. = 50%
     */
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
     * Save all draft answers in batch (called by "Simpan Draft" button)
     * Updates status to SUBMITTED
     */
    public function saveDraftBatch($penugasan, array $answers)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($penugasan, $answers) {
            foreach ($answers as $answer) {
                $dto = new \App\DTO\PenugasanDTO\JawabanDTO($penugasan->id, $answer);
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

                // Aturan skor: hanya dihitung saat jawaban DAN tautan bukti keduanya terisi.
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

            // Update status to SUBMITTED
            $this->repository->updateStatusPenugasan($penugasan->id, 'SUBMITTED');

            // Simpan rekap skor persen ke Penugasan
            $this->recapAndSaveSkor($penugasan);

            return ['saved_count' => count($answers)];
        });
    }

    /**
     * Helper to safely format JSON jawaban_teks for display (Reviewer & Dashboard)
     */
    private function formatJawabanTeksDisplay($teks): ?string
    {
        if (empty($teks))
            return null;
            
        $decoded = is_string($teks) ? json_decode($teks, true) : $teks;
        
        if (is_array($decoded)) {
            // Check if multi-scale (like B.13 or C.10)
            if (isset($decoded['total_poin']) || isset($decoded['lokal']) || isset($decoded['regional']) || isset($decoded['nasional']) || isset($decoded['internasional'])) {
                $getVal = function($scale) use ($decoded) {
                    if (!isset($decoded[$scale])) return 0;
                    $item = $decoded[$scale];
                    if (is_array($item)) return $item['nilai'] ?? 0;
                    return (int) $item;
                };
                
                $lokal = $getVal('lokal');
                $regional = $getVal('regional');
                $nasional = $getVal('nasional');
                $internasional = $getVal('internasional');
                $total = $decoded['total_poin'] ?? (($lokal*1) + ($regional*2) + ($nasional*3) + ($internasional*4));
                
                return "Lokal: {$lokal}, Regional: {$regional}, Nasional: {$nasional}, Internasional: {$internasional} (Total Poin: {$total})";
            }
            
            $raw = $decoded['raw_input'] ?? null;
            $calc = $decoded['calculated_percentage'] ?? $decoded['calculated'] ?? null;

            if ($raw !== null && $raw !== '' && $calc !== null && $calc !== '') {
                return "{$raw} (Kalkulasi: {$calc}%)";
            }
            if ($raw !== null && $raw !== '') {
                return (string) $raw;
            }
            if ($calc !== null && $calc !== '') {
                return (string) $calc;
            }
            
            // Return null if both are empty/null to fallback to 'Belum diisi'
            if (($raw === null || $raw === '') && ($calc === null || $calc === '')) {
                return null;
            }
        }
        
        return is_string($teks) ? $teks : json_encode($teks);
    }

    /**
     * Menghitung dan menyimpan rekap skor persen total & per-kategori ke kolom skor_rekap_json.
     * Bisa dipanggil setelah peserta submit atau setelah reviewer finalisasi.
     *
     * Struktur JSON yang disimpan:
     * {
     *   "total_persen": 78.50,
     *   "per_kategori": {
     *     "A.": { "nama": "A. ...", "skor": 17, "max": 20, "bobot": 20, "persen_mentah": 85.0, "persen_tertimbang": 17.0 },
     *     ...
     *   },
     *   "updated_at": "2026-05-07T10:00:00+07:00"
     * }
     */
    public function recapAndSaveSkor($penugasan): void
    {
        $penugasan->refresh();

        $allCategories = $this->pertanyaanRepository->getAllCategoriesWithPertanyaans();

        $answers = $this->repository->getAllAnswersByPenugasan($penugasan->id)
            ->keyBy('pertanyaan_id');

        // Sum overall reviewer scores
        $sumR1 = 0;
        $sumR2 = 0;
        $sumR3 = 0;
        foreach ($answers as $answer) {
            $scores = $answer->skor_validasi_reviewer;
            if (is_array($scores)) {
                $sumR1 += isset($scores['r1']) ? (float)$scores['r1'] : 0;
                $sumR2 += isset($scores['r2']) ? (float)$scores['r2'] : 0;
                $sumR3 += isset($scores['r3']) ? (float)$scores['r3'] : 0;
            }
        }
        $penugasan->update([
            'nilai_reviewer_1' => $sumR1,
            'nilai_reviewer_2' => $sumR2,
            'nilai_reviewer_3' => $sumR3,
        ]);

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
                    $catSkor += $answer->resolved_reviewer_score ?? $answer->skor_sistem ?? 0;
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

    public function saveReviewerScores($penugasan, string $reviewerKey, array $scores, array $notes)
    {
        return DB::transaction(function () use ($penugasan, $reviewerKey, $scores, $notes) {
            $allIds = array_unique(array_merge(array_keys($scores), array_keys($notes)));
            
            foreach ($allIds as $pertanyaanId) {
                // LOCK FOR UPDATE (Pessimistic locking to guarantee FIFO / concurrency safety)
                $respon = \App\Models\ResponPenugasan::where('penugasan_id', $penugasan->id)
                    ->where('pertanyaan_id', (int) $pertanyaanId)
                    ->lockForUpdate()
                    ->first();
                
                if (!$respon) {
                    $respon = new \App\Models\ResponPenugasan([
                        'penugasan_id' => $penugasan->id,
                        'pertanyaan_id' => (int) $pertanyaanId,
                        'skor_sistem' => 0,
                    ]);
                }

                if (array_key_exists($pertanyaanId, $scores)) {
                    $val = $scores[$pertanyaanId];
                    $currentScores = $respon->skor_validasi_reviewer ?? ['r1' => null, 'r2' => null, 'r3' => null];
                    
                    if ($val === null || $val === '') {
                        $currentScores[$reviewerKey] = null;
                    } else {
                        $currentScores[$reviewerKey] = min(5, max(0, (int) $val));
                    }
                    
                    $respon->skor_validasi_reviewer = $currentScores;
                }
                
                if (array_key_exists($pertanyaanId, $notes)) {
                    $note = $notes[$pertanyaanId];
                    $currentNotes = $respon->note_reviewer ?? ['r1' => null, 'r2' => null, 'r3' => null];
                    
                    if ($note === null || trim($note) === '') {
                        $currentNotes[$reviewerKey] = null;
                    } else {
                        $currentNotes[$reviewerKey] = $note;
                    }
                    
                    $respon->note_reviewer = $currentNotes;
                }
                
                $respon->save();
            }

            // Perbarui rekap skor JSON setelah skor reviewer disimpan
            return $this->recapAndSaveSkor($penugasan);
        });
    }

    public function countValidReviewerScoresForReviewer(int $penugasanId, string $reviewerKey): int
    {
        $answers = $this->repository->getAllAnswersByPenugasan($penugasanId);
        $count = 0;
        foreach ($answers as $answer) {
            $scores = $answer->skor_validasi_reviewer;
            $note = $answer->note_reviewer;
            
            $hasScore = false;
            if (is_array($scores)) {
                $hasScore = isset($scores[$reviewerKey]) && $scores[$reviewerKey] !== null && $scores[$reviewerKey] !== '';
            }
            
            $hasNote = false;
            if (is_array($note)) {
                $noteText = $note[$reviewerKey] ?? null;
                $hasNote = $noteText !== null && trim($noteText) !== '' && mb_strlen(trim($noteText)) >= 20;
            } else {
                $hasNote = $note !== null && trim($note) !== '' && mb_strlen(trim($note)) >= 20;
            }
            
            if ($hasScore && $hasNote) {
                $count++;
            }
        }
        return $count;
    }

    public function finalizeReview($penugasan, string $reviewerKey)
    {
        // Cek semua pertanyaan sudah ada skor dari reviewer ini
        $totalPertanyaan = $this->pertanyaanRepository->countTotalMandatoryQuestions();
        $totalDinilai = $this->countValidReviewerScoresForReviewer($penugasan->id, $reviewerKey);

        if ($totalDinilai < $totalPertanyaan) {
            $belum = $totalPertanyaan - $totalDinilai;
            throw new Exception("Finalisasi gagal: Masih ada {$belum} indikator yang belum Anda beri skor.", 422);
        }

        // Set status ke GRADED hanya jika semua reviewer yang ditugaskan telah memfinalisasi
        $r1Assigned = $penugasan->reviewer_1_id !== null;
        $r2Assigned = $penugasan->reviewer_2_id !== null;

        $r1Completed = !$r1Assigned || ($this->countValidReviewerScoresForReviewer($penugasan->id, 'r1') >= $totalPertanyaan);
        $r2Completed = !$r2Assigned || ($this->countValidReviewerScoresForReviewer($penugasan->id, 'r2') >= $totalPertanyaan);

        if ($r1Completed && $r2Completed) {
            $this->repository->updateStatusPenugasan($penugasan->id, 'GRADED');
        } else {
            $this->repository->updateStatusPenugasan($penugasan->id, 'IN_PROGRESS');
        }

        // Update rekap skor final
        return $this->recapAndSaveSkor($penugasan);
    }

    public function assignReviewer(int|string $penugasanId, int|string $reviewerId): bool
    {
        return $this->repository->update($penugasanId, ['reviewer_1_id' => $reviewerId]);
    }
}
