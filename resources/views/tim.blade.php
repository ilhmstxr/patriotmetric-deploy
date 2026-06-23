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
    $daftar = $teamGrid->firstWhere('key', 'daftar')?->value ?? [];
    if (is_string($daftar)) {
        $daftar = json_decode($daftar, true) ?? [];
    }

    // Build tree
    $isLegacy = true;
    foreach ($daftar as $item) {
        if (!empty($item['parent_id'])) {
            $isLegacy = false;
            break;
        }
    }

    $tree = [];
    if ($isLegacy && count($daftar) > 0) {
        $root = $daftar[0];
        $root['children'] = array_slice($daftar, 1);
        $tree[] = $root;
    } else {
        $mapped = [];
        foreach ($daftar as $index => $item) {
            $id = !empty($item['id']) ? $item['id'] : (string)$index;
            $item['id'] = $id;
            $item['children'] = [];
            $mapped[$id] = $item;
        }
        foreach ($mapped as $id => &$item) {
            if (!empty($item['parent_id']) && isset($mapped[$item['parent_id']])) {
                $mapped[$item['parent_id']]['children'][] = &$item;
            } else {
                $tree[] = &$item;
            }
        }
        unset($item);
    }
@endphp

<x-layouts.app>
    <style>
        /* CSS for Org Chart */
        .org-tree {
            display: flex;
            justify-content: center;
            overflow-x: auto;
            padding-bottom: 2rem;
        }
        .org-tree ul {
            padding-top: 32px;
            position: relative;
            display: flex;
            justify-content: center;
            transition: all 0.5s;
        }
        .org-tree li {
            float: left;
            text-align: center;
            list-style-type: none;
            position: relative;
            padding: 32px 12px 0 12px;
            transition: all 0.5s;
        }
        /* Connectors */
        .org-tree li::before, .org-tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 2px solid #cbd5e1;
            width: 50%;
            height: 32px;
        }
        .org-tree li::after {
            right: auto;
            left: 50%;
            border-left: 2px solid #cbd5e1;
        }
        /* Remove lines from first and last */
        .org-tree li:only-child::after, .org-tree li:only-child::before {
            display: none;
        }
        .org-tree li:only-child {
            padding-top: 0;
        }
        .org-tree li:first-child::before, .org-tree li:last-child::after {
            border: 0 none;
        }
        /* Add vertical line back for first and last */
        .org-tree li:last-child::before {
            border-right: 2px solid #cbd5e1;
        }
        /* Time to add downward connectors from parents */
        .org-tree ul ul::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: 2px solid #cbd5e1;
            width: 0;
            height: 32px;
            transform: translateX(-50%);
        }
        
        /* Adjust scale for smaller screens if needed instead of huge scrolling */
        @media (max-width: 1024px) {
            .org-tree {
                transform-origin: top center;
                transform: scale(0.9);
            }
        }
        @media (max-width: 768px) {
            .org-tree {
                transform: scale(0.75);
            }
        }
        @media (max-width: 640px) {
            .org-tree {
                transform: scale(0.6);
            }
        }
    </style>

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
        @if(count($tree) > 0)
            <section class="py-14 md:py-20 bg-[#f8fafc] overflow-hidden">
                <div class="w-full px-4">
                    <div class="org-tree">
                        <ul>
                            @foreach($tree as $rootNode)
                                <x-org-chart-node :member="$rootNode" />
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
