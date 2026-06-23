@props(['member'])

<li>
    <div class="inline-flex flex-col items-center">
        {{-- Node Card --}}
        <div class="w-full max-w-[280px] bg-white rounded-2xl border border-[#f1f5f9] overflow-hidden hover:shadow-lg hover:border-[#1B5E20]/10 transition-all duration-300 group z-10 relative text-left">
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

    {{-- Children --}}
    @if(!empty($member['children']) && count($member['children']) > 0)
        <ul>
            @foreach($member['children'] as $child)
                <x-org-chart-node :member="$child" />
            @endforeach
        </ul>
    @endif
</li>
