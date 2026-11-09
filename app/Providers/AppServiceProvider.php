<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Modules\Core\Services\AuditLogger::class);
        $this->app->singleton(\App\Modules\Core\Services\NikEncryptor::class, function ($app) {
            return new \App\Modules\Core\Services\NikEncryptor(config('rumahasa.nik_hmac_key', 'default-key'));
        });
        $this->app->singleton(\App\Modules\Core\Services\FotoStorageService::class, function ($app) {
            return new \App\Modules\Core\Services\FotoStorageService(
                disk: config('rumahasa.foto_disk', 'private'),
                maxSize: config('rumahasa.foto_max_size', 5242880),
                maxPerKomponen: config('rumahasa.foto_max_per_komponen', 10),
            );
        });
        $this->app->singleton(\App\Modules\Workflow\Services\StatusWorkflow::class);
    }

    public function boot(): void
    {
        //
    }
}