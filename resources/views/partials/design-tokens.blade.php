{{--
    Shared design-token layer.

    Included by every browser-rendered layout (storefront, admin, auth portal) so
    a single source of truth drives both light and dark themes.

    Layer 1  primitives  — raw values, never theme-dependent
    Layer 2  semantic    — purpose aliases (light values here, dark below)
    Layer 3  component   — per-component bindings

    The CMYK brand aliases (--cmyk-*) stay in each layout's own :root because the
    auth portal uses a slightly different brand yellow.
--}}
:root {
    /* ---- Layer 1: PRIMITIVES (raw values) ---- */
    --p-white: #FFFFFF;

    --p-ink-950: #0B1120;
    --p-ink-900: #0F172A;
    --p-ink-850: #172033;
    --p-ink-800: #1E293B;
    --p-ink-700: #334155;
    --p-ink-600: #475569;
    --p-ink-500: #64748B;
    --p-ink-400: #94A3B8;
    --p-ink-300: #CBD5E1;
    --p-ink-200: #E2E8F0;
    --p-ink-100: #F1F5F9;
    --p-ink-050: #F8FAFC;

    --p-cyan-050: #E6F4FF;
    --p-cyan-100: #BAE6FD;
    --p-cyan-300: #7DD3FC;
    --p-cyan-400: #38BDF8;
    --p-cyan-500: #0099FF;
    --p-cyan-600: #0080FF;

    --p-magenta-050: #FDE8F5;
    --p-magenta-100: #FBCFE8;
    --p-magenta-400: #F472B6;
    --p-magenta-500: #EC008C;
    --p-magenta-600: #C80075;

    --p-yellow-050: #FEF9C3;
    --p-yellow-100: #FDE047;
    --p-yellow-500: #EAB308;
    --p-yellow-600: #CA8A04;

    --p-violet-050: #F5F3FF;
    --p-violet-100: #DDD6FE;
    --p-violet-500: #7C3AED;
    --p-violet-600: #6D28D9;

    --p-green-050: #F0FDF4;
    --p-green-100: #BBF7D0;
    --p-green-500: #22C55E;
    --p-green-600: #16A34A;
    --p-green-700: #15803D;
    --p-green-800: #14532D;

    --p-red-050: #FEF2F2;
    --p-red-100: #FEE2E2;
    --p-red-500: #EF4444;
    --p-red-600: #DC2626;
    --p-red-700: #B91C1C;
    --p-red-800: #991B1B;

    --p-amber-050: #FEF3C7;
    --p-amber-100: #FDE68A;
    --p-amber-500: #F59E0B;
    --p-amber-600: #D97706;
    --p-amber-700: #B45309;
    --p-amber-800: #92400E;

    --p-orange-050: #FFF7ED;
    --p-orange-100: #FED7AA;
    --p-orange-500: #EA580C;
    --p-orange-600: #C2410C;
    --p-orange-700: #9A3412;

    --p-gray-050: #F9FAFB;
    --p-gray-100: #F3F4F6;
    --p-gray-200: #E5E7EB;
    --p-gray-300: #D1D5DB;
    --p-gray-400: #9CA3AF;
    --p-gray-500: #6B7280;
    --p-gray-600: #4B5563;
    --p-gray-700: #374151;
    --p-gray-800: #1F2937;
    --p-gray-900: #111827;

    /* ---- Layer 2: SEMANTIC (light theme is the default) ---- */
    --accent-light: var(--p-cyan-050);
    --primary-dark: var(--cmyk-key);

    --bg-page: var(--p-ink-050);
    --bg-surface: var(--p-white);
    --bg-surface-2: #FBFCFD;
    --bg-subtle: var(--p-ink-100);
    --bg-stripe: var(--p-ink-050);
    --bg-elevated: var(--p-white);
    --bg-inverse: var(--p-ink-900);
    --bg-overlay: rgba(15, 23, 42, 0.5);

    --text-main: var(--p-ink-900);
    --text-muted: var(--p-ink-500);
    --text-subtle: var(--p-ink-400);
    --text-disabled: var(--p-ink-300);
    --text-inverse: var(--p-white);
    --text-accent: var(--p-cyan-600);
    --text-success: var(--p-green-700);
    --text-warning: var(--p-amber-700);
    --text-danger: var(--p-red-700);
    --text-magenta: var(--p-magenta-600);
    --text-violet: var(--p-violet-600);

    --border-color: var(--p-ink-200);
    --border-strong: var(--p-ink-300);
    --border-accent: var(--p-cyan-500);

    --accent-color: var(--p-cyan-500);
    --accent-hover: var(--p-cyan-600);
    --accent-soft: var(--p-cyan-050);
    --accent-contrast: var(--p-white);
    --accent-ring: rgba(0, 153, 255, 0.35);

    --success-color: var(--p-green-600);
    --success-soft: var(--p-green-050);
    --success-border: var(--p-green-100);

    --warning-color: var(--p-amber-600);
    --warning-soft: var(--p-amber-050);
    --warning-border: var(--p-amber-100);

    --danger-color: var(--p-red-600);
    --danger-soft: var(--p-red-050);
    --danger-border: var(--p-red-100);

    --info-color: var(--p-cyan-600);
    --info-soft: var(--p-cyan-050);
    --info-border: var(--p-cyan-100);

    --brand-cyan-fg: var(--p-cyan-500);
    --brand-cyan-soft: var(--p-cyan-050);
    --brand-magenta-fg: var(--p-magenta-500);
    --brand-magenta-soft: var(--p-magenta-050);
    --brand-yellow-fg: var(--p-yellow-600);
    --brand-yellow-soft: var(--p-yellow-050);
    --brand-violet-fg: var(--p-violet-500);
    --brand-violet-soft: var(--p-violet-050);
    --brand-key-fg: var(--p-ink-800);

    --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.04);
    --shadow-md: 0 4px 12px -4px rgba(15, 23, 42, 0.10);
    --shadow-lg: 0 16px 40px -16px rgba(15, 23, 42, 0.18);

    --radius-sm: 8px;
    --radius-md: 10px;
    --radius-lg: 16px;
    --radius-xl: 20px;
    --radius-pill: 9999px;

    /* ---- Layer 3: COMPONENT ---- */
    --btn-primary-bg: var(--accent-color);
    --btn-primary-bg-hover: var(--accent-hover);
    --btn-primary-text: var(--accent-contrast);
    --btn-outline-border: var(--border-strong);
    --btn-outline-text: var(--text-main);
    --btn-outline-bg-hover: var(--bg-subtle);
    --btn-ghost-text: var(--text-main);
    --btn-ghost-bg-hover: var(--bg-subtle);

    --card-bg: var(--bg-surface);
    --card-border: var(--border-color);
    --card-shadow: var(--shadow-sm);
    --card-shadow-hover: var(--shadow-md);

    /* Header chrome. The storefront header is a glass layer, the admin
       topbar is opaque — they share the same colour language. */
    --header-bg: rgba(255, 255, 255, 0.88);
    --header-solid: var(--p-white);
    --header-border: var(--border-color);
    --header-shadow: 0 4px 18px -2px rgba(15, 23, 42, 0.07);
    --header-text: var(--text-main);
    --header-text-muted: var(--text-muted);
    --header-hover-bg: var(--bg-subtle);
    --header-active-bg: var(--accent-soft);
    --header-control-bg: var(--bg-surface);
    --header-control-border: var(--border-strong);
    --header-control-text: var(--text-muted);
    --header-brand-chip: transparent;

    --input-bg: var(--bg-surface);
    --input-border: var(--border-strong);
    --input-text: var(--text-main);
    --input-placeholder: var(--p-ink-400);

    --focus-ring: 0 0 0 3px var(--accent-ring);
}

