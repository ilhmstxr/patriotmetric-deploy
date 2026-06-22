<?php

namespace Database\Seeders;

use App\Models\ComproContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComproContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds all 7 compro pages with content extracted from blade templates.
     * Uses firstOrCreate for idempotency — existing records are never overwritten.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedWelcomePage();
            $this->seedProfilePage();
            $this->seedTimPage();
            $this->seedPenghargaanPage();
            $this->seedPanduanPage();
            $this->seedPengumumanPage();
            $this->seedBeritaPage();
        });

        // Clear all compro content cache after seeding
        $pages = ['welcome', 'profile', 'tim', 'penghargaan', 'panduan', 'pengumuman', 'berita'];
        foreach ($pages as $page) {
            \Illuminate\Support\Facades\Cache::forget("compro_content.{$page}");
        }
    }

    /**
     * Helper method to create a content record (idempotent).
     */
    private function createContent(string $page, string $section, string $key, string $type, mixed $value, int $order = 0): void
    {
        ComproContent::updateOrCreate(
            ['page' => $page, 'section' => $section, 'key' => $key],
            ['type' => $type, 'value' => is_array($value) ? json_encode($value) : $value, 'order' => $order]
        );
    }    /**
     * Seed Welcome (Homepage) content.
     */
    private function seedWelcomePage(): void
    {
        $page = 'welcome';

        // Hero Section
        $this->createContent($page, 'hero', 'judul', 'text', 'Membangun Karakter Bangsa dari Kampus', 1);
        $this->createContent($page, 'hero', 'deskripsi', 'text', 'Sebuah sistem pemeringkatan nasional yang didedikasikan untuk mengukur, membina, dan mengapresiasi nilai-nilai bela negara di lingkungan pendidikan tinggi.', 2);
        $this->createContent($page, 'hero', 'background_image', 'image', 'welcome/homepage-bg-1.webp', 3);

        // About Section
        $this->createContent($page, 'about', 'judul', 'text', 'Patriot Metric', 1);
        $this->createContent($page, 'about', 'deskripsi', 'richtext', '<p>Patriot Metric University Ranking merupakan sistem pemeringkatan kinerja yang mengintegrasikan pengukuran capaian akademik, tata kelola institusi,dan pembentukan karakter Bela Negara ke dalam satu kerangka nilai yang utuh, melalui aspek kebijakan, kelembagaan, dan patriotisme peserta didik.</p><ul><li><p>Refleksi kinerja institusi berbasis nilai</p></li><li><p>Penguatan arah kebijakan dan tata kelola</p></li><li><p>Internalisasi nilai kebangsaan secara terukur</p></li><li><p>Jejaring dan pembelajaran antar institusi</p></li></ul>', 2);
        $this->createContent($page, 'about', 'video_url', 'url', 'https://www.youtube.com/embed/nB4YzOhnkBo?si=KXFTn2dRpO-TDdKc', 3);

        // Institusi Partisipan Section
        $this->createContent($page, 'institusi', 'judul', 'text', 'Institusi yang Telah Berpartisipasi', 1);
        $this->createContent($page, 'institusi', 'deskripsi', 'text', 'Bergabung bersama perguruan tinggi terbaik Indonesia dalam mewujudkan kampus berkarakter bela negara.', 2);
        $this->createContent($page, 'institusi', 'daftar_baris_1', 'repeater', [
            ['nama' => 'UPN "Veteran" Jawa Timur', 'logo' => 'welcome/institusi-upn-veteran-jatim.webp'],
            ['nama' => 'Universitas Negeri Surabaya', 'logo' => 'welcome/institusi-unesa.webp'],
            ['nama' => 'Universitas 17 Agustus', 'logo' => 'welcome/institusi-untag.webp'],
        ], 3);
        $this->createContent($page, 'institusi', 'daftar_baris_2', 'repeater', [
            ['nama' => 'UPN "Veteran" Yogyakarta', 'logo' => 'welcome/institusi-upn-yogya.webp'],
            ['nama' => 'Universitas Bhayangkara Jakarta Raya', 'logo' => 'welcome/institusi-ubhara.webp'],
            ['nama' => 'Universitas Mega Buana Palopo', 'logo' => 'welcome/mega-buana-palopo.webp'],
        ], 4);

        // Timeline Section
        $this->createContent($page, 'timeline', 'judul', 'text', 'Timeline Patriot Metric', 1);
        $this->createContent($page, 'timeline', 'deskripsi', 'text', 'Jadwal dan tahapan proses pemeringkatan institusi Anda.', 2);
        $this->createContent($page, 'timeline', 'daftar', 'repeater', [
            ['nomor' => '01', 'tanggal' => 'Juli - September', 'judul' => 'Registrasi & Pengisian Data', 'deskripsi' => 'Periode pendaftaran institusi dan pengisian data PIC peserta dan Rubrik melalui portal Patriot Metric.'],
            ['nomor' => '02', 'tanggal' => 'Oktober - November', 'judul' => 'Penilaian & Validasi', 'deskripsi' => 'Periode penilaian hasil pengisian rubrik peserta oleh reviewer.'],
            ['nomor' => '03', 'tanggal' => 'Desember', 'judul' => 'Pengumuman Hasil Penilaian', 'deskripsi' => 'Pengumuman hasil final skor pemeringkatan.'],
        ], 3);

        // Instagram Section
        $this->createContent($page, 'instagram', 'judul', 'text', 'Ikuti Aktivitas Kami', 1);
        $this->createContent($page, 'instagram', 'deskripsi', 'text', 'Pantau perkembangan terbaru Patriot Metric melalui Instagram kami.', 2);
        $this->createContent($page, 'instagram', 'posts', 'repeater', [
            ['url' => 'https://www.instagram.com/reel/DQ5YayFEtfN/', 'gambar' => null, 'alt_text' => 'Post Instagram Patriot Metric 1'],
            ['url' => 'https://www.instagram.com/p/DQssRuxksft/', 'gambar' => null, 'alt_text' => 'Post Instagram Patriot Metric 2'],
        ], 3);
    }

    /**
     * Seed Profile page content.
     */
    private function seedProfilePage(): void
    {
        $page = 'profile';

        // Hero Section
        $this->createContent($page, 'hero', 'judul', 'text', 'UPN VETERAN JATIM PATRIOT METRIC <br> <span class="block mt-1 text-[20px] sm:text-[28px] md:text-[32px] font-semibold text-white/90">UNIVERSITY RANKING</span>', 1);
        $this->createContent($page, 'hero', 'deskripsi', 'text', 'Sebuah sistem pemeringkatan nasional yang didedikasikan untuk mengukur, membina, dan mengapresiasi nilai-nilai bela negara di lingkungan pendidikan tinggi.', 2);
        $this->createContent($page, 'hero', 'background_image', 'image', 'profile/bg.webp', 3);

        // Latar Belakang Section
        $this->createContent($page, 'latar-belakang', 'judul', 'text', 'Latar Belakang', 1);
        $this->createContent($page, 'latar-belakang', 'deskripsi', 'richtext', '<p>Di tengah arus globalisasi, nilai patriotisme menghadapi tantangan serius, mulai dari
menurunnya pemahaman sejarah, derasnya arus
disinformasi dan radikalisme digital yang memicu
polarisasi, hingga meningkatnya individualisme
yang menggerus kepedulian sosial.</p><p>
<strong>Patriot Metric</strong> hadir sebagai jawaban atas
kebutuhan tersebut. Gagasan ini berangkat dari
identitas historis UPN “Veteran” Jawa Timur
sebagai kampus Bela Negara yang lahir dari
semangat para pejuang kemerdekaan, dengan
semboyan <i>Widya Mwat Yasa</i> – ilmu pengetahuan
yang diabdikan untuk pembangunan bangsa.</p>
<p><strong>Patriot Metric</strong> merupakan instrumen evaluasi yang objektif dan
terstandarisasi, agar pembinaan kesadaran bela negara, khususnya
dalam konteks nasionalisme dan patriotisme, dapat dianalisis,
dievaluasi, dan dikembangkan secara berkelanjutan.</p>
<p>Instrumen Patriot Metric dirancang berbasis konstruk psikososial
sehingga tidak hanya dipahami secara normatif, tetapi juga dapat
memotivasi institusi pendidikan tinggi melalui internalisasi,
implementasi, dan pengembangan nilai-nilai bela negara serta
karakter kebangsaan dalam Tridharma Perguruan Tinggi.</p>
', 2);

        // Tujuan Utama Section
        $this->createContent($page, 'tujuan-utama', 'judul', 'text', 'Tujuan Pemeringkatan', 1);
        $this->createContent($page, 'tujuan-utama', 'deskripsi', 'text', ' ', 2);
        $this->createContent($page, 'tujuan-utama', 'daftar', 'repeater', [
            ['nomor' => '01', 'judul' => "Menjadi Instrumen\nEvaluasi Pembinaan\nKesadaran Bela Negara", 'deskripsi' => "Menilai sejauh mana upaya\npembinaan karakter bela negara\ntelah diinternalisasikan melalui\nkebijakan dan program\nperguruan tinggi."],
            ['nomor' => '02', 'judul' => "Memperkuat Ekosistem\nPerguruan Tinggi Berbasis\nNilai-nilai Bela Negara", 'deskripsi' => "Tercipta ekosistem perguruan\ntinggi yang lebih kuat dalam\nmenanamkan nilai-nilai bela\nnegara dan tanggung jawab\nsosial melalui berbagai aspek\nTridharma Perguruan Tinggi."],
            ['nomor' => '03', 'judul' => "Mendorong Sinergi\nAntar-Perguruan Tinggi", 'deskripsi' => "Perguruan Tinggi dapat saling\nmemotivasi, bersama-sama\nmembangun generasi muda\nyang berdaya saing global,\\nadaptif, dan patriotik."],
        ], 3);

        // Manfaat Pemeringkatan Section
        $this->createContent($page, 'manfaat-pemeringkatan', 'judul', 'text', 'Manfaat Pemeringkatan', 1);
        $this->createContent($page, 'manfaat-pemeringkatan', 'daftar', 'repeater', [
            ['nomor' => '01', 'judul' => 'Meningkatkan Kesadaran Bela Negara', 'deskripsi' => 'Mendorong Perguruan Tinggi untuk mewujudkan dan meningkatkan karakter bela negara.'],
            ['nomor' => '02', 'judul' => 'Membangun Jejaring dan Kolaborasi Nasional', 'deskripsi' => 'Membuka peluang bagi perguruan tinggi peserta menjadi bagian dari jejaring Patriot Metric yang memungkinkan kolaborasi di tingkat nasional.'],
            ['nomor' => '03', 'judul' => 'Mendapatkan Pengakuan dan Reputasi', 'deskripsi' => 'Meningkatkan citra dan reputasi perguruan tinggi sebagai kampus yang berkomitmen pada penguatan karakter bela negara.'],
            ['nomor' => '04', 'judul' => 'Mendorong Perubahan dan Aksi Sosial', 'deskripsi' => 'Perguruan tinggi dapat melakukan perubahan karakter dalam segala aspek Tridharma Perguruan Tinggi.'],
        ], 2);
    }

    /**
     * Seed Tim page content.
     */
    private function seedTimPage(): void
    {
        $page = 'tim';

        // Hero Section
        $this->createContent($page, 'hero', 'judul', 'text', 'Tim Kami', 1);
        $this->createContent($page, 'hero', 'deskripsi', 'text', 'Para profesional yang berdedikasi dalam mengembangkan dan mengelola sistem pemeringkatan Patriot Metric.', 2);

        // Team Grid Section
        $this->createContent($page, 'team-grid', 'daftar', 'repeater', [
            ['nama' => 'Lorem Ipsum', 'role' => 'Lorem Ipsum', 'foto' => 'tim/blank-profile.webp'],
            ['nama' => 'Lorem Ipsum', 'role' => 'Lorem Ipsum', 'foto' => 'tim/blank-profile.webp'],
            ['nama' => 'Lorem Ipsum', 'role' => 'Lorem Ipsum', 'foto' => 'tim/blank-profile.webp'],
            ['nama' => 'Lorem Ipsum', 'role' => 'Lorem Ipsum', 'foto' => 'tim/blank-profile.webp'],
        ], 1);
    }

    /**
     * Seed Penghargaan page content.
     */
    private function seedPenghargaanPage(): void
    {
        $page = 'penghargaan';

        // Hero Section
        $this->createContent($page, 'hero', 'judul', 'text', 'Galeri Penghargaan', 1);
        $this->createContent($page, 'hero', 'deskripsi', 'text', 'Penghormatan tertinggi bagi institusi yang telah membuktikan dedikasinya dalam membangun karakter patriotik dan bela negara.', 2);
        $this->createContent($page, 'hero', 'background_image', 'image', 'penghargaan/hero-bg.webp', 3);

        // Daftar Penerima Section
        $this->createContent($page, 'daftar-penerima', 'judul', 'text', 'Daftar Penganugrahan Patriot Metric', 1);
        $this->createContent($page, 'daftar-penerima', 'daftar', 'repeater', [
            ['nama' => 'UPN "Veteran" Jawa Timur', 'logo' => 'welcome/logo-upn.webp', 'rating' => 5],
            ['nama' => 'Universitas Negeri Surabaya', 'logo' => 'welcome/logo-upn.webp', 'rating' => 5],
            ['nama' => 'Universitas 17 Agustus', 'logo' => 'welcome/logo-upn.webp', 'rating' => 4.5],
            ['nama' => 'UPN "Veteran" Yogyakarta', 'logo' => 'welcome/logo-upn.webp', 'rating' => 4],
            ['nama' => 'Universitas Bhayangkara Jakarta Raya', 'logo' => 'welcome/logo-upn.webp', 'rating' => 5],
            ['nama' => 'Universitas Mega Buana Palopo', 'logo' => 'welcome/logo-upn.webp', 'rating' => 4],
        ], 2);
    }

    /**
     * Seed Panduan page content.
     */
    private function seedPanduanPage(): void
    {
        $page = 'panduan';

        // Hero Section
        $this->createContent($page, 'hero', 'judul', 'text', 'Panduan Teknis Sistem', 1);
        $this->createContent($page, 'hero', 'deskripsi', 'text', 'Panduan lengkap bagi peserta baru untuk memahami apa itu Patriot Metric dan cara menggunakan sistem pemeringkatan.', 2);
        $this->createContent($page, 'hero', 'tombol_teks', 'text', 'Pedoman Patriot Metric UPN Veteran Jatim', 3);
        $this->createContent($page, 'hero', 'tombol_link', 'url', 'https://bit.ly/PEDOMANPATRIOTMETRIC', 4);

        // Persyaratan Sistem Section
        $this->createContent($page, 'persyaratan', 'daftar', 'repeater', [
            ['icon' => 'globe', 'judul' => 'Browser Modern', 'deskripsi' => 'Gunakan Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari versi terbaru untuk pengalaman terbaik.'],
            ['icon' => 'wifi', 'judul' => 'Koneksi Internet Stabil', 'deskripsi' => 'Pastikan perangkat Anda terhubung ke internet dengan koneksi yang stabil selama proses pengisian data.'],
            ['icon' => 'user-check', 'judul' => 'Akun Terdaftar', 'deskripsi' => 'Anda memerlukan akun PIC (Person in Charge) yang telah diverifikasi untuk mengakses sistem pemeringkatan.'],
            ['icon' => 'file-text', 'judul' => 'Dokumen Pendukung', 'deskripsi' => 'Siapkan dokumen bukti pendukung dalam format PDF atau gambar (JPG/PNG) dengan ukuran maksimal 5MB per file.'],
            ['icon' => 'shield', 'judul' => 'Surat Tugas Resmi', 'deskripsi' => 'PIC wajib memiliki Surat Tugas dari rektorat atau pimpinan perguruan tinggi sebagai bukti penunjukan resmi.'],
            ['icon' => 'monitor', 'judul' => 'Perangkat Desktop/Laptop', 'deskripsi' => 'Disarankan menggunakan perangkat desktop atau laptop untuk kenyamanan pengisian rubrik penilaian.'],
        ], 1);

        // Panduan Langkah Section
        $this->createContent($page, 'panduan-langkah', 'judul', 'text', 'Langkah Penggunaan Sistem', 1);
        $this->createContent($page, 'panduan-langkah', 'daftar', 'repeater', [
            ['nomor' => '1', 'judul' => 'Registrasi Akun', 'deskripsi' => 'Kunjungi halaman pendaftaran Patriot Metric dan buat akun PIC dengan mengisi data diri, institusi, dan mengunggah Surat Tugas. Setelah registrasi, tunggu proses verifikasi oleh admin.'],
            ['nomor' => '2', 'judul' => 'Login ke Dashboard', 'deskripsi' => 'Setelah akun terverifikasi, login menggunakan email dan password yang telah didaftarkan. Anda akan diarahkan ke dashboard utama institusi.'],
            ['nomor' => '3', 'judul' => 'Lengkapi Profil Institusi', 'deskripsi' => 'Pada dashboard, lengkapi data profil institusi Anda meliputi informasi umum, visi-misi, dan data pendukung lainnya yang diminta oleh sistem.'],
            ['nomor' => '4', 'judul' => 'Isi Rubrik Penilaian', 'deskripsi' => 'Buka menu Rubrik Penilaian dan isi setiap indikator yang tersedia. Setiap indikator memiliki panduan pengisian dan skor maksimal yang telah ditetapkan. Unggah bukti pendukung sesuai ketentuan.'],
            ['nomor' => '5', 'judul' => 'Review & Submit', 'deskripsi' => 'Setelah semua rubrik terisi, periksa kembali kelengkapan data pada halaman review. Pastikan semua dokumen pendukung telah diunggah sebelum melakukan submit final.'],
            ['nomor' => '6', 'judul' => 'Validasi oleh Tim Evaluator', 'deskripsi' => 'Tim Evaluator akan memverifikasi data yang telah Anda submit, termasuk kemungkinan wawancara dan visitasi lapangan. Pantau status validasi melalui dashboard.'],
            ['nomor' => '7', 'judul' => 'Lihat Hasil Pemeringkatan', 'deskripsi' => 'Setelah proses penilaian selesai, hasil pemeringkatan institusi Anda akan tersedia di dashboard dalam bentuk skor dan peringkat bintang.'],
        ], 2);

        // Catatan Teknis Section
        $this->createContent($page, 'catatan', 'judul', 'text', 'Catatan Teknis', 1);
        $this->createContent($page, 'catatan', 'daftar', 'repeater', [
            ['tipe' => 'warning', 'judul' => 'Batas Waktu Pengisian', 'deskripsi' => 'Pastikan Anda menyelesaikan pengisian rubrik sebelum batas waktu yang ditentukan. Data yang belum di-submit sebelum deadline tidak akan diproses oleh Tim Evaluator.'],
            ['tipe' => 'info', 'judul' => 'Simpan Berkala', 'deskripsi' => 'Sistem mendukung penyimpanan bertahap (draft). Anda tidak perlu menyelesaikan semua rubrik dalam satu sesi. Klik tombol "Simpan Draft" secara berkala untuk menghindari kehilangan data.'],
            ['tipe' => 'tip', 'judul' => 'Format Dokumen Pendukung', 'deskripsi' => 'Untuk mempercepat proses validasi, gunakan penamaan file yang jelas dan deskriptif. Contoh: "SK_Rektor_BelaNegaraProgram_2025.pdf". Pastikan dokumen memiliki resolusi yang cukup dan terbaca dengan jelas.'],
            ['tipe' => 'info', 'judul' => 'Bantuan Teknis', 'deskripsi' => 'Jika mengalami kendala teknis, Anda dapat menghubungi tim support melalui fitur bantuan di dashboard atau melalui email yang tertera di halaman kontak.'],
        ], 2);
    }

    /**
     * Seed Pengumuman page content.
     */
    private function seedPengumumanPage(): void
    {
        $page = 'pengumuman';

        // Hero Section
        $this->createContent($page, 'hero', 'judul', 'text', 'Pengumuman', 1);
        $this->createContent($page, 'hero', 'deskripsi', 'text', 'Dokumen resmi dan surat edaran Patriot Metric.', 2);

        // Dokumen Section
        $this->createContent($page, 'artikel', 'daftar', 'repeater', [
            ['tanggal' => '2025-07-01', 'judul' => 'Surat Edaran Pendaftaran Patriot Metric 2025', 'excerpt' => 'Surat edaran resmi untuk seluruh perguruan tinggi', 'dokumen' => ''],
            ['tanggal' => '2025-08-15', 'judul' => 'Panduan Pengisian Rubrik Penilaian Bela Negara', 'excerpt' => 'Petunjuk teknis pengisian rubrik', 'dokumen' => ''],
            ['tanggal' => '2025-09-01', 'judul' => 'SK Penetapan Tim Reviewer Patriot Metric 2025', 'excerpt' => 'Surat keputusan penetapan tim penilai', 'dokumen' => ''],
            ['tanggal' => '2025-10-01', 'judul' => 'Jadwal Pelaksanaan Penilaian Periode 2025-2026', 'excerpt' => 'Timeline resmi kegiatan penilaian', 'dokumen' => ''],
            ['tanggal' => '2025-11-15', 'judul' => 'Surat Edaran Perpanjangan Batas Waktu Pengumpulan Data', 'excerpt' => 'Perpanjangan deadline pengumpulan dokumen', 'dokumen' => ''],
            ['tanggal' => '2026-01-10', 'judul' => 'Pengumuman Hasil Pemeringkatan Patriot Metric 2025', 'excerpt' => 'Hasil resmi pemeringkatan nasional', 'dokumen' => ''],
        ], 1);
    }

    /**
     * Seed Berita page content.
     */
    private function seedBeritaPage(): void
    {
        $page = 'berita';

        // Hero Section
        $this->createContent($page, 'hero', 'judul', 'text', 'Berita', 1);
        $this->createContent($page, 'hero', 'deskripsi', 'text', 'Informasi dan berita terbaru seputar kegiatan Patriot Metric dan perguruan tinggi.', 2);

        // Berita Section
        $this->createContent($page, 'berita', 'daftar', 'repeater', [
            [
                'tanggal' => '2025-01-27',
                'judul' => 'Mendiktisaintek Resmikan Menara Wimaya UPN "Veteran" Jawa Timur',
                'excerpt' => 'Menteri Pendidikan Tinggi, Sains, dan Teknologi Brian Yuliarto menekankan pentingnya pemanfaatan bersama sumber daya pendidikan tinggi saat meresmikan Menara Wimaya.',
                'konten' => 'Menteri Pendidikan Tinggi, Sains, dan Teknologi (Mendiktisaintek) Brian Yuliarto menekankan pentingnya pemanfaatan bersama sumber daya pendidikan tinggi, khususnya laboratorium dan peralatan riset, agar utilisasinya semakin optimal. Hal tersebut disampaikan saat meresmikan Menara Wimaya (Twin Tower) Universitas Pembangunan Nasional (UPN) "Veteran" Jawa Timur, Senin (27/1).' . "\n\n" . 'Mendiktisaintek mendorong penguatan kolaborasi antarkampus, baik perguruan tinggi negeri maupun swasta. Menurutnya, praktik berbagi fasilitas dan sumber daya telah menjadi hal yang lazim di berbagai negara dan perlu terus diperkuat di Indonesia. "Gedungnya memang ada di sini, tetapi fasilitas dan laboratoriumnya tentu bisa dimanfaatkan juga oleh kampus-kampus lain. Riset dan pengajaran bisa kita lakukan bersama-sama," ujar Menteri Brian.' . "\n\n" . 'Peresmian Menara Wimaya merupakan bagian dari kunjungan kerja Mendiktisaintek ke UPN "Veteran" Jawa Timur sekaligus bentuk apresiasi atas komitmen kampus dalam membangun fasilitas pendidikan tinggi secara mandiri dan berkelanjutan. Ia menilai pembangunan gedung tersebut sebagai capaian penting yang mencerminkan semangat kolegialitas dan kebersamaan antarkampus.' . "\n\n" . 'Selain infrastruktur, Mendiktisaintek juga mendorong pengembangan bahan ajar daring berbasis video yang dapat diakses secara luas asbagaia implementasi praktik perguruan tinggi kelas dunia. "Dosen-dosen terbaik bisa membagikan ilmunya ke seluas-luasnya masyarakat, dan kampus-kampus di berbagai daerah dapat memanfaatkannya," katanya.' . "\n\n" . 'Dalam kesempatan yang sama, Menteri Brian menegaskan bahwa keunggulan perguruan tinggi tidak hanya ditentukan oleh kemegahan infrastruktur, tetapi juga oleh lingkungan kampus yang tertata, bersih, dan memberikan layanan prima. Lingkungan tersebut dinilai penting dalam membentuk karakter, etos kerja, dan integritas mahasiswa sebagai calon sumber daya manusia unggul.' . "\n\n" . 'Rektor UPN "Veteran" Jawa Timur, Prof. Dr. Ir. Akhmad Fauzi, MMT.,IPU menyampaikan bahwa pembangunan Menara Wimaya merupakan bagian dari transformasi berkelanjutan kampus bela negara dalam memperkuat atmosfer akademik.' . "\n\n" . 'Menara Wimaya dibangun secara multi-years selama tiga tahun oleh PT PP, dengan peletakan batu pertama pada 16 Juli 2022 dan rampung pada 2025. Gedung kembar seluas sekitar 29.000 meter persegi dan terdiri atas 13 lantai ini didanai melalui Penerimaan Negara Bukan Pajak (PNBP), serta dirancang sebagai simbol konektivitas dan kolaborasi antarbidang keilmuan melalui sky bridge yang menghubungkan kedua menara.' . "\n\n" . 'Selain peresmian Menara Wimaya, UPN "Veteran" Jawa Timur juga meluncurkan dua program strategis, yakni Patriot Metric University Ranking dan U-Bridge Program. Kedua program tersebut diposisikan sebagai penguat transformasi institusi, sejalan dengan pembangunan infrastruktur utama kampus dalam mendukung kebijakan Diktisaintek Berdampak dan upaya mencetak lulusan berkarakter, berdaya saing, serta berkontribusi nyata bagi bangsa.',
                'gambar' => '',
            ],
            [
                'tanggal' => '2025-01-27',
                'judul' => 'Manfaat Pemeringkatan Patriot Metric bagi Perguruan Tinggi',
                'excerpt' => 'Patriot Metric memberikan berbagai manfaat strategis bagi perguruan tinggi yang berpartisipasi dalam pemeringkatan.',
                'konten' => 'Meningkatkan Kesadaran Bela Negara; Mendorong Perguruan Tinggi untuk mewujudkan dan meningkatkan karakter bela negara.' . "\n\n" . 'Membangun Jejaring dan Kolaborasi Nasional; Membuka peluang bagi perguruan tinggi peserta menjadi bagian dari jejaring Patriot Metric yang memungkinkan kolaborasi di tingkat nasional.' . "\n\n" . 'Mendapatkan Pengakuan dan Reputasi; Meningkatkan citra dan reputasi perguruan tinggi sebagai kampus yang berkomitmen pada penguatan karakter bela negara.' . "\n\n" . 'Mendorong Perubahan dan Aksi Sosial; Perguruan tinggi dapat melakukan perubahan karakter dalam segala aspek Tridharma Perguruan Tinggi.',
                'gambar' => '',
            ],
        ], 1);
    }
}
