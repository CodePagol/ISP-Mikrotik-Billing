<?php

use App\Http\Middleware\CheckSiteStatus;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocaleFromSession::class,
        ]);
        $middleware->append(CheckSiteStatus::class);
        $middleware->alias([
            'reseller'           => \App\Http\Middleware\EnsureUserIsReseller::class,
            'restrict.profile'   => \App\Http\Middleware\RestrictToProfileIfNoPermissions::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/payment/bkash/callback',
            '/payment/nagad/callback',
            '/payment/sslcommerz/callback',
            '/payment/mock/submit',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
