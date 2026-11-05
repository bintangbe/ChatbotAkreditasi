@extends('layouts.app')

@section('title', $judulForm)
@section('breadcrumb', $grup . ' · ' . $judul)

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[880px] mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-4">
        @include('layouts.partials.flash')

        <form method="POST" action="{{ $action }}" class="bg-white border border-brand-100 rounded-2xl p-4 sm:p-6" novalidate>
            @csrf
            @if ($method !== 'POST') @method($method) @endif

            <div class="grid sm:grid-cols-2 gap-x-5 gap-y-4">
                @foreach ($formFields as $kolom => $f)
                    @include('master._crud._field', ['kolom' => $kolom, 'f' => $f, 'nilai' => $record->{$kolom}])
                @endforeach
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <a href="{{ $kembali }}" class="text-center border border-gray-300 hover:bg-gray-50 rounded-xl px-5 py-2.5 text-[15px]">Batal</a>
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white rounded-xl px-5 py-2.5 text-[15px] font-medium">Simpan</button>
            </div>
        </form>
        <p class="text-sm text-gray-500"><span class="text-brand-600">*</span> wajib diisi. Setiap perubahan tercatat di log aktivitas.</p>
    </div>
</main>
@endsection
