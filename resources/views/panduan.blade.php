@inject('comproService', 'App\Services\ComproContentService')

@php
    $content = $comproService->getPageContent('panduan');

    $hero = $content->get('hero', collect());
    $persyaratan = $content->get('persyaratan', collect());
    $panduanLangkah = $content->get('panduan-langkah', collect());
    $catatan = $content->get('catatan', collect());

    $heroJudul = $hero->firstWhere('key', 'judul')?->value ?? 'Panduan Teknis Sistem';
    $heroDeskripsi = $hero->firstWhere('key', 'deskripsi')?->value ?? '';
    $heroTombolTeks = $hero->firstWhere('key', 'tombol_teks')?->value ?? '';
    $heroTombolLink = $hero->firstWhere('key', 'tombol_link')?->value ?? '';

    $persyaratanDaftar = $persyaratan->firstWhere('key', 'daftar')?->value ?? [];

    $langkahJudul = $panduanLangkah->firstWhere('key', 'judul')?->value ?? 'Langkah Penggunaan Sistem';
    $langkahDaftar = $panduanLangkah->firstWhere('key', 'daftar')?->value ?? [];

    $catatanJudul = $catatan->firstWhere('key', 'judul')?->value ?? 'Catatan Teknis';
    $catatanDaftar = $catatan->firstWhere('key', 'daftar')?->value ?? [];
@endphp

