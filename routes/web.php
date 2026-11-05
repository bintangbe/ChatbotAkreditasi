<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Pengaturan\PenggunaController;
use App\Http\Controllers\Referensi\IndikatorController;
use App\Http\Controllers\Referensi\StandarController;
use Illuminate\Support\Facades\Route;

// ===== Login =====
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:20,1');
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ===== Halaman yang butuh login =====
Route::middleware('auth')->group(function () {
    Route::get('/', [PageController::class, 'dashboard'])->name('dashboard');

    // Tanya Asisten AI. Mendukung ?q= untuk mengisi kotak input (dev.md §8.3)
    Route::get('/asisten-ai', [ChatController::class, 'index'])->name('asisten-ai');
    Route::post('/api/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    // PDF dokumen (hanya yang terdaftar di config/dokumen.php) untuk pratinjau & tautan halaman sumber
    Route::get('/api/chat/doc/{fileId}', [ChatController::class, 'document'])
        ->where('fileId', '[A-Za-z0-9_-]+')
        ->name('chat.doc');

    // Referensi Standar (read-only)
    Route::get('/referensi/standar', [StandarController::class, 'index'])
        ->middleware('akses:standar,r')->name('referensi.standar');
    Route::get('/referensi/indikator', [IndikatorController::class, 'index'])
        ->middleware('akses:indikator,r')->name('referensi.indikator');

    // Pengaturan
    Route::get('/pengaturan/pengguna', [PenggunaController::class, 'index'])
        ->middleware('peran:administrator')->name('pengaturan.pengguna');

    // Menu yang belum dibangun -> halaman "sedang disiapkan", dijaga per peran (dev.md §6).
    // Satu route bisa muncul di beberapa menu (mis. Log Aktivitas): peran digabung.
    $belum = [];
    foreach (config('siakred.menu') as $item) {
        foreach ($item['children'] ?? [$item] as $menu) {
            if (empty($menu['route']) || in_array($menu['route'], config('siakred.built'), true)) {
                continue;
            }
            $belum[$menu['route']] ??= [
                'url'   => $menu['url'],
                'judul' => $menu['label'],
                'grup'  => isset($item['children']) ? $item['label'] : null,
                'roles' => [],
            ];
            $belum[$menu['route']]['roles'] = array_unique([...$belum[$menu['route']]['roles'], ...$menu['roles']]);
        }
    }
    foreach ($belum as $nama => $r) {
        Route::get($r['url'], [PageController::class, 'placeholder'])
            ->name($nama)
            ->middleware('peran:' . implode(',', $r['roles']))
            ->defaults('judul', $r['judul'])
            ->defaults('grup', $r['grup']);
    }
});
