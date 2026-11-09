<?php

namespace Database\Seeders;

use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

// Satu akun contoh per peran (dev.md §9). Akun yang sudah ada tidak diubah passwordnya.
// Password: .env SIAKRED_DEMO_PASSWORD bila diisi, selain itu acak & ditampilkan sekali.
class AkunContohSeeder extends Seeder
{
    private const AKUN = [
        ['admin',      'Administrator',          'administrator'],
        ['kaprodi',    'Kepala Program Studi',   'kaprodi'],
        ['akreditasi', 'Anggota Tim Akreditasi', 'tim_akreditasi'],
        ['gkm',        'Anggota Tim GKM',        'tim_gkm'],
    ];

    public function run(): void
    {
        $prodiId = ProgramStudi::where('kode', 'S1-SI-SBY')->value('id');
        $passwordTetap = env('SIAKRED_DEMO_PASSWORD');

        foreach (self::AKUN as [$username, $nama, $peran]) {
            $user = User::withTrashed()->where('username', $username)->first();

            if (!$user) {
                $password = $passwordTetap ?: Str::password(12, symbols: false);
                $user = User::create([
                    'username' => $username,
                    'name'     => $nama,
                    'email'    => "{$username}@siakred.local",
                    'password' => $password,
                    'prodi_id' => $peran === 'administrator' ? null : $prodiId,
                    'is_aktif' => true,
                ]);
                $this->command?->warn(sprintf('Akun %-11s (%s) -> password: %s', $username, $peran, $passwordTetap ? '(dari .env)' : $password));
            }

            $user->roles()->syncWithoutDetaching(Role::where('kode', $peran)->pluck('id'));
        }
    }
}
