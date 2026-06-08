<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Timeline Submission {{ $tahunPeriode ? '— Periode ' . $tahunPeriode : '' }}
        </x-slot>

        @if(! $hasTimeline)
            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada timeline submission yang dikonfigurasi.</p>
        @else
            {{-- Phase badge --}}
            <div class="flex items-center gap-2 mb-4">
                <span @class([
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'     => $phase === 'locked',
                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' => $phase === 'upcoming',
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $phase === 'open',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' => $phase === 'closed',
                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' => $phase === 'published',
                    'bg-gray-100 text-gray-500' => $phase === 'none',
                ])>
                    {{ $phaseLabel }}
                </span>
                @if($isLocked)
                    <span class="text-xs text-red-500">Locked</span>
                @endif
            </div>

            @if($note)
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 italic">{{ $note }}</p>
            @endif

            {{-- Progress bar --}}
            @if(in_array($phase, ['open', 'published']))
                <div class="mb-4">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>{{ $opensAt?->format('d M Y') }}</span>
                        <span>{{ $closesAt?->format('d M Y') }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div
                            class="bg-amber-500 h-2.5 rounded-full transition-all duration-500"
                            style="width: {{ $progressPercent }}%"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 text-right">{{ $progressPercent }}% berlalu</p>
                </div>
            @endif

            {{-- Countdown --}}
            @if($daysLeft !== null && $daysLeft >= 0)
                <div class="flex gap-4 mt-2">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-amber-500">{{ $daysLeft }}</div>
                        <div class="text-xs text-gray-500">hari</div>
                    </div>
                    @if($phase === 'open')
                        <div class="text-center">
                            <div class="text-2xl font-bold text-amber-500">{{ $hoursLeft % 24 }}</div>
                            <div class="text-xs text-gray-500">jam</div>
                        </div>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    @if($phase === 'upcoming') Sampai submission dibuka
                    @elseif($phase === 'open') Sisa waktu submission
                    @elseif($phase === 'closed') Sampai hasil dipublikasikan
                    @endif
                </p>
            @endif

            {{-- Tanggal-tanggal penting --}}
            <div class="mt-4 space-y-1 border-t pt-3 border-gray-200 dark:border-gray-700">
                @if($opensAt)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Buka</span>
                        <span class="font-medium">{{ $opensAt->translatedFormat('d M Y H:i') }}</span>
                    </div>
                @endif
                @if($closesAt)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Tutup</span>
                        <span class="font-medium">{{ $closesAt->translatedFormat('d M Y H:i') }}</span>
                    </div>
                @endif
                @if($resultsAt)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Pengumuman</span>
                        <span class="font-medium">{{ $resultsAt->translatedFormat('d M Y H:i') }}</span>
                    </div>
                @endif
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
