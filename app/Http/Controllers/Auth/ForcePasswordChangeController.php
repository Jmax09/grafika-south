<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChangeController extends Controller
{
    /**
     * Show the mandatory "set your new password" screen.
     */
    public function show()
    {
        return view('auth.force-change-password');
    }

    /**
     * Persist the new password and release the forced-change lock.
     */
    public function update(Request $request): Response
    {
        $user = $request->user();

        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.min' => 'Your new password must be at least 8 characters long.',
            'new_password.confirmed' => 'The password confirmation does not match.',
        ]);

        // The new password must differ from the temporary one we emailed.
        if (Hash::check($validated['new_password'], $user->password)) {
            return back()->withErrors([
                'new_password' => 'Please choose a password different from your temporary one.',
            ]);
        }

        // The 'password' cast will hash this value automatically.
        $user->password = $validated['new_password'];
        $user->must_change_password = false;
        $user->save();

        $request->session()->regenerate();

        if ($user->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard')
                ->with('success', 'Password updated. Welcome aboard!');
        }

        if ($user->isAdmin()) {
            return redirect($user->getFirstAccessibleRoute())
                ->with('success', 'Password updated. Welcome aboard!');
        }

        return redirect()->route('customer.dashboard')
            ->with('success', 'Password updated. Welcome aboard!');
    }
}
