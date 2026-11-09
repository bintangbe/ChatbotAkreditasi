<?php

namespace App\Services;

use App\Models\CapaianIndikator;
use App\Models\Indikator;
use App\Models\Periode;
use App\Models\Standar;
use App\Models\TargetIndikator;
use Illuminate\Support\Collection;

/**
 * Ringkasan capaian per standar untuk satu periode & prodi.
 * "Tercapai" = capaian berstatus disetujui yang memenuhi target sesuai arah indikator.
 */
class RingkasanCapaian
{
    public function __construct(private ?Periode $periode, private ?int $prodiId) {}

    private function lingkupProdi($q)
    {
        return $q->where(fn ($w) => $w->whereNull('prodi_id')->orWhere('prodi_id', $this->prodiId));
    }

    /** capaian periode ini, di-key per indikator_id */
    public function capaian(): Collection
    {
        if (!$this->periode) {
            return collect();
        }

        return $this->lingkupProdi(CapaianIndikator::where('periode_id', $this->periode->id))
            ->get()->keyBy('indikator_id');
    }

    public function target(): Collection
    {
        if (!$this->periode) {
            return collect();
        }

        return $this->lingkupProdi(TargetIndikator::where('periode_id', $this->periode->id))
            ->get()->keyBy('indikator_id');
    }

    public static function memenuhi(Indikator $i, ?CapaianIndikator $c, ?TargetIndikator $t): ?bool
    {
        if (!$c || !$t || $c->nilai === null) {
            return null; // belum bisa dinilai
        }

        return match ($i->arah) {
            'turun' => (float) $c->nilai <= (float) $t->nilai_target,
            'tetap' => abs((float) $c->nilai - (float) $t->nilai_target) < 0.005,
            default => (float) $c->nilai >= (float) $t->nilai_target,
        };
    }

    /**
     * Per standar: total indikator lingkup prodi, ada data, disetujui, tercapai, persen, plus
     * daftar indikator di bawah target.
     */
    public function perStandar(): array
    {
        $capaian = $this->capaian();
        $target = $this->target();
        $standar = Standar::where('is_berlaku', true)->orderBy('kode')
            ->with(['indikator' => fn ($q) => $q->where('is_ruang_lingkup_prodi', true)])
            ->get();

        $kartu = [];
        $dibawahTarget = [];
        foreach ($standar as $s) {
            $n = ['total' => 0, 'ada_data' => 0, 'disetujui' => 0, 'tercapai' => 0];
            foreach ($s->indikator as $i) {
                $n['total']++;
                $c = $capaian[$i->id] ?? null;
                if ($c) {
                    $n['ada_data']++;
                }
                if ($c?->status === 'disetujui') {
                    $n['disetujui']++;
                    $ok = self::memenuhi($i, $c, $target[$i->id] ?? null);
                    if ($ok) {
                        $n['tercapai']++;
                    } elseif ($ok === false) {
                        $dibawahTarget[] = ['indikator' => $i->setRelation('standar', $s), 'capaian' => $c, 'target' => $target[$i->id]];
                    }
                }
            }
            $n['persen'] = $n['total'] ? (int) round($n['tercapai'] / $n['total'] * 100) : 0;
            $kartu[] = ['standar' => $s] + $n;
        }

        return ['kartu' => $kartu, 'dibawah_target' => $dibawahTarget];
    }
}
