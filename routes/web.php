<?php

use App\Modules\Auth\Http\Controllers\LoginController;
use App\Modules\Auth\Http\Controllers\PasswordController;
use App\Modules\Auth\Http\Controllers\SessionController;
use App\Modules\Rumah\Http\Controllers\RumahController;
use App\Modules\Survei\Http\Controllers\SurveiController;
use App\Modules\Users\Http\Controllers\RoleController;
use App\Modules\Users\Http\Controllers\UserController;
use App\Modules\Users\Http\Controllers\UserFormDataController;
use App\Modules\Wilayah\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;

// 1. GUEST ROUTES
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

// 2. AUTHENTICATED ROUTES
Route::middleware('auth')->group(function () {

    Route::post('/logout', [SessionController::class, 'logout'])->name('logout');

    Route::match(['get', 'post'], '/session/ping', function () {
        return response()->json(['ok' => true, 'ts' => now()->toIso8601String()]);
    })->name('session.ping');

    Route::get('/password/change', [PasswordController::class, 'edit'])->name('password.change');
    Route::put('/password',        [PasswordController::class, 'update'])->name('password.update');

    // 3. ROUTES YANG BUTUH PASSWORD SUDAH DIGANTI
    Route::middleware('must.change.pwd')->group(function () {

        Route::get('/dashboard', function () {
            return view('dashboard.placeholder');
        })->name('dashboard')->middleware('can:view-dashboard');

        Route::get('/', function () {
            return redirect()->route('dashboard');
        })->name('home');

        // 4. ADMIN ROUTES
        Route::middleware('role:manage-users')->group(function () {

            // FR-05 & FR-06: User Management & Hydrate Endpoints
            Route::get('users/form-data', [UserFormDataController::class, 'index'])->name('users.form-data');
            Route::get('users/roles',     [RoleController::class, 'index'])->name('users.roles');
            Route::resource('users', UserController::class);
            Route::match(['put', 'post'], 'users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
            Route::match(['put', 'patch'], 'users/{user}/toggle-aktif',   [UserController::class, 'toggleAktif'])->name('users.toggle-aktif');
            Route::post('users/restore',                                  [UserController::class, 'restore'])->name('users.restore');
            Route::post('users/{userId}/restore',                         [UserController::class, 'restore'])->name('users.restore.id');

            // FR-08: Wilayah Management
            Route::prefix('wilayah')->name('wilayah.')->group(
                base_path('app/Modules/Wilayah/routes.php')
            );

            // FR-09: Audit Log
            Route::prefix('audit-log')->name('audit-log.')->group(
                base_path('app/Modules/Audit/routes.php')
            );

            // API endpoints untuk cascading dropdown wilayah
            Route::get('api/wilayah/kabupaten',                          [WilayahController::class, 'getKabupaten'])->name('api.wilayah.kabupaten');
            Route::get('api/wilayah/kabupaten/{kabupatenId}/kecamatan', [WilayahController::class, 'getKecamatan'])->name('api.wilayah.kecamatan');
            Route::get('api/wilayah/kecamatan/{kecamatanId}/kelurahan', [WilayahController::class, 'getKelurahan'])->name('api.wilayah.kelurahan');

            // FR-07: Rumah Management
            Route::resource('rumah', RumahController::class);
        });

        // 5. SURVEYOR ROUTES
        Route::middleware('role:access-surveyor')->name('survei.')->group(function () {
            Route::get('survei',                 [SurveiController::class, 'index'])->name('index');
            Route::get('survei/{rumah}/create',  [SurveiController::class, 'create'])->name('create');
            Route::post('survei/{rumah}',        [SurveiController::class, 'store'])->name('store');
        });
    });
});