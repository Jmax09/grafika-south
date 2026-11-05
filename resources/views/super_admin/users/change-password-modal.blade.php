{{-- ==========================================================================
     SUPER ADMIN CHANGE PASSWORD MODAL (Hardened - Automatic Email Integration)
     - Password-only change (email/phone live in the Update My Credentials modal)
     - Mandatory re-authentication: current password verified server-side BEFORE
       any OTP is dispatched, and again before the password is applied
     - Security code is automatically dispatched to the verified registered email
     - Two-step flow: Step 1 = request OTP with current password, Step 2 = confirm OTP + current password + apply
     - New password is hashed server-side and held in the session (never re-rendered)
     Included from: super_admin/users/index.blade.php
     ========================================================================== --}}
@php
    $saPw = auth()->user();

    // Mask email: keep first 2 chars + domain tail
    $atPosPw = $saPw->email ? strpos($saPw->email, '@') : false;
    $maskedEmailPw = !$saPw->email
        ? '—'
        : ($atPosPw > 2
            ? \Illuminate\Support\Str::mask($saPw->email, '*', 2, $atPosPw - 2)
            : \Illuminate\Support\Str::mask($saPw->email, '*', 1));

    // Mask phone: keep first 3 and last 3 digits visible
    $saPwPhone = $saPw->contact ?: $saPw->phone;
    $maskedPhonePw = ($saPwPhone && strlen($saPwPhone) > 6)
        ? \Illuminate\Support\Str::mask($saPwPhone, '*', 3, -3)
        : ($saPwPhone ?: '—');

    // Keep the modal open when there is a pending OTP confirm or a step-1 error
    $pwModalOpen = session('pw_otp_sent') || session('pw_error') || session('pw_reset')
        || $errors->has('super_admin_password') || $errors->has('new_password') || $errors->has('otp');
    $pwOtpPending = (bool) session('pw_otp_sent');
    // A password change is pending (hash stashed server-side; never rendered to the DOM)
    $hasPendingPassword = is_string(session('sa_pending_new_password')) && session('sa_pending_new_password') !== '';
@endphp

