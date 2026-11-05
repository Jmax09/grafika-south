@php
    $user = Auth::user();
    $unreadNotifCount = 0;
    if ($user) {
        $unreadNotifCount = \App\Models\Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }
    $cart = session()->get('cart', []);
    $cartCount = is_array($cart) ? count($cart) : 0;

    $currentRoute = request()->route() ? request()->route()->getName() : '';
@endphp

@once('customer-sidebar-drawer')
<style>
    /* Completely hide static in-page customer sidebar across all viewports for full-screen desktop experience */
    .customer-sidebar,
    aside.customer-sidebar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        overflow: hidden !important;
        visibility: hidden !important;
    }

    .customer-main-content {
        width: 100% !important;
        max-width: 100% !important;
        flex: 1 1 100% !important;
    }

    /* Offcanvas Customer Drawer Backdrop */
    .customer-drawer-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.48);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 10000;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.25s ease, visibility 0.25s ease;
    }

    .customer-drawer-backdrop.is-open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    /* Offcanvas Customer Drawer Panel (Slide in from Right) */
    .customer-drawer {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: auto;
        width: 320px;
        max-width: 86vw;
        background: var(--bg-surface);
        z-index: 10001;
        transform: translateX(100%);
        transition: transform 0.26s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: -10px 0 40px -10px rgba(15, 23, 42, 0.28);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .customer-drawer.is-open {
        transform: translateX(0);
    }

    /* Drawer Header */
    .customer-drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.15rem 1.15rem 1rem 1.15rem;
        background: linear-gradient(180deg, var(--bg-page) 0%, var(--bg-surface) 100%);
        border-bottom: 1px solid var(--border-color);
        position: relative;
    }

    .customer-drawer-user {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
        flex: 1;
    }

    .customer-drawer-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: var(--accent-color);
        color: var(--text-inverse);
        font-weight: 800;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 153, 255, 0.25);
        border: 2px solid var(--border-color);
    }

    .customer-drawer-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .customer-drawer-user-info {
        min-width: 0;
        flex: 1;
    }

    .customer-drawer-name {
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0 0 2px 0;
    }

    .customer-drawer-close-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: transparent;
        border: 1px solid transparent;
        color: var(--text-muted);
        font-size: 1.05rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.18s ease;
        flex-shrink: 0;
        margin-left: 0.5rem;
    }

    .customer-drawer-close-btn:hover {
        background: var(--bg-subtle);
        color: var(--text-main);
        border-color: var(--border-color);
    }

    /* Drawer Body / Menu items */
    .customer-drawer-body {
        flex: 1;
        overflow-y: auto;
        padding: 0.85rem 0.75rem;
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .customer-drawer-section-title {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-subtle);
        padding: 0.4rem 0.75rem 0.25rem;
    }

    .drawer-menu-item {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.75rem 0.9rem;
        border-radius: 10px;
        color: var(--text-muted);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.92rem;
        transition: background-color 0.18s ease, color 0.18s ease;
        position: relative;
    }

    .drawer-menu-item i {
        width: 1.25rem;
        text-align: center;
        font-size: 1rem;
        flex-shrink: 0;
        color: var(--text-muted);
        transition: color 0.18s ease;
    }

    .drawer-menu-item:hover {
        background: var(--bg-page);
        color: var(--text-main);
    }

    .drawer-menu-item:hover i {
        color: var(--text-accent);
    }

    .drawer-menu-item.active {
        background: var(--accent-soft);
        color: var(--text-accent);
        font-weight: 700;
    }

    .drawer-menu-item.active i {
        color: var(--text-accent);
    }

    .drawer-badge-pill {
        background: var(--brand-magenta-fg);
        color: var(--text-inverse);
        font-size: 0.75rem;
        font-weight: 800;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: auto;
    }

    .drawer-divider {
        height: 1px;
        background: var(--text-disabled);
        margin: 0.65rem 0.5rem;
    }

    .drawer-menu-item.logout-item {
        color: var(--text-danger);
        width: 100%;
        border: none;
        background: transparent;
        cursor: pointer;
        text-align: left;
    }

    .drawer-menu-item.logout-item i {
        color: var(--text-danger);
    }

    .drawer-menu-item.logout-item:hover {
        background: var(--danger-soft);
        color: var(--text-danger);
    }

    .drawer-menu-item.logout-item:hover i {
        color: var(--text-danger);
    }

    /* Drawer Footer */
    .customer-drawer-footer {
        padding: 0.9rem 1.15rem;
        border-top: 1px solid var(--border-color);
        background: var(--bg-page);
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.78rem;
        color: var(--text-subtle);
        font-weight: 600;
    }

    .customer-drawer-no-scroll {
        overflow: hidden !important;
    }

    /* Logout Confirmation Modal Styling */
    .logout-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        animation: fadeInBackdrop 0.2s ease-out;
    }

    @keyframes fadeInBackdrop {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .logout-modal-card {
        background: var(--bg-surface);
        border-radius: 16px;
        width: 100%;
        max-width: 440px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border: 1px solid var(--border-color);
        overflow: hidden;
        animation: slideUpModal 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 1.5rem;
    }

    @keyframes slideUpModal {
        from { transform: translateY(12px) scale(0.98); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }

    .logout-modal-header {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .logout-modal-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--danger-soft);
        color: var(--text-danger);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
        border: 1px solid var(--danger-border);
    }

    .logout-modal-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--text-main);
        margin: 0 0 0.25rem 0;
    }

    .logout-modal-subtitle {
        font-size: 0.88rem;
        color: var(--text-muted);
        margin: 0;
        line-height: 1.45;
        font-weight: 600;
    }

    .logout-modal-body {
        background: var(--bg-page);
        border-radius: 10px;
        padding: 0.85rem 1rem;
        border: 1px solid var(--border-color);
        margin-bottom: 1.25rem;
    }

    .logout-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    .logout-btn-cancel {
        padding: 0.6rem 1.25rem;
        border-radius: 10px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-muted);
        font-weight: 700;
        font-size: 0.88rem;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .logout-btn-cancel:hover {
        background: var(--bg-subtle);
        color: var(--text-main);
        border-color: var(--border-strong);
    }

    .logout-btn-confirm {
        padding: 0.6rem 1.35rem;
        border-radius: 10px;
        border: none;
        background: var(--danger-color);
        color: var(--text-inverse);
        font-weight: 700;
        font-size: 0.88rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
        transition: all 0.18s ease;
    }

    .logout-btn-confirm:hover {
        background: var(--danger-color);
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35);
    }
