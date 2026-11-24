<?php

use App\Modules\Auth\Http\Controllers\LoginController;
use App\Modules\Auth\Http\Controllers\PasswordController;
use App\Modules\Auth\Http\Controllers\SessionController;
use App\Modules\Dashboard\Http\Controllers\DashboardController;
use App\Modules\Heatmap\Http\Controllers\HeatmapController;
use App\Modules\Wilayah\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;

// FR-01: Login routes (public)
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// FR-02: Logout & session ping
Route::post('/logout',       [SessionController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/session/ping', [SessionController::class, 'ping'])->middleware('auth')->name('session.ping');

// FR-03: Ganti password
Route::middleware('auth')->group(function () {
    Route::get('/password/change', [PasswordController::class, 'edit'])->name('password.change');
    Route::put('/password',         [PasswordController::class, 'update'])->name('password.update');
});

// Dashboard + modul-modul (protected oleh must.change.pwd)
Route::middleware(['auth', 'must.change.pwd'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // FR-31: Heatmap renovasi (Verifikator, Surveyor, Pimpinan) — SCRUM-159 s.d. SCRUM-163
    Route::get('/heatmap/data', [HeatmapController::class, 'data'])->name('heatmap.data');

    // API Internal Cascading Wilayah (terstandar di dalam grup auth + must.change.pwd)
    Route::prefix('api/wilayah')->name('api.wilayah.')->group(function () {
        Route::get('/kabupaten', [WilayahController::class, 'getKabupaten'])
            ->name('kabupaten');
        Route::get('/kabupaten/{kabupatenId}/kecamatan', [WilayahController::class, 'getKecamatan'])
            ->whereNumber('kabupatenId')
            ->name('kecamatan');
        Route::get('/kecamatan/{kecamatanId}/kelurahan', [WilayahController::class, 'getKelurahan'])
            ->whereNumber('kecamatanId')
            ->name('kelurahan');
    });

    // scrum-17: Kelola Wilayah (FR-08) — admin only
    Route::middleware('role:manage-wilayah')->prefix('wilayah')->name('wilayah.')->group(function () {
        // FIX: Arahkan ke WilayahController@index supaya pagination & filter jalan
        Route::get('/view', [WilayahController::class, 'index'])->name('view');
        require __DIR__ . '/../app/Modules/Wilayah/routes.php';
    });

    // scrum-14: Kelola User (FR-05) — admin only
    Route::middleware('role:manage-users')->prefix('users')->name('users.')->group(function () {
        Route::get('/view', [\App\Modules\Users\Http\Controllers\UserController::class, 'index'])->name('view');
        require __DIR__ . '/../app/Modules/Users/routes.php';
    });

    // scrum-18: Audit Log (FR-09) — admin only
    Route::middleware('role:view-audit-log')->prefix('audit')->name('audit.')->group(function () {
        Route::get('/view', fn () => view('audit.index'))->name('view');
        require __DIR__ . '/../app/Modules/Audit/routes.php';
    });

    // scrum-19: Rumah (FR-10, 12, 14)
    Route::prefix('rumah')->name('rumah.')->group(function () {
        require __DIR__ . '/../app/Modules/Rumah/routes.php';
    });

    // API wilayah children: digunakan form input rumah (cascading dropdown) — semua role bisa akses
    Route::get('/wilayah/{wilayah}/children', [WilayahController::class, 'children'])->name('wilayah.children');

    // scrum-20/21: Survei & Foto (FR-11a, FR-11b)
    Route::prefix('survei')->name('survei.')->group(function () {
        require __DIR__ . '/../app/Modules/Survei/routes.php';
    });

    // FR-15: Review survei (Verifikator) — SCRUM-110 & SCRUM-111
    Route::middleware('role:verifikator')->prefix('verifikator')->name('verifikator.')->group(function () {
        require __DIR__ . '/../app/Modules/Verifikator/routes.php';
    });
});

// Root redirect
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/up', fn () => response()->json(['status' => 'ok']));

// scrum-32 s.d. 36: Bantuan dan renovasi (Verifikator, Surveyor, Admin, Pimpinan)
Route::middleware(['auth', 'must.change.pwd', 'role:verifikator|surveyor|admin|pimpinan'])
    ->prefix('bantuan')->name('bantuan.')
    ->group(function () {
        require __DIR__ . '/../app/Modules/Bantuan/routes.php';
    });
