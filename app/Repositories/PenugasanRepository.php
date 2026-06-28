<?php

namespace App\Repositories;

use App\Models\Agama;
use App\Models\Identitas;
use App\Models\Institusi;
use App\Models\Penugasan;
use App\Models\ResponPenugasan;
use App\Models\Kategori;
use App\Models\OpsiJawaban;
use App\Models\Pertanyaan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PenugasanRepository extends BaseRepository
{
    public function __construct(Penugasan $model)
    {
        parent::__construct($model);
    }

    public function findActivePenugasanByUserId($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->latest()
            ->first();
    }

    public function findActivePenugasanByUserIdAndYear($userId, $year)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('tahun_periode', $year)
            ->with(['institusi', 'identitas.agamas'])
            ->first();
    }

    public function findLatestPenugasanByUserId($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('tahun_periode', 'desc')
            ->first();
    }

    public function getAgamasByIdentitasId($identitasId)
    {
        return Agama::where('identitas_id', $identitasId)->get();
    }

    /**
     * AUTO-SAVE (Upsert Batch)
     */
    public function upsertJawaban(array $payload)
    {
        // Set default 0 hanya jika skor_sistem tidak dikirim dari service
        $payload['skor_sistem'] = $payload['skor_sistem'] ?? 0;

        return ResponPenugasan::updateOrCreate(
            [
                'penugasan_id' => $payload['penugasan_id'],
                'pertanyaan_id' => $payload['pertanyaan_id']
            ],
            $payload
        );
    }
    /**
     * Logic Pencocokan (Kondisi 2): Mencari opsi_jawaban berdasarkan value
     * Alur: Cari opsi yang nilainya <= input, ambil yang paling mendekati (terbesar)
     */
    public function findMatchingOpsiByValue($pertanyaanId, $inputValue)
    {
        $input = (int) $inputValue;

        if ($input <= 0) {
            return OpsiJawaban::where('pertanyaan_id', $pertanyaanId)
                ->whereNull('value')
                ->first();
        }

        $match = OpsiJawaban::where('pertanyaan_id', $pertanyaanId)
            ->whereNotNull('value')
            ->where('value', '>=', $input)
            ->orderBy('value', 'asc')
            ->first();

        if (!$match) {
            $match = OpsiJawaban::where('pertanyaan_id', $pertanyaanId)
                ->whereNotNull('value')
                ->orderBy('value', 'desc')
                ->first();
        }

        return $match;
    }

    /**
     * COUNT: Total jawaban valid (sudah diisi claim_value dan evidence_url)
     */
    public function countValidAnswers($penugasan)
    {
        return ResponPenugasan::where('penugasan_id', $penugasan->id)
            ->whereNotNull('jawaban_id')
            ->count();
    }

    /**
     * PREVIEW: Mengambil seluruh jawaban dari satu Penugasan
     */
    public function getAllAnswersByPenugasan($penugasanId)
    {
        return ResponPenugasan::where('penugasan_id', $penugasanId)->get();
    }

    /**
     * Mencari record identitas berdasarkan penugasan_id.
     */
    public function findIdentitasByPenugasanId(int $penugasanId)
    {
        return Identitas::where('penugasan_id', $penugasanId)->first();
    }

    public function findInstitusiById(string $id)
    {
        return Institusi::find($id);
    }

    public function updateInstitusi(string $id, array $data)
    {
        return Institusi::where('id', $id)->update($data);
    }
    /**
     * Logic Create or Update (Upsert) untuk tabel Identitas.
     */
    public function upsertIdentitas(int $penugasanId, array $data)
    {
        return Identitas::updateOrCreate(
            ['penugasan_id' => $penugasanId],
            $data
        );
    }

    /**
     * Logic Upsert untuk tabel Agama berdasarkan identitas_id dan nama agama.
     */
    public function upsertAgama(int $identitasId, string $agama, int $jumlah)
    {
        return Agama::updateOrCreate(
            [
                'identitas_id' => $identitasId,
                'agama' => $agama
            ],
            ['jumlah' => $jumlah]
        );
    }

    public function getAssignedPenugasansByReviewer(int $reviewerId)
    {
        return $this->model
            ->where(function ($query) use ($reviewerId) {
                $query->where('reviewer_1_id', $reviewerId)
                    ->orWhere('reviewer_2_id', $reviewerId)
                    ->orWhere('reviewer_3_id', $reviewerId);
            })
            ->with(['institusi' => function ($query) {
                $query->select('id', 'nama_institusi', 'jenis_institusi');
            }])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function isUserActive(int $userId): bool
    {
        $user = User::find($userId);
        return $user && $user->status === 'ACTIVE';
    }

    public function getProfilePeserta(int $pesertaId)
    {
        return $this->model
            ->where('user_id', $pesertaId)
            ->with(['institusi', 'identitas.agamas', 'identitas'])
            ->first();
    }

    public function getDetailPenugasanByReviewer(int $reviewerId, int $pesertaId)
    {
        return $this->model
            ->where(function ($query) use ($reviewerId) {
                $query->where('reviewer_1_id', $reviewerId)
                    ->orWhere('reviewer_2_id', $reviewerId)
                    ->orWhere('reviewer_3_id', $reviewerId);
            })
            ->where('id', $pesertaId)
            ->with([
                'user',
                'institusi',
                'identitas.agamas',
                'jawabans.pertanyaan.kategori',
                'jawabans.pertanyaan.OpsiJawaban',
                'jawabans.jawabanOpsi',
            ])
            ->first();
    }

    /**
     * Get all answers for an penugasan grouped by category, including pertanyaan & opsi data
     */
    public function getAnswersGroupedByCategory(int $penugasanId)
    {
        return ResponPenugasan::where('penugasan_id', $penugasanId)
            ->with([
                'pertanyaan.kategori',
                'pertanyaan.OpsiJawaban',
                'jawabanOpsi',
            ])
            ->get();
    }

    /**
     * Update status of an penugasan
     */
    public function updateStatusPenugasan(int $penugasanId, string $status)
    {
        $penugasan = $this->model->find($penugasanId);
        if ($penugasan) {
            $penugasan->update(['status' => $status]);
        }
        return true;
    }
    public function getIdentitasWithAgama(int $penugasanId)
    {
        return Identitas::with('agamas')
            ->where('penugasan_id', $penugasanId)
            ->first();
    }

    public function getLatestJawabanUpdate(int $penugasanId)
    {
        return ResponPenugasan::where('penugasan_id', $penugasanId)->max('updated_at');
    }

    public function updateRekapSkor(int $penugasanId, array $rekap)
    {
        return $this->model->where('id', $penugasanId)->update([
            'skor_rekap_json' => json_encode($rekap),
        ]);
    }

    public function batchUpdateStatusByYear(string $tahun, array $fromStatuses, string $toStatus)
    {
        $penugasans = $this->model
            ->where('tahun_periode', $tahun)
            ->whereIn('status', $fromStatuses)
            ->where('status', '!=', $toStatus)
            ->get();

        if ($penugasans->isEmpty()) {
            return 0;
        }

        $ids     = $penugasans->pluck('id')->all();

        $this->model->whereIn('id', $ids)->update(['status' => $toStatus]);

        return count($ids);
    }

    public function countValidReviewerScores(int $penugasanId)
    {
        return ResponPenugasan::where('penugasan_id', $penugasanId)
            ->whereNotNull('skor_validasi_reviewer')
            ->whereNotNull('note_reviewer')
            ->whereRaw('CHAR_LENGTH(TRIM(note_reviewer)) >= 20')
            ->count();
    }

    public function countValidReviewerScoresForRole(int $penugasanId, string $roleIndex)
    {
        $answers = ResponPenugasan::where('penugasan_id', $penugasanId)->get();
        $count = 0;
        foreach ($answers as $answer) {
            $grades = $answer->reviewer_grades_json;
            if (is_string($grades)) {
                $grades = json_decode($grades, true);
            }
            if (isset($grades[$roleIndex]['skor']) && $grades[$roleIndex]['skor'] !== null && $grades[$roleIndex]['skor'] !== '') {
                $count++;
            }
        }
        return $count;
    }
}
