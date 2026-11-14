<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Command custom untuk generate kode registrasi rumah
Artisan::command('rumahasa:kode-registrasi', function () {
    $kode = 'RUMAH-' . date('Y') . '-' . str_pad(\App\Models\RumahWarga::withTrashed()->count() + 1, 4, '0', STR_PAD_LEFT);
    $this->info($kode);
})->purpose('Generate kode registrasi rumah baru');
