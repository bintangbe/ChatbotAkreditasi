<?php

namespace Database\Seeders;

use App\Models\Periode;
use Illuminate\Database\Seeder;

// Semester ganjil: Agustus–Januari, genap: Februari–Juli. Tahun kalender untuk indikator tahunan.
class PeriodeSeeder extends Seeder
{
    public function run(): void
    {
        $aktif = '2026/2027-1';

        foreach ([2023, 2024, 2025, 2026] as $th) {
            $ta = $th . '/' . ($th + 1);

            $baris = [
                [$ta, "Tahun Akademik {$ta}", 'tahun_akademik', "{$th}-08-01", ($th + 1) . '-07-31'],
                ["{$ta}-1", "Semester Ganjil {$ta}", 'semester', "{$th}-08-01", ($th + 1) . '-01-31'],
                ["{$ta}-2", "Semester Genap {$ta}", 'semester', ($th + 1) . '-02-01', ($th + 1) . '-07-31'],
                ["{$th}", "Tahun {$th}", 'tahun_kalender', "{$th}-01-01", "{$th}-12-31"],
            ];

            foreach ($baris as [$kode, $nama, $jenis, $mulai, $akhir]) {
                if ($jenis === 'semester' && $mulai > now()->toDateString()) {
                    continue; // jangan buat semester yang belum dimulai
                }
                Periode::updateOrCreate(['kode' => $kode], [
                    'nama'          => $nama,
                    'jenis'         => $jenis,
                    'tanggal_mulai' => $mulai,
                    'tanggal_akhir' => $akhir,
                    'is_aktif'      => $kode === $aktif,
                ]);
            }
        }
    }
}
