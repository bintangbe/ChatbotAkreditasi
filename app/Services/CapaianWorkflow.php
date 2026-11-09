<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CapaianIndikator;
use App\Models\User;
use App\Models\Verifikasi;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * State machine status capaian (dev.md §3.3).
 *
 *   draft ──ajukan(tim_akreditasi)──► diajukan
 *   diajukan ──verifikasi(tim_gkm)──► terverifikasi
 *   diajukan ──tolak(tim_gkm)──────► ditolak ──revisi──► draft
 *   terverifikasi ──setujui(kaprodi)──► disetujui
 *   terverifikasi ──kembalikan(kaprodi)──► dikembalikan ──revisi──► draft
 *
 * Sesuai §5.2, `ajukan` juga boleh langsung dari `ditolak`.
 * Transisi tidak sah dilempar sebagai DomainException — UI tidak boleh jadi satu-satunya penjaga.
 */
class CapaianWorkflow
{
    /** transisi => [status asal yang sah, status tujuan, aksi wewenang, catatan wajib?] */
    public const TRANSISI = [
        'ajukan'     => [['draft', 'ditolak'],               'diajukan',      'ajukan_capaian',     false],
        'verifikasi' => [['diajukan'],                       'terverifikasi', 'verifikasi_capaian', false],
        'tolak'      => [['diajukan'],                       'ditolak',       'verifikasi_capaian', true],
        'setujui'    => [['terverifikasi'],                  'disetujui',     'setujui_capaian',    false],
        'kembalikan' => [['terverifikasi'],                  'dikembalikan',  'setujui_capaian',    true],
        'revisi'     => [['ditolak', 'dikembalikan'],        'draft',         'ajukan_capaian',     false],
    ];

    // Peran yang dicatat sebagai pelaku untuk tiap aksi wewenang
    private const PERAN_AKSI = [
        'ajukan_capaian'     => 'tim_akreditasi',
        'verifikasi_capaian' => 'tim_gkm',
        'setujui_capaian'    => 'kaprodi',
    ];

    /** Transisi yang boleh dilakukan $user atas $capaian saat ini (untuk merender tombol). */
    public function tersedia(CapaianIndikator $capaian, User $user): array
    {
        return array_keys(array_filter(
            self::TRANSISI,
            fn ($t) => in_array($capaian->status, $t[0], true) && $user->bolehAksi($t[2])
        ));
    }

    public function jalankan(CapaianIndikator $capaian, string $transisi, User $user, ?string $catatan = null): CapaianIndikator
    {
        $aturan = self::TRANSISI[$transisi] ?? throw new DomainException("Transisi '{$transisi}' tidak dikenal.");
        [$asal, $tujuan, $wewenang, $catatanWajib] = $aturan;

        if (!$user->bolehAksi($wewenang)) {
            throw new DomainException('Peran Anda tidak berwenang melakukan transisi ini.');
        }
        if (!in_array($capaian->status, $asal, true)) {
            throw new DomainException("Capaian berstatus '{$capaian->status}' tidak dapat di-{$transisi}.");
        }
        $catatan = trim((string) $catatan) ?: null;
        if ($catatanWajib && !$catatan) {
            throw new DomainException('Catatan wajib diisi untuk transisi ini.');
        }

        return DB::transaction(function () use ($capaian, $transisi, $tujuan, $wewenang, $user, $catatan) {
            // Kunci baris supaya dua pengguna tidak memproses capaian yang sama bersamaan
            $capaian = CapaianIndikator::lockForUpdate()->findOrFail($capaian->id);
            $statusLama = $capaian->status;

            $capaian->status = $tujuan;
            $capaian->save();

            // Tabel verifikasi hanya punya status hasil evaluasi (§3.3)
            if (in_array($tujuan, ['terverifikasi', 'ditolak', 'dikembalikan', 'disetujui'], true)) {
                Verifikasi::create([
                    'capaian_id'  => $capaian->id,
                    'user_id'     => $user->id,
                    'role_pelaku' => self::PERAN_AKSI[$wewenang],
                    'status'      => $tujuan,
                    'catatan'     => $catatan,
                ]);
            }

            AuditLog::create([
                'user_id'    => $user->id,
                'entitas'    => 'capaian_indikator',
                'entitas_id' => $capaian->id,
                'aksi'       => 'verify',
                'data_lama'  => ['status' => $statusLama],
                'data_baru'  => ['status' => $tujuan, 'transisi' => $transisi, 'catatan' => $catatan, 'role_pelaku' => self::PERAN_AKSI[$wewenang]],
                'ip_address' => request()?->ip(),
            ]);

            return $capaian;
        });
    }
}
