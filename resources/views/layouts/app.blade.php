<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'SIAKRED') · Akreditasi Prodi</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            fontFamily: { sans: ['"Instrument Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
            colors: {
                brand: {
                    50: '#FFF3F3', 100: '#FFE1E2', 200: '#FFC4C7', 300: '#FF9A9F',
                    500: '#E0262F', 600: '#C8161F', 700: '#A8101A', 800: '#870D16', 900: '#5E0A10'
                }
            }
        }
    }
}
</script>
<style>
    .nav-group > summary { list-style: none; }
    .nav-group > summary::-webkit-details-marker { display: none; }
    .nav-group[open] .nav-chevron { transform: rotate(90deg); }
    #sidebar nav::-webkit-scrollbar { width: 6px; }
    #sidebar nav::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,.2); border-radius: 9999px; }
    .page-scroll::-webkit-scrollbar { width: 6px; }
    .page-scroll::-webkit-scrollbar-thumb { background-color: #f0c4c6; border-radius: 9999px; }
    @media (prefers-reduced-motion: reduce) { #sidebar { transition: none; } }
</style>
@stack('head')
</head>
<body class="h-[100dvh] bg-[#FBF6F5] text-gray-800 font-sans antialiased flex overflow-hidden">

    @include('layouts.partials.sidebar')

    <div class="flex-1 min-w-0 h-full flex flex-col">

        {{-- HEADER --}}
        <header class="bg-brand-700 text-white shrink-0">
            <div class="@yield('header_width', 'max-w-[1200px]') mx-auto px-4 sm:px-6 py-3.5 sm:py-5 flex items-center gap-3 sm:gap-4">
                <button type="button" id="menu-toggle" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false"
                        class="lg:hidden -ml-2 p-2 rounded-lg hover:bg-white/10 shrink-0">
                    @include('layouts.partials.icon', ['name' => 'menu', 'class' => 'h-6 w-6'])
                </button>
                @hasSection('header')
                    @yield('header')
                @else
                    <div class="min-w-0">
                        @hasSection('breadcrumb')
                            <p class="text-[13px] sm:text-sm text-brand-100 truncate">@yield('breadcrumb')</p>
                        @endif
                        <h1 class="text-[19px] sm:text-[22px] font-bold leading-tight tracking-tight truncate">@yield('title', 'SIAKRED')</h1>
                    </div>
                @endif
            </div>
        </header>

        @yield('content')
    </div>

<script>
(function () {
    // Sidebar geser (layar < 1024px)
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const toggle = document.getElementById('menu-toggle');
    function setOpen(open) {
        sidebar.classList.toggle('-translate-x-full', !open);
        backdrop.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    toggle.addEventListener('click', function () { setOpen(true); });
    document.getElementById('sidebar-close').addEventListener('click', function () { setOpen(false); });
    backdrop.addEventListener('click', function () { setOpen(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !backdrop.classList.contains('hidden')) setOpen(false);
    });
    // Kembali ke layar lebar: bersihkan status menu geser
    window.matchMedia('(min-width: 1024px)').addEventListener('change', function (e) {
        if (e.matches) setOpen(false);
    });
})();
</script>
@stack('scripts')
</body>
</html>
