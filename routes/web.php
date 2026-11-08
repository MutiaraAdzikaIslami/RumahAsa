<?php

use App\Modules\Auth\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return view('dashboard.placeholder', ['user' => auth()->user()]);
})->name('dashboard');

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/up', fn () => response()->json(['status' => 'ok']));
