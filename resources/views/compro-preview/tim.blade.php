<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - Tim</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.webp') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
    </style>
</head>
<body class="bg-white min-h-screen">

    {{-- Hero Section --}}
    @php
        $hero = $content->get('hero', collect());
        $heroJudul = $hero->firstWhere('key', 'judul')?->value ?? 'Tim Kami';
        $heroDeskripsi = $hero->firstWhere('key', 'deskripsi')?->value ?? '';
        $heroBackground = $hero->firstWhere('key', 'background_image')?->value ?? '';
    @endphp
    <section class="relative bg-[#0a1f0d] overflow-hidden">
        <div class="absolute inset-0">
            @if($heroBackground)
                <img src="{{ '/cms-assets/' . $heroBackground }}" alt="" class="w-full h-full object-cover object-center" />
                <div class="absolute inset-0 bg-gradient-to-r from-[#1B5E20]/60 to-[#0a1f0d]/95"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#0a1f0d]/90 via-transparent to-transparent"></div>
            </div>
            @else
                <div class="absolute inset-0 bg-[#1B5E20]"></div>
            @endif
        </div>
        <div class="absolute top-16 right-16 w-80 h-80 bg-[#d4af37]/15 rounded-full blur-[100px]"></div>
        <div class="relative max-w-[900px] mx-auto px-6 md:px-8 py-20 md:py-28 text-center">
            <h1 class="font-bold text-[34px] md:text-[50px] text-white leading-tight">{{ $heroJudul }}</h1>
            @if($heroDeskripsi)
                <p class="mt-4 text-[16px] md:text-[19px] leading-[28px] text-white/85 max-w-[600px] mx-auto">
                    {{ $heroDeskripsi }}
                </p>
            @endif
        </div>
    </section>

    {{-- Team Grid Section --}}
    @php
        $teamGrid = $content->get('team-grid', collect());
        $strukturImage = $teamGrid->firstWhere('key', 'struktur_organisasi')?->value ?? '';
    @endphp
    @if($strukturImage)
        <section class="py-14 md:py-20 bg-[#f8fafc]">
            <div class="max-w-[1100px] mx-auto px-6 md:px-8">
                <img src="{{ '/cms-assets/' . $strukturImage }}" alt="Struktur Organisasi" class="w-full h-auto block rounded-lg shadow-sm" />
            </div>
        </section>
    @endif

</body>
</html>
