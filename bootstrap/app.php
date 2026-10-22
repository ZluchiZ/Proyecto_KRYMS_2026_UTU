<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
<<<<<<< HEAD
            fn (Request $request) => $request->is('api/*'),
=======
            fn (Request $request) => $request->expectsJson() || $request->is('api/*'),
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
        );
    })->create();
