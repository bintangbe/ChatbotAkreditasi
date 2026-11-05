<?php

/*
|--------------------------------------------------------------------------
| SIAKRED — navigasi & data referensi
|--------------------------------------------------------------------------
| Struktur sidebar mengikuti markdowns/dev.md §2. Setiap item punya
| 'route' (nama route) dan 'url'. Halaman yang belum dibangun otomatis
| dirender sebagai halaman "sedang disiapkan" oleh PageController.
| 'icon' merujuk ke nama ikon di layouts/partials/icon.blade.php.
| 'entitas' = tabel yang dicek hak baca-nya (config/permissions.php);
| menu tanpa 'entitas' tampil untuk semua pengguna yang login.
*/

return [

    'menu' => [
        ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'dashboard', 'url' => '/'],

        ['label' => 'Capaian Indikator', 'icon' => 'chart', 'children' => [
            ['label' => 'Ringkasan per Standar', 'route' => 'capaian.index',     'url' => 'capaian', 'entitas' => 'capaian_indikator'],
            ['label' => 'Detail Indikator',      'route' => 'capaian.indikator', 'url' => 'capaian/indikator', 'entitas' => 'capaian_indikator'],
        ]],

        ['label' => 'Master Data', 'icon' => 'database', 'children' => [
            ['label' => 'Program Studi',            'route' => 'master.prodi',      'url' => 'master/program-studi', 'entitas' => 'program_studi'],
            ['label' => 'Pegawai (Dosen & Tendik)', 'route' => 'master.pegawai',    'url' => 'master/pegawai', 'entitas' => 'pegawai'],
            ['label' => 'Mahasiswa & Lulusan',      'route' => 'master.mahasiswa',  'url' => 'master/mahasiswa', 'entitas' => 'mahasiswa'],
            ['label' => 'Kurikulum & Mata Kuliah',  'route' => 'master.kurikulum',  'url' => 'master/kurikulum', 'entitas' => 'kurikulum'],
            ['label' => 'Kerjasama & Mitra',        'route' => 'master.kerjasama',  'url' => 'master/kerjasama', 'entitas' => 'kerjasama'],
            ['label' => 'Kegiatan Mahasiswa',       'route' => 'master.kegiatan',   'url' => 'master/kegiatan-mahasiswa', 'entitas' => 'kegiatan_mahasiswa'],
            ['label' => 'Penelitian & Luaran',      'route' => 'master.penelitian', 'url' => 'master/penelitian', 'entitas' => 'penelitian'],
            ['label' => 'Sarana & Prasarana',       'route' => 'master.sarpras',    'url' => 'master/sarpras', 'entitas' => 'sarpras'],
            ['label' => 'Survei Kepuasan',          'route' => 'master.survei',     'url' => 'master/survei', 'entitas' => 'survei'],
            ['label' => 'Anggaran',                 'route' => 'master.anggaran',   'url' => 'master/anggaran', 'entitas' => 'anggaran'],
            ['label' => 'Akreditasi & Sertifikasi', 'route' => 'master.akreditasi', 'url' => 'master/akreditasi', 'entitas' => 'akreditasi_sertifikasi'],
        ]],

        ['label' => 'Dokumen Bukti', 'icon' => 'document', 'route' => 'dokumen.index', 'url' => 'dokumen', 'entitas' => 'dokumen_bukti'],

        ['label' => 'Referensi Standar', 'icon' => 'book', 'children' => [
            ['label' => 'Daftar Standar',   'route' => 'referensi.standar',   'url' => 'referensi/standar', 'entitas' => 'standar'],
            ['label' => 'Daftar Indikator', 'route' => 'referensi.indikator', 'url' => 'referensi/indikator', 'entitas' => 'indikator'],
        ]],

        ['divider' => true],
        ['label' => 'Tanya Asisten AI', 'icon' => 'chat', 'route' => 'asisten-ai', 'url' => 'asisten-ai', 'special' => true],
        ['divider' => true],

        ['label' => 'Pengaturan', 'icon' => 'cog', 'children' => [
            ['label' => 'Periode',            'route' => 'pengaturan.periode',   'url' => 'pengaturan/periode', 'entitas' => 'periode'],
            ['label' => 'Pengguna & Peran',   'route' => 'pengaturan.pengguna',  'url' => 'pengaturan/pengguna', 'entitas' => 'users'],
            ['label' => 'Log Aktivitas',      'route' => 'pengaturan.log',       'url' => 'pengaturan/log', 'entitas' => 'audit_log'],
        ]],
    ],

    // Halaman yang sudah punya tampilan sendiri (tidak dibuatkan route placeholder).
    'built' => ['dashboard', 'asisten-ai', 'referensi.standar', 'referensi.indikator', 'pengaturan.pengguna'],


    // Panel batasan di halaman Asisten AI (dev.md §8.4)
    'asisten' => [
        'jumlah_dokumen' => 10,
        'versi_standar'  => 'Standar 2020 Revisi 01',
    ],
];
