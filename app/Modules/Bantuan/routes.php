<?php

use App\Modules\Bantuan\Http\Controllers\BantuanController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BantuanController::class, 'index'])->name('index');
