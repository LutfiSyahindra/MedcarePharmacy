<?php

use App\Http\Middleware\EnsureCashierIsAvailable;
use App\Http\Middleware\EnsureStockMenusAreAvailable;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    // ->withRouting(
    //     web: __DIR__.'/../routes/web.php',
    //     commands: __DIR__.'/../routes/console.php',
    //     health: '/up',
    // )
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        channels: __DIR__.'/../routes/channels.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'stock.menus.available' => EnsureStockMenusAreAvailable::class,
            'cashier.available' => EnsureCashierIsAvailable::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
