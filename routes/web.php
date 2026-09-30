<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

// Rute untuk menampilkan halaman chat (memanggil fungsi index)
Route::get('/', [ChatController::class, 'index'])->name('chat.index');

// Rute untuk mengirim pesan (memanggil fungsi sendMessage agar cocok dengan controller)
Route::post('/api/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');