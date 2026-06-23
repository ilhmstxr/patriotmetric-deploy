@inject('comproService', 'App\Services\ComproContentService')
@php
    $content = $comproService->getPageContent('berita');
    $hero = $content->get('hero', collect());
    $heroBackground = $hero->firstWhere('key', 'background_image')?->value ?? '';

    $gambarUrl = null;
    if ($berita->gambar) {
        $gambarPath = $berita->gambar;
        if (str_starts_with($gambarPath, 'assets/')) {
            $gambarUrl = asset($gambarPath);
        } else {
            $gambarUrl = asset('assets/' . $gambarPath);
        }
    }
@endphp

<x-layouts.app
    :title="$berita->judul . ' - Patriot Metric'"
    :metaDescription="str($berita->konten)->stripTags()->limit(160)"
    :ogImage="$gambarUrl"
>
    <div class="bg-white min-h-screen">
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
            <div class="relative max-w-[900px] mx-auto px-6 md:px-8 py-16 md:py-24 text-center">
                <a href="{{ url('/berita') }}" class="inline-flex items-center gap-1 font-['Plus_Jakarta_Sans',sans-serif] text-[14px] text-white/70 hover:text-white mb-6 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    Kembali ke Berita
                </a>
                <h1 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[28px] md:text-[42px] text-white leading-tight">{{ $berita->judul }}</h1>
                <p class="mt-4 font-['Plus_Jakarta_Sans',sans-serif] text-[15px] text-white/70">
                    {{ $berita->tanggal->translatedFormat('j F Y') }}
                </p>
            </div>
        </section>

        {{-- Featured Image --}}
        @if($berita->gambar && $gambarUrl)
            <div class="max-w-[900px] mx-auto px-6 md:px-8 -mt-8">
                <div class="w-full rounded-xl overflow-hidden shadow-lg">
                    <img
                        src="{{ $gambarUrl }}"
                        alt="{{ $berita->judul }}"
                        class="w-full md:h-[450px] object-cover"
                        style="height: 300px;"
                    >
                </div>
            </div>
        @endif

        {{-- Content --}}
        <article class="max-w-[750px] mx-auto px-6 md:px-8 py-12">
            <div class="prose prose-lg max-w-none
                font-['Plus_Jakarta_Sans',sans-serif] text-[16px] leading-[30px] text-[#334155]
                prose-headings:font-['Plus_Jakarta_Sans',sans-serif] prose-headings:text-[#1d293d]
                prose-p:mb-6 prose-p:leading-[30px]
                prose-img:rounded-xl prose-img:shadow-md prose-img:my-8 prose-img:w-full prose-img:object-cover prose-img:max-h-[450px]
                prose-a:text-[#1B5E20] prose-a:no-underline hover:prose-a:underline
                prose-strong:text-[#1d293d]">
                {!! $berita->konten !!}
            </div>
        </article>

        {{-- Back link --}}
        <div class="max-w-[750px] mx-auto px-6 md:px-8 pb-16">
            <a href="{{ url('/berita') }}" class="inline-flex items-center gap-2 font-['Plus_Jakarta_Sans',sans-serif] font-medium text-[14px] text-[#1B5E20] hover:underline">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Kembali ke daftar berita
            </a>
        </div>
    </div>
</x-layouts.app>
