<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

// Akun admin_spm awal. Password dari .env SIAKRED_ADMIN_PASSWORD, atau dibuat acak lalu ditampilkan sekali.
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('username', 'admin')->exists()) {
            $this->command?->info('Akun admin sudah ada, dilewati.');
            return;
        }

        $password = env('SIAKRED_ADMIN_PASSWORD') ?: Str::password(14, symbols: false);

        $user = User::create([
            'username' => 'admin',
            'name'     => 'Administrator SPM',
            'email'    => 'admin@siakred.local',
            'password' => $password,
            'is_aktif' => true,
        ]);
        $user->roles()->sync(Role::where('kode', 'admin_spm')->pluck('id'));

        if (!env('SIAKRED_ADMIN_PASSWORD')) {
            $this->command?->warn("Akun admin dibuat -> username: admin | password: {$password}");
            $this->command?->warn('Simpan password ini dan segera ganti.');
        }
    }
}
