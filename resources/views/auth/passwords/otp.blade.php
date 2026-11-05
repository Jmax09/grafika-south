@extends('layouts.app')

@section('title', 'Enter OTP Code | Grafika South')

@push('styles')
<style>
    /* Page-scoped (this block is pushed only on this view): drop the layout container's vertical
       margins so the card anchors to the area below the sticky header instead of stacking past it. */
    body > .container {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .auth-page-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.9rem;
        padding: 0.75rem 0;
        box-sizing: border-box;
        /* 65px = 5px CMYK process strip + 60px navbar */
        min-height: calc(100vh - 65px);
        min-height: calc(100dvh - 65px);
    }

    .auth-card-unicorp {
        background: var(--bg-surface);
        border: 2px solid var(--border-color);
        border-radius: 18px;
        width: 100%;
        max-width: 500px;
        padding: 2rem 2.25rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
    }

    .auth-header-block {
        text-align: center;
        margin-bottom: 1.4rem;
    }

    .auth-logo-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem auto;
        width: 68px;
        height: 68px;
        border-radius: 14px;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        padding: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
        box-sizing: border-box;
    }

    .auth-logo-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 10px;
    }

    .auth-heading {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.5px;
        margin-bottom: 0.35rem;
    }

    .auth-subheading {
        font-size: 0.95rem;
        color: var(--text-muted);
        line-height: 1.5;
    }

    .otp-digits-container {
        display: flex;
        gap: 0.6rem;
        justify-content: center;
        margin: 1.5rem 0;
    }

    .otp-digit-input {
        width: 58px;
        height: 66px;
        font-size: 1.8rem;
        font-weight: 800;
        text-align: center;
        font-family: 'Courier New', Courier, monospace;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-page);
        color: var(--text-main);
        transition: all 0.2s ease;
    }

    .otp-digit-input:focus {
        outline: none;
        border-color: var(--cmyk-cyan);
        background: var(--bg-surface);
        box-shadow: 0 0 0 4px rgba(0, 153, 255, 0.18);
    }

    .btn-submit-unicorp {
        width: 100%;
        padding: 0.95rem 1.5rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-inverse);
        background: var(--cmyk-cyan);
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(0, 153, 255, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-submit-unicorp:hover {
        background: var(--accent-color);
        transform: translateY(-2px);
    }

    .auth-footer-text {
        text-align: center;
        margin-top: 0.9rem;
        font-size: 0.9rem;
        color: var(--text-muted);
    }

    .auth-footer-text a {
        color: var(--brand-cyan-fg);
        font-weight: 700;
        text-decoration: none;
    }

    /* Short windows (laptops at 768px tall and below): fall back to the compact scale so the
       enlarged card still clears the fold without a scrollbar. */
    @media (max-height: 800px) {
        .auth-card-unicorp { max-width: 420px; padding: 1.15rem 1.25rem; }
        .auth-logo-wrap { width: 44px; height: 44px; margin-bottom: 0.5rem; padding: 5px; }
        .auth-heading { font-size: 1.2rem; }
        .auth-subheading { font-size: 0.85rem; }
        .auth-header-block { margin-bottom: 0.75rem; }
        .otp-digits-container { gap: 0.4rem; margin: 0.75rem 0; }
        .otp-digit-input { width: 42px; height: 48px; font-size: 1.2rem; border-radius: 10px; }
        .btn-submit-unicorp { padding: 0.6rem 1.25rem; font-size: 0.9rem; }
        .auth-footer-text { margin-top: 0.6rem; font-size: 0.78rem; }
    }
</style>
@endpush

@section('content')
<div class="auth-page-container">
    <div style="width: 100%; max-width: 500px;">
        <a href="{{ route('password.request') }}" class="btn btn-secondary" style="font-size: 0.95rem; padding: 0.5rem 1.05rem; border-radius: 9999px;">
            <i class="fa-solid fa-arrow-left"></i> Change Email
        </a>
    </div>

    <div class="auth-card-unicorp">
        <div class="auth-header-block">
            <div class="auth-logo-wrap">
                <img src="{{ asset('images/logo.png') }}" alt="Grafika South Logo" class="auth-logo-img">
            </div>
            <h1 class="auth-heading">Verify 6-Digit OTP</h1>
            <p class="auth-subheading">Enter the code sent to your inbox &mdash; check the spam folder too:</p>
            <div style="background: var(--bg-subtle); border: 1px solid var(--border-color); border-radius: 9px; padding: 0.45rem 0.9rem; font-weight: 700; font-size: 0.95rem; color: var(--brand-cyan-fg); margin-top: 0.6rem; word-break: break-all; display: inline-block;">
                <i class="fa-solid fa-envelope"></i> {{ $email }}
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-error" style="margin-bottom: 1.25rem; padding: 0.75rem 1rem; font-size: 0.9rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        {{-- The "OTP sent" success flash is deliberately not re-rendered here: the layout already
             shows it as a top-right toast, and the email chip above carries the same information. --}}

        <form action="{{ route('password.otp.verify') }}" method="POST" id="otpForm">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="otp" id="fullOtpInput" value="">

            <div class="otp-digits-container">
                <input type="text" class="otp-digit-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autofocus required>
                <input type="text" class="otp-digit-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-digit-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-digit-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-digit-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-digit-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            </div>

            <button type="submit" class="btn-submit-unicorp">
                <i class="fa-solid fa-shield-halved"></i> Verify Code & Continue
            </button>
        </form>

        <div style="margin-top: 1.25rem; text-align: center; font-size: 0.9rem; color: var(--text-muted);">
            Didn't receive the code?
            <form action="{{ route('password.email') }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <button type="submit" style="background: none; border: none; color: var(--brand-cyan-fg); font-weight: 700; cursor: pointer; text-decoration: underline; padding: 0; font-size: inherit;">
                    Resend OTP
                </button>
            </form>
        </div>

        <div class="auth-footer-text">
            Remembered your password? <a href="{{ route('login') }}">Sign In</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const digitInputs = document.querySelectorAll('.otp-digit-input');
    const fullOtpInput = document.getElementById('fullOtpInput');
    const otpForm = document.getElementById('otpForm');

    function updateFullOtp() {
        let code = '';
        digitInputs.forEach(input => code += input.value);
        fullOtpInput.value = code;
    }

    digitInputs.forEach((input, index) => {
        input.addEventListener('input', function (e) {
            let value = e.target.value;
            // Only allow digits
            if (!/^\d*$/.test(value)) {
                e.target.value = '';
                return;
            }

            if (value.length >= 1) {
                if (index < digitInputs.length - 1) {
                    digitInputs[index + 1].focus();
                }
            }
            updateFullOtp();
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                digitInputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            if (pasteData) {
                pasteData.split('').forEach((char, i) => {
                    if (digitInputs[i]) {
                        digitInputs[i].value = char;
                    }
                });
                if (digitInputs[Math.min(pasteData.length, 5)]) {
                    digitInputs[Math.min(pasteData.length, 5)].focus();
                }
                updateFullOtp();
            }
        });
    });

    otpForm.addEventListener('submit', function (e) {
        updateFullOtp();
        if (fullOtpInput.value.length < 6) {
            e.preventDefault();
            alert('Please enter all 6 digits of the OTP code.');
        }
    });
});
</script>
@endpush
