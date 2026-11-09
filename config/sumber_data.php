<?php

/*
|--------------------------------------------------------------------------
| Peta sumber_data (resolver) -> tabel master yang dibacanya
|--------------------------------------------------------------------------
| Dipakai untuk (dev.md §4.3):
|  - panel "Indikator terdampak" di halaman detail master
|  - menandai capaian_indikator.dihitung_pada = NULL setiap kali tabel
|    master berubah, supaya capaian tsb. dihitung ulang
| Lengkapi bersama resolvernya (Tahap 6). Mapping Perhitungan (admin) nanti
| membaca/menulis peta ini.
*/

$pegawai = ['pegawai'];

return [
    // Pegawai (Standar 1.5)
    'dosen_s3'                      => [...$pegawai, 'pegawai_pendidikan'],
    'tendik_s1_s2'                  => [...$pegawai, 'pegawai_pendidikan'],
    'dosen_serdik'                  => [...$pegawai, 'pegawai_sertifikasi'],
    'dosen_sertifikasi_profesi'     => [...$pegawai, 'pegawai_sertifikasi'],
    'tendik_bersertifikasi'         => [...$pegawai, 'pegawai_sertifikasi'],
    'dosen_jabatan_lektor_plus'     => [...$pegawai, 'pegawai_jabatan'],
    'rekognisi_dosen'               => [...$pegawai, 'pegawai_rekognisi'],
    'lecture_exchange'              => [...$pegawai, 'pegawai_penugasan'],
    'dosen_praktisi_industri'       => [...$pegawai, 'pegawai_penugasan'],
    'dosen_tamu_industri'           => [...$pegawai, 'pegawai_penugasan'],
    'dosen_asing_inbound'           => [...$pegawai, 'pegawai_penugasan'],
    'dosen_outbound'                => [...$pegawai, 'pegawai_penugasan'],
    'persentase_dosen_tidak_tetap'  => $pegawai,
    'laboran_per_lab'               => ['laboratorium'],
    'sitasi_per_dosen'              => [...$pegawai, 'skor_sinta'],

    // Program studi
    'prodi_kelas_internasional'     => ['program_studi'],
    'prodi_mbkm'                    => ['program_studi'],
    'prodi_akreditasi_internasional' => ['program_studi', 'akreditasi_sertifikasi'],
    'prodi_akreditasi_unggul'       => ['program_studi', 'akreditasi_sertifikasi'],

    // Mahasiswa, lulusan, tracer
    'keberhasilan_studi'            => ['mahasiswa', 'lulusan'],
    'rerata_ipk_lulusan'            => ['lulusan'],
    'rerata_eprt'                   => ['lulusan'],
    'rerata_tak'                    => ['lulusan'],
    'lulus_tepat_waktu'             => ['lulusan'],
    'peminat_fast_track'            => ['mahasiswa'],
    'lulusan_kerja_luar_negeri'     => ['lulusan', 'tracer_study'],
    'lulusan_wirausaha_berizin'     => ['lulusan', 'tracer_study'],
    'lulusan_pendiri_startup'       => ['lulusan', 'tracer_study'],

    // Kurikulum & pembelajaran
    'kelengkapan_plo_clo'           => ['mata_kuliah', 'plo', 'clo', 'plo_clo_map'],
    'kelengkapan_clo_mk'            => ['mata_kuliah', 'clo'],
    'rps_terstandar'                => ['mata_kuliah', 'rps'],
    'kesesuaian_rps'                => ['rps', 'rps_pertemuan'],
    'kesesuaian_penilaian_rps'      => ['rps', 'penilaian_mk', 'clo'],
    'mk_diajar_praktisi'            => ['mata_kuliah', 'mk_pengampu'],
    'mk_tersertifikasi'             => ['mata_kuliah'],

    // Kerjasama & kegiatan mahasiswa
    'mitra_penyerap_lulusan'        => ['mitra'],
    'kerjasama_pendidikan_pt_ln'    => ['kerjasama', 'mitra'],
    'kerjasama_pendidikan_non_pt'   => ['kerjasama', 'mitra'],
    'program_joint_degree'          => ['kerjasama'],
    'mhs_outbound_exchange'         => ['kegiatan_mahasiswa'],
    'mhs_short_program'             => ['kegiatan_mahasiswa'],
    'mhs_mbkm'                      => ['kegiatan_mahasiswa'],
    'kelulusan_mbkm'                => ['kegiatan_mahasiswa'],

    // Penelitian & luaran
    'luaran_hak_cipta'              => ['luaran'],
    'luaran_paten'                  => ['luaran'],
    'luaran_desain_industri'        => ['luaran'],
    'luaran_produk_inovasi'         => ['luaran'],
    'publikasi_internasional'       => ['luaran'],
    'publikasi_jurnal_sinta'        => ['luaran'],
    'publikasi_kolaborasi_internasional' => ['luaran'],
    'publikasi_konferensi_internasional' => ['luaran'],
    'publikasi_mitra_qs'            => ['luaran', 'mitra'],
    'penghargaan_riset_internasional' => ['luaran'],
    'skor_sinta_universitas'        => ['skor_sinta'],
    'penelitian_per_klasifikasi'    => ['penelitian'],
    'kesesuaian_roadmap'            => ['penelitian', 'roadmap_penelitian'],
    'inkubasi_startup'              => ['inkubasi_startup'],
    'hasil_riset_dimanfaatkan'      => ['pemanfaatan_hasil'],
    'pemanfaatan_penelitian_pembelajaran' => ['pemanfaatan_hasil'],
    'pemanfaatan_pkm_pembelajaran'  => ['pemanfaatan_hasil'],

    // Sarpras, survei, anggaran, PDDIKTI
    'sarpras_aksesibel'             => ['sarpras'],
    'mitra_hibah_peralatan'         => ['sarpras', 'mitra'],
    'index_kepuasan'                => ['survei', 'survei_item', 'survei_respon'],
    'non_tuition_fee_pembelajaran'  => ['anggaran'],
    'anggaran_per_mahasiswa'        => ['anggaran', 'anggaran_realisasi', 'mahasiswa'],
    'kelengkapan_pddikti'           => ['laporan_pddikti'],
];
