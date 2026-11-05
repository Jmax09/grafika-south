{{-- ==========================================================================
     SUPER ADMIN CREDENTIALS MODAL (Hardened - Automatic Email Integration)
     - Data masking: current email shown partially redacted (anti shoulder-surfing)
     - Mandatory re-authentication: Current password verified server-side
       BEFORE any OTP is dispatched, and again before email is updated
     - Email change only
     - Security code is automatically dispatched to the verified current email
     - Two-step flow: Step 1 = request OTP with current password, Step 2 = confirm OTP + current password + apply
     Included from: super_admin/users/index.blade.php
     ========================================================================== --}}
@php
    $sa = auth()->user();

    // Mask email: keep first 2 chars + domain tail (Str::mask count is bounded to stay valid)
    $atPos = $sa->email ? strpos($sa->email, '@') : false;
    $maskedEmail = !$sa->email
        ? '—'
        : ($atPos > 2
            ? \Illuminate\Support\Str::mask($sa->email, '*', 2, $atPos - 2)
            : \Illuminate\Support\Str::mask($sa->email, '*', 1));

    // Keep the modal open when there is a pending OTP confirm or a step-1 error
    $credModalOpen = session('cred_otp_sent') || session('cred_error') || session('cred_reset')
        || $errors->has('super_admin_password') || $errors->has('otp') || $errors->has('new_email');
    $credOtpPending = (bool) session('cred_otp_sent');
@endphp

