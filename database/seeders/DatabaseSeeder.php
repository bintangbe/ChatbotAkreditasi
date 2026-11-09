<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// Urutan sesuai dev.md §9
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PeriodeSeeder::class,
            ProgramStudiSeeder::class,
            StandarIndikatorSeeder::class,
            AkunContohSeeder::class,
        ]);
    }
}
