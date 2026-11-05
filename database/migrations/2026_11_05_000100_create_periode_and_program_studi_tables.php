<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// dev.md §3.2 — referensi
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();            // '2025/2026-1', '2025'
            $table->string('nama', 100);
            $table->enum('jenis', ['semester', 'tahun_akademik', 'tahun_kalender']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir');
            $table->boolean('is_aktif')->default(false);
            $table->boolean('is_terkunci')->default(false);  // periode yang sudah dilaporkan
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('program_studi', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 150);
            $table->enum('jenjang', ['D3', 'D4', 'S1', 'S2', 'S3', 'Profesi']);
            $table->string('fakultas', 150)->nullable();
            $table->string('kampus', 100)->nullable();
            $table->boolean('kelas_internasional')->default(false);
            $table->boolean('pelaksana_mbkm')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_studi');
        Schema::dropIfExists('periode');
    }
};
