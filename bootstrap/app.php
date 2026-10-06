<?php

use App\Http\Middleware\ApiAuthenticate;
use App\Http\Middleware\ApiKeyAuth;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureStaff;
use App\Http\Middleware\EnsureTwoFactor;
use App\Http\Middleware\RequirePair;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            RequirePair::class,
            SetLocale::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => CheckRole::class,
            'staff' => EnsureStaff::class,
            'api.key' => ApiKeyAuth::class,
            'api.auth' => ApiAuthenticate::class,
            '2fa' => EnsureTwoFactor::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
