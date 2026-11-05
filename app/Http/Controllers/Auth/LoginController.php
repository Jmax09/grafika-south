<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    /**
     * Show the unified login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle authentication attempt via email or username and redirect based on role.
     */
    public function login(Request $request)
    {
        $loginInput = trim((string) ($request->input('login') ?: $request->input('email', '')));

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if ($loginInput === '') {
            return back()->withErrors([
                'login' => 'Please enter your email address or username.',
                'email' => 'Please enter your email address or username.',
            ])->withInput();
        }

        $remember = $request->boolean('remember');

        // Check if login input is an email address or username
        $isEmail = (bool) filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $credentials = [
            $isEmail ? 'email' : 'username' => $isEmail ? $loginInput : strtolower($loginInput),
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'login' => 'Your account is deactivated. Please contact an administrator.',
                    'email' => 'Your account is deactivated. Please contact an administrator.',
                ]);
            }

            // ── Portal / role gate ──────────────────────
            // Staff accounts must sign in through the "Login as Admin" portal,
            // and customer accounts through the regular store login.
            $portal  = $request->input('login_portal', 'customer');
            $isStaff = $user->isAdmin() || $user->isSuperAdmin();

            if ($portal !== 'admin' && $isStaff) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'login' => 'This is a staff account. Please sign in through the "Login as Admin" portal.',
                    'email' => 'This is a staff account. Please sign in through the "Login as Admin" portal.',
                ]);
            }

            if ($portal === 'admin' && !$isStaff) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'login' => 'Customer accounts must sign in through the regular login page.',
                    'email' => 'Customer accounts must sign in through the regular login page.',
                ]);
            }

            $request->session()->regenerate();

            // Security default: signing in always terminates every other active
            // session for this account, so it stays logged in on this device only.
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('login', 'email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Successfully logged out.');
    }

    /**
     * Helper method to redirect user based on role (respecting intended URL if set).
     */
    protected function redirectBasedOnRole($user)
    {
        if ($user->must_change_password) {
            return redirect()->route('password.force.change');
        }

        if ($user->isSuperAdmin()) {
            return redirect()->intended(route('super-admin.dashboard'));
        }

        if ($user->isAdmin()) {
            return redirect()->intended($user->getFirstAccessibleRoute());
        }

        // Customers land on the storefront (landing page) after login.
        // redirect()->intended() still honors a protected page they were bounced from, if any.
        return redirect()->intended(route('landing'));
    }
}
