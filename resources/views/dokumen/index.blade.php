@extends('layouts.app')

@section('title', 'Pusat Dokumen Bukti')
@section('breadcrumb', 'Dokumen Bukti')

@php $kelas = 'w-full border border-gray-300 rounded-xl px-3 py-2.5 text-[15px] bg-white focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100'; @endphp

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-4">
        @include('layouts.partials.flash')

        <form method="GET" class="bg-white border border-brand-100 rounded-2xl p-4 grid sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] gap-3 items-end">
            <div>
                <label for="q" class="block text-sm font-medium text-gray-600 mb-1">Cari judul / nomor</label>
                <input id="q" type="search" name="q" value="{{ $filter['q'] ?? '' }}" class="{{ $kelas }}">
            </div>
            <div>
                <label for="jenis" class="block text-sm font-medium text-gray-600 mb-1">Jenis</label>
                <select id="jenis" name="jenis" class="{{ $kelas }}">
                    <option value="">Semua</option>
                    @foreach (\App\Models\DokumenBukti::JENIS as $j)
                        <option @selected(($filter['jenis'] ?? null) === $j)>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="entitas" class="block text-sm font-medium text-gray-600 mb-1">Terlampir pada</label>
                <select id="entitas" name="entitas" class="{{ $kelas }}">
                    <option value="">Semua</option>
                    <option value="_tanpa" @selected(($filter['entitas'] ?? null) === '_tanpa')>Belum dilampirkan</option>
                    @foreach ($daftarEntitas as $e)
                        <option value="{{ $e }}" @selected(($filter['entitas'] ?? null) === $e)>{{ \App\Support\Entitas::label($e) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button class="flex-1 bg-brand-600 hover:bg-brand-700 text-white text-[15px] font-medium rounded-xl px-5 py-2.5">Terapkan</button>
                @if (array_filter($filter))
                    <a href="{{ route('dokumen.index') }}" class="flex-1 text-center border border-gray-300 hover:bg-gray-50 text-[15px] rounded-xl px-4 py-2.5">Reset</a>
                @endif
            </div>
        </form>

        <p class="text-sm text-gray-500">{{ $dokumen->total() }} dokumen. Unggah dokumen dari halaman detail data (mis. Pegawai) agar langsung tertaut.</p>

        @if ($dokumen->isEmpty())
            <div class="bg-white border border-brand-100 rounded-2xl px-6 py-12 text-center text-gray-500 text-[15px]">Belum ada dokumen.</div>
        @else
            <ul class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach ($dokumen as $d)
                    <li class="bg-white border border-brand-100 rounded-2xl p-4 flex gap-3">
                        <div class="w-10 h-10 shrink-0 rounded-xl bg-brand-50 text-brand-700 grid place-items-center">
                            @include('layouts.partials.icon', ['name' => 'document'])
                        </div>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('dokumen.unduh', $d->id) }}" target="_blank" rel="noopener" class="block text-[15px] font-medium hover:text-brand-700 hover:underline line-clamp-2">{{ $d->judul }}</a>
                            <p class="text-xs text-gray-500 truncate">{{ collect([$d->jenis, $d->nomor_dokumen, $d->tanggal?->format('d/m/Y'), $d->ukuranTeks()])->filter()->implode(' · ') }}</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @forelse ($d->relasi as $r)
                                    @php $url = \App\Support\Entitas::url($r->entitas, $r->entitas_id); @endphp
                                    <a @if ($url) href="{{ $url }}" @endif class="text-xs border border-gray-200 bg-gray-50 rounded-full px-2.5 py-0.5 {{ $url ? 'hover:border-brand-300 hover:text-brand-700' : '' }}">
                                        {{ \App\Support\Entitas::label($r->entitas) }} #{{ $r->entitas_id }}
                                    </a>
                                @empty
                                    <span class="text-xs border border-amber-200 bg-amber-50 text-amber-800 rounded-full px-2.5 py-0.5">Belum dilampirkan</span>
                                @endforelse
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if ($dokumen->hasPages())
                <nav class="flex items-center justify-between gap-3 text-[15px]" aria-label="Halaman">
                    <a href="{{ $dokumen->previousPageUrl() ?? '#' }}" class="px-4 py-2 rounded-xl border {{ $dokumen->onFirstPage() ? 'border-gray-200 text-gray-300 pointer-events-none' : 'border-gray-300 bg-white hover:bg-gray-50' }}">Sebelumnya</a>
                    <span class="text-gray-500">Halaman {{ $dokumen->currentPage() }} dari {{ $dokumen->lastPage() }}</span>
                    <a href="{{ $dokumen->nextPageUrl() ?? '#' }}" class="px-4 py-2 rounded-xl border {{ $dokumen->hasMorePages() ? 'border-gray-300 bg-white hover:bg-gray-50' : 'border-gray-200 text-gray-300 pointer-events-none' }}">Berikutnya</a>
                </nav>
            @endif
        @endif
    </div>
</main>
@endsection
