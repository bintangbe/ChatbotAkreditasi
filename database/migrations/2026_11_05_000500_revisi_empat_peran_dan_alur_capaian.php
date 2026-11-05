<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * Revisi 4 peran (dev.md §6) + alur status capaian (dev.md §3.3).
 * - Enum status capaian: + dikembalikan, disetujui
 * - Tabel verifikasi mencatat seluruh transisi: status baru + kolom role_pelaku
 * - 10 peran lama dipetakan ke 4 peran baru, lalu dihapus
 */
return new class extends Migration
{
    // peran lama => peran baru (dosen tidak punya padanan: user-nya jadi tanpa peran)
    private const PETA = [
        'admin_spm'           => 'administrator',
        'pimpinan'            => 'kaprodi',
        'verifikator'         => 'tim_gkm',
        'admin_prodi'         => 'tim_akreditasi',
        'operator_sdm'        => 'tim_akreditasi',
        'operator_akademik'   => 'tim_akreditasi',
        'operator_penelitian' => 'tim_akreditasi',
        'operator_keuangan'   => 'tim_akreditasi',
        'operator_sarpras'    => 'tim_akreditasi',
    ];

    public function up(): void
    {
        DB::statement("ALTER TABLE capaian_indikator MODIFY status
            ENUM('draft','diajukan','terverifikasi','ditolak','dikembalikan','disetujui') NOT NULL DEFAULT 'draft'");

        DB::statement("ALTER TABLE verifikasi
            MODIFY status ENUM('terverifikasi','ditolak','dikembalikan','disetujui') NOT NULL,
            ADD COLUMN role_pelaku VARCHAR(50) NOT NULL AFTER user_id");

        // Peran baru
        foreach (config('permissions.daftar') as $kode => [$nama, $deskripsi]) {
            DB::table('roles')->updateOrInsert(['kode' => $kode], ['nama' => $nama, 'deskripsi' => $deskripsi]);
        }
        $idBaru = DB::table('roles')->pluck('id', 'kode');

        // Pindahkan pemegang peran lama
        foreach (self::PETA as $lama => $baru) {
            $idLama = $idBaru[$lama] ?? null;
            if (!$idLama) {
                continue;
            }
            foreach (DB::table('user_roles')->where('role_id', $idLama)->pluck('user_id') as $userId) {
                DB::table('user_roles')->insertOrIgnore(['user_id' => $userId, 'role_id' => $idBaru[$baru]]);
            }
        }

        // Hapus peran lama (user_roles ikut terhapus lewat cascade)
        DB::table('roles')->whereNotIn('kode', array_keys(config('permissions.daftar')))->delete();
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE verifikasi
            DROP COLUMN role_pelaku,
            MODIFY status ENUM('terverifikasi','ditolak') NOT NULL");

        DB::statement("ALTER TABLE capaian_indikator MODIFY status
            ENUM('draft','diajukan','terverifikasi','ditolak') NOT NULL DEFAULT 'draft'");
        // Pemetaan peran tidak dibalik: jalankan ulang RoleSeeder versi lama bila perlu.
    }
};
