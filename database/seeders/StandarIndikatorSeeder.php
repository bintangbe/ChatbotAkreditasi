<?php

namespace Database\Seeders;

use App\Models\Indikator;
use App\Models\Standar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// dev.md §9 langkah 2–3: 10 standar + 84 indikator dari seksi 2.3 tiap PDF.
// Data: database/seeders/data/standar_indikator.php. Aman dijalankan ulang (upsert).
class StandarIndikatorSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (require __DIR__ . '/data/standar_indikator.php' as $s) {
                $standar = Standar::updateOrCreate(
                    ['kode' => $s['kode'], 'tahun_standar' => 2020, 'revisi' => '01'],
                    [
                        'nomor_dokumen' => $s['nomor_dokumen'],
                        'nama'          => $s['nama'],
                        'klaster'       => $s['klaster'],
                        'file_path'     => $s['file_path'],
                        'is_berlaku'    => true,
                    ]
                );

                foreach ($s['indikator'] as $i) {
                    Indikator::updateOrCreate(
                        ['standar_id' => $standar->id, 'nomor' => $i['nomor']],
                        $i
                    );
                }
            }
        });

        $this->command?->info('Standar: ' . Standar::count() . ', indikator: ' . Indikator::count());
    }
}
