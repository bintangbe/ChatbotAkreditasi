<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// dev.md §3.5 — master pegawai + relasi anak
return new class extends Migration
{
    private function jejak(Blueprint $table): void
    {
        $table->timestamps();
        $table->softDeletes();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
    }

    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->nullable()->unique();
            $table->string('nidn', 20)->nullable()->unique();
            $table->string('nama', 150);
            $table->enum('jenis', ['dosen', 'tendik']);
            $table->enum('status_kepegawaian', ['tetap', 'tidak_tetap', 'kontrak', 'tamu']);
            $table->foreignId('prodi_id')->nullable()->constrained('program_studi');
            $table->string('unit', 150)->nullable();
            $table->string('kelompok_keahlian', 150)->nullable();
            $table->string('warga_negara', 50)->nullable()->default('Indonesia');
            $table->string('email', 150)->nullable();
            $table->boolean('is_aktif')->default(true);
            $this->jejak($table);
        });

        Schema::create('pegawai_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai');
            $table->enum('jenjang', ['SMA', 'D3', 'S1', 'S2', 'S3']);
            $table->string('bidang', 150)->nullable();
            $table->string('institusi', 200)->nullable();
            $table->string('negara', 100)->nullable();
            $table->year('tahun_lulus')->nullable();
            $table->boolean('is_tertinggi')->default(false);
            $this->jejak($table);
        });

        Schema::create('pegawai_sertifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai');
            $table->enum('jenis', ['pendidik', 'profesi', 'keahlian', 'kompetensi']);
            $table->string('nama', 200);
            $table->string('lembaga', 200)->nullable();
            $table->enum('tingkat', ['nasional', 'internasional'])->nullable();
            $table->string('nomor', 100)->nullable();
            $table->date('tanggal_terbit')->nullable();
            $table->date('berlaku_sampai')->nullable();
            $this->jejak($table);
        });

        Schema::create('pegawai_jabatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai');
            $table->enum('jabatan', ['tenaga_pengajar', 'asisten_ahli', 'lektor', 'lektor_kepala', 'guru_besar']);
            $table->string('golongan', 20)->nullable();
            $table->string('nomor_sk', 100)->nullable();
            $table->date('tmt')->nullable();
            $table->boolean('is_aktif')->default(true);
            $this->jejak($table);
        });

        Schema::create('pegawai_rekognisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai');
            $table->foreignId('periode_id')->constrained('periode');
            $table->enum('jenis', ['keynote_speaker', 'invited_speaker', 'visiting_lecturer', 'reviewer', 'editor', 'penghargaan', 'lainnya']);
            $table->enum('tingkat', ['regional', 'nasional', 'internasional']);
            $table->string('judul', 255);
            $table->string('penyelenggara', 200)->nullable();
            $table->date('tanggal')->nullable();
            $this->jejak($table);
        });

        Schema::create('pegawai_penugasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai');
            $table->foreignId('periode_id')->constrained('periode');
            $table->enum('jenis', ['praktisi_industri', 'inbound_lectureship', 'outbound_lectureship',
                'faculty_exchange', 'visiting_professor', 'global_week', 'dosen_tamu']);
            $table->string('institusi', 200)->nullable();
            $table->string('negara', 100)->nullable();
            $table->unsignedBigInteger('mitra_id')->nullable(); // FK ke mitra ditambahkan saat tabel mitra dibuat (Tahap 4)
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_akhir')->nullable();
            $table->decimal('durasi_bulan', 5, 2)->nullable();
            $this->jejak($table);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('pegawai_id')->references('id')->on('pegawai');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
        });
        foreach (['pegawai_penugasan', 'pegawai_rekognisi', 'pegawai_jabatan', 'pegawai_sertifikasi', 'pegawai_pendidikan', 'pegawai'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
