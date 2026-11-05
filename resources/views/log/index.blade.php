@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('breadcrumb', auth()->user()->hasRole('administrator') ? 'Pengaturan' : 'Read-only')

@php $kelas = 'w-full border border-gray-300 rounded-xl px-3 py-2.5 text-[15px] bg-white focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100'; @endphp

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1100px] mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-4">
        @include('layouts.partials.flash')

        <form method="GET" class="bg-white border border-brand-100 rounded-2xl p-4 grid sm:grid-cols-2 lg:grid-cols-[repeat(5,minmax(0,1fr))_auto] gap-3 items-end">
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-600 mb-1">Pengguna</label>
                <select id="user_id" name="user_id" class="{{ $kelas }}">
                    <option value="">Semua</option>
                    @foreach ($users as $id => $nama)
                        <option value="{{ $id }}" @selected(($filter['user_id'] ?? null) == $id)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="entitas" class="block text-sm font-medium text-gray-600 mb-1">Entitas</label>
                <select id="entitas" name="entitas" class="{{ $kelas }}">
                    <option value="">Semua</option>
                    @foreach ($daftarEntitas as $e)
                        <option value="{{ $e }}" @selected(($filter['entitas'] ?? null) === $e)>{{ \App\Support\Entitas::label($e) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="aksi" class="block text-sm font-medium text-gray-600 mb-1">Aksi</label>
                <select id="aksi" name="aksi" class="{{ $kelas }}">
                    <option value="">Semua</option>
                    @foreach (['create' => 'Tambah', 'update' => 'Ubah', 'delete' => 'Hapus', 'restore' => 'Pulihkan', 'verify' => 'Status capaian'] as $v => $l)
                        <option value="{{ $v }}" @selected(($filter['aksi'] ?? null) === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="dari" class="block text-sm font-medium text-gray-600 mb-1">Dari</label>
                <input id="dari" type="date" name="dari" value="{{ $filter['dari'] ?? '' }}" class="{{ $kelas }}">
            </div>
            <div>
                <label for="sampai" class="block text-sm font-medium text-gray-600 mb-1">Sampai</label>
                <input id="sampai" type="date" name="sampai" value="{{ $filter['sampai'] ?? '' }}" class="{{ $kelas }}">
            </div>
            <div class="flex gap-2">
                <button class="flex-1 bg-brand-600 hover:bg-brand-700 text-white text-[15px] font-medium rounded-xl px-5 py-2.5">Terapkan</button>
                @if (array_filter($filter))
                    <a href="{{ route('log.index') }}" class="flex-1 text-center border border-gray-300 hover:bg-gray-50 text-[15px] rounded-xl px-4 py-2.5">Reset</a>
                @endif
            </div>
        </form>

        <p class="text-sm text-gray-500">{{ $log->total() }} aktivitas</p>
        @include('log._daftar', ['log' => $log])
    </div>
</main>
@endsection
