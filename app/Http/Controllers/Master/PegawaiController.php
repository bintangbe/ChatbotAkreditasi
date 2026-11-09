<?php

namespace App\Http\Controllers\Master;

use App\Models\Pegawai;
use App\Models\PegawaiJabatan;
use App\Models\PegawaiPendidikan;
use App\Models\PegawaiPenugasan;
use App\Models\PegawaiRekognisi;
use App\Models\PegawaiSertifikasi;
use App\Models\Periode;
use App\Models\ProgramStudi;

class PegawaiController extends BaseCrudController
{
    protected string $model = Pegawai::class;
    protected string $entitas = 'pegawai';
    protected string $judul = 'Pegawai (Dosen & Tendik)';
    protected string $routeBase = 'master.pegawai';
    protected array $kolomTabel = ['nidn', 'nama', 'jenis', 'status_kepegawaian', 'unit'];
    protected array $kolomCari = ['nama', 'nidn', 'nip'];
    protected array $filter = ['jenis', 'status_kepegawaian', 'prodi_id'];

    protected array $fields = [
        'nama'   => ['label' => 'Nama lengkap', 'type' => 'text', 'rules' => 'required|string|max:150'],
        'nip'    => ['label' => 'NIP', 'type' => 'text', 'rules' => 'nullable|string|max:30|unique:pegawai,nip,{id}'],
        'nidn'   => ['label' => 'NIDN', 'type' => 'text', 'rules' => 'nullable|string|max:20|unique:pegawai,nidn,{id}'],
        'jenis'  => ['label' => 'Jenis', 'type' => 'select', 'rules' => 'required', 'options' => ['dosen' => 'Dosen', 'tendik' => 'Tenaga kependidikan']],
        'status_kepegawaian' => ['label' => 'Status kepegawaian', 'type' => 'select', 'rules' => 'required',
            'options' => ['tetap' => 'Tetap', 'tidak_tetap' => 'Tidak tetap', 'kontrak' => 'Kontrak', 'tamu' => 'Tamu']],
        'prodi_id' => ['label' => 'Program studi', 'type' => 'select', 'rules' => 'nullable', 'relasi' => [ProgramStudi::class, 'nama']],
        'unit'     => ['label' => 'Unit', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
        'kelompok_keahlian' => ['label' => 'Kelompok keahlian', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
        'warga_negara' => ['label' => 'Warga negara', 'type' => 'text', 'rules' => 'nullable|string|max:50', 'bantuan' => 'Dipakai untuk indikator dosen asing'],
        'email'    => ['label' => 'Email', 'type' => 'email', 'rules' => 'nullable|email|max:150'],
        'is_aktif' => ['label' => 'Masih aktif', 'type' => 'boolean'],
    ];

    public function __construct()
    {
        $periode = ['label' => 'Periode', 'type' => 'select', 'rules' => 'required', 'relasi' => [Periode::class, 'kode']];

        $this->relasiAnak = [
            'pendidikan' => [
                'judul' => 'Pendidikan', 'model' => PegawaiPendidikan::class, 'entitas' => 'pegawai_pendidikan', 'fk' => 'pegawai_id',
                'kolom' => ['jenjang', 'bidang', 'institusi', 'tahun_lulus', 'is_tertinggi'],
                'fields' => [
                    'jenjang'     => ['label' => 'Jenjang', 'type' => 'select', 'rules' => 'required', 'options' => ['SMA' => 'SMA', 'D3' => 'D3', 'S1' => 'S1', 'S2' => 'S2', 'S3' => 'S3']],
                    'bidang'      => ['label' => 'Bidang ilmu', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
                    'institusi'   => ['label' => 'Institusi', 'type' => 'text', 'rules' => 'nullable|string|max:200'],
                    'negara'      => ['label' => 'Negara', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
                    'tahun_lulus' => ['label' => 'Tahun lulus', 'type' => 'year', 'rules' => 'nullable|integer|min:1950|max:2100'],
                    'is_tertinggi' => ['label' => 'Pendidikan tertinggi', 'type' => 'boolean', 'bantuan' => 'Dipakai indikator 1.5 no.4 (dosen S3) & no.5'],
                ],
            ],
            'sertifikasi' => [
                'judul' => 'Sertifikasi', 'model' => PegawaiSertifikasi::class, 'entitas' => 'pegawai_sertifikasi', 'fk' => 'pegawai_id',
                'kolom' => ['jenis', 'nama', 'lembaga', 'tingkat', 'berlaku_sampai'],
                'fields' => [
                    'jenis'   => ['label' => 'Jenis', 'type' => 'select', 'rules' => 'required',
                                  'options' => ['pendidik' => 'Pendidik (serdos)', 'profesi' => 'Profesi', 'keahlian' => 'Keahlian', 'kompetensi' => 'Kompetensi']],
                    'nama'    => ['label' => 'Nama sertifikasi', 'type' => 'text', 'rules' => 'required|string|max:200'],
                    'lembaga' => ['label' => 'Lembaga penerbit', 'type' => 'text', 'rules' => 'nullable|string|max:200'],
                    'tingkat' => ['label' => 'Tingkat', 'type' => 'select', 'rules' => 'nullable', 'options' => ['nasional' => 'Nasional', 'internasional' => 'Internasional']],
                    'nomor'   => ['label' => 'Nomor sertifikat', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
                    'tanggal_terbit' => ['label' => 'Tanggal terbit', 'type' => 'date', 'rules' => 'nullable|date'],
                    'berlaku_sampai' => ['label' => 'Berlaku sampai', 'type' => 'date', 'rules' => 'nullable|date|after_or_equal:tanggal_terbit'],
                ],
            ],
            'jabatan' => [
                'judul' => 'Jabatan fungsional', 'model' => PegawaiJabatan::class, 'entitas' => 'pegawai_jabatan', 'fk' => 'pegawai_id',
                'kolom' => ['jabatan', 'golongan', 'nomor_sk', 'tmt', 'is_aktif'],
                'fields' => [
                    'jabatan'  => ['label' => 'Jabatan', 'type' => 'select', 'rules' => 'required',
                                   'options' => ['tenaga_pengajar' => 'Tenaga pengajar', 'asisten_ahli' => 'Asisten ahli', 'lektor' => 'Lektor', 'lektor_kepala' => 'Lektor kepala', 'guru_besar' => 'Guru besar']],
                    'golongan' => ['label' => 'Golongan', 'type' => 'text', 'rules' => 'nullable|string|max:20'],
                    'nomor_sk' => ['label' => 'Nomor SK', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
                    'tmt'      => ['label' => 'TMT', 'type' => 'date', 'rules' => 'nullable|date'],
                    'is_aktif' => ['label' => 'Jabatan aktif saat ini', 'type' => 'boolean'],
                ],
            ],
            'rekognisi' => [
                'judul' => 'Rekognisi', 'model' => PegawaiRekognisi::class, 'entitas' => 'pegawai_rekognisi', 'fk' => 'pegawai_id',
                'kolom' => ['periode_id', 'jenis', 'tingkat', 'judul', 'tanggal'],
                'fields' => [
                    'periode_id' => $periode,
                    'jenis'   => ['label' => 'Jenis', 'type' => 'select', 'rules' => 'required',
                                  'options' => ['keynote_speaker' => 'Keynote speaker', 'invited_speaker' => 'Invited speaker', 'visiting_lecturer' => 'Visiting lecturer',
                                                'reviewer' => 'Reviewer', 'editor' => 'Editor', 'penghargaan' => 'Penghargaan', 'lainnya' => 'Lainnya']],
                    'tingkat' => ['label' => 'Tingkat', 'type' => 'select', 'rules' => 'required', 'options' => ['regional' => 'Regional', 'nasional' => 'Nasional', 'internasional' => 'Internasional']],
                    'judul'   => ['label' => 'Judul / nama kegiatan', 'type' => 'text', 'rules' => 'required|string|max:255'],
                    'penyelenggara' => ['label' => 'Penyelenggara', 'type' => 'text', 'rules' => 'nullable|string|max:200'],
                    'tanggal' => ['label' => 'Tanggal', 'type' => 'date', 'rules' => 'nullable|date'],
                ],
            ],
            'penugasan' => [
                'judul' => 'Penugasan', 'model' => PegawaiPenugasan::class, 'entitas' => 'pegawai_penugasan', 'fk' => 'pegawai_id',
                'kolom' => ['periode_id', 'jenis', 'institusi', 'negara', 'durasi_bulan'],
                'fields' => [
                    'periode_id' => $periode,
                    'jenis'   => ['label' => 'Jenis', 'type' => 'select', 'rules' => 'required',
                                  'options' => ['praktisi_industri' => 'Praktisi di industri', 'inbound_lectureship' => 'Inbound lectureship', 'outbound_lectureship' => 'Outbound lectureship',
                                                'faculty_exchange' => 'Faculty exchange', 'visiting_professor' => 'Visiting professor', 'global_week' => 'Global week', 'dosen_tamu' => 'Dosen tamu']],
                    'institusi' => ['label' => 'Institusi', 'type' => 'text', 'rules' => 'nullable|string|max:200'],
                    'negara'    => ['label' => 'Negara', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
                    'tanggal_mulai' => ['label' => 'Tanggal mulai', 'type' => 'date', 'rules' => 'nullable|date'],
                    'tanggal_akhir' => ['label' => 'Tanggal akhir', 'type' => 'date', 'rules' => 'nullable|date|after_or_equal:tanggal_mulai'],
                    'durasi_bulan'  => ['label' => 'Durasi (bulan)', 'type' => 'decimal', 'rules' => 'nullable|numeric|min:0|max:999',
                                        'bantuan' => 'Indikator 1.5 no.12 mensyaratkan praktisi industri minimal 6 bulan'],
                ],
            ],
        ];
    }
}
