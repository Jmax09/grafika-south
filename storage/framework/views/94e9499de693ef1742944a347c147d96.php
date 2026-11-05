<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Grafika South - Custom Print Shop Ordering Platform'); ?></title>

    <link rel="icon" type="image/png" href="<?php echo e(asset('images/favicon.png')); ?>">
    <link rel="apple-touch-icon" type="image/png" href="<?php echo e(asset('images/favicon.png')); ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo e(asset('images/favicon.png')); ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS (Preflight Disabled to preserve existing custom styles) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,
            },
            theme: {
                extend: {
                    // Neutral scales resolve to the theme tokens so utility
                    // markup (bg-gray-50, text-slate-400, border-slate-200)
                    // themes together with the rest of the UI.
                    // 900/950 stay literal - they are used as dark backdrops.
                    colors: {
                        slate: {
                            50: 'var(--bg-surface-2)',
                            100: 'var(--bg-subtle)',
                            200: 'var(--border-color)',
                            300: 'var(--border-strong)',
                            400: 'var(--text-subtle)',
                            500: 'var(--text-muted)',
                            600: 'var(--text-muted)',
                            700: 'var(--text-main)',
                            800: 'var(--text-main)',
                        },
                        gray: {
                            50: 'var(--bg-surface-2)',
                            100: 'var(--bg-subtle)',
                            200: 'var(--border-color)',
                            300: 'var(--border-strong)',
                            400: 'var(--text-subtle)',
                            500: 'var(--text-muted)',
                            600: 'var(--text-muted)',
                            700: 'var(--text-main)',
                            800: 'var(--text-main)',
                        },
                    },
                },
            },
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <?php
        // Dark mode is opt-in per route via the shared controller.
        $darkModeEnabled = true;
    ?>

    <?php echo $__env->make('partials.theme-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        [x-cloak] { display: none !important; }

        /* ============================================================
           DESIGN TOKENS — 3 layers: primitive → semantic → component
           Layer 1 primitives are raw values and never change per theme.
           ============================================================ */
        :root {
            /* ---- Legacy CMYK aliases (kept: other views still use them) ---- */
            --cmyk-cyan: #0099FF;
            --cmyk-cyan-hover: #0080FF;
            --cmyk-cyan-light: #E6F4FF;

            --cmyk-magenta: #EC008C;
            --cmyk-magenta-hover: #C80075;
            --cmyk-magenta-light: #FDE8F5;

            --cmyk-yellow: #EAB308;
            --cmyk-yellow-dark: #CA8A04;
            --cmyk-yellow-light: #FEF9C3;

            --cmyk-key: #0F172A;
            --cmyk-key-light: #1E293B;
        }

        <?php echo $__env->make('partials.design-tokens', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        html {
            overflow-x: clip; /* clip prevents scrollbar but does NOT break position:sticky (unlike overflow:hidden) */
            max-width: 100vw;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: clip; /* clip instead of hidden — preserves sticky positioning on child elements */
        }

        h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; }

        /* CMYK 4-Process Strip Bar */
        .cmyk-process-strip {
            display: flex;
            height: 5px;
            width: 100%;
        }
        .cmyk-strip-c { flex: 1; background-color: var(--cmyk-cyan); }
        .cmyk-strip-m { flex: 1; background-color: var(--cmyk-magenta); }
        .cmyk-strip-y { flex: 1; background-color: var(--cmyk-yellow); }
        .cmyk-strip-k { flex: 1; background-color: var(--cmyk-key); }

        .top-promo-bar {
            background-color: var(--bg-inverse);
            color: var(--text-inverse);
            font-size: 0.82rem;
            font-weight: 600;
            padding: 0.45rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        @media (min-width: 640px) {
            .top-promo-bar { padding: 0.45rem 2rem; }
        }
        @media (min-width: 1280px) {
            .top-promo-bar { padding: 0.45rem 3rem; }
        }

        .top-promo-text {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 auto;
        }

        /* Sticky Site Header */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--header-bg);
            backdrop-filter: saturate(180%) blur(12px);
            -webkit-backdrop-filter: saturate(180%) blur(12px);
            box-shadow: var(--header-shadow);
            width: 100%;
            transition: box-shadow 0.25s ease, background-color 0.25s ease;
        }

        /* Header-scoped colour language. In dark mode the header is a deep
           brand blue, so its contents need their own light-on-blue palette
           rather than the page's text tokens. */
        .site-header .navbar-brand { color: var(--header-text); }
        .site-header .nav-link-item { color: var(--header-text-muted); }
        .site-header .nav-link-item i { color: var(--header-text-muted); }
        .site-header .nav-link-item:hover {
            background-color: var(--header-hover-bg);
            color: var(--header-text);
        }
        .site-header .nav-link-item:hover i { color: var(--header-text); }
        .site-header .nav-link-item.active {
            background-color: var(--header-active-bg);
            color: var(--header-text);
        }
        .site-header .nav-link-item.active i { color: var(--header-text); }
        .site-header .nav-divider { background-color: var(--header-border); }
        .site-header .mobile-nav-btn {
            background: var(--header-control-bg);
            border-color: var(--header-control-border);
            color: var(--header-control-text);
        }
        .site-header .mobile-nav-btn:hover {
            background: var(--header-hover-bg);
            border-color: var(--header-control-border);
            color: var(--header-text);
        }
        .site-header .theme-toggle {
            background: var(--header-control-bg);
            border-color: var(--header-control-border);
            color: var(--header-control-text);
        }
        .site-header .theme-toggle:hover {
            background: var(--header-hover-bg);
            border-color: var(--header-control-border);
            color: var(--header-text);
        }
        /* Light header is white, so the dark wordmark needs no chip — sits flat. */
        .site-header .navbar-brand img {
            background: transparent;
            border-radius: 10px;
            box-sizing: content-box;
            padding: 0;
            box-shadow: none;
            transition: filter 0.25s ease;
        }
        /* Dark mode: no white chip — recolor the wordmark to a light version so it stays readable on the dark header. */
        html.theme-dark .site-header .navbar-brand img {
            background: transparent;
            padding: 0;
            box-shadow: none;
            filter: invert(1) hue-rotate(180deg);
        }

        .navbar {
            background-color: transparent;
            border-bottom: 1px solid var(--header-border);
            padding: 0 1.5rem;
            height: 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            max-width: 1680px;
            margin: 0 auto;
            width: 100%;
        }
        @media (min-width: 640px) {
            .navbar { padding: 0 2rem; }
        }
        @media (min-width: 1280px) {
            .navbar { padding: 0 3rem; }
        }

        /* Mobile Header Breakpoint (< md: 768px) */
        @media (max-width: 767.98px) {
            .navbar {
                padding: 0 1rem !important;
                height: 60px !important;
                flex-wrap: nowrap !important;
            }
            .desktop-nav-group {
                display: none !important;
            }
            .mobile-nav-actions {
                display: flex !important;
            }
        }

        .navbar-brand {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            letter-spacing: -0.5px;
            flex-shrink: 0;
        }

        .navbar-brand img {
            height: 34px;
            max-height: 34px;
            width: auto;
            max-width: 160px;
            object-fit: contain;
            transition: height 0.2s ease, max-width 0.2s ease;
        }

        @media (max-width: 767.98px) {
            .navbar-brand img {
                height: 32px !important;
                max-height: 32px !important;
                max-width: 145px !important;
            }
        }

        @media (max-width: 380px) {
            .navbar-brand img {
                height: 28px !important;
                max-height: 28px !important;
                max-width: 120px !important;
            }
        }

        /* Very narrow phones (320px): shrink the wordmark and tighten the action
           gap so the mobile nav actions are never clipped off-screen. */
        @media (max-width: 359.98px) {
            .navbar-brand img {
                height: 24px !important;
                max-height: 24px !important;
                max-width: 88px !important;
            }
            .mobile-nav-actions {
                gap: 0.3rem;
            }
        }

        /* Mobile Navigation Icons & Hamburger Button */
        .mobile-nav-actions {
            display: none;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .mobile-nav-btn {
            background: var(--bg-surface);
            border: 1.5px solid var(--border-strong);
            color: var(--text-muted);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            cursor: pointer;
            text-decoration: none;
            position: relative;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mobile-nav-btn:hover, .mobile-nav-btn:active {
            background: var(--accent-soft);
            border-color: var(--accent-color);
            color: var(--accent-hover);
            transform: translateY(-1px);
        }

        .mobile-cart-btn .nav-cart-badge {
            position: absolute;
            top: -5px;
            right: -6px;
            font-size: 0.68rem;
            height: 18px;
            min-width: 18px;
            padding: 0 4px;
        }

        /* Slide-out Mobile Navigation Drawer & Backdrop */
        .mobile-drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5); /* bg-black/50 */
            backdrop-filter: blur(4px);
            z-index: 10000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        .mobile-drawer-overlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .mobile-nav-drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: 20rem; /* w-80 (320px) */
            max-width: 85vw; /* max-w-[85vw] */
            height: 100vh;
            height: 100dvh;
            background: var(--bg-surface);
            z-index: 10001;
            box-shadow: -8px 0 32px rgba(15, 23, 42, 0.2);
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .mobile-nav-drawer.is-open {
            transform: translateX(0);
        }

        .mobile-drawer-header {
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-surface);
            flex-shrink: 0;
        }

        .mobile-drawer-close {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            border: 1.5px solid var(--border-strong);
            background: var(--bg-subtle);
            color: var(--text-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .mobile-drawer-close:hover {
            background: var(--danger-soft);
            color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .mobile-drawer-back {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            border: 1.5px solid var(--border-strong);
            background: var(--bg-subtle);
            color: var(--text-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .mobile-drawer-back:hover {
            background: var(--accent-soft);
            color: var(--accent-hover);
            border-color: var(--accent-color);
        }

        .mobile-drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .mobile-drawer-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 0.95rem;
            border-radius: 10px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.94rem;
            transition: all 0.2s ease;
            position: relative;
        }

        .mobile-drawer-link i {
            width: 1.25rem;
            text-align: center;
            font-size: 1rem;
            color: var(--text-subtle);
            transition: color 0.2s ease;
        }

        .mobile-drawer-link:hover, .mobile-drawer-link.active {
            background: var(--accent-soft);
            color: var(--accent-hover);
            font-weight: 700;
        }

        .mobile-drawer-link:hover i, .mobile-drawer-link.active i {
            color: var(--accent-hover);
        }

        .mobile-drawer-divider {
            height: 1px;
            background: var(--border-color);
            margin: 0.6rem 0.5rem;
        }

        .mobile-drawer-user-card {
            background: var(--bg-surface-2);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.85rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .mobile-drawer-user-info {
            flex: 1;
            min-width: 0;
        }

        .mobile-drawer-user-name {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mobile-drawer-user-email {
            font-size: 0.78rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mobile-drawer-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border-color);
            background: var(--bg-surface-2);
            font-size: 0.8rem;
            color: var(--text-muted);
            flex-shrink: 0;
        }

        /* Customer Dashboard Layout - Full Screen on Desktop */
        .customer-dashboard-layout {
            width: 100%;
        }
        .customer-sidebar, aside.customer-sidebar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            overflow: hidden !important;
        }
        .customer-main-content {
            width: 100% !important;
            max-width: 100% !important;
            flex: 1 1 100% !important;
        }
        @media (max-width: 767.98px) {
            .customer-dashboard-layout {
                flex-direction: column !important;
                gap: 1.25rem !important;
            }
        }

        /* Customer Header Burger Menu Button */
        .customer-header-burger-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--bg-surface);
            border: 1.5px solid var(--border-color);
            color: var(--text-muted);
            font-size: 1.05rem;
            cursor: pointer;
            position: relative;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            flex-shrink: 0;
        }

        .customer-header-burger-btn:hover {
            background: var(--accent-soft);
            border-color: var(--accent-color);
            color: var(--accent-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 153, 255, 0.15);
        }

        .customer-header-burger-btn:active {
            transform: translateY(0);
        }

        .customer-header-burger-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--cmyk-magenta);
            border: 2px solid var(--bg-surface);
            box-shadow: 0 0 0 1px var(--cmyk-magenta);
        }

        /* Customer profile picture inside the header menu button */
        .customer-header-avatar {
            width: 100%;
            height: 100%;
            border-radius: 9px;
            object-fit: cover;
            display: block;
        }
        .customer-header-avatar-initial {
            width: 100%;
            height: 100%;
            border-radius: 9px;
            background: var(--accent-soft);
            color: var(--accent-hover);
            font-weight: 800;
            font-size: 0.95rem;
            line-height: 38px;
            text-align: center;
            display: block;
        }

        /* Responsive Layout & Container Utility Tokens */
        .overflow-x-hidden { overflow-x: hidden !important; }
        @media (max-width: 639.98px) {
            .container { padding: 0 1rem !important; margin: 1rem auto !important; }
        }

        .h-10 {
            height: 2.5rem;
        }

        .w-auto {
            width: auto;
        }

        .object-contain {
            object-fit: contain;
        }

        @media (min-width: 768px) {
            .md\:h-12 {
                height: 3rem;
            }
        }

        .navbar-brand .cmyk-dots {
            display: inline-flex;
            gap: 3px;
            align-items: center;
        }

        .cmyk-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }
        .dot-c { background-color: var(--cmyk-cyan); }
        .dot-m { background-color: var(--cmyk-magenta); }
        .dot-y { background-color: var(--cmyk-yellow); }
        .dot-k { background-color: var(--cmyk-key); }

        .nav-items-group {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .nav-divider {
            width: 1px;
            height: 22px;
            background-color: var(--border-color);
            margin: 0 0.35rem;
        }

        .nav-link-item {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            padding: 0.45rem 0.8rem;
            border-radius: 8px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            line-height: 1;
            white-space: nowrap;
        }

        .nav-link-item i {
            font-size: 0.92rem;
            color: var(--text-subtle);
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .nav-link-item:hover {
            background-color: var(--accent-soft);
            color: var(--accent-hover);
            transform: translateY(-1px);
        }

        .nav-link-item:hover i {
            color: var(--accent-hover);
            transform: scale(1.15);
        }

        .nav-link-item:active {
            transform: scale(0.95);
            background-color: var(--accent-soft);
            transition: all 0.08s ease;
        }

        .nav-link-item.active {
            background-color: var(--accent-soft);
            color: var(--accent-hover);
            font-weight: 700;
        }

        .nav-link-item.active i {
            color: var(--accent-hover);
        }

        /* Cart Badge Polish */
        .nav-cart-badge {
            background: var(--brand-magenta-fg);
            color: var(--text-inverse);
            font-size: 0.72rem;
            font-weight: 800;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            box-shadow: 0 2px 6px rgba(236, 0, 140, 0.35);
            transition: transform 0.2s ease;
            margin-left: 2px;
        }

        .nav-link-item:hover .nav-cart-badge {
            transform: scale(1.12);
        }

        /* Profile Thumbnail Polish */
        .nav-profile-link {
            padding: 0.3rem 0.75rem 0.3rem 0.45rem;
            border-radius: 9999px;
        }

        .nav-profile-thumb {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid var(--accent-color);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--accent-soft);
            color: var(--text-accent);
            font-weight: 800;
            font-size: 0.78rem;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 153, 255, 0.2);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .nav-profile-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .nav-link-item:hover .nav-profile-thumb {
            transform: scale(1.08);
            border-color: var(--accent-color);
        }

        .nav-register-btn {
            padding: 0.45rem 1.1rem;
            font-size: 0.85rem;
            border-radius: 9999px;
            margin-left: 0.35rem;
            background: var(--accent-color);
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(0, 153, 255, 0.25);
            transition: all 0.2s ease;
        }

        .nav-register-btn:hover {
            background: var(--accent-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 153, 255, 0.35);
        }

        .role-badge {
            display: inline-block;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-super-admin { background-color: var(--cmyk-magenta); color: var(--text-inverse); }
        .badge-admin { background-color: var(--cmyk-cyan); color: var(--text-inverse); }
        .badge-customer { background-color: var(--cmyk-yellow-dark); color: var(--text-inverse); }

        .btn-logout {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            padding: 0.4rem 0.85rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background-color: var(--cmyk-magenta);
            color: var(--text-inverse);
            border-color: var(--cmyk-magenta);
        }

        /* Admin top navigation bar */
        .admin-topbar {
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border-color);
            padding: 0 2rem;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 200;
            gap: 1rem;
            flex-shrink: 0;
        }

        .admin-topbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            flex-shrink: 0;
        }

        .admin-topbar-brand img {
            height: 32px;
            width: auto;
            object-fit: contain;
            border-radius: 4px;
        }

        .admin-topbar-brand-text {
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.3px;
        }

        .admin-topbar-right {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .admin-topbar-badge {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.28rem 0.75rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .admin-topbar-user {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
        }

        .admin-topbar-divider {
            width: 1px;
            height: 22px;
            background: var(--text-disabled);
            margin: 0 0.15rem;
        }

        .admin-topbar-link {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.38rem 0.85rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.15s;
            white-space: nowrap;
            background: var(--bg-surface);
        }

        .admin-topbar-link:hover {
            background: var(--bg-page);
            border-color: var(--border-color);
            color: var(--text-main);
        }

        .admin-topbar-link.danger {
            border-color: transparent;
            color: var(--text-danger);
        }

        .admin-topbar-link.danger:hover {
            background: var(--danger-soft);
            border-color: var(--danger-border);
            color: var(--text-danger);
        }

        /* Admin & Super Admin Left Sidebar Navigation Panel */
        .admin-portal-layout {
            display: flex;
            gap: 0;
            align-items: flex-start;
            min-height: calc(100vh - 65px);
        }

        .admin-sidebar {
            width: 180px;
            flex-shrink: 0;
            background: var(--bg-surface);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 65px;
            height: calc(100vh - 65px);
            overflow-y: auto;
            z-index: 40;
        }

        /* Role / user header */
        .admin-sidebar-header {
            padding: 0.7rem 0.85rem 0.6rem;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-sidebar-header .role-name {
            font-size: 0.67rem;
            font-weight: 800;
            color: var(--text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.09em;
            display: block;
            margin-bottom: 0.2rem;
        }

        .admin-sidebar-header .user-name {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        /* Nav list */
        .admin-sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 0.05rem;
            flex: 1;
            padding: 0.5rem 0.5rem 0;
        }

        .admin-nav-item {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.5rem 0.65rem;
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.13s ease;
            border-left: 3px solid transparent;
            line-height: 1.3;
        }

        .admin-nav-item i {
            width: 15px;
            text-align: center;
            font-size: 0.85rem;
            flex-shrink: 0;
            color: var(--text-subtle);
            transition: color 0.13s;
        }

        .admin-nav-item:hover {
            background: var(--bg-subtle);
            color: var(--text-main);
            border-left-color: var(--border-color);
        }

        .admin-nav-item:hover i { color: var(--text-accent); }

        .admin-nav-item.active {
            background: var(--accent-soft);
            color: var(--text-accent);
            font-weight: 600;
            border-left-color: var(--accent-color);
        }

        .admin-nav-item.active i { color: var(--text-accent); }

        .admin-nav-divider {
            height: 1px;
            background: var(--bg-subtle);
            margin: 0.4rem 0.25rem;
        }

        /* Click-to-toggle dropdown group (Stocks) */
        .admin-nav-group { position: relative; }
        .admin-nav-parent .admin-nav-caret {
            margin-left: auto;
            font-size: 0.62rem;
            color: var(--text-subtle);
            transition: transform 0.15s ease;
            width: auto;
        }
        .admin-nav-submenu {
            display: none;
            flex-direction: column;
            gap: 0.05rem;
            margin: 0.1rem 0 0 0.65rem;
            padding-left: 0.55rem;
            border-left: 2px solid var(--border-color);
        }
        /* Click-to-toggle: Stocks parent opens/closes the submenu (kept open while inside a child page) */
        .admin-nav-group.open .admin-nav-submenu { display: flex; }
        .admin-nav-group.open .admin-nav-caret { transform: rotate(180deg); }
        /* Opened parent lights up blue like an active item */
        .admin-nav-group.open .admin-nav-parent { background: var(--accent-soft); color: var(--text-accent); }
        .admin-nav-group.open .admin-nav-parent i { color: var(--text-accent); }
        .admin-nav-group.open .admin-nav-parent.active { background: var(--accent-color); color: var(--text-inverse); }
        .admin-nav-group.open .admin-nav-parent.active i { color: var(--text-inverse); }
        .admin-nav-subitem {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.78rem;
            line-height: 1.3;
            transition: all 0.13s ease;
        }
        .admin-nav-subitem i { width: 13px; font-size: 0.72rem; color: var(--text-subtle); flex-shrink: 0; }
        .admin-nav-subitem:hover { background: var(--bg-subtle); color: var(--text-main); }
        .admin-nav-subitem:hover i { color: var(--text-accent); }
        .admin-nav-subitem.active { background: var(--accent-soft); color: var(--text-accent); }
        .admin-nav-subitem.active i { color: var(--text-accent); }
        /* Momentary blue flash when a submenu link is clicked (before navigation) */
        .admin-nav-subitem.nav-pressed { background: var(--accent-color); color: var(--text-inverse); }
        .admin-nav-subitem.nav-pressed i { color: var(--text-inverse); }

        .admin-nav-item.logout-item { color: var(--text-danger); }
        .admin-nav-item.logout-item i { color: var(--text-danger); }
        .admin-nav-item.logout-item:hover {
            background: var(--danger-soft);
            color: var(--text-danger);
            border-left-color: var(--danger-border);
        }
        .admin-nav-item.logout-item:hover i { color: var(--text-danger); }

        .admin-sidebar-logout-wrap {
            padding: 0.5rem 0.5rem 0.75rem;
            border-top: 1px solid var(--border-color);
        }

        .admin-main-content {
            flex: 1;
            min-width: 0;
            padding: 1.75rem 2rem;
        }

        @media (max-width: 992px) {
            .admin-portal-layout { flex-direction: column; min-height: auto; }
            .admin-sidebar {
                width: 100%;
                position: static;
                height: auto;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }
            .admin-main-content { padding: 1.25rem 1rem; }
        }

        .container {
            max-width: 1680px;
            width: 100%;
            margin: 1.75rem auto;
            padding: 0 1.5rem;
            flex: 1;
        }
        @media (min-width: 640px) {
            .container { padding: 0 2rem; }
        }
        @media (min-width: 1280px) {
            .container { padding: 0 3rem; }
        }

        /* Admin pages: container flush, no top margin, sidebar handles its own padding */
        .container.admin-container {
            margin: 0 auto;
            padding: 0;
        }

        .card {
            background: var(--bg-surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            padding: 1.75rem;
            margin-bottom: 1.5rem;
        }

        .alert {
            padding: 0.9rem 1.2rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .alert-success { background-color: var(--accent-soft); color: var(--text-accent); border: 1px solid var(--accent-color); }
        .alert-error { background-color: var(--brand-magenta-soft); color: var(--brand-magenta-fg); border: 1px solid var(--brand-magenta-fg); }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary { background-color: var(--btn-primary-bg); color: var(--btn-primary-text); }
        .btn-primary:hover:not(:disabled) { background-color: var(--btn-primary-bg-hover); }
        .btn:disabled, .btn[disabled] {
            background-color: var(--p-ink-400) !important;
            color: var(--p-ink-100) !important;
            opacity: 0.6;
            cursor: not-allowed !important;
            pointer-events: none;
            box-shadow: none;
        }

        .btn-magenta { background-color: var(--cmyk-magenta); color: var(--text-inverse); }
        .btn-magenta:hover { background-color: var(--cmyk-magenta-hover); }

        .btn-yellow { background-color: var(--cmyk-yellow); color: var(--text-main); font-weight: 700; }
        .btn-yellow:hover { background-color: var(--cmyk-yellow-dark); color: var(--text-inverse); }

        .btn-outline { background-color: var(--bg-surface); color: var(--btn-outline-text); border: 1px solid var(--btn-outline-border); }
        .btn-outline:hover { border-color: var(--accent-color); color: var(--accent-hover); background: var(--btn-outline-bg-hover); }

        /* Universal Pagination SVG & Layout Reset */
        nav[role="navigation"] svg,
        .pagination svg,
        .pagination-btn svg,
        .pagination-num svg,
        svg.w-5.h-5,
        svg.w-4.h-4 {
            width: 1.15rem !important;
            height: 1.15rem !important;
            max-width: 1.15rem !important;
            max-height: 1.15rem !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }

        .unicorp-footer {
            background-color: var(--bg-surface);
            border-top: 1px solid var(--border-color);
            margin-top: auto;
            padding-top: 3rem;
            padding-bottom: 1.5rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
            margin-bottom: 2.5rem;
        }

        .footer-col h4 {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 1rem;
        }

        .footer-col p {
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.88rem;
            transition: color 0.2s;
        }

        .footer-links a:hover { color: var(--accent-hover); }

        .footer-bottom {
            border-top: 1px solid var(--border-color);
            padding-top: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }


        /* ── Track & Trace quick modal (light redesign) ── */
        #trackOrderModal .tt-card {
            background: var(--bg-surface);
            border-radius: 18px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 30px 60px -15px rgba(15,23,42,0.35);
            border: 1px solid var(--border-color);
            overflow: hidden;
            animation: modalFadeIn 0.2s ease-out;
        }
        #trackOrderModal .tt-header {
            padding: 1.5rem 1.6rem 0.4rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            background: transparent;
        }
        #trackOrderModal .tt-header-main { display: flex; align-items: center; gap: 0.75rem; }
        #trackOrderModal .tt-icon-chip {
            width: 44px; height: 44px; border-radius: 13px;
            background: rgba(0,153,255,0.12); color: var(--brand-cyan-fg);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; flex: 0 0 auto;
        }
        #trackOrderModal .tt-title { font-size: 1.25rem; font-weight: 800; margin: 0; color: var(--text-main); letter-spacing: -0.02em; }
        #trackOrderModal .tt-subtitle { font-size: 0.82rem; color: var(--text-muted); margin: 3px 0 0 0; }
        #trackOrderModal .tt-close {
            background: transparent; border: none; color: var(--text-subtle);
            width: 34px; height: 34px; border-radius: 9px; font-size: 1.1rem;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background 0.15s, color 0.15s; flex: 0 0 auto;
        }
        #trackOrderModal .tt-close:hover { background: var(--bg-surface-2); color: var(--text-main); }
        #trackOrderModal .tt-body { padding: 1.1rem 1.6rem 1.6rem; }
        #trackOrderModal .tt-label {
            font-size: 0.82rem; font-weight: 700; color: var(--text-main);
            display: block; margin-bottom: 0.5rem;
        }
        #trackOrderModal .tt-label .tt-req { color: var(--danger-color); }
        #trackOrderModal .tt-field { position: relative; display: flex; align-items: center; }
        #trackOrderModal .tt-field-icon {
            position: absolute; left: 1.05rem; color: var(--text-subtle);
            font-size: 1rem; pointer-events: none;
        }
        #trackOrderModal .tt-input {
            width: 100%; padding: 0.95rem 1.1rem 0.95rem 2.7rem;
            border: 1.5px solid var(--input-border); border-radius: 12px;
            background: var(--input-bg); color: var(--input-text);
            font-size: 1.05rem; font-family: monospace; font-weight: 700;
            text-transform: uppercase; box-sizing: border-box; outline: none;
            transition: border-color 0.18s, box-shadow 0.18s;
        }
        #trackOrderModal .tt-input::placeholder { text-transform: none; font-weight: 500; letter-spacing: 0.02em; color: var(--text-subtle); font-size: 0.95rem; }
        #trackOrderModal .tt-input:focus { border-color: var(--accent-color); box-shadow: 0 0 0 4px rgba(0,153,255,0.14); }
        #trackOrderModal .tt-note {
            display: flex; align-items: flex-start; gap: 0.5rem;
            background: rgba(0,153,255,0.05); border: 1px solid rgba(0,153,255,0.12);
            border-radius: 10px; padding: 0.7rem 0.85rem; margin: 1rem 0 1.3rem;
            font-size: 0.76rem; line-height: 1.45; color: var(--text-muted);
        }
        #trackOrderModal .tt-note i { color: var(--brand-cyan-fg); margin-top: 1px; }
        #trackOrderModal .tt-note code { font-family: monospace; color: var(--text-main); font-weight: 700; }
        #trackOrderModal .tt-btn-primary {
            width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.9rem 1.2rem; font-size: 0.95rem; font-weight: 800;
            border-radius: 12px; cursor: pointer; border: none;
            background: var(--cmyk-cyan); color: var(--text-inverse);
            transition: all 0.16s cubic-bezier(0.4,0,0.2,1);
        }
        #trackOrderModal .tt-btn-primary:hover { background: var(--accent-color); transform: translateY(-1px); }
        #trackOrderModal .tt-btn-primary:active { transform: translateY(0); }
        #trackOrderModal .tt-btn-primary i { font-size: 0.95rem; }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>

    <?php if(!request()->routeIs('admin.login', 'login', 'register')): ?>
    <!-- Sticky Site Header (Full Width with Shadow & Sticky Behavior) -->
    <header class="site-header sticky top-0 z-50 w-full">
        <!-- CMYK 4-Process Bar at Very Top of Header -->
        <div class="cmyk-process-strip">
            <div class="cmyk-strip-c" title="Cyan (C:100 M:0 Y:0 K:0)"></div>
            <div class="cmyk-strip-m" title="Magenta (C:0 M:100 Y:0 K:0)"></div>
            <div class="cmyk-strip-y" title="Yellow (C:0 M:0 Y:100 K:0)"></div>
            <div class="cmyk-strip-k" title="Key/Black (C:0 M:0 Y:0 K:100)"></div>
        </div>

        <?php if(!request()->routeIs('admin.*') && !request()->routeIs('super-admin.*')): ?>
            
            <nav class="navbar flex items-center justify-between w-full">
                <a href="<?php echo e(url('/')); ?>" class="navbar-brand" style="padding: 0;">
                    <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Grafika South Logo">
                </a>

                
                <div class="nav-items-group desktop-nav-group ml-auto flex items-center gap-1">
                    <?php if(auth()->guard()->guest()): ?>
                        <a href="<?php echo e(route('landing')); ?>" class="nav-link-item <?php echo e(request()->routeIs('landing') ? 'active' : ''); ?>">
                            <i class="fa-solid fa-house"></i>
                            <span>Home</span>
                        </a>
                        <div class="nav-divider"></div>
                        <a href="<?php echo e(route('orders.track')); ?>" class="nav-link-item <?php echo e(request()->routeIs('orders.track') ? 'active' : ''); ?>" onclick="if(event.ctrlKey || event.metaKey) return; event.preventDefault(); openTrackOrderModal();">
                            <i class="fa-solid fa-magnifying-glass-location"></i>
                            <span>Track &amp; Trace</span>
                        </a>
                        <div class="nav-divider"></div>
                        <a href="<?php echo e(route('products.index')); ?>" class="nav-link-item <?php echo e(request()->routeIs('products.*') ? 'active' : ''); ?>">
                            <i class="fa-solid fa-layer-group"></i>
                            <span>Catalog</span>
                        </a>
                        <div class="nav-divider"></div>
                        <a href="<?php echo e(route('cart.index')); ?>" class="nav-link-item <?php echo e(request()->routeIs('cart.*') ? 'active' : ''); ?>">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span>Cart</span>
                            <?php $cartCount = count(session('cart', [])); ?>
                            <span class="nav-cart-badge <?php echo e($cartCount > 0 ? 'has-items' : ''); ?>">
                                <?php echo e($cartCount); ?>

                            </span>
                        </a>
                        <div class="nav-divider"></div>
                        <a href="<?php echo e(route('support')); ?>" class="nav-link-item <?php echo e(request()->routeIs('customer.tickets.*') || request()->routeIs('admin.tickets.*') ? 'active' : ''); ?>">
                            <i class="fa-solid fa-headset"></i>
                            <span>Support</span>
                        </a>
                        <div class="nav-divider"></div>
                        <a href="<?php echo e(route('login')); ?>" class="nav-link-item">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span>Login</span>
                        </a>
                        <a href="<?php echo e(route('register')); ?>" class="btn btn-primary nav-register-btn">Register</a>
                    <?php else: ?>
                        <?php if(Auth::user()?->isCustomer() && !Auth::user()?->isSuperAdmin() && !Auth::user()?->isAdmin() && !Auth::user()?->isStaff()): ?>
                        
                        <a href="<?php echo e(route('landing')); ?>" class="nav-link-item <?php echo e(request()->routeIs('landing') ? 'active' : ''); ?>">
                            <i class="fa-solid fa-house"></i>
                            <span>Home</span>
                        </a>
                        <div class="nav-divider"></div>
                        <a href="<?php echo e(route('products.index')); ?>" class="nav-link-item <?php echo e(request()->routeIs('products.*') ? 'active' : ''); ?>">
                            <i class="fa-solid fa-layer-group"></i>
                            <span>Catalog</span>
                        </a>
                        <div class="nav-divider"></div>
                        <a href="<?php echo e(route('cart.index')); ?>" class="nav-link-item <?php echo e(request()->routeIs('cart.*') ? 'active' : ''); ?>">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span>Cart</span>
                            <?php $cartCount = count(session('cart', [])); ?>
                            <span class="nav-cart-badge <?php echo e($cartCount > 0 ? 'has-items' : ''); ?>">
                                <?php echo e($cartCount); ?>

                            </span>
                        </a>

                        
                        <div class="nav-divider"></div>
                        <?php
                            $headerFirstName = trim(Auth::user()?->first_name ?: strtok(Auth::user()?->name ?? '', ' ') ?: 'C');
                        ?>
                        <a href="<?php echo e(route('customer.profile')); ?>"
                           class="customer-header-burger-btn"
                           style="text-decoration:none; margin-right:0.4rem;"
                           aria-label="My Profile"
                           title="My Profile">
                            <?php if(Auth::user()?->avatar): ?>
                                <img src="<?php echo e(asset('storage/' . Auth::user()->avatar)); ?>" alt="<?php echo e($headerFirstName); ?>" class="customer-header-avatar">
                            <?php else: ?>
                                <span class="customer-header-avatar-initial"><?php echo e(strtoupper(substr($headerFirstName, 0, 1))); ?></span>
                            <?php endif; ?>
                        </a>
                        <button type="button"
                                id="customerDrawerBurgerBtn"
                                onclick="toggleCustomerDrawer()"
                                class="customer-header-burger-btn"
                                aria-label="Toggle Customer Menu"
                                title="Customer Menu">
                            <i class="fa-solid fa-bars"></i>
                            <?php
                                $unreadDrawerCount = \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->count();
                            ?>
                            <?php if($unreadDrawerCount > 0): ?>
                                <span class="customer-header-burger-dot"></span>
                            <?php endif; ?>
                        </button>
                        <?php else: ?>
                        
                        <a href="<?php echo e(route('products.index')); ?>" class="nav-link-item <?php echo e(request()->routeIs('products.*') ? 'active' : ''); ?>">
                            <i class="fa-solid fa-layer-group"></i>
                            <span>Catalog</span>
                        </a>
                        <div class="nav-divider"></div>
                        <a href="<?php echo e(route('cart.index')); ?>" class="nav-link-item <?php echo e(request()->routeIs('cart.*') ? 'active' : ''); ?>">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span>Cart</span>
                            <?php $cartCount = count(session('cart', [])); ?>
                            <span class="nav-cart-badge <?php echo e($cartCount > 0 ? 'has-items' : ''); ?>">
                                <?php echo e($cartCount); ?>

                            </span>
                        </a>
                        <div class="nav-divider"></div>
                        <?php if(Auth::user()?->isSuperAdmin()): ?>
                            <a href="<?php echo e(route('super-admin.dashboard')); ?>" class="nav-link-item <?php echo e(request()->routeIs('super-admin.*') ? 'active' : ''); ?>">
                                <i class="fa-solid fa-shield-halved"></i>
                                <span>Super Admin</span>
                            </a>
                        <?php elseif(Auth::user()?->isAdmin()): ?>
                            <a href="<?php echo e(Auth::user()?->getFirstAccessibleRoute() ?? url('/')); ?>" class="nav-link-item <?php echo e(request()->routeIs('admin.*') ? 'active' : ''); ?>">
                                <i class="fa-solid fa-user-shield"></i>
                                <span>Admin</span>
                            </a>
                        <?php elseif(Auth::user()?->isStaff()): ?>
                            <a href="<?php echo e(Auth::user()?->getFirstAccessibleRoute() ?? url('/')); ?>" class="nav-link-item <?php echo e(request()->routeIs('admin.*') ? 'active' : ''); ?>">
                                <i class="fa-solid fa-user-gear"></i>
                                <span>Staff</span>
                            </a>
                        <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($darkModeEnabled && auth()->check()): ?>
                        <div class="nav-divider"></div>
                        <?php echo $__env->make('partials.theme-toggle', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                </div>

                
                <div class="mobile-nav-actions">
                    <?php if($darkModeEnabled && auth()->check()): ?>
                        <?php echo $__env->make('partials.theme-toggle', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                    <?php if(request()->routeIs('landing')): ?>
                    <a href="<?php echo e(route('products.index')); ?>" onclick="if(event.ctrlKey || event.metaKey) return; event.preventDefault(); openSearchModal();" class="bg-sky-500 hover:bg-sky-600 text-white rounded-full p-2.5 shadow-md flex items-center justify-center transition-all focus:outline-none focus:ring-2 focus:ring-sky-400 w-10 h-10 flex-shrink-0" style="text-decoration: none;" aria-label="Search Products" title="Search Products">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('cart.index')); ?>" class="mobile-nav-btn mobile-cart-btn <?php echo e(request()->routeIs('cart.*') ? 'active' : ''); ?>" aria-label="Shopping Cart">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <?php $cartCount = count(session('cart', [])); ?>
                        <span class="nav-cart-badge <?php echo e($cartCount > 0 ? 'has-items' : ''); ?>">
                            <?php echo e($cartCount); ?>

                        </span>
                    </a>
                    <button type="button" class="mobile-nav-btn mobile-hamburger-btn" id="mobileMenuToggleBtn" aria-label="Open Navigation Menu" aria-expanded="false">
                        <i class="fa-solid fa-bars" id="mobileHamburgerIcon"></i>
                    </button>
                </div>
            </nav>
        <?php else: ?>
            
            <?php if(auth()->guard()->check()): ?>
                <?php if(Auth::user()?->isSuperAdmin() || Auth::user()?->isAdmin() || Auth::user()?->isStaff()): ?>
                    <nav class="navbar flex items-center justify-between w-full">
                        <a href="<?php echo e(Auth::user()?->isSuperAdmin() ? route('super-admin.dashboard') : (Auth::user()?->getFirstAccessibleRoute() ?? url('/'))); ?>" class="navbar-brand" style="padding: 0; flex-shrink: 0;">
                            <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Grafika South">
                        </a>

                        
                        <div class="nav-items-group desktop-nav-group ml-auto flex items-center gap-1">
                            <a href="<?php echo e(route('landing')); ?>" class="nav-link-item">
                                <i class="fa-solid fa-house"></i>
                                <span>Home</span>
                            </a>
                            <div class="nav-divider"></div>
                            <a href="<?php echo e(route('products.index')); ?>" class="nav-link-item">
                                <i class="fa-solid fa-layer-group"></i>
                                <span>Catalog</span>
                            </a>
                            <div class="nav-divider"></div>
                            <a href="<?php echo e(route('cart.index')); ?>" class="nav-link-item">
                                <i class="fa-solid fa-cart-shopping"></i>
                                <span>Cart</span>
                                <?php $cartCount = count(session('cart', [])); ?>
                                <span class="nav-cart-badge <?php echo e($cartCount > 0 ? 'has-items' : ''); ?>">
                                    <?php echo e($cartCount); ?>

                                </span>
                            </a>
                            <div class="nav-divider"></div>
                            <?php if(Auth::user()?->isSuperAdmin()): ?>
                                <a href="<?php echo e(route('super-admin.dashboard')); ?>" class="nav-link-item <?php echo e(request()->routeIs('super-admin.*') ? 'active' : ''); ?>">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    <span>Super Admin</span>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(Auth::user()?->getFirstAccessibleRoute() ?? url('/')); ?>" class="nav-link-item <?php echo e((request()->routeIs('admin.*') || request()->routeIs('super-admin.hero.*')) ? 'active' : ''); ?>">
                                    <i class="fa-solid fa-briefcase"></i>
                                    <span>Admin Panel</span>
                                </a>
                            <?php endif; ?>
                        </div>

                        
                        <div class="mobile-nav-actions">
                            <a href="<?php echo e(route('products.index')); ?>" onclick="if(event.ctrlKey || event.metaKey) return; event.preventDefault(); openSearchModal();" class="bg-sky-500 hover:bg-sky-600 text-white rounded-full p-2.5 shadow-md flex items-center justify-center transition-all focus:outline-none focus:ring-2 focus:ring-sky-400 w-10 h-10 flex-shrink-0" style="text-decoration: none;" aria-label="Search Products" title="Search Products">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </a>
                            <a href="<?php echo e(route('cart.index')); ?>" class="mobile-nav-btn mobile-cart-btn" aria-label="Shopping Cart">
                                <i class="fa-solid fa-cart-shopping"></i>
                                <?php $cartCount = count(session('cart', [])); ?>
                                <span class="nav-cart-badge <?php echo e($cartCount > 0 ? 'has-items' : ''); ?>">
                                    <?php echo e($cartCount); ?>

                                </span>
                            </a>
                            <button type="button" class="mobile-nav-btn mobile-hamburger-btn" id="mobileMenuToggleBtn" aria-label="Open Navigation Menu" aria-expanded="false">
                                <i class="fa-solid fa-bars"></i>
                            </button>
                        </div>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </header>

    <!-- MOBILE NAVIGATION SLIDE-OUT DRAWER & OVERLAY -->
    <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>


    <!-- Main Content Container -->
    <div class="container px-4 sm:px-6 lg:px-8 <?php if(auth()->guard()->check()): ?> <?php if((Auth::user()?->isSuperAdmin() || Auth::user()?->isAdmin() || Auth::user()?->isStaff()) && (request()->routeIs('admin.*') || request()->routeIs('super-admin.*')) && !request()->routeIs('admin.login')): ?> admin-container <?php endif; ?> <?php endif; ?>">

    
    <?php if(!request()->routeIs('admin.*') && !request()->routeIs('super-admin.*') && !request()->routeIs('login') && !request()->routeIs('register') && !request()->routeIs('customer.profile') && !request()->is('customer/profile*') && !request()->routeIs('cart.*')): ?>
        <?php if(session('success') || session('warning') || session('error')): ?>
        <div id="flash-toast-stack" style="position: fixed; top: 5rem; right: 1.25rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.6rem; width: min(92vw, 360px);">
            <?php if(session('success')): ?>
                <div class="alert alert-success flash-toast" style="margin: 0; box-shadow: 0 10px 28px rgba(15,23,42,0.15); border-radius: 10px;">
                    <i class="fa-solid fa-circle-check"></i> <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('warning')): ?>
                <div class="alert flash-toast" style="margin: 0; background-color: var(--warning-soft); color: var(--text-warning); border: 1px solid var(--warning-border); box-shadow: 0 10px 28px rgba(15,23,42,0.15); border-radius: 10px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo e(session('warning')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-error flash-toast" style="margin: 0; box-shadow: 0 10px 28px rgba(15,23,42,0.15); border-radius: 10px;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>
        </div>
        <script>
        (function () {
            document.querySelectorAll('.flash-toast').forEach(function (el) {
                setTimeout(function () {
                    el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    el.style.opacity = '0';
                    el.style.transform = 'translateX(20px)';
                    setTimeout(function () { el.remove(); }, 420);
                }, 5000);
            });
        })();
        </script>
        <?php endif; ?>
    <?php endif; ?>

        <?php if(auth()->guard()->check()): ?>
            <?php if((Auth::user()?->isSuperAdmin() || Auth::user()?->isAdmin() || Auth::user()?->isStaff()) && (request()->routeIs('admin.*') || request()->routeIs('super-admin.*')) && !request()->routeIs('admin.login')): ?>
                <div class="admin-portal-layout">
                    <!-- LEFT SIDEBAR -->
                    <aside class="admin-sidebar sticky top-[65px] h-[calc(100vh-65px)] overflow-y-auto">

                        <!-- Nav links -->
                        <nav class="admin-sidebar-nav">
                            <?php if(Auth::user()?->isSuperAdmin()): ?>
                                <a href="<?php echo e(route('super-admin.dashboard')); ?>" class="admin-nav-item <?php echo e(request()->routeIs('super-admin.dashboard') ? 'active' : ''); ?>">
                                    <i class="fa-solid fa-chart-line"></i> Sales
                                </a>
                                <a href="<?php echo e(route('super-admin.hero.edit')); ?>" class="admin-nav-item <?php echo e(request()->routeIs('super-admin.hero.*') ? 'active' : ''); ?>">
                                    <i class="fa-solid fa-images"></i> Banners
                                </a>
                                <a href="<?php echo e(route('super-admin.users.index')); ?>" class="admin-nav-item <?php echo e(request()->routeIs('super-admin.users.*') ? 'active' : ''); ?>">
                                    <i class="fa-solid fa-users-gear"></i> Accounts
                                </a>
                                <a href="<?php echo e(route('super-admin.products.index')); ?>" class="admin-nav-item <?php echo e(request()->routeIs('super-admin.products.*') ? 'active' : ''); ?>">
                                    <i class="fa-solid fa-boxes-stacked"></i> Products
                                </a>
                                <a href="<?php echo e(route('super-admin.orders.index')); ?>" class="admin-nav-item <?php echo e(request()->routeIs('super-admin.orders.*') ? 'active' : ''); ?>">
                                    <i class="fa-solid fa-list-check"></i> Orders
                                </a>
                                <div class="admin-nav-group <?php echo e(request()->routeIs('admin.materials.*') ? 'open' : ''); ?>" data-nav-group>
                                    <a href="<?php echo e(route('admin.materials.index')); ?>" class="admin-nav-item admin-nav-parent <?php echo e(request()->routeIs('admin.materials.*') ? 'active' : ''); ?>">
                                        <i class="fa-solid fa-warehouse"></i> Stocks
                                        <i class="fa-solid fa-chevron-down admin-nav-caret"></i>
                                    </a>
                                    <div class="admin-nav-submenu">
                                        <a href="<?php echo e(route('admin.materials.index')); ?>" class="admin-nav-subitem <?php echo e(request()->routeIs('admin.materials.index') ? 'active' : ''); ?>"><i class="fa-solid fa-boxes-stacked"></i> Materials Stock</a>
                                        <a href="<?php echo e(route('admin.materials.movements')); ?>" class="admin-nav-subitem <?php echo e(request()->routeIs('admin.materials.movements') ? 'active' : ''); ?>"><i class="fa-solid fa-clock-rotate-left"></i> Stock Movements</a>
                                    </div>
                                </div>
                                <a href="<?php echo e(route('super-admin.payment-methods.index')); ?>" class="admin-nav-item <?php echo e(request()->routeIs('super-admin.payment-methods.*') ? 'active' : ''); ?>">
                                    <i class="fa-solid fa-credit-card"></i> Payments
                                </a>
                            <?php elseif(Auth::user()?->isAdmin() || Auth::user()?->isStaff()): ?>
                                <?php if(Auth::user()->hasPermission('orders')): ?>
                                    <a href="<?php echo e(route('admin.orders.index')); ?>" class="admin-nav-item <?php echo e(request()->routeIs('admin.orders.*') ? 'active' : ''); ?>">
                                        <i class="fa-solid fa-clipboard-list"></i> Orders
                                    </a>
                                <?php endif; ?>
                                <?php if(Auth::user()->hasPermission('tickets')): ?>
                                    <a href="<?php echo e(route('admin.tickets.index')); ?>" class="admin-nav-item <?php echo e(request()->routeIs('admin.tickets.*') ? 'active' : ''); ?>">
                                        <i class="fa-solid fa-headset"></i> Support
                                    </a>
                                <?php endif; ?>
                                <?php if(Auth::user()->hasPermission('materials')): ?>
                                    <div class="admin-nav-group <?php echo e(request()->routeIs('admin.materials.*') ? 'open' : ''); ?>" data-nav-group>
                                        <a href="<?php echo e(route('admin.materials.index')); ?>" class="admin-nav-item admin-nav-parent <?php echo e(request()->routeIs('admin.materials.*') ? 'active' : ''); ?>">
                                            <i class="fa-solid fa-boxes-packing"></i> Stocks
                                            <i class="fa-solid fa-chevron-down admin-nav-caret"></i>
                                        </a>
                                        <div class="admin-nav-submenu">
                                            <a href="<?php echo e(route('admin.materials.index')); ?>" class="admin-nav-subitem <?php echo e(request()->routeIs('admin.materials.index') ? 'active' : ''); ?>"><i class="fa-solid fa-boxes-stacked"></i> Materials Stock</a>
                                            <a href="<?php echo e(route('admin.materials.movements')); ?>" class="admin-nav-subitem <?php echo e(request()->routeIs('admin.materials.movements') ? 'active' : ''); ?>"><i class="fa-solid fa-clock-rotate-left"></i> Stock Movements</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if(Auth::user()->hasPermission('products')): ?>
                                    <a href="<?php echo e(route('admin.products.index')); ?>" class="admin-nav-item <?php echo e(request()->routeIs('admin.products.*') ? 'active' : ''); ?>">
                                        <i class="fa-solid fa-box"></i> Products
                                    </a>
                                <?php endif; ?>
                                <?php if(Auth::user()->hasPermission('hero')): ?>
                                    <a href="<?php echo e(route('super-admin.hero.edit')); ?>" class="admin-nav-item <?php echo e(request()->routeIs('super-admin.hero.*') ? 'active' : ''); ?>">
                                        <i class="fa-solid fa-images"></i> Banners
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </nav>

                        <!-- Logout -->
                        <div class="admin-sidebar-logout-wrap">
                            <form action="<?php echo e(route('logout')); ?>" method="POST" onsubmit="return confirm('Are you sure you want to logout?');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="admin-nav-item logout-item" style="width:100%; border:none; background:none; cursor:pointer; text-align:left; font-family:inherit; font-size:0.85rem; font-weight:600;">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                                </button>
                            </form>
                        </div>

                    </aside>

                    <!-- MAIN CONTENT AREA -->
                    <main class="admin-main-content">
                        <?php echo $__env->yieldContent('content'); ?>
                    </main>
                </div>
            <?php else: ?>
                <?php echo $__env->yieldContent('content'); ?>
            <?php endif; ?>
        <?php else: ?>
            <?php echo $__env->yieldContent('content'); ?>
        <?php endif; ?>
    </div>

    <!-- Footer — hidden on customer dashboard, cart, admin, super-admin, auth (login/register), checkout, the catalog page, and the product detail page -->
    <?php if(!request()->routeIs('customer.*', 'cart.*', 'admin.*', 'super-admin.*', 'login', 'register', 'password.*', 'verification.*', 'checkout.*', 'products.index', 'products.show')): ?>
    <footer class="unicorp-footer">
        <div class="container" style="margin-top:0; margin-bottom:0;">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4 style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="cmyk-dots">
                            <span class="cmyk-dot dot-c"></span>
                            <span class="cmyk-dot dot-m"></span>
                            <span class="cmyk-dot dot-y"></span>
                            <span class="cmyk-dot dot-k"></span>
                        </span>
                        GRAFIKA SOUTH
                    </h4>
                    <p>Grafika South is your trusted local printing shop offering high-quality T-shirt printing, PVC ID cards, business cards, lamination, and custom print services in San Pedro, Laguna.</p>
                </div>

                <div class="footer-col">
                    <h4>Our Print Services</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo e(route('products.index', ['category' => 'Apparel'])); ?>">T-Shirt Printing</a></li>
                        <li><a href="<?php echo e(route('products.index', ['category' => 'Identification'])); ?>">PVC ID Card Printing</a></li>
                        <li><a href="<?php echo e(route('products.index', ['category' => 'Marketing'])); ?>">Business Cards (100 Pack)</a></li>
                        <li><a href="<?php echo e(route('products.index', ['category' => 'Services'])); ?>">Lamination Services</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo e(route('products.index')); ?>">Products Catalog</a></li>
                        <li><a href="<?php echo e(route('cart.index')); ?>">Shopping Cart</a></li>
                        <li><a href="<?php echo e(route('login')); ?>">User Login</a></li>
                        <li><a href="<?php echo e(route('register')); ?>">Create Account</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Contact Support</h4>
                    <p><i class="fa-solid fa-location-dot" style="color: var(--brand-cyan-fg);"></i> San Pedro, Laguna, Philippines</p>
                    <p><i class="fa-solid fa-phone" style="color: var(--brand-magenta-fg);"></i> +63 962 292 7736</p>
                    <p><i class="fa-solid fa-envelope" style="color: var(--brand-yellow-fg);"></i> grafikasouth69@gmail.com</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo e(date('Y')); ?> Grafika South Printing Services. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <!-- QUICK TRACK & TRACE ORDER MODAL -->
    <div id="trackOrderModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
        <div class="tt-card">
            <div class="tt-header">
                <div class="tt-header-main">
                    <div class="tt-icon-chip">
                        <i class="fa-solid fa-magnifying-glass-location"></i>
                    </div>
                    <div>
                        <h3 class="tt-title">Track &amp; Trace Order</h3>
                        <p class="tt-subtitle">Enter your Grafika South order number for live status</p>
                    </div>
                </div>
                <button type="button" class="tt-close" onclick="closeTrackOrderModal()" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="<?php echo e(route('orders.track')); ?>" method="GET" class="tt-body" onsubmit="return handleTrackOrderSubmit(event)">
                <label class="tt-label" for="quick_track_order_number">Order Number / Tracking ID <span class="tt-req">*</span></label>
                <div class="tt-field">
                    <i class="fa-solid fa-hashtag tt-field-icon"></i>
                    <input type="text" id="quick_track_order_number" name="order_number" placeholder="e.g. GS-20260817-6CB93" required class="tt-input">
                </div>

                <div class="tt-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>You can find your order number on your receipt, invoice email, or order history (e.g. <code>GS-20260817-6CB93</code>).</span>
                </div>

                <button type="submit" class="tt-btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Track Order Status</button>
            </form>
        </div>
    </div>

    <!-- QUICK PRODUCT SEARCH MODAL -->
    <div id="searchModal" onclick="if(event.target === this) closeSearchModal()" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 9999; align-items: flex-start; justify-content: center; padding: 1.5rem 1rem;">
        <div style="background: var(--bg-surface); border-radius: var(--radius-lg); width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); overflow: hidden; animation: modalFadeIn 0.2s ease-out; margin-top: 8vh;">
            <div style="background: var(--bg-inverse); padding: 1.15rem 1.35rem; color: var(--text-inverse); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: rgba(0, 153, 255, 0.2); color: var(--brand-cyan-fg); display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-inverse); margin: 0;">Search Products &amp; Services</h3>
                        <p style="font-size: 0.75rem; color: var(--p-ink-400); margin: 2px 0 0 0;">Find custom prints, shirts, PVC IDs, cards &amp; more</p>
                    </div>
                </div>
                <button type="button" onclick="closeSearchModal()" style="background: none; border: none; color: var(--p-ink-400); font-size: 1.2rem; cursor: pointer; padding: 4px; border-radius: 50%; transition: color 0.15s;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#94A3B8'">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="<?php echo e(route('products.index')); ?>" method="GET" style="padding: 1.25rem;">
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.85rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 0.4rem;">
                        Keyword / Product Name
                    </label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1rem; color: var(--text-subtle); font-size: 0.95rem; pointer-events: none;"></i>
                        <input type="text" id="quick_search_input" name="q" placeholder="Type to search (e.g. T-Shirt, PVC ID, Business Cards)..." value="<?php echo e(request('q')); ?>" required style="width: 100%; padding: 0.8rem 1rem 0.8rem 2.6rem; border: 2px solid var(--input-border); border-radius: 12px; background: var(--input-bg); color: var(--input-text); font-size: 0.95rem; outline: none; transition: all 0.2s; box-sizing: border-box;" onfocus="this.style.borderColor='var(--accent-color)'; this.style.boxShadow='0 0 0 3px var(--accent-ring)'" onblur="this.style.borderColor='var(--input-border)'; this.style.boxShadow='none'">
                    </div>
                </div>

                <!-- Popular Search Suggestions -->
                <div style="margin-bottom: 1.25rem;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.45rem;">Popular Searches</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                        <a href="<?php echo e(route('products.index', ['q' => 'T-Shirt'])); ?>" style="font-size: 0.78rem; font-weight: 600; padding: 0.3rem 0.65rem; border-radius: 9999px; background: var(--bg-subtle); color: var(--text-muted); text-decoration: none; transition: all 0.15s;">T-Shirt</a>
                        <a href="<?php echo e(route('products.index', ['q' => 'PVC ID'])); ?>" style="font-size: 0.78rem; font-weight: 600; padding: 0.3rem 0.65rem; border-radius: 9999px; background: var(--bg-subtle); color: var(--text-muted); text-decoration: none; transition: all 0.15s;">PVC ID Card</a>
                        <a href="<?php echo e(route('products.index', ['q' => 'Business Cards'])); ?>" style="font-size: 0.78rem; font-weight: 600; padding: 0.3rem 0.65rem; border-radius: 9999px; background: var(--bg-subtle); color: var(--text-muted); text-decoration: none; transition: all 0.15s;">Business Cards</a>
                        <a href="<?php echo e(route('products.index', ['q' => 'Lamination'])); ?>" style="font-size: 0.78rem; font-weight: 600; padding: 0.3rem 0.65rem; border-radius: 9999px; background: var(--bg-subtle); color: var(--text-muted); text-decoration: none; transition: all 0.15s;">Lamination</a>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.6rem;">
                    <button type="button" onclick="closeSearchModal()" class="btn btn-outline" style="padding: 0.55rem 1.1rem; font-size: 0.88rem; border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding: 0.55rem 1.3rem; font-size: 0.88rem; border-radius: 10px;">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openSearchModal() {
        const modal = document.getElementById('searchModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                const input = document.getElementById('quick_search_input');
                if (input) input.focus();
            }, 60);
        } else {
            window.location.href = "<?php echo e(route('products.index')); ?>";
        }
    }

    function closeSearchModal() {
        const modal = document.getElementById('searchModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function openTrackOrderModal() {
        const modal = document.getElementById('trackOrderModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                const input = document.getElementById('quick_track_order_number');
                if (input) input.focus();
            }, 50);
        }
    }

    function closeTrackOrderModal() {
        const modal = document.getElementById('trackOrderModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function handleTrackOrderSubmit(e) {
        const input = document.getElementById('quick_track_order_number');
        if (input) {
            let val = input.value.trim().replace(/^#/, '');
            input.value = val;
        }
        return true;
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeTrackOrderModal();
            closeSearchModal();
        }
    });

    // Mobile Drawer Navigation Toggle Functions
    function openMobileDrawer() {
        const drawer = document.getElementById('mobileNavDrawer');
        const overlay = document.getElementById('mobileDrawerOverlay');
        const btn = document.getElementById('mobileMenuToggleBtn');
        if (drawer && overlay) {
            drawer.classList.add('is-open');
            overlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            if (btn) btn.setAttribute('aria-expanded', 'true');
        }
    }

    function closeMobileDrawer() {
        const drawer = document.getElementById('mobileNavDrawer');
        const overlay = document.getElementById('mobileDrawerOverlay');
        const btn = document.getElementById('mobileMenuToggleBtn');
        if (drawer && overlay) {
            drawer.classList.remove('is-open');
            overlay.classList.remove('is-open');
            document.body.style.overflow = '';
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const menuBtn = document.getElementById('mobileMenuToggleBtn');
        const closeBtn = document.getElementById('mobileDrawerCloseBtn');
        const overlay = document.getElementById('mobileDrawerOverlay');

        if (menuBtn) {
            menuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const drawer = document.getElementById('mobileNavDrawer');
                if (drawer && drawer.classList.contains('is-open')) {
                    closeMobileDrawer();
                } else {
                    openMobileDrawer();
                }
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeMobileDrawer);
        }

        if (overlay) {
            overlay.addEventListener('click', closeMobileDrawer);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileDrawer();
            }
        });
    });

    // Click-to-toggle sidebar groups (Stocks): every click on the parent
    // only opens/closes the submenu — it never navigates directly.
    document.querySelectorAll('[data-nav-group] .admin-nav-parent').forEach(function (parent) {
        parent.addEventListener('click', function (e) {
            e.preventDefault();
            var group = parent.closest('[data-nav-group]');
            if (!group) return;
            document.querySelectorAll('[data-nav-group].open').forEach(function (g) {
                if (g !== group) g.classList.remove('open');
            });
            group.classList.toggle('open');
        });
    });
    document.querySelectorAll('[data-nav-group] .admin-nav-subitem').forEach(function (sub) {
        sub.addEventListener('click', function () {
            sub.classList.add('nav-pressed');
        });
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('[data-nav-group]')) {
            document.querySelectorAll('[data-nav-group].open').forEach(function (g) {
                if (g.querySelector('.admin-nav-subitem.active')) return; // keep active section expanded
                g.classList.remove('open');
            });
        }
    });
    </script>

    <?php if(auth()->guard()->check()): ?>
        <?php if (isset($component)) { $__componentOriginal6ee4ffff15e67f822ae015f9667c6629 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ee4ffff15e67f822ae015f9667c6629 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.customer-sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('customer-sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ee4ffff15e67f822ae015f9667c6629)): ?>
<?php $attributes = $__attributesOriginal6ee4ffff15e67f822ae015f9667c6629; ?>
<?php unset($__attributesOriginal6ee4ffff15e67f822ae015f9667c6629); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ee4ffff15e67f822ae015f9667c6629)): ?>
<?php $component = $__componentOriginal6ee4ffff15e67f822ae015f9667c6629; ?>
<?php unset($__componentOriginal6ee4ffff15e67f822ae015f9667c6629); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\grafika-south\resources\views/layouts/app.blade.php ENDPATH**/ ?>