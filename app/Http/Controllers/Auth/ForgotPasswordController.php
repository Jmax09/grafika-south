<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /**
     * Display the email request form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Generate 6-digit OTP and send via email (supports email or username for all roles).
     */
    public function sendResetOtp(Request $request)
    {
        $loginInput = trim((string) ($request->input('email') ?: $request->input('login', '')));

        if ($loginInput === '') {
            return back()->withInput()->withErrors([
                'email' => 'Please enter your registered email address or username.',
            ]);
        }

        // Resolve user by email OR username — works for customer, admin, and super_admin roles
        $user = User::findByEmailOrUsername($loginInput);

        if (! $user) {
            // Generic message to prevent account enumeration
            return back()->withInput()->withErrors([
                'email' => 'We could not find an account associated with that email address or username.',
            ]);
        }

        // Generate a cryptographically random 6-digit OTP
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Remove any pre-existing OTP for this email (prevent stale code confusion)
        PasswordOtp::where('email', $user->email)->delete();

        // Store fresh OTP with 15-minute expiry
        PasswordOtp::create([
            'email'      => $user->email,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Attempt to dispatch email synchronously (respects configured MAIL_MAILER driver, e.g. log or smtp)
        try {
            Mail::to($user->email)->sendNow(new PasswordResetOtpMail($user, $otp));
        } catch (\Throwable $e) {
            // Clean up the stored OTP so it cannot be guessed without a successful delivery
            PasswordOtp::where('email', $user->email)->delete();

            Log::error('Password reset OTP mail dispatch failed', [
                'user_id'         => $user->id,
                'user_role'       => $user->role,
                'email'           => $user->email,
                'exception_class' => get_class($e),
                'mailer'          => config('mail.default'),
                'host'            => config('mail.mailers.smtp.host'),
                'port'            => config('mail.mailers.smtp.port'),
                'error'           => $e->getMessage(),
            ]);

            // Provide an actionable error — avoid leaking SMTP internals to the browser
            $friendlyError = str_contains($e->getMessage(), '535') || str_contains($e->getMessage(), 'credential') || str_contains($e->getMessage(), 'authenticate')
                ? 'Mail service authentication error. Please contact the system administrator to update the SMTP credentials.'
                : 'We were unable to send the OTP email right now. Please try again in a few minutes or contact support.';

            return back()->withInput()->withErrors([
                'email' => $friendlyError,
            ]);
        }

        Log::info('Password reset OTP sent', [
            'user_id'   => $user->id,
            'user_role' => $user->role,
            'email'     => $user->email,
        ]);

        return redirect()->route('password.otp', ['email' => $user->email])
            ->with('success', 'A 6-digit OTP code has been sent to ' . $user->email . '. Please check your inbox (and spam folder).');
    }

    /**
     * Display 6-digit OTP verification screen.
     */
    public function showOtpForm(Request $request)
    {
        $email = $request->query('email') ?: old('email');
        return view('auth.passwords.otp', compact('email'));
    }

    /**
     * Verify the 6-digit OTP.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'otp' => ['required', 'string'],
        ]);

        $cleanOtp = preg_replace('/\D/', '', $request->otp);

        $otpRecord = PasswordOtp::where('email', $request->email)
            ->where('otp', $cleanOtp)
            ->where('expires_at', '>=', now())
            ->first();

        if (! $otpRecord) {
            return back()->withInput()->with('error', 'Invalid or expired 6-digit OTP code. Please check your email or request a new code.');
        }

        // OTP is valid! Delete record and set verified session token
        $otpRecord->delete();

        session([
            'otp_verified_email' => $request->email,
            'otp_verified_at' => now()->timestamp,
        ]);

        return redirect()->route('password.reset.form')
            ->with('success', 'OTP verified successfully! Please enter your new password below.');
    }

    /**
     * Display new password reset form after successful OTP verification.
     */
    public function showResetForm()
    {
        $email = session('otp_verified_email');
        $verifiedAt = session('otp_verified_at');

        if (! $email || ! $verifiedAt || (now()->timestamp - $verifiedAt > 1800)) {
            return redirect()->route('password.request')
                ->with('error', 'Session expired. Please request a new OTP code to reset your password.');
        }

        return view('auth.passwords.reset', compact('email'));
    }

    /**
     * Save new password for the OTP-verified account (all roles: customer, admin, super_admin).
     */
    public function resetPassword(Request $request)
    {
        $email     = session('otp_verified_email');
        $verifiedAt = session('otp_verified_at');

        // Guard: session must exist and be fresh (30-minute window)
        if (! $email || ! $verifiedAt || (now()->timestamp - $verifiedAt > 1800)) {
            return redirect()->route('password.request')
                ->with('error', 'Session expired. Please request a new OTP code to reset your password.');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min'       => 'New password must be at least 8 characters.',
        ]);

        // Safe lookup — firstOrFail() would throw ModelNotFoundException (500/404)
        $user = User::where('email', $email)->first();

        if (! $user) {
            Log::error('Password reset failed — account not found after OTP verification', [
                'email' => $email,
            ]);
            session()->forget(['otp_verified_email', 'otp_verified_at']);
            return redirect()->route('password.request')
                ->with('error', 'Account not found. Please request a new OTP code.');
        }

        // Prevent reusing the same password
        if (Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Your new password cannot be the same as your current password.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        Log::info('Password reset completed via OTP', [
            'user_id'   => $user->id,
            'user_role' => $user->role,
            'email'     => $user->email,
        ]);

        session()->forget(['otp_verified_email', 'otp_verified_at']);

        // Already logged in (profile "Change Password"): keep the user in-app and return to their profile.
        if (Auth::check()) {
            return redirect()->route('customer.profile')
                ->with('success', 'Your password has been updated successfully.');
        }

        // Guest reset — redirect based on role: staff/admin → admin login; others → standard login
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()->route('admin.login')
                ->with('success', 'Password set successfully! You can now log in with your new password.');
        }

        return redirect()->route('login')
            ->with('success', 'Your password has been reset successfully. You can now log in with your new password.');
    }
}
