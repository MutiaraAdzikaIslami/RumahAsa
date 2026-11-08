<?php

namespace App\Providers;

use App\Models\RumahWarga;
use App\Modules\Core\Policies\RumahPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        RumahWarga::class => RumahPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // === Gate untuk 4 role (FR-04) ===
        Gate::define('access-admin',       fn ($user) => $user->role->kode === 'admin');
        Gate::define('access-verifikator', fn ($user) => $user->role->kode === 'verifikator');
        Gate::define('access-pimpinan',    fn ($user) => $user->role->kode === 'pimpinan');
        Gate::define('access-surveyor',    fn ($user) => $user->role->kode === 'surveyor');

        // === Composite gates untuk menu (FR-04 + sidebar filter) ===
        Gate::define('manage-users',    fn ($user) => in_array($user->role->kode, ['admin']));
        Gate::define('manage-wilayah',  fn ($user) => in_array($user->role->kode, ['admin']));
        Gate::define('view-audit-log',  fn ($user) => in_array($user->role->kode, ['admin']));
        Gate::define('input-rumah',     fn ($user) => in_array($user->role->kode, ['surveyor']));
        Gate::define('view-dashboard',  fn ($user) => in_array($user->role->kode, ['surveyor', 'verifikator', 'admin', 'pimpinan']));
    }
}
