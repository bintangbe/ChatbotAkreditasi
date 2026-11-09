@extends('layouts.app')

@section('title', 'Status Sistem')
@section('breadcrumb', 'Administrator')

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">

        <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @include('beranda._stat', [
                'label' => 'Pengguna aktif', 'nilai' => $penggunaAktif,
                'ket' => 'dari ' . $penggunaTotal . ' akun', 'href' => route('pengaturan.pengguna'),
            ])
            @include('beranda._stat', [
                'label' => 'Periode berjalan', 'nilai' => $periode?->kode ?? 'Belum diatur',
                'ket' => $periode ? ($periode->is_terkunci ? 'Terkunci' : 'Terbuka') . ' · ' . $periodeTerkunci . ' periode terkunci' : 'Atur di Pengaturan › Periode',
                'nada' => $periode ? 'normal' : 'perhatian', 'href' => route('pengaturan.periode'),
            ])
            @include('beranda._stat', [
                'label' => 'Indikator tanpa resolver', 'nilai' => $tanpaResolver,
                'ket' => 'dari ' . $totalIndikator . ' indikator (pola A tidak butuh resolver)',
                'nada' => $tanpaResolver ? 'perhatian' : 'normal', 'href' => route('pengaturan.mapping'),
            ])
            @include('beranda._stat', [
                'label' => 'Kalkulasi terakhir',
                'nilai' => $hitungTerakhir ? \Illuminate\Support\Carbon::parse($hitungTerakhir)->translatedFormat('d M Y H:i') : 'Belum pernah',
                'ket' => 'Waktu capaian terakhir dihitung',
            ])
        </section>

        <section class="bg-white border {{ $errorLog['jumlah'] ? 'border-brand-300' : 'border-brand-100' }} rounded-2xl px-4 sm:px-5 py-4 flex items-start gap-3">
            <div class="w-10 h-10 shrink-0 rounded-xl grid place-items-center {{ $errorLog['jumlah'] ? 'bg-brand-50 text-brand-600' : 'bg-gray-50 text-gray-500' }}">
                @include('layouts.partials.icon', ['name' => 'alert'])
            </div>
            <div class="min-w-0">
                <h2 class="text-[16px] font-semibold">Error log 24 jam terakhir</h2>
                <p class="text-[15px] text-gray-600">
                    @if ($errorLog['jumlah'])
                        {{ $errorLog['jumlah'] }} error tercatat, terakhir pada {{ $errorLog['terakhir'] }}. Lihat <code class="text-[13px]">storage/logs/laravel.log</code>.
                    @else
                        Tidak ada error tercatat.
                    @endif
                </p>
            </div>
        </section>

        <section>
            <h2 class="text-[17px] font-semibold mb-3">Pintasan</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach ([
                    ['pengaturan.pengguna', 'users', 'Pengguna & Peran', 'Akun dan peran pengguna'],
                    ['pengaturan.periode', 'clock', 'Periode', 'Buka/kunci periode pelaporan'],
                    ['pengaturan.mapping', 'cog', 'Mapping Perhitungan', 'Hubungkan indikator ke resolver'],
                    ['referensi.indikator', 'book', 'Daftar Indikator', 'Standar & butir indikator'],
                ] as [$r, $ikon, $judul, $ket])
                    <a href="{{ route($r) }}" class="bg-white border border-brand-100 hover:border-brand-300 rounded-2xl p-4 flex items-center gap-3 transition-colors">
                        <div class="w-10 h-10 shrink-0 rounded-xl bg-brand-50 text-brand-700 grid place-items-center">
                            @include('layouts.partials.icon', ['name' => $ikon])
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-[15px]">{{ $judul }}</p>
                            <p class="text-sm text-gray-500 truncate">{{ $ket }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</main>
@endsection
