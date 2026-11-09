{{-- Daftar entri audit_log. Param: $log (paginator) --}}
@php
    use App\Support\Entitas;
    $warnaAksi = [
        'create'  => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'update'  => 'bg-sky-50 text-sky-800 border-sky-200',
        'delete'  => 'bg-brand-50 text-brand-800 border-brand-200',
        'restore' => 'bg-amber-50 text-amber-800 border-amber-200',
        'verify'  => 'bg-violet-50 text-violet-800 border-violet-200',
    ];
    $labelAksi = ['create' => 'Tambah', 'update' => 'Ubah', 'delete' => 'Hapus', 'restore' => 'Pulihkan', 'verify' => 'Status capaian'];
    $fmt = fn ($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (is_bool($v) ? ($v ? 'Ya' : 'Tidak') : ($v === null || $v === '' ? '–' : (string) $v));
    $bolehRestore = auth()->user()->bolehAksi('restore');
@endphp

@if ($log->isEmpty())
    <div class="bg-white border border-brand-100 rounded-2xl px-6 py-12 text-center text-gray-500 text-[15px]">Belum ada aktivitas tercatat.</div>
@else
    <ul class="space-y-3">
        @foreach ($log as $l)
            @php $url = Entitas::url($l->entitas, $l->entitas_id); @endphp
            <li class="bg-white border border-brand-100 rounded-2xl px-4 sm:px-5 py-3.5">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span class="text-xs font-medium border rounded-full px-2.5 py-0.5 {{ $warnaAksi[$l->aksi] ?? '' }}">{{ $labelAksi[$l->aksi] ?? $l->aksi }}</span>
                    <span class="text-[15px] font-medium">
                        {{ Entitas::label($l->entitas) }}
                        @if ($url) <a href="{{ $url }}" class="text-brand-700 hover:underline">#{{ $l->entitas_id }}</a> @else #{{ $l->entitas_id }} @endif
                    </span>
                    <span class="text-sm text-gray-500 sm:ml-auto">{{ $l->user?->name ?? 'Sistem' }} · {{ $l->created_at?->format('d/m/Y H:i') }}</span>
                </div>

                @php
                    $kunci = array_unique(array_merge(array_keys($l->data_lama ?? []), array_keys($l->data_baru ?? [])));
                @endphp
                @if ($kunci && $l->aksi !== 'create')
                    <details class="mt-2 group">
                        <summary class="cursor-pointer text-sm text-brand-700 hover:underline select-none">Lihat {{ count($kunci) }} perubahan</summary>
                        <div class="mt-2 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-gray-500 text-left"><tr><th class="py-1 pr-3 font-medium">Kolom</th><th class="py-1 pr-3 font-medium">Sebelum</th><th class="py-1 font-medium">Sesudah</th></tr></thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($kunci as $k)
                                        <tr class="align-top">
                                            <td class="py-1 pr-3 text-gray-600 whitespace-nowrap">{{ $k }}</td>
                                            <td class="py-1 pr-3 text-gray-500 break-all">{{ Str::limit($fmt($l->data_lama[$k] ?? null), 200) }}</td>
                                            <td class="py-1 break-all">{{ Str::limit($fmt($l->data_baru[$k] ?? null), 200) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endif

                @if ($l->aksi === 'delete' && $bolehRestore)
                    @php $row = Entitas::cari($l->entitas, $l->entitas_id); @endphp
                    @if ($row && method_exists($row, 'trashed') && $row->trashed())
                        <form method="POST" action="{{ route('log.restore', $l->id) }}" class="mt-2">
                            @csrf
                            <button class="text-sm font-medium text-brand-700 hover:underline">Pulihkan data ini</button>
                        </form>
                    @endif
                @endif
            </li>
        @endforeach
    </ul>

    @if ($log->hasPages())
        <nav class="mt-4 flex items-center justify-between gap-3 text-[15px]" aria-label="Halaman">
            <a href="{{ $log->previousPageUrl() ?? '#' }}" class="px-4 py-2 rounded-xl border {{ $log->onFirstPage() ? 'border-gray-200 text-gray-300 pointer-events-none' : 'border-gray-300 bg-white hover:bg-gray-50' }}">Sebelumnya</a>
            <span class="text-gray-500">Halaman {{ $log->currentPage() }} dari {{ $log->lastPage() }}</span>
            <a href="{{ $log->nextPageUrl() ?? '#' }}" class="px-4 py-2 rounded-xl border {{ $log->hasMorePages() ? 'border-gray-300 bg-white hover:bg-gray-50' : 'border-gray-200 text-gray-300 pointer-events-none' }}">Berikutnya</a>
        </nav>
    @endif
@endif
