<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Standar extends Model
{
    use Auditable, Blameable, SoftDeletes;

    protected $table = 'standar';

    protected $fillable = [
        'kode', 'nomor_dokumen', 'nama', 'klaster', 'tahun_standar', 'revisi',
        'tanggal_sah', 'file_path', 'is_berlaku',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sah' => 'date',
            'is_berlaku'  => 'boolean',
        ];
    }

    public function indikator(): HasMany
    {
        return $this->hasMany(Indikator::class)->orderBy('nomor');
    }

    /** ID file Google Drive dari config/dokumen.php (null kalau belum didaftarkan). */
    public function driveFileId(): ?string
    {
        return $this->file_path ? config('dokumen.drive.' . $this->file_path) : null;
    }

    /** URL PDF sumber, opsional langsung ke halaman tertentu. */
    public function urlPdf(?int $halaman = null): ?string
    {
        $id = $this->driveFileId();
        if (!$id) {
            return null;
        }

        return route('chat.doc', $id) . ($halaman ? '#page=' . $halaman : '');
    }
}
