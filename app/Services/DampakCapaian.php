<?php

namespace App\Services;

use App\Models\CapaianIndikator;
use App\Models\Indikator;
use Illuminate\Support\Collection;

/** Hubungan tabel master <-> indikator lewat config/sumber_data.php (dev.md §4.3). */
class DampakCapaian
{
    /** sumber_data yang membaca tabel ini */
    public static function sumberDataUntuk(string $tabel): array
    {
        return array_keys(array_filter(config('sumber_data', []), fn ($tabelList) => in_array($tabel, $tabelList, true)));
    }

    /** Indikator yang angkanya bergantung pada tabel ini */
    public static function indikatorTerdampak(string $tabel): Collection
    {
        $sumber = self::sumberDataUntuk($tabel);
        if (!$sumber) {
            return collect();
        }

        return Indikator::with('standar')
            ->whereIn('sumber_data', $sumber)
            ->get()
            ->sortBy(fn ($i) => $i->standar->kode . '.' . str_pad($i->nomor, 3, '0', STR_PAD_LEFT))
            ->values();
    }

    /** Tandai capaian indikator terdampak agar dihitung ulang. */
    public static function tandaiHitungUlang(string $tabel): void
    {
        $sumber = self::sumberDataUntuk($tabel);
        if (!$sumber) {
            return;
        }

        CapaianIndikator::whereNotNull('dihitung_pada')
            ->whereIn('indikator_id', Indikator::whereIn('sumber_data', $sumber)->select('id'))
            ->update(['dihitung_pada' => null]);
    }
}
