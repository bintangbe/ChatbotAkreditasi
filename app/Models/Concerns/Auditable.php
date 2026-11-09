<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use App\Services\DampakCapaian;
use Illuminate\Support\Facades\Auth;

/**
 * Mencatat create/update/delete/restore ke audit_log (dev.md §0 prinsip 3, §3.2)
 * dan menandai capaian terdampak untuk dihitung ulang (§4.3).
 */
trait Auditable
{
    /** Alasan hapus diisi controller sebelum delete(); ikut dicatat di audit_log. */
    public static ?string $alasanHapus = null;

    private static array $kolomDiabaikan = ['created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'password', 'remember_token'];

    public static function bootAuditable(): void
    {
        static::created(fn ($m) => $m->catatAudit('create', null, $m->dataAudit($m->getAttributes())));

        static::updated(function ($m) {
            $berubah = $m->dataAudit($m->getChanges());
            if ($berubah) {
                $lama = array_intersect_key($m->dataAudit($m->getOriginal()), $berubah);
                $m->catatAudit('update', $lama, $berubah);
            }
        });

        static::deleted(function ($m) {
            $baru = static::$alasanHapus ? ['alasan' => static::$alasanHapus] : null;
            $m->catatAudit('delete', $m->dataAudit($m->getAttributes()), $baru);
            static::$alasanHapus = null;
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(fn ($m) => $m->catatAudit('restore', null, null));
        }
    }

    private function dataAudit(array $data): array
    {
        return array_diff_key($data, array_flip(self::$kolomDiabaikan));
    }

    private function catatAudit(string $aksi, ?array $lama, ?array $baru): void
    {
        AuditLog::create([
            'user_id'    => Auth::id(),
            'entitas'    => $this->getTable(),
            'entitas_id' => $this->getKey(),
            'aksi'       => $aksi,
            'data_lama'  => $lama,
            'data_baru'  => $baru,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);

        DampakCapaian::tandaiHitungUlang($this->getTable());
    }
}
