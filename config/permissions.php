<?php

/*
|--------------------------------------------------------------------------
| Matriks hak akses (dev.md §6)
|--------------------------------------------------------------------------
| Format: kode_role => [entitas => huruf aksi]
|   c = create, r = read, u = update, d = delete (soft delete)
| Entitas = nama tabel. Kunci '*' berlaku untuk semua entitas yang tidak
| disebut secara eksplisit. Dicek lewat User::bolehAkses() dan middleware
| `akses:entitas,aksi`.
*/

$masterAkademik = ['mahasiswa', 'lulusan', 'tracer_study', 'kurikulum', 'mata_kuliah', 'rps', 'penilaian_mk'];
$masterPenelitian = ['penelitian', 'luaran', 'roadmap_penelitian', 'inkubasi_startup', 'pemanfaatan_hasil', 'skor_sinta'];

return [

    // Daftar peran (diisi ke tabel `roles` oleh RoleSeeder)
    'daftar' => [
        'admin_spm'           => ['Admin SPM', 'Semua modul, kunci/buka periode, kelola standar & indikator'],
        'admin_prodi'         => ['Admin Prodi', 'Master data lingkup prodinya, ajukan capaian'],
        'operator_sdm'        => ['Operator SDM', 'Pegawai dan relasi anaknya'],
        'operator_akademik'   => ['Operator Akademik', 'Mahasiswa, lulusan, mata kuliah, RPS, kurikulum'],
        'operator_penelitian' => ['Operator Penelitian', 'Penelitian, luaran, roadmap, inkubasi'],
        'operator_keuangan'   => ['Operator Keuangan', 'Anggaran dan realisasi'],
        'operator_sarpras'    => ['Operator Sarpras', 'Sarana prasarana dan laboratorium'],
        'dosen'               => ['Dosen', 'Baca capaian; ubah RPS & penilaian MK yang diampu'],
        'verifikator'         => ['Verifikator', 'Verifikasi/tolak capaian, baca semua'],
        'pimpinan'            => ['Pimpinan', 'Baca semua, akses dashboard'],
    ],

    // Hak dasar setiap pengguna yang login
    'semua_pengguna' => [
        'standar'           => 'r',
        'indikator'         => 'r',
        'capaian_indikator' => 'r',
        'dokumen_bukti'     => 'r',
    ],

    'roles' => [
        'admin_spm' => ['*' => 'crud'],

        'admin_prodi' => [
            '*'                 => 'r',
            'users'             => '',
            'audit_log'         => '',
            'roles'             => '',
            'periode'           => 'r',
            'standar'           => 'r',
            'indikator'         => 'r',
            'capaian_indikator' => 'cru',
            'target_indikator'  => 'cru',
            'program_studi'     => 'ru',
            'pegawai'           => 'crud',
            'kerjasama'         => 'crud',
            'mitra'             => 'crud',
            'kegiatan_mahasiswa' => 'crud',
            'sarpras'           => 'crud',
            'survei'            => 'crud',
            'anggaran'          => 'crud',
            'akreditasi_sertifikasi' => 'crud',
            'laporan_pddikti'   => 'crud',
            'dokumen_bukti'     => 'crud',
        ] + array_fill_keys([...$masterAkademik, ...$masterPenelitian], 'crud'),

        'operator_sdm' => array_fill_keys(
            ['pegawai', 'pegawai_pendidikan', 'pegawai_sertifikasi', 'pegawai_jabatan', 'pegawai_rekognisi', 'pegawai_penugasan'],
            'crud'
        ) + ['dokumen_bukti' => 'cr'],

        'operator_akademik' => array_fill_keys($masterAkademik, 'crud') + ['dokumen_bukti' => 'cr'],

        'operator_penelitian' => array_fill_keys($masterPenelitian, 'crud') + ['dokumen_bukti' => 'cr'],

        'operator_keuangan' => ['anggaran' => 'crud', 'anggaran_realisasi' => 'crud', 'dokumen_bukti' => 'cr'],

        'operator_sarpras' => ['sarpras' => 'crud', 'laboratorium' => 'crud', 'dokumen_bukti' => 'cr'],

        // Pembatasan "hanya MK yang diampunya" diterapkan di controller RPS (Tahap 3)
        'dosen' => ['rps' => 'ru', 'penilaian_mk' => 'cru', 'dokumen_bukti' => 'cr'],

        'verifikator' => [
            '*'                 => 'r',
            'users'             => '',
            'verifikasi'        => 'cr',
            'capaian_indikator' => 'ru',
        ],

        'pimpinan' => ['*' => 'r'],
    ],
];
