<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;

class PenggunaController extends Controller
{
    // Daftar pengguna & peran (read-only). Tambah/ubah via `php artisan siakred:user`
    // sampai modul CRUD generik (Tahap 2) tersedia.
    public function index()
    {
        return view('pengaturan.pengguna', [
            'users' => User::with(['roles', 'prodi'])->orderBy('name')->paginate(25),
            'roles' => Role::withCount('users')->orderBy('id')->get(),
        ]);
    }
}
