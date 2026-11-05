<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Newly-created staff accounts must set their own password before they can
     * use the rest of the application. Until the flag is cleared, every request
     * is funnelled to the "Set Your New Password" screen.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->must_change_password) {
            // Allow the change-password screen itself and logging out to pass through.
            if ($request->routeIs('password.force.change', 'password.force.change.update', 'logout')) {
                return $next($request);
            }

            return redirect()->route('password.force.change')
                ->with('warning', 'For security, please set a new password before continuing.');
        }

        return $next($request);
    }
}
