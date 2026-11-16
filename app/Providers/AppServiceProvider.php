<?php

namespace App\Providers;

use App\Models\RumahWarga;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Modules\Core\Services\AuditLogger::class);

        // NikEncryptor butuh NIK_HMAC_KEY dari config — bind manual supaya container
        // tidak mencoba auto-resolve string primitive (yang akan gagal).
        $this->app->singleton(\App\Modules\Core\Services\NikEncryptor::class, function ($app) {
            $hmacKey = (string) config('rumahasa.nik.hmac_key', '');

            if ($hmacKey === '') {
                $hmacKey = (string) config('app.key');
            }

            return new \App\Modules\Core\Services\NikEncryptor($hmacKey);
        });

        $this->app->singleton(\App\Modules\Core\Services\FotoStorageService::class, function ($app) {
            return new \App\Modules\Core\Services\FotoStorageService(
                disk: (string) config('rumahasa.foto.disk', 'local'),
                maxSize: (int) config('rumahasa.foto.max_size', 5 * 1024 * 1024),
                maxPerKomponen: (int) config('rumahasa.foto.max_per_komponen', 5),
            );
        });
        $this->app->singleton(\App\Modules\Workflow\Services\StatusWorkflow::class);
        $this->app->scoped(\App\Modules\Users\Services\UserService::class);
        $this->app->scoped(\App\Modules\Rumah\Services\RumahService::class);
    }

    public function boot(): void
    {
        // Register Blade directives helper untuk enkripsi NIK / No KK di form edit
        Blade::directive('decrypt_nik', function ($expression) {
            return "<?php echo \App\Providers\AppServiceProvider::decryptNikForEdit($expression); ?>";
        });

        Blade::directive('decrypt_nokk', function ($expression) {
            return "<?php echo \App\Providers\AppServiceProvider::decryptNoKkForEdit($expression); ?>";
        });
    }

    /**
     * Helper: decrypt NIK untuk ditampilkan di form edit (admin only).
     * Bila gagal decrypt (key salah / data rusak), kembalikan string kosong.
     */
    public static function decryptNikForEdit(?RumahWarga $rumah): string
    {
        if (!$rumah || !isset($rumah->nik_enc)) return '';
        try {
            return app(\App\Modules\Core\Services\NikEncryptor::class)->decryptNik($rumah->nik_enc);
        } catch (\Throwable) {
            return '';
        }
    }

    public static function decryptNoKkForEdit(?RumahWarga $rumah): string
    {
        if (!$rumah || !isset($rumah->no_kk_enc)) return '';
        try {
            return app(\App\Modules\Core\Services\NikEncryptor::class)->decryptNoKk($rumah->no_kk_enc);
        } catch (\Throwable) {
            return '';
        }
    }
}