<div class="edit-modal-backdrop {{ $credModalOpen ? 'active' : '' }}" id="modal-sa-credentials">
    <div class="edit-modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-shield-halved" style="color: var(--brand-magenta-fg);"></i> Update My Credentials
            </h3>
            <button type="button" onclick="closeEditModal('modal-sa-credentials')" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-subtle);">&times;</button>
        </div>

        {{-- Security notices (server-side flash) --}}
        @if(session('cred_error'))
            <div style="background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); padding: 0.7rem 0.9rem; border-radius: 8px; font-size: 0.83rem; margin-bottom: 1rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('cred_error') }}
            </div>
        @endif

        @if(session('cred_reset'))
            <div style="background: var(--success-soft); color: var(--text-success); border: 1px solid var(--success-border); padding: 0.7rem 0.9rem; border-radius: 8px; font-size: 0.83rem; margin-bottom: 1rem;">
                <i class="fa-solid fa-rotate-left"></i> {{ session('cred_reset') }}
            </div>
        @endif

        @if(session('cred_otp_sent'))
            <div style="background: var(--accent-soft); color: var(--text-accent); border: 1px solid var(--info-border); padding: 0.7rem 0.9rem; border-radius: 8px; font-size: 0.83rem; margin-bottom: 1rem;">
                <i class="fa-solid fa-paper-plane"></i> {{ session('cred_otp_sent') }}
            </div>
        @endif

        @if($errors->has('super_admin_password') || $errors->has('new_email') || $errors->has('otp'))
            <div style="background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); padding: 0.7rem 0.9rem; border-radius: 8px; font-size: 0.83rem; margin-bottom: 1rem;">
                <ul style="margin: 0; padding-left: 1.1rem;">
                    @foreach(['super_admin_password', 'new_email', 'otp'] as $credField)
                        @error($credField)
                            <li>{{ $message }}</li>
                        @enderror
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- MASKED CURRENT CREDENTIALS (anti shoulder-surfing) --}}
        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 mb-4">
            <p class="text-xs text-gray-500">Current Email: <strong class="text-gray-900" style="letter-spacing: 0.5px;">{{ $maskedEmail }}</strong></p>
            <p style="font-size: 0.7rem; color: var(--text-subtle); margin: 0.45rem 0 0;"><i class="fa-solid fa-user-secret"></i> Values are masked to prevent shoulder surfing.</p>
        </div>

        @if($credOtpPending)
            {{-- STEP 2: OTP CONFIRMATION --}}
            <form action="{{ route('super-admin.credentials.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="new_email" value="{{ old('new_email') }}">

                <div style="margin-bottom: 0.85rem;">
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0 0 0.6rem;">
                        Confirm the pending change:
                        @if(old('new_email'))
                            <span style="display: inline-block; background: var(--accent-soft); color: var(--text-accent); border: 1px solid var(--info-border); border-radius: 6px; padding: 0.15rem 0.6rem; font-weight: 700; margin-top: 0.35rem;">
                                Email &rarr; {{ old('new_email') }}
                            </span>
                        @else
                            Nothing to apply.
                        @endif
                    </p>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">6-Digit Security Code *</label>
                    <input type="text" name="otp" value="{{ old('otp') }}" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="e.g. 123456" autocomplete="one-time-code" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid {{ $errors->has('otp') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: 8px; font-size: 1.15rem; letter-spacing: 8px; font-weight: 800; text-align: center;">
                    <p style="font-size: 0.72rem; color: var(--text-muted); margin: 0.35rem 0 0;"><i class="fa-solid fa-envelope"></i> Code sent to your current email: <strong>{{ $maskedEmail }}</strong></p>
                </div>

                <div style="margin-bottom: 1.1rem;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Current Password * <span style="font-weight: 500; color: var(--text-subtle);">(required again to apply)</span></label>
                    <div style="position: relative;">
                        <input type="password" name="super_admin_password" id="input-sa-cred-pass-confirm" required autocomplete="current-password" placeholder="Enter your current password" style="width: 100%; padding: 0.6rem 2.5rem 0.6rem 0.85rem; border: 1px solid {{ $errors->has('super_admin_password') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: 8px; font-size: 0.9rem;">
                        <button type="button" onclick="togglePasswordVisibility('input-sa-cred-pass-confirm', 'eye-icon-sa-cred-pass-confirm')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                            <i class="fa-solid fa-eye" id="eye-icon-sa-cred-pass-confirm"></i>
                        </button>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="background: var(--cmyk-cyan); border: none;">
                        <i class="fa-solid fa-lock"></i> Verify &amp; Update Email
                    </button>
                </div>
            </form>

            <div style="margin-top: 0.85rem; text-align: center;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0 0 0.5rem;">Code not arriving? Check your spam folder — or cancel and request a new one.</p>
                <form action="{{ route('super-admin.credentials.cancel') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-rotate-left"></i> Cancel &amp; start over
                    </button>
                </form>
            </div>
        @else
            {{-- STEP 1: REQUEST OTP (password re-auth gate) --}}
            <form action="{{ route('super-admin.credentials.send-otp') }}" method="POST">
                @csrf
                <div style="margin-bottom: 0.85rem;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">New Email Address *</label>
                    <input type="email" name="new_email" value="{{ old('new_email') }}" required placeholder="Enter your new email address" autocomplete="off" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid {{ $errors->has('new_email') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: 8px; font-size: 0.9rem;">
                </div>

                {{-- AUTOMATIC EMAIL DELIVERY NOTICE --}}
                <div style="margin-bottom: 0.85rem; padding: 0.65rem 0.8rem; background: var(--accent-soft); border: 1px solid var(--info-border); border-radius: 8px; display: flex; align-items: center; gap: 0.6rem;">
                    <i class="fa-solid fa-envelope" style="color: var(--text-accent); font-size: 1.05rem; flex-shrink: 0;"></i>
                    <p style="font-size: 0.78rem; color: var(--text-accent); margin: 0; line-height: 1.4;">
                        Security verification code will be sent automatically to your current email: <strong>{{ $maskedEmail }}</strong>
                    </p>
                </div>

                <div style="margin-bottom: 0.85rem; border: 1px solid var(--brand-magenta-soft); border-radius: 10px; padding: 0.75rem 0.85rem; background: var(--brand-magenta-soft);">
                    <label style="font-size: 0.82rem; font-weight: 800; color: var(--text-magenta); display: block; margin-bottom: 0.3rem;">
                        <i class="fa-solid fa-key"></i> Current Password * <span style="font-weight: 500;">(mandatory security check)</span>
                    </label>
                    <div style="position: relative;">
                        <input type="password" name="super_admin_password" id="input-sa-cred-pass-request" required autocomplete="current-password" placeholder="Confirm your current password first" style="width: 100%; padding: 0.6rem 2.5rem 0.6rem 0.85rem; border: 1px solid {{ $errors->has('super_admin_password') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: 8px; font-size: 0.9rem;">
                        <button type="button" onclick="togglePasswordVisibility('input-sa-cred-pass-request', 'eye-icon-sa-cred-pass-request')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                            <i class="fa-solid fa-eye" id="eye-icon-sa-cred-pass-request"></i>
                        </button>
                    </div>
                    <p style="font-size: 0.72rem; color: var(--text-magenta); margin: 0.4rem 0 0;">
                        Your current password is required to change your email address. A 6-digit code will be sent to your email after this check passes.
                    </p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeEditModal('modal-sa-credentials')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: var(--cmyk-cyan); border: none;">
                        <i class="fa-solid fa-paper-plane"></i> Send Security Code
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
