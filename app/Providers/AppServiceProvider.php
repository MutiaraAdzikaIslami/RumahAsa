<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind custom services (singleton)
        $this->app->singleton(\App\Modules\Core\Services\AuditLogger::class);
        $this->app->singleton(\App\Modules\Core\Services\NikEncryptor::class);
        $this->app->singleton(\App\Modules\Core\Services\FotoStorageService::class);
        $this->app->singleton(\App\Modules\Workflow\Services\StatusWorkflow::class);
    }

    public function boot(): void
    {
        //
    }
}