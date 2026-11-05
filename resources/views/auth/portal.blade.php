{{-- ============================================================
     Grafika South Auth Portal — Strictly CMYK Aesthetic
     ─────────────────────────────────────────────────────────────
     C = Cyan    (#0099FF / #00AEEF)
     M = Magenta (#EC008C / #EC1885)
     Y = Yellow  (#FFD200 / #FFF200)
     K = Key     (#0F172A / #1E293B)

     Rendered by:
       - auth.login    ($mode = 'login')
       - auth.register ($mode = 'register')
     $showSwitcher = false for /admin/login
     ============================================================ --}}
@php
    $mode         = $mode         ?? 'login';
    $showSwitcher = $showSwitcher ?? true;
    $isReg        = ($mode === 'register');
@endphp

<style>
    /* ─── CMYK Design Tokens ─── */
    .ap-root {
        --cmyk-cyan: #0099FF;
        --cmyk-cyan-hover: #0080FF;
        --cmyk-cyan-dark: #0066CC;
        --cmyk-cyan-light: #E6F4FF;

        --cmyk-magenta: #EC008C;
        --cmyk-magenta-hover: #C80075;
        --cmyk-magenta-dark: #A3005F;
        --cmyk-magenta-light: #FDE8F5;

        --cmyk-yellow: #FFD200;
        --cmyk-yellow-dark: #EAB308;
        --cmyk-yellow-light: #FEF9C3;

        --cmyk-key: #0F172A;
        --cmyk-key-light: #1E293B;
        --cmyk-ink: #111827;

        /* Foreground variants — these theme with the UI, unlike the
           brand swatches above which always stay literal. */
        --cmyk-key-fg: #0F172A;
        --cmyk-cyan-fg: #0099FF;
        --cmyk-magenta-fg: #EC008C;
        --cmyk-yellow-fg: #EAB308;

        --ease-slide: cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ─── Locked Full Viewport (Zero Scrollbars) ─── */
    html, body {
        height: 100%;
        max-height: 100vh;
        overflow: hidden;
    }

    /* Dark theme variants for the portal's own palette. */
    html.theme-dark .ap-root {
        --cmyk-key-fg: #E6EDF7;
        --cmyk-cyan-fg: #38BDF8;
        --cmyk-magenta-fg: #F472B6;
        --cmyk-yellow-fg: #FBBF24;
        --cmyk-cyan-dark: #7DD3FC;
    }

    .ap-root {
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        max-height: 100vh;
        overflow: hidden;
        background: var(--bg-surface);
        font-family: inherit;
        z-index: 100;
        margin: 0 !important;
        padding: 0 !important;
        /* Native controls (calendar popup, dropdowns) follow the active theme */
        color-scheme: light dark;
    }

    /* The banner photo sits on a white base; in dark mode it sits on ink so
       the slide reads as one surface instead of a bright panel. */
    html.theme-dark .ap-banner {
        background-color: var(--p-ink-950);
    }
    html.theme-dark .ap-banner::after {
        background: linear-gradient(180deg, rgba(8, 13, 24, 0.55) 0%, rgba(8, 13, 24, 0.25) 100%);
    }

    /* ─────────────────────────────────────────────────────────────
       SLIDING BANNER (Luminous CMYK Studio White Theme)
       Rest states:
         Login active    → banner on LEFT  (translateX 0),  rounded-r-3xl
         Register active → banner on RIGHT (translateX 100%), rounded-l-3xl
    ───────────────────────────────────────────────────────────── */
    .ap-banner {
        position: absolute;
        top: 0; bottom: 0; left: 0;
        width: 50%;
        background: #FFFFFF url('{{ asset('images/login-bg.jpg') }}') center center / cover no-repeat;
        color: var(--cmyk-key-fg);
        padding: 2.25rem 2.75rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        z-index: 20;
        border-right: 1px solid var(--info-border);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        will-change: transform, border-radius;
        transition: transform 0.72s var(--ease-slide),
                    border-radius 0.72s var(--ease-slide);
        overflow: hidden;
    }

    /* Login state: banner left, right edge curved */
    .ap-banner:not(.is-register) {
        transform: translateX(0);
        border-radius: 0 2rem 2rem 0;
    }

    /* Register state: banner slides to right half, left edge curved */
    .ap-banner.is-register {
        transform: translateX(100%);
        border-radius: 2rem 0 0 2rem;
    }

    /* Subtle Print Registration / Halftone Dot Background Pattern */
    .ap-banner::before {
        display: none;
    }

    /* Flat Overlay */
    .ap-banner::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.05) 0%, rgba(240, 249, 255, 0.15) 100%);
        pointer-events: none;
    }

    /* Print Registration Marks at corners */
    .print-reg-mark {
        position: absolute;
        font-family: monospace;
        font-size: 1.15rem;
        line-height: 1;
        pointer-events: none;
        z-index: 4;
        user-select: none;
    }
    .reg-mark-tl { top: 1.25rem; left: 1.25rem; color: rgba(0, 153, 255, 0.5); }
    .reg-mark-tr { top: 1.25rem; right: 1.25rem; color: rgba(0, 153, 255, 0.5); }
    .reg-mark-bl { bottom: 1.25rem; left: 1.25rem; color: rgba(0, 153, 255, 0.5); }
    .reg-mark-br { bottom: 1.25rem; right: 1.25rem; color: rgba(0, 153, 255, 0.5); }

    /* Banner Content Layer */
    .ap-banner-content {
        position: relative;
        z-index: 6;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        max-width: 27.5rem;
        margin: 0 auto;
        width: 100%;
    }

    /* Banner Top Row */
    .ap-banner-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        margin-bottom: 0.5rem;
    }

    .cmyk-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        background: var(--bg-surface);
        border: 1.5px solid var(--accent-color);
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        box-shadow: none;
    }

    .cmyk-badge-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    .cmyk-badge-dot.c { background: var(--cmyk-cyan); }
    .cmyk-badge-dot.m { background: var(--cmyk-magenta); }
    .cmyk-badge-dot.y { background: var(--cmyk-yellow); }
    .cmyk-badge-dot.k { background: var(--cmyk-key); }

    .cmyk-badge-text {
        font-size: 0.64rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--cmyk-key-fg);
    }

    .ap-banner-edition {
        font-size: 0.62rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    /* ─── Hero Grafika South Brand Showcase (Centered) ─── */
    .ap-banner-hero {
        margin: auto;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    .ap-logo-showcase-card {
        background: color-mix(in srgb, var(--bg-surface) 94%, transparent);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-radius: 1.5rem;
        padding: 2rem 2.5rem 1.85rem;
        border: 1.5px solid rgba(125, 211, 252, 0.8);
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.2);
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .ap-logo-showcase-card:hover {
        transform: translateY(-2px) scale(1.01);
        box-shadow: 0 4px 12px rgba(0, 153, 255, 0.15);
    }

    .ap-banner-hero-logo {
        width: 100%;
        height: auto;
        max-height: 100px;
        object-fit: contain;
        display: block;
        margin: 0 auto;
    }

    /* Dark mode: the wordmark is dark, so recolor it light to stay readable on the dark glass card. */
    html.theme-dark .ap-banner-hero-logo {
        filter: invert(1) hue-rotate(180deg);
    }

    /* Banner Bottom CMYK Calibration Indicators */
    .ap-banner-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding-top: 0.85rem;
        border-top: 1px solid var(--border-color);
    }

    .cmyk-swatches {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .cmyk-swatch-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        background: var(--bg-surface);
        border: 1px solid var(--info-border);
        padding: 0.24rem 0.55rem;
        border-radius: 6px;
        box-shadow: none;
    }

    .swatch-color {
        width: 8px;
        height: 8px;
        border-radius: 2px;
    }
    .swatch-color.c { background: var(--cmyk-cyan); }
    .swatch-color.m { background: var(--cmyk-magenta); }
    .swatch-color.y { background: var(--cmyk-yellow); }
    .swatch-color.k { background: var(--cmyk-key); }

    .swatch-lbl {
        font-size: 0.6rem;
        font-weight: 800;
        color: var(--text-muted);
        letter-spacing: 0.06em;
    }

    .cmyk-quality-badge {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.62rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    .quality-icon {
        color: var(--cmyk-cyan-fg);
        font-size: 0.85rem;
    }
    /* ─────────────────────────────────────────────────────────────
       SIDE SWITCHER TABS (Anchored to Banner's Seam Edge)
       - When banner on left  (login)    → left: 100% (right seam)
       - When banner on right (register) → left: 0    (left seam)
    ───────────────────────────────────────────────────────────── */
    .ap-tabs {
        position: absolute;
        top: 50%;
        left: 100%;
        transform: translate(-50%, -50%);
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        z-index: 30;
        will-change: left;
        transition: left 0.72s var(--ease-slide);
    }

    .ap-banner:not(.is-register) .ap-tabs { left: 100%; }
    .ap-banner.is-register           .ap-tabs { left: 0;    }

    .ap-tab {
        width: 6.85rem;
        padding: 0.65rem 0.65rem;
        background: var(--bg-surface);
        color: var(--cmyk-key-fg);
        border: 1.5px solid var(--border-color);
        border-radius: 9999px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.1rem;
        cursor: pointer;
        font-family: inherit;
        position: relative;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease, opacity 0.25s ease, border-color 0.25s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .ap-tab:hover {
        transform: scale(1.06);
    }

    .ap-tab-title {
        font-size: 0.74rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--cmyk-key-fg);
    }

    .ap-tab-sub {
        font-size: 0.54rem;
        font-weight: 700;
        color: var(--text-muted);
        letter-spacing: 0.03em;
    }

    /* Active Tab: Solid Sky Blue Border & Fill */
    .ap-tab--active {
        background: var(--bg-surface);
        border-color: var(--cmyk-cyan);
        box-shadow: 0 0 0 2px var(--cmyk-cyan);
        opacity: 1;
    }

    .ap-tab--active .ap-tab-title {
        color: var(--cmyk-cyan-fg);
    }

    .ap-tab--inactive {
        background: color-mix(in srgb, var(--bg-surface) 92%, transparent);
        opacity: 0.75;
    }

    .ap-tab--inactive:hover {
        opacity: 1;
        background: var(--bg-surface);
    }

    /* ─────────────────────────────────────────────────────────────
       FORM PANES (Zero Vertical Scroll, Vertically Centered)
       Login  pane → right half of screen (right: 0)
       Register pane → left half of screen (left: 0)
    ───────────────────────────────────────────────────────────── */
    .ap-pane {
        position: absolute;
        top: 0; bottom: 0;
        width: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        overflow: hidden;
        z-index: 10;
        transition: opacity 0.4s ease, visibility 0.4s ease;
    }

    /* Fallback for unusually small display heights */
    @media (max-height: 580px) and (min-width: 901px) {
        .ap-pane {
            align-items: flex-start;
            overflow-y: auto;
            padding-top: 3rem;
            padding-bottom: 1.5rem;
        }
    }

    .ap-pane--login  { right: 0; left: auto; }
    .ap-pane--register { left: 0; right: auto; }

    .ap-pane.is-hidden { opacity: 0; visibility: hidden; pointer-events: none; }

    /* Pane Inner Cards */
    .ap-card {
        width: 100%;
        max-width: 420px;
        padding: 0;
        margin: auto 0;
    }

    .ap-card--register {
        max-width: 490px;
        padding: 0;
        margin: auto 0;
    }

    /* Fixed Utility Links Bar (Outside Card Flow) */
    .ap-utils-bar {
        position: absolute;
        top: 1rem;
        z-index: 20;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .ap-utils-bar--register { left: 1.5rem; }
    .ap-utils-bar--login { right: 1.5rem; }
        .ap-utils-bar--back { left: 1.5rem; }

    .ap-util-link {
        font-size: 0.74rem;
        font-weight: 700;
        padding: 0.28rem 0.75rem;
        border-radius: 9999px;
        border: 1px solid var(--info-border);
        background: var(--accent-soft);
        color: var(--text-accent);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    .ap-util-link:hover {
        background: var(--accent-soft);
        color: var(--text-accent);
        border-color: var(--accent-color);
    }

    /* Admin link — CMYK magenta accent */
    .ap-util-link--admin {
        color: var(--text-magenta);
        background: var(--brand-magenta-soft);
        border-color: var(--brand-magenta-soft);
    }

    .ap-util-link--admin:hover {
        background: var(--brand-magenta-soft);
        color: var(--text-magenta);
        border-color: var(--brand-magenta-soft);
    }

    /* ─── CMYK 4-Process Indicator Dots Bar ─── */
    .cmyk-dots-indicator {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.55rem;
    }

    .cmyk-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
    }
    .cmyk-dot.c { background: var(--cmyk-cyan); }
    .cmyk-dot.m { background: var(--cmyk-magenta); }
    .cmyk-dot.y { background: var(--cmyk-yellow); }
    .cmyk-dot.k { background: var(--cmyk-key); }

    /* ─── Brand Blocks ─── */
    .ap-brand {
        text-align: center;
        margin-bottom: 1.15rem;
    }

    .ap-brand-logo {
        width: auto;
        max-width: 175px;
        height: auto;
        max-height: 42px;
        object-fit: contain;
        margin: 0 auto 0.4rem;
        display: block;
    }

    .ap-brand-title {
        font-size: 1.7rem;
        font-weight: 800;
        letter-spacing: 2px;
        color: var(--cmyk-key-fg);
        margin: 0 0 0.15rem;
        text-transform: uppercase;
        line-height: 1.1;
    }

    .ap-brand-sub {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin: 0;
    }

    /* Compact brand for Register */
    .ap-brand--compact {
        margin-bottom: 0.65rem;
    }

    .ap-brand--compact .ap-brand-logo {
        width: auto;
        max-width: 140px;
        height: auto;
        max-height: 32px;
        margin-bottom: 0.25rem;
    }

    .ap-brand--compact .ap-brand-title {
        font-size: 1.25rem;
        letter-spacing: 1.5px;
        margin-bottom: 0.1rem;
    }

    .ap-brand--compact .ap-brand-sub {
        font-size: 0.74rem;
        color: var(--text-muted);
    }

    /* ─── Alerts ─── */
    .auth-alert {
        border-radius: 8px;
        padding: 0.4rem 0.75rem;
        margin-bottom: 0.5rem;
        font-size: 0.76rem;
        display: flex;
        align-items: flex-start;
        gap: 0.4rem;
    }

    /* ─── Login Underline Fields (CMYK Cyan Accents) ─── */
    .field-label-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.12rem;
    }

    .field-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--cmyk-key-fg);
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .field-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-bottom: 2px solid var(--border-color);
        padding: 0.5rem 0.25rem;
        margin-bottom: 1.15rem;
        transition: border-color 0.2s ease;
    }

    .field-row:focus-within {
        border-bottom-color: var(--cmyk-cyan);
    }

    .field-row i {
        color: var(--cmyk-cyan-fg);
        font-size: 1rem;
        width: 1.2rem;
        text-align: center;
        flex-shrink: 0;
    }

    .field-row input[type="text"],
    .field-row input[type="password"],
    .field-row input[type="email"] {
        flex: 1;
        border: none;
        outline: none;
        background: transparent;
        font-size: 0.9rem;
        font-family: inherit;
        color: var(--cmyk-key-fg);
        min-width: 0;
    }

    .field-row input::placeholder {
        color: var(--text-subtle);
    }

    .field-error {
        color: var(--text-danger);
        font-size: 0.76rem;
        margin: -0.85rem 0 0.9rem 2rem;
        display: block;
    }

    .password-toggle-btn {
        background: transparent;
        border: none;
        padding: 0.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--cmyk-cyan-fg);
        cursor: pointer;
        border-radius: 6px;
        transition: color 0.15s ease;
        z-index: 2;
        flex-shrink: 0;
    }
    .password-toggle-btn:hover {
        color: var(--cmyk-cyan-fg);
    }

    .eye-toggle-icon {
        width: 1.15rem;
        height: 1.15rem;
        display: block;
    }
    /* Only ONE eye icon is visible at a time (CSS-toggled, no Alpine needed) */
    .eye-toggle-icon .icon-eye-slash { display: none; }
    .password-toggle-btn.is-visible .eye-toggle-icon .icon-eye { display: none; }
    .password-toggle-btn.is-visible .eye-toggle-icon .icon-eye-slash { display: inline; }

    .auth-options-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: -0.25rem 0 1.25rem 0;
        font-size: 0.82rem;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .checkbox-custom {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        cursor: pointer;
        color: var(--text-muted);
        font-weight: 500;
        font-size: 0.8rem;
    }
    .checkbox-custom input[type="checkbox"] {
        accent-color: var(--cmyk-cyan);
        width: 0.95rem;
        height: 0.95rem;
    }

    .forgot-link {
        color: var(--cmyk-cyan-fg);
        font-weight: 700;
        text-decoration: none;
        font-size: 0.8rem;
        transition: color 0.15s ease;
    }
    .forgot-link:hover {
        color: var(--cmyk-magenta-fg);
        text-decoration: underline;
    }

    .auth-action-row {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 1rem;
    }

    /* CMYK Primary Action Buttons */
    .btn-login-pill {
        background: var(--accent-color);
        color: var(--text-inverse);
        border: none;
        padding: 0.7rem 2.2rem;
        border-radius: 9999px;
        font-size: 0.9rem;
        font-weight: 800;
        letter-spacing: 0.4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        box-shadow: none;
        transition: all 0.2s ease;
    }
    .btn-login-pill:hover {
        background: var(--accent-color);
        box-shadow: none;
        transform: translateY(-1px);
    }
    .btn-login-pill:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
    }

    .btn-login-pill.btn-verify {
        background: var(--cmyk-magenta);
        box-shadow: none;
    }
    .btn-login-pill.btn-verify:hover {
        background: var(--cmyk-magenta-hover);
        box-shadow: none;
    }

    /* ─── Register: Ultra-Compact Fields (CMYK Accents) ─── */
    .reg-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.38rem 0.75rem;
    }

    .reg-group {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .reg-label {
        font-size: 0.66rem;
        font-weight: 700;
        color: var(--cmyk-key-fg);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        display: flex;
        align-items: center;
        gap: 0.2rem;
        line-height: 1.1;
    }

    .reg-label .req {
        color: var(--cmyk-magenta-fg);
        font-size: 0.75rem;
        font-weight: 800;
    }

    .reg-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .reg-input-wrap i {
        position: absolute;
        left: 0.7rem;
        color: var(--cmyk-cyan-fg);
        font-size: 0.72rem;
        pointer-events: none;
        z-index: 1;
    }

    .reg-input {
        width: 100%;
        height: 33px;
        padding: 0.3rem 0.6rem 0.3rem 1.95rem;
        font-size: 0.8rem;
        font-family: inherit;
        border: 1.5px solid var(--border-color);
        border-radius: 7px;
        background: var(--bg-surface);
        color: var(--cmyk-key-fg);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        box-sizing: border-box;
        line-height: 1.2;
    }

    .reg-input:focus {
        outline: none;
        border-color: var(--cmyk-cyan);
        box-shadow: 0 0 0 2px rgba(0, 153, 255, 0.2);
    }

    .reg-input::placeholder {
        color: var(--text-subtle);
        font-size: 0.76rem;
    }

    .reg-select-wrap {
        position: relative;
    }

    .reg-select-wrap select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        padding-right: 1.85rem !important;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230099FF' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.6rem center;
        background-size: 0.8em;
        cursor: pointer;
    }

    .reg-select-wrap select:disabled {
        background-color: var(--bg-page);
        color: var(--text-subtle);
        cursor: not-allowed;
    }

    .reg-select-wrap select:required:invalid {
        color: var(--text-subtle);
    }
    .reg-select-wrap select option {
        color: var(--cmyk-key-fg);
        background: var(--bg-surface);
    }

    /* ─── Native date input: brand the calendar picker in CMYK cyan ───── */
    .reg-input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 1;
        cursor: pointer;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230099FF' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3crect x='3' y='4.5' width='18' height='16' rx='2'/%3e%3cline x1='16' y1='2.5' x2='16' y2='6.5'/%3e%3cline x1='8' y1='2.5' x2='8' y2='6.5'/%3e%3cline x1='3' y1='10' x2='21' y2='10'/%3e%3c/svg%3e");
        background-size: 1.05em;
        background-repeat: no-repeat;
        background-position: center;
        transition: opacity 0.15s ease;
    }
    .reg-input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 0.7;
    }
    .reg-input[type="date"]::-webkit-datetime-edit,
    .reg-input[type="date"]::-webkit-datetime-edit-fields-wrapper,
    .reg-input[type="date"]::-webkit-datetime-edit-text {
        color: var(--cmyk-key-fg);
    }

    /* Philippine Address Divider (CMYK Styling) */
    .addr-divider {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin: 0.32rem 0 0.28rem;
    }

    .addr-divider::before, .addr-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-color);
    }

    .addr-divider-label {
        font-size: 0.62rem;
        font-weight: 800;
        color: var(--cmyk-key-fg);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .reg-field-error {
        color: var(--text-danger);
        font-size: 0.66rem;
        margin-top: 0.1rem;
        display: block;
    }

    /* ─── Custom select (styled dropdown — replaces the native popup) ── */
    .reg-select-wrap { position: relative; }
    .reg-select-native {
        position: absolute !important;
        inset: 0;
        width: 100% !important;
        height: 100% !important;
        opacity: 0;
        pointer-events: none;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .reg-select-trigger {
        position: relative;
        display: block;
        width: 100%;
        height: 33px;
        line-height: 30px;
        padding: 0 1.85rem 0 1.95rem;
        font-size: 0.8rem;
        font-family: inherit;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--cmyk-key-fg);
        background: var(--bg-surface);
        border: 1.5px solid var(--border-color);
        border-radius: 7px;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .reg-select-trigger::after {
        content: '';
        position: absolute;
        right: 0.6rem;
        top: 50%;
        width: 0.8em;
        height: 0.8em;
        margin-top: -0.4em;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230099FF' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
        pointer-events: none;
    }
    .reg-select-trigger.is-placeholder { color: var(--text-subtle); }
    .reg-select-trigger:hover { border-color: var(--info-border); }
    .reg-select-trigger.is-open {
        border-color: var(--cmyk-cyan);
        box-shadow: 0 0 0 2px rgba(0, 153, 255, 0.2);
    }
    .reg-select-trigger.is-disabled {
        background: var(--bg-page);
        color: var(--text-subtle);
        cursor: not-allowed;
    }
    .reg-select-menu {
        position: fixed;
        z-index: 1400;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
        max-height: 250px;
        overflow-y: auto;
        padding: 0.3rem;
    }
    .reg-select-menu::-webkit-scrollbar { width: 8px; }
    .reg-select-menu::-webkit-scrollbar-thumb { background: var(--text-disabled); border-radius: 4px; }
    .reg-select-option {
        padding: 0.45rem 0.6rem;
        border-radius: 6px;
        font-size: 0.8rem;
        color: var(--text-muted);
        cursor: pointer;
        white-space: normal;
    }
    .reg-select-option:hover { background: var(--accent-soft); color: var(--text-accent); }
    .reg-select-option.is-selected { background: var(--accent-color); color: var(--text-inverse); font-weight: 700; }
    .reg-select-option.is-disabled { color: var(--text-disabled); cursor: not-allowed; }
    .reg-select-option.is-disabled:hover { background: transparent; color: var(--text-disabled); }

    /* ─── Custom date picker (styled — replaces the native calendar) ──── */
    .reg-calendar-icon {
        position: absolute;
        right: 0.55rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--cmyk-cyan-fg);
        font-size: 0.85rem;
        cursor: pointer;
        pointer-events: auto;
    }
    .reg-calendar-icon:hover { color: var(--cmyk-cyan-fg); }
    /* Hide the native calendar indicator — the custom one is used instead */
    .reg-input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0 !important;
        pointer-events: none;
    }
    .reg-input[type="date"] { padding-right: 2rem; }
    .reg-calendar {
        position: fixed;
        z-index: 1400;
        width: 272px;
        padding: 0.75rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.18);
        font-size: 0.8rem;
        color: var(--cmyk-key-fg);
    }
    .reg-calendar-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.55rem;
    }
    .reg-calendar-title { font-weight: 800; font-size: 0.85rem; color: var(--cmyk-key-fg); }
    .reg-calendar-title-btn {
        background: none;
        border: none;
        padding: 0;
        font-family: inherit;
        font-weight: 800;
        font-size: 0.85rem;
        color: var(--cmyk-key-fg);
        cursor: pointer;
        transition: color 0.15s ease;
    }
    .reg-calendar-title-btn:hover { color: var(--cmyk-cyan-fg); text-decoration: underline; }
    .reg-calendar-months {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.3rem;
    }
    .reg-calendar-years { grid-template-columns: repeat(4, 1fr); }
    .reg-calendar-month {
        padding: 0.5rem 0.25rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-surface);
        color: var(--text-muted);
        font-family: inherit;
        font-size: 0.74rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }
    .reg-calendar-month:hover:not(:disabled) { background: var(--accent-soft); border-color: var(--info-border); color: var(--text-accent); }
    .reg-calendar-month.is-selected { background: var(--accent-color); border-color: var(--accent-color); color: var(--text-inverse); }
    .reg-calendar-month:disabled { background: var(--bg-page); color: var(--text-disabled); cursor: not-allowed; }
    .reg-calendar-nav { display: flex; gap: 0.25rem; }
    .reg-calendar-nav button {
        width: 26px;
        height: 26px;
        border-radius: 7px;
        border: 1px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        line-height: 1;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }
    .reg-calendar-nav button:hover { background: var(--accent-color); border-color: var(--accent-color); color: var(--text-inverse); }
    .reg-calendar-nav button:disabled { background: var(--bg-page); border-color: var(--border-color); color: var(--text-disabled); cursor: not-allowed; }
    .reg-calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
    .reg-calendar-dow {
        text-align: center;
        font-size: 0.62rem;
        font-weight: 800;
        color: var(--text-subtle);
        padding: 0.25rem 0;
    }
    .reg-calendar-day {
        height: 30px;
        border: none;
        background: transparent;
        border-radius: 8px;
        color: var(--text-muted);
        font-size: 0.75rem;
        font-family: inherit;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.12s ease, color 0.12s ease;
    }
    .reg-calendar-day:hover:not(:disabled) { background: var(--accent-soft); color: var(--text-accent); }
    .reg-calendar-day.is-muted { color: var(--text-disabled); }
    .reg-calendar-day.is-today { box-shadow: inset 0 0 0 1.5px var(--accent-color); color: var(--text-accent); font-weight: 700; }
    .reg-calendar-day.is-selected { background: var(--accent-color); color: var(--text-inverse); font-weight: 700; }
    .reg-calendar-day:disabled { color: var(--text-disabled); cursor: not-allowed; }
    .reg-calendar-foot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.55rem;
        padding-top: 0.5rem;
        border-top: 1px solid var(--border-color);
    }
    .reg-calendar-foot button {
        background: none;
        border: none;
        color: var(--text-accent);
        font-weight: 700;
        font-size: 0.74rem;
        font-family: inherit;
        cursor: pointer;
    }
    .reg-calendar-foot button:hover { color: var(--text-accent); text-decoration: underline; }
    .reg-calendar-foot button:disabled { color: var(--text-disabled); cursor: not-allowed; text-decoration: none; }

    /* Register Submit: Solid Sky Blue */
    .btn-register-pill {
        width: 100%;
        height: 36px;
        background: var(--accent-color);
        color: var(--text-inverse);
        border: none;
        padding: 0 1.25rem;
        border-radius: 9999px;
        font-size: 0.86rem;
        font-weight: 800;
        letter-spacing: 0.4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow: none;
        transition: all 0.2s ease;
        margin-top: 0.55rem;
    }

    .btn-register-pill:hover {
        background: var(--accent-color);
        box-shadow: none;
        transform: translateY(-1px);
    }

    /* Shared Footer Links */
    .auth-footer-text {
        text-align: center;
        margin-top: 0.5rem;
        font-size: 0.78rem;
        color: var(--text-muted);
    }
    .auth-footer-text a {
        color: var(--cmyk-cyan-fg);
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: color 0.15s ease;
    }
    .auth-footer-text a:hover {
        color: var(--cmyk-magenta-fg);
        text-decoration: underline;
    }

    /* ─────────────────────────────────────────────────────────────
       MOBILE (≤ 900 px): Banner → Top Strip, Stacked Panes
    ───────────────────────────────────────────────────────────── */
    @media (max-width: 900px) {
        html, body {
            overflow-x: hidden;
            overflow-y: auto;
            height: auto;
            width: 100%;
            max-width: 100%;
        }

        .ap-root {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 100vw;
            height: auto;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: visible;
            z-index: 1;
        }

        .ap-banner {
            display: none;
        }

        .ap-banner-top {
            justify-content: center;
        }

        .ap-banner-edition {
            display: none;
        }

        .ap-logo-showcase-card {
            max-width: 270px;
            padding: 0.85rem 1.15rem 0.65rem;
            margin-bottom: 0.75rem;
        }

        .ap-banner-hero-logo {
            max-height: 48px;
        }

        .ap-banner-hero {
            margin: 0.5rem auto;
        }

        .ap-banner-bottom,
        .print-reg-mark {
            display: none;
        }

        .ap-tabs {
            position: relative;
            top: auto;
            left: auto !important;
            transform: none;
            flex-direction: row;
            justify-content: center;
            gap: 0.6rem;
            margin-top: 0.85rem;
        }

        .ap-tab {
            width: 6.2rem;
        }

        .ap-pane {
            position: relative;
            top: auto; bottom: auto; left: auto; right: auto;
            width: 100%;
            height: auto;
            flex: 1 0 auto;
            flex-direction: column;
            align-items: center;
            padding: 1.75rem 1.25rem 2.5rem;
            overflow: visible;
        }

        .ap-utils-bar {
            position: static !important;
            top: auto !important;
            left: auto !important;
            right: auto !important;
            width: 100%;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.85rem;
        }

        .ap-pane.is-hidden {
            display: none !important;
            opacity: 1;
            pointer-events: auto;
        }

        .reg-grid {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        /* Inline "span 2" would force an implicit 2nd column; make every field full-width */
        .reg-grid .reg-group {
            grid-column: 1 / -1 !important;
        }
        .auth-action-row {
            justify-content: center;
        }
        .btn-login-pill {
            width: 100%;
        }

        .ap-card {
            padding: 1rem 0;
            max-width: 100%;
            margin: 0;
        }
        .ap-card--register {
            max-width: none;
            padding: 1rem 0 1.5rem;
        }
    }
</style>

{{-- ══════════════════════════════════════════════════════════════
     Alpine.js Root Component: State + Slide Control
══════════════════════════════════════════════════════════════ --}}
<div class="ap-root" x-data="{
        isRegister: {{ $isReg ? 'true' : 'false' }},
        init() {
            window.addEventListener('popstate', () => {
                this.isRegister = window.location.pathname.includes('register');
            });
        },
        switchTo(mode) {
            this.isRegister = (mode === 'register');
            const url = (mode === 'register') ? '{{ route('register') }}' : '{{ route('login') }}';
            window.history.pushState({}, '', url);
        }
     }">

    {{-- ── SLIDING CMYK BANNER & GRAFIKA SOUTH SHOWCASE ── --}}
    <div class="ap-banner {{ $isReg ? 'is-register' : '' }}" :class="{ 'is-register': isRegister }">

        {{-- Authentic Print Registration Marks at corners --}}
        <span class="print-reg-mark reg-mark-tl">⌖</span>
        <span class="print-reg-mark reg-mark-tr">⌖</span>
        <span class="print-reg-mark reg-mark-bl">⌖</span>
        <span class="print-reg-mark reg-mark-br">⌖</span>

        {{-- Banner Content --}}
        <div class="ap-banner-content">
            {{-- Center: HERO GRAFIKA SOUTH BRAND SHOWCASE (Dead Center) --}}
            <div class="ap-banner-hero">
                {{-- Floating Pristine Logo Showcase Card --}}
                <div class="ap-logo-showcase-card">
                    <img src="{{ asset('images/logo.png') }}" alt="Grafika South" class="ap-banner-hero-logo">
                </div>
            </div>

        </div>

    </div>
    {{-- ── END SLIDING BANNER ── --}}


    {{-- ═══════════════════════════════════════════════════════
         REGISTER PANE — occupies LEFT half (revealed when banner
         slides to right)
    ══════════════════════════════════════════════════════════ --}}
    @if($showSwitcher)
    <div class="ap-pane ap-pane--register"
         @if(!$isReg) x-cloak @endif
         :class="{ 'is-hidden': !isRegister }">

        {{-- Utility link --}}
        <div class="ap-utils-bar ap-utils-bar--register">
            <a href="{{ route('landing') }}" class="ap-util-link">
                <i class="fa-solid fa-arrow-left" style="font-size:0.68rem;"></i> Store Home
            </a>
        </div>

        <div class="ap-card ap-card--register">

            {{-- Brand Block with CMYK Dots --}}
            <div class="ap-brand ap-brand--compact">
                <div class="cmyk-dots-indicator">
                    <span class="cmyk-dot c" title="Cyan"></span>
                    <span class="cmyk-dot m" title="Magenta"></span>
                    <span class="cmyk-dot y" title="Yellow"></span>
                    <span class="cmyk-dot k" title="Key"></span>
                </div>
                <h2 class="ap-brand-title">REGISTER</h2>
                <p class="ap-brand-sub">Create your Grafika South account</p>
            </div>

            {{-- Alerts --}}
            @if(session('warning'))
                <div class="auth-alert" style="background:var(--warning-soft); color:var(--text-warning); border:1px solid var(--warning-border);">
                    <i class="fa-solid fa-triangle-exclamation" style="flex-shrink:0;"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            {{-- Validation error summary removed — per-field errors below are enough --}}

            <form action="{{ route('register') }}" method="POST" novalidate>
                @csrf

                <div class="reg-grid">

                    {{-- First Name --}}
                    <div class="reg-group">
                        <label for="reg-firstname" class="reg-label">
                            First Name <span class="req">*</span>
                        </label>
                        <div class="reg-input-wrap">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" id="reg-firstname" name="first_name"
                                   class="reg-input"
                                   placeholder="e.g. Juan"
                                   value="{{ old('first_name') }}"
                                   @if($mode === 'register') autofocus @endif required>
                        </div>
                        @if(isset($errors) && $errors->has('first_name'))
                            <span class="reg-field-error">{{ $errors->first('first_name') }}</span>
                        @endif
                    </div>

                    {{-- Last Name --}}
                    <div class="reg-group">
                        <label for="reg-lastname" class="reg-label">
                            Last Name <span class="req">*</span>
                        </label>
                        <div class="reg-input-wrap">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" id="reg-lastname" name="last_name"
                                   class="reg-input"
                                   placeholder="e.g. Dela Cruz"
                                   value="{{ old('last_name') }}" required>
                        </div>
                        @if(isset($errors) && $errors->has('last_name'))
                            <span class="reg-field-error">{{ $errors->first('last_name') }}</span>
                        @endif
                    </div>

                    {{-- Middle Name (optional) --}}
                    <div class="reg-group">
                        <label for="reg-middlename" class="reg-label">
                            Middle Name <span style="color:var(--text-subtle);font-weight:600;">(optional)</span>
                        </label>
                        <div class="reg-input-wrap">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" id="reg-middlename" name="middle_name"
                                   class="reg-input"
                                   placeholder="e.g. Santos"
                                   value="{{ old('middle_name') }}">
                        </div>
                        @if(isset($errors) && $errors->has('middle_name'))
                            <span class="reg-field-error">{{ $errors->first('middle_name') }}</span>
                        @endif
                    </div>

                    {{-- Date of Birth (must be 18+) --}}
                    <div class="reg-group">
                        <label for="reg-dob" class="reg-label">
                            Date of Birth <span class="req">*</span>
                        </label>
                        <div class="reg-input-wrap">
                            <i class="fa-solid fa-cake-candles"></i>
                            <input type="date" id="reg-dob" name="date_of_birth"
                                   class="reg-input"
                                   max="{{ now()->subYears(18)->toDateString() }}"
                                   oninput="this.setCustomValidity(this.value && this.value > this.max ? 'You must be at least 18 years old to create an account.' : '')"
                                   value="{{ old('date_of_birth') }}" required>
                        </div>
                        @if(isset($errors) && $errors->has('date_of_birth'))
                            <span class="reg-field-error">{{ $errors->first('date_of_birth') }}</span>
                        @endif
                    </div>

                    {{-- Email --}}
                    <div class="reg-group">
                        <label for="reg-email" class="reg-label">
                            Email <span class="req">*</span>
                        </label>
                        <div class="reg-input-wrap">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" id="reg-email" name="email"
                                   class="reg-input"
                                   placeholder="name@domain.com"
                                   value="{{ old('email') }}" required>
                        </div>
                        @if(isset($errors) && $errors->has('email'))
                            <span class="reg-field-error">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    {{-- Phone --}}
                    <div class="reg-group">
                        <label for="reg-phone" class="reg-label">Phone</label>
                        <div class="reg-input-wrap">
                            <i class="fa-solid fa-phone"></i>
                            <input type="tel" id="reg-phone" name="phone"
                                   class="reg-input"
                                   placeholder="09XXXXXXXXX"
                                   inputmode="numeric" pattern="09[0-9]{9}" maxlength="11"
                                   title="Enter a valid 11-digit mobile number starting with 09"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)"
                                   value="{{ old('phone') }}">
                        </div>
                        @if(isset($errors) && $errors->has('phone'))
                            <span class="reg-field-error">{{ $errors->first('phone') }}</span>
                        @endif
                    </div>

                    {{-- Password --}}
                    <div class="reg-group">
                        <label for="reg-password" class="reg-label">
                            Password <span class="req">*</span>
                        </label>
                        <div class="reg-input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="reg-password" name="password"
                                   class="reg-input"
                                   placeholder="Min. 8 characters" required>
                        </div>
                        @if(isset($errors) && $errors->has('password'))
                            <span class="reg-field-error">{{ $errors->first('password') }}</span>
                        @endif
                    </div>

                    {{-- Confirm Password --}}
                    <div class="reg-group">
                        <label for="reg-confirm" class="reg-label">
                            Confirm Password <span class="req">*</span>
                        </label>
                        <div class="reg-input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="reg-confirm" name="password_confirmation"
                                   class="reg-input"
                                   placeholder="Re-enter password" required>
                        </div>
                    </div>

                </div>

                {{-- Philippine Address Divider (CMYK) --}}
                <div class="addr-divider">
                    <span class="addr-divider-label">
                        <i class="fa-solid fa-location-dot" style="color: var(--cmyk-cyan-fg);"></i> Philippine Address
                    </span>
                </div>

                <div class="reg-grid">

                    {{-- Region --}}
                    <div class="reg-group">
                        <label for="reg-region" class="reg-label">
                            Region <span class="req">*</span>
                        </label>
                        <div class="reg-input-wrap reg-select-wrap">
                            <i class="fa-solid fa-globe"></i>
                            <select id="reg-region" name="region" class="reg-input" required>
                                <option value="" disabled selected>Select Region</option>
                            </select>
                        </div>
                        @if(isset($errors) && $errors->has('region'))
                            <span class="reg-field-error">{{ $errors->first('region') }}</span>
                        @endif
                    </div>

                    {{-- Province --}}
                    <div class="reg-group">
                        <label for="reg-province" class="reg-label">
                            Province <span class="req">*</span>
                        </label>
                        <div class="reg-input-wrap reg-select-wrap">
                            <i class="fa-solid fa-map"></i>
                            <select id="reg-province" name="province" class="reg-input" required disabled>
                                <option value="" disabled selected>Select Province</option>
                            </select>
                        </div>
                        @if(isset($errors) && $errors->has('province'))
                            <span class="reg-field-error">{{ $errors->first('province') }}</span>
                        @endif
                    </div>

                    {{-- City / Municipality --}}
                    <div class="reg-group">
                        <label for="reg-city" class="reg-label">
                            City / Municipality <span class="req">*</span>
                        </label>
                        <div class="reg-input-wrap reg-select-wrap">
                            <i class="fa-solid fa-city"></i>
                            <select id="reg-city" name="city" class="reg-input" required disabled>
                                <option value="" disabled selected>Select City</option>
                            </select>
                        </div>
                        @if(isset($errors) && $errors->has('city'))
                            <span class="reg-field-error">{{ $errors->first('city') }}</span>
                        @endif
                    </div>

                    {{-- Barangay --}}
                    <div class="reg-group">
                        <label for="reg-barangay" class="reg-label">
                            Barangay <span class="req">*</span>
                        </label>
                        <div class="reg-input-wrap reg-select-wrap">
                            <i class="fa-solid fa-map-pin"></i>
                            <select id="reg-barangay" name="barangay" class="reg-input" required disabled>
                                <option value="" disabled selected>Select Barangay</option>
                            </select>
                        </div>
                        @if(isset($errors) && $errors->has('barangay'))
                            <span class="reg-field-error">{{ $errors->first('barangay') }}</span>
                        @endif
                    </div>

                    {{-- Street (span 2) --}}
                    <div class="reg-group" style="grid-column: span 2;">
                        <label for="reg-street" class="reg-label">Street / House / Unit / Building</label>
                        <div class="reg-input-wrap">
                            <i class="fa-solid fa-house-chimney"></i>
                            <input type="text" id="reg-street" name="street"
                                   class="reg-input"
                                   placeholder="e.g. 123 Mabini St., Unit 4B"
                                   value="{{ old('street') }}">
                        </div>
                        @if(isset($errors) && $errors->has('street'))
                            <span class="reg-field-error">{{ $errors->first('street') }}</span>
                        @endif
                    </div>

                </div>

                {{-- CMYK Register Submit Button --}}
                <button type="submit" class="btn-register-pill">
                    <i class="fa-solid fa-user-check"></i> Register Account &amp; Sign In
                </button>
            </form>

            <div class="auth-footer-text">
                Already have an account?
                <a @click.prevent="switchTo('login')" href="{{ route('login') }}">Log In Here</a>
            </div>

        </div>{{-- /.ap-card--register --}}
    </div>{{-- /.ap-pane--register --}}
    @endif


    {{-- ═══════════════════════════════════════════════════════
         LOGIN PANE — occupies RIGHT half (revealed when banner
         slides to left)
    ══════════════════════════════════════════════════════════ --}}
    <div class="ap-pane ap-pane--login"
         @if($isReg) x-cloak @endif
         :class="{ 'is-hidden': isRegister }">

        {{-- Back button (admin login only: return to the customer sign-in portal) --}}
        @if(!$showSwitcher)
        <div class="ap-utils-bar ap-utils-bar--back">
            <a href="{{ route('login') }}" class="ap-util-link">
                <i class="fa-solid fa-arrow-left" style="font-size:0.68rem;"></i> Back
            </a>
        </div>
        @endif

        {{-- Utility links --}}
        <div class="ap-utils-bar ap-utils-bar--login">
            <a href="{{ route('landing') }}" class="ap-util-link">
                <i class="fa-solid fa-arrow-left" style="font-size:0.68rem;"></i> Store Home
            </a>
            @if($showSwitcher)
            <a href="{{ url('/admin/login') }}" class="ap-util-link ap-util-link--admin">
                🔒 Login as Admin
            </a>
            @endif
        </div>

        <div class="ap-card">

            {{-- Brand block with CMYK Dots --}}
            <div class="ap-brand">
                <div class="cmyk-dots-indicator">
                    <span class="cmyk-dot c" title="Cyan"></span>
                    <span class="cmyk-dot m" title="Magenta"></span>
                    <span class="cmyk-dot y" title="Yellow"></span>
                    <span class="cmyk-dot k" title="Key"></span>
                </div>
                <h2 class="ap-brand-title">LOGIN</h2>
                <p class="ap-brand-sub">Welcome back to Grafika South</p>
            </div>

            {{-- Alerts --}}
            @if(session('warning'))
                <div class="auth-alert" style="background:var(--warning-soft); color:var(--text-warning); border:1px solid var(--warning-border);">
                    <i class="fa-solid fa-triangle-exclamation" style="flex-shrink:0;"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="auth-alert" style="background:var(--danger-soft); color:var(--text-danger); border:1px solid var(--danger-border);">
                    <i class="fa-solid fa-circle-exclamation" style="flex-shrink:0;"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="auth-alert" style="background:var(--success-soft); color:var(--text-success); border:1px solid var(--success-border);">
                    <i class="fa-solid fa-circle-check" style="flex-shrink:0;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Login form --}}
            <div x-data="loginForm()">

                {{-- Credentials --}}
                <div>
                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="login_portal" value="{{ request()->routeIs('admin.login') ? 'admin' : 'customer' }}">

                        <label for="login-input" class="field-label">Email Address</label>
                        <div class="field-row">
                            <i class="fa-solid fa-circle-user"></i>
                            <input type="text" id="login-input" name="login"
                                   x-model="loginValue"
                                   placeholder="Enter your email"
                                   value="{{ old('login', old('email')) }}"
                                   @if($mode === 'login') autofocus @endif required>
                        </div>

                        <div class="field-label-row">
                            <label for="login-password" class="field-label">Password</label>
                            <a href="{{ route('password.request') }}" class="forgot-link">Forgot Password?</a>
                        </div>
                        <div class="field-row">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password"
                                   id="login-password" name="password"
                                   placeholder="Enter your password"
                                   style="padding-right: 2rem;">
                            <button type="button" id="toggle-password"
                                    class="password-toggle-btn" aria-label="Show password" aria-pressed="false">
                                <svg class="eye-toggle-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <g class="icon-eye">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </g>
                                    <g class="icon-eye-slash">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </g>
                                </svg>
                            </button>
                        </div>
                        @if(isset($errors))
                            @if($errors->has('password'))
                                <span class="field-error">{{ $errors->first('password') }}</span>
                            @elseif($errors->has('login') || $errors->has('email'))
                                <span class="field-error">{{ $errors->first('login') ?: $errors->first('email') }}</span>
                            @endif
                        @endif

                        <div class="auth-options-row">
                            <label class="checkbox-custom">
                                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <span>Remember Me</span>
                            </label>
                        </div>

                        <div class="auth-action-row">
                            <button type="submit" class="btn-login-pill">
                                <span>CONTINUE <i class="fa-solid fa-arrow-right"></i></span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            @if($showSwitcher)
                <div class="auth-footer-text">
                    Don't have an account?
                    <a @click.prevent="switchTo('register')" href="{{ route('register') }}">Register Now</a>
                </div>
            @endif

        </div>{{-- /.ap-card --}}
    </div>{{-- /.ap-pane--login --}}

