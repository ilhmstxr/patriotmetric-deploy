@inject('comproService', 'App\Services\ComproContentService')

@php
$content = $comproService->getPageContent('panduan');

$hero = $content->get('hero', collect());
$heroJudul = $hero->firstWhere('key', 'judul')?->value ?? 'Panduan & Pedoman';
$heroDeskripsi = $hero->firstWhere('key', 'deskripsi')?->value ?? 'Informasi seputar penggunaan sistem dan pedoman penyelenggaraan Patriot Metric.';
$heroBackground = $hero->firstWhere('key', 'background_image')?->value ?? '';

$panduanSection = $content->get('panduan', collect());
$panduanDeskripsi = $panduanSection->firstWhere('key', 'deskripsi')?->value ?? '';
$panduanDaftar = $panduanSection->firstWhere('key', 'daftar')?->value ?? null;

$pedomanSection = $content->get('pedoman', collect());
$pedomanFile = $pedomanSection->firstWhere('key', 'file')?->value ?? '';

$hasPanduan = !empty($panduanDeskripsi) || (!empty($panduanDaftar) && is_array($panduanDaftar));
$hasPedoman = !empty($pedomanFile);
$defaultTab = $hasPanduan ? 'panduan' : ($hasPedoman ? 'pedoman' : '');
@endphp

<x-layouts.app>
    <div x-data="{ activeTab: '{{ $defaultTab }}' }" class="bg-white min-h-screen">
        {{-- Hero --}}
        <section class="relative bg-[#0a1f0d] overflow-hidden">
            <div class="absolute inset-0">
                @if($heroBackground)
                    <img src="{{ url('cms-assets/' . $heroBackground) }}" alt="" class="w-full h-full object-cover object-center" />
                @else
                    <img src="{{ asset('assets/panduan/background.jpeg') }}" alt="Persiapan Pendaftaran" class="w-full h-auto bg-gray-50">
                @endif
                <div class="absolute inset-0 bg-gradient-to-r from-[#1B5E20]/50 to-[#0a1f0d]/70"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#0a1f0d]/70 via-transparent to-transparent"></div>
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
        @if($hasPanduan || $hasPedoman)
        <div class="max-w-[1200px] mx-auto px-4 md:px-8 py-12 flex flex-col md:flex-row gap-8 lg:gap-16 items-stretch">

            {{-- Sidebar Plain --}}
            @if($hasPanduan && $hasPedoman)
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
            @endif

            {{-- Content Area Plain --}}
            <div class="flex-1 w-full pb-16">

                {{-- TAB PANDUAN --}}
                @if($hasPanduan)
                <div x-show="activeTab === 'panduan'" x-transition.opacity>
                    <h2 class="text-[32px] font-bold text-[#1d293d] mb-8">Panduan Menjadi Peserta</h2>

                    <div class="prose prose-slate max-w-none text-[#45556c] text-[16px] leading-relaxed">
                        @if(!empty($panduanDeskripsi))
                            <p class="mb-6">{!! nl2br(e($panduanDeskripsi)) !!}</p>
                        @endif

                        @if(!empty($panduanDaftar) && is_array($panduanDaftar))
                            @foreach($panduanDaftar as $index => $step)
                                <h3 class="text-[#1d293d] font-bold text-[22px] mt-10 mb-4">{{ ($index + 1) . '. ' . ($step['judul'] ?? '') }}</h3>
                                <p>{!! nl2br(e($step['deskripsi'] ?? '')) !!}</p>
                                @if(!empty($step['gambar']))
                                    <div class="my-6">
                                        <img src="{{ url('cms-assets/' . $step['gambar']) }}" alt="{{ $step['judul'] ?? '' }}" class="w-full h-auto bg-gray-50 max-w-[800px] rounded-lg shadow-sm border border-gray-100">
                                    </div>
                                @endif
                            @endforeach
                        @endif

                    </div>
                </div>
                @endif

                {{-- TAB PEDOMAN --}}
                @if($hasPedoman)
                <div x-show="activeTab === 'pedoman'" x-transition.opacity style="display: none;">
                    @php
                        $pdfUrl = url('cms-assets/' . $pedomanFile);
                    @endphp
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        <h2 class="text-[28px] md:text-[32px] font-bold text-[#1d293d]">Buku Pedoman Patriot Metric 2026</h2>
                        <a href="{{ $pdfUrl }}" target="_blank" download class="inline-flex items-center justify-center gap-2 bg-[#1b5e20] hover:bg-[#15461c] text-white font-bold text-[14px] px-6 py-2.5 rounded-lg transition-all shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Unduh Dokumen PDF
                        </a>
                    </div>

                    <div class="w-full bg-[#f8fafc] border border-[#cbd5e1] overflow-hidden shadow-sm" style="height: 100vh; min-height: 600px;">
                        <iframe src="{{ $pdfUrl }}" width="100%" height="100%" style="border: none;">
                            <p class="text-center p-8 text-[#64748b]">Browser Anda tidak mendukung preview PDF. Silakan klik tombol "Unduh Dokumen PDF" di atas untuk membacanya.</p>
                        </iframe>
                    </div>
                </div>
                @endif

            </div>
        </div>
        @else
        <div class="max-w-[1200px] mx-auto px-4 md:px-8 py-20 text-center text-[#64748b]">
            <p>Belum ada panduan atau pedoman yang tersedia saat ini.</p>
        </div>
        @endif
    </div>
</x-layouts.app>