@extends('layouts.app')

@section('title', 'Antrean Verifikasi')
@section('breadcrumb', 'Tim Gugus Kendali Mutu')

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-4">
        <p class="text-[15px] text-gray-600">Capaian berstatus <strong>diajukan</strong>, urut dari pengajuan terlama.</p>

        @if ($antrean->isEmpty())
            <div class="bg-white border border-brand-100 rounded-2xl px-6 py-12 text-center">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-brand-50 text-brand-700 grid place-items-center">
                    @include('layouts.partials.icon', ['name' => 'check', 'class' => 'h-6 w-6'])
                </div>
                <p class="mt-3 text-[16px] font-medium">Tidak ada capaian yang menunggu verifikasi.</p>
            </div>
        @else
            {{-- Tabel: layar lebar --}}
            <div class="hidden md:block bg-white border border-brand-100 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-[15px]">
                    <thead class="bg-brand-50 text-brand-800 text-sm">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Indikator</th>
                            <th class="px-4 py-3 font-semibold text-right">Angka</th>
                            <th class="px-4 py-3 font-semibold text-right">Target</th>
                            <th class="px-4 py-3 font-semibold text-right">Bukti</th>
                            <th class="px-4 py-3 font-semibold">Diajukan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($antrean as $c)
                            <tr class="align-top">
                                <td class="px-4 py-3">
                                    <span class="text-sm font-semibold text-brand-700">{{ $c->indikator->standar->kode }}.{{ $c->indikator->nomor }}</span>
                                    <span class="block text-[14.5px] line-clamp-2">{{ $c->indikator->pernyataan }}</span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">{{ $c->nilai === null ? '–' : rtrim(rtrim($c->nilai, '0'), '.') }} {{ $c->indikator->satuan }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">{{ isset($target[$c->indikator_id]) ? rtrim(rtrim($target[$c->indikator_id]->nilai_target, '0'), '.') : '–' }}</td>
                                <td class="px-4 py-3 text-right text-gray-400">–</td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $c->updated_at->translatedFormat('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Kartu: HP --}}
            <ul class="md:hidden space-y-3">
                @foreach ($antrean as $c)
                    <li class="bg-white border border-brand-100 rounded-2xl px-4 py-3.5">
                        <span class="text-sm font-semibold text-brand-700">{{ $c->indikator->standar->kode }}.{{ $c->indikator->nomor }}</span>
                        <p class="text-[15px] line-clamp-3">{{ $c->indikator->pernyataan }}</p>
                        <p class="mt-2 text-sm text-gray-600">
                            Angka {{ $c->nilai === null ? '–' : rtrim(rtrim($c->nilai, '0'), '.') }}
                            · Target {{ isset($target[$c->indikator_id]) ? rtrim(rtrim($target[$c->indikator_id]->nilai_target, '0'), '.') : '–' }}
                            · {{ $c->updated_at->translatedFormat('d M Y') }}
                        </p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</main>
@endsection
