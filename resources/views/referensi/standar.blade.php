@extends('layouts.app')

@section('title', 'Daftar Standar')
@section('breadcrumb', 'Referensi Standar')

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <p class="text-[15px] text-gray-600 max-w-[760px]">Standar Mutu Universitas Telkom (dokumen SPM) yang saat ini masuk korpus. Klik standar untuk melihat indikatornya.</p>

        @if ($standar->isEmpty())
            <div class="mt-5 bg-white border border-brand-100 rounded-2xl px-6 py-10 text-center text-gray-500">
                Belum ada data standar. Jalankan <code class="text-brand-800">php artisan db:seed</code>.
            </div>
        @else
            {{-- Tabel: layar sedang ke atas --}}
            <div class="hidden md:block mt-5 bg-white border border-brand-100 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-[15px]">
                    <thead class="bg-brand-50 text-brand-800 text-sm">
                        <tr>
                            <th class="px-5 py-3 font-semibold w-20">Kode</th>
                            <th class="px-5 py-3 font-semibold">Standar</th>
                            <th class="px-5 py-3 font-semibold">Nomor dokumen</th>
                            <th class="px-5 py-3 font-semibold">Revisi</th>
                            <th class="px-5 py-3 font-semibold text-right">Indikator</th>
                            <th class="px-5 py-3 font-semibold text-right">PDF</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($standar as $s)
                            <tr class="hover:bg-brand-50/40">
                                <td class="px-5 py-3 font-semibold text-brand-700">{{ $s->kode }}</td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('referensi.indikator', ['standar' => $s->id]) }}" class="font-medium hover:text-brand-700 hover:underline">{{ $s->nama }}</a>
                                    <span class="block text-sm text-gray-500 capitalize">{{ $s->klaster }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $s->nomor_dokumen }}</td>
                                <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $s->tahun_standar }} · Rev {{ $s->revisi }}</td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    {{ $s->indikator_count }}
                                    @if ($s->indikator_prodi_count < $s->indikator_count)
                                        <span class="block text-xs text-gray-500">{{ $s->indikator_prodi_count }} lingkup prodi</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    @if ($url = $s->urlPdf())
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-sm font-medium text-brand-700 hover:underline">
                                            Buka @include('layouts.partials.icon', ['name' => 'external', 'class' => 'h-4 w-4'])
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-400">&ndash;</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 text-sm">
                        <tr>
                            <td class="px-5 py-3 font-semibold" colspan="4">Total</td>
                            <td class="px-5 py-3 font-semibold text-right">{{ $standar->sum('indikator_count') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Kartu: layar HP --}}
            <ul class="md:hidden mt-5 space-y-3">
                @foreach ($standar as $s)
                    <li>
                        <a href="{{ route('referensi.indikator', ['standar' => $s->id]) }}" class="bg-white border border-brand-100 rounded-2xl px-4 py-3.5 flex items-center gap-3 active:bg-brand-50">
                            <span class="w-12 shrink-0 text-center text-sm font-semibold text-brand-700 bg-brand-50 rounded-lg py-1">{{ $s->kode }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-[15px] font-medium">{{ $s->nama }}</p>
                                <p class="text-sm text-gray-500"><span class="capitalize">{{ $s->klaster }}</span> · {{ $s->indikator_count }} indikator</p>
                            </div>
                            @include('layouts.partials.icon', ['name' => 'chevron', 'class' => 'h-4 w-4 text-gray-400 shrink-0'])
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</main>
@endsection
