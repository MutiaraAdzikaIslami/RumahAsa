<?php

use Illuminate\Support\Facades\Route;

/**
 * Routes untuk scrum-56-konfigurasi-database (FOUNDATION).
 *
 * Route auth/dashboard akan didaftarkan bertahap di branch berikutnya:
 *  - scrum-10-login: route /login, /logout
 *  - scrum-11-sesi-logout: route /session/ping
 *  - scrum-12-ganti-password: route /password/change, /password
 *  - scrum-13-kontrol-akses: route group dengan middleware auth
 *  - dst.
 *
 * Untuk scrum-56: hanya root route untuk verifikasi bootstrap Laravel jalan.
 */
Route::get('/', function () {
    return response()->json([
        'app'      => config('app.name'),
        'status'   => 'ok',
        'version'  => 'scrum-56',
        'message'  => 'Laravel RumahAsa foundation berhasil dimuat. Jalankan: php artisan migrate --seed',
    ]);
});

// Health check Laravel
Route::get('/up', fn () => response()->json(['status' => 'ok']));
