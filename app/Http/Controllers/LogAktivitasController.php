<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\Entitas;
use Illuminate\Http\Request;

/** Log Aktivitas (dev.md §6.1 & §6.4): filter pengguna/entitas/aksi, restore untuk administrator. */
class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->validate([
            'user_id' => ['nullable', 'integer'],
            'entitas' => ['nullable', 'string', 'max:100'],
            'aksi'    => ['nullable', 'in:create,update,delete,restore,verify'],
            'dari'    => ['nullable', 'date'],
            'sampai'  => ['nullable', 'date'],
        ]);

        $log = AuditLog::with('user')
            ->when($filter['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filter['entitas'] ?? null, fn ($q, $v) => $q->where('entitas', $v))
            ->when($filter['aksi'] ?? null, fn ($q, $v) => $q->where('aksi', $v))
            ->when($filter['dari'] ?? null, fn ($q, $v) => $q->where('created_at', '>=', $v))
            ->when($filter['sampai'] ?? null, fn ($q, $v) => $q->where('created_at', '<', \Carbon\Carbon::parse($v)->addDay()))
            ->latest('created_at')->latest('id')
            ->paginate(30)->withQueryString();

        return view('log.index', [
            'log'     => $log,
            'filter'  => $filter,
            'users'   => User::withTrashed()->orderBy('name')->pluck('name', 'id'),
            'daftarEntitas' => AuditLog::distinct()->orderBy('entitas')->pluck('entitas'),
        ]);
    }

    public function restore(Request $request, AuditLog $log)
    {
        abort_unless($request->user()->bolehAksi('restore'), 403);
        abort_unless($log->aksi === 'delete', 422);

        $row = Entitas::cari($log->entitas, $log->entitas_id);
        abort_unless($row && method_exists($row, 'trashed') && $row->trashed(), 404, 'Data sudah dipulihkan atau tidak ditemukan.');
        $row->restore();

        return back()->with('sukses', Entitas::label($log->entitas) . ' #' . $log->entitas_id . ' dipulihkan.');
    }
}
