<?php

namespace App\Models;

use App\Models\Concerns\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indikator extends Model
{
    use Blameable, SoftDeletes;

    protected $table = 'indikator';

    public const POLA = [
        'A' => 'Ketersediaan dokumen',
        'B' => 'Hitungan / tren',
        'C' => 'Rasio / rata-rata',
        'D' => 'Index kepuasan',
        'E' => 'Kesesuaian / mapping',
    ];

    protected $fillable = [
        'standar_id', 'nomor', 'pernyataan', 'pola', 'satuan', 'arah', 'sumber_data',
        'formula', 'level_tanggung_jawab', 'is_ruang_lingkup_prodi', 'halaman_sumber',
    ];

    protected function casts(): array
    {
        return ['is_ruang_lingkup_prodi' => 'boolean'];
    }

    public function standar(): BelongsTo
    {
        return $this->belongsTo(Standar::class);
    }

    public function target(): HasMany
    {
        return $this->hasMany(TargetIndikator::class);
    }

    public function capaian(): HasMany
    {
        return $this->hasMany(CapaianIndikator::class);
    }

    /** Pola A & D boleh input manual; B/C/E wajib dihitung dari master data (dev.md §5.3). */
    public function bolehInputManual(): bool
    {
        return in_array($this->pola, ['A', 'D'], true);
    }

    /** Pertanyaan siap pakai untuk deep link ke Asisten AI (dev.md §8.3). */
    public function pertanyaanAsisten(): string
    {
        return "Jelaskan Indikator nomor {$this->nomor} pada Standar {$this->standar->nama}, "
             . 'dan dokumen bukti apa saja yang dibutuhkan.';
    }
}
