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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'logging' => \App\Http\Middleware\LoggingMiddleware::class,
           
        ]);
        // CORS middleware pour permettre les requêtes depuis Swagger UI
        $middleware->web(append: [
          
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
