@extends('layouts.app')

@section('title', 'Set Your New Password | Grafika South')

@section('content')
@php $fcUser = Auth::user(); @endphp
<div style="max-width: 520px; margin: 2.5rem auto; padding: 0 1rem;">
    <div class="card" style="padding: 2.25rem; background: var(--bg-surface); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); text-align: center;">

        <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--accent-soft); color: var(--brand-cyan-fg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; font-size: 1.8rem;">
            <i class="fa-solid fa-key"></i>
        </div>

        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem; font-family: 'Outfit', sans-serif;">
            Set Your New Password
        </h1>

        <p style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 0.5rem;">
            Welcome, <strong>{{ $fcUser ? $fcUser->name : 'there' }}</strong>! For your security, please replace your temporary password with a new one of your own before continuing.
        </p>

        @if (session('warning'))
            <div style="background: var(--warning-soft); border: 1px solid var(--warning-border); border-radius: 10px; padding: 0.75rem 1rem; margin: 1rem 0 0; text-align: left; font-size: 0.84rem; color: var(--text-warning); display: flex; gap: 0.6rem; align-items: flex-start;">
                <i class="fa-solid fa-triangle-exclamation" style="color: var(--text-warning); margin-top: 2px; flex-shrink: 0;"></i>
                <div>{{ session('warning') }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.force.change.update') }}" style="margin-top: 1.75rem; text-align: left;">
            @csrf

            <label for="new_password" class="field-label" style="display:block; font-weight:700; font-size:0.85rem; color:var(--text-main); margin-bottom:0.4rem;">New Password</label>
            <div style="position: relative; margin-bottom: 0.5rem;">
                <input type="password" id="new_password" name="new_password" autocomplete="new-password" required
                       placeholder="At least 8 characters"
                       style="width:100%; padding:0.8rem 2.6rem 0.8rem 0.9rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.95rem;">
                <button type="button" onclick="toggleFcPw('new_password', this)" style="position:absolute; right:0.7rem; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1rem;">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            @error('new_password')
                <span style="color:var(--text-danger); font-size:0.8rem;"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
            @enderror

            <label for="new_password_confirmation" class="field-label" style="display:block; font-weight:700; font-size:0.85rem; color:var(--text-main); margin:1.1rem 0 0.4rem;">Confirm New Password</label>
            <div style="position: relative;">
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" autocomplete="new-password" required
                       placeholder="Re-enter your new password"
                       style="width:100%; padding:0.8rem 2.6rem 0.8rem 0.9rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.95rem;">
                <button type="button" onclick="toggleFcPw('new_password_confirmation', this)" style="position:absolute; right:0.7rem; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1rem;">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            @error('new_password_confirmation')
                <span style="color:var(--text-danger); font-size:0.8rem;"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
            @enderror

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1.75rem; padding: 0.85rem 1.5rem; font-size: 0.95rem; background: var(--cmyk-cyan); border-color: var(--cmyk-cyan);">
                <i class="fa-solid fa-shield-halved"></i> Save Password &amp; Continue
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="margin-top: 0.75rem;" onsubmit="return confirm('Are you sure you want to log out?');">
            @csrf
            <button type="submit" class="btn btn-outline" style="width: 100%; padding: 0.7rem 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
                <i class="fa-solid fa-right-from-bracket"></i> Log Out
            </button>
        </form>
    </div>
</div>

<script>
function toggleFcPw(id, btn) {
    var input = document.getElementById(id);
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection
