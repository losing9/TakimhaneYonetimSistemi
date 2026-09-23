<?php

use App\Http\Middleware\AdminPortalAccess;
use App\Http\Middleware\SecurityHeaders;
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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        // Tüm web yanıtlarına güvenlik başlıkları ekle
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        // Admin portalı için route middleware tanımı
        $middleware->alias([
            'admin-portal' => AdminPortalAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Hata mesajlarında yığın izi (stack trace) üretim ortamında gösterilmez (APP_DEBUG=false)
    })->create();

