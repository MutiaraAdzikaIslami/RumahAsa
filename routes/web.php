<?php

use App\Modules\Auth\Http\Controllers\LoginController;
use App\Modules\Auth\Http\Controllers\PasswordController;
use App\Modules\Auth\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

// FR-01: Login routes (public)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// FR-02: Logout & session ping
Route::post('/logout', [SessionController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/session/ping', [SessionController::class, 'ping'])->middleware('auth')->name('session.ping');

// FR-03: Ganti password
Route::middleware('auth')->group(function () {
    Route::get('/password/change', [PasswordController::class, 'edit'])->name('password.change');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
});

// Dashboard + modul-modul (protected oleh must.change.pwd)
Route::middleware(['auth', 'must.change.pwd'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.placeholder', ['user' => auth()->user()]);
    })->name('dashboard');

    // scrum-17: Kelola Wilayah (FR-08) — admin only
    Route::middleware('role:manage-wilayah')->prefix('wilayah')->name('wilayah.')->group(function () {
        Route::get('/view', fn () => view('wilayah.index'))->name('view');
        require __DIR__.'/../app/Modules/Wilayah/routes.php';
    });

    // scrum-14: Kelola User (FR-05) — admin only
    Route::middleware('role:manage-users')->prefix('users')->name('users.')->group(function () {
        Route::get('/view', fn () => view('users.index'))->name('view');
        require __DIR__.'/../app/Modules/Users/routes.php';
    });

    // scrum-18: Audit Log (FR-09) — admin only
    Route::middleware('role:view-audit-log')->prefix('audit')->name('audit.')->group(function () {
        Route::get('/view', fn () => view('audit.index'))->name('view');
        require __DIR__.'/../app/Modules/Audit/routes.php';
    });

    // scrum-19: Rumah (FR-10, 12, 14)
    Route::prefix('rumah')->name('rumah.')->group(function () {
        require __DIR__.'/../app/Modules/Rumah/routes.php';
    });

    // scrum-20/21: Survei & Foto (FR-11a, FR-11b)
    Route::prefix('survei')->name('survei.')->group(function () {
        require __DIR__.'/../app/Modules/Survei/routes.php';
    });

    // FR-15: Review survei (Verifikator) — SCRUM-110 & SCRUM-111
    Route::middleware('role:access-verifikator')->prefix('verifikator')->name('verifikator.')->group(function () {
        require __DIR__.'/../app/Modules/Verifikator/routes.php';
    });
});

// Root redirect
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/up', fn () => response()->json(['status' => 'ok']));
