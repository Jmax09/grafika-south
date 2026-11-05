<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\SuperAdminCredentialOtpMail;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SuperAdminCredentialController extends Controller
{
    /**
     * How long a dispatched OTP stays valid (minutes).
     */
    private const OTP_TTL_MINUTES = 10;

    /**
     * Step 1 — Re-authenticate with the current password and provide the new
     * email, then dispatch a distinct one-time code to BOTH the current email and
     * the new email address. Step 2 requires both codes to be entered, proving
     * ownership of the old address and reachability of the new one.
     */
    public function sendOtp(Request $request)
    {
        $user = auth()->user();

        // Super Admin email is locked — only Admin/Staff may change their email.
        if ($user && $user->isSuperAdmin()) {
            return back()->with('error', 'The Super Admin email address cannot be changed.');
        }

        $request->validate([
            'super_admin_password' => ['required', 'string'],
            'new_email'            => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ], [
            'super_admin_password.required' => 'Your current password is required to authorize changing your email.',
            'new_email.required'            => 'Please enter a new email address.',
            'new_email.email'               => 'Please enter a valid new email address.',
            'new_email.unique'              => 'That email address is already taken by another account.',
        ]);

        // SECURITY GATE: verify the current password before dispatching the OTPs.
        if (!Hash::check($request->super_admin_password, $user->password)) {
            return back()->withInput($request->only('new_email'))
                ->with('cred_error', 'Your current password could not be verified. Please try again.');
        }

        $newEmail = trim($request->new_email);

        if ($newEmail === $user->email) {
            return back()->withInput($request->only('new_email'))
                ->with('cred_error', 'The new email address cannot be the same as your current email.');
        }

        // Two independent 6-digit codes: one keyed by the current email, one keyed by
        // the user id ("new:<id>") so Step 2 can look both up before applying.
        $otpCurrent = sprintf('%06d', random_int(0, 999999));
        $otpNew     = sprintf('%06d', random_int(0, 999999));
        $currentKey = PasswordOtp::CREDENTIAL_TYPE_PREFIX . $user->email;
        $newKey     = PasswordOtp::CREDENTIAL_TYPE_PREFIX . 'new:' . $user->id;

        PasswordOtp::where('email', $currentKey)->delete();
        PasswordOtp::where('email', $newKey)->delete();

        PasswordOtp::create([
            'email'      => $currentKey,
            'otp'        => hash('sha256', $otpCurrent),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
        ]);
        PasswordOtp::create([
            'email'      => $newKey,
            'otp'        => hash('sha256', $otpNew),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
        ]);

        // Remember the pending new email so Step 2 can carry it (Step 2 no longer
        // re-collects the email) and stays open across an incorrect-code retry.
        $request->session()->put('sa_pending_new_email', $newEmail);

        // Dispatch a code to each address.
        Mail::to($user->email)->sendNow(new SuperAdminCredentialOtpMail($user, $otpCurrent, 'credentials'));
        Mail::to($newEmail)->sendNow(new SuperAdminCredentialOtpMail($user, $otpNew, 'credentials'));

        return redirect()->back()
            ->withInput($request->only('new_email'))
            ->with('cred_otp_sent', 'A security code was sent to BOTH your current email (' . $user->email . ') and your new email (' . $newEmail . '). Enter both codes to confirm. They expire in ' . self::OTP_TTL_MINUTES . ' minutes.');
    }

    /**
     * Step 2 — Verify BOTH one-time codes (the one delivered to the current email
     * and the one delivered to the new email), then apply the pending change.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        // Super Admin email is locked — only Admin/Staff may change their email.
        if ($user && $user->isSuperAdmin()) {
            return back()->with('error', 'The Super Admin email address cannot be changed.');
        }

        $request->validate([
            'new_email'   => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'otp_current' => ['required', 'digits:6'],
            'otp_new'     => ['required', 'digits:6'],
        ], [
            'new_email.required'    => 'Your pending email change is missing. Please start over.',
            'new_email.email'       => 'The pending email address is invalid. Please start over.',
            'new_email.unique'      => 'That email address is already taken by another account.',
            'otp_current.required'  => 'Please enter the code sent to your current email.',
            'otp_current.digits'    => 'The current-email code must be exactly 6 digits.',
            'otp_new.required'      => 'Please enter the code sent to your new email.',
            'otp_new.digits'        => 'The new-email code must be exactly 6 digits.',
        ]);

        $newEmail   = trim($request->new_email);
        $currentKey = PasswordOtp::CREDENTIAL_TYPE_PREFIX . $user->email;
        $newKey     = PasswordOtp::CREDENTIAL_TYPE_PREFIX . 'new:' . $user->id;

        $currentRecord = PasswordOtp::where('email', $currentKey)->latest('id')->first();
        $newRecord     = PasswordOtp::where('email', $newKey)->latest('id')->first();

        if (!$currentRecord || !$newRecord) {
            $request->session()->forget('sa_pending_new_email');
            return back()->with('cred_error', 'No pending verification was found. Please request new security codes.');
        }

        if (now()->greaterThan($currentRecord->expires_at) || now()->greaterThan($newRecord->expires_at)) {
            $currentRecord->delete();
            $newRecord->delete();
            $request->session()->forget('sa_pending_new_email');
            return back()->with('cred_error', 'Your security codes have expired. Please request new ones.');
        }

        $currentOk = hash_equals($currentRecord->otp, hash('sha256', $request->otp_current));
        $newOk     = hash_equals($newRecord->otp, hash('sha256', $request->otp_new));

        if (!$currentOk || !$newOk) {
            // Wrong code(s): stay on Step 2 (the pending email is kept in session).
            return back()->with('cred_error', 'One or both security codes are incorrect. Please check them and try again.');
        }

        // Both codes validated — consume them so they cannot be replayed.
        $currentRecord->delete();
        $newRecord->delete();

        $user->email = $newEmail;
        $user->save();

        $request->session()->forget(['sa_pending_new_email', '_old_input']);

        return redirect()->back()
            ->with('success', 'Your email address has been updated successfully.');
    }

    /**
     * Cancel a pending credential change: discard the issued OTP,
     * then reopen Step 1 fresh.
     */
    public function cancel(Request $request)
    {
        $user = auth()->user();

        PasswordOtp::where('email', PasswordOtp::CREDENTIAL_TYPE_PREFIX . $user->email)->delete();
        PasswordOtp::where('email', PasswordOtp::CREDENTIAL_TYPE_PREFIX . 'new:' . $user->id)->delete();

        $request->session()->forget(['sa_pending_new_email', '_old_input']);

        return redirect()->back()
            ->with('cred_reset', 'The pending change was cancelled. Please start over.');
    }
}
