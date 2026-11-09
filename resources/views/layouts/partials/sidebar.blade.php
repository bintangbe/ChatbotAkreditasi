{{-- SIDEBAR NAVIGASI SIAKRED (dev.md §2). Di bawah 1024px jadi menu geser. --}}
<aside id="sidebar" aria-label="Navigasi utama"
       class="fixed inset-y-0 left-0 z-40 w-[280px] max-w-[85vw] -translate-x-full lg:translate-x-0 lg:static shrink-0 transition-transform duration-200 bg-[#A8101A] text-gray-100 flex flex-col">

    {{-- Logo + nama aplikasi --}}
    <div class="px-4 pt-5 pb-4 flex items-center gap-3 border-b border-white/10 shrink-0">
        <div class="bg-white rounded-xl p-1.5 shrink-0 shadow-sm">
            <img src="{{ asset('images/logo-telkom.png') }}" alt="Logo Telkom University" class="h-11 w-auto block">
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-[17px] font-bold leading-tight tracking-tight">SIAKRED</p>
            <p class="text-[13px] text-white/70 leading-snug">Sistem Informasi Akreditasi Prodi</p>
        </div>
        <button type="button" id="sidebar-close" aria-label="Tutup menu" class="lg:hidden -mr-1 p-2 rounded-lg hover:bg-white/10 shrink-0">
            @include('layouts.partials.icon', ['name' => 'close'])
        </button>
    </div>

    @php
        // Sidebar per peran (dev.md §6.1–6.4). Item untuk peran lain TIDAK dirender.
        $user = auth()->user();
        $peranUser = $user?->roleCodes() ?? [];
        $peranUtama = $user?->peranUtama();
        $boleh = fn ($m) => (bool) array_intersect($m['roles'] ?? [], $peranUser);
        $label = fn ($l) => is_array($l) ? ($l[$peranUtama] ?? reset($l)) : $l;

        $menu = collect(config('siakred.menu'))
            ->map(function ($m) use ($boleh, $label, $peranUtama) {
                if (!empty($m['divider'])) {
                    return $m;
                }
                if (isset($m['children'])) {
                    $anak = array_values(array_filter($m['children'], $boleh));
                    if (count($anak) === 1) {
                        // Grup dengan satu anak -> tautan biasa berlabel nama grup
                        return ['label' => $m['label'], 'icon' => $m['icon'], 'route' => $anak[0]['route']];
                    }
                    return $anak ? ['children' => $anak] + $m : null;
                }
                if (!$boleh($m)) {
                    return null;
                }
                $m['label'] = $label($m['label']);
                $m['badge'] = $m['badge'] ?? ($m['badge_roles'][$peranUtama] ?? null);
                return $m;
            })
            ->filter()
            ->values();

        // Angka antrean (hanya dihitung bila ada menu yang memakainya)
        $badgeDipakai = $menu->pluck('badge')->filter()->unique();
        $hitungBadge = [
            'verifikasi'  => fn () => \App\Models\CapaianIndikator::where('status', 'diajukan')->count(),
            'persetujuan' => fn () => \App\Models\CapaianIndikator::where('status', 'terverifikasi')->count(),
        ];
        $badge = $badgeDipakai->mapWithKeys(fn ($b) => [$b => $hitungBadge[$b]()]);
    @endphp

    <nav class="flex-1 overflow-y-auto overscroll-contain px-3 py-3 space-y-0.5 text-[15px]">
        @foreach ($menu as $item)
            @if (!empty($item['divider']))
                <div class="my-2 border-t border-white/15" role="separator"></div>

            @elseif (isset($item['children']))
                @php $groupActive = collect($item['children'])->contains(fn ($c) => request()->routeIs($c['route'])); @endphp
                <details class="nav-group" @if ($groupActive) open @endif>
                    <summary class="flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer select-none hover:bg-white/10 font-medium {{ $groupActive ? 'text-white' : '' }}">
                        @include('layouts.partials.icon', ['name' => $item['icon']])
                        <span class="flex-1 min-w-0 truncate">{{ $item['label'] }}</span>
                        @include('layouts.partials.icon', ['name' => 'chevron', 'class' => 'nav-chevron h-4 w-4 opacity-70 transition-transform'])
                    </summary>
                    <div class="mt-0.5 mb-1 ml-[22px] pl-3 border-l border-white/15 space-y-0.5">
                        @foreach ($item['children'] as $child)
                            @php $active = request()->routeIs($child['route']); @endphp
                            <a href="{{ route($child['route']) }}"
                               class="block px-3 py-2 rounded-lg text-[14.5px] truncate transition-colors {{ $active ? 'bg-white/15 text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                               @if ($active) aria-current="page" @endif>{{ $child['label'] }}</a>
                        @endforeach
                    </div>
                </details>

            @else
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors {{ $active ? 'bg-white/15 text-white' : 'hover:bg-white/10' }} {{ !empty($item['special']) && !$active ? 'bg-white/[.06]' : '' }}"
                   @if ($active) aria-current="page" @endif>
                    @include('layouts.partials.icon', ['name' => $item['icon']])
                    <span class="flex-1 min-w-0 truncate">{{ $item['label'] }}</span>
                    @if (!empty($item['badge']) && ($badge[$item['badge']] ?? 0) > 0)
                        <span class="shrink-0 min-w-[22px] h-[22px] px-1.5 rounded-full bg-white text-brand-700 text-xs font-bold grid place-items-center"
                              aria-label="{{ $badge[$item['badge']] }} menunggu">{{ $badge[$item['badge']] }}</span>
                    @endif
                </a>
                {{-- Panel tambahan (mis. riwayat chat) muncul tepat di bawah menu yang aktif --}}
                @if ($active && !empty($item['special']))
                    @yield('sidebar_extra')
                @endif
            @endif
        @endforeach
    </nav>

    {{-- Pengguna yang login --}}
    @auth
        <div class="shrink-0 border-t border-white/10 px-3 py-3 flex items-center gap-3">
            <div class="w-9 h-9 shrink-0 rounded-full bg-white/15 grid place-items-center text-sm font-semibold uppercase">
                {{ mb_substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[14.5px] font-medium truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-white/60 truncate">{{ auth()->user()->roles->pluck('nama')->implode(', ') ?: 'Tanpa peran' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit" title="Keluar" aria-label="Keluar" class="p-2 rounded-lg hover:bg-white/10">
                    @include('layouts.partials.icon', ['name' => 'logout'])
                </button>
            </form>
        </div>
    @endauth
</aside>
<div id="sidebar-backdrop" class="hidden fixed inset-0 z-30 bg-black/40 lg:hidden"></div>
