<footer class="bg-[#f8fafc] border-t border-[#e2e8f0] overflow-hidden relative w-full">
    <div class="max-w-[1536px] mx-auto px-8 pt-16 pb-0 flex flex-col gap-6">
        {{-- Main Content --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Logo --}}
            <div class="flex flex-col items-start">
                <div class="h-[81px] w-[322px] relative">
                    <div class="absolute inset-0 overflow-hidden pointer-events-none">
                        <img alt="Patriot Metric" class="absolute h-[236.75%] left-[-0.11%] max-w-none top-[-58.29%] w-full" src="{{ asset('assets/welcome/logo-patriot-metric.webp') }}" />
                    </div>
                </div>
            </div>

            {{-- Tautan Cepat --}}
            <div class="flex flex-col gap-6">
                <p class="font-['Plus_Jakarta_Sans',sans-serif] font-semibold leading-[28px] text-[#1B5E20] text-[18px]">Tautan Cepat</p>
                <div class="flex flex-col gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-3.5 group">
                        <div class="bg-[#1B5E20] rounded-full size-[6px] shrink-0 transition-transform group-hover:scale-110"></div>
                        <span class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[14px] text-[#45556C] group-hover:text-[#1B5E20] transition-colors">Beranda</span>
                    </a>
                    
                    <div x-data="{ tentangOpen: false }" class="flex flex-col">
                        <button @click="tentangOpen = !tentangOpen" class="flex items-center justify-between w-[200px] group py-1 text-left">
                            <div class="flex items-center gap-3.5">
                                <div class="bg-[#1B5E20] rounded-full size-[6px] shrink-0"></div>
                                <span class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[14px] text-[#45556C]">Tentang Kami</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300 text-[#45556C] opacity-60" :class="tentangOpen ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="tentangOpen" x-transition.opacity.duration.300ms class="flex flex-col gap-2.5 pl-[20px] border-l-2 border-[#f1f5f9] ml-[2px] mt-2 mb-1" style="display: none;">
                            <a href="{{ url('/profile') }}" class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[13px] text-[#62748E] hover:text-[#1B5E20] hover:translate-x-1 transition-all">
                                Profil
                            </a>
                            <a href="{{ url('/tim') }}" class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[13px] text-[#62748E] hover:text-[#1B5E20] hover:translate-x-1 transition-all">
                                Tim
                            </a>
                        </div>
                    </div>

                    <div x-data="{ infoOpen: false }" class="flex flex-col">
                        <button @click="infoOpen = !infoOpen" class="flex items-center justify-between w-[200px] group py-1 text-left">
                            <div class="flex items-center gap-3.5">
                                <div class="bg-[#1B5E20] rounded-full size-[6px] shrink-0"></div>
                                <span class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[14px] text-[#45556C]">Informasi</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300 text-[#45556C] opacity-60" :class="infoOpen ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="infoOpen" x-transition.opacity.duration.300ms class="flex flex-col gap-2.5 pl-[20px] border-l-2 border-[#f1f5f9] ml-[2px] mt-2 mb-1" style="display: none;">
                            <a href="{{ url('/pengumuman') }}" class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[13px] text-[#62748E] hover:text-[#1B5E20] hover:translate-x-1 transition-all">
                                Pengumuman
                            </a>
                            <a href="{{ url('/penghargaan') }}" class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[13px] text-[#62748E] hover:text-[#1B5E20] hover:translate-x-1 transition-all">
                                Penghargaan
                            </a>
                        </div>
                    </div>

                    <a href="{{ url('/panduan') }}" class="flex items-center gap-3.5 group mt-1">
                        <div class="bg-[#1B5E20] rounded-full size-[6px] shrink-0 transition-transform group-hover:scale-110"></div>
                        <span class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[14px] text-[#45556C] group-hover:text-[#1B5E20] transition-colors">Panduan</span>
                    </a>
                </div>
            </div>

            {{-- Hubungi Kami --}}
            <div class="flex flex-col gap-6">
                <p class="font-['Plus_Jakarta_Sans',sans-serif] font-semibold leading-[28px] text-[#1B5E20] text-[18px]">Hubungi Kami</p>
                <div class="flex flex-col gap-4">
                    <div class="flex gap-3 items-start">
                        <i data-lucide="map-pin" class="shrink-0 size-5 mt-0.5 text-[#1B5E20]"></i>
                        <p class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[14px] text-[#45556C]">
                            Jl. Rungkut Madya No.1, Gn. Anyar, Kec. Gn. Anyar, Surabaya, Jawa Timur 60294
                        </p>
                    </div>
                    <div class="flex gap-3 items-center">
                        <i class="shrink-0 size-5 text-[#1B5E20]">
                            <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                                <title>Instagram</title>
                                <path d="M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077"/>
                            </svg>
                        </i>
                        <p class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[14px] text-[#45556C]"><a href="https://www.instagram.com/patriotmetric.upnjatim/" target="_blank">@patriotmetric.upnjatim</p>
                    </div>
                    <div class="flex gap-3 items-center">
                        <i data-lucide="mail" class="shrink-0 size-5 text-[#1B5E20]"></i>
                        <p class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[14px] text-[#45556C]">patriot@upnjatim.ac.id</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="flex flex-col md:flex-row items-center justify-between py-4 border-t border-[#e2e8f0] gap-4 text-center md:text-left mt-8">
            <p class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[14px] text-[#45556C]">
                &copy; {{ date('Y') }} Patriot Metric - Universitas Pembangunan Nasional "Veteran" Jawa Timur. Hak Cipta Dilindungi.
            </p>
            <div class="flex gap-4 items-center">
                <span class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[14px] text-[#45556C]">Syarat &amp; Ketentuan</span>
                <span class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[14px] text-[#45556C]">&bull;</span>
                <span class="font-['Plus_Jakarta_Sans',sans-serif] font-normal leading-[20px] text-[14px] text-[#45556C]">Kebijakan Privasi</span>
            </div>
        </div>
    </div>
</footer>
