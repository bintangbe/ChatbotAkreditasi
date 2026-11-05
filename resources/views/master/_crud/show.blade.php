@extends('layouts.app')

@php
    $Base = \App\Http\Controllers\Master\BaseCrudController::class;
    $namaRecord = $Base::tampil($record, $kolomJudul, $fields[$kolomJudul] ?? null);
    $terhapus = method_exists($record, 'trashed') && $record->trashed();
    $u = auth()->user();
@endphp

@section('title', $namaRecord)
@section('breadcrumb', $grup . ' · ' . $judul)

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-5">
        @include('layouts.partials.flash')

        @if ($terhapus)
            <div class="rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
                <p class="text-[15px] text-gray-700">Data ini sudah dihapus pada {{ $record->deleted_at->format('d/m/Y H:i') }}.</p>
                @if ($boleh['restore'])
                    <form method="POST" action="{{ route($routeBase . '.restore', $record->id) }}">
                        @csrf
                        <button class="bg-brand-600 hover:bg-brand-700 text-white rounded-xl px-4 py-2 text-[15px] font-medium">Pulihkan</button>
                    </form>
                @endif
            </div>
        @endif

        {{-- Aksi --}}
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route($routeBase) }}" class="inline-flex items-center gap-1.5 text-[15px] text-gray-600 hover:text-brand-700 mr-auto">
                @include('layouts.partials.icon', ['name' => 'chevron', 'class' => 'h-4 w-4 rotate-180']) Kembali ke daftar
            </a>
            @if ($boleh['log'])
                <a href="{{ route($routeBase . '.log', $record->id) }}" class="inline-flex items-center gap-2 border border-gray-300 hover:bg-gray-50 rounded-xl px-4 py-2.5 text-[15px]">
                    @include('layouts.partials.icon', ['name' => 'clock', 'class' => 'h-4 w-4']) Riwayat
                </a>
            @endif
            @if (!$terhapus && $boleh['u'])
                <a href="{{ route($routeBase . '.edit', $record->id) }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl px-4 py-2.5 text-[15px] font-medium">Ubah</a>
            @endif
            @if (!$terhapus && $boleh['d'])
                @include('master._crud._hapus', ['action' => route($routeBase . '.destroy', $record->id), 'id' => 'utama', 'nama' => $namaRecord])
            @endif
        </div>

        {{-- Data utama --}}
        <section class="bg-white border border-brand-100 rounded-2xl p-4 sm:p-5">
            <dl class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                @foreach ($fields as $kolom => $f)
                    <div class="min-w-0">
                        <dt class="text-sm text-gray-500">{{ $f['label'] }}</dt>
                        <dd class="text-[15px] break-words">{{ $Base::tampil($record, $kolom, $f) }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        {{-- Relasi anak --}}
        @foreach ($anak as $kunci => $a)
            @php
                $bolehAnak = ['c' => $u->bolehAkses($a['entitas'], 'c'), 'u' => $u->bolehAkses($a['entitas'], 'u'), 'd' => $u->bolehAkses($a['entitas'], 'd')];
            @endphp
            <section id="{{ $kunci }}" class="bg-white border border-brand-100 rounded-2xl overflow-hidden scroll-mt-4">
                <div class="px-4 sm:px-5 py-3.5 border-b border-gray-100 flex items-center justify-between gap-3">
                    <h2 class="text-[17px] font-semibold">{{ $a['judul'] }} <span class="text-sm font-normal text-gray-500">({{ $a['rows']->count() }})</span></h2>
                    @if (!$terhapus && $bolehAnak['c'])
                        <a href="{{ route($routeBase . '.anak.create', [$record->id, $kunci]) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-700 hover:underline shrink-0">
                            @include('layouts.partials.icon', ['name' => 'plus', 'class' => 'h-4 w-4']) Tambah
                        </a>
                    @endif
                </div>
                @if ($a['rows']->isEmpty())
                    <p class="px-5 py-6 text-center text-[15px] text-gray-500">Belum ada data.</p>
                @else
                    {{-- Judul kolom (layar lebar) --}}
                    <div class="hidden md:flex items-center gap-4 px-5 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <div class="flex-1 min-w-0 grid grid-cols-{{ min(count($a['kolom']), 5) }} gap-x-4">
                            @foreach ($a['kolom'] as $k)
                                <span class="truncate">{{ $a['fields'][$k]['label'] }}</span>
                            @endforeach
                        </div>
                        @if (!$terhapus && ($bolehAnak['u'] || $bolehAnak['d']))
                            <span class="shrink-0 w-[92px]"><span class="sr-only">Aksi</span></span>
                        @endif
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($a['rows'] as $r)
                            <li class="px-4 sm:px-5 py-3 flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
                                <div class="flex-1 min-w-0 grid grid-cols-2 md:grid-cols-{{ min(count($a['kolom']), 5) }} gap-x-4 gap-y-1">
                                    @foreach ($a['kolom'] as $k)
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-500 md:hidden">{{ $a['fields'][$k]['label'] }}</p>
                                            <p class="text-[15px] truncate" title="{{ $Base::tampil($r, $k, $a['fields'][$k]) }}">{{ $Base::tampil($r, $k, $a['fields'][$k]) }}</p>
                                        </div>
                                    @endforeach
                                </div>
                                @if (!$terhapus && ($bolehAnak['u'] || $bolehAnak['d']))
                                    <div class="shrink-0 md:w-[92px] flex items-center gap-4">
                                        @if ($bolehAnak['u'])
                                            <a href="{{ route($routeBase . '.anak.edit', [$record->id, $kunci, $r->id]) }}" class="text-sm font-medium text-brand-700 hover:underline">Ubah</a>
                                        @endif
                                        @if ($bolehAnak['d'])
                                            @include('master._crud._hapus', [
                                                'action' => route($routeBase . '.anak.destroy', [$record->id, $kunci, $r->id]),
                                                'id' => $kunci . '-' . $r->id, 'nama' => $a['judul'] . ' ini', 'kecil' => true,
                                            ])
                                        @endif
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endforeach

        <div class="grid lg:grid-cols-2 gap-5 items-start">
            {{-- Dokumen bukti --}}
            @include('components.dokumen-bukti', ['entitas' => $entitas, 'entitasId' => $record->id, 'dokumen' => $dokumen, 'nonaktif' => $terhapus])

            {{-- Indikator terdampak (dev.md §4.3) --}}
            <section class="bg-white border border-brand-100 rounded-2xl overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 border-b border-gray-100">
                    <h2 class="text-[17px] font-semibold">Indikator terdampak</h2>
                    <p class="text-sm text-gray-500">Angka indikator berikut dihitung dari data {{ strtolower($judul) }}. Perubahan di sini menandai capaiannya untuk dihitung ulang.</p>
                </div>
                @if ($terdampak->isEmpty())
                    <p class="px-5 py-6 text-center text-[15px] text-gray-500">Tidak ada indikator yang bergantung pada data ini.</p>
                @else
                    <ul class="divide-y divide-gray-100 max-h-[360px] overflow-y-auto">
                        @foreach ($terdampak as $i)
                            <li class="px-4 sm:px-5 py-2.5 flex items-start gap-3">
                                <span class="shrink-0 min-w-[46px] text-sm font-semibold text-brand-700">{{ $i->standar->kode }}.{{ $i->nomor }}</span>
                                <span class="text-[14.5px] line-clamp-2">{{ $i->pernyataan }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</main>
@endsection
