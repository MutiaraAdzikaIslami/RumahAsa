<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/**
 * Bootstrap utama Laravel 11 RumahAsa — versi scrum-56 (FOUNDATION).
 *
 * Middleware aliases & web group extensions ditambahkan bertahap
 * sesuai branch Scrum:
 *  - scrum-13-kontrol-akses: 'role' middleware alias
 *  - scrum-11-sesi-logout: 'session.timeout' middleware + append to web group
 *  - scrum-12-ganti-password: 'must.change.pwd' middleware alias
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Sengaja kosong di scrum-56.
        // Middleware aliases akan didaftarkan di branch yang relevan.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
