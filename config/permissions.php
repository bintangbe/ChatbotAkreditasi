<?php

/*
|--------------------------------------------------------------------------
| Matriks hak akses — 4 peran (markdowns/dev.md §6)
|--------------------------------------------------------------------------
| Prinsip: yang mengisi data tidak memverifikasi; yang memverifikasi tidak
| menetapkan target; administrator tidak ikut menilai mutu.
|
| 'entitas' : nama tabel => huruf aksi. c=create r=read u=update d=delete.
|             '*' berlaku untuk entitas yang tidak disebut eksplisit.
|             Dicek lewat User::bolehAkses() & middleware `akses:entitas,aksi`.
| 'aksi'    : aksi khusus di luar CRUD. Dicek lewat User::bolehAksi() &
|             middleware `aksi:nama`. Transisi status capaian juga dijaga
|             ulang di App\Services\CapaianWorkflow.
| 'lingkup_log' : semua | prodi | sendiri (Log Aktivitas)
*/

// Seluruh tabel master data (§3.5–§3.10)
$master = [
    'program_studi',
    'pegawai', 'pegawai_pendidikan', 'pegawai_sertifikasi', 'pegawai_jabatan', 'pegawai_rekognisi', 'pegawai_penugasan',
    'mahasiswa', 'lulusan', 'tracer_study',
    'kurikulum', 'peo', 'plo', 'peo_plo_map', 'mata_kuliah', 'clo', 'plo_clo_map', 'rps', 'rps_pertemuan', 'mk_pengampu', 'penilaian_mk',
    'mitra', 'kerjasama', 'kegiatan_mahasiswa',
    'roadmap_penelitian', 'penelitian', 'penelitian_anggota', 'luaran', 'pemanfaatan_hasil', 'inkubasi_startup', 'skor_sinta',
    'sarpras', 'laboratorium', 'survei', 'survei_item', 'survei_respon', 'anggaran', 'anggaran_realisasi',
    'akreditasi_sertifikasi', 'laporan_pddikti',
];
$referensi = ['standar', 'indikator'];

return [

    // Urutan juga menentukan "peran utama" bila satu pengguna punya beberapa peran
    'daftar' => [
        'administrator'  => ['Administrator', 'Teknis & konfigurasi sistem'],
        'kaprodi'        => ['Kepala Program Studi', 'Penetapan target & persetujuan final'],
        'tim_gkm'        => ['Tim Gugus Kendali Mutu', 'Verifikasi capaian & audit mutu internal'],
        'tim_akreditasi' => ['Tim Akreditasi', 'Pengumpulan data, bukti, dan pengajuan capaian'],
    ],

    'roles' => [

        'administrator' => [
            'entitas' => array_fill_keys($master, 'crud') + array_fill_keys($referensi, 'crud') + [
                'dokumen_bukti'     => 'crud',
                'target_indikator'  => 'r',
                'capaian_indikator' => 'r',
                'verifikasi'        => 'r',
                'temuan_ami'        => 'r',
                'tindak_lanjut'     => 'r',
                'periode'           => 'crud',
                'users'             => 'crud',
                'roles'             => 'crud',
                'audit_log'         => 'r',
            ],
            'aksi' => ['hitung_capaian', 'kunci_periode', 'kelola_pengguna', 'mapping_perhitungan', 'import', 'export', 'restore', 'hard_delete'],
            'lingkup_log' => 'semua',
        ],

        'kaprodi' => [
            'entitas' => array_fill_keys($master, 'r') + array_fill_keys($referensi, 'r') + [
                'dokumen_bukti'     => 'r',
                'target_indikator'  => 'crud',
                'capaian_indikator' => 'r',
                'verifikasi'        => 'r',
                'temuan_ami'        => 'r',
                'tindak_lanjut'     => 'r',
                'periode'           => 'r',
                'audit_log'         => 'r',
            ],
            'aksi' => ['setujui_capaian', 'export'],
            'lingkup_log' => 'prodi',
        ],

        'tim_akreditasi' => [
            'entitas' => array_fill_keys($master, 'crud') + array_fill_keys($referensi, 'r') + [
                'dokumen_bukti'     => 'cru',
                'target_indikator'  => 'r',
                'capaian_indikator' => 'cru',
                'verifikasi'        => 'r',
                'temuan_ami'        => 'r',
                'tindak_lanjut'     => 'cru',
                'periode'           => 'r',
                'audit_log'         => 'r',
            ],
            'aksi' => ['hitung_capaian', 'input_manual_capaian', 'ajukan_capaian', 'import', 'export'],
            'lingkup_log' => 'sendiri',
        ],

        'tim_gkm' => [
            'entitas' => array_fill_keys($master, 'r') + array_fill_keys($referensi, 'r') + [
                'dokumen_bukti'     => 'r',
                'target_indikator'  => 'r',
                'capaian_indikator' => 'r',
                'verifikasi'        => 'cr',
                'temuan_ami'        => 'crud',
                'tindak_lanjut'     => 'r',
                'periode'           => 'r',
                'audit_log'         => 'r',
            ],
            'aksi' => ['verifikasi_capaian', 'tutup_temuan', 'export'],
            'lingkup_log' => 'semua',
        ],
    ],
];
