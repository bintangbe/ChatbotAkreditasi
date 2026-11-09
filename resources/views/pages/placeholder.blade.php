@extends('layouts.app')

@section('title', $judul)
@if ($grup)
    @section('breadcrumb', $grup)
@endif

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-10 sm:py-16">
        <div class="max-w-[520px] mx-auto text-center bg-white border border-brand-100 rounded-2xl px-6 py-10">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-brand-50 text-brand-700 grid place-items-center">
                @include('layouts.partials.icon', ['name' => 'tools', 'class' => 'h-7 w-7'])
            </div>
            <h2 class="mt-4 text-[20px] font-semibold">Halaman sedang disiapkan</h2>
            <p class="mt-2 text-[15px] text-gray-500">Modul <strong class="text-gray-700">{{ $judul }}</strong> belum tersedia. Sementara itu, Anda bisa bertanya ke Asisten AI seputar isi dokumen standar.</p>
            <a href="{{ route('asisten-ai') }}" class="mt-5 inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-[15px] font-medium rounded-xl px-4 py-2.5 transition-colors">
                @include('layouts.partials.icon', ['name' => 'chat', 'class' => 'h-5 w-5'])
                Tanya Asisten AI
            </a>
        </div>
    </div>
</main>
@endsection
