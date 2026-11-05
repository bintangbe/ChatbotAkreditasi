@extends('layouts.app')

@section('title', 'Pengguna & Peran')
@section('breadcrumb', 'Pengaturan')

@section('content')
<main class="page-scroll flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">

        <div class="rounded-2xl border border-brand-100 bg-brand-50 px-4 py-3 text-[14.5px] text-gray-700">
            Untuk sementara, pengguna ditambahkan lewat terminal:
            <code class="block sm:inline mt-1 sm:mt-0 text-[13px] text-brand-800 break-all">php artisan siakred:user username email "Nama Lengkap" --role=tim_akreditasi</code>
        </div>

        {{-- Pengguna --}}
        <section class="bg-white border border-brand-100 rounded-2xl overflow-hidden">
            <h2 class="px-4 sm:px-5 py-4 text-[17px] font-semibold border-b border-gray-100">Pengguna ({{ $users->total() }})</h2>
            <ul class="divide-y divide-gray-100">
                @forelse ($users as $u)
                    <li class="px-4 sm:px-5 py-3.5 flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-4">
                        <div class="min-w-0 sm:flex-1">
                            <p class="font-medium text-[15px] truncate">
                                {{ $u->name }}
                                @unless ($u->is_aktif)
                                    <span class="ml-1 text-xs font-normal text-gray-500 border border-gray-300 rounded-full px-2 py-0.5">nonaktif</span>
                                @endunless
                            </p>
                            <p class="text-sm text-gray-500 truncate">{{ $u->username }} · {{ $u->email }}{{ $u->prodi ? ' · ' . $u->prodi->jenjang . ' ' . $u->prodi->nama : '' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-1.5 sm:justify-end">
                            @forelse ($u->roles as $r)
                                <span class="text-xs font-medium bg-brand-50 text-brand-800 border border-brand-100 rounded-full px-2.5 py-0.5">{{ $r->nama }}</span>
                            @empty
                                <span class="text-xs text-gray-400">Tanpa peran</span>
                            @endforelse
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-gray-500">Belum ada pengguna.</li>
                @endforelse
            </ul>
            @if ($users->hasPages())
                <div class="px-4 sm:px-5 py-3 border-t border-gray-100 flex justify-between text-sm">
                    <a href="{{ $users->previousPageUrl() ?? '#' }}" class="{{ $users->onFirstPage() ? 'text-gray-300 pointer-events-none' : 'text-brand-700 hover:underline' }}">Sebelumnya</a>
                    <span class="text-gray-500">{{ $users->currentPage() }} / {{ $users->lastPage() }}</span>
                    <a href="{{ $users->nextPageUrl() ?? '#' }}" class="{{ $users->hasMorePages() ? 'text-brand-700 hover:underline' : 'text-gray-300 pointer-events-none' }}">Berikutnya</a>
                </div>
            @endif
        </section>

        {{-- Peran --}}
        <section class="bg-white border border-brand-100 rounded-2xl overflow-hidden">
            <h2 class="px-4 sm:px-5 py-4 text-[17px] font-semibold border-b border-gray-100">Peran</h2>
            <ul class="divide-y divide-gray-100">
                @foreach ($roles as $r)
                    <li class="px-4 sm:px-5 py-3 flex items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-[15px]">{{ $r->nama }} <code class="ml-1 text-xs text-gray-500">{{ $r->kode }}</code></p>
                            <p class="text-sm text-gray-500">{{ $r->deskripsi }}</p>
                        </div>
                        <span class="shrink-0 text-sm text-gray-500">{{ $r->users_count }} pengguna</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</main>
@endsection
