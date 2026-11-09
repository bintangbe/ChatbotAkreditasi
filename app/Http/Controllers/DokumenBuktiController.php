<?php

namespace App\Http\Controllers;

use App\Models\DokumenBukti;
use App\Models\DokumenRelasi;
use App\Support\Entitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Komponen & Pusat Dokumen Bukti (dev.md §3.4, §4.3, §6.3). */
class DokumenBuktiController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->bolehAkses('dokumen_bukti', 'r'), 403);

        $filter = $request->validate([
            'q'       => ['nullable', 'string', 'max:100'],
            'jenis'   => ['nullable', 'string', 'max:100'],
            'entitas' => ['nullable', 'string', 'max:100'],
        ]);

        $dokumen = DokumenBukti::with('relasi')
            ->when($filter['q'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('judul', 'like', '%' . addcslashes($v, '%_\\') . '%')
                ->orWhere('nomor_dokumen', 'like', '%' . addcslashes($v, '%_\\') . '%')))
            ->when($filter['jenis'] ?? null, fn ($q, $v) => $q->where('jenis', $v))
            ->when($filter['entitas'] ?? null, fn ($q, $v) => $v === '_tanpa'
                ? $q->doesntHave('relasi')
                : $q->whereHas('relasi', fn ($r) => $r->where('entitas', $v)))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('dokumen.index', [
            'dokumen' => $dokumen,
            'filter'  => $filter,
            'daftarEntitas' => DokumenRelasi::distinct()->pluck('entitas'),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->bolehAkses('dokumen_bukti', 'c'), 403);

        $data = $request->validate([
            'file'          => ['required', 'file', 'mimes:' . implode(',', DokumenBukti::MIMES), 'max:' . DokumenBukti::MAKS_KB],
            'judul'         => ['required', 'string', 'max:255'],
            'jenis'         => ['nullable', 'string', 'max:100'],
            'nomor_dokumen' => ['nullable', 'string', 'max:100'],
            'tanggal'       => ['nullable', 'date'],
            'entitas'       => ['nullable', 'string', 'max:100'],
            'entitas_id'    => ['nullable', 'required_with:entitas', 'integer'],
            'periode_id'    => ['nullable', 'integer', 'exists:periode,id'],
        ], [], ['file' => 'berkas']);

        // Hanya boleh menautkan ke entitas terdaftar yang barisnya ada & boleh dibaca pengguna
        if (!empty($data['entitas'])) {
            abort_unless(Entitas::def($data['entitas']) && $request->user()->bolehAkses($data['entitas'], 'r'), 403);
            abort_unless(Entitas::cari($data['entitas'], (int) $data['entitas_id']), 404);
        }

        $file = $request->file('file');
        $path = $file->storeAs('dokumen_bukti/' . now()->format('Y/m'), Str::uuid() . '.' . strtolower($file->getClientOriginalExtension()), 'local');

        DB::transaction(function () use ($data, $file, $path) {
            $dok = DokumenBukti::create([
                'judul'         => $data['judul'],
                'jenis'         => $data['jenis'] ?? null,
                'nomor_dokumen' => $data['nomor_dokumen'] ?? null,
                'tanggal'       => $data['tanggal'] ?? null,
                'file_path'     => $path,
                'file_nama'     => Str::limit($file->getClientOriginalName(), 250, ''),
                'file_mime'     => $file->getMimeType(),
                'file_size'     => $file->getSize(),
            ]);
            if (!empty($data['entitas'])) {
                DokumenRelasi::create([
                    'dokumen_id' => $dok->id,
                    'entitas'    => $data['entitas'],
                    'entitas_id' => $data['entitas_id'],
                    'periode_id' => $data['periode_id'] ?? null,
                ]);
            }
        });

        return back()->with('sukses', 'Dokumen bukti diunggah.');
    }

    public function unduh(Request $request, int $id)
    {
        abort_unless($request->user()->bolehAkses('dokumen_bukti', 'r'), 403);
        $dok = DokumenBukti::findOrFail($id);
        abort_unless(Storage::disk('local')->exists($dok->file_path), 404, 'Berkas tidak ditemukan.');

        $inline = str_starts_with((string) $dok->file_mime, 'image/') || $dok->file_mime === 'application/pdf';

        return Storage::disk('local')->response($dok->file_path, $dok->file_nama ?: basename($dok->file_path), [
            'Content-Type'           => $dok->file_mime,
            'X-Content-Type-Options' => 'nosniff',
        ], $inline ? 'inline' : 'attachment');
    }
}
