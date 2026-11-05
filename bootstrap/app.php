<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render terminates TLS at its load balancer, so the container only ever
        // sees plain HTTP with X-Forwarded-Proto set. Without this, route() emits
        // http:// URLs and forms trip the browser's insecure-submission warning.
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\CheckStaffPermission::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Newly-created staff accounts must set their own password before use.
        $middleware->web(append: [
            \App\Http\Middleware\ForcePasswordChange::class,
        ]);

        // Restore the signed-in customer's saved (account) cart into the session.
        $middleware->web(append: [
            \App\Http\Middleware\SyncCartWithAccount::class,
        ]);

        $middleware->redirectGuestsTo('/login');

        $middleware->redirectUsersTo(function (Request $request) {
            $user = Auth::user();
            if ($user) {
                if ($user->must_change_password) {
                    return route('password.force.change');
                }
                if ($user->isSuperAdmin()) {
                    return route('super-admin.dashboard');
                }
                if ($user->isAdmin()) {
                    return $user->getFirstAccessibleRoute();
                }
                return route('customer.dashboard');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            if ($request->is('logout')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('success', 'Successfully logged out.');
            }

            return redirect()->route('login')->with('warning', 'Your session expired. Please sign in again.');
        });
    })->create();
