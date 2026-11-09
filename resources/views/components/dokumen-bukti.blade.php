{{--
    Komponen dokumen bukti reusable (dev.md §4.3). Dipasang di halaman mana pun:
    @include('components.dokumen-bukti', ['entitas' => 'pegawai', 'entitasId' => $id, 'dokumen' => $dokumen])
    Opsional: 'periodeId' (ditautkan ke periode), 'nonaktif' (sembunyikan form unggah)
--}}
@php
    $u = auth()->user();
    $bolehUnggah = $u->bolehAkses('dokumen_bukti', 'c') && empty($nonaktif);
    $idForm = 'unggah-' . $entitas . '-' . $entitasId;
@endphp
<section class="bg-white border border-brand-100 rounded-2xl overflow-hidden">
    <div class="px-4 sm:px-5 py-3.5 border-b border-gray-100 flex items-center justify-between gap-3">
        <h2 class="text-[17px] font-semibold">Dokumen bukti <span class="text-sm font-normal text-gray-500">({{ $dokumen->count() }})</span></h2>
        @if ($bolehUnggah)
            <button type="button" onclick="document.getElementById('{{ $idForm }}').classList.toggle('hidden')" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-700 hover:underline shrink-0">
                @include('layouts.partials.icon', ['name' => 'upload', 'class' => 'h-4 w-4']) Unggah
            </button>
        @endif
    </div>

    @if ($bolehUnggah)
        <form id="{{ $idForm }}" method="POST" action="{{ route('dokumen.store') }}" enctype="multipart/form-data"
              class="{{ $errors->hasAny(['file', 'judul']) ? '' : 'hidden' }} px-4 sm:px-5 py-4 bg-brand-50/50 border-b border-gray-100 grid sm:grid-cols-2 gap-3">
            @csrf
            <input type="hidden" name="entitas" value="{{ $entitas }}">
            <input type="hidden" name="entitas_id" value="{{ $entitasId }}">
            @isset($periodeId) <input type="hidden" name="periode_id" value="{{ $periodeId }}"> @endisset
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1" for="{{ $idForm }}-file">Berkas <span class="text-brand-600">*</span></label>
                <input id="{{ $idForm }}-file" type="file" name="file" required accept=".{{ implode(',.', \App\Models\DokumenBukti::MIMES) }}"
                       class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-brand-600 file:text-white file:px-3 file:py-2 file:font-medium hover:file:bg-brand-700">
                <p class="mt-1 text-xs text-gray-500">PDF, JPG, PNG, DOCX, atau XLSX. Maksimal 10 MB.</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="{{ $idForm }}-judul">Judul <span class="text-brand-600">*</span></label>
                <input id="{{ $idForm }}-judul" name="judul" required maxlength="255" value="{{ old('judul') }}"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2 text-[15px] focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="{{ $idForm }}-jenis">Jenis</label>
                <select id="{{ $idForm }}-jenis" name="jenis" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-[15px] bg-white focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                    <option value="">— Pilih —</option>
                    @foreach (\App\Models\DokumenBukti::JENIS as $j)
                        <option @selected(old('jenis') === $j)>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="{{ $idForm }}-nomor">Nomor dokumen</label>
                <input id="{{ $idForm }}-nomor" name="nomor_dokumen" maxlength="100" value="{{ old('nomor_dokumen') }}"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2 text-[15px] focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="{{ $idForm }}-tgl">Tanggal dokumen</label>
                <input id="{{ $idForm }}-tgl" type="date" name="tanggal" value="{{ old('tanggal') }}"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2 text-[15px] focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
            </div>
            <div class="sm:col-span-2 flex justify-end">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white rounded-xl px-4 py-2 text-[15px] font-medium">Unggah dokumen</button>
            </div>
        </form>
    @endif

    @if ($dokumen->isEmpty())
        <p class="px-5 py-6 text-center text-[15px] text-gray-500">Belum ada dokumen bukti.</p>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach ($dokumen as $d)
                <li class="px-4 sm:px-5 py-3 flex items-center gap-3">
                    <div class="w-9 h-9 shrink-0 rounded-lg bg-brand-50 text-brand-700 grid place-items-center">
                        @include('layouts.partials.icon', ['name' => 'document', 'class' => 'h-5 w-5'])
                    </div>
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('dokumen.unduh', $d->id) }}" target="_blank" rel="noopener" class="block text-[15px] font-medium truncate hover:text-brand-700 hover:underline">{{ $d->judul }}</a>
                        <p class="text-xs text-gray-500 truncate">{{ collect([$d->jenis, $d->nomor_dokumen, $d->tanggal?->format('d/m/Y'), $d->ukuranTeks()])->filter()->implode(' · ') }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</section>