/* ---- Layer 2 overrides: DARK theme ---- */
html.theme-dark {
    --bg-page: #080D18;
    --bg-surface: #0E1626;
    --bg-surface-2: #131D31;
    --bg-subtle: #18233A;
    --bg-stripe: #131D31;
    --bg-elevated: #16223A;
    --bg-inverse: var(--p-white);
    --bg-overlay: rgba(2, 6, 16, 0.68);

    /* The key black was a foreground colour; on a dark surface it has to
       become the light text colour instead. */
    --primary-dark: var(--text-main);

    --text-main: #E6EDF7;
    --text-muted: #94A3B8;
    --text-subtle: #64748B;
    --text-disabled: #475569;
    --text-inverse: var(--p-ink-900);
    --text-accent: var(--p-cyan-400);
    --text-success: #4ADE80;
    --text-warning: #FBBF24;
    --text-danger: #F87171;
    --text-magenta: #F472B6;
    --text-violet: #A78BFA;

    --border-color: #1E293B;
    --border-strong: #334155;
    --border-accent: var(--p-cyan-400);

    --accent-color: var(--p-cyan-400);
    --accent-hover: var(--p-cyan-500);
    --accent-soft: rgba(56, 189, 248, 0.12);
    --accent-contrast: #04121F;
    --accent-ring: rgba(56, 189, 248, 0.45);

    --success-color: #4ADE80;
    --success-soft: rgba(34, 197, 94, 0.14);
    --success-border: rgba(34, 197, 94, 0.32);

    --warning-color: #FBBF24;
    --warning-soft: rgba(245, 158, 11, 0.14);
    --warning-border: rgba(245, 158, 11, 0.32);

    --danger-color: #F87171;
    --danger-soft: rgba(239, 68, 68, 0.14);
    --danger-border: rgba(239, 68, 68, 0.32);

    --info-color: var(--p-cyan-400);
    --info-soft: rgba(56, 189, 248, 0.12);
    --info-border: rgba(56, 189, 248, 0.3);

    /* Soft brand tints become translucent so CMYK chips do not glare on dark. */
    --p-cyan-050: rgba(56, 189, 248, 0.14);
    --p-cyan-100: rgba(56, 189, 248, 0.26);
    --p-magenta-050: rgba(236, 0, 140, 0.16);
    --p-magenta-100: rgba(236, 0, 140, 0.28);
    --p-yellow-050: rgba(234, 179, 8, 0.16);
    --p-yellow-100: rgba(234, 179, 8, 0.28);
    --p-violet-050: rgba(124, 58, 237, 0.16);
    --p-violet-100: rgba(124, 58, 237, 0.28);
    --p-green-050: rgba(34, 197, 94, 0.14);
    --p-green-100: rgba(34, 197, 94, 0.28);
    --p-red-050: rgba(239, 68, 68, 0.14);
    --p-red-100: rgba(239, 68, 68, 0.28);
    --p-amber-050: rgba(245, 158, 11, 0.14);
    --p-amber-100: rgba(245, 158, 11, 0.28);
    --p-orange-050: rgba(234, 88, 12, 0.14);
    --p-orange-100: rgba(234, 88, 12, 0.28);

    --brand-cyan-fg: var(--p-cyan-400);
    --brand-cyan-soft: rgba(56, 189, 248, 0.12);
    --brand-magenta-fg: #F472B6;
    --brand-magenta-soft: rgba(236, 0, 140, 0.16);
    --brand-yellow-fg: var(--p-yellow-500);
    --brand-yellow-soft: rgba(234, 179, 8, 0.16);
    --brand-violet-fg: #A78BFA;
    --brand-violet-soft: rgba(124, 58, 237, 0.16);
    --brand-key-fg: var(--p-ink-300);

    --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.40);
    --shadow-md: 0 4px 14px -4px rgba(0, 0, 0, 0.55);
    --shadow-lg: 0 18px 44px -18px rgba(0, 0, 0, 0.70);

    /* Dark header is a deep brand blue rather than near-black, so the chrome
       reads as blue while keeping white text at ~8.5:1. */
    --header-bg: rgba(12, 74, 110, 0.92);
    --header-solid: #0C4A6E;
    --header-border: rgba(255, 255, 255, 0.16);
    --header-shadow: 0 4px 20px -2px rgba(3, 20, 36, 0.55);
    --header-text: #FFFFFF;
    --header-text-muted: rgba(255, 255, 255, 0.78);
    --header-hover-bg: rgba(255, 255, 255, 0.16);
    --header-active-bg: rgba(255, 255, 255, 0.22);
    --header-control-bg: rgba(255, 255, 255, 0.12);
    --header-control-border: rgba(255, 255, 255, 0.26);
    --header-control-text: rgba(255, 255, 255, 0.9);
    --header-brand-chip: #FFFFFF;

    --input-placeholder: #64748B;
}