<x-layouts.app>
    <div class="bg-white min-h-screen">
        {{-- Hero --}}
        <section class="relative bg-[#1B5E20] overflow-hidden">
            {{-- Decorative blobs --}}
            <div class="absolute -top-20 -left-20 w-72 h-72 bg-[#d4af37]/10 rounded-full blur-[80px]"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-[#0a1f0d]/30 rounded-full blur-[100px]"></div>

            <div class="relative max-w-[900px] mx-auto px-6 md:px-8 py-16 md:py-24 text-center">
                <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-1.5 mb-6">
                    <i data-lucide="book-open" class="w-4 h-4 text-[#d4af37]"></i>
                    <span class="font-['Plus_Jakarta_Sans',sans-serif] text-[13px] font-semibold text-white/90 tracking-wide uppercase">Panduan Teknis</span>
                </div>
                <h1 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[30px] md:text-[44px] text-white leading-tight">{{ $heroJudul }}</h1>
                @if($heroDeskripsi)
                    <p class="mt-4 font-['Plus_Jakarta_Sans',sans-serif] text-[15px] md:text-[17px] leading-[26px] text-white/80 max-w-[580px] mx-auto">
                        {{ $heroDeskripsi }}
                    </p>
                @endif
                @if($heroTombolTeks && $heroTombolLink)
                    <div class="mt-8">
                        <a href="{{ $heroTombolLink }}" target="_blank" class="inline-flex items-center gap-2.5 bg-[#d4af37] text-[#1d293d] font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[15px] md:text-[16px] px-7 py-3.5 rounded-xl shadow-lg hover:brightness-110 hover:scale-[1.02] transition-all duration-200">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                            {{ $heroTombolTeks }}
                        </a>
                    </div>
                @endif
            </div>
        </section>

        {{-- Persyaratan Sistem --}}
        @if(is_array($persyaratanDaftar) && count($persyaratanDaftar) > 0)
            <section class="py-16 md:py-20 bg-[#f8fafc]">
                <div class="max-w-[1100px] mx-auto px-6 md:px-8">
                    <div class="text-center mb-12">
                        <div class="inline-flex items-center gap-2 bg-[#1b5e20]/10 rounded-full px-4 py-1.5 mb-4">
                            <i data-lucide="settings" class="w-4 h-4 text-[#1b5e20]"></i>
                            <span class="font-['Plus_Jakarta_Sans',sans-serif] text-[13px] font-semibold text-[#1b5e20] tracking-wide uppercase">Persiapan</span>
                        </div>
                        <h2 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[26px] md:text-[32px] text-[#1d293d]">Persyaratan Sistem</h2>
                        <p class="mt-3 font-['Plus_Jakarta_Sans',sans-serif] text-[15px] text-[#64748b] max-w-[500px] mx-auto">
                            Pastikan perangkat dan akun Anda memenuhi persyaratan berikut sebelum menggunakan sistem.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($persyaratanDaftar as $item)
                            <div class="group bg-white rounded-2xl border border-[#e2e8f0] p-7 hover:shadow-lg hover:border-[#1b5e20]/20 transition-all duration-300">
                                <div class="bg-[#1b5e20]/10 group-hover:bg-[#1b5e20] rounded-xl size-12 flex items-center justify-center mb-5 transition-colors duration-300">
                                    <i data-lucide="{{ $item['icon'] ?? 'circle' }}" class="w-6 h-6 text-[#1b5e20] group-hover:text-white transition-colors duration-300"></i>
                                </div>
                                <h3 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[17px] text-[#1d293d]">{{ $item['judul'] ?? '' }}</h3>
                                <p class="mt-2 font-['Plus_Jakarta_Sans',sans-serif] text-[14px] leading-[22px] text-[#45556c]">{{ $item['deskripsi'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Panduan Langkah --}}
        @if(is_array($langkahDaftar) && count($langkahDaftar) > 0)
            <section class="py-16 md:py-24 bg-white">
                <div class="max-w-[860px] mx-auto px-6 md:px-8">
                    <div class="text-center mb-14">
                        <div class="inline-flex items-center gap-2 bg-[#1b5e20]/10 rounded-full px-4 py-1.5 mb-4">
                            <i data-lucide="list-ordered" class="w-4 h-4 text-[#1b5e20]"></i>
                            <span class="font-['Plus_Jakarta_Sans',sans-serif] text-[13px] font-semibold text-[#1b5e20] tracking-wide uppercase">Langkah demi Langkah</span>
                        </div>
                        <h2 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[26px] md:text-[32px] text-[#1d293d]">{{ $langkahJudul }}</h2>
                    </div>

                    {{-- Steps timeline --}}
                    <div class="relative">
                        {{-- Vertical line --}}
                        <div class="absolute left-6 md:left-8 top-0 bottom-0 w-[2px] bg-gradient-to-b from-[#1b5e20] via-[#1b5e20]/40 to-transparent"></div>

                        <div class="flex flex-col gap-8">
                            @foreach($langkahDaftar as $i => $langkah)
                                <div class="relative pl-16 md:pl-20">
                                    {{-- Step number circle --}}
                                    <div class="absolute left-0 top-0 size-12 md:size-16 bg-[#1b5e20] rounded-full flex items-center justify-center shadow-[0_8px_16px_rgba(27,94,32,0.25)] z-10">
                                        <span class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-white text-[16px] md:text-[20px]">{{ $langkah['nomor'] ?? ($i + 1) }}</span>
                                    </div>

                                    {{-- Content card --}}
                                    <div class="bg-[#f8fafc] rounded-2xl border border-[#e2e8f0] p-6 md:p-8 hover:shadow-md transition-shadow duration-300">
                                        <h3 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[18px] md:text-[20px] text-[#1d293d] leading-tight">{{ $langkah['judul'] ?? '' }}</h3>
                                        <p class="mt-3 font-['Plus_Jakarta_Sans',sans-serif] text-[14px] md:text-[15px] leading-[24px] text-[#45556c]">{{ $langkah['deskripsi'] ?? '' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- Catatan Teknis --}}
        @if(is_array($catatanDaftar) && count($catatanDaftar) > 0)
            <section class="py-16 md:py-20 bg-[#f8fafc]">
                <div class="max-w-[860px] mx-auto px-6 md:px-8">
                    <div class="text-center mb-12">
                        <div class="inline-flex items-center gap-2 bg-[#1b5e20]/10 rounded-full px-4 py-1.5 mb-4">
                            <i data-lucide="info" class="w-4 h-4 text-[#1b5e20]"></i>
                            <span class="font-['Plus_Jakarta_Sans',sans-serif] text-[13px] font-semibold text-[#1b5e20] tracking-wide uppercase">Penting</span>
                        </div>
                        <h2 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[26px] md:text-[32px] text-[#1d293d]">{{ $catatanJudul }}</h2>
                    </div>

                    <div class="flex flex-col gap-5">
                        @foreach($catatanDaftar as $note)
                            @php
                                $tipe = $note['tipe'] ?? 'info';
                                $config = match($tipe) {
                                    'warning' => [
                                        'bg' => 'bg-amber-50',
                                        'border' => 'border-amber-200',
                                        'icon_bg' => 'bg-amber-100',
                                        'icon_color' => 'text-amber-600',
                                        'icon' => 'alert-triangle',
                                        'label' => 'Peringatan',
                                        'label_color' => 'text-amber-700',
                                    ],
                                    'tip' => [
                                        'bg' => 'bg-emerald-50',
                                        'border' => 'border-emerald-200',
                                        'icon_bg' => 'bg-emerald-100',
                                        'icon_color' => 'text-emerald-600',
                                        'icon' => 'lightbulb',
                                        'label' => 'Tips',
                                        'label_color' => 'text-emerald-700',
                                    ],
                                    default => [
                                        'bg' => 'bg-blue-50',
                                        'border' => 'border-blue-200',
                                        'icon_bg' => 'bg-blue-100',
                                        'icon_color' => 'text-blue-600',
                                        'icon' => 'info',
                                        'label' => 'Informasi',
                                        'label_color' => 'text-blue-700',
                                    ],
                                };
                            @endphp
                            <div class="{{ $config['bg'] }} {{ $config['border'] }} border rounded-2xl p-6 md:p-7">
                                <div class="flex items-start gap-4">
                                    <div class="{{ $config['icon_bg'] }} rounded-xl size-10 shrink-0 flex items-center justify-center">
                                        <i data-lucide="{{ $config['icon'] }}" class="w-5 h-5 {{ $config['icon_color'] }}"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-['Plus_Jakarta_Sans',sans-serif] text-[11px] font-bold uppercase tracking-wider {{ $config['label_color'] }}">{{ $config['label'] }}</span>
                                        </div>
                                        <h3 class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[16px] md:text-[17px] text-[#1d293d]">{{ $note['judul'] ?? '' }}</h3>
                                        <p class="mt-1.5 font-['Plus_Jakarta_Sans',sans-serif] text-[14px] leading-[22px] text-[#45556c]">{{ $note['deskripsi'] ?? '' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>