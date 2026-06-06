@inject('comproService', 'App\Services\ComproContentService')

@php
    $content = $comproService->getPageContent('pengumuman');

    $hero = $content->get('hero', collect());
    $artikel = $content->get('artikel', collect());

    $heroJudul = $hero->firstWhere('key', 'judul')?->value ?? 'Pengumuman';
    $heroDeskripsi = $hero->firstWhere('key', 'deskripsi')?->value ?? 'Informasi terbaru seputar Patriot Metric.';

    $artikelDaftar = $artikel->firstWhere('key', 'daftar')?->value ?? [];
    
    $groupedArtikel = collect($artikelDaftar)->groupBy(function($item) {
        return !empty($item['tanggal']) ? \Carbon\Carbon::parse($item['tanggal'])->format('Y') : 'Lainnya';
    })->sortKeysDesc();
@endphp

<x-layouts.app>
    <div class="bg-[#f8fafc] min-h-screen">
        {{-- Hero --}}
        <section class="bg-[#1B5E20]">
            <div class="max-w-[1200px] mx-auto px-6 md:px-8 py-16 md:py-22 text-center">
                <h1 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[30px] md:text-[44px] text-white leading-tight">{{ $heroJudul }}</h1>
                @if($heroDeskripsi)
                    <p class="mt-3 font-['Plus_Jakarta_Sans',sans-serif] text-[15px] md:text-[17px] leading-[26px] text-white/80 max-w-[540px] mx-auto">
                        {{ $heroDeskripsi }}
                    </p>
                @endif
            </div>
        </section>

        {{-- Document List --}}
        <div class="max-w-[1000px] mx-auto px-6 md:px-8 py-16 md:py-20">
            @if($groupedArtikel->isNotEmpty())
                <div class="space-y-14">
                    @foreach($groupedArtikel as $year => $items)
                        <div>
                            {{-- Section Header --}}
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-1 h-8 bg-[#1B5E20] rounded-full"></div>
                                <h2 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[24px] md:text-[28px] text-[#1d293d]">
                                    {{ $year !== 'Lainnya' ? 'Tahun ' . $year : $year }}
                                </h2>
                            </div>
                            
                            {{-- Items --}}
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($items as $item)
                                    <div class="bg-white rounded-2xl border border-[#e2e8f0] p-5 md:p-6 flex flex-col md:flex-row items-start md:items-center gap-4 md:gap-6">
                                        {{-- Icon --}}
                                        <div class="shrink-0 w-12 h-12 rounded-xl bg-[#f8fafc] border border-[#f1f5f9] flex items-center justify-center">
                                            <svg class="w-6 h-6 text-[#1B5E20]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                        </div>

                                        {{-- Content --}}
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[16px] md:text-[18px] text-[#1d293d] leading-snug">
                                                {{ $item['judul'] ?? '' }}
                                            </h3>
                                            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                                                @if(!empty($item['tanggal']))
                                                    <span class="font-['Plus_Jakarta_Sans',sans-serif] text-[14px] text-[#45556c] flex items-center gap-1.5">
                                                        <i data-lucide="calendar" class="w-4 h-4 text-[#64748b]"></i>
                                                        {{ \Carbon\Carbon::parse($item['tanggal'])->translatedFormat('j F Y') }}
                                                    </span>
                                                @endif
                                                @if(!empty($item['excerpt']))
                                                    <span class="hidden md:inline text-[#cbd5e1]">•</span>
                                                    <span class="font-['Plus_Jakarta_Sans',sans-serif] text-[14px] text-[#45556c]">
                                                        {{ $item['excerpt'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Actions --}}
                                        @if(!empty($item['dokumen']))
                                            <div class="shrink-0 flex items-center gap-3 w-full md:w-auto mt-2 md:mt-0">
                                                <a href="{{ url('assets/' . $item['dokumen']) }}" target="_blank" rel="noopener"
                                                   class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-[14px] font-bold font-['Plus_Jakarta_Sans',sans-serif] text-[#1d293d] bg-white border border-[#e2e8f0] hover:bg-[#f8fafc] hover:border-[#cbd5e1] transition-all">
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                    <span class="hidden sm:inline">Lihat</span>
                                                </a>
                                                <a href="{{ url('assets/' . $item['dokumen']) }}" download
                                                   class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-[14px] font-bold font-['Plus_Jakarta_Sans',sans-serif] text-white bg-[#1B5E20] hover:bg-[#145214] shadow-sm hover:shadow transition-all">
                                                    <i data-lucide="download" class="w-4 h-4"></i>
                                                    <span class="hidden sm:inline">Unduh</span>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20 bg-white rounded-3xl border border-[#f1f5f9] shadow-sm">
                    <div class="w-16 h-16 mx-auto rounded-full bg-[#f8fafc] flex items-center justify-center mb-4 border border-[#e2e8f0]">
                        <svg class="w-8 h-8 text-[#94a3b8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9.75m3 0H9.75m0 0v3m0-3v-3m-3.375-6H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <p class="font-['Plus_Jakarta_Sans',sans-serif] text-[16px] text-[#45556c]">Belum ada dokumen pengumuman.</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
