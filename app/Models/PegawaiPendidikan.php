<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PegawaiPendidikan extends Model
{
    use Auditable, Blameable, SoftDeletes;

    protected $table = 'pegawai_pendidikan';

    protected $fillable = ['pegawai_id', 'jenjang', 'bidang', 'institusi', 'negara', 'tahun_lulus', 'is_tertinggi'];

    protected function casts(): array
    {
        return ['is_tertinggi' => 'boolean'];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