</div>{{-- /.ap-root --}}


{{-- ══════════════════════════════════════════════════════════════
     Login Form Script
══════════════════════════════════════════════════════════════ --}}
<script>
    function loginForm() {
        return {
            loginValue: '{{ old('login', old('email')) }}',
        };
    }
</script>


{{-- ══════════════════════════════════════════════════════════════
     Philippine Address Cascade (Register Pane)
══════════════════════════════════════════════════════════════ --}}
@if($showSwitcher)
<script>
(function () {
    const regionSelect   = document.getElementById('reg-region');
    const provinceSelect = document.getElementById('reg-province');
    const citySelect     = document.getElementById('reg-city');
    const barangaySelect = document.getElementById('reg-barangay');
    if (!regionSelect) return;

    const oldRegion   = @json(old('region'));
    const oldProvince = @json(old('province'));
    const oldCity     = @json(old('city'));
    const oldBarangay = @json(old('barangay'));

    let currentRegionCode = null, currentProvinceCode = null, currentCityName = null;

    function buildOptions(list, valueKey, labelKey, placeholder) {
        let html = `<option value="" disabled selected>${placeholder}</option>`;
        list.forEach(item => {
            const val = valueKey ? item[valueKey] : item.name;
            const lab = labelKey ? item[labelKey] : item.name;
            html += `<option value="${escapeHtml(val)}">${escapeHtml(lab)}</option>`;
        });
        return html;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = String(str ?? '');
        return div.innerHTML;
    }

    function resetSelect(sel, placeholder) {
        sel.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
        sel.disabled = true;
    }

    async function loadRegions() {
        try {
            const res = await fetch('{{ route('api.address.regions') }}');
            const regions = await res.json();
            regionSelect.innerHTML = buildOptions(regions, 'code', 'name', 'Select Region');
            regionSelect.disabled = false;
            if (oldRegion) {
                regionSelect.value = oldRegion;
                currentRegionCode = oldRegion;
                await loadProvinces(oldRegion);
            }
        } catch (err) { console.error('Failed to load regions:', err); }
    }

    async function loadProvinces(regionCode) {
        resetSelect(provinceSelect, 'Select Province');
        resetSelect(citySelect, 'Select City');
        resetSelect(barangaySelect, 'Select Barangay');
        currentRegionCode = regionCode; currentProvinceCode = null; currentCityName = null;
        try {
            const res = await fetch(`{{ url('/') }}/api/address/regions/${encodeURIComponent(regionCode)}/provinces`);
            const provinces = await res.json();
            provinceSelect.innerHTML = buildOptions(provinces, 'code', 'name', 'Select Province');
            provinceSelect.disabled = false;
            if (oldProvince) {
                provinceSelect.value = oldProvince;
                currentProvinceCode = oldProvince;
                await loadCities(regionCode, oldProvince);
            }
        } catch (err) { console.error('Failed to load provinces:', err); }
    }

    async function loadCities(regionCode, provinceCode) {
        resetSelect(citySelect, 'Select City');
        resetSelect(barangaySelect, 'Select Barangay');
        currentProvinceCode = provinceCode; currentCityName = null;
        try {
            const res = await fetch(`{{ url('/') }}/api/address/regions/${encodeURIComponent(regionCode)}/provinces/${encodeURIComponent(provinceCode)}/cities`);
            const cities = await res.json();
            citySelect.innerHTML = buildOptions(cities, null, 'name', 'Select City / Municipality');
            citySelect.disabled = false;
            if (oldCity) {
                citySelect.value = oldCity;
                currentCityName = oldCity;
                await loadBarangays(regionCode, provinceCode, oldCity);
            }
        } catch (err) { console.error('Failed to load cities:', err); }
    }

    async function loadBarangays(regionCode, provinceCode, cityName) {
        resetSelect(barangaySelect, 'Select Barangay');
        currentCityName = cityName;
        try {
            const res = await fetch(`{{ url('/') }}/api/address/regions/${encodeURIComponent(regionCode)}/provinces/${encodeURIComponent(provinceCode)}/cities/${encodeURIComponent(cityName)}/barangays`);
            const barangays = await res.json();
            barangaySelect.innerHTML = buildOptions(barangays, null, 'name', 'Select Barangay');
            barangaySelect.disabled = false;
            if (oldBarangay) { barangaySelect.value = oldBarangay; }
        } catch (err) { console.error('Failed to load barangays:', err); }
    }

    regionSelect.addEventListener('change', function () { if (this.value) loadProvinces(this.value); });
    provinceSelect.addEventListener('change', function () { if (this.value && currentRegionCode) loadCities(currentRegionCode, this.value); });
    citySelect.addEventListener('change', function () { if (this.value && currentRegionCode && currentProvinceCode) loadBarangays(currentRegionCode, currentProvinceCode, this.value); });

    loadRegions();
})();
</script>
@endif

