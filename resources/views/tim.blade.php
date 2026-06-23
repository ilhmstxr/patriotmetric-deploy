@inject('comproService', 'App\Services\ComproContentService')

@php
    $content = $comproService->getPageContent('tim');

    // Hero section
    $hero = $content->get('hero', collect());
    $heroJudul = $hero->firstWhere('key', 'judul')?->value ?? 'Tim Kami';
    $heroDeskripsi = $hero->firstWhere('key', 'deskripsi')?->value ?? '';
    $heroBackground = $hero->firstWhere('key', 'background_image')?->value ?? '';

    // Team Grid section
    $teamGrid = $content->get('team-grid', collect());
    $strukturImage = $teamGrid->firstWhere('key', 'struktur_organisasi')?->value ?? '';
@endphp

<x-layouts.app>
    <style>
        /* No specific styles needed for structural image */
    </style>

    <div class="bg-white min-h-screen">
        {{-- Hero --}}
        <section class="relative bg-[#0a1f0d] overflow-hidden">
            <div class="absolute inset-0">
                @if($heroBackground)
                    <img src="{{ url('cms-assets/' . $heroBackground) }}" alt="" class="w-full h-full object-cover object-center" />
                    <div class="absolute inset-0 bg-gradient-to-r from-[#1B5E20]/50 to-[#0a1f0d]/70"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0a1f0d]/70 via-transparent to-transparent"></div>
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

        {{-- Team Grid (Structural) --}}
        @if($strukturImage)
            <section class="py-14 md:py-20 bg-[#f8fafc]">
                <div class="max-w-[1200px] mx-auto px-4">
                    <img src="{{ url('cms-assets/' . $strukturImage) }}" alt="Struktur Organisasi" class="w-full h-auto block rounded-lg shadow-sm" />
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
