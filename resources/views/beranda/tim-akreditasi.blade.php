@extends('layouts.app')

@section('title', 'Beranda Kerja')
@section('breadcrumb', 'Periode ' . ($periode?->nama ?? 'belum diatur'))

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">

        <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @include('beranda._stat', [
                'label' => 'Indikator belum ada data', 'nilai' => $belumAdaData->count(),
                'ket' => 'lingkup prodi, periode aktif', 'nada' => $belumAdaData->count() ? 'perhatian' : 'normal',
            ])
            @include('beranda._stat', [
                'label' => 'Ditolak / dikembalikan', 'nilai' => $perluRevisi->count(),
                'ket' => 'perlu diperbaiki & diajukan ulang', 'nada' => $perluRevisi->count() ? 'perhatian' : 'normal',
            ])
            @include('beranda._stat', ['label' => 'Temuan AMI perlu tindak lanjut', 'nilai' => '–', 'ket' => 'Modul AMI belum tersedia'])
            @include('beranda._stat', ['label' => 'Indikator tanpa bukti', 'nilai' => '–', 'ket' => 'Modul Dokumen Bukti belum tersedia'])
        </section>

        @if ($perluRevisi->isNotEmpty())
            <section class="bg-white border border-brand-300 rounded-2xl overflow-hidden">
                <h2 class="px-4 sm:px-5 py-4 text-[17px] font-semibold border-b border-gray-100">Perlu diperbaiki</h2>
                <ul class="divide-y divide-gray-100">
                    @foreach ($perluRevisi as $c)
                        <li class="px-4 sm:px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="shrink-0 text-sm font-semibold text-brand-700">{{ $c->indikator->standar->kode }}.{{ $c->indikator->nomor }}</span>
                                <span class="flex-1 min-w-0 text-[15px] truncate">{{ $c->indikator->pernyataan }}</span>
                                <span class="shrink-0 text-xs font-medium border border-brand-200 bg-brand-50 text-brand-800 rounded-full px-2.5 py-0.5">{{ \App\Models\CapaianIndikator::STATUS[$c->status] }}</span>
                            </div>
                            @if ($catatan = $c->verifikasi->first()?->catatan)
                                <p class="mt-1 text-sm text-gray-600">Catatan: {{ $catatan }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="bg-white border border-brand-100 rounded-2xl overflow-hidden">
            <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                <h2 class="text-[17px] font-semibold">Indikator belum ada data</h2>
                <span class="text-sm text-gray-500 shrink-0">{{ $belumAdaData->count() }} indikator</span>
            </div>
            @if ($belumAdaData->isEmpty())
                <p class="px-5 py-8 text-center text-gray-500 text-[15px]">Semua indikator lingkup prodi sudah punya data pada periode ini.</p>
            @else
                <ul class="divide-y divide-gray-100 max-h-[480px] overflow-y-auto">
                    @foreach ($belumAdaData as $i)
                        <li class="px-4 sm:px-5 py-3 flex items-start gap-3">
                            <span class="shrink-0 min-w-[46px] text-sm font-semibold text-brand-700">{{ $i->standar->kode }}.{{ $i->nomor }}</span>
                            <span class="flex-1 min-w-0 text-[15px] line-clamp-2">{{ $i->pernyataan }}</span>
                            <span class="hidden sm:inline shrink-0 text-xs text-gray-500 border border-gray-200 rounded-full px-2 py-0.5">Pola {{ $i->pola }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</main>
@endsection
