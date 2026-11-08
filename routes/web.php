<?php

use App\Modules\Auth\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

// FR-01: Login routes (public)
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// FR-02: Logout placeholder (akan diisi di scrum-11)
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

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
