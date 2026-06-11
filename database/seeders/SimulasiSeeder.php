<?php

namespace Database\Seeders;

use App\Models\Agama;
use App\Models\Assessment;
use App\Models\Identitas;
use App\Models\Institusi;
use App\Models\Reviewer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SimulasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // 1. Seed Reviewer User & profile record
        // 2. Seed Participant User (Peserta X)
        // 3. Seed Institusi
        // 4. Seed Assessment linked to the reviewer
        // 5. Seed Identitas (legal documents without kalender_akademik)
        // 6. Seed Agama linked to Identitas

        $reviewerUser = User::where('email', 'reviewer@gmail.com')->first();
        if (!$reviewerUser) {
            $reviewerUser = User::create([
                'email' => 'reviewer@gmail.com',
                'password' => bcrypt('12313123'),
                'role' => 'REVIEWER',
                'status' => 'ACTIVE',
                'email_verified_at' => now(),
            ]);
        }

        $reviewer = Reviewer::where('user_id', $reviewerUser->id)->first();
        if (!$reviewer) {
            $reviewer = Reviewer::create([
                'user_id' => $reviewerUser->id,
                'nama_lengkap' => 'Reviewer',
                'nip' => '198001012000031001',
            ]);
        }


        $pesertaUser = User::where('email', '23082010166@student.upnjatim.ac.id')->first();
        if (!$pesertaUser) {
            $pesertaUser = User::create([
                'email' => '23082010166@student.upnjatim.ac.id',
                'password' => bcrypt('Ilham6769'),
                'role' => 'PESERTA',
                'status' => 'ACTIVE',
                'email_verified_at' => now(),
            ]);
        }


        $institusi = Institusi::where('domain_email', 'student.upnjatim.ac.id')->first();
        if (!$institusi) {
            $institusi = Institusi::create([
                'id' => (string) Str::uuid(),
                'nama_institusi' => 'Universitas Pembangunan Nasional Jawa Timur',
                'jenis_institusi' => 'PTN',
                'domain_email' => 'student.upnjatim.ac.id',
                'logo_url' => '/storage/verifikasi/logo.webp',
            ]);
        } else {
            $institusi->update([
                'nama_institusi' => 'Universitas Pembangunan Nasional Jawa Timur',
                'jenis_institusi' => 'PTN',
                'logo_url' => '/storage/verifikasi/logo.webp',
            ]);
        }


        $assessment = Assessment::where('user_id', $pesertaUser->id)->where('tahun_periode', 2026)->first();
        if (!$assessment) {
            $assessment = Assessment::create([
                'user_id' => $pesertaUser->id,
                'tahun_periode' => 2026,
                'institution_id' => $institusi->id,
                'nama_pic' => 'Prof. Dr. Ir. Rossyda Priyadashini, MP',
                'jabatan_pic' => 'Wakil Rektor 4',
                'no_hp_pic' => '081234567890',
                'status' => 'ACTIVE',
                'reviewer_id' => $reviewer->id,
            ]);
        } else {
            $assessment->update([
                'institution_id' => $institusi->id,
                'nama_pic' => 'Prof. Dr. Ir. Rossyda Priyadashini, MP',
                'jabatan_pic' => 'Wakil Rektor 4',
                'no_hp_pic' => '081234567890',
                'status' => 'ACTIVE',
                'reviewer_id' => $reviewer->id,
            ]);
        }


        $identitas = Identitas::where('Assessment_id', $assessment->id)->first();
        if (!$identitas) {
            $identitas = Identitas::create([
                'Assessment_id' => $assessment->id,
                'jml_mahasiswa' => 23000,
                'jml_dosen' => 2300,
                'jml_tendik' => 300,
                'jml_prodi' => 40,
                'jml_ukm' => 20,
                'jml_ormawa' => 40,
                'jml_fakultas' => 8,
                'visi' => 'Unggul dalam Ilmu Pengetahuan, Teknologi, dan Humaniora yang Berwawasan Kebangsaan demi Kemaslahatan Bangsa',
                'misi' => 'Visi Institusi Peserta X',
                'legal_documents' => [
                    'logo_url' => '/storage/verifikasi/logo.webp',
                    'profil_pt' => '/storage/verifikasi/profil.pdf',
                    'sk_pendirian' => '/storage/verifikasi/sk_pendirian.pdf',
                    'surat_pernyataan' => '/storage/verifikasi/surat_pernyataan.pdf',
                    'struktur_organisasi' => '/storage/verifikasi/struktur_organisasi.pdf',
                ],
                'is_verified' => false,
            ]);
        } else {
            $identitas->update([
                'jml_mahasiswa' => 23000,
                'jml_dosen' => 2300,
                'jml_tendik' => 300,
                'jml_prodi' => 40,
                'jml_ukm' => 20,
                'jml_ormawa' => 40,
                'jml_fakultas' => 8,
                'visi' => 'Visi Institusi Peserta X',
                'misi' => 'Misi Institusi Peserta X',
                'legal_documents' => [
                    'logo_url' => '/storage/verifikasi/logo.webp',
                    'profil_pt' => '/storage/verifikasi/profil.pdf',
                    'sk_pendirian' => '/storage/verifikasi/sk_pendirian.pdf',
                    'surat_pernyataan' => '/storage/verifikasi/surat_pernyataan.pdf',
                    'struktur_organisasi' => '/storage/verifikasi/struktur_organisasi.pdf',
                ],
                'is_verified' => false,
            ]);
        }

        $religions = [
            'islam' => 20000,
            'kristen' => 1500,
            'katolik' => 1000,
            'hindu' => 0,
            'buddha' => 500,
            'konghucu' => 0,
        ];

        foreach ($religions as $agamaName => $jumlah) {
            Agama::updateOrCreate(
                [
                    'identitas_id' => $identitas->id,
                    'agama' => $agamaName,
                ],
                [
                    'jumlah' => $jumlah,
                ]
            );
        }
    }
}
