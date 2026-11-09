<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// dev.md §3.2 — audit trail
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('entitas', 100);                  // nama tabel
            $table->unsignedBigInteger('entitas_id');
            $table->enum('aksi', ['create', 'update', 'delete', 'restore', 'verify']);
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('created_at');

            $table->index(['entitas', 'entitas_id'], 'idx_entitas');
            $table->index('created_at', 'idx_waktu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
