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
        ->middleware('akses:users,r')->name('pengaturan.pengguna');

    // Menu yang belum dibangun -> halaman "sedang disiapkan"
    $built = config('siakred.built');
    foreach (config('siakred.menu') as $item) {
        $items = isset($item['children'])
            ? array_map(fn ($c) => $c + ['grup' => $item['label']], $item['children'])
            : [$item];

        foreach ($items as $menu) {
            if (empty($menu['route']) || in_array($menu['route'], $built, true)) {
                continue;
            }
            $route = Route::get($menu['url'], [PageController::class, 'placeholder'])
                ->name($menu['route'])
                ->defaults('judul', $menu['label'])
                ->defaults('grup', $menu['grup'] ?? null);

            if (!empty($menu['entitas'])) {
                $route->middleware('akses:' . $menu['entitas'] . ',r');
            }
        }
    }
});
