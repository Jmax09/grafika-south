
<?php
    $sa = auth()->user();

    // Mask email: keep first 2 chars + domain tail (Str::mask count is bounded to stay valid)
    $atPos = ($sa && $sa->email) ? strpos($sa->email, '@') : false;
    $maskedEmail = (!$sa || !$sa->email)
        ? '—'
        : ($atPos > 2
            ? \Illuminate\Support\Str::mask($sa->email, '*', 2, $atPos - 2)
            : \Illuminate\Support\Str::mask($sa->email, '*', 1));

    $hasPendingPassword = is_string(session('sa_pending_new_password')) && session('sa_pending_new_password') !== '';
    $hasPendingEmail = is_string(session('sa_pending_new_email')) && session('sa_pending_new_email') !== '';

    // Auto-open only right after a real action (flash/error), so a plain page
    // refresh does NOT keep reopening the modal. The persistent pending flags are
    // used only to pick Step 1 vs Step 2 (see $pwOtpPending / $credOtpPending),
    // so manually reopening the modal resumes an in-progress OTP verification.
    $hasPwAction = session('pw_otp_sent') || session('pw_error') || session('pw_reset')
        || $errors->has('new_password');
    $hasCredAction = session('cred_otp_sent') || session('cred_error') || session('cred_reset')
        || $errors->has('new_email') || $errors->has('otp_current') || $errors->has('otp_new');

    $isModalOpen = $hasPwAction || $hasCredAction || $errors->has('super_admin_password') || $errors->has('otp');

    // Super Admins cannot change their own email — the Update Email tab is
    // rendered only for Admin/Staff. Admin and Staff keep the full flow.
    $canUpdateEmail = $sa && !$sa->isSuperAdmin();

    $defaultTab = ($hasCredAction && $canUpdateEmail) ? 'email' : 'password';

    // Stay on the OTP (Step 2) screen whenever a change is still pending, so an
    // incorrect code shows "try again" here instead of dropping back to Step 1.
    $pwOtpPending = (bool) session('pw_otp_sent') || $hasPendingPassword;
    $credOtpPending = (bool) session('cred_otp_sent') || $hasPendingEmail;
?>

