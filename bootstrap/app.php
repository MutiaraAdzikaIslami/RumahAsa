<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register 3 middleware alias (scrum-13, scrum-11, scrum-12)
        $middleware->alias([
            'role'             => \App\Modules\Core\Http\Middleware\RoleMiddleware::class,
            'session.timeout'  => \App\Modules\Auth\Http\Middleware\SessionTimeout::class,
            'must.change.pwd'  => \App\Modules\Auth\Http\Middleware\MustChangePassword::class,
        ]);

        // scrum-11: Append SessionTimeout ke web group
        $middleware->appendToGroup('web', \App\Modules\Auth\Http\Middleware\SessionTimeout::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();