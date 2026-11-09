<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// dev.md §3.4 — dokumen bukti (polimorfik)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_bukti', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 255);
            $table->string('jenis', 100)->nullable();       // 'SK','MoU','sertifikat','laporan','RPS',...
            $table->string('nomor_dokumen', 100)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('file_path', 255);
            $table->string('file_nama', 255)->nullable();    // nama file asli saat diunggah
            $table->string('file_mime', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->smallInteger('versi')->default(1);
            $table->foreignId('parent_id')->nullable()->constrained('dokumen_bukti'); // versi sebelumnya
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('dokumen_relasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('dokumen_bukti');
            $table->string('entitas', 100);                  // 'indikator','pegawai','penelitian',...
            $table->unsignedBigInteger('entitas_id');
            $table->foreignId('periode_id')->nullable()->constrained('periode');
            $table->index(['entitas', 'entitas_id'], 'idx_rel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_relasi');
        Schema::dropIfExists('dokumen_bukti');
    }
};
