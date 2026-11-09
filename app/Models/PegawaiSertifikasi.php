<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PegawaiSertifikasi extends Model
{
    use Auditable, Blameable, SoftDeletes;

    protected $table = 'pegawai_sertifikasi';

    protected $fillable = ['pegawai_id', 'jenis', 'nama', 'lembaga', 'tingkat', 'nomor', 'tanggal_terbit', 'berlaku_sampai'];

    protected function casts(): array
    {
        return ['tanggal_terbit' => 'date', 'berlaku_sampai' => 'date'];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
