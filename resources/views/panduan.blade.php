@inject('comproService', 'App\Services\ComproContentService')

@php
    $content = $comproService->getPageContent('panduan');

    $hero = $content->get('hero', collect());
    $steps = $content->get('steps', collect());
    $faq = $content->get('faq', collect());

    $heroJudul = $hero->firstWhere('key', 'judul')?->value ?? 'Panduan Penggunaan Sistem';
    $heroDeskripsi = $hero->firstWhere('key', 'deskripsi')?->value ?? '';
    $heroTombolTeks = $hero->firstWhere('key', 'tombol_teks')?->value ?? '';
    $heroTombolLink = $hero->firstWhere('key', 'tombol_link')?->value ?? '';

    $stepsDaftar = $steps->firstWhere('key', 'daftar')?->value ?? [];

    $faqJudul = $faq->firstWhere('key', 'judul')?->value ?? 'Tanya Jawab (FAQ)';
    $faqDaftar = $faq->firstWhere('key', 'daftar')?->value ?? [];
@endphp

<x-layouts.app>
    <div class="bg-white min-h-screen">
        {{-- Hero --}}
        <section class="bg-[#1B5E20]">
            <div class="max-w-[900px] mx-auto px-6 md:px-8 py-16 md:py-22 text-center">
                <h1 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[30px] md:text-[44px] text-white leading-tight">{{ $heroJudul }}</h1>
                @if($heroDeskripsi)
                    <p class="mt-3 font-['Plus_Jakarta_Sans',sans-serif] text-[15px] md:text-[17px] leading-[26px] text-white/80 max-w-[540px] mx-auto">
                        {{ $heroDeskripsi }}
                    </p>
                @endif
                @if($heroTombolTeks && $heroTombolLink)
                    <div class="mt-8">
                        <a href="{{ $heroTombolLink }}" target="_blank" class="inline-flex flex-col sm:flex-row text-center items-center gap-2 bg-[#d4af37] text-[#1d293d] font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[16px] md:text-[18px] px-6 sm:px-8 py-4 rounded-2xl shadow-lg hover:brightness-110 transition leading-tight">
                            {{ $heroTombolTeks }} &rarr;
                        </a>
                    </div>
                @endif
            </div>
        </section>

        {{-- Steps --}}
        @if(is_array($stepsDaftar) && count($stepsDaftar) > 0)
            <section class="py-16 bg-[#f8fafc]">
                <div class="max-w-[1100px] mx-auto px-6 md:px-8">
                    <div class="relative">
                        {{-- Line --}}
                        <div class="hidden md:block absolute top-[168px] left-10 right-10 h-1 bg-[#1b5e20]"></div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                            @foreach($stepsDaftar as $step)
                                <div class="bg-white rounded-3xl border border-[#f1f5f9] shadow-lg p-8 relative z-10">
                                    <div class="bg-[#1b5e20] rounded-2xl size-16 flex items-center justify-center mb-6 shadow-[0px_10px_15px_0px_rgba(27,94,32,0.2)]">
                                        <i data-lucide="{{ $step['icon'] ?? 'circle' }}" class="w-8 h-8 text-white"></i>
                                    </div>
                                    <p class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[14px] text-[#1b5e20]">{{ $step['label'] ?? '' }}</p>
                                    <h3 class="mt-2 font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[20px] leading-[28px] text-[#1d293d]">{{ $step['judul'] ?? '' }}</h3>
                                    <p class="mt-2 font-['Plus_Jakarta_Sans',sans-serif] font-normal text-[14px] leading-[22.75px] text-[#45556c]">{{ $step['deskripsi'] ?? '' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
