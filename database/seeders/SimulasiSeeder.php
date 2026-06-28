<?php

namespace Database\Seeders;

use App\Models\Agama;
use App\Models\Penugasan;
use App\Models\Identitas;
use App\Models\Institusi;
use App\Models\Reviewer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SimulasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Flow:
     * 1. Buat/update akun Reviewer beserta profil reviewer-nya
     * 2. Buat/update akun Peserta (sudah ACTIVE, email terverifikasi)
     * 3. Buat/update Institusi yang terhubung ke peserta
     * 4. Buat/update Penugasan (status ACTIVE) yang sudah diikat ke reviewer
     * 5. Buat/update Identitas (data baseline) beserta dokumen legal
     * 6. Buat/update data Agama (demografi mahasiswa)
     */
    public function run(): void
    {
        // ─────────────────────────────────────────
        // 1. REVIEWER
        // ─────────────────────────────────────────
        $reviewerUser = User::updateOrCreate(
            ['email' => 'reviewer@patriotmetric.com'],
            [
                'password'          => bcrypt('Reviewer123!'),
                'role'              => 'REVIEWER',
                'status'            => 'ACTIVE',
                'email_verified_at' => now(),
            ]
        );

        $reviewer = Reviewer::updateOrCreate(
            ['user_id' => $reviewerUser->id],
            [
                'nama_lengkap' => 'Dr. Budi Santoso, M.Pd',
                'nip'          => '198001012000031001',
            ]
        );

        // ─────────────────────────────────────────
        // 2. PESERTA
        // ─────────────────────────────────────────
        $pesertaUser = User::updateOrCreate(
            ['email' => 'pic@upnjatim.ac.id'],
            [
                'password'          => bcrypt('Peserta123!'),
                'role'              => 'PESERTA',
                'status'            => 'ACTIVE',
                'email_verified_at' => now(),
            ]
        );

        // ─────────────────────────────────────────
        // 3. INSTITUSI
        // ─────────────────────────────────────────
        $domain    = 'upnjatim.ac.id';
        $institusi = Institusi::where('domain_email', $domain)->first();

        if (!$institusi) {
            $institusi = Institusi::create([
                'id'             => (string) Str::uuid(),
                'nama_institusi' => 'Universitas Pembangunan Nasional "Veteran" Jawa Timur',
                'jenis_institusi' => 'PTN',
                'domain_email'   => $domain,
                'logo_url'       => null,
            ]);
        } else {
            $institusi->update([
                'nama_institusi'  => 'Universitas Pembangunan Nasional "Veteran" Jawa Timur',
                'jenis_institusi' => 'PTN',
            ]);
        }

        // ─────────────────────────────────────────
        // 4. PENUGASAN
        // ─────────────────────────────────────────
        $tahunPeriode = 2026;

        $penugasan = Penugasan::where('user_id', $pesertaUser->id)
            ->where('tahun_periode', $tahunPeriode)
            ->first();

        $penugasanData = [
            'institution_id' => $institusi->id,
            'nama_pic'       => 'Prof. Dr. Ir. Rossyda Priyadashini, MP',
            'jabatan_pic'    => 'Wakil Rektor 4',
            'no_hp_pic'      => '081234567890',
            'status'         => 'ACTIVE',
            'reviewer_id'    => $reviewer->id,
            'reviewer_1_id'  => $reviewer->id,
        ];

        if (!$penugasan) {
            $penugasan = Penugasan::create(array_merge([
                'user_id'       => $pesertaUser->id,
                'tahun_periode' => $tahunPeriode,
            ], $penugasanData));
        } else {
            $penugasan->update($penugasanData);
        }

        // ─────────────────────────────────────────
        // 5. IDENTITAS
        // ─────────────────────────────────────────
        $identitasData = [
            'jml_mahasiswa' => 23000,
            'jml_dosen'     => 2300,
            'jml_tendik'    => 300,
            'jml_prodi'     => 40,
            'jml_ukm'       => 20,
            'jml_ormawa'    => 40,
            'jml_fakultas'  => 8,
            'visi'          => 'Unggul dalam Ilmu Pengetahuan, Teknologi, dan Humaniora yang Berwawasan Kebangsaan demi Kemaslahatan Bangsa.',
            'misi'          => implode("\n", [
                '1. Menyelenggarakan pendidikan tinggi yang berkualitas dan berwawasan bela negara.',
                '2. Mengembangkan penelitian inovatif yang memberikan kontribusi nyata bagi masyarakat.',
                '3. Melaksanakan pengabdian kepada masyarakat berbasis kebutuhan lokal dan nasional.',
                '4. Membangun kerjasama strategis di tingkat nasional dan internasional.',
            ]),
            'legal_documents' => [
                'logo_url'            => null,
                'profil_pt'           => null,
                'sk_pendirian'        => null,
                'surat_pernyataan'    => null,
                'struktur_organisasi' => null,
            ],
            'is_verified' => true,
        ];

        $identitas = Identitas::where('penugasan_id', $penugasan->id)->first();

        if (!$identitas) {
            $identitas = Identitas::create(array_merge(
                ['penugasan_id' => $penugasan->id],
                $identitasData
            ));
        } else {
            $identitas->update($identitasData);
        }

        // ─────────────────────────────────────────
        // 6. AGAMA (Demografi Mahasiswa)
        // ─────────────────────────────────────────
        $religions = [
            'islam'    => 20000,
            'kristen'  => 1500,
            'katolik'  => 1000,
            'hindu'    => 200,
            'buddha'   => 300,
            'konghucu' => 0,
        ];

        foreach ($religions as $agamaName => $jumlah) {
            Agama::updateOrCreate(
                [
                    'identitas_id' => $identitas->id,
                    'agama'        => $agamaName,
                ],
                [
                    'jumlah' => $jumlah,
                ]
            );
        }

        $this->command->info('SimulasiSeeder selesai!');
        $this->command->info('  Reviewer : reviewer@patriotmetric.com / Reviewer123!');
        $this->command->info('  Peserta  : pic@upnjatim.ac.id / Peserta123!');
    }
}
