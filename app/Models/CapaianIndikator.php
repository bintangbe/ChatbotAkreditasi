<?php

namespace App\Models;

use App\Models\Concerns\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CapaianIndikator extends Model
{
    use Blameable, SoftDeletes;

    protected $table = 'capaian_indikator';

    public const STATUS = [
        'draft'         => 'Draft',
        'diajukan'      => 'Diajukan',
        'terverifikasi' => 'Terverifikasi',
        'ditolak'       => 'Ditolak',
        'dikembalikan'  => 'Dikembalikan',
        'disetujui'     => 'Disetujui',
    ];

    protected $fillable = [
        'indikator_id', 'periode_id', 'prodi_id', 'nilai', 'metode', 'rincian',
        'catatan', 'dihitung_pada', 'status',
    ];

    protected function casts(): array
    {
        return [
            'rincian'       => 'array',
            'dihitung_pada' => 'datetime',
        ];
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(Indikator::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function verifikasi(): HasMany
    {
        return $this->hasMany(Verifikasi::class, 'capaian_id')->latest('created_at');
    }
}
