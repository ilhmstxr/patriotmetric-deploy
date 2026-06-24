@inject('comproService', 'App\Services\ComproContentService')

@php
$content = $comproService->getPageContent('panduan');

$hero = $content->get('hero', collect());
$heroJudul = $hero->firstWhere('key', 'judul')?->value ?? 'Panduan & Pedoman';
$heroDeskripsi = $hero->firstWhere('key', 'deskripsi')?->value ?? 'Informasi seputar penggunaan sistem dan pedoman penyelenggaraan Patriot Metric.';
$heroBackground = $hero->firstWhere('key', 'background_image')?->value ?? '';
@endphp

<x-layouts.app>
    <div x-data="{ activeTab: 'panduan' }" class="bg-white min-h-screen">
        {{-- Hero --}}
        <section class="relative bg-[#0a1f0d] overflow-hidden">
            <div class="absolute inset-0">
                <!-- @if($heroBackground) -->
                <!-- <img src="{{ url('cms-assets/' . $heroBackground) }}" alt="" class="w-full h-full object-cover object-center" />
                <div class="absolute inset-0 bg-gradient-to-r from-[#1B5E20]/50 to-[#0a1f0d]/70"></div> -->
                <img src="{{ asset('assets/panduan/background.jpeg') }}" alt="Persiapan Pendaftaran" class="w-full h-auto bg-gray-50">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0a1f0d]/70 via-transparent to-transparent"></div>
                <!-- @else
                <div class="absolute inset-0 bg-[#1B5E20]"></div>
                @endif -->
                <!-- @if($heroBackground)
                <img src="{{ url('cms-assets/' . $heroBackground) }}" alt="" class="w-full h-full object-cover object-center" />
                <div class="absolute inset-0 bg-gradient-to-r from-[#1B5E20]/50 to-[#0a1f0d]/70"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#0a1f0d]/70 via-transparent to-transparent"></div>
                @else
                <div class="absolute inset-0 bg-[#1B5E20]"></div>
                @endif -->
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
            <div class="w-full md:w-[240px] shrink-0 md:border-r-0 border-[#e2e8f0] pr-0 md:pr-6 mb-6 md:mb-0">
                <div class="flex flex-row md:flex-col overflow-x-auto gap-6 md:gap-2 border-b md:border-b-0 border-[#e2e8f0]">
                    <button @click="activeTab = 'panduan'"
                        :class="activeTab === 'panduan' ? 'text-[#1b5e20] font-bold border-b-2 md:border-b-0 md:border-l-4 md:bg-[#1b5e20]/5 border-[#1b5e20] md:pl-4 py-2 md:py-3' : 'text-[#64748b] hover:text-[#1d293d] border-b-2 md:border-b-0 md:border-l-4 border-transparent hover:border-gray-300 hover:bg-gray-50 md:pl-4 py-2 md:py-3'"
                        class="flex items-center text-left text-[16px] transition-all whitespace-nowrap w-full rounded-r-md">
                        Panduan
                    </button>
                    <button @click="activeTab = 'pedoman'"
                        :class="activeTab === 'pedoman' ? 'text-[#1b5e20] font-bold border-b-2 md:border-b-0 md:border-l-4 md:bg-[#1b5e20]/5 border-[#1b5e20] md:pl-4 py-2 md:py-3' : 'text-[#64748b] hover:text-[#1d293d] border-b-2 md:border-b-0 md:border-l-4 border-transparent hover:border-gray-300 hover:bg-gray-50 md:pl-4 py-2 md:py-3'"
                        class="flex items-center text-left text-[16px] transition-all whitespace-nowrap w-full rounded-r-md">
                        Pedoman
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
                            <!-- <img src="https://placehold.co/800x400/f8fafc/64748b?text=Screenshot+Persiapan+Pendaftaran" alt="Persiapan Pendaftaran" class="w-full h-auto bg-gray-50"> -->
                            <img src="{{ asset('assets/panduan/pre-registrasi.jpeg') }}" alt="Persiapan Pendaftaran" class="w-full h-auto bg-gray-50">
                        </div>

                        <h3 class="text-[#1d293d] font-bold text-[22px] mt-10 mb-4">2. Registrasi Peserta</h3>
                        <p>Kunjungi laman pendaftaran pada website resmi Patriot Metric. Anda diwajibkan mendaftarkan 1 (satu) akun email yang menggunakan domain resmi institusi (.ac.id).</p>
                        <div class="my-6">
                            <!-- GANTI SRC DI BAWAH INI DENGAN SCREENSHOT YANG SESUAI -->
                            <!-- <img src="https://placehold.co/800x400/f8fafc/64748b?text=Screenshot+Registrasi+Akun" alt="Registrasi Akun" class="w-full h-auto bg-gray-50"> -->
                            <img src="{{ asset('assets/panduan/registrasi.png') }}" alt="Persiapan Pendaftaran" class="w-full h-auto bg-gray-50">

                        </div>
                        <p>Setelah formulir dikirim, sistem akan mengirimkan email konfirmasi ke alamat email tersebut. Di dalam email, terdapat tautan verifikasi akun dan tautan khusus untuk bergabung ke dalam Grup WhatsApp koordinasi.</p>

                        <h3 class="text-[#1d293d] font-bold text-[22px] mt-10 mb-4">3. Melengkapi Profil Perguruan Tinggi</h3>
                        <p>Silakan masuk (login) ke dalam dashboard peserta menggunakan akun yang telah diverifikasi. Langkah pertama adalah melengkapi data profil institusi yang mencakup dokumen legalitas, visi misi, serta statistik dasar seperti jumlah mahasiswa dan jumlah pegawai.</p>
                        <div class="my-6">
                            <!-- GANTI SRC DI BAWAH INI DENGAN SCREENSHOT YANG SESUAI -->
                            <!-- <img src="https://placehold.co/800x400/f8fafc/64748b?text=Screenshot+Melengkapi+Profil" alt="Melengkapi Profil" class="w-full h-auto bg-gray-50"> -->
                            <img src="{{ asset('assets/panduan/verifikasi dokumen - 1.png') }}" alt="Persiapan Pendaftaran" class="w-full h-auto bg-gray-50">

                        </div>

                        <h3 class="text-[#1d293d] font-bold text-[22px] mt-10 mb-4">4. Pengisian Indikator & Unggah Bukti (Self Assessment)</h3>
                        <p>Masuk ke menu Penilaian. Anda akan melihat daftar indikator evaluasi yang terbagi dalam beberapa variabel. Jawab setiap pertanyaan sesuai dengan kondisi nyata di perguruan tinggi Anda.</p>
                        <div class="my-6">
                            <!-- GANTI SRC DI BAWAH INI DENGAN SCREENSHOT YANG SESUAI -->
                            <!-- <img src="https://placehold.co/800x400/f8fafc/64748b?text=Screenshot+Pengisian+Penilaian" alt="Pengisian Penilaian" class="w-full h-auto bg-gray-50"> -->
                            <img src="{{ asset('assets/panduan/pengisian rubrik.png') }}" alt="Persiapan Pendaftaran" class="w-full h-auto bg-gray-50">

                        </div>
                        <p>Setiap jawaban memerlukan dokumen bukti. Harap lampirkan URL / Tautan ke folder penyimpanan awan (contoh: Google Drive) yang memuat berkas-berkas tersebut. <strong>Penting:</strong> Pastikan tautan dokumen bukti disetel ke Akses Publik (Anyone with the link). Dokumen yang terkunci tidak akan dinilai.</p>

                    </div>
                </div>

                {{-- TAB PEDOMAN --}}
                <div x-show="activeTab === 'pedoman'" x-transition.opacity style="display: none;">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        <h2 class="text-[28px] md:text-[32px] font-bold text-[#1d293d]">Buku Pedoman Patriot Metric 2026</h2>
                        <a href="{{ asset('assets/documents/PEDOMAN-UPN PATRIOT METRIC 2026.docx.pdf') }}" target="_blank" download class="inline-flex items-center justify-center gap-2 bg-[#1b5e20] hover:bg-[#15461c] text-white font-bold text-[14px] px-6 py-2.5 rounded-lg transition-all shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Unduh Dokumen PDF
                        </a>
                    </div>

                    <div class="w-full bg-[#f8fafc] border border-[#cbd5e1] overflow-hidden shadow-sm" style="height: 100vh; min-height: 600px;">
                        <iframe src="{{ asset('assets/documents/PEDOMAN-UPN PATRIOT METRIC 2026.docx.pdf') }}" width="100%" height="100%" style="border: none;">
                            <p class="text-center p-8 text-[#64748b]">Browser Anda tidak mendukung preview PDF. Silakan klik tombol "Unduh Dokumen PDF" di atas untuk membacanya.</p>
                        </iframe>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>