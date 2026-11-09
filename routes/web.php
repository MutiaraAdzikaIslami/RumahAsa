<?php

use App\Modules\Auth\Http\Controllers\LoginController;
use App\Modules\Auth\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

// FR-01: Login routes (public)
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// FR-02: Logout & session ping (butuh auth)
Route::post('/logout',       [SessionController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/session/ping', [SessionController::class, 'ping'])->middleware('auth')->name('session.ping');

// Dashboard placeholder (akan diisi di scrum-38)
Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return view('dashboard.placeholder', ['user' => auth()->user()]);
})->name('dashboard');

// Root redirect
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Health check
Route::get('/up', fn () => response()->json(['status' => 'ok']));