@inject('comproService', 'App\Services\ComproContentService')

@php
    $content = $comproService->getPageContent('tim');

    // Hero section
    $hero = $content->get('hero', collect());
    $heroJudul = $hero->firstWhere('key', 'judul')?->value ?? 'Tim Kami';
    $heroDeskripsi = $hero->firstWhere('key', 'deskripsi')?->value ?? '';

    // Team Grid section
    $teamGrid = $content->get('team-grid', collect());
    $daftar = $teamGrid->firstWhere('key', 'daftar')?->value ?? [];
    if (is_string($daftar)) {
        $daftar = json_decode($daftar, true) ?? [];
    }
@endphp

<x-layouts.app>
    <div class="bg-white min-h-screen">
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

        {{-- Team Grid --}}
        @if(count($daftar) > 0)
            <section class="py-14 md:py-20 bg-[#f8fafc]">
                <div class="max-w-[1100px] mx-auto px-6 md:px-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($daftar as $member)
                            <div class="bg-white rounded-2xl border border-[#f1f5f9] overflow-hidden hover:shadow-lg hover:border-[#1B5E20]/10 transition-all duration-300 group">
                                <div class="p-4 pb-0">
                                    <div class="bg-[#f8fafc] rounded-xl overflow-hidden h-[280px] sk-wrap">
                                        @if(!empty($member['foto']))
                                            <div class="sk-ph sk" style="height:280px"></div>
                                            <img src="{{ '/cms-assets/' . $member['foto'] }}" alt="{{ $member['nama'] ?? '' }}"
                                                class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500"
                                                onload="this.classList.add('sk-ok'); this.previousElementSibling.classList.add('sk-done')"
                                                onerror="this.previousElementSibling.classList.add('sk-done')" />
                                        @else
                                            <img src="{{ asset('assets/tim/blank-profile.webp') }}" alt="{{ $member['nama'] ?? '' }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500" />
                                        @endif
                                    </div>
                                </div>
                                <div class="p-5 pt-4 text-center">
                                    <h3 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[17px] text-[#1d293d]">{{ $member['nama'] ?? '' }}</h3>
                                    <p class="mt-1.5 font-['Plus_Jakarta_Sans',sans-serif] font-medium text-[13px] text-[#1B5E20]">{{ $member['role'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
