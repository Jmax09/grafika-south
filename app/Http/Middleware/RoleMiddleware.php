<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact support.');
        }

        // Check if user's role matches any of the required roles
        if (!empty($roles) && !$user->hasRole($roles)) {
            if ($user->isSuperAdmin()) {
                return redirect()->route('super-admin.dashboard')->with('error', 'Unauthorized area. Redirected to your Super Admin dashboard.');
            }

            if ($user->isAdmin()) {
                return redirect($user->getFirstAccessibleRoute())->with('error', 'Unauthorized area.');
            }

            return redirect()->route('customer.dashboard')->with('error', 'Unauthorized area. Redirected to your Customer dashboard.');
        }

        return $next($request);
    }
}
