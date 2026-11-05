@extends('layouts.app')

@section('title', 'Register New Admin - Grafika South')

@push('styles')
<style>
    .form-wrapper {
        max-width: 600px;
        margin: 1.5rem auto;
    }
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-group label {
        display: block;
        font-weight: 600;
        font-size: 0.88rem;
        margin-bottom: 0.4rem;
        color: var(--text-main);
    }
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.95rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(45, 45, 45, 0.12);
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
</style>
@endpush

@section('content')
<div class="form-wrapper">
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('super-admin.dashboard') }}" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
            <i class="fa-solid fa-arrow-left"></i> Back to Super Admin Dashboard
        </a>
    </div>

    <div class="card">
        <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
            <h2 style="font-size: 1.5rem; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-user-plus" style="color: var(--highlight-color);"></i> Register New Admin Account
            </h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">
                Create a new staff admin user with management privileges.
            </p>
        </div>

        <form action="{{ route('super-admin.admins.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name"><i class="fa-solid fa-user"></i> Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Maria Santos">
                @error('name')
                    <span style="color: var(--highlight-color); font-size: 0.83rem; margin-top: 0.3rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="staff.admin@grafika.com">
                @error('email')
                    <span style="color: var(--highlight-color); font-size: 0.83rem; margin-top: 0.3rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password"><i class="fa-solid fa-lock"></i> Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                    @error('password')
                        <span style="color: var(--highlight-color); font-size: 0.83rem; margin-top: 0.3rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation"><i class="fa-solid fa-lock"></i> Confirm Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="••••••••">
                </div>
            </div>

            <div class="form-group">
                <label for="phone"><i class="fa-solid fa-phone"></i> Phone Number (Optional)</label>
                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="0917XXXXXXX">
                @error('phone')
                    <span style="color: var(--highlight-color); font-size: 0.83rem; margin-top: 0.3rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="address"><i class="fa-solid fa-location-dot"></i> Address (Optional)</label>
                <textarea id="address" name="address" class="form-control" rows="2" placeholder="San Pedro, Laguna">{{ old('address') }}</textarea>
                @error('address')
                    <span style="color: var(--highlight-color); font-size: 0.83rem; margin-top: 0.3rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-highlight" style="flex: 1; justify-content: center;">
                    <i class="fa-solid fa-user-check"></i> Register Admin User
                </button>
                <a href="{{ route('super-admin.dashboard') }}" class="btn btn-secondary" style="justify-content: center;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
