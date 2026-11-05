<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pegawai extends Model
{
    use Auditable, Blameable, SoftDeletes;

    protected $table = 'pegawai';

    // Nilai bawaan form tambah (sama dengan default kolom di database)
    protected $attributes = ['is_aktif' => true, 'warga_negara' => 'Indonesia'];

    protected $fillable = [
        'nip', 'nidn', 'nama', 'jenis', 'status_kepegawaian', 'prodi_id', 'unit',
        'kelompok_keahlian', 'warga_negara', 'email', 'is_aktif',
    ];

    protected function casts(): array
    {
        return ['is_aktif' => 'boolean'];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'prodi_id');
    }

    public function pendidikan(): HasMany  { return $this->hasMany(PegawaiPendidikan::class); }
    public function sertifikasi(): HasMany { return $this->hasMany(PegawaiSertifikasi::class); }
    public function jabatan(): HasMany     { return $this->hasMany(PegawaiJabatan::class); }
    public function rekognisi(): HasMany   { return $this->hasMany(PegawaiRekognisi::class); }
    public function penugasan(): HasMany   { return $this->hasMany(PegawaiPenugasan::class); }
}
