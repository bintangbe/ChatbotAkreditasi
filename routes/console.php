<?php

use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Buat / perbarui pengguna beserta perannya, sampai halaman CRUD pengguna tersedia.
// Contoh: php artisan siakred:user budi budi@telkomuniversity.ac.id "Budi Santoso" --role=tim_akreditasi --prodi=S1-SI-SBY
// Peran: administrator, kaprodi, tim_akreditasi, tim_gkm
Artisan::command('siakred:user {username} {email} {nama} {--role=*} {--prodi=} {--password=}', function () {
    $kodeRole = $this->option('role');
    if (!$kodeRole) {
        $this->error('Sebutkan minimal satu peran dengan --role=...');
        $this->line('Pilihan: ' . implode(', ', array_keys(config('permissions.daftar'))));
        return 1;
    }
    $roles = Role::whereIn('kode', $kodeRole)->get();
    $tidakAda = array_diff($kodeRole, $roles->pluck('kode')->all());
    if ($tidakAda) {
        $this->error('Peran tidak dikenal: ' . implode(', ', $tidakAda));
        $this->line('Pilihan: ' . Role::orderBy('id')->pluck('kode')->implode(', '));
        return 1;
    }

    $prodiId = null;
    if ($kode = $this->option('prodi')) {
        $prodiId = ProgramStudi::where('kode', $kode)->value('id');
        if (!$prodiId) {
            $this->error("Program studi '{$kode}' tidak ditemukan.");
            return 1;
        }
    }

    $password = $this->option('password') ?: Str::password(12, symbols: false);
    $user = User::withTrashed()->updateOrCreate(
        ['username' => $this->argument('username')],
        [
            'email'    => $this->argument('email'),
            'name'     => $this->argument('nama'),
            'password' => $password,
            'prodi_id' => $prodiId,
            'is_aktif' => true,
        ]
    );
    $user->restore();
    $user->roles()->sync($roles->pluck('id'));

    $this->info("Pengguna '{$user->username}' disimpan dengan peran: " . $roles->pluck('kode')->implode(', '));
    if (!$this->option('password')) {
        $this->warn("Password: {$password}");
    }
})->purpose('Buat atau perbarui pengguna SIAKRED beserta perannya');