/* ---- Global focus ring (visible for keyboard navigation) ---- */
:focus-visible {
    outline: 2px solid var(--accent-color);
    outline-offset: 2px;
    border-radius: 4px;
}

/* ============================================================
   Tailwind colour-utility overrides
   The Tailwind CDN injects literal colours, which would ignore the
   token layer. These re-point the remaining surface utilities at the
   theme tokens so utility markup themes with everything else.
   ============================================================ */
html.theme-dark .bg-white { background-color: var(--bg-surface); }
html.theme-dark .border-black { border-color: var(--border-strong); }
html.theme-dark .bg-gray-50 { background-color: var(--bg-surface-2); }
html.theme-dark .bg-slate-50 { background-color: var(--bg-surface-2); }
html.theme-dark .bg-sky-50 { background-color: var(--accent-soft); }
html.theme-dark .bg-rose-50 { background-color: var(--danger-soft); }
html.theme-dark .bg-emerald-50 { background-color: var(--success-soft); }
html.theme-dark .bg-amber-50 { background-color: var(--warning-soft); }
html.theme-dark .border-sky-200 { border-color: var(--info-border); }
html.theme-dark .border-rose-200 { border-color: var(--danger-border); }
html.theme-dark .border-emerald-200 { border-color: var(--success-border); }
html.theme-dark .border-amber-200 { border-color: var(--warning-border); }
html.theme-dark .text-sky-600, html.theme-dark .text-sky-700 { color: var(--accent-color); }
html.theme-dark .text-sky-800, html.theme-dark .text-sky-900 { color: var(--accent-hover); }
html.theme-dark .text-rose-600, html.theme-dark .text-rose-700, html.theme-dark .text-rose-800 { color: var(--text-danger); }
html.theme-dark .text-emerald-600, html.theme-dark .text-emerald-700, html.theme-dark .text-emerald-800 { color: var(--text-success); }
html.theme-dark .text-amber-600, html.theme-dark .text-amber-700, html.theme-dark .text-amber-800 { color: var(--text-warning); }
html.theme-dark .text-red-600, html.theme-dark .text-red-700, html.theme-dark .text-red-800 { color: var(--text-danger); }

/* ===== Theme toggle button (partials.theme-toggle) ===== */
.theme-toggle {
    width: 44px;
    height: 44px;
    min-width: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-strong);
    background: var(--bg-surface);
    color: var(--text-muted);
    cursor: pointer;
    padding: 0;
    transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}
.theme-toggle:hover {
    background: var(--accent-soft);
    border-color: var(--accent-color);
    color: var(--accent-hover);
}
.theme-toggle__icons {
    position: relative;
    width: 20px;
    height: 20px;
    display: block;
}
.theme-toggle__icon {
    position: absolute;
    inset: 0;
    width: 20px;
    height: 20px;
    transition: opacity 0.2s ease, transform 0.25s ease;
}
/* Light theme shows the moon (the action is "go dark"); dark theme shows the sun. */
.theme-toggle__icon--moon { opacity: 1; transform: scale(1) rotate(0); }
.theme-toggle__icon--sun  { opacity: 0; transform: scale(0.6) rotate(-30deg); }
html.theme-dark .theme-toggle__icon--moon { opacity: 0; transform: scale(0.6) rotate(30deg); }
html.theme-dark .theme-toggle__icon--sun  { opacity: 1; transform: scale(1) rotate(0); }

/* Drawer variant — a full-width labelled row */
.theme-toggle--drawer {
    width: 100%;
    height: 44px;
    justify-content: flex-start;
    gap: 0.85rem;
    padding: 0 0.95rem;
    background: transparent;
    border-color: var(--border-color);
}
.theme-toggle--drawer .theme-toggle__label {
    font-size: 0.94rem;
    font-weight: 600;
    color: var(--text-muted);
}
