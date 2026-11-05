<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// dev.md §3.2 & §6 — pengguna & peran.
// Tabel `users` bawaan Laravel diperluas: `name` = nama lengkap, `password` = hash.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 100)->unique()->after('id');
            $table->unsignedBigInteger('pegawai_id')->nullable()->after('password'); // FK ditambah saat tabel pegawai dibuat
            $table->foreignId('prodi_id')->nullable()->after('pegawai_id')->constrained('program_studi');
            $table->boolean('is_aktif')->default(true)->after('prodi_id');
            $table->softDeletes();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prodi_id');
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'pegawai_id', 'is_aktif', 'deleted_at']);
        });
    }
};
