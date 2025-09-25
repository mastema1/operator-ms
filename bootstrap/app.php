<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::redirect('/home', '/dashboard');
            
            // Load debug routes only in development
            if (app()->environment('local', 'development')) {
                Route::middleware('web')->group(__DIR__.'/../routes/debug.php');
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Security middleware stack (order matters for performance)
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\SessionSecurityMiddleware::class,
            \App\Http\Middleware\TenantIsolationMiddleware::class,
            \App\Http\Middleware\InputSanitizationMiddleware::class,
            \App\Http\Middleware\AuditLoggingMiddleware::class,
        ]);
        
        // Rate limiting aliases for different endpoint types
        $middleware->alias([
            'rate.api' => \App\Http\Middleware\EnhancedRateLimitMiddleware::class.':api',
            'rate.auth' => \App\Http\Middleware\EnhancedRateLimitMiddleware::class.':auth',
            'rate.search' => \App\Http\Middleware\EnhancedRateLimitMiddleware::class.':search',
            'tenant.isolation' => \App\Http\Middleware\TenantIsolationMiddleware::class,
            'security.session' => \App\Http\Middleware\SessionSecurityMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