</style>

{{-- Offcanvas Backdrop --}}
<div id="customerDrawerBackdrop" 
     class="customer-drawer-backdrop" 
     onclick="closeCustomerDrawer()" 
     aria-hidden="true"></div>

{{-- Offcanvas Customer Drawer Panel --}}
<aside id="customerDrawer" 
       class="customer-drawer" 
       role="dialog" 
       aria-modal="true" 
       aria-label="Customer Navigation Drawer">

    {{-- Drawer Header with User Profile --}}
    @php
        // Show only the first name in the drawer header
        $drawerFirstName = trim($user?->first_name ?: strtok($user?->name ?? '', ' ') ?: 'Customer');
    @endphp
    <div class="customer-drawer-header">
        <div class="customer-drawer-user">
            <div class="customer-drawer-avatar">
                @if($user?->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $drawerFirstName }}">
                @else
                    <span>{{ strtoupper(substr($drawerFirstName, 0, 1)) }}</span>
                @endif
            </div>
            <div class="customer-drawer-user-info">
                <h4 class="customer-drawer-name">{{ $drawerFirstName }}</h4>
            </div>
        </div>
        <button type="button" 
                class="customer-drawer-close-btn" 
                onclick="closeCustomerDrawer()" 
                aria-label="Close menu" 
                title="Close menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{-- Drawer Body Links --}}
    <div class="customer-drawer-body">
        <div class="customer-drawer-section-title">Account Menu</div>

        {{-- 1. Profile Settings (At the very top) --}}
        <a href="{{ route('customer.profile') }}" class="drawer-menu-item {{ $currentRoute === 'customer.profile' ? 'active' : '' }}">
            <i class="fa-regular fa-user"></i>
            <span>Profile Settings</span>
        </a>

        {{-- 2. My Orders --}}
        <a href="{{ route('customer.dashboard') }}" class="drawer-menu-item {{ $currentRoute === 'customer.dashboard' && request('tab') !== 'to-rate' ? 'active' : '' }}">
            <i class="fa-regular fa-clipboard"></i>
            <span>My Orders</span>
        </a>

        {{-- 3. To Rate (Yellow color removed from star) --}}
        <a href="{{ route('customer.dashboard', ['tab' => 'to-rate']) }}" class="drawer-menu-item {{ $currentRoute === 'customer.dashboard' && request('tab') === 'to-rate' ? 'active' : '' }}">
            <i class="fa-regular fa-star"></i>
            <span>To Rate</span>
            @if($user && $user->to_rate_count > 0)
                <span class="drawer-badge-pill">{{ $user->to_rate_count }}</span>
            @endif
        </a>

        {{-- 4. Track & Trace --}}
        <a href="{{ route('orders.track') }}" class="drawer-menu-item {{ request()->routeIs('orders.track') ? 'active' : '' }}" onclick="closeCustomerDrawer(); if(event.ctrlKey || event.metaKey) return; event.preventDefault(); openTrackOrderModal();">
            <i class="fa-solid fa-magnifying-glass-location"></i>
            <span>Track &amp; Trace</span>
        </a>

        {{-- 5. Notifications --}}
        <a href="{{ route('customer.notifications') }}" class="drawer-menu-item {{ $currentRoute === 'customer.notifications' ? 'active' : '' }}">
            <i class="fa-regular fa-bell"></i>
            <span>Notifications</span>
            @if($unreadNotifCount > 0)
                <span class="drawer-badge-pill">{{ $unreadNotifCount }}</span>
            @endif
        </a>

        {{-- 6. Support Tickets --}}
        <a href="{{ route('customer.tickets.index') }}" class="drawer-menu-item {{ str_contains($currentRoute, 'customer.tickets') || request()->routeIs('support') ? 'active' : '' }}">
            <i class="fa-solid fa-headset"></i>
            <span>Support Tickets</span>
        </a>

        @if($user && ($user->isSuperAdmin() || $user->isAdmin() || $user->isStaff()))
            <div class="drawer-divider"></div>
            @if($user->isSuperAdmin())
                <a href="{{ route('super-admin.dashboard') }}" class="drawer-menu-item {{ request()->routeIs('super-admin.*') ? 'active' : '' }}" style="color: var(--text-magenta);">
                    <i class="fa-solid fa-shield-halved" style="color: var(--text-magenta);"></i>
                    <span>Super Admin Panel</span>
                </a>
            @else
                <a href="{{ $user->getFirstAccessibleRoute() ?? url('/') }}" class="drawer-menu-item {{ (request()->routeIs('admin.*') || request()->routeIs('super-admin.hero.*')) ? 'active' : '' }}" style="color: var(--text-accent);">
                    <i class="fa-solid fa-briefcase" style="color: var(--text-accent);"></i>
                    <span>Staff Admin Panel</span>
                </a>
            @endif
        @endif

        {{-- Divider --}}
        <div class="drawer-divider"></div>

        {{-- 5. Logout Trigger --}}
        <button type="button" class="drawer-menu-item logout-item" onclick="openLogoutModal()">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Logout</span>
        </button>
    </div>

    {{-- Drawer Footer --}}
    <div class="customer-drawer-footer">
        <span class="flex items-center gap-1.5">
            <i class="fa-solid fa-shield-halved text-sky-500"></i>
            <span>Grafika South Portal</span>
        </span>
        <div class="flex items-center gap-1">
            <span style="width: 7px; height: 7px; border-radius: 50%; background: var(--accent-color); display: inline-block;"></span>
            <span style="width: 7px; height: 7px; border-radius: 50%; background: var(--brand-magenta-fg); display: inline-block;"></span>
            <span style="width: 7px; height: 7px; border-radius: 50%; background: var(--warning-color); display: inline-block;"></span>
            <span style="width: 7px; height: 7px; border-radius: 50%; background: var(--text-main); display: inline-block;"></span>
        </div>
    </div>
