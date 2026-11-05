<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PegawaiPenugasan extends Model
{
    use Auditable, Blameable, SoftDeletes;

    protected $table = 'pegawai_penugasan';

    protected $fillable = ['pegawai_id', 'periode_id', 'jenis', 'institusi', 'negara', 'mitra_id', 'tanggal_mulai', 'tanggal_akhir', 'durasi_bulan'];

    protected function casts(): array
    {
        return ['tanggal_mulai' => 'date', 'tanggal_akhir' => 'date'];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }
}
