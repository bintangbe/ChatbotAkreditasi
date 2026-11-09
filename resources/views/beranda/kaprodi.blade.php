@extends('layouts.app')

@section('title', 'Dashboard Capaian')
@section('breadcrumb', 'Periode ' . ($periode?->nama ?? 'belum diatur'))

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">

        <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @include('beranda._stat', [
                'label' => 'Menunggu persetujuan', 'nilai' => $antreanSetuju, 'ket' => 'capaian terverifikasi GKM',
                'nada' => $antreanSetuju ? 'perhatian' : 'normal', 'href' => route('persetujuan.index'),
            ])
            @include('beranda._stat', [
                'label' => 'Indikator di bawah target', 'nilai' => count($dibawahTarget),
                'nada' => count($dibawahTarget) ? 'perhatian' : 'normal',
            ])
            @include('beranda._stat', [
                'label' => 'Indikator tercapai', 'nilai' => collect($kartu)->sum('tercapai'),
                'ket' => 'dari ' . collect($kartu)->sum('total') . ' indikator lingkup prodi',
            ])
            @include('beranda._stat', [
                'label' => 'Temuan AMI terbuka', 'nilai' => '–', 'ket' => 'Modul AMI belum tersedia',
            ])
        </section>

        {{-- Kartu per standar --}}
        <section>
            <h2 class="text-[17px] font-semibold mb-3">Pemenuhan per standar</h2>
            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach ($kartu as $k)
                    <div class="bg-white border border-brand-100 rounded-2xl p-4">
                        <div class="flex items-start gap-3">
                            <span class="shrink-0 w-12 text-center text-sm font-semibold text-brand-700 bg-brand-50 rounded-lg py-1">{{ $k['standar']->kode }}</span>
                            <p class="flex-1 min-w-0 text-[15px] font-medium leading-snug">{{ $k['standar']->nama }}</p>
                            <span class="shrink-0 text-[17px] font-bold {{ $k['disetujui'] ? 'text-brand-700' : 'text-gray-400' }}">{{ $k['disetujui'] ? $k['persen'] . '%' : '–' }}</span>
                        </div>
                        <div class="mt-3 h-2 rounded-full bg-gray-100 overflow-hidden" role="progressbar" aria-valuenow="{{ $k['persen'] }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="h-full bg-brand-600 rounded-full" style="width: {{ $k['persen'] }}%"></div>
                        </div>
                        <p class="mt-2 text-[13px] text-gray-500">
                            {{ $k['tercapai'] }} tercapai · {{ $k['disetujui'] }} disetujui · {{ $k['total'] - $k['ada_data'] }} belum ada data
                        </p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Di bawah target --}}
        <section class="bg-white border border-brand-100 rounded-2xl overflow-hidden">
            <h2 class="px-4 sm:px-5 py-4 text-[17px] font-semibold border-b border-gray-100">Indikator di bawah target</h2>
            @if ($dibawahTarget)
                <ul class="divide-y divide-gray-100">
                    @foreach ($dibawahTarget as $d)
                        <li class="px-4 sm:px-5 py-3 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
                            <span class="shrink-0 text-sm font-semibold text-brand-700">{{ $d['indikator']->standar->kode }}.{{ $d['indikator']->nomor }}</span>
                            <span class="flex-1 min-w-0 text-[15px] line-clamp-2">{{ $d['indikator']->pernyataan }}</span>
                            <span class="shrink-0 text-sm text-gray-600">{{ rtrim(rtrim($d['capaian']->nilai, '0'), '.') }} / target {{ rtrim(rtrim($d['target']->nilai_target, '0'), '.') }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="px-5 py-8 text-center text-gray-500 text-[15px]">
                    {{ collect($kartu)->sum('disetujui') ? 'Semua capaian yang disetujui memenuhi target.' : 'Belum ada capaian yang disetujui pada periode ini.' }}
                </p>
            @endif
        </section>
    </div>
</main>
@endsection
