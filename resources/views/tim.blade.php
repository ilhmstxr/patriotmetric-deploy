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

        {{-- Team Grid (Structural) --}}
        @if(count($daftar) > 0)
            <section class="py-14 md:py-20 bg-[#f8fafc]">
                <div class="max-w-[1100px] mx-auto px-6 md:px-8">
                    <div class="flex flex-col items-center">
                        {{-- Leader / Top Level --}}
                        <div class="w-full max-w-[280px] bg-white rounded-2xl border border-[#f1f5f9] overflow-hidden hover:shadow-lg hover:border-[#1B5E20]/10 transition-all duration-300 group z-10 relative">
                            <div class="p-4 pb-0">
                                <div class="bg-[#f8fafc] rounded-xl overflow-hidden h-[280px]">
                                    @if(!empty($daftar[0]['foto']))
                                        <img src="{{ url('cms-assets/' . $daftar[0]['foto']) }}" alt="{{ $daftar[0]['nama'] ?? '' }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500" />
                                    @else
                                        <img src="{{ asset('assets/tim/blank-profile.webp') }}" alt="{{ $daftar[0]['nama'] ?? '' }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500" />
                                    @endif
                                </div>
                            </div>
                            <div class="p-5 pt-4 text-center">
                                <h3 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[17px] text-[#1d293d]">{{ $daftar[0]['nama'] ?? '' }}</h3>
                                <p class="mt-1.5 font-['Plus_Jakarta_Sans',sans-serif] font-medium text-[13px] text-[#1B5E20]">{{ $daftar[0]['role'] ?? '' }}</p>
                            </div>
                        </div>

                        {{-- Connector --}}
                        @if(count($daftar) > 1)
                        <div class="w-[2px] h-8 bg-[#cbd5e1] relative z-0"></div>
                        
                        {{-- Members --}}
                        <div class="overflow-x-auto w-full pb-4">
                            <div class="min-w-max mx-auto flex flex-col items-center">
                                <div class="relative flex justify-center gap-6 pt-8 px-4">
                                    {{-- Horizontal Line --}}
                                    @if(count($daftar) > 2)
                                        <div class="absolute top-0 left-[156px] right-[156px] h-[2px] bg-[#cbd5e1]"></div>
                                    @elseif(count($daftar) == 2)
                                        {{-- If only 1 subordinate, we only need a vertical line which is handled --}}
                                    @endif
                                    
                                    @foreach(array_slice($daftar, 1) as $index => $member)
                                        <div class="relative flex flex-col items-center w-[280px]">
                                            {{-- Vertical line --}}
                                            <div class="absolute top-0 -mt-8 w-[2px] h-8 bg-[#cbd5e1]"></div>
                                            
                                            <div class="w-full bg-white rounded-2xl border border-[#f1f5f9] overflow-hidden hover:shadow-lg hover:border-[#1B5E20]/10 transition-all duration-300 group z-10 relative">
                                                <div class="p-4 pb-0">
                                                    <div class="bg-[#f8fafc] rounded-xl overflow-hidden h-[280px]">
                                                        @if(!empty($member['foto']))
                                                            <img src="{{ url('cms-assets/' . $member['foto']) }}" alt="{{ $member['nama'] ?? '' }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500" />
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
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
