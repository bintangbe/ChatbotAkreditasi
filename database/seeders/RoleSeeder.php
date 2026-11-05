<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('permissions.daftar') as $kode => [$nama, $deskripsi]) {
            Role::updateOrCreate(['kode' => $kode], ['nama' => $nama, 'deskripsi' => $deskripsi]);
        }
    }
}
