<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsurePasswordIsChanged; 

// 1. Pengalihan Default
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Rute Guest (Belum Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// 3. Rute Auth (Sudah Login)
Route::middleware('auth')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/ganti-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/ganti-password', [AuthController::class, 'updatePassword'])->name('password.update');

    // 4. Rute Terlindungi Penuh (Harus sudah ganti password)
    Route::middleware(EnsurePasswordIsChanged::class)->group(function () {
        
        // KEMBALIKAN KE TAMPILAN BLADE ASLI
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
        
        // Resource Manajemen User Admin
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('users', UserController::class);
        });

    });
});