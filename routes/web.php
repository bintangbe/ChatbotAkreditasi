<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

// Rute untuk menampilkan halaman chat (memanggil fungsi index)
Route::get('/', [ChatController::class, 'index'])->name('chat.index');

// Rute untuk mengirim pesan (memanggil fungsi sendMessage agar cocok dengan controller)
Route::post('/api/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');

// Menyajikan PDF dokumen (hanya yang terdaftar di config/dokumen.php) untuk pratinjau halaman
Route::get('/api/chat/doc/{fileId}', [ChatController::class, 'document'])
    ->where('fileId', '[A-Za-z0-9_-]+')
    ->name('chat.doc');