@extends('layouts.app')

@section('title', 'Verify Your Email Address - Grafika South')

@section('content')
<div style="max-width: 580px; margin: 2.5rem auto; padding: 0 1rem;">
    <div class="card" style="padding: 2.25rem; background: var(--bg-surface); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); text-align: center;">
        
        <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--accent-soft); color: var(--brand-cyan-fg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; font-size: 1.8rem;">
            <i class="fa-solid fa-envelope-circle-check"></i>
        </div>

        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem; font-family: 'Outfit', sans-serif;">
            Verify Your Email Address
        </h1>

        <p style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 1.5rem;">
            Thanks for registering with <strong>Grafika South</strong>! Before accessing your dashboard features, please verify your email address by clicking on the link sent to:
        </p>

        @if(Auth::check())
            <div style="background: var(--bg-page); border: 1px solid var(--border-color); border-radius: 10px; padding: 0.85rem 1.25rem; font-weight: 700; font-size: 1.05rem; color: var(--brand-cyan-fg); margin-bottom: 1.75rem; word-break: break-all;">
                <i class="fa-solid fa-at"></i> {{ Auth::user()->email }}
            </div>
        @endif

        <div style="background: var(--warning-soft); border: 1px solid var(--warning-border); border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1.75rem; text-align: left; font-size: 0.84rem; color: var(--text-warning); display: flex; gap: 0.6rem; align-items: flex-start;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 1rem; color: var(--text-warning); margin-top: 2px; flex-shrink: 0;"></i>
            <div>
                <strong>Dashboard Access Restricted:</strong> Unverified accounts cannot view order history, profile settings, or place online orders. Verification emails are sent directly from <strong>grafikasouth69@gmail.com</strong>.
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success" style="margin-bottom: 1.5rem; font-size: 0.88rem; text-align: left;">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error" style="margin-bottom: 1.5rem; font-size: 0.88rem; text-align: left;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @auth
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.8rem 1.5rem; font-size: 0.95rem; background: var(--cmyk-cyan); border-color: var(--cmyk-cyan);">
                        <i class="fa-solid fa-paper-plane"></i> Resend Verification Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Are you sure you want to logout?');">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="width: 100%; padding: 0.7rem 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
                        <i class="fa-solid fa-right-from-bracket"></i> Log Out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary" style="width: 100%; padding: 0.8rem 1.5rem; font-size: 0.95rem;">
                    <i class="fa-solid fa-right-to-bracket"></i> Log In to Your Account
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection
