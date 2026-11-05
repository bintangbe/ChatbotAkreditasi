<?php

/*
|--------------------------------------------------------------------------
| SIAKRED — navigasi per peran & data referensi
|--------------------------------------------------------------------------
| Sidebar mengikuti markdowns/dev.md §6.1–6.4. Satu daftar menu dipakai
| bersama; 'roles' menentukan peran mana yang melihat item tsb. Item yang
| tidak cocok dengan peran pengguna TIDAK dirender sama sekali.
|
| Kunci item:
|   route, url  nama route & URL (halaman yang belum dibangun otomatis jadi
|               halaman "sedang disiapkan", lihat routes/web.php)
|   roles       daftar kode peran yang melihat item ini
|   icon        nama ikon di layouts/partials/icon.blade.php
|   badge       'verifikasi' | 'persetujuan' — angka antrean di samping menu
|   label       teks; boleh array [kode_peran => teks] bila berbeda per peran
| Grup dengan satu anak yang terlihat dirender sebagai tautan biasa.
*/

$ADM = 'administrator';
$KPR = 'kaprodi';
$TA  = 'tim_akreditasi';
$GKM = 'tim_gkm';
$SEMUA = [$ADM, $KPR, $TA, $GKM];

return [

    'menu' => [
        // Halaman awal: isinya berbeda per peran (PageController::beranda)
        ['label' => [$ADM => 'Status Sistem', $KPR => 'Dashboard', $TA => 'Beranda Kerja', $GKM => 'Antrean Verifikasi'],
         'icon' => 'home', 'route' => 'dashboard', 'url' => '/', 'roles' => $SEMUA,
         'badge_roles' => [$GKM => 'verifikasi']],

        ['label' => 'Capaian Indikator', 'icon' => 'chart', 'children' => [
            ['label' => 'Ringkasan per Standar', 'route' => 'capaian.index',     'url' => 'capaian',           'roles' => [$KPR, $TA, $GKM]],
            ['label' => 'Detail Indikator',      'route' => 'capaian.indikator', 'url' => 'capaian/indikator', 'roles' => [$KPR, $TA, $GKM]],
        ]],

        ['label' => 'Persetujuan', 'icon' => 'check', 'route' => 'persetujuan.index', 'url' => 'persetujuan', 'roles' => [$KPR], 'badge' => 'persetujuan'],

        ['label' => 'Temuan AMI', 'icon' => 'flag', 'children' => [
            ['label' => 'Daftar Temuan',         'route' => 'ami.temuan', 'url' => 'ami/temuan', 'roles' => [$KPR, $GKM]],
            ['label' => 'Pantau Tindak Lanjut',  'route' => 'ami.pantau', 'url' => 'ami/pantau', 'roles' => [$GKM]],
        ]],

        ['label' => 'Laporan',          'icon' => 'report', 'route' => 'laporan.index',  'url' => 'laporan',          'roles' => [$KPR]],
        ['label' => 'Laporan Evaluasi', 'icon' => 'report', 'route' => 'laporan.evaluasi', 'url' => 'laporan/evaluasi', 'roles' => [$GKM]],

        ['label' => 'Referensi Standar', 'icon' => 'book', 'children' => [
            ['label' => 'Daftar Standar',   'route' => 'referensi.standar',   'url' => 'referensi/standar',   'roles' => [$ADM]],
            ['label' => 'Daftar Indikator', 'route' => 'referensi.indikator', 'url' => 'referensi/indikator', 'roles' => [$ADM]],
        ]],

        ['label' => 'Master Data', 'icon' => 'database', 'children' => [
            ['label' => 'Program Studi',            'route' => 'master.prodi',      'url' => 'master/program-studi',      'roles' => [$ADM, $KPR, $GKM]],
            ['label' => 'Pegawai (Dosen & Tendik)', 'route' => 'master.pegawai',    'url' => 'master/pegawai',            'roles' => $SEMUA],
            ['label' => 'Mahasiswa & Lulusan',      'route' => 'master.mahasiswa',  'url' => 'master/mahasiswa',          'roles' => $SEMUA],
            ['label' => 'Kurikulum & Mata Kuliah',  'route' => 'master.kurikulum',  'url' => 'master/kurikulum',          'roles' => $SEMUA],
            ['label' => 'Kerjasama & Mitra',        'route' => 'master.kerjasama',  'url' => 'master/kerjasama',          'roles' => $SEMUA],
            ['label' => 'Kegiatan Mahasiswa',       'route' => 'master.kegiatan',   'url' => 'master/kegiatan-mahasiswa', 'roles' => $SEMUA],
            ['label' => 'Penelitian & Luaran',      'route' => 'master.penelitian', 'url' => 'master/penelitian',         'roles' => $SEMUA],
            ['label' => 'Sarana & Prasarana',       'route' => 'master.sarpras',    'url' => 'master/sarpras',            'roles' => $SEMUA],
            ['label' => 'Survei Kepuasan',          'route' => 'master.survei',     'url' => 'master/survei',             'roles' => $SEMUA],
            ['label' => 'Anggaran',                 'route' => 'master.anggaran',   'url' => 'master/anggaran',           'roles' => $SEMUA],
            ['label' => 'Akreditasi & Sertifikasi', 'route' => 'master.akreditasi', 'url' => 'master/akreditasi',         'roles' => $SEMUA],
            ['label' => 'Laporan PDDIKTI',          'route' => 'master.pddikti',    'url' => 'master/laporan-pddikti',    'roles' => $SEMUA],
        ]],

        ['label' => 'Dokumen Bukti',        'icon' => 'document', 'route' => 'dokumen.index',      'url' => 'dokumen',       'roles' => [$TA]],
        ['label' => 'Tindak Lanjut Temuan', 'icon' => 'flag',     'route' => 'ami.tindak-lanjut',  'url' => 'tindak-lanjut', 'roles' => [$TA]],
        ['label' => 'Import Data',          'icon' => 'upload',   'route' => 'import.index',       'url' => 'import',        'roles' => [$TA]],
        ['label' => 'Log Aktivitas',        'icon' => 'clock',    'route' => 'log.index',          'url' => 'log-aktivitas', 'roles' => [$GKM]],

        ['label' => 'Pengaturan', 'icon' => 'cog', 'children' => [
            ['label' => 'Periode',             'route' => 'pengaturan.periode',  'url' => 'pengaturan/periode',  'roles' => [$ADM]],
            ['label' => 'Pengguna & Peran',    'route' => 'pengaturan.pengguna', 'url' => 'pengaturan/pengguna', 'roles' => [$ADM]],
            ['label' => 'Mapping Perhitungan', 'route' => 'pengaturan.mapping',  'url' => 'pengaturan/mapping',  'roles' => [$ADM]],
            ['label' => 'Integrasi',           'route' => 'pengaturan.integrasi', 'url' => 'pengaturan/integrasi', 'roles' => [$ADM]],
            ['label' => 'Log Aktivitas',       'route' => 'log.index',           'url' => 'log-aktivitas',       'roles' => [$ADM]],
        ]],

        ['divider' => true],
        ['label' => 'Tanya Asisten AI', 'icon' => 'chat', 'route' => 'asisten-ai', 'url' => 'asisten-ai', 'roles' => $SEMUA, 'special' => true],
    ],

    // Halaman yang sudah punya tampilan sendiri (tidak dibuatkan route placeholder).
    'built' => ['dashboard', 'asisten-ai', 'referensi.standar', 'referensi.indikator', 'pengaturan.pengguna'],

    // Panel batasan di halaman Asisten AI (dev.md §8.4)
    'asisten' => [
        'jumlah_dokumen' => 10,
        'versi_standar'  => 'Standar 2020 Revisi 01',
    ],
];
