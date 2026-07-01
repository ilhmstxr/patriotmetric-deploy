<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - Panduan</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.webp') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    </style>
</head>
<body class="bg-white min-h-screen">
    @php
        $hero = $content->get('hero', collect());
        $heroJudul = $hero->firstWhere('key', 'judul')?->value ?? 'Panduan & Pedoman';
        $heroDeskripsi = $hero->firstWhere('key', 'deskripsi')?->value ?? 'Informasi seputar penggunaan sistem dan pedoman penyelenggaraan Patriot Metric.';
        $heroBackground = $hero->firstWhere('key', 'background_image')?->value ?? '';
    @endphp

    <div x-data="{ activeTab: 'panduan' }" class="bg-white min-h-screen">
        {{-- Hero --}}
        <section class="relative bg-[#0a1f0d] overflow-hidden">
            <div class="absolute inset-0">
                @if(isset($heroBackground) && $heroBackground)
                    <img src="{{ url('cms-assets/' . $heroBackground) }}" alt="" class="w-full h-full object-cover object-center" />
                    <div class="absolute inset-0 bg-gradient-to-r from-[#1B5E20]/60 to-[#0a1f0d]/95"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0a1f0d]/90 via-transparent to-transparent"></div>
                @else
                    <div class="absolute inset-0 bg-[#1B5E20]"></div>
                @endif
            </div>
            <div class="absolute top-16 right-16 w-80 h-80 bg-[#d4af37]/15 rounded-full blur-[100px]"></div>
            <div class="relative max-w-[1200px] mx-auto px-6 md:px-8 py-16 md:py-22 text-center">
                <h1 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[30px] md:text-[44px] text-white leading-tight">{{ $heroJudul }}</h1>
                @if($heroDeskripsi)
                    <p class="mt-3 font-['Plus_Jakarta_Sans',sans-serif] text-[15px] md:text-[17px] leading-[26px] text-white/80 max-w-[540px] mx-auto">
                        {{ $heroDeskripsi }}
                    </p>
                @endif
            </div>
        </section>

        {{-- Main Content with Sidebar (Plain Design) --}}
        <div class="max-w-[1200px] mx-auto px-4 md:px-8 py-12 flex flex-col md:flex-row gap-8 lg:gap-16 items-stretch">
            
            {{-- Sidebar Plain --}}
            <div class="w-full md:w-[240px] shrink-0 md:border-r border-[#e2e8f0] pr-6">
                <div class="flex flex-col gap-2">
                    <button @click="activeTab = 'panduan'" 
                            :class="activeTab === 'panduan' ? 'text-[#1b5e20] font-bold border-l-2 border-[#1b5e20] pl-3' : 'text-[#64748b] hover:text-[#1d293d] border-l-2 border-transparent pl-3'"
                            class="text-left text-[16px] py-2 transition-colors">
                        Panduan Penggunaan
                    </button>
                    <button @click="activeTab = 'pedoman'" 
                            :class="activeTab === 'pedoman' ? 'text-[#1b5e20] font-bold border-l-2 border-[#1b5e20] pl-3' : 'text-[#64748b] hover:text-[#1d293d] border-l-2 border-transparent pl-3'"
                            class="text-left text-[16px] py-2 transition-colors">
                        Buku Pedoman
                    </button>
                </div>
            </div>

            {{-- Content Area Plain --}}
            <div class="flex-1 w-full pb-16">
                
                {{-- TAB PANDUAN --}}
                <div x-show="activeTab === 'panduan'" x-transition.opacity>
                    <h2 class="text-[32px] font-bold text-[#1d293d] mb-8">Panduan Menjadi Peserta</h2>

                    <div class="prose prose-slate max-w-none text-[#45556c] text-[16px] leading-relaxed">
                        <p class="mb-6">Berikut adalah panduan lengkap langkah-demi-langkah bagi perguruan tinggi untuk mengikuti proses pendaftaran dan pengisian evaluasi di sistem Patriot Metric.</p>
                        
                        <h3 class="text-[#1d293d] font-bold text-[22px] mt-10 mb-4">1. Persiapan Pendaftaran</h3>
                        <p>Pastikan Anda menggunakan perangkat berupa Laptop atau PC (tidak disarankan menggunakan ponsel pintar atau tablet). Kami sangat merekomendasikan penggunaan browser Google Chrome atau Microsoft Edge untuk pengalaman terbaik.</p>
                        <div class="my-6">
                            <!-- GANTI SRC DI BAWAH INI DENGAN SCREENSHOT YANG SESUAI -->
                            <img src="https://placehold.co/800x400/f8fafc/64748b?text=Screenshot+Persiapan+Pendaftaran" alt="Persiapan Pendaftaran" class="w-full h-auto bg-gray-50">
                        </div>

                        <h3 class="text-[#1d293d] font-bold text-[22px] mt-10 mb-4">2. Registrasi Peserta</h3>
                        <p>Kunjungi laman pendaftaran pada website resmi Patriot Metric. Anda diwajibkan mendaftarkan 1 (satu) akun email yang menggunakan domain resmi institusi (.ac.id).</p>
                        <div class="my-6">
                            <!-- GANTI SRC DI BAWAH INI DENGAN SCREENSHOT YANG SESUAI -->
                            <img src="https://placehold.co/800x400/f8fafc/64748b?text=Screenshot+Registrasi+Akun" alt="Registrasi Akun" class="w-full h-auto bg-gray-50">
                        </div>
                        <p>Setelah formulir dikirim, sistem akan mengirimkan email konfirmasi ke alamat email tersebut. Di dalam email, terdapat tautan verifikasi akun dan tautan khusus untuk bergabung ke dalam Grup WhatsApp koordinasi.</p>

                        <h3 class="text-[#1d293d] font-bold text-[22px] mt-10 mb-4">3. Melengkapi Profil Perguruan Tinggi</h3>
                        <p>Silakan masuk (login) ke dalam dashboard peserta menggunakan akun yang telah diverifikasi. Langkah pertama adalah melengkapi data profil institusi yang mencakup dokumen legalitas, visi misi, serta statistik dasar seperti jumlah mahasiswa dan jumlah pegawai.</p>
                        <div class="my-6">
                            <!-- GANTI SRC DI BAWAH INI DENGAN SCREENSHOT YANG SESUAI -->
                            <img src="https://placehold.co/800x400/f8fafc/64748b?text=Screenshot+Melengkapi+Profil" alt="Melengkapi Profil" class="w-full h-auto bg-gray-50">
                        </div>

                        <h3 class="text-[#1d293d] font-bold text-[22px] mt-10 mb-4">4. Pengisian Indikator & Unggah Bukti (Self Penugasan)</h3>
                        <p>Masuk ke menu Penilaian. Anda akan melihat daftar indikator evaluasi yang terbagi dalam beberapa variabel. Jawab setiap pertanyaan sesuai dengan kondisi nyata di perguruan tinggi Anda.</p>
                        <div class="my-6">
                            <!-- GANTI SRC DI BAWAH INI DENGAN SCREENSHOT YANG SESUAI -->
                            <img src="https://placehold.co/800x400/f8fafc/64748b?text=Screenshot+Pengisian+Penilaian" alt="Pengisian Penilaian" class="w-full h-auto bg-gray-50">
                        </div>
                        <p>Setiap jawaban memerlukan dokumen bukti. Harap lampirkan URL / Tautan ke folder penyimpanan awan (contoh: Google Drive) yang memuat berkas-berkas tersebut. <strong>Penting:</strong> Pastikan tautan dokumen bukti disetel ke Akses Publik (Anyone with the link). Dokumen yang terkunci tidak akan dinilai.</p>

                        <h3 class="text-[#1d293d] font-bold text-[22px] mt-10 mb-4">5. Pengiriman dan Validasi</h3>
                        <p>Setelah semua profil dan penilaian telah diisi dengan lengkap dan benar, tekan tombol Submit / Ajukan Penilaian. Data Anda akan dikunci dan diteruskan ke Tim Evaluator untuk proses verifikasi silang dan validasi dokumen.</p>
                        <div class="my-6">
                            <!-- GANTI SRC DI BAWAH INI DENGAN SCREENSHOT YANG SESUAI -->
                            <img src="https://placehold.co/800x400/f8fafc/64748b?text=Screenshot+Kirim+Penilaian" alt="Kirim Penilaian" class="w-full h-auto bg-gray-50">
                        </div>
                    </div>
                </div>

                {{-- TAB PEDOMAN --}}
                <div x-show="activeTab === 'pedoman'" x-transition.opacity style="display: none;">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                        <h2 class="text-[32px] font-bold text-[#1d293d]">Buku Pedoman Patriot Metric 2026</h2>
                        <a href="{{ asset('dokumen/referensi/PEDOMAN-UPN PATRIOT METRIC 2026.docx.pdf') }}" target="_blank" download class="inline-flex items-center justify-center gap-2 bg-[#1b5e20] hover:bg-[#15461c] text-white font-bold text-[14px] px-6 py-2.5 transition-all shrink-0">
                            Unduh Dokumen PDF
                        </a>
                    </div>

                    <div class="prose prose-slate max-w-none text-[#45556c] text-[16px] leading-relaxed">
                        <p class="mb-6">Buku Pedoman Patriot Metric 2026 merupakan acuan utama bagi seluruh perguruan tinggi peserta dalam mengikuti proses pemeringkatan. Pemeringkatan ini dirancang untuk menilai sejauh mana institusi pendidikan tinggi mampu menginternalisasikan nilai-nilai bela negara.</p>

                        <h3 class="text-[#1d293d] font-bold text-[20px] mt-10 mb-3">Tujuan Patriot Metric</h3>
                        <p>Patriot Metric berfungsi sebagai alat evaluasi kelembagaan yang dapat digunakan oleh perguruan tinggi untuk menilai upaya pembinaan karakter bela negara. Selain itu, program ini bertujuan menciptakan ekosistem pendidikan berbasis nilai kebangsaan dan meningkatkan sinergi antarperguruan tinggi di tingkat nasional.</p>

                        <h3 class="text-[#1d293d] font-bold text-[20px] mt-10 mb-3">Dimensi Penilaian</h3>
                        <p>Instrumen evaluasi dikembangkan melalui tiga dimensi utama:</p>
                        <ol class="list-decimal pl-5 mb-5 space-y-2">
                            <li><strong>Dimensi Bela Negara:</strong> Meliputi cinta tanah air, kesadaran berbangsa dan bernegara, kesetiaan kepada Pancasila, kerelaan berkorban, dan kemampuan dasar bela negara.</li>
                            <li><strong>Dimensi Psikometrik:</strong> Mengukur afeksi kebangsaan, komitmen ideologis, partisipasi aktif, kesediaan berkorban, dan kesiapsiagaan sivitas akademika.</li>
                            <li><strong>Dimensi Tri Dharma Perguruan Tinggi:</strong> Implementasi nilai kebangsaan pada bidang Pendidikan, Penelitian, dan Pengabdian kepada Masyarakat.</li>
                        </ol>

                        <h3 class="text-[#1d293d] font-bold text-[20px] mt-10 mb-3">Variabel dan Pembobotan</h3>
                        <p>Indikator-indikator evaluasi dikelompokkan ke dalam tiga variabel utama dengan total nilai maksimal 100% (Skor Maksimal 500). Rinciannya adalah sebagai berikut:</p>
                        <ul class="list-disc pl-5 mb-5 space-y-3 mt-4">
                            <li><strong>A. Patriotisme Kebijakan (Bobot 20%)</strong><br>Menilai keberadaan dan komitmen perguruan tinggi dalam menetapkan kebijakan yang sejalan dengan nilai bela negara. Terdapat 5 indikator penilaian pada variabel ini.</li>
                            <li><strong>B. Patriotisme Kelembagaan (Bobot 30%)</strong><br>Menilai kinerja institusi dalam menyelenggarakan Tri Dharma Perguruan Tinggi serta menyediakan infrastruktur pendukung karakter bangsa. Terdapat 20 indikator penilaian.</li>
                            <li><strong>C. Patriotisme Mahasiswa (Bobot 50%)</strong><br>Menilai tingkat internalisasi dan kontribusi nyata mahasiswa terhadap kedaulatan negara melalui unit kegiatan kemahasiswaan, kompetisi, kepedulian lingkungan dan program pengabdian. Terdapat 15 indikator penilaian.</li>
                        </ul>

                        <h3 class="text-[#1d293d] font-bold text-[20px] mt-10 mb-3">Klasifikasi Predikat Penilaian</h3>
                        <p>Hasil akhir ditetapkan berdasarkan persentase skor kumulatif yang didapatkan oleh perguruan tinggi. Peringkat ini diklasifikasikan ke dalam 5 tingkatan bintang:</p>
                        <ul class="list-none pl-0 mb-5 space-y-2 mt-4">
                            <li>⭐ <strong class="text-[#1d293d]">Bintang 5:</strong> Skor 85,00 - 100</li>
                            <li>⭐ <strong class="text-[#1d293d]">Bintang 4:</strong> Skor 70,00 - 84,99</li>
                            <li>⭐ <strong class="text-[#1d293d]">Bintang 3:</strong> Skor 60,00 - 69,99</li>
                            <li>⭐ <strong class="text-[#1d293d]">Bintang 2:</strong> Skor 50,00 - 59,99</li>
                            <li>⭐ <strong class="text-[#1d293d]">Bintang 1:</strong> Skor < 50,00</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
