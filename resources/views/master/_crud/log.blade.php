@extends('layouts.app')

@php
    $Base = \App\Http\Controllers\Master\BaseCrudController::class;
    $namaRecord = $Base::tampil($record, $kolomJudul, $fields[$kolomJudul] ?? null);
@endphp

@section('title', 'Riwayat · ' . $namaRecord)
@section('breadcrumb', $grup . ' · ' . $judul)

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[960px] mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-4">
        <a href="{{ route($routeBase . '.show', $record->id) }}" class="inline-flex items-center gap-1.5 text-[15px] text-gray-600 hover:text-brand-700">
            @include('layouts.partials.icon', ['name' => 'chevron', 'class' => 'h-4 w-4 rotate-180']) Kembali ke detail
        </a>
        @include('log._daftar', ['log' => $log])
    </div>
</main>
@endsection
