<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// dev.md §3.3 — lapis pengukuran (standar, indikator, target, capaian, verifikasi)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standar', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20);                       // '1.1', '2.1'
            $table->string('nomor_dokumen', 100)->nullable(); // 'Tel_U-UT-REK-SPM-SN-1.1'
            $table->string('nama', 200);
            $table->enum('klaster', ['pendidikan', 'penelitian', 'pkm', 'lainnya']);
            $table->year('tahun_standar');
            $table->string('revisi', 10)->nullable();
            $table->date('tanggal_sah')->nullable();
            $table->string('file_path', 255)->nullable();     // nama dokumen di config/dokumen.php
            $table->boolean('is_berlaku')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['kode', 'tahun_standar', 'revisi'], 'uq_standar');
        });

        Schema::create('indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standar_id')->constrained('standar');
            $table->smallInteger('nomor');                    // nomor butir di seksi 2.3
            $table->text('pernyataan');
            // A: ketersediaan dokumen | B: hitungan/tren | C: rasio/rata-rata
            // D: index kepuasan       | E: kesesuaian/mapping
            $table->enum('pola', ['A', 'B', 'C', 'D', 'E']);
            $table->string('satuan', 50)->nullable();
            $table->enum('arah', ['naik', 'turun', 'tetap'])->default('naik');
            $table->string('sumber_data', 100)->nullable();   // nama resolver (§7)
            $table->text('formula')->nullable();
            $table->enum('level_tanggung_jawab', ['universitas', 'fakultas', 'prodi', 'dosen', 'direktorat']);
            $table->boolean('is_ruang_lingkup_prodi')->default(true);
            $table->smallInteger('halaman_sumber')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['standar_id', 'nomor'], 'uq_indikator');
        });

        Schema::create('target_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('indikator');
            $table->foreignId('periode_id')->constrained('periode');
            $table->foreignId('prodi_id')->nullable()->constrained('program_studi');
            $table->decimal('nilai_target', 15, 2);
            $table->decimal('nilai_baseline', 15, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['indikator_id', 'periode_id', 'prodi_id'], 'uq_target');
        });

        Schema::create('capaian_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('indikator');
            $table->foreignId('periode_id')->constrained('periode');
            $table->foreignId('prodi_id')->nullable()->constrained('program_studi');
            $table->decimal('nilai', 15, 2)->nullable();
            $table->enum('metode', ['otomatis', 'manual']);
            $table->json('rincian')->nullable();              // id baris master penyusun angka (drill-down)
            $table->text('catatan')->nullable();
            $table->dateTime('dihitung_pada')->nullable();
            $table->enum('status', ['draft', 'diajukan', 'terverifikasi', 'ditolak'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['indikator_id', 'periode_id', 'prodi_id'], 'uq_capaian');
        });

        Schema::create('verifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capaian_id')->constrained('capaian_indikator');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ['terverifikasi', 'ditolak']);
            $table->text('catatan')->nullable();
            $table->dateTime('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi');
        Schema::dropIfExists('capaian_indikator');
        Schema::dropIfExists('target_indikator');
        Schema::dropIfExists('indikator');
        Schema::dropIfExists('standar');
    }
};
