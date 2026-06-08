@props([
    'hideNav' => false, 
    'hideFooter' => false,
    'title' => 'Patriot Metric',
    'metaDescription' => 'Patriot Metric - UPN Veteran JATIM University Ranking untuk mengukur, membina, dan mengapresiasi nilai-nilai bela negara di lingkungan pendidikan tinggi Indonesia.',
    'ogImage' => null
])

@php
    $defaultImage = asset('assets/images/Banner Email Patriot Metric.png');
    $imageToUse = $ogImage ?? $defaultImage;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.webp') }}" />

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="patriot metric, university ranking, bela negara, pendidikan tinggi, UPN Veteran Jatim, pemeringkatan universitas">
    <meta name="author" content="UPN Veteran Jawa Timur">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $imageToUse }}">
    <meta property="og:site_name" content="Patriot Metric">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $imageToUse }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireStyles
    <style>
        /* ── Global Skeleton Loading System ───────────────────────────────────── */
        @keyframes sk-wave {
            0%   { background-position: -600px 0; }
            100% { background-position: 600px 0; }
        }
        .sk {
            --sk-color1: #e8ecf0;
            --sk-color2: #d4d9de;
            background: linear-gradient(90deg, var(--sk-color1) 25%, var(--sk-color2) 50%, var(--sk-color1) 75%);
            background-size: 1200px 100%;
            animation: sk-wave 1.5s infinite linear;
            border-radius: inherit;
        }
        /* Wrapper: must be position:relative */
        .sk-wrap { position: relative; overflow: hidden; }
        /* The placeholder (absolutely covers parent) */
        .sk-ph  { position: absolute; inset: 0; z-index: 1; }
        /* The real image (starts hidden, fades in) */
        .sk-wrap img {
            opacity: 0;
            transition: opacity .35s ease;
            position: relative;
            z-index: 2;
        }
        .sk-wrap img.sk-ok  { opacity: 1; }
        .sk-ph.sk-done      { display: none !important; }
        /* Hero variant: image is absolute, placeholder is a dark gradient */
        .sk-hero { position: absolute; inset: 0; }
        .sk-hero .sk-ph-hero {
            position: absolute; inset: 0;
            background: linear-gradient(135deg,#162d1a 0%,#0f172b 100%);
            z-index: 0;
        }
        .sk-hero img {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover; object-position: center top;
            opacity: 0; transition: opacity .5s ease;
            z-index: 1;
        }
        .sk-hero img.sk-ok { opacity: 1; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col font-['Plus_Jakarta_Sans',sans-serif]"
      x-data="{ showBar: true, lastPos: 0, threshold: 50 }"
      @scroll.window="
          const cur = window.pageYOffset;
          if (cur < 10) { showBar = true; }
          else if (cur < lastPos - threshold) { showBar = true; }
          else if (cur > lastPos + 10) { showBar = false; }
          lastPos = cur;
      "
      x-init="$nextTick(() => { lucide.createIcons() })">
    @if(!$hideNav)
        <header class="fixed top-0 left-0 w-full z-50 transition-transform duration-300"
                :class="showBar ? 'translate-y-0' : '-translate-y-full'">
            <x-navbar />
        </header>
        <div class="h-[65px]"></div>
    @endif
    
    <main class="flex-1">
        {{ $slot }}
    </main>

    @if(!$hideFooter)
        <x-footer />
    @endif

    @livewireScripts
</body>
</html>
