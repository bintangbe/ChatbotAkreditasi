@if (session('sukses'))
    <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-[15px]">{{ session('sukses') }}</div>
@endif
@if ($errors->any())
    <div role="alert" class="rounded-xl border border-brand-200 bg-brand-50 text-brand-800 px-4 py-3 text-[15px]">
        <p class="font-medium">Periksa kembali isian berikut:</p>
        <ul class="mt-1 list-disc pl-5 text-sm">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif
