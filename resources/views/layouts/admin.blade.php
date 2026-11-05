<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - Grafika South')</title>

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js (drives admin modals & dynamic purchase-order rows) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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

    @include('partials.theme-script')

    <style>
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

        @include('partials.design-tokens')

        html {
            max-width: 100vw;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        [x-cloak] { display: none !important; }

        a {
            text-decoration: none;
            color: inherit;
        }

        a:hover {
            text-decoration: none;
        }

        button {
            font-family: inherit;
            border: 0;
            background: transparent;
            cursor: pointer;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; }

        /* â”€â”€ Top navbar â”€â”€ */
        .admin-topbar {
            background: var(--header-solid);
            border-bottom: 1px solid var(--header-border);
            padding: 0 2rem;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            width: 100%;
            flex-shrink: 0;
            box-shadow: var(--header-shadow);
            transition: box-shadow 0.25s ease;
        }

        /* Header-scoped palette — in dark mode the topbar is brand blue, so
           its links and controls need light-on-blue colours. */
        .admin-topbar .nav-link-item { color: var(--header-text-muted); }
        .admin-topbar .nav-link-item i { color: var(--header-text-muted); }
        .admin-topbar .nav-link-item:hover {
            background-color: var(--header-hover-bg);
            color: var(--header-text);
        }
        .admin-topbar .nav-link-item:hover i { color: var(--header-text); }
        .admin-topbar .nav-link-item.active {
            background-color: var(--header-active-bg);
            color: var(--header-text);
        }
        .admin-topbar .nav-link-item.active i { color: var(--header-text); }
        .admin-topbar .nav-divider { background-color: var(--header-border); }
        .admin-topbar .theme-toggle {
            background: var(--header-control-bg);
            border-color: var(--header-control-border);
            color: var(--header-control-text);
        }
        .admin-topbar .theme-toggle:hover {
            background: var(--header-hover-bg);
            border-color: var(--header-control-border);
            color: var(--header-text);
        }
        /* Light header is white, so the dark wordmark needs no chip — sits flat. */
        .admin-topbar-brand img {
            background: transparent;
            border-radius: 10px;
            box-sizing: content-box;
            padding: 0;
            box-shadow: none;
            transition: filter 0.25s ease;
        }
        /* Dark mode: no white chip — recolor the wordmark to a light version so it stays readable on the dark header. */
        html.theme-dark .admin-topbar-brand img {
            background: transparent;
            padding: 0;
            box-shadow: none;
            filter: invert(1) hue-rotate(180deg);
        }

        .admin-topbar-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
            flex-shrink: 0;
        }

        .admin-topbar-brand img {
            height: 34px;
            width: auto;
            object-fit: contain;
        }

        .nav-divider {
            width: 1px;
            height: 22px;
            background: var(--text-disabled);
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
            white-space: nowrap;
            line-height: 1;
        }

        .nav-link-item i {
            font-size: 0.92rem;
            color: var(--text-muted);
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .nav-link-item:hover {
            background: var(--accent-soft);
            color: var(--text-accent);
            transform: translateY(-1px);
        }

        .nav-link-item:hover i {
            color: var(--text-accent);
            transform: scale(1.15);
        }

        .nav-link-item.active {
            background: var(--accent-soft);
            color: var(--text-accent);
            font-weight: 700;
        }

        .nav-link-item.active i {
            color: var(--text-accent);
        }

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

        /* â”€â”€ Portal layout â”€â”€ */
        .admin-portal-layout {
            display: flex;
            align-items: flex-start;
            min-height: calc(100vh - 60px);
        }

        /* â”€â”€ Sidebar â”€â”€ */
        .admin-sidebar {
            width: 180px;
            flex-shrink: 0;
            background: var(--bg-surface);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 60px;
            height: calc(100vh - 60px);
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 40;
        }

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

        .admin-sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 0.05rem;
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
            border-right: 3px solid transparent;
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

        .admin-nav-item:hover { background: var(--bg-subtle); color: var(--text-main); border-right-color: var(--border-color); }
        .admin-nav-item:hover i { color: var(--text-accent); }
        .admin-nav-item.active {
            background: var(--accent-color);
            color: var(--text-inverse);
            font-weight: 700;
            border-right-color: transparent;
        }
        .admin-nav-item.active i { color: var(--text-inverse); }
        .admin-nav-item.active:hover { background: var(--accent-color); color: var(--text-inverse); border-right-color: transparent; }
        .admin-nav-item.active:hover i { color: var(--text-inverse); }

        .admin-nav-divider { height: 1px; background: var(--bg-subtle); margin: 0.4rem 0.25rem; }

        /* Click-to-toggle dropdown (Stocks group) */
        .admin-nav-group { position: relative; }
        .admin-nav-parent .admin-nav-caret { margin-left: auto; font-size: 0.62rem; color: var(--text-subtle); transition: transform 0.15s ease; width: auto; }
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
        .admin-nav-item.logout-item:hover { background: var(--danger-soft); color: var(--text-danger); border-right-color: var(--danger-border); }
        .admin-nav-item.logout-item:hover i { color: var(--text-danger); }

        .admin-sidebar-logout-wrap {
            padding: 0.5rem 0.5rem 0.75rem;
            border-top: 1px solid var(--border-color);
        }

        /* â”€â”€ Main content â”€â”€ */
        .admin-main-content {
            flex: 1;
            min-width: 0;
            padding: 1.75rem 2rem;
        }

        /* â”€â”€ Shared utility â”€â”€ */
        .card {
            background: var(--bg-surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 1.75rem;
            margin-bottom: 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.875rem;
            line-height: 1.25rem;
            text-decoration: none !important;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
            white-space: nowrap;
        }

        .btn:hover {
            text-decoration: none !important;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: var(--text-inverse);
            box-shadow: 0 1px 2px rgba(0, 153, 255, 0.2);
        }
        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: var(--text-inverse);
        }
        .btn-magenta { background-color: var(--brand-magenta-fg); border-color: var(--brand-magenta-fg); color: var(--text-inverse); }
        .btn-magenta:hover { background-color: var(--brand-magenta-fg); border-color: var(--brand-magenta-fg); }
        .btn-yellow { background-color: var(--warning-color); border-color: var(--warning-color); color: var(--text-main); font-weight: 700; }
        .btn-yellow:hover { background-color: var(--warning-color); border-color: var(--warning-color); color: var(--text-inverse); }
        .btn-outline {
            background-color: var(--bg-surface);
            color: var(--text-muted);
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }
        .btn-outline:hover {
            background-color: var(--bg-page);
            border-color: var(--accent-color);
            color: var(--text-accent);
        }
        .btn-outline i {
            color: var(--text-muted);
            transition: color 0.15s ease;
        }
        .btn-outline:hover i {
            color: var(--text-accent);
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
        .alert-success { background: var(--accent-soft); color: var(--text-accent); border: 1px solid var(--accent-color); }
        .alert-error { background: var(--brand-magenta-soft); color: var(--text-magenta); border: 1px solid var(--brand-magenta-soft); }

        /* ── Toast notifications ── */
        #toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            pointer-events: none;
        }
        .toast {
            pointer-events: all;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 14px;
            min-width: 280px;
            max-width: 380px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.14), 0 2px 8px rgba(0,0,0,0.08);
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.4;
            animation: toastIn 0.3s cubic-bezier(.21,1.02,.73,1) forwards;
        }
        .toast.hiding { animation: toastOut 0.25s ease forwards; }
        .toast-success { background: var(--bg-surface); border-left: 4px solid var(--success-color); color: var(--text-main); }
        .toast-warning { background: var(--bg-surface); border-left: 4px solid var(--warning-color); color: var(--text-main); }
        .toast-error   { background: var(--bg-surface); border-left: 4px solid var(--danger-color); color: var(--text-main); }
        .toast-icon { flex-shrink: 0; width: 1.75rem; height: 1.75rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; margin-top: 0.05rem; }
        .toast-success .toast-icon { background: var(--success-soft); color: var(--text-success); }
        .toast-warning .toast-icon { background: var(--warning-soft); color: var(--text-warning); }
        .toast-error   .toast-icon { background: var(--danger-soft); color: var(--text-danger); }
        .toast-close { margin-left: auto; flex-shrink: 0; background: none; border: none; color: var(--text-subtle); cursor: pointer; font-size: 1rem; line-height: 1; padding: 0 0 0 0.5rem; }
        .toast-close:hover { color: var(--text-muted); }
        @keyframes toastIn  { from { opacity: 0; transform: translateX(2rem) scale(0.95); } to { opacity: 1; transform: translateX(0) scale(1); } }
        @keyframes toastOut { from { opacity: 1; transform: translateX(0); }               to { opacity: 0; transform: translateX(2rem); } }

        .role-badge {
            display: inline-block;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-super-admin { background: var(--brand-magenta-fg); color: var(--text-inverse); }
        .badge-admin { background: var(--accent-color); color: var(--text-inverse); }

        /* Modal Backdrop & Container */
        .edit-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: flex-start;
            justify-content: center;
            z-index: 1000;
            padding: 1rem;
        }

        .edit-modal-backdrop.active {
            display: flex;
        }

        .edit-modal-box {
            background: var(--bg-surface);
            border-radius: 16px;
            width: 100%;
            max-width: 550px;
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
            margin: auto;
            padding: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            position: relative;
        }

        @media (max-width: 992px) {
            /* The sidebar carries Tailwind utilities (sticky / top-[60px] /
               h-[calc(100vh-60px)] / overflow-y-auto) that the runtime CDN
               injects after this stylesheet, so the overrides below need
               !important to actually win.
               align-items must become stretch here: with flex-direction:column the
               cross axis is horizontal, so flex-start would size the content to
               its intrinsic width and push it off-screen on narrow viewports.
               flex:1 (basis 0%) also collapses to zero height in a column, so
               the content needs to size itself here. */
            .admin-portal-layout { flex-direction: column !important; min-height: auto !important; align-items: stretch !important; }
            .admin-sidebar {
                width: 100% !important;
                position: static !important;
                height: auto !important;
                border-right: none !important;
                border-bottom: 1px solid var(--border-color);
                display: flex;
                align-items: center;
                gap: 0.3rem;
                overflow-x: auto;
                scrollbar-width: thin;
                -webkit-overflow-scrolling: touch;
            }
            .admin-main-content { flex: 0 0 auto !important; padding: 1.25rem 1rem; width: 100%; max-width: 100%; }

            /* ── Mobile: turn the vertical sidebar into a horizontal scroll strip
                   so the page content is reachable without scrolling past the
                   entire menu first. ── */
            .admin-sidebar {
                display: flex;
                align-items: center;
                gap: 0.3rem;
                overflow-x: auto;
                scrollbar-width: thin;
                -webkit-overflow-scrolling: touch;
            }
            .admin-sidebar-header { display: none; }
            .admin-sidebar-nav {
                flex-direction: row;
                align-items: center;
                gap: 0.25rem;
                padding: 0.5rem;
                overflow: visible;
            }
            .admin-nav-item {
                white-space: nowrap;
                border-right: none;
                border-bottom: 3px solid transparent;
                padding: 0.45rem 0.6rem;
            }
            .admin-nav-item:hover { border-right-color: transparent; border-bottom-color: var(--border-color); }
            .admin-nav-item.active,
            .admin-nav-item.active:hover { border-bottom-color: transparent; }
            .admin-nav-item.logout-item:hover { border-right-color: transparent; border-bottom-color: var(--danger-border); }
            .admin-nav-divider { display: none; }

            /* The Stocks submenu is a hover dropdown on desktop — on touch
               devices there is no hover, so reveal the items inline. */
            .admin-nav-group { position: static; display: flex; align-items: center; }
            .admin-nav-group .admin-nav-submenu {
                display: flex !important;
                flex-direction: row;
                gap: 0.2rem;
                margin: 0;
                padding: 0;
                border-left: none;
            }
            .admin-nav-parent .admin-nav-caret { display: none; }
            .admin-nav-subitem { white-space: nowrap; }

            .admin-sidebar-logout-wrap {
                padding: 0.5rem 0.5rem 0.5rem 0;
                border-top: none;
                flex-shrink: 0;
            }
        }

        /* Suppress native browser password reveal/clear icons so custom eye toggles aren't duplicated */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input[type="password"]::-webkit-contacts-auto-fill-button,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
    </style>

    @stack('styles')
</head>
<body>

    {{-- Top navbar: logo + far right nav links --}}
    <nav class="admin-topbar sticky top-0 z-50 w-full flex items-center justify-between">
        <a href="{{ Auth::user()->isSuperAdmin() ? route('super-admin.dashboard') : Auth::user()->getFirstAccessibleRoute() }}" class="admin-topbar-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Grafika South">
        </a>

        <div class="ml-auto flex items-center gap-2">
            <a href="{{ route('products.index') }}" class="nav-link-item"><i class="fa-solid fa-layer-group"></i> Catalog</a>
            <div class="nav-divider"></div>
            <a href="{{ route('cart.index') }}" class="nav-link-item">
                <i class="fa-solid fa-cart-shopping"></i> Cart
                <span style="background:var(--brand-magenta-fg); color:var(--text-inverse); font-size:0.72rem; font-weight:700; padding:1px 7px; border-radius:9999px; margin-left:3px;">
                    {{ count(session('cart', [])) }}
                </span>
            </a>
            <div class="nav-divider"></div>
            @if(Auth::user()->isSuperAdmin())
                <a href="{{ route('super-admin.dashboard') }}" class="nav-link-item {{ request()->routeIs('super-admin.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved"></i> Super Admin
                </a>
            @else
                <a href="{{ Auth::user()->getFirstAccessibleRoute() }}" class="nav-link-item {{ (request()->routeIs('admin.*') || request()->routeIs('super-admin.hero.*')) ? 'active' : '' }}">
                    <i class="fa-solid fa-briefcase"></i> Admin Panel
                </a>
            @endif
            <div class="nav-divider"></div>
            @include('partials.theme-toggle')
        </div>
    </nav>

    {{-- Portal layout --}}
    <div class="admin-portal-layout">

        {{-- Sidebar --}}
        <aside class="admin-sidebar sticky top-[60px] h-[calc(100vh-60px)] overflow-y-auto">

            <nav class="admin-sidebar-nav">
                @if(Auth::user()->isSuperAdmin())
                    <a href="{{ route('super-admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line"></i> Dashboard
                    </a>
                    <a href="{{ route('super-admin.hero.edit') }}" class="admin-nav-item {{ request()->routeIs('super-admin.hero.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-images"></i> Banners
                    </a>
                    <a href="{{ route('super-admin.users.index') }}" class="admin-nav-item {{ request()->routeIs('super-admin.users.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users-gear"></i> Accounts
                    </a>
                    <a href="{{ route('super-admin.products.index') }}" class="admin-nav-item {{ request()->routeIs('super-admin.products.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-boxes-stacked"></i> Products
                    </a>
                    <a href="{{ route('super-admin.orders.index') }}" class="admin-nav-item {{ request()->routeIs('super-admin.orders.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-list-check"></i> Orders
                    </a>
                    <div class="admin-nav-group {{ request()->routeIs('admin.materials.*') ? 'open' : '' }}" data-nav-group>
                        <a href="{{ route('admin.materials.index') }}" class="admin-nav-item admin-nav-parent {{ request()->routeIs('admin.materials.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-warehouse"></i> Stocks
                            <i class="fa-solid fa-chevron-down admin-nav-caret"></i>
                        </a>
                        <div class="admin-nav-submenu">
                            <a href="{{ route('admin.materials.index') }}" class="admin-nav-subitem {{ request()->routeIs('admin.materials.index') ? 'active' : '' }}"><i class="fa-solid fa-boxes-stacked"></i> Materials Stock</a>
                            <a href="{{ route('admin.materials.movements') }}" class="admin-nav-subitem {{ request()->routeIs('admin.materials.movements') ? 'active' : '' }}"><i class="fa-solid fa-clock-rotate-left"></i> Stock Movements</a>
                        </div>
                    </div>
                    <a href="{{ route('admin.suppliers.index') }}" class="admin-nav-item {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-truck-field"></i> Suppliers
                    </a>
                    <a href="{{ route('admin.purchases.index') }}" class="admin-nav-item {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Purchases
                    </a>
                    <a href="{{ route('super-admin.payment-methods.index') }}" class="admin-nav-item {{ request()->routeIs('super-admin.payment-methods.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-credit-card"></i> Payments
                    </a>
                    <a href="{{ route('admin.tickets.index') }}" class="admin-nav-item {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-headset"></i> Support
                    </a>
                    <button type="button" class="admin-nav-item" onclick="openEditModal('modal-sa-security')" style="width:100%; border:none; background:none; cursor:pointer; text-align:left; font-family:inherit; font-size:0.85rem; font-weight:600; color:var(--text-muted);">
                        <i class="fa-solid fa-shield-halved"></i> Security
                    </button>
                @else
                    @if(Auth::user()->hasPermission('orders'))
                        <a href="{{ route('admin.orders.index') }}" class="admin-nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-clipboard-list"></i> Orders
                        </a>
                    @endif
                    @if(Auth::user()->hasPermission('tickets'))
                        <a href="{{ route('admin.tickets.index') }}" class="admin-nav-item {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-headset"></i> Support
                        </a>
                    @endif
                    @if(Auth::user()->hasPermission('materials'))
                        <div class="admin-nav-group {{ request()->routeIs('admin.materials.*') ? 'open' : '' }}" data-nav-group>
                            <a href="{{ route('admin.materials.index') }}" class="admin-nav-item admin-nav-parent {{ request()->routeIs('admin.materials.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-boxes-packing"></i> Stocks
                                <i class="fa-solid fa-chevron-down admin-nav-caret"></i>
                            </a>
                            <div class="admin-nav-submenu">
                                <a href="{{ route('admin.materials.index') }}" class="admin-nav-subitem {{ request()->routeIs('admin.materials.index') ? 'active' : '' }}"><i class="fa-solid fa-boxes-stacked"></i> Materials Stock</a>
                                <a href="{{ route('admin.materials.movements') }}" class="admin-nav-subitem {{ request()->routeIs('admin.materials.movements') ? 'active' : '' }}"><i class="fa-solid fa-clock-rotate-left"></i> Stock Movements</a>
                            </div>
                        </div>
                    @endif
                    @if(Auth::user()->hasPermission('suppliers'))
                        <a href="{{ route('admin.suppliers.index') }}" class="admin-nav-item {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-truck-field"></i> Suppliers
                        </a>
                        <a href="{{ route('admin.purchases.index') }}" class="admin-nav-item {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-file-invoice-dollar"></i> Purchases
                        </a>
                    @endif
                    @if(Auth::user()->hasPermission('products'))
                        <a href="{{ route('admin.products.index') }}" class="admin-nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-box"></i> Products
                        </a>
                    @endif
                    @if(Auth::user()->hasPermission('hero'))
                        <a href="{{ route('super-admin.hero.edit') }}" class="admin-nav-item {{ request()->routeIs('super-admin.hero.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-images"></i> Banners
                        </a>
                    @endif
                    <button type="button" class="admin-nav-item" onclick="openEditModal('modal-sa-security')" style="width:100%; border:none; background:none; cursor:pointer; text-align:left; font-family:inherit; font-size:0.85rem; font-weight:600; color:var(--text-muted);">
                        <i class="fa-solid fa-shield-halved"></i> Security
                    </button>
                @endif
            </nav>

            <div class="admin-sidebar-logout-wrap">
                <form id="admin-logout-form" action="{{ route('logout') }}" method="POST">
                    @csrf
                </form>
                <button type="button" class="admin-nav-item logout-item" onclick="openAdminLogoutModal()" style="width:100%; border:none; background:none; cursor:pointer; text-align:left; font-family:inherit; font-size:0.85rem; font-weight:600;">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </button>
            </div>

        </aside>

        {{-- Logout Confirmation Modal --}}
        <div id="adminLogoutModal" onclick="if(event.target===this) closeAdminLogoutModal()">
            <div class="alm-card">
                <div class="alm-head">
                    <div class="alm-icon"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
                    <div>
                        <div class="alm-title">Log out?</div>
                        <div class="alm-sub">Are you sure you want to logout?</div>
                    </div>
                </div>
                <div class="alm-actions">
                    <button type="button" class="alm-cancel" onclick="closeAdminLogoutModal()">Cancel</button>
                    <button type="submit" form="admin-logout-form" class="alm-confirm"><i class="fa-solid fa-arrow-right-from-bracket"></i> Yes, Log Out</button>
                </div>
            </div>
        </div>
        <style>
            #adminLogoutModal{display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:9999;align-items:center;justify-content:center;padding:1rem;}
            #adminLogoutModal .alm-card{background:var(--bg-surface);border-radius:16px;max-width:400px;width:100%;padding:1.5rem;box-shadow:0 20px 50px rgba(0,0,0,.25);}
            #adminLogoutModal .alm-head{display:flex;align-items:center;gap:.9rem;margin-bottom:1.25rem;}
            #adminLogoutModal .alm-icon{width:44px;height:44px;border-radius:12px;background:var(--danger-soft);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
            #adminLogoutModal .alm-icon i{color:var(--text-danger);font-size:1.1rem;}
            #adminLogoutModal .alm-title{font-size:1.12rem;font-weight:800;color:var(--text-main);}
            #adminLogoutModal .alm-sub{font-size:.88rem;color:var(--text-muted);margin-top:2px;}
            #adminLogoutModal .alm-actions{display:flex;justify-content:flex-end;gap:.6rem;}
            #adminLogoutModal .alm-cancel{padding:.6rem 1.2rem;border-radius:10px;border:1px solid var(--border-color);background:var(--bg-surface);color:var(--text-main);font-weight:700;cursor:pointer;}
            #adminLogoutModal .alm-cancel:hover{background:var(--bg-subtle);}
            #adminLogoutModal .alm-confirm{padding:.6rem 1.2rem;border-radius:10px;border:none;background:var(--danger-color);color:var(--text-inverse);font-weight:700;cursor:pointer;}
            #adminLogoutModal .alm-confirm:hover{background:var(--danger-color);}
        </style>
        <script>
            function openAdminLogoutModal(){var m=document.getElementById('adminLogoutModal');if(m){m.style.display='flex';}}
            function closeAdminLogoutModal(){var m=document.getElementById('adminLogoutModal');if(m){m.style.display='none';}}
        </script>

        {{-- Main content --}}
        <main class="admin-main-content">

            {{-- Flash alerts — toast notifications (bottom-right) --}}
            @if(!View::hasSection('hide_alerts'))
                <div id="toast-container">
                    @if(session('success'))
                        <div class="toast toast-success" role="alert">
                            <span class="toast-icon"><i class="fa-solid fa-check"></i></span>
                            <span style="flex:1;">{{ session('success') }}</span>
                            <button class="toast-close" onclick="dismissToast(this.closest('.toast'))" aria-label="Close">&times;</button>
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="toast toast-warning" role="alert">
                            <span class="toast-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                            <span style="flex:1;">{{ session('warning') }}</span>
                            <button class="toast-close" onclick="dismissToast(this.closest('.toast'))" aria-label="Close">&times;</button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="toast toast-error" role="alert">
                            <span class="toast-icon"><i class="fa-solid fa-circle-exclamation"></i></span>
                            <span style="flex:1;">{{ session('error') }}</span>
                            <button class="toast-close" onclick="dismissToast(this.closest('.toast'))" aria-label="Close">&times;</button>
                        </div>
                    @endif
                    @if($errors->any() && !View::hasSection('custom_errors'))
                        <div class="toast toast-error" role="alert" style="max-width:420px;">
                            <span class="toast-icon" style="align-self:flex-start; margin-top:0.1rem;"><i class="fa-solid fa-circle-exclamation"></i></span>
                            <div style="flex:1;">
                                <div style="font-weight:700; margin-bottom:0.25rem; font-size:0.82rem;">Please fix the following:</div>
                                <ul style="margin:0; padding-left:1.1rem; font-size:0.82rem;">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button class="toast-close" onclick="dismissToast(this.closest('.toast'))" aria-label="Close">&times;</button>
                        </div>
                    @endif
                </div>
                <script>
                    function dismissToast(el) {
                        el.classList.add('hiding');
                        el.addEventListener('animationend', () => el.remove(), { once: true });
                    }
                    // Auto-dismiss after 5 s
                    document.addEventListener('DOMContentLoaded', function () {
                        document.querySelectorAll('.toast').forEach(function (t) {
                            setTimeout(function () { if (t.isConnected) dismissToast(t); }, 5000);
                        });
                    });
                </script>
            @endif

            @yield('content')
        </main>
    </div>

    @if(Auth::check() && (Auth::user()->isSuperAdmin() || Auth::user()->isAdmin() || Auth::user()->isStaff()))
        @include('super_admin.users.security-modal')
    @endif

    <script>
        function openEditModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
            }
        }

        function closeEditModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
            }
        }

        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input && icon) {
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
        }

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('edit-modal-backdrop')) {
                e.target.classList.remove('active');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.edit-modal-backdrop.active').forEach(modal => {
                    modal.classList.remove('active');
                });
            }
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

    @stack('modals')
    @stack('scripts')
</body>
</html>
