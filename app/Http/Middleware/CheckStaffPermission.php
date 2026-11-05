<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckStaffPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $user = Auth::user();

        // Active account check
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact support.');
        }

        // Super Admin has unrestricted access to everything
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // For Staff Admin, check whether this specific module is granted
        if ($user->isAdmin()) {
            if (!$user->hasPermission($permission)) {
                $firstRoute = $user->getFirstAccessibleRoute();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Access denied: You do not have permission to access this module.',
                    ], 403);
                }

                return redirect($firstRoute)->with('error', 'Access denied: You do not have permission to access that section.');
            }

            return $next($request);
        }

        // If customer tries to access admin routes
        return redirect()->route('customer.dashboard')->with('error', 'Unauthorized area.');
    }
}
