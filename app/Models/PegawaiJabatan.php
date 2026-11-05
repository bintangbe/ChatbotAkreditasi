<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PegawaiJabatan extends Model
{
    use Auditable, Blameable, SoftDeletes;

    protected $table = 'pegawai_jabatan';

    // Nilai bawaan form tambah (sama dengan default kolom di database)
    protected $attributes = ['is_aktif' => true];

    protected $fillable = ['pegawai_id', 'jabatan', 'golongan', 'nomor_sk', 'tmt', 'is_aktif'];

    protected function casts(): array
    {
        return ['tmt' => 'date', 'is_aktif' => 'boolean'];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
