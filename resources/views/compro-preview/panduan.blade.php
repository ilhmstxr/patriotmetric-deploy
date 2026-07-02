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

    $panduanSection = $content->get('panduan', collect());
    $panduanDeskripsi = $panduanSection->firstWhere('key', 'deskripsi')?->value ?? '';
    $panduanDaftar = $panduanSection->firstWhere('key', 'daftar')?->value ?? null;

    $pedomanSection = $content->get('pedoman', collect());
    $pedomanFile = $pedomanSection->firstWhere('key', 'file')?->value ?? '';

    $hasPanduan = !empty($panduanDeskripsi) || (!empty($panduanDaftar) && is_array($panduanDaftar));
    $hasPedoman = !empty($pedomanFile);
    $defaultTab = $hasPanduan ? 'panduan' : ($hasPedoman ? 'pedoman' : '');
    @endphp

    <div x-data="{ activeTab: '{{ $defaultTab }}' }" class="bg-white min-h-screen">
        {{-- Hero --}}
        <section class="relative bg-[#0a1f0d] overflow-hidden">
            <div class="absolute inset-0">
                @if($heroBackground)
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
        @if($hasPanduan || $hasPedoman)
        <div class="max-w-[1200px] mx-auto px-4 md:px-8 py-12 flex flex-col md:flex-row gap-8 lg:gap-16 items-stretch">

            {{-- Sidebar Plain --}}
            @if($hasPanduan && $hasPedoman)
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
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                        <h2 class="text-[32px] font-bold text-[#1d293d]">Buku Pedoman Patriot Metric 2026</h2>
                        <a href="{{ $pdfUrl }}" target="_blank" download class="inline-flex items-center justify-center gap-2 bg-[#1b5e20] hover:bg-[#15461c] text-white font-bold text-[14px] px-6 py-2.5 transition-all shrink-0">
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

    <script>
        lucide.createIcons();
    </script>
</body>

</html>