</aside>

{{-- Hidden Logout Form --}}
<form id="customer-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

{{-- LOGOUT CONFIRMATION MODAL --}}
<div id="logoutConfirmationModal" class="logout-modal-backdrop" onclick="if(event.target === this) closeLogoutModal()">
    <div class="logout-modal-card">
        <div class="logout-modal-header">
            <div class="logout-modal-icon">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </div>
            <div>
                <h3 class="logout-modal-title">Log Out Confirmation</h3>
                <p class="logout-modal-subtitle">Are you sure you want to log out of your Grafika South account?</p>
            </div>
        </div>

        <div class="logout-modal-body">
            <p style="font-size: 0.84rem; color: var(--text-muted); margin: 0; line-height: 1.5;">
                <i class="fa-solid fa-shield-halved" style="color: var(--text-accent); margin-right: 4px;"></i>
                Your active session will be ended securely. You can log back in anytime with your registered credentials.
            </p>
        </div>

        <div class="logout-modal-actions">
            <button type="button" class="logout-btn-cancel" onclick="closeLogoutModal()">
                Cancel
            </button>
            <button type="button" class="logout-btn-confirm" onclick="confirmLogout()">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Yes, Log Out
            </button>
        </div>
    </div>
</div>

<script>
function openCustomerDrawer() {
    const drawer = document.getElementById('customerDrawer');
    const backdrop = document.getElementById('customerDrawerBackdrop');
    if (drawer) drawer.classList.add('is-open');
    if (backdrop) backdrop.classList.add('is-open');
    document.body.classList.add('customer-drawer-no-scroll');
}

function closeCustomerDrawer() {
    const drawer = document.getElementById('customerDrawer');
    const backdrop = document.getElementById('customerDrawerBackdrop');
    if (drawer) drawer.classList.remove('is-open');
    if (backdrop) backdrop.classList.remove('is-open');
    document.body.classList.remove('customer-drawer-no-scroll');
}

function toggleCustomerDrawer() {
    const drawer = document.getElementById('customerDrawer');
    if (drawer && drawer.classList.contains('is-open')) {
        closeCustomerDrawer();
    } else {
        openCustomerDrawer();
    }
}

function openLogoutModal() {
    closeCustomerDrawer();
    const modal = document.getElementById('logoutConfirmationModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutConfirmationModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function confirmLogout() {
    const form = document.getElementById('customer-logout-form');
    if (form) {
        form.submit();
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('logoutConfirmationModal');
        if (modal && modal.style.display === 'flex') {
            closeLogoutModal();
            return;
        }
        closeCustomerDrawer();
    }
});
</script>
@endonce
