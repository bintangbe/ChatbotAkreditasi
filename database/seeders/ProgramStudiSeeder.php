<?php

namespace Database\Seeders;

use App\Models\ProgramStudi;
use Illuminate\Database\Seeder;

class ProgramStudiSeeder extends Seeder
{
    public function run(): void
    {
        // Fakultas belum diisi: lengkapi lewat modul Program Studi
        ProgramStudi::updateOrCreate(['kode' => 'S1-SI-SBY'], [
            'nama'    => 'Sistem Informasi',
            'jenjang' => 'S1',
            'kampus'  => 'Telkom University Surabaya',
        ]);
    }
}
