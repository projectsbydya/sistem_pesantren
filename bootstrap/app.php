<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        $middleware->alias([
            // All tenant.* aliases resolve to the same unified ResolveTenant middleware
            'tenant.resolve'   => \App\Http\Middleware\ResolveTenant::class,
            'tenant'           => \App\Http\Middleware\ResolveTenant::class,
            'tenant.session'   => \App\Http\Middleware\ResolveTenant::class,
            'tenant.subdomain' => \App\Http\Middleware\ResolveTenant::class,

            'tenant.log'       => \App\Http\Middleware\TenantRequestLogger::class,
            'super_admin.gate' => \App\Http\Middleware\EnsureSuperAdminGate::class,
            'owns.tenant'      => \App\Http\Middleware\EnsureOwnsTenant::class,
            'not.super_admin'  => \App\Http\Middleware\NotSuperAdmin::class,
            'password.change'  => \App\Http\Middleware\RequirePasswordChange::class,
            'onboarding'       => \App\Http\Middleware\RequireProgramSelection::class,
            'setup.progressive' => \App\Http\Middleware\RequireProgramSelection::class,
            'program.access'    => \App\Http\Middleware\ValidateProgramAccess::class,
        ]);

        // TenantRequestLogger is NOT added globally — it is applied only on
        // authenticated tenant routes via the 'tenant.log' alias, so public
        // routes (/, /login, /register) are not burdened with unnecessary overhead.
    })
    
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
