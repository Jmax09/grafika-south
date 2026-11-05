<?php

namespace App\Services;

use App\Models\UserCart;
use Illuminate\Support\Facades\Auth;

/**
 * Keeps the session cart mirrored to the signed-in customer's account so the
 * cart (including attached artwork files) survives new sessions / devices.
 *
 * The session cart stays the working copy for every existing feature; this
 * service only loads the account cart into the session when it is empty and
 * writes the session cart back to the account after every cart mutation.
 */
class CartAccountService
{
    /**
     * Load the customer's saved cart into the session when the session has no cart yet.
     */
    public static function hydrateFromAccount(): void
    {
        if (! Auth::check()) {
            return;
        }

        $sessionCart = session()->get('cart', []);
        if (! empty($sessionCart)) {
            return; // session already holds a cart (e.g. guest cart carried over on login)
        }

        $saved = UserCart::where('user_id', Auth::id())->first();
        if (! $saved || empty($saved->items)) {
            return;
        }

        session()->put('cart', $saved->items);
    }

    /**
     * Persist the current session cart to the customer's account.
     */
    public static function persistToAccount(): void
    {
        if (! Auth::check()) {
            return;
        }

        $items = session()->get('cart', []);

        if (empty($items)) {
            UserCart::where('user_id', Auth::id())->delete();

            return;
        }

        UserCart::updateOrCreate(
            ['user_id' => Auth::id()],
            ['items' => $items]
        );
    }

    /**
     * Remove the saved cart from the customer's account (after checkout / clear).
     */
    public static function forgetAccountCart(): void
    {
        if (! Auth::check()) {
            return;
        }

        UserCart::where('user_id', Auth::id())->delete();
    }
}
