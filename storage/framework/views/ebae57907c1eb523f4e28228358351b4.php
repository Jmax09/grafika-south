<?php
    $cart = session()->get('cart', []);
    $cartCount = is_array($cart) ? count($cart) : 0;
    $unreadNotifCount = 0;
    if (Auth::check()) {
        $unreadNotifCount = \App\Models\Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }
?>

<!-- MOBILE NAVIGATION SLIDE-OUT DRAWER & BACKDROP -->
<div class="mobile-drawer-overlay fixed inset-0 bg-black/50 z-[10000]" id="mobileDrawerOverlay"></div>

<div class="mobile-nav-drawer fixed top-0 right-0 w-80 max-w-[85vw] h-full z-[10001] shadow-2xl flex flex-col" id="mobileNavDrawer" aria-label="Mobile Navigation Menu">
    <!-- Drawer Header -->
    <div class="mobile-drawer-header">
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <button type="button" class="mobile-drawer-back" onclick="closeMobileDrawer(); if (history.length > 1) { history.back(); } else { window.location.href = '<?php echo e(route('landing')); ?>'; }" aria-label="Go Back" title="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
            <a href="<?php echo e(url('/')); ?>" style="display: flex; align-items: center; text-decoration: none;" aria-label="Grafika South Home">
                <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Grafika South Logo" style="height: 32px; width: auto; object-fit: contain; border-radius: 6px;">
            </a>
        </div>
        <button type="button" class="mobile-drawer-close" id="mobileDrawerCloseBtn" aria-label="Close Navigation Menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Drawer Body (Clean Minimal List with pb-8) -->
    <div class="mobile-drawer-body flex-1 overflow-y-auto p-4 space-y-1 pb-8 flex flex-col">
        <?php if(!empty($darkModeEnabled)): ?>
            <?php echo $__env->make('partials.theme-toggle', ['variant' => 'drawer'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="mobile-drawer-divider"></div>
        <?php endif; ?>
        <?php if(auth()->guard()->check()): ?>
            <!-- User Account Info Card (No Orange Client Badge) -->
            <div class="mobile-drawer-user-card mb-3 flex items-center gap-3">
                <div class="nav-profile-thumb shrink-0" style="width: 38px; height: 38px; font-size: 0.95rem;">
                    <?php if(Auth::user()->avatar): ?>
                        <img src="<?php echo e(asset('storage/' . Auth::user()->avatar)); ?>" alt="<?php echo e(Auth::user()->name); ?>">
                    <?php else: ?>
                        <span><?php echo e(strtoupper(substr(Auth::user()->name, 0, 1))); ?></span>
                    <?php endif; ?>
                </div>
                <div class="mobile-drawer-user-info min-w-0 flex-1">
                    <div class="mobile-drawer-user-name truncate font-bold text-sm"><?php echo e(Auth::user()->name); ?></div>
                    <div class="mobile-drawer-user-email truncate break-all text-xs" title="<?php echo e(Auth::user()->email); ?>"><?php echo e(Auth::user()->email); ?></div>
                </div>
                <?php if(Auth::user()->isSuperAdmin()): ?>
                    <span class="role-badge badge-super-admin shrink-0" style="font-size: 0.65rem;">Super</span>
                <?php elseif(Auth::user()->isAdmin()): ?>
                    <span class="role-badge badge-admin shrink-0" style="font-size: 0.65rem;">Staff</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- 1. Home -->
        <a href="<?php echo e(route('landing')); ?>" class="mobile-drawer-link <?php echo e(request()->routeIs('landing') ? 'active' : ''); ?>">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>

        <!-- 2. Products Catalog -->
        <a href="<?php echo e(route('products.index')); ?>" class="mobile-drawer-link <?php echo e(request()->routeIs('products.*') ? 'active' : ''); ?>">
            <i class="fa-solid fa-layer-group"></i>
            <span>Products Catalog</span>
        </a>

        <!-- 3. Shopping Cart -->
        <a href="<?php echo e(route('cart.index')); ?>" class="mobile-drawer-link <?php echo e(request()->routeIs('cart.*') ? 'active' : ''); ?>">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Shopping Cart</span>
            <span class="nav-cart-badge <?php echo e($cartCount > 0 ? 'has-items' : ''); ?>" style="margin-left: auto;">
                <?php echo e($cartCount); ?>

            </span>
        </a>

        <!-- 4. Track & Trace Order -->
        <a href="<?php echo e(route('orders.track')); ?>" class="mobile-drawer-link <?php echo e(request()->routeIs('orders.track') ? 'active' : ''); ?>" onclick="closeMobileDrawer(); if(event.ctrlKey || event.metaKey) return; event.preventDefault(); openTrackOrderModal();">
            <i class="fa-solid fa-magnifying-glass-location"></i>
            <span>Track &amp; Trace Order</span>
        </a>

        <?php if(auth()->guard()->check()): ?>
            <?php if(Auth::user()->isSuperAdmin()): ?>
                <div class="mobile-drawer-divider my-2"></div>
                <a href="<?php echo e(route('super-admin.dashboard')); ?>" class="mobile-drawer-link <?php echo e(request()->routeIs('super-admin.*') ? 'active' : ''); ?>" style="color: var(--brand-magenta-fg);">
                    <i class="fa-solid fa-shield-halved" style="color: var(--brand-magenta-fg);"></i>
                    <span>Super Admin Panel</span>
                </a>
            <?php elseif(Auth::user()->isAdmin()): ?>
                <div class="mobile-drawer-divider my-2"></div>
                <a href="<?php echo e(Auth::user()->getFirstAccessibleRoute()); ?>" class="mobile-drawer-link <?php echo e((request()->routeIs('admin.*') || request()->routeIs('super-admin.hero.*')) ? 'active' : ''); ?>" style="color: var(--brand-cyan-fg);">
                    <i class="fa-solid fa-briefcase" style="color: var(--brand-cyan-fg);"></i>
                    <span>Staff Admin Panel</span>
                </a>
            <?php else: ?>
                <div class="mobile-drawer-divider my-2"></div>

                <!-- 5. My Orders (inactive while the To Rate tab is open) -->
                <a href="<?php echo e(route('customer.dashboard')); ?>" class="mobile-drawer-link <?php echo e(request()->routeIs('customer.dashboard') && request('tab') !== 'to-rate' ? 'active' : ''); ?>">
                    <i class="fa-regular fa-clipboard"></i>
                    <span>My Orders</span>
                </a>

                <!-- 5b. To Rate (dedicated tab for pending product reviews) -->
                <a href="<?php echo e(route('customer.dashboard', ['tab' => 'to-rate'])); ?>" class="mobile-drawer-link <?php echo e(request()->routeIs('customer.dashboard') && request('tab') === 'to-rate' ? 'active' : ''); ?>">
                    <i class="fa-solid fa-star" style="color: var(--warning-color);"></i>
                    <span>To Rate</span>
                    <?php if(Auth::user()->to_rate_count > 0): ?>
                        <span class="notif-badge-pill" style="margin-left: auto; background: var(--warning-color);"><?php echo e(Auth::user()->to_rate_count); ?></span>
                    <?php endif; ?>
                </a>

                <!-- 6. Profile Settings -->
                <a href="<?php echo e(route('customer.profile')); ?>" class="mobile-drawer-link <?php echo e(request()->routeIs('customer.profile') ? 'active' : ''); ?>">
                    <i class="fa-regular fa-user"></i>
                    <span>Profile Settings</span>
                </a>

                <!-- 7. Notifications -->
                <a href="<?php echo e(route('customer.notifications')); ?>" class="mobile-drawer-link <?php echo e(request()->routeIs('customer.notifications') ? 'active' : ''); ?>">
                    <i class="fa-regular fa-bell"></i>
                    <span>Notifications</span>
                    <?php if($unreadNotifCount > 0): ?>
                        <span class="notif-badge-pill" style="margin-left: auto;"><?php echo e($unreadNotifCount); ?></span>
                    <?php endif; ?>
                </a>

                <!-- 8. Support Tickets -->
                <a href="<?php echo e(route('customer.tickets.index')); ?>" class="mobile-drawer-link <?php echo e(str_contains(request()->route() ? request()->route()->getName() : '', 'customer.tickets') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-headset"></i>
                    <span>Support Tickets</span>
                </a>
            <?php endif; ?>

            <!-- Subtle Divider -->
            <div class="mobile-drawer-divider my-2"></div>

            <!-- 9. Sign Out -->
            <form action="<?php echo e(route('logout')); ?>" method="POST" class="pt-1" onsubmit="return confirm('Are you sure you want to logout?');">
                <?php echo csrf_field(); ?>
                <button type="submit" class="mobile-drawer-link" style="width: 100%; border: none; background: var(--danger-soft); color: var(--danger-color); cursor: pointer; border-radius: 10px; font-weight: 700;">
                    <i class="fa-solid fa-arrow-right-from-bracket" style="color: var(--danger-color);"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        <?php else: ?>
            <!-- Guest Customer Support -->
            <a href="<?php echo e(route('support')); ?>" class="mobile-drawer-link <?php echo e(request()->routeIs('customer.tickets.*') || request()->routeIs('admin.tickets.*') ? 'active' : ''); ?>">
                <i class="fa-solid fa-headset"></i>
                <span>Customer Support</span>
            </a>

            <!-- Subtle Divider -->
            <div class="mobile-drawer-divider my-2"></div>

            <!-- Guest Auth Section -->
            <div style="display: flex; flex-direction: column; gap: 0.6rem; padding: 0.5rem 0.25rem;">
                <a href="<?php echo e(route('login')); ?>" class="btn btn-outline" style="width: 100%; justify-content: center; font-weight: 700; border-radius: 10px;">
                    <i class="fa-solid fa-right-to-bracket"></i> Login to Account
                </a>
                <a href="<?php echo e(route('register')); ?>" class="btn btn-primary" style="width: 100%; justify-content: center; font-weight: 700; border-radius: 10px;">
                    <i class="fa-solid fa-user-plus"></i> Create Free Account
                </a>
            </div>
        <?php endif; ?>

        <!-- Drawer Footer Info inside scroll container with clean pb-8 spacing -->
        <div class="mobile-drawer-footer mt-auto pt-4 text-xs pb-8">
            <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.35rem;">
                <i class="fa-solid fa-phone" style="color: var(--accent-color); font-size: 0.75rem;"></i>
                <span>+63 962 292 7736</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-location-dot" style="color: var(--brand-magenta-fg); font-size: 0.75rem;"></i>
                <span>San Pedro, Laguna, Philippines</span>
            </div>
        </div>
    </div>

    <!-- CMYK Process Strip at Bottom of Drawer -->
    <div class="cmyk-process-strip" style="height: 4px; flex-shrink: 0;">
        <div class="cmyk-strip-c"></div>
        <div class="cmyk-strip-m"></div>
        <div class="cmyk-strip-y"></div>
        <div class="cmyk-strip-k"></div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\grafika-south\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>