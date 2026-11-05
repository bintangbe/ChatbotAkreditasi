@extends('layouts.app')

@section('title', $judul)
@section('breadcrumb', $grup)

@php $Base = \App\Http\Controllers\Master\BaseCrudController::class; @endphp

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-4">
        @include('layouts.partials.flash')

        {{-- Cari, filter, tambah --}}
        <form method="GET" action="{{ route($routeBase) }}" class="bg-white border border-brand-100 rounded-2xl p-4 flex flex-col lg:flex-row lg:items-end gap-3">
            <div class="flex-1 min-w-0">
                <label for="q" class="block text-sm font-medium text-gray-600 mb-1">Cari</label>
                <input id="q" name="q" type="search" value="{{ $q }}" placeholder="Ketik kata kunci…"
                       class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-[15px] focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
            </div>
            @foreach ($filter as $kolom)
                @php $f = $fields[$kolom]; $opsi = $f['options'] ?? $Base::opsiRelasi($f['relasi']); @endphp
                <div class="lg:w-48">
                    <label for="flt-{{ $kolom }}" class="block text-sm font-medium text-gray-600 mb-1">{{ $f['label'] }}</label>
                    <select id="flt-{{ $kolom }}" name="{{ $kolom }}" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-[15px] bg-white focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                        <option value="">Semua</option>
                        @foreach ($opsi as $v => $l)
                            <option value="{{ $v }}" @selected(($nilaiFilter[$kolom] ?? null) == $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            @if ($boleh['restore'])
                <label class="flex items-center gap-2 text-[15px] text-gray-600 lg:pb-2.5 select-none">
                    <input type="checkbox" name="terhapus" value="1" @checked($terhapus) class="h-4 w-4 accent-[#C8161F]"> Data terhapus
                </label>
            @endif
            <div class="flex gap-2">
                <button type="submit" class="flex-1 lg:flex-none bg-brand-600 hover:bg-brand-700 text-white text-[15px] font-medium rounded-xl px-5 py-2.5">Terapkan</button>
                @if ($q !== '' || $nilaiFilter || $terhapus)
                    <a href="{{ route($routeBase) }}" class="flex-1 lg:flex-none text-center border border-gray-300 hover:bg-gray-50 text-[15px] rounded-xl px-4 py-2.5">Reset</a>
                @endif
            </div>
        </form>

        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-gray-500">{{ $rows->total() }} data{{ $terhapus ? ' terhapus' : '' }}</p>
            @if ($boleh['c'] && !$terhapus)
                <a href="{{ route($routeBase . '.create') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-[15px] font-medium rounded-xl px-4 py-2.5">
                    @include('layouts.partials.icon', ['name' => 'plus', 'class' => 'h-4 w-4']) Tambah
                </a>
            @endif
        </div>

        @if ($rows->isEmpty())
            <div class="bg-white border border-brand-100 rounded-2xl px-6 py-12 text-center text-gray-500 text-[15px]">
                {{ $q !== '' || $nilaiFilter ? 'Tidak ada data yang cocok.' : 'Belum ada data.' }}
            </div>
        @else
            {{-- Tabel: layar sedang ke atas --}}
            <div class="hidden md:block bg-white border border-brand-100 rounded-2xl overflow-x-auto">
                <table class="w-full text-left text-[15px]">
                    <thead class="bg-brand-50 text-brand-800 text-sm">
                        <tr>
                            @foreach ($kolomTabel as $k)
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">{{ $fields[$k]['label'] ?? $k }}</th>
                            @endforeach
                            <th class="px-4 py-3"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($rows as $row)
                            <tr class="hover:bg-brand-50/40">
                                @foreach ($kolomTabel as $k)
                                    <td class="px-4 py-3 {{ $k === $kolomJudul ? 'font-medium' : 'text-gray-700' }}">
                                        @if ($k === $kolomJudul)
                                            <a href="{{ route($routeBase . '.show', $row->id) }}" class="hover:text-brand-700 hover:underline">{{ $Base::tampil($row, $k, $fields[$k] ?? null) }}</a>
                                        @else
                                            {{ $Base::tampil($row, $k, $fields[$k] ?? null) }}
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route($routeBase . '.show', $row->id) }}" class="text-sm font-medium text-brand-700 hover:underline">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Kartu: HP --}}
            <ul class="md:hidden space-y-3">
                @foreach ($rows as $row)
                    <li>
                        <a href="{{ route($routeBase . '.show', $row->id) }}" class="block bg-white border border-brand-100 rounded-2xl px-4 py-3.5 active:bg-brand-50">
                            <p class="text-[15px] font-medium">{{ $Base::tampil($row, $kolomJudul, $fields[$kolomJudul] ?? null) }}</p>
                            <p class="mt-0.5 text-sm text-gray-500">
                                {{ collect($kolomTabel)->reject(fn ($k) => $k === $kolomJudul)->map(fn ($k) => $Base::tampil($row, $k, $fields[$k] ?? null))->reject(fn ($v) => $v === '–')->implode(' · ') }}
                            </p>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if ($rows->hasPages())
                <nav class="flex items-center justify-between gap-3 text-[15px]" aria-label="Halaman">
                    <a href="{{ $rows->previousPageUrl() ?? '#' }}" class="px-4 py-2 rounded-xl border {{ $rows->onFirstPage() ? 'border-gray-200 text-gray-300 pointer-events-none' : 'border-gray-300 bg-white hover:bg-gray-50' }}">Sebelumnya</a>
                    <span class="text-gray-500">Halaman {{ $rows->currentPage() }} dari {{ $rows->lastPage() }}</span>
                    <a href="{{ $rows->nextPageUrl() ?? '#' }}" class="px-4 py-2 rounded-xl border {{ $rows->hasMorePages() ? 'border-gray-300 bg-white hover:bg-gray-50' : 'border-gray-200 text-gray-300 pointer-events-none' }}">Berikutnya</a>
                </nav>
            @endif
        @endif
    </div>
</main>
@endsection
