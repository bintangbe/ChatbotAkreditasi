{{-- Satu input form. Param: $kolom, $f (definisi), $nilai --}}
@php
    $tipe = $f['type'] ?? 'text';
    $aturan = $f['rules'] ?? '';
    $wajib = str_contains(is_array($aturan) ? implode('|', $aturan) : $aturan, 'required');
    $kelasInput = 'w-full bg-white border rounded-xl px-3.5 py-2.5 text-[15px] focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100 '
        . ($errors->has($kolom) ? 'border-brand-500' : 'border-gray-300');
    $nilai = old($kolom, $nilai instanceof \DateTimeInterface ? $nilai->format('Y-m-d') : $nilai);
@endphp
<div class="{{ $tipe === 'textarea' ? 'sm:col-span-2' : '' }}">
    @if ($tipe === 'boolean')
        <label class="flex items-start gap-3 py-2 select-none cursor-pointer">
            <input type="hidden" name="{{ $kolom }}" value="0">
            <input type="checkbox" name="{{ $kolom }}" value="1" @checked($nilai) class="mt-0.5 h-5 w-5 rounded border-gray-300 accent-[#C8161F]">
            <span>
                <span class="text-[15px] font-medium">{{ $f['label'] }}</span>
                @isset($f['bantuan']) <span class="block text-sm text-gray-500">{{ $f['bantuan'] }}</span> @endisset
            </span>
        </label>
    @else
        <label for="f-{{ $kolom }}" class="block text-[15px] font-medium mb-1.5">
            {{ $f['label'] }} @if ($wajib)<span class="text-brand-600" aria-hidden="true">*</span>@endif
        </label>
        @if ($tipe === 'select')
            @php $opsi = $f['options'] ?? \App\Http\Controllers\Master\BaseCrudController::opsiRelasi($f['relasi']); @endphp
            <select id="f-{{ $kolom }}" name="{{ $kolom }}" class="{{ $kelasInput }}" @required($wajib)>
                <option value="">— Pilih —</option>
                @foreach ($opsi as $v => $l)
                    <option value="{{ $v }}" @selected((string) $nilai === (string) $v)>{{ $l }}</option>
                @endforeach
            </select>
        @elseif ($tipe === 'textarea')
            <textarea id="f-{{ $kolom }}" name="{{ $kolom }}" rows="4" class="{{ $kelasInput }}" @required($wajib)>{{ $nilai }}</textarea>
        @else
            <input id="f-{{ $kolom }}" name="{{ $kolom }}" value="{{ $nilai }}" @required($wajib)
                   type="{{ ['email' => 'email', 'number' => 'number', 'decimal' => 'number', 'date' => 'date', 'year' => 'number'][$tipe] ?? 'text' }}"
                   @if ($tipe === 'decimal') step="0.01" inputmode="decimal" @elseif (in_array($tipe, ['number', 'year'])) step="1" inputmode="numeric" @endif
                   class="{{ $kelasInput }}">
        @endif
        @isset($f['bantuan']) <p class="mt-1 text-sm text-gray-500">{{ $f['bantuan'] }}</p> @endisset
        @error($kolom) <p class="mt-1 text-sm text-brand-700">{{ $message }}</p> @enderror
    @endif
</div>