{{-- ─── Custom UI/UX: styled select dropdown + styled date picker (CMYK cyan) ─── --}}
<script>
(function () {
    'use strict';

    var CYAN = '#0099FF';

    /* ── Shared helpers ─────────────────────────────────────────────── */
    function closeAllMenus(except) {
        var menus = document.querySelectorAll('.reg-select-menu');
        for (var i = 0; i < menus.length; i++) {
            if (menus[i] !== except) {
                var t = menus[i].__trigger;
                menus[i].remove();
                if (t) { t.classList.remove('is-open'); }
            }
        }
    }

    function positionFloating(el, anchor) {
        var r = anchor.getBoundingClientRect();
        var w = el.offsetWidth;
        var h = el.offsetHeight;
        var top = r.bottom + 4;
        if (top + h > window.innerHeight - 8) {
            top = Math.max(8, r.top - h - 4);
        }
        var left = r.left;
        if (left + w > window.innerWidth - 8) {
            left = Math.max(8, window.innerWidth - w - 8);
        }
        el.style.top = top + 'px';
        el.style.left = left + 'px';
        el.style.minWidth = r.width + 'px';
    }

    /* ── Custom select dropdown ─────────────────────────────────────── */
    function enhanceSelect(sel) {
        if (!sel || sel.dataset.customReady === '1') { return; }
        var wrap = sel.closest('.reg-select-wrap');
        if (!wrap) { return; }
        sel.dataset.customReady = '1';

        var firstOpt = sel.options[0];
        var placeholder = (firstOpt && firstOpt.value === '') ? firstOpt.textContent.trim() : 'Select';

        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'reg-select-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        wrap.appendChild(trigger);

        sel.classList.add('reg-select-native');
        sel.setAttribute('tabindex', '-1');
        sel.setAttribute('aria-hidden', 'true');

        var menu = null;

        function renderTrigger() {
            var opt = sel.options[sel.selectedIndex];
            var empty = (!opt || opt.value === '');
            trigger.textContent = empty ? placeholder : opt.textContent.trim();
            trigger.classList.toggle('is-placeholder', empty);
            var off = !!sel.disabled;
            trigger.classList.toggle('is-disabled', off);
            trigger.disabled = off;
        }

        function renderMenu() {
            if (!menu) { return; }
            menu.innerHTML = '';
            var frag = document.createDocumentFragment();
            for (var i = 0; i < sel.options.length; i++) {
                var opt = sel.options[i];
                var o = document.createElement('div');
                o.className = 'reg-select-option';
                o.setAttribute('role', 'option');
                o.textContent = opt.textContent.trim();
                if (opt.value === sel.value) { o.classList.add('is-selected'); }
                if (opt.disabled) {
                    o.classList.add('is-disabled');
                    o.setAttribute('aria-disabled', 'true');
                } else {
                    (function (value, node) {
                        node.addEventListener('click', function () {
                            sel.value = value;
                            sel.dispatchEvent(new Event('change', { bubbles: true }));
                            closeMenu();
                            renderTrigger();
                        });
                    })(opt.value, o);
                }
                frag.appendChild(o);
            }
            menu.appendChild(frag);
        }

        function onOutside(e) {
            if (menu && !menu.contains(e.target) && !trigger.contains(e.target)) { closeMenu(); }
        }
        function onKey(e) {
            if (e.key === 'Escape') { closeMenu(); }
        }
        function reposition() {
            if (menu) { positionFloating(menu, trigger); }
        }

        function openMenu() {
            if (sel.disabled) { return; }
            closeAllMenus();
            menu = document.createElement('div');
            menu.className = 'reg-select-menu';
            menu.setAttribute('role', 'listbox');
            menu.__trigger = trigger;
            document.body.appendChild(menu);
            renderMenu();
            positionFloating(menu, trigger);
            trigger.classList.add('is-open');
            document.addEventListener('mousedown', onOutside, true);
            document.addEventListener('keydown', onKey, true);
            window.addEventListener('resize', reposition);
            window.addEventListener('scroll', reposition, true);
        }

        function closeMenu() {
            if (menu) { menu.remove(); menu = null; }
            trigger.classList.remove('is-open');
            document.removeEventListener('mousedown', onOutside, true);
            document.removeEventListener('keydown', onKey, true);
            window.removeEventListener('resize', reposition);
            window.removeEventListener('scroll', reposition, true);
        }

        trigger.addEventListener('click', function () {
            if (menu) { closeMenu(); } else { openMenu(); }
        });

        sel.addEventListener('change', renderTrigger);
        if (window.MutationObserver) {
            new MutationObserver(renderTrigger).observe(sel, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['disabled']
            });
        }
        renderTrigger();
    }

    /* ── Custom date picker ─────────────────────────────────────────── */
    var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'];
    var MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var DOWS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

    function pad(n) { return (n < 10 ? '0' : '') + n; }

    function parseISO(v) {
        if (!v) { return null; }
        var p = String(v).split('-');
        if (p.length !== 3) { return null; }
        var d = new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10));
        return isNaN(d.getTime()) ? null : d;
    }

    function toISO(d) {
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    function enhanceDate(input) {
        if (!input || input.dataset.calReady === '1') { return; }
        input.dataset.calReady = '1';

        var wrap = input.parentElement;
        var icon = document.createElement('i');
        icon.className = 'fa-solid fa-calendar-days reg-calendar-icon';
        icon.setAttribute('aria-hidden', 'true');
        if (wrap) { wrap.appendChild(icon); }

        var cal = null;
        var view = new Date();
        var mode = 'day';
        var yearPage = null;
        var today = new Date();
        today.setHours(0, 0, 0, 0);

        function yearBounds() {
            var maxD = parseISO(input.max);
            var minD = parseISO(input.min);
            var maxY = maxD ? maxD.getFullYear() : today.getFullYear();
            var minY = minD ? minD.getFullYear() : maxY - 100;
            if (minY > maxY) { minY = maxY; }
            return { min: minY, max: maxY };
        }

        function yearPageStart() {
            var b = yearBounds();
            if (yearPage !== null) { return yearPage; }
            var y = view.getFullYear();
            var start = b.min + (Math.floor((y - b.min) / 12) * 12);
            if (start + 11 < b.min) { start = b.min; }
            return start;
        }

        function monthCell(y, m, day, muted) {
            var d = new Date(y, m, day);
            var max = parseISO(input.max);
            var min = parseISO(input.min);
            var sel = parseISO(input.value);
            var cls = 'reg-calendar-day';
            if (muted) { cls += ' is-muted'; }
            if (sel && sel.getFullYear() === d.getFullYear() && sel.getMonth() === d.getMonth() && sel.getDate() === d.getDate()) {
                cls += ' is-selected';
            } else if (today.getFullYear() === d.getFullYear() && today.getMonth() === d.getMonth() && today.getDate() === d.getDate()) {
                cls += ' is-today';
            }
            var disabled = (max && d > max) || (min && d < min);
            return '<button type="button" class="' + cls + '" data-y="' + y + '" data-m="' + m + '" data-d="' + day + '"' + (disabled ? ' disabled' : '') + '>' + day + '</button>';
        }

        function render() {
            var y = view.getFullYear();
            var m = view.getMonth();
            var firstDow = new Date(y, m, 1).getDay();
            var daysInMonth = new Date(y, m + 1, 0).getDate();
            var prevDays = new Date(y, m, 0).getDate();
            var cells = Math.ceil((firstDow + daysInMonth) / 7) * 7;

            var html = '';
            var maxDate = parseISO(input.max);
            var minDate = parseISO(input.min);
            var bounds = yearBounds();

            if (mode === 'month') {
                html += '<div class="reg-calendar-head">';
                html += '<div class="reg-calendar-nav"><button type="button" data-year-nav="-1" aria-label="Previous year"' + (y <= bounds.min ? ' disabled' : '') + '>&#8249;</button></div>';
                html += '<button type="button" class="reg-calendar-title-btn" data-act="to-years" title="Choose year">' + y + '</button>';
                html += '<div class="reg-calendar-nav"><button type="button" data-year-nav="1" aria-label="Next year"' + (y >= bounds.max ? ' disabled' : '') + '>&#8250;</button></div>';
                html += '</div>';
                html += '<div class="reg-calendar-months">';
                for (var mi = 0; mi < 12; mi++) {
                    var mStart = new Date(y, mi, 1);
                    var mEnd = new Date(y, mi + 1, 0);
                    var mDisabled = (maxDate && mStart > maxDate) || (minDate && mEnd < minDate);
                    var mCls = 'reg-calendar-month' + (mi === m ? ' is-selected' : '');
                    html += '<button type="button" class="' + mCls + '" data-month="' + mi + '"' + (mDisabled ? ' disabled' : '') + '>' + MONTHS_SHORT[mi] + '</button>';
                }
                html += '</div>';
                html += '<div class="reg-calendar-foot"><button type="button" data-act="clear">Clear</button>';
                html += '<button type="button" data-act="to-days">Back</button></div>';
            } else if (mode === 'year') {
                var pageStart = yearPageStart();
                html += '<div class="reg-calendar-head">';
                html += '<div class="reg-calendar-nav"><button type="button" data-year-page="-1" aria-label="Earlier years"' + (pageStart <= bounds.min ? ' disabled' : '') + '>&#8249;</button></div>';
                html += '<span class="reg-calendar-title">' + pageStart + '&#8211;' + (pageStart + 11) + '</span>';
                html += '<div class="reg-calendar-nav"><button type="button" data-year-page="1" aria-label="Later years"' + ((pageStart + 11) >= bounds.max ? ' disabled' : '') + '>&#8250;</button></div>';
                html += '</div>';
                html += '<div class="reg-calendar-months reg-calendar-years">';
                for (var yy = pageStart; yy <= pageStart + 11; yy++) {
                    if (yy < bounds.min || yy > bounds.max) { continue; }
                    var yCls = 'reg-calendar-month' + (yy === y ? ' is-selected' : '');
                    html += '<button type="button" class="' + yCls + '" data-year="' + yy + '">' + yy + '</button>';
                }
                html += '</div>';
                html += '<div class="reg-calendar-foot"><button type="button" data-act="clear">Clear</button>';
                html += '<button type="button" data-act="to-months">Back</button></div>';
            } else {
                html += '<div class="reg-calendar-head">';
                html += '<button type="button" class="reg-calendar-title-btn" data-act="to-months" title="Choose month and year">' + MONTHS[m] + ' ' + y + '</button>';
                html += '<div class="reg-calendar-nav"><button type="button" data-nav="-1" aria-label="Previous month">&#8249;</button>';
                html += '<button type="button" data-nav="1" aria-label="Next month">&#8250;</button></div></div>';
                html += '<div class="reg-calendar-grid">';
                for (var d = 0; d < DOWS.length; d++) { html += '<div class="reg-calendar-dow">' + DOWS[d] + '</div>'; }
                for (var i = firstDow - 1; i >= 0; i--) { html += monthCell(y, m - 1, prevDays - i, true); }
                for (var day = 1; day <= daysInMonth; day++) { html += monthCell(y, m, day, false); }
                for (var k = 1; k <= cells - firstDow - daysInMonth; k++) { html += monthCell(y, m + 1, k, true); }
                html += '</div>';
                html += '<div class="reg-calendar-foot"><button type="button" data-act="clear">Clear</button>';
                html += '<button type="button" data-act="today"' + ((maxDate && today > maxDate) ? ' disabled' : '') + '>Today</button></div>';
            }

            cal.innerHTML = html;

            var navs = cal.querySelectorAll('[data-nav]');
            for (var n = 0; n < navs.length; n++) {
                navs[n].addEventListener('click', function (e) {
                    var dir = parseInt(e.currentTarget.getAttribute('data-nav'), 10);
                    var mm = view.getMonth() + dir;
                    var yy = view.getFullYear();
                    if (mm < 0) { mm = 11; yy--; }
                    if (mm > 11) { mm = 0; yy++; }
                    view = new Date(yy, mm, 1);
                    render();
                });
            }

            var yNavs = cal.querySelectorAll('[data-year-nav]');
            for (var yn = 0; yn < yNavs.length; yn++) {
                yNavs[yn].addEventListener('click', function (e) {
                    var dir = parseInt(e.currentTarget.getAttribute('data-year-nav'), 10);
                    view = new Date(view.getFullYear() + dir, view.getMonth(), 1);
                    yearPage = null;
                    render();
                });
            }

            var yPages = cal.querySelectorAll('[data-year-page]');
            for (var yp = 0; yp < yPages.length; yp++) {
                yPages[yp].addEventListener('click', function (e) {
                    var dir = parseInt(e.currentTarget.getAttribute('data-year-page'), 10);
                    var b = yearBounds();
                    var np = yearPageStart() + (dir * 12);
                    if (np < b.min) { np = b.min; }
                    if (np + 11 > b.max) { np = b.max - 11; }
                    yearPage = np;
                    render();
                });
            }

            var monthBtns = cal.querySelectorAll('[data-month]');
            for (var mb = 0; mb < monthBtns.length; mb++) {
                monthBtns[mb].addEventListener('click', function (e) {
                    var mi = parseInt(e.currentTarget.getAttribute('data-month'), 10);
                    view = new Date(view.getFullYear(), mi, 1);
                    mode = 'day';
                    yearPage = null;
                    render();
                });
            }

            var yearBtns = cal.querySelectorAll('[data-year]');
            for (var yb = 0; yb < yearBtns.length; yb++) {
                yearBtns[yb].addEventListener('click', function (e) {
                    var yy = parseInt(e.currentTarget.getAttribute('data-year'), 10);
                    view = new Date(yy, view.getMonth(), 1);
                    mode = 'month';
                    yearPage = null;
                    render();
                });
            }

            var acts = cal.querySelectorAll('[data-act]');
            for (var ac = 0; ac < acts.length; ac++) {
                acts[ac].addEventListener('click', function (e) {
                    var act = e.currentTarget.getAttribute('data-act');
                    if (act === 'to-months') { mode = 'month'; yearPage = null; }
                    else if (act === 'to-years') { mode = 'year'; yearPage = yearPageStart(); }
                    else if (act === 'to-days') { mode = 'day'; yearPage = null; }
                    else { return; }
                    render();
                });
            }

            var days = cal.querySelectorAll('.reg-calendar-day');
            for (var j = 0; j < days.length; j++) {
                days[j].addEventListener('click', function (e) {
                    var b = e.currentTarget;
                    var picked = new Date(parseInt(b.getAttribute('data-y'), 10), parseInt(b.getAttribute('data-m'), 10), parseInt(b.getAttribute('data-d'), 10));
                    input.value = toISO(picked);
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    closeCal();
                });
            }

            var clearBtn = cal.querySelector('[data-act="clear"]');
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    input.value = '';
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    closeCal();
                });
            }
            var todayBtn = cal.querySelector('[data-act="today"]');
            if (todayBtn) {
                todayBtn.addEventListener('click', function () {
                    input.value = toISO(today);
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    closeCal();
                });
            }
        }

        function onOutside(e) {
            if (cal && !cal.contains(e.target) && e.target !== input && !(icon && icon.contains(e.target))) {
                closeCal();
            }
        }
        function onKey(e) {
            if (e.key === 'Escape') { closeCal(); }
        }
        function reposition() {
            if (cal) { positionFloating(cal, input); }
        }

        function openCal() {
            if (cal) { reposition(); return; }
            var selected = parseISO(input.value);
            view = selected ? new Date(selected.getFullYear(), selected.getMonth(), 1) : new Date(today.getFullYear(), today.getMonth(), 1);
            mode = 'day';
            yearPage = null;
            cal = document.createElement('div');
            cal.className = 'reg-calendar';
            document.body.appendChild(cal);
            render();
            positionFloating(cal, input);
            document.addEventListener('mousedown', onOutside, true);
            document.addEventListener('keydown', onKey, true);
            window.addEventListener('resize', reposition);
            window.addEventListener('scroll', reposition, true);
        }

        function closeCal() {
            if (cal) { cal.remove(); cal = null; }
            document.removeEventListener('mousedown', onOutside, true);
            document.removeEventListener('keydown', onKey, true);
            window.removeEventListener('resize', reposition);
            window.removeEventListener('scroll', reposition, true);
        }

        // Block the native picker and open ours instead
        input.addEventListener('mousedown', function (e) { e.preventDefault(); input.focus(); openCal(); });
        input.addEventListener('click', function (e) { e.preventDefault(); openCal(); });
        input.addEventListener('focus', function () { setTimeout(openCal, 0); });
        input.addEventListener('blur', function () { setTimeout(function () { if (cal && !cal.contains(document.activeElement)) { closeCal(); } }, 150); });
        if (icon) {
            icon.addEventListener('mousedown', function (e) { e.preventDefault(); e.stopPropagation(); });
            icon.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); openCal(); });
        }
    }

    /* ── Password visibility toggle (plain JS — no Alpine dependency) ── */
    function initPasswordToggle() {
        var btn = document.getElementById('toggle-password');
        var input = document.getElementById('login-password');
        if (!btn || !input) { return; }

        // Chrome / Edge ship their own built-in "reveal password" eye inside the
        // field (browser-level, cannot be styled or hidden by the page).
        // Showing our own button on top of it produces TWO eyes — so we hide ours
        // there and keep it for browsers without a built-in control (Firefox/Safari).
        var ua = navigator.userAgent || '';
        var hasNativeReveal = (/Chrome|Chromium|Edg|OPR/i).test(ua) && !(/Firefox/i).test(ua);
        if (hasNativeReveal) {
            btn.style.display = 'none';
            return;
        }

        btn.addEventListener('click', function () {
            var show = (input.type === 'password');
            input.type = show ? 'text' : 'password';
            btn.classList.toggle('is-visible', show);
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            input.focus();
        });
    }

    /* ── Boot ────────────────────────────────────────────────────────── */
    function init() {
        var selects = document.querySelectorAll('.reg-select-wrap select.reg-input');
        for (var i = 0; i < selects.length; i++) { enhanceSelect(selects[i]); }
        var dates = document.querySelectorAll('input[type="date"].reg-input');
        for (var j = 0; j < dates.length; j++) { enhanceDate(dates[j]); }
        initPasswordToggle();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
