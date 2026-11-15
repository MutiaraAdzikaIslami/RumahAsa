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

        // Role Gates (dengan prefix access- dan nama role langsung)
        Gate::define('access-admin',       fn ($user) => $user->role?->kode === 'admin');
        Gate::define('access-verifikator', fn ($user) => $user->role?->kode === 'verifikator');
        Gate::define('access-pimpinan',    fn ($user) => $user->role?->kode === 'pimpinan');
        Gate::define('access-surveyor',    fn ($user) => $user->role?->kode === 'surveyor');

        Gate::define('admin',       fn ($user) => $user->role?->kode === 'admin');
        Gate::define('verifikator', fn ($user) => $user->role?->kode === 'verifikator');
        Gate::define('pimpinan',    fn ($user) => $user->role?->kode === 'pimpinan');
        Gate::define('surveyor',    fn ($user) => $user->role?->kode === 'surveyor');

        // Permission Gates
        Gate::define('manage-users',    fn ($user) => in_array($user->role?->kode, ['admin']));
        Gate::define('manage-wilayah',  fn ($user) => in_array($user->role?->kode, ['admin']));
        Gate::define('view-audit-log',  fn ($user) => in_array($user->role?->kode, ['admin']));
        Gate::define('input-rumah',     fn ($user) => in_array($user->role?->kode, ['admin']));
        Gate::define('view-dashboard',  fn ($user) => in_array($user->role?->kode, ['surveyor', 'verifikator', 'admin', 'pimpinan']));
    }
}