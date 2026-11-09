<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DokumenBukti extends Model
{
    use Auditable, Blameable, SoftDeletes;

    protected $table = 'dokumen_bukti';

    public const JENIS = ['SK', 'MoU', 'Sertifikat', 'Laporan', 'RPS', 'Surat Tugas', 'Ijazah', 'Foto', 'Lainnya'];
    public const MIMES = ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'xlsx'];
    public const MAKS_KB = 10240; // 10 MB (dev.md §4.3)

    protected $fillable = [
        'judul', 'jenis', 'nomor_dokumen', 'tanggal', 'file_path', 'file_nama', 'file_mime',
        'file_size', 'versi', 'parent_id',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function relasi(): HasMany
    {
        return $this->hasMany(DokumenRelasi::class, 'dokumen_id');
    }

    public function ukuranTeks(): string
    {
        $b = (int) $this->file_size;
        return $b >= 1048576 ? round($b / 1048576, 1) . ' MB' : max(1, round($b / 1024)) . ' KB';
    }

    /** Dokumen yang ditautkan ke satu baris entitas */
    public static function untuk(string $entitas, int $id)
    {
        return static::whereHas('relasi', fn ($q) => $q->where('entitas', $entitas)->where('entitas_id', $id))
            ->latest()->get();
    }
}
