<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $colorClass = match($badgeColor) {
                'success' => 'text-emerald-600 dark:text-emerald-400',
                'warning' => 'text-amber-600 dark:text-amber-400',
                'info'    => 'text-blue-600 dark:text-blue-400',
                'danger'  => 'text-red-600 dark:text-red-400',
                default   => 'text-gray-600 dark:text-gray-400',
            };
            $dotClass = match($badgeColor) {
                'success' => 'bg-emerald-500',
                'warning' => 'bg-amber-500',
                'info'    => 'bg-blue-500',
                'danger'  => 'bg-red-500',
                default   => 'bg-gray-400',
            };
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Status card --}}
            <div class="rounded-xl bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 shadow-sm px-5 py-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-3">
                    Status Fase Penugasan
                </p>
                <div class="flex items-center gap-2 mb-2">
                    <span class="relative flex h-2.5 w-2.5">
                        @if($badgeColor === 'success')
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $dotClass }} opacity-50"></span>
                        @endif
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $dotClass }}"></span>
                    </span>
                    <span class="text-xl font-bold tracking-tight {{ $colorClass }}">
                        {{ $status }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                    {{ $description }}
                </p>
            </div>

            {{-- Timeline detail card --}}
            @if($hasTimeline && isset($timeline))
                <div class="rounded-xl bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 shadow-sm px-5 py-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-3">
                        Periode {{ $timeline->tahun_periode }}
                    </p>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between border-b border-gray-100 dark:border-white/5 pb-2">
                            <span class="text-gray-500 dark:text-gray-400">Pendaftaran & Pengerjaan</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300 tabular-nums text-right">
                                {{ $timeline->opens_at?->translatedFormat('d M Y, H:i') }} –
                                {{ $timeline->closes_at?->translatedFormat('d M Y, H:i') }}
                            </span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 dark:border-white/5 pb-2">
                            <span class="text-gray-500 dark:text-gray-400">Penilaian Reviewer</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300 tabular-nums">
                                Setelah {{ $timeline->closes_at?->translatedFormat('d M Y, H:i') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Pengumuman Hasil</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300 tabular-nums">
                                {{ $timeline->results_published_at?->translatedFormat('d M Y, H:i') ?? '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
