<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\SuperAdminCredentialOtpMail;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SuperAdminPasswordController extends Controller
{
    /**
     * How long a dispatched OTP stays valid (minutes).
     */
    private const OTP_TTL_MINUTES = 10;

    /**
     * Step 1 — Re-authenticate with the current password, then automatically dispatch a one-time
     * code to the registered email address to authorize the pending password change.
     * The new password is hashed server-side and held in the session — never re-rendered.
     */
    public function sendOtp(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'super_admin_password' => ['required', 'string'],
            'new_password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'super_admin_password.required' => 'Your current password is required to authorize a password change.',
            'new_password.required'         => 'Please enter a new password.',
            'new_password.min'              => 'The new password must be at least 8 characters.',
            'new_password.confirmed'        => 'The new password confirmation does not match.',
        ]);

        // SECURITY GATE: verify the current password before dispatching the OTP.
        if (!Hash::check($request->super_admin_password, $user->password)) {
            return back()
                ->with('pw_error', 'Your current password could not be verified. Please try again.');
        }

        // Reject reuse of the current password.
        if (Hash::check($request->new_password, $user->password)) {
            return back()
                ->with('pw_error', 'Your new password cannot be the same as your current password.');
        }

        // Hash the new password now and stash it server-side until OTP verification.
        $request->session()->put('sa_pending_new_password', Hash::make($request->new_password));

        // Generate a strict 6-digit numeric code (zero-padded).
        $otp    = sprintf('%06d', random_int(0, 999999));
        $otpKey = PasswordOtp::PASSWORD_TYPE_PREFIX . $user->email;

        PasswordOtp::where('email', $otpKey)->delete();

        PasswordOtp::create([
            'email'      => $otpKey,
            'otp'        => hash('sha256', $otp),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
        ]);

        // Automatically dispatch OTP to the user's registered email
        Mail::to($user->email)->sendNow(new SuperAdminCredentialOtpMail($user, $otp, 'password'));

        return redirect()->back()
            ->with('pw_otp_sent', 'A 6-digit security code was sent to your registered email address (' . $user->email . '). It expires in ' . self::OTP_TTL_MINUTES . ' minutes.');
    }

    /**
     * Step 2 — Verify the OTP (and re-confirm the current password), then apply
     * the pending password change and refresh the session.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Please enter the 6-digit security code.',
            'otp.digits'   => 'The security code must be exactly 6 digits.',
        ]);

        $otpKey = PasswordOtp::PASSWORD_TYPE_PREFIX . $user->email;
        $record = PasswordOtp::where('email', $otpKey)->latest('id')->first();

        if (!$record) {
            $request->session()->forget('sa_pending_new_password');
            return back()->with('pw_error', 'No pending verification was found. Please request a new security code.');
        }

        if (now()->greaterThan($record->expires_at)) {
            $record->delete();
            $request->session()->forget('sa_pending_new_password');
            return back()->with('pw_error', 'Your security code has expired. Please request a new one.');
        }

        if (!hash_equals($record->otp, hash('sha256', $request->otp))) {
            return back()->with('pw_error', 'The security code you entered is incorrect.');
        }

        $pendingPasswordHash = $request->session()->get('sa_pending_new_password');
        if (!is_string($pendingPasswordHash) || $pendingPasswordHash === '') {
            $record->delete();
            return back()->with('pw_error', 'Your password change session expired. Please start over.');
        }

        // OTP validated — consume it so it cannot be replayed.
        $record->delete();

        $user->password = $pendingPasswordHash;
        $user->save();

        $request->session()->forget('sa_pending_new_password');

        // Password changed: refresh the session to prevent session-fixation reuse.
        $request->session()->regenerate();

        return redirect()->back()
            ->with('success', 'Your password has been changed successfully.');
    }

    /**
     * Cancel a pending password change: discard the issued OTP, drop the
     * server-side pending password hash, reopen Step 1.
     */
    public function cancel(Request $request)
    {
        $user = auth()->user();

        PasswordOtp::where('email', PasswordOtp::PASSWORD_TYPE_PREFIX . $user->email)->delete();
        $request->session()->forget(['sa_pending_new_password', '_old_input']);

        return redirect()->back()
            ->with('pw_reset', 'The password change was cancelled. Please start over.');
    }
}
