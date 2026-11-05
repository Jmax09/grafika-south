@extends('layouts.app')

@section('title', 'Set New Password | Grafika South')

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
        max-width: 480px;
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

    .form-group-unicorp {
        margin-bottom: 1.4rem;
    }

    .form-label-unicorp {
        display: block;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.45rem;
    }

    .input-icon-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon-wrap i {
        position: absolute;
        left: 1.1rem;
        color: var(--text-subtle);
        font-size: 1.05rem;
        pointer-events: none;
    }

    .form-control-unicorp {
        width: 100%;
        padding: 0.85rem 1.1rem 0.85rem 3rem;
        font-size: 1rem;
        font-family: inherit;
        border: 2px solid var(--border-color);
        border-radius: 11px;
        background: var(--bg-surface);
        color: var(--text-main);
        transition: all 0.2s ease;
    }

    .form-control-unicorp:focus {
        outline: none;
        border-color: var(--cmyk-cyan);
        box-shadow: 0 0 0 4px rgba(0, 153, 255, 0.15);
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
        margin-top: 1rem;
    }

    .btn-submit-unicorp:hover {
        background: var(--accent-color);
        transform: translateY(-2px);
    }

    .auth-footer-text {
        text-align: center;
        margin-top: 1.25rem;
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
        .form-group-unicorp { margin-bottom: 0.85rem; }
        .form-label-unicorp { font-size: 0.85rem; margin-bottom: 0.3rem; }
        .form-control-unicorp { padding: 0.6rem 0.9rem 0.6rem 2.5rem; font-size: 0.9rem; }
        .btn-submit-unicorp { padding: 0.6rem 1.25rem; font-size: 0.9rem; margin-top: 0.5rem; }
        .auth-footer-text { margin-top: 0.6rem; font-size: 0.78rem; }
    }
</style>
@endpush

@section('content')
<div class="auth-page-container">
    <div class="auth-card-unicorp">
        <div class="auth-header-block">
            <div class="auth-logo-wrap">
                <img src="{{ asset('images/logo.png') }}" alt="Grafika South Logo" class="auth-logo-img">
            </div>
            <h1 class="auth-heading">Set New Password</h1>
            <p class="auth-subheading">Create a strong new password for <strong>{{ $email }}</strong></p>
        </div>

        @if(session('error'))
            <div class="alert alert-error" style="margin-bottom: 1.25rem; padding: 0.75rem 1rem; font-size: 0.9rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 1.25rem; padding: 0.75rem 1rem; font-size: 0.9rem;">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf

            <!-- New Password -->
            <div class="form-group-unicorp">
                <label for="password" class="form-label-unicorp">New Password <span style="color: var(--brand-magenta-fg);">*</span></label>
                <div class="input-icon-wrap">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control-unicorp" placeholder="At least 8 characters" required autofocus>
                </div>
                @if(isset($errors) && $errors->has('password'))
                    <span style="color: var(--text-danger); font-size: 0.82rem; margin-top: 0.3rem; display: block;">{{ $errors->first('password') }}</span>
                @endif
            </div>

            <!-- Confirm New Password -->
            <div class="form-group-unicorp">
                <label for="password_confirmation" class="form-label-unicorp">Confirm New Password <span style="color: var(--brand-magenta-fg);">*</span></label>
                <div class="input-icon-wrap">
                    <i class="fa-solid fa-check-double"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control-unicorp" placeholder="Repeat new password" required>
                </div>
            </div>

            <button type="submit" class="btn-submit-unicorp">
                <i class="fa-solid fa-key"></i> Update Password & Sign In
            </button>
        </form>

        <div class="auth-footer-text">
            Remembered your password? <a href="{{ route('login') }}">Sign In</a>
        </div>
    </div>
</div>
@endsection
