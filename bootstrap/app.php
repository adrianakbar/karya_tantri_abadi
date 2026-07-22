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
        // Register audit trail middleware for web routes
        $middleware->web(append: [
            \App\Http\Middleware\LogUserActivity::class,
        ]);
        
        // Register alias for role based redirect
        $middleware->alias([
            'role.redirect' => \App\Http\Middleware\RoleBasedRedirect::class,
            'redirect.role' => \App\Http\Middleware\RedirectBasedOnRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle POST to GET-only routes (e.g. Livewire failure due to Cloudflare)
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->isMethod('post') && $request->path() === 'auth/login') {
                return redirect('/auth/login')
                    ->with('error', 'Gagal memproses login. Silakan refresh halaman dan coba lagi.');
            }
            return null; // let Laravel handle normally
        });
    })->create();
