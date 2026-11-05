<?php

namespace App\Http\Middleware;

use App\Services\CartAccountService;
use Closure;
use Illuminate\Http\Request;

class SyncCartWithAccount
{
    public function handle(Request $request, Closure $next)
    {
        // Restore the signed-in customer's saved cart into the session
        // (only when the session does not already hold one).
        CartAccountService::hydrateFromAccount();

        return $next($request);
    }
}
