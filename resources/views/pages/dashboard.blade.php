@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Selamat datang, ' . auth()->user()->name)

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6 sm:space-y-8">

        {{-- Ringkasan korpus --}}
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach ([
                ['Dokumen standar', count($standar)],
                ['Indikator', $totalIndikator],
                ['Klaster Pendidikan', $standar->where('klaster', 'pendidikan')->count() . ' standar'],
                ['Periode aktif', $periodeAktif?->kode ?? 'Belum diatur'],
            ] as [$label, $nilai])
                <div class="bg-white border border-brand-100 rounded-2xl px-4 py-4 sm:px-5 sm:py-5">
                    <p class="text-[13px] sm:text-sm text-gray-500">{{ $label }}</p>
                    <p class="mt-1 text-[22px] sm:text-[30px] font-bold text-brand-700 leading-tight break-words">{{ $nilai }}</p>
                </div>
            @endforeach
        </section>

        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Daftar standar --}}
            <section class="lg:col-span-2 bg-white border border-brand-100 rounded-2xl overflow-hidden">
                <div class="px-4 sm:px-5 py-4 flex items-center justify-between gap-3 border-b border-gray-100">
                    <h2 class="text-[17px] font-semibold">Standar yang dikelola</h2>
                    <a href="{{ route('referensi.standar') }}" class="text-sm font-medium text-brand-700 hover:underline shrink-0">Lihat semua</a>
                </div>
                <ul class="divide-y divide-gray-100">
                    @forelse ($standar as $s)
                        <li>
                            <a href="{{ route('referensi.indikator', ['standar' => $s->id]) }}" class="px-4 sm:px-5 py-3 flex items-center gap-3 sm:gap-4 hover:bg-brand-50/40">
                                <span class="w-12 shrink-0 text-center text-sm font-semibold text-brand-700 bg-brand-50 rounded-lg py-1">{{ $s->kode }}</span>
                                <span class="flex-1 min-w-0 text-[15px] truncate">{{ $s->nama }}</span>
                                <span class="shrink-0 text-sm text-gray-500">{{ $s->indikator_count }}<span class="hidden sm:inline"> indikator</span></span>
                            </a>
                        </li>
                    @empty
                        <li class="px-5 py-8 text-center text-gray-500">Belum ada data standar.</li>
                    @endforelse
                </ul>
            </section>

            {{-- Akses cepat --}}
            <section class="space-y-4">
                <a href="{{ route('asisten-ai') }}" class="block bg-brand-700 hover:bg-brand-800 transition-colors text-white rounded-2xl p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-white text-brand-700 grid place-items-center shrink-0">
                            @include('layouts.partials.icon', ['name' => 'chat', 'class' => 'h-6 w-6'])
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-[17px]">Tanya Asisten AI</p>
                            <p class="text-sm text-brand-100">Tanya isi dokumen standar akreditasi</p>
                        </div>
                    </div>
                </a>
                <div class="bg-white border border-brand-100 rounded-2xl p-5">
                    <h2 class="text-[17px] font-semibold">Capaian indikator</h2>
                    <p class="mt-1.5 text-[15px] text-gray-500">Capaian akan dihitung otomatis dari master data setelah modul Master Data dan perhitungan capaian selesai dibangun.</p>
                    <a href="{{ route('capaian.index') }}" class="mt-3 inline-block text-sm font-medium text-brand-700 hover:underline">Buka Capaian Indikator</a>
                </div>
            </section>
        </div>
    </div>
</main>
@endsection
