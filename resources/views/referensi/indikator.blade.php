@extends('layouts.app')

@section('title', 'Daftar Indikator')
@section('breadcrumb', 'Referensi Standar')

@php
    $polaWarna = [
        'A' => 'bg-sky-50 text-sky-800 border-sky-200',
        'B' => 'bg-amber-50 text-amber-800 border-amber-200',
        'C' => 'bg-violet-50 text-violet-800 border-violet-200',
        'D' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'E' => 'bg-slate-100 text-slate-700 border-slate-300',
    ];
    $levelLabel = [
        'universitas' => 'Universitas', 'fakultas' => 'Fakultas', 'prodi' => 'Prodi',
        'dosen' => 'Dosen', 'direktorat' => 'Direktorat',
    ];
    $adaFilter = array_filter($filter);
@endphp

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8">

        {{-- Filter --}}
        <form method="GET" action="{{ route('referensi.indikator') }}" class="bg-white border border-brand-100 rounded-2xl p-4 sm:p-5">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.6fr)_minmax(0,1.2fr)_minmax(0,1fr)_auto]">
                <div>
                    <label for="q" class="block text-sm font-medium text-gray-600 mb-1">Cari pernyataan</label>
                    <input id="q" name="q" type="search" value="{{ $filter['q'] ?? '' }}" placeholder="mis. IPK, publikasi, RPS"
                           class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-[15px] focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                </div>
                <div>
                    <label for="standar" class="block text-sm font-medium text-gray-600 mb-1">Standar</label>
                    <select id="standar" name="standar" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-[15px] bg-white focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                        <option value="">Semua standar</option>
                        @foreach ($daftarStandar as $s)
                            <option value="{{ $s->id }}" @selected(($filter['standar'] ?? null) == $s->id)>{{ $s->kode }} {{ $s->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="pola" class="block text-sm font-medium text-gray-600 mb-1">Pola</label>
                    <select id="pola" name="pola" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-[15px] bg-white focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                        <option value="">Semua pola</option>
                        @foreach (\App\Models\Indikator::POLA as $kode => $label)
                            <option value="{{ $kode }}" @selected(($filter['pola'] ?? null) === $kode)>{{ $kode }} · {{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="lingkup" class="block text-sm font-medium text-gray-600 mb-1">Lingkup</label>
                    <select id="lingkup" name="lingkup" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-[15px] bg-white focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                        <option value="">Semua</option>
                        <option value="prodi" @selected(($filter['lingkup'] ?? null) === 'prodi')>Lingkup prodi</option>
                        <option value="luar" @selected(($filter['lingkup'] ?? null) === 'luar')>Di luar lingkup prodi</option>
                    </select>
                </div>
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="flex-1 lg:flex-none bg-brand-600 hover:bg-brand-700 text-white text-[15px] font-medium rounded-xl px-5 py-2.5 transition-colors">Terapkan</button>
                    @if ($adaFilter)
                        <a href="{{ route('referensi.indikator') }}" class="flex-1 lg:flex-none text-center border border-gray-300 hover:bg-gray-50 text-[15px] rounded-xl px-4 py-2.5">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <p class="mt-5 text-sm text-gray-500">
            {{ $indikator->total() }} indikator{{ $adaFilter ? ' sesuai filter' : '' }}.
            Teks indikator disalin persis dari seksi 2.3 dokumen standar.
        </p>

        {{-- Daftar indikator --}}
        <ul class="mt-3 space-y-3">
            @forelse ($indikator as $i)
                <li class="bg-white border border-brand-100 rounded-2xl px-4 py-4 sm:px-5 {{ $i->is_ruang_lingkup_prodi ? '' : 'opacity-80' }}">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <span class="shrink-0 min-w-[52px] text-center text-sm font-semibold text-brand-700 bg-brand-50 rounded-lg px-2 py-1">{{ $i->standar->kode }}.{{ $i->nomor }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-gray-500">Standar {{ $i->standar->nama }} &middot; butir {{ $i->nomor }}</p>
                            <p class="mt-1 text-[15.5px] leading-relaxed whitespace-pre-line">{{ $i->pernyataan }}</p>

                            <div class="mt-3 flex flex-wrap items-center gap-1.5 text-xs">
                                <span class="border rounded-full px-2.5 py-0.5 font-medium {{ $polaWarna[$i->pola] }}" title="{{ \App\Models\Indikator::POLA[$i->pola] }}">Pola {{ $i->pola }} · {{ \App\Models\Indikator::POLA[$i->pola] }}</span>
                                @if ($i->satuan)
                                    <span class="border border-gray-200 bg-gray-50 text-gray-700 rounded-full px-2.5 py-0.5">Satuan: {{ $i->satuan }}</span>
                                @endif
                                <span class="border border-gray-200 bg-gray-50 text-gray-700 rounded-full px-2.5 py-0.5">Tanggung jawab: {{ $levelLabel[$i->level_tanggung_jawab] }}</span>
                                @unless ($i->is_ruang_lingkup_prodi)
                                    <span class="border border-gray-300 bg-white text-gray-600 rounded-full px-2.5 py-0.5">Di luar lingkup prodi</span>
                                @endunless
                                @if ($i->bolehInputManual())
                                    <span class="border border-gray-200 bg-gray-50 text-gray-700 rounded-full px-2.5 py-0.5">Input manual + bukti</span>
                                @else
                                    <span class="border border-gray-200 bg-gray-50 text-gray-700 rounded-full px-2.5 py-0.5">Dihitung dari master data</span>
                                @endif
                            </div>

                            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                                @if ($url = $i->standar->urlPdf($i->halaman_sumber))
                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 font-medium text-brand-700 hover:underline">
                                        @include('layouts.partials.icon', ['name' => 'document', 'class' => 'h-4 w-4'])
                                        PDF halaman {{ $i->halaman_sumber }}
                                    </a>
                                @endif
                                <a href="{{ route('asisten-ai', ['q' => $i->pertanyaanAsisten(), 'ref_indikator' => $i->id]) }}" class="inline-flex items-center gap-1.5 font-medium text-brand-700 hover:underline">
                                    @include('layouts.partials.icon', ['name' => 'chat', 'class' => 'h-4 w-4'])
                                    Tanya Asisten AI
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="bg-white border border-brand-100 rounded-2xl px-6 py-10 text-center text-gray-500">Tidak ada indikator yang cocok dengan filter.</li>
            @endforelse
        </ul>

        @if ($indikator->hasPages())
            <nav class="mt-6 flex items-center justify-between gap-3 text-[15px]" aria-label="Halaman">
                @if ($indikator->onFirstPage())
                    <span class="px-4 py-2 rounded-xl border border-gray-200 text-gray-400">Sebelumnya</span>
                @else
                    <a href="{{ $indikator->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50">Sebelumnya</a>
                @endif
                <span class="text-gray-500">Halaman {{ $indikator->currentPage() }} dari {{ $indikator->lastPage() }}</span>
                @if ($indikator->hasMorePages())
                    <a href="{{ $indikator->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50">Berikutnya</a>
                @else
                    <span class="px-4 py-2 rounded-xl border border-gray-200 text-gray-400">Berikutnya</span>
                @endif
            </nav>
        @endif
    </div>
</main>
@endsection
