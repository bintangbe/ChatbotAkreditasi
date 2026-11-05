<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

// Hanya 4 peran (dev.md §6). Peran lain yang masih ada di tabel dihapus.
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = config('permissions.daftar');

        foreach ($daftar as $kode => [$nama, $deskripsi]) {
            Role::updateOrCreate(['kode' => $kode], ['nama' => $nama, 'deskripsi' => $deskripsi]);
        }

        Role::whereNotIn('kode', array_keys($daftar))->delete();
    }
}