<div class="edit-modal-backdrop {{ $pwModalOpen ? 'active' : '' }}" id="modal-sa-changepw">
    <div class="edit-modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-key" style="color: var(--brand-magenta-fg);"></i> Change Password
            </h3>
            <button type="button" onclick="closeEditModal('modal-sa-changepw')" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-subtle);">&times;</button>
        </div>

        {{-- Security notices (server-side flash) --}}
        @if(session('pw_error'))
            <div style="background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); padding: 0.7rem 0.9rem; border-radius: 8px; font-size: 0.83rem; margin-bottom: 1rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('pw_error') }}
            </div>
        @endif

        @if(session('pw_reset'))
            <div style="background: var(--success-soft); color: var(--text-success); border: 1px solid var(--success-border); padding: 0.7rem 0.9rem; border-radius: 8px; font-size: 0.83rem; margin-bottom: 1rem;">
                <i class="fa-solid fa-rotate-left"></i> {{ session('pw_reset') }}
            </div>
        @endif

        @if(session('pw_otp_sent'))
            <div style="background: var(--accent-soft); color: var(--text-accent); border: 1px solid var(--info-border); padding: 0.7rem 0.9rem; border-radius: 8px; font-size: 0.83rem; margin-bottom: 1rem;">
                <i class="fa-solid fa-paper-plane"></i> {{ session('pw_otp_sent') }}
            </div>
        @endif

        @if($errors->has('super_admin_password') || $errors->has('new_password') || $errors->has('otp'))
            <div style="background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); padding: 0.7rem 0.9rem; border-radius: 8px; font-size: 0.83rem; margin-bottom: 1rem;">
                <ul style="margin: 0; padding-left: 1.1rem;">
                    @foreach(['super_admin_password', 'new_password', 'otp'] as $pwField)
                        @error($pwField)
                            <li>{{ $message }}</li>
                        @enderror
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- WHERE THE CODE GOES (masked, anti shoulder-surfing) --}}
        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 mb-4">
            <p class="text-xs text-gray-500">Email: <strong class="text-gray-900" style="letter-spacing: 0.5px;">{{ $maskedEmailPw }}</strong></p>
            <p class="text-xs text-gray-500" style="margin-top: 0.25rem;">Phone: <strong class="text-gray-900" style="letter-spacing: 0.5px;">{{ $maskedPhonePw }}</strong></p>
            <p style="font-size: 0.7rem; color: var(--text-subtle); margin: 0.45rem 0 0;"><i class="fa-solid fa-user-secret"></i> Values are masked to prevent shoulder surfing.</p>
        </div>

        @if($pwOtpPending)
            {{-- STEP 2: OTP CONFIRMATION --}}
            <form action="{{ route('super-admin.password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 0.85rem;">
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0 0 0.6rem;">
                        Confirm the pending change:
                        @if($hasPendingPassword)
                            <span style="display: inline-block; background: var(--brand-magenta-soft); color: var(--text-magenta); border: 1px solid var(--brand-magenta-soft); border-radius: 6px; padding: 0.1rem 0.5rem; font-weight: 700; margin-top: 0.35rem;"><i class="fa-solid fa-key"></i> Password will be changed</span>
                        @else
                            <span style="display: block; margin-top: 0.35rem;">Nothing to apply — please start over.</span>
                        @endif
                    </p>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">6-Digit Security Code *</label>
                    <input type="text" name="otp" value="{{ old('otp') }}" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="e.g. 123456" autocomplete="one-time-code" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid {{ $errors->has('otp') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: 8px; font-size: 1.15rem; letter-spacing: 8px; font-weight: 800; text-align: center;">
                    <p style="font-size: 0.72rem; color: var(--text-muted); margin: 0.35rem 0 0;"><i class="fa-solid fa-envelope"></i> Code sent to your registered email: <strong>{{ $maskedEmailPw }}</strong></p>
                </div>

                <div style="margin-bottom: 1.1rem;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Current Password * <span style="font-weight: 500; color: var(--text-subtle);">(required again to apply)</span></label>
                    <div style="position: relative;">
                        <input type="password" name="super_admin_password" id="input-sa-pw-pass-confirm" required autocomplete="current-password" placeholder="Enter your current password" style="width: 100%; padding: 0.6rem 2.5rem 0.6rem 0.85rem; border: 1px solid {{ $errors->has('super_admin_password') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: 8px; font-size: 0.9rem;">
                        <button type="button" onclick="togglePasswordVisibility('input-sa-pw-pass-confirm', 'eye-icon-sa-pw-pass-confirm')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                            <i class="fa-solid fa-eye" id="eye-icon-sa-pw-pass-confirm"></i>
                        </button>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="background: var(--cmyk-cyan); border: none;">
                        <i class="fa-solid fa-lock"></i> Verify &amp; Change Password
                    </button>
                </div>
            </form>

            <div style="margin-top: 0.85rem; text-align: center;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0 0 0.5rem;">Code not arriving? Check your spam folder — or cancel and request a new one.</p>
                <form action="{{ route('super-admin.password.cancel') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-rotate-left"></i> Cancel &amp; start over
                    </button>
                </form>
            </div>
        @else
            {{-- STEP 1: REQUEST OTP (password re-auth gate) --}}
            <form action="{{ route('super-admin.password.send-otp') }}" method="POST" x-data="{ showPass: false }">
                @csrf

                <div style="margin-bottom: 0.85rem;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">New Password *</label>
                    <div style="position: relative;">
                        <input :type="showPass ? 'text' : 'password'" name="new_password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required style="width: 100%; padding: 0.6rem 2.5rem 0.6rem 0.85rem; border: 1px solid {{ $errors->has('new_password') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: 8px; font-size: 0.9rem;">
                        <button type="button" @click="showPass = !showPass" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                            <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div style="margin-bottom: 0.85rem;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Confirm New Password *</label>
                    <div style="position: relative;">
                        <input :type="showPass ? 'text' : 'password'" name="new_password_confirmation" placeholder="Repeat new password" autocomplete="new-password" minlength="8" required style="width: 100%; padding: 0.6rem 2.5rem 0.6rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                        <button type="button" @click="showPass = !showPass" tabindex="-1" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                            <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- AUTOMATIC EMAIL DELIVERY NOTICE --}}
                <div style="margin-bottom: 0.85rem; padding: 0.65rem 0.8rem; background: var(--accent-soft); border: 1px solid var(--info-border); border-radius: 8px; display: flex; align-items: center; gap: 0.6rem;">
                    <i class="fa-solid fa-envelope" style="color: var(--text-accent); font-size: 1.05rem; flex-shrink: 0;"></i>
                    <p style="font-size: 0.78rem; color: var(--text-accent); margin: 0; line-height: 1.4;">
                        Security verification code will be sent automatically to your registered email: <strong>{{ $maskedEmailPw }}</strong>
                    </p>
                </div>

                <div style="margin-bottom: 0.85rem; border: 1px solid var(--brand-magenta-soft); border-radius: 10px; padding: 0.75rem 0.85rem; background: var(--brand-magenta-soft);">
                    <label style="font-size: 0.82rem; font-weight: 800; color: var(--text-magenta); display: block; margin-bottom: 0.3rem;">
                        <i class="fa-solid fa-key"></i> Current Password * <span style="font-weight: 500;">(mandatory security check)</span>
                    </label>
                    <div style="position: relative;">
                        <input type="password" name="super_admin_password" id="input-sa-pw-pass-request" required autocomplete="current-password" placeholder="Confirm your current password first" style="width: 100%; padding: 0.6rem 2.5rem 0.6rem 0.85rem; border: 1px solid {{ $errors->has('super_admin_password') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: 8px; font-size: 0.9rem;">
                        <button type="button" onclick="togglePasswordVisibility('input-sa-pw-pass-request', 'eye-icon-sa-pw-pass-request')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                            <i class="fa-solid fa-eye" id="eye-icon-sa-pw-pass-request"></i>
                        </button>
                    </div>
                    <p style="font-size: 0.72rem; color: var(--text-magenta); margin: 0.4rem 0 0;">
                        Your current password is required to change password. A 6-digit code will be sent to your email after this check passes.
                    </p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeEditModal('modal-sa-changepw')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: var(--cmyk-cyan); border: none;">
                        <i class="fa-solid fa-paper-plane"></i> Send Security Code
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