<div class="edit-modal-backdrop <?php echo e($isModalOpen ? 'active' : ''); ?>" id="modal-sa-security">
    <div class="edit-modal-box" style="max-width: 520px;" x-data="{ tab: '<?php echo e($defaultTab); ?>' }">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-shield-halved" style="color: var(--text-magenta);"></i> Security &amp; Credentials
            </h3>
            <button type="button" onclick="closeEditModal('modal-sa-security')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--text-subtle); line-height: 1;">&times;</button>
        </div>

        
        <div style="padding: 0.75rem 0.9rem; background: var(--bg-page); border: 1px solid var(--border-color); border-radius: 10px; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block;">Registered Account Email</span>
                <strong style="font-size: 0.88rem; color: var(--text-main); letter-spacing: 0.5px;"><?php echo e($maskedEmail); ?></strong>
            </div>
        </div>

        
        <div style="display: flex; gap: 0.4rem; margin-bottom: 1.15rem; background: var(--bg-subtle); padding: 0.25rem; border-radius: 10px;">
            <button type="button"
                    @click="tab = 'password'"
                    :style="tab === 'password' ? 'flex: 1; padding: 0.5rem 0.75rem; border: none; background: var(--bg-surface); color: var(--text-accent); font-weight: 800; font-size: 0.85rem; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: center; gap: 0.45rem;' : 'flex: 1; padding: 0.5rem 0.75rem; border: none; background: transparent; color: var(--text-muted); font-weight: 700; font-size: 0.85rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.45rem;'">
                <i class="fa-solid fa-key"></i> Change Password
                <?php if($pwOtpPending): ?>
                    <span style="width: 7px; height: 7px; background: var(--brand-magenta-fg); border-radius: 50%; display: inline-block;"></span>
                <?php endif; ?>
            </button>
            <?php if($canUpdateEmail): ?>
            <button type="button"
                    @click="tab = 'email'"
                    :style="tab === 'email' ? 'flex: 1; padding: 0.5rem 0.75rem; border: none; background: var(--bg-surface); color: var(--text-accent); font-weight: 800; font-size: 0.85rem; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: center; gap: 0.45rem;' : 'flex: 1; padding: 0.5rem 0.75rem; border: none; background: transparent; color: var(--text-muted); font-weight: 700; font-size: 0.85rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.45rem;'">
                <i class="fa-solid fa-envelope"></i> Update Email
                <?php if($credOtpPending): ?>
                    <span style="width: 7px; height: 7px; background: var(--brand-magenta-fg); border-radius: 50%; display: inline-block;"></span>
                <?php endif; ?>
            </button>
            <?php endif; ?>
        </div>


        
        <div x-show="tab === 'password'" x-cloak>
            
            <?php if(session('pw_error')): ?>
                <div style="background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.82rem; margin-bottom: 0.9rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo e(session('pw_error')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('pw_reset')): ?>
                <div style="background: var(--success-soft); color: var(--text-success); border: 1px solid var(--success-border); padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.82rem; margin-bottom: 0.9rem;">
                    <i class="fa-solid fa-rotate-left"></i> <?php echo e(session('pw_reset')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('pw_otp_sent')): ?>
                <div style="background: var(--accent-soft); color: var(--text-accent); border: 1px solid var(--info-border); padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.82rem; margin-bottom: 0.9rem;">
                    <i class="fa-solid fa-paper-plane"></i> <?php echo e(session('pw_otp_sent')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->has('new_password') || ($errors->has('super_admin_password') && $defaultTab === 'password')): ?>
                <div style="background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.82rem; margin-bottom: 0.9rem;">
                    <ul style="margin: 0; padding-left: 1.1rem;">
                        <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <?php $__errorArgs = ['super_admin_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if($pwOtpPending): ?>
                
                <form action="<?php echo e(route('super-admin.password.update')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">6-Digit Security Code *</label>
                        <input type="text" name="otp" value="<?php echo e(old('otp')); ?>" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="e.g. 123456" autocomplete="one-time-code" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid <?php echo e($errors->has('otp') ? 'var(--danger-color)' : 'var(--border-color)'); ?>; border-radius: 8px; font-size: 1.15rem; letter-spacing: 8px; font-weight: 800; text-align: center;">
                        <p style="font-size: 0.72rem; color: var(--text-muted); margin: 0.35rem 0 0;"><i class="fa-solid fa-envelope"></i> Code sent to your registered email: <strong><?php echo e($maskedEmail); ?></strong></p>
                    </div>

                    <div style="display: flex; justify-content: space-between; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary" style="background: var(--accent-color); border: none; width: 100%; justify-content: center;">
                            <i class="fa-solid fa-lock"></i> Verify &amp; Change Password
                        </button>
                    </div>
                </form>

                <div style="margin-top: 0.85rem;">
                    <form action="<?php echo e(route('super-admin.password.cancel')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-outline" style="width: 100%; justify-content: center;">
                            <i class="fa-solid fa-rotate-left"></i> Cancel &amp; start over
                        </button>
                    </form>
                </div>
            <?php else: ?>
                
                <form action="<?php echo e(route('super-admin.password.send-otp')); ?>" method="POST" x-data="{ showPass: false }">
                    <?php echo csrf_field(); ?>

                    <div style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">New Password *</label>
                        <div style="position: relative;">
                            <input :type="showPass ? 'text' : 'password'" name="new_password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required style="width: 100%; padding: 0.6rem 2.5rem 0.6rem 0.85rem; border: 1px solid <?php echo e($errors->has('new_password') ? 'var(--danger-color)' : 'var(--border-color)'); ?>; border-radius: 8px; font-size: 0.9rem;">
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

                    
                    <div style="margin-bottom: 0.85rem; padding: 0.65rem 0.8rem; background: var(--accent-soft); border: 1px solid var(--info-border); border-radius: 8px; display: flex; align-items: center; gap: 0.6rem;">
                        <i class="fa-solid fa-envelope" style="color: var(--text-accent); font-size: 1.05rem; flex-shrink: 0;"></i>
                        <p style="font-size: 0.78rem; color: var(--text-accent); margin: 0; line-height: 1.4;">
                            Security verification code will be sent automatically to: <strong><?php echo e($maskedEmail); ?></strong>
                        </p>
                    </div>

                    
                    <div style="margin-bottom: 1rem; border: 1px solid var(--brand-magenta-soft); border-radius: 10px; padding: 0.75rem 0.85rem; background: var(--brand-magenta-soft);">
                        <label style="font-size: 0.82rem; font-weight: 800; color: var(--text-magenta); display: block; margin-bottom: 0.3rem;">
                            <i class="fa-solid fa-key"></i> Current Password
                        </label>
                        <div style="position: relative;">
                            <input type="password" name="super_admin_password" id="input-sec-pw-pass-request" required autocomplete="new-password" placeholder="Confirm your current password first" style="width: 100%; padding: 0.6rem 2.5rem 0.6rem 0.85rem; border: 1px solid <?php echo e($errors->has('super_admin_password') ? 'var(--danger-color)' : 'var(--border-color)'); ?>; border-radius: 8px; font-size: 0.9rem;">
                            <button type="button" onclick="togglePasswordVisibility('input-sec-pw-pass-request', 'eye-icon-sec-pw-pass-request')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                                <i class="fa-solid fa-eye" id="eye-icon-sec-pw-pass-request"></i>
                            </button>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.5rem;">
                        <button type="button" class="btn btn-outline" onclick="closeEditModal('modal-sa-security')">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background: var(--accent-color); border: none;">
                            <i class="fa-solid fa-paper-plane"></i> Send Security Code
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>


        <?php if($canUpdateEmail): ?>
        
        <div x-show="tab === 'email'" x-cloak>
            
            <?php if(session('cred_error')): ?>
                <div style="background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.82rem; margin-bottom: 0.9rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo e(session('cred_error')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('cred_reset')): ?>
                <div style="background: var(--success-soft); color: var(--text-success); border: 1px solid var(--success-border); padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.82rem; margin-bottom: 0.9rem;">
                    <i class="fa-solid fa-rotate-left"></i> <?php echo e(session('cred_reset')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('cred_otp_sent')): ?>
                <div style="background: var(--accent-soft); color: var(--text-accent); border: 1px solid var(--info-border); padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.82rem; margin-bottom: 0.9rem;">
                    <i class="fa-solid fa-paper-plane"></i> <?php echo e(session('cred_otp_sent')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->has('new_email') || ($errors->has('super_admin_password') && $defaultTab === 'email')): ?>
                <div style="background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.82rem; margin-bottom: 0.9rem;">
                    <ul style="margin: 0; padding-left: 1.1rem;">
                        <?php $__errorArgs = ['new_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <?php $__errorArgs = ['super_admin_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if($credOtpPending): ?>
                
                <form action="<?php echo e(route('super-admin.credentials.update')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <input type="hidden" name="new_email" value="<?php echo e(session('sa_pending_new_email')); ?>">

                    <div style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Code sent to your <strong>current</strong> email *</label>
                        <input type="text" name="otp_current" value="<?php echo e(old('otp_current')); ?>" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="e.g. 123456" autocomplete="one-time-code" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid <?php echo e($errors->has('otp_current') ? 'var(--danger-color)' : 'var(--border-color)'); ?>; border-radius: 8px; font-size: 1.15rem; letter-spacing: 8px; font-weight: 800; text-align: center;">
                        <p style="font-size: 0.72rem; color: var(--text-muted); margin: 0.35rem 0 0;"><i class="fa-solid fa-envelope"></i> Sent to your current email: <strong><?php echo e($maskedEmail); ?></strong></p>
                    </div>

                    <div style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Code sent to your <strong>new</strong> email *</label>
                        <input type="text" name="otp_new" value="<?php echo e(old('otp_new')); ?>" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="e.g. 123456" autocomplete="one-time-code" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid <?php echo e($errors->has('otp_new') ? 'var(--danger-color)' : 'var(--border-color)'); ?>; border-radius: 8px; font-size: 1.15rem; letter-spacing: 8px; font-weight: 800; text-align: center;">
                        <p style="font-size: 0.72rem; color: var(--text-muted); margin: 0.35rem 0 0;"><i class="fa-solid fa-envelope"></i> Sent to your new email: <strong><?php echo e(session('sa_pending_new_email')); ?></strong></p>
                    </div>

                    <div style="display: flex; justify-content: space-between; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary" style="background: var(--accent-color); border: none; width: 100%; justify-content: center;">
                            <i class="fa-solid fa-lock"></i> Verify &amp; Update Email
                        </button>
                    </div>
                </form>

                <div style="margin-top: 0.85rem;">
                    <form action="<?php echo e(route('super-admin.credentials.cancel')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-outline" style="width: 100%; justify-content: center;">
                            <i class="fa-solid fa-rotate-left"></i> Cancel &amp; start over
                        </button>
                    </form>
                </div>
            <?php else: ?>
                
                <form action="<?php echo e(route('super-admin.credentials.send-otp')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">New Email Address *</label>
                        <input type="email" name="new_email" value="<?php echo e(old('new_email')); ?>" required placeholder="Enter your new email address" autocomplete="off" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid <?php echo e($errors->has('new_email') ? 'var(--danger-color)' : 'var(--border-color)'); ?>; border-radius: 8px; font-size: 0.9rem;">
                    </div>

                    
                    <div style="margin-bottom: 0.85rem; padding: 0.65rem 0.8rem; background: var(--accent-soft); border: 1px solid var(--info-border); border-radius: 8px; display: flex; align-items: center; gap: 0.6rem;">
                        <i class="fa-solid fa-envelope" style="color: var(--text-accent); font-size: 1.05rem; flex-shrink: 0;"></i>
                        <p style="font-size: 0.78rem; color: var(--text-accent); margin: 0; line-height: 1.4;">
                            A security code will be sent to <strong>both</strong> your current email and the new email address above. You will enter both codes to confirm the change.
                        </p>
                    </div>

                    
                    <div style="margin-bottom: 1rem; border: 1px solid var(--brand-magenta-soft); border-radius: 10px; padding: 0.75rem 0.85rem; background: var(--brand-magenta-soft);">
                        <label style="font-size: 0.82rem; font-weight: 800; color: var(--text-magenta); display: block; margin-bottom: 0.3rem;">
                            <i class="fa-solid fa-key"></i> Current Password
                        </label>
                        <div style="position: relative;">
                            <input type="password" name="super_admin_password" id="input-sec-cred-pass-request" required autocomplete="new-password" placeholder="Confirm your current password first" style="width: 100%; padding: 0.6rem 2.5rem 0.6rem 0.85rem; border: 1px solid <?php echo e($errors->has('super_admin_password') ? 'var(--danger-color)' : 'var(--border-color)'); ?>; border-radius: 8px; font-size: 0.9rem;">
                            <button type="button" onclick="togglePasswordVisibility('input-sec-cred-pass-request', 'eye-icon-sec-cred-pass-request')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted);">
                                <i class="fa-solid fa-eye" id="eye-icon-sec-cred-pass-request"></i>
                            </button>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.5rem;">
                        <button type="button" class="btn btn-outline" onclick="closeEditModal('modal-sa-security')">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background: var(--accent-color); border: none;">
                            <i class="fa-solid fa-paper-plane"></i> Send Security Code
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\grafika-south\resources\views/super_admin/users/security-modal.blade.php ENDPATH**/ ?>