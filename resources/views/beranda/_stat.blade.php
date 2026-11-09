{{-- Kartu angka. Param: label, nilai, ket (opsional), href (opsional), nada: normal|perhatian --}}
@php
    $nada = $nada ?? 'normal';
    $kelas = 'block bg-white border rounded-2xl px-4 py-4 sm:px-5 sm:py-5 '
        . ($nada === 'perhatian' ? 'border-brand-300' : 'border-brand-100')
        . (isset($href) ? ' hover:border-brand-300 transition-colors' : '');
@endphp
@if (isset($href))
<a href="{{ $href }}" class="{{ $kelas }}">
@else
<div class="{{ $kelas }}">
@endif
    <p class="text-[13px] sm:text-sm text-gray-500">{{ $label }}</p>
    <p class="mt-1 text-[22px] sm:text-[28px] font-bold leading-tight break-words {{ $nada === 'perhatian' ? 'text-brand-600' : 'text-brand-700' }}">{{ $nilai }}</p>
    @isset($ket)
        <p class="mt-1 text-[13px] text-gray-500">{{ $ket }}</p>
    @endisset
@if (isset($href))
</a>
@else
</div>
@endif
