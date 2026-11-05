# Grafika South — Landing Page Redesign & Dark Mode Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild the public landing page on a 3-layer token system with a "Clean Studio" visual design, and add a persistent light/dark theme toggle to the landing page and shared site chrome — without changing any existing functionality.

**Architecture:** A three-layer CSS custom-property system (primitive → semantic → component) is declared in the shared layout. Dark mode is a second set of semantic overrides keyed on `html.theme-dark`; a small inline controller in `<head>` resolves the stored preference before paint (no FOUC) and a delegated click listener flips and persists it. The landing view is rebuilt on top of those tokens so no hardcoded hex remains, and every inline `style=""` attribute is replaced with a token or a class.

**Tech Stack:** Laravel 12 + Blade, Tailwind CDN (preflight off, no build step), FontAwesome 6.4, vanilla JS IIFEs, PHPUnit 11 + in-memory SQLite.

## Global Constraints

- **No build step.** The project has no `@vite` directive in any view. Do NOT add one, do NOT add npm dependencies, do NOT create a bundler pipeline.
- **Dark mode scope is landing + shared chrome only.** `html.theme-dark` is applied *only* on the `landing` route. `/products`, `/products/{id}`, `/cart`, `/checkout`, `/login`, `/register`, all `admin.*`, `customer.*`, `super-admin.*` routes MUST stay light.
- **No route, model, controller, migration, or seed changes.**
- **Do not modify** `public/css/style.css`, `public/js/main.js` (both unreferenced legacy files), `resources/views/layouts/admin.blade.php`, or anything under `resources/views/{admin,customer,super-admin,auth,products}/`.
- **Preserve these existing DOM contracts** — `navbar-search-form` (class on the landing search form), the literal string `Search products`, the ids `heroCarousel`, `heroCarouselTrack`, `heroPrevBtn`, `heroNextBtn`, `heroDots`, `floatingSearchWidget`, `bubbleToggleBtn`, `bubbleSearchInput`, `bubbleCloseBtn`, `floatingBubbleForm`, and the classes `hero-slide`, `carousel-dot`, `product-storefront-card`.
- **No emoji as UI icons.** FontAwesome (`fa-solid fa-*`) or inline SVG only.
- **No `git` binary exists** in this environment and the project is not a repository. Skip every commit step; use the stated verification checkpoint instead.
- **Contrast:** body text ≥ 4.5:1, large text ≥ 3:1, in BOTH themes.
- **Touch targets:** interactive elements ≥ 44×44px, `cursor: pointer` on every clickable element.
- **Motion:** 150–250ms for UI transitions, transform/opacity only; `@media (prefers-reduced-motion: reduce)` disables the Ken Burns drift, the bubble pulse and all transitions.
- **Copy rule:** every claim on the landing page must be traceable to an existing capability or existing app copy. Never invent business statistics (order counts, customer counts, years in business).

---

## File Structure

| File | Responsibility |
|---|---|
| `resources/views/layouts/app.blade.php` | Owns the token system (all three layers), the theme controller script, the `$darkModeEnabled` flag, and the themed shared chrome (header, nav, drawer, footer, modals) |
| `resources/views/partials/theme-toggle.blade.php` | Owns the single toggle button markup + its SVG icons. Included three times |
| `resources/views/welcome.blade.php` | Owns the landing page markup and its landing-only CSS. All colours read from tokens |
| `tests/Feature/LandingDarkModeTest.php` | Asserts theme wiring exists on `landing` and is absent elsewhere |
| `tests/Feature/LandingPageSectionsTest.php` | Asserts the redesigned section structure and that legacy DOM contracts survive |

---

### Task 1: Token layer + theme controller in the shared layout

**Files:**
- Modify: `resources/views/layouts/app.blade.php` — the `:root` block at lines 38–71, the `<head>` (lines 1–35), the `<header>` element (line 1170), the desktop nav group (lines 1187–1311), the mobile action bar (lines 1314–1332), the mobile drawer, and the footer CSS (lines 1107–1160)
- Test: `tests/Feature/LandingDarkModeTest.php`

**Interfaces:**
- Consumes: nothing (first task).
- Produces:
  - CSS custom properties `--p-*` (primitive), `--bg-*`, `--text-*`, `--border-*`, `--accent-*`, `--shadow-*` (semantic), `--btn-*`, `--card-*`, `--header-*`, `--input-*` (component).
  - `html.theme-dark` class on `<html>` when dark.
  - A `[data-theme-toggle]` attribute on every toggle button; the controller in the layout head handles clicks on it.
  - `localStorage` key `grafika-theme` holding `'light'` or `'dark'`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/LandingDarkModeTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingDarkModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_exposes_theme_toggle_and_dark_token_layer(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('data-theme-toggle', false);
        $response->assertSee('theme-dark', false);
        $response->assertSee('grafika-theme', false);
    }

    public function test_non_landing_public_routes_stay_light_and_expose_no_toggle(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertDontSee('data-theme-toggle', false);
    }

    public function test_layout_declares_primitive_and_semantic_token_layers(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertStringContainsString('--p-cyan-500', $layout);
        $this->assertStringContainsString('--bg-page', $layout);
        $this->assertStringContainsString('--accent-color', $layout);
        $this->assertStringContainsString('html.theme-dark', $layout);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=LandingDarkModeTest`
Expected: FAIL — `data-theme-toggle` is not present on the landing page.

- [ ] **Step 3: Replace the `:root` block with the 3-layer token system**

In `resources/views/layouts/app.blade.php`, replace the existing `:root { ... }` block (lines 38–71) with the following. Keep the existing `--cmyk-*` variables — other views still reference them.

```css
        /* ============================================================
           DESIGN TOKENS — 3 layers: primitive → semantic → component
           Layer 1 primitives are raw values and never change per theme.
           ============================================================ */
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
            --p-cyan-400: #38BDF8;
            --p-cyan-500: #0099FF;
            --p-cyan-600: #0080FF;

            --p-magenta-050: #FDE8F5;
            --p-magenta-500: #EC008C;
            --p-magenta-600: #C80075;

            --p-yellow-050: #FEF9C3;
            --p-yellow-500: #EAB308;
            --p-yellow-600: #CA8A04;

            --p-green-500: #22C55E;
            --p-amber-500: #F59E0B;
            --p-red-500: #EF4444;
            --p-red-600: #DC2626;

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

            /* ---- Layer 2: SEMANTIC (light theme is the default) ---- */
            --bg-page: var(--p-ink-050);
            --bg-surface: var(--p-white);
            --bg-surface-2: #FBFCFD;
            --bg-subtle: var(--p-ink-100);
            --bg-inverse: var(--p-ink-900);

            --text-main: var(--p-ink-900);
            --text-muted: var(--p-ink-500);
            --text-subtle: var(--p-ink-400);
            --text-inverse: var(--p-white);
            --text-accent: var(--p-cyan-600);

            --border-color: var(--p-ink-200);
            --border-strong: var(--p-ink-300);

            --accent-color: var(--p-cyan-500);
            --accent-hover: var(--p-cyan-600);
            --accent-soft: var(--p-cyan-050);
            --accent-contrast: var(--p-white);
            --accent-ring: rgba(0, 153, 255, 0.35);

            --success-color: var(--p-green-500);
            --warning-color: var(--p-amber-500);
            --danger-color: var(--p-red-600);
            --danger-soft: #FEE2E2;

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

            --header-bg: rgba(255, 255, 255, 0.88);
            --header-border: var(--border-color);
            --header-shadow: 0 4px 18px -2px rgba(15, 23, 42, 0.07);

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
            --bg-inverse: var(--p-white);

            --text-main: #E6EDF7;
            --text-muted: #94A3B8;
            --text-subtle: #64748B;
            --text-inverse: var(--p-ink-900);
            --text-accent: var(--p-cyan-400);

            --border-color: #1E293B;
            --border-strong: #334155;

            --accent-color: var(--p-cyan-400);
            --accent-hover: var(--p-cyan-500);
            --accent-soft: rgba(56, 189, 248, 0.12);
            --accent-contrast: #04121F;
            --accent-ring: rgba(56, 189, 248, 0.45);

            --danger-soft: rgba(239, 68, 68, 0.14);

            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.40);
            --shadow-md: 0 4px 14px -4px rgba(0, 0, 0, 0.55);
            --shadow-lg: 0 18px 44px -18px rgba(0, 0, 0, 0.70);

            --header-bg: rgba(14, 22, 38, 0.88);
            --header-shadow: 0 4px 18px -2px rgba(0, 0, 0, 0.50);
        }
```

- [ ] **Step 4: Add the theme controller to `<head>`**

Change `<meta name="color-scheme" content="light">` to `<meta name="color-scheme" content="light dark">`.

Then, immediately after the Tailwind config `<script>` block and **before** the `<style>` block, insert:

```blade
    @php
        // Dark mode is opt-in per route. Only the landing page ships the token
        // overrides today, so every other route must stay light.
        $darkModeEnabled = request()->route()?->getName() === 'landing';
    @endphp

    @if($darkModeEnabled)
    <script>
        (function () {
            var root = document.documentElement;
            var STORAGE_KEY = 'grafika-theme';
            var mql = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

            function readStored() {
                try { return localStorage.getItem(STORAGE_KEY); } catch (e) { return null; }
            }

            function syncToggles(dark) {
                var buttons = document.querySelectorAll('[data-theme-toggle]');
                for (var i = 0; i < buttons.length; i++) {
                    var label = dark ? 'Switch to light theme' : 'Switch to dark theme';
                    buttons[i].setAttribute('aria-pressed', dark ? 'true' : 'false');
                    buttons[i].setAttribute('aria-label', label);
                    buttons[i].setAttribute('title', label);
                }
            }

            function apply(mode) {
                var dark = mode === 'dark';
                root.classList.toggle('theme-dark', dark);
                root.style.colorScheme = dark ? 'dark' : 'light';
                syncToggles(dark);
            }

            // 1) Resolve the mode BEFORE first paint (no flash of wrong theme).
            var stored = readStored();
            var prefersDark = mql ? mql.matches : false;
            apply(stored === 'dark' || (stored !== 'light' && prefersDark) ? 'dark' : 'light');

            // 2) Toggle handler — delegated so it works for every button instance.
            document.addEventListener('click', function (e) {
                var button = e.target.closest ? e.target.closest('[data-theme-toggle]') : null;
                if (!button) return;
                var next = root.classList.contains('theme-dark') ? 'light' : 'dark';
                apply(next);
                try { localStorage.setItem(STORAGE_KEY, next); } catch (err) {}
            });

            // 3) Follow the OS preference only while the user has not chosen explicitly.
            if (mql && mql.addEventListener) {
                mql.addEventListener('change', function (e) {
                    if (!readStored()) apply(e.matches ? 'dark' : 'light');
                });
            }
        })();
    </script>
    @endif
```

- [ ] **Step 5: Remove the Tailwind `bg-white` from the `<header>` element**

Change:

```html
    <header class="site-header sticky top-0 z-50 w-full bg-white shadow-sm">
```

to:

```html
    <header class="site-header sticky top-0 z-50 w-full">
```

`.site-header` now owns the background via `--header-bg` (see Step 6), so the Tailwind utility would otherwise fight the theme.

- [ ] **Step 6: Tokenise the shared chrome**

Apply these replacements across the layout's `<style>` block and markup. Each row is a literal find → replace.

| Find | Replace |
|---|---|
| `.site-header { … background: #FFFFFF; … box-shadow: 0 4px 18px -2px rgba(15, 23, 42, 0.07), 0 2px 6px -1px rgba(15, 23, 42, 0.03); … }` | `background: var(--header-bg);` + `box-shadow: var(--header-shadow);` + add `backdrop-filter: saturate(180%) blur(12px);` |
| `.navbar { background-color: #FFFFFF; border-bottom: 1px solid #E2E8F0; … }` | `background-color: transparent; border-bottom: 1px solid var(--header-border);` |
| `.navbar-brand { … color: var(--cmyk-key); … }` | `color: var(--text-main);` |
| `.nav-link-item { color: #475569; … }` | `color: var(--text-muted);` |
| `.nav-link-item i { … color: #64748B; … }` | `color: var(--text-subtle);` |
| `.nav-link-item:hover { background-color: #F0F9FF; color: #0080FF; … }` | `background-color: var(--accent-soft); color: var(--accent-hover);` |
| `.nav-link-item.active { background-color: #E6F4FF; color: #0099FF; … }` | `background-color: var(--accent-soft); color: var(--accent-hover);` |
| `.nav-link-item.active i { color: #0099FF; }` | `color: var(--accent-hover);` |
| `.nav-divider { background-color: #E2E8F0; }` | `background-color: var(--border-color);` |
| `.mobile-nav-btn { background: #FFFFFF; border: 1.5px solid #CBD5E1; color: #334155; … }` | `background: var(--bg-surface); border: 1.5px solid var(--border-strong); color: var(--text-muted);` |
| `.mobile-nav-btn:hover { background: #F0F9FF; border-color: #0099FF; color: #0099FF; … }` | `background: var(--accent-soft); border-color: var(--accent-color); color: var(--accent-hover);` |
| `.mobile-nav-drawer { background: #FFFFFF; … }` | `background: var(--bg-surface);` |
| `.mobile-drawer-header { border-bottom: 1px solid #E2E8F0; background: #FFFFFF; … }` | `border-bottom: 1px solid var(--border-color); background: var(--bg-surface);` |
| `.mobile-drawer-close { border: 1.5px solid #CBD5E1; background: #F8FAFC; color: #64748B; … }` | `border: 1.5px solid var(--border-strong); background: var(--bg-subtle); color: var(--text-muted);` |
| `.mobile-drawer-close:hover { background: #FEE2E2; border-color: #FCA5A5; }` | `background: var(--danger-soft); border-color: var(--danger-color);` |
| `.mobile-drawer-back { border: 1.5px solid #CBD5E1; background: #F8FAFC; color: #64748B; … }` | `border: 1.5px solid var(--border-strong); background: var(--bg-subtle); color: var(--text-muted);` |
| `.mobile-drawer-back:hover { background: #F0F9FF; border-color: #0099FF; color: #0099FF; }` | `background: var(--accent-soft); border-color: var(--accent-color); color: var(--accent-hover);` |
| `.mobile-drawer-link { color: #334155; … }` | `color: var(--text-muted);` |
| `.mobile-drawer-link i { color: #64748B; … }` | `color: var(--text-subtle);` |
| `.mobile-drawer-link:hover, .mobile-drawer-link.active { background: #E6F4FF; color: #0099FF; … }` | `background: var(--accent-soft); color: var(--accent-hover);` |
| `.mobile-drawer-link:hover i, .mobile-drawer-link.active i { color: #0099FF; }` | `color: var(--accent-hover);` |
| `.mobile-drawer-divider { background: #E2E8F0; }` | `background: var(--border-color);` |
| `.mobile-drawer-user-card { background: #F8FAFC; border: 1px solid #E2E8F0; … }` | `background: var(--bg-surface-2); border: 1px solid var(--border-color);` |
| `.mobile-drawer-user-name { color: #0F172A; … }` | `color: var(--text-main);` |
| `.mobile-drawer-user-email { color: #64748B; … }` | `color: var(--text-muted);` |
| `.mobile-drawer-footer { border-top: 1px solid #E2E8F0; background: #FAFCFE; color: #64748B; … }` | `border-top: 1px solid var(--border-color); background: var(--bg-surface-2); color: var(--text-muted);` |
| `.customer-header-burger-btn { background: #FFFFFF; border: 1.5px solid #E2E8F0; color: #334155; … }` | `background: var(--bg-surface); border: 1.5px solid var(--border-color); color: var(--text-muted);` |
| `.customer-header-burger-btn:hover { background: #F0F9FF; border-color: #0099FF; color: #0099FF; … }` | `background: var(--accent-soft); border-color: var(--accent-color); color: var(--accent-hover);` |
| `.customer-header-avatar-initial { background: #E6F4FF; color: #0099FF; … }` | `background: var(--accent-soft); color: var(--accent-hover);` |
| `.top-promo-bar { background-color: var(--cmyk-key); color: #FFFFFF; … }` | `background-color: var(--bg-inverse); color: var(--text-inverse);` |

Also update the footer CSS block (`.unicorp-footer`, `.footer-col h4`, `.footer-col p`, `.footer-links a`, `.footer-links a:hover`, `.footer-bottom`) to use `var(--bg-surface)`, `var(--text-main)`, `var(--text-muted)`, `var(--border-color)` and `var(--accent-hover)`.

- [ ] **Step 7: Add the toggle to the header, mobile bar and drawer**

Create the partial in Task 2 first, then include it in three places in `layouts/app.blade.php`:

1. **Desktop nav group** — inside `.nav-items-group`, immediately before the `@guest` / `@else` fork:

```blade
                    @if($darkModeEnabled)
                        @include('partials.theme-toggle')
                        <div class="nav-divider"></div>
                    @endif
```

2. **Mobile action bar** — inside `.mobile-nav-actions`, before the cart button:

```blade
                    @if($darkModeEnabled)
                        @include('partials.theme-toggle')
                    @endif
```

3. **Mobile drawer body** — inside `.mobile-drawer-body`, at the top:

```blade
                        @if($darkModeEnabled)
                            @include('partials.theme-toggle', ['variant' => 'drawer'])
                        @endif
```

- [ ] **Step 8: Add a global focus-visible ring**

Append to the layout's `<style>` block:

```css
        :focus-visible {
            outline: 2px solid var(--accent-color);
            outline-offset: 2px;
            border-radius: 4px;
        }
```

- [ ] **Step 9: Run the test**

Run: `php artisan test --filter=LandingDarkModeTest`
Expected: FAIL — `data-theme-toggle` still absent (the partial does not exist yet). Proceed to Task 2.

---

### Task 2: The theme toggle partial

**Files:**
- Create: `resources/views/partials/theme-toggle.blade.php`
- Test: `tests/Feature/LandingDarkModeTest.php` (extend)

**Interfaces:**
- Consumes: the `[data-theme-toggle]` delegated listener installed by Task 1's head script.
- Produces: a button with `data-theme-toggle`, `aria-pressed`, `aria-label`, `title`; accepts an optional `$variant` (`'icon'` default, `'drawer'`).

- [ ] **Step 1: Create the partial**

Create `resources/views/partials/theme-toggle.blade.php`:

```blade
{{--
    Theme toggle button.

    The controller lives in layouts/app.blade.php <head>: it delegates on
    [data-theme-toggle], so this button needs no inline JavaScript.

    $variant: 'icon' (default, square button for the nav bar) | 'drawer' (full-width row)
--}}
@php
    $variant = $variant ?? 'icon';
@endphp

@if($variant === 'drawer')
    <button type="button"
            class="theme-toggle theme-toggle--drawer"
            data-theme-toggle
            aria-pressed="false"
            aria-label="Switch to dark theme"
            title="Switch to dark theme">
        <span class="theme-toggle__icons" aria-hidden="true">
            <svg class="theme-toggle__icon theme-toggle__icon--sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="4"></circle>
                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
            </svg>
            <svg class="theme-toggle__icon theme-toggle__icon--moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </span>
        <span class="theme-toggle__label">Appearance</span>
    </button>
@else
    <button type="button"
            class="theme-toggle"
            data-theme-toggle
            aria-pressed="false"
            aria-label="Switch to dark theme"
            title="Switch to dark theme">
        <span class="theme-toggle__icons" aria-hidden="true">
            <svg class="theme-toggle__icon theme-toggle__icon--sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="4"></circle>
                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
            </svg>
            <svg class="theme-toggle__icon theme-toggle__icon--moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </span>
    </button>
@endif
```

- [ ] **Step 2: Add the toggle styles to the layout's `<style>` block**

Append to `resources/views/layouts/app.blade.php`:

```css
        /* ===== Theme toggle ===== */
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
        /* Light theme shows the moon (the action is "go dark"); dark shows the sun. */
        .theme-toggle__icon--moon { opacity: 1; transform: scale(1); }
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
```

- [ ] **Step 3: Extend the test**

Add to `tests/Feature/LandingDarkModeTest.php`:

```php
    public function test_theme_toggle_button_is_accessible_and_wired_to_the_controller(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('data-theme-toggle', false);
        $response->assertSee('aria-pressed="false"', false);
        $response->assertSee('Switch to dark theme', false);
        $response->assertSee('theme-toggle__icon--sun', false);
        $response->assertSee('theme-toggle__icon--moon', false);
    }
```

- [ ] **Step 4: Run the test**

Run: `php artisan test --filter=LandingDarkModeTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Verify checkpoint**

Run: `php artisan view:clear && php artisan route:list --name=landing`
Expected: the `landing` route is listed; views recompiled without error.

---

### Task 3: Rebuild the landing page

**Files:**
- Modify: `resources/views/welcome.blade.php` (full rewrite)
- Test: `tests/Feature/LandingPageSectionsTest.php`

**Interfaces:**
- Consumes: the token system and `.theme-dark` overrides from Task 1; the `$products`, `$heroBanners`, `$announcements` variables supplied by the `landing` route closure.
- Produces: the redesigned landing markup. Preserves the DOM contracts listed in Global Constraints.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/LandingPageSectionsTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\HeroBanner;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageSectionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Custom Sublimation T-Shirt',
            'slug' => 'custom-sublimation-t-shirt',
            'category' => 'Apparel',
            'description' => 'Vibrant CMYK sublimation on polyester fabric.',
            'price' => 250.00,
            'base_price' => 250.00,
            'is_active' => true,
        ], $overrides));
    }

    public function test_landing_renders_the_redesigned_section_headings(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('Services Offered', false);
        $response->assertSee('How it works', false);
        $response->assertSee('Why Grafika South', false);
        $response->assertSee('Print Products', false);
        $response->assertSee('Live Pricing', false);
        $response->assertSee('Order Tracking', false);
        $response->assertSee('Ready to put your design on press?', false);
    }

    public function test_landing_preserves_the_existing_search_contract(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('navbar-search-form', false);
        $response->assertSee('Search products', false);
        $response->assertSee('floatingSearchWidget', false);
        $response->assertSee('bubbleToggleBtn', false);
    }

    public function test_service_cards_link_to_their_category_routes(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee(route('products.index', ['category' => 'Apparel']), false);
        $response->assertSee(route('products.index', ['category' => 'Identification']), false);
        $response->assertSee(route('products.index', ['category' => 'Marketing']), false);
        $response->assertSee(route('products.index', ['category' => 'Services']), false);
    }

    public function test_product_cards_link_to_the_product_detail_page(): void
    {
        $product = $this->makeProduct();

        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee(route('products.show', $product->slug), false);
        $response->assertSee('Custom Sublimation T-Shirt', false);
        $response->assertSee('₱250.00', false);
    }

    public function test_hero_carousel_renders_when_active_banners_exist(): void
    {
        HeroBanner::create([
            'image_path' => 'hero/test.png',
            'title' => 'Summer Promo',
            'link_url' => 'https://example.com',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('heroCarousel', false);
        $response->assertSee('heroCarouselTrack', false);
        $response->assertSee('Summer Promo', false);
        $response->assertSee('heroPrevBtn', false);
        $response->assertSee('heroDots', false);
    }

    public function test_landing_uses_no_hardcoded_hex_colours_in_inline_styles(): void
    {
        $response = $this->get(route('landing'));
        $html = $response->getContent();

        // Inline style attributes on the landing view must not carry raw hex values.
        preg_match_all('/style="([^"]*)"/', $html, $matches);

        foreach ($matches[1] as $inlineStyle) {
            $this->assertDoesNotMatchRegularExpression(
                '/#[0-9a-fA-F]{3,8}\b/',
                $inlineStyle,
                'Landing page contains a hardcoded hex colour in an inline style: ' . $inlineStyle
            );
        }
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=LandingPageSectionsTest`
Expected: FAIL — "How it works", "Why Grafika South", "Live Pricing" etc. are absent.

- [ ] **Step 3: Rewrite `resources/views/welcome.blade.php`**

Write the file with the following structure. The `@section('content')` body is given in full; the `@push('styles')` block holds the landing-only CSS (Task 3 Step 4).

```blade
@extends('layouts.app')

@section('title', 'Grafika South - CMYK Print Shop & Ordering Platform')

@push('styles')
{{-- Landing page styles: token-driven, scoped with .landing-* and .hero-* classes. --}}
@endpush

@section('content')
{{-- ==========================================================
     LANDING PAGE — "Clean Studio"
     Sections: search bubble · announcements · hero · trust strip ·
     services · how it works · why us · products · closing CTA
     ========================================================== }}

{{-- ============ FLOATING SEARCH BUBBLE (kept) ============ --}}
<div class="floating-search-bubble-wrap" id="floatingSearchWidget">
    <div class="bubble-search-tooltip" id="bubbleSearchTooltip">
        <span>Search products</span>
        <div class="tooltip-arrow"></div>
    </div>

    <form action="{{ route('products.index') }}" method="GET" class="floating-bubble-search navbar-search-form" id="floatingBubbleForm">
        <button type="button" class="bubble-toggle-btn" id="bubbleToggleBtn" title="Search products" aria-label="Open product search">
            <i class="fa-solid fa-magnifying-glass bubble-icon-search"></i>
        </button>

        <div class="bubble-input-wrap">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products, services, prints..." class="bubble-search-input" id="bubbleSearchInput" autocomplete="off" aria-label="Search products, services, prints">
            <button type="submit" class="bubble-submit-btn" title="Search" aria-label="Submit search">
                <i class="fa-solid fa-arrow-right"></i>
            </button>
            <button type="button" class="bubble-close-btn" id="bubbleCloseBtn" title="Close search" aria-label="Close search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </form>
</div>

{{-- ============ ANNOUNCEMENTS (kept) ============ --}}
@if(isset($announcements) && $announcements->count() > 0)
    <div class="landing-announcement">
        <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
        <div class="landing-announcement__text">
            @foreach($announcements as $ann)
                <span>{{ $ann->message }}</span>
            @endforeach
        </div>
    </div>
@endif

{{-- ============ HERO (kept: DB-driven carousel + fallback) ============ --}}
@php
    $hasActiveBanners = isset($heroBanners) && $heroBanners->count() > 0;
@endphp

@if($hasActiveBanners)
    <section class="hero-carousel" id="heroCarousel" aria-roledescription="carousel" aria-label="Grafika South Promotional Banner Slider">
        @if(Auth::check() && Auth::user()->isSuperAdmin())
            <a href="{{ route('super-admin.hero.edit') }}" class="btn btn-yellow super-admin-edit-btn" title="Super Admin Access Only - Manage Slider Banners">
                <i class="fa-solid fa-pen-to-square"></i> Manage Hero Slider
            </a>
        @endif

        <div class="hero-carousel-track" id="heroCarouselTrack">
            @foreach($heroBanners as $idx => $banner)
                @php
                    $hasTitle = !empty(trim($banner->title ?? ''));
                    $hasLink = !empty(trim($banner->link_url ?? ''));
                @endphp
                <div class="hero-slide {{ $idx === 0 ? 'active' : '' }}"
                     data-index="{{ $idx }}"
                     aria-roledescription="slide"
                     aria-label="Banner {{ $idx + 1 }} of {{ $heroBanners->count() }}{{ $hasTitle ? ' - ' . $banner->title : '' }}">

                    @if($hasLink && !$hasTitle)
                        <a href="{{ $banner->link_url }}" class="hero-slide__link" aria-label="Go to banner promotion">
                            <img src="{{ asset('storage/' . $banner->image_path) }}"
                                 alt="{{ $banner->title ?: 'Grafika South Promotional Banner ' . ($idx + 1) }}"
                                 loading="{{ $idx === 0 ? 'eager' : 'lazy' }}">
                        </a>
                    @else
                        <img src="{{ asset('storage/' . $banner->image_path) }}"
                             alt="{{ $banner->title ?: 'Grafika South Promotional Banner ' . ($idx + 1) }}"
                             loading="{{ $idx === 0 ? 'eager' : 'lazy' }}">

                        @if($hasTitle || $hasLink)
                            <div class="hero-slide-overlay" aria-hidden="true"></div>
                            <div class="hero-slide-content">
                                @if($hasTitle)
                                    <h2 class="hero-slide-title">{{ $banner->title }}</h2>
                                @endif
                                @if($hasLink)
                                    <a href="{{ $banner->link_url }}" class="hero-slide-link">
                                        <span>{{ $hasTitle ? 'Learn More' : 'View Offer' }}</span>
                                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>

        @if($heroBanners->count() > 1)
            <button class="carousel-arrow prev" id="heroPrevBtn" type="button" aria-label="Previous promotional banner">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </button>
            <button class="carousel-arrow next" id="heroNextBtn" type="button" aria-label="Next promotional banner">
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>

            <div class="carousel-dots" id="heroDots" role="tablist">
                @foreach($heroBanners as $idx => $banner)
                    <button class="carousel-dot {{ $idx === 0 ? 'active' : '' }}"
                            type="button"
                            data-slide="{{ $idx }}"
                            role="tab"
                            aria-label="Go to banner {{ $idx + 1 }}"
                            aria-selected="{{ $idx === 0 ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
        @endif
    </section>
@else
    {{-- ============ HERO FALLBACK (no banners uploaded) ============ --}}
    <section class="hero-fallback">
        @if(Auth::check() && Auth::user()->isSuperAdmin())
            <a href="{{ route('super-admin.hero.edit') }}" class="btn btn-yellow super-admin-edit-btn" title="Super Admin Access Only - Manage Slider Banners">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload Banners
            </a>
        @endif

        <div class="hero-fallback__inner">
            <span class="hero-eyebrow">
                <span class="cmyk-dots" aria-hidden="true">
                    <span class="cmyk-dot dot-c"></span>
                    <span class="cmyk-dot dot-m"></span>
                    <span class="cmyk-dot dot-y"></span>
                    <span class="cmyk-dot dot-k"></span>
                </span>
                4-Process CMYK &middot; San Pedro, Laguna
            </span>

            <h1 class="hero-fallback__title">
                Custom prints, <span class="text-accent">ordered online.</span>
            </h1>

            <p class="hero-fallback__sub">
                T-shirts, PVC ID cards, business cards and lamination &mdash; priced live as you
                configure, reviewed by our print team, and tracked from press to pickup.
            </p>

            <div class="hero-cta-row">
                <a href="{{ route('products.index') }}" class="btn btn-primary hero-cta-primary">
                    <i class="fa-solid fa-shop" aria-hidden="true"></i>
                    Browse Catalog &amp; Order
                </a>
                @guest
                    <a href="{{ route('register') }}" class="btn hero-cta-ghost">
                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                        Create Customer Account
                    </a>
                @endguest
            </div>
        </div>
    </section>
@endif

{{-- ============ TRUST STRIP (new) ============ --}}
<section class="landing-section landing-section--tight" aria-label="Why shop with Grafika South">
    <div class="landing-container">
        <ul class="trust-strip">
            <li class="trust-strip__item">
                <span class="trust-strip__icon trust-strip__icon--cyan" aria-hidden="true"><i class="fa-solid fa-layer-group"></i></span>
                <div>
                    <span class="trust-strip__label">4-Process CMYK</span>
                    <span class="trust-strip__sub">Commercial colour, calibrated</span>
                </div>
            </li>
            <li class="trust-strip__item">
                <span class="trust-strip__icon trust-strip__icon--magenta" aria-hidden="true"><i class="fa-solid fa-calculator"></i></span>
                <div>
                    <span class="trust-strip__label">Live Pricing</span>
                    <span class="trust-strip__sub">Updates as you configure</span>
                </div>
            </li>
            <li class="trust-strip__item">
                <span class="trust-strip__icon trust-strip__icon--yellow" aria-hidden="true"><i class="fa-solid fa-boxes-stacked"></i></span>
                <div>
                    <span class="trust-strip__label">Auto Stock Sync</span>
                    <span class="trust-strip__sub">Inventory deducts on order</span>
                </div>
            </li>
            <li class="trust-strip__item">
                <span class="trust-strip__icon trust-strip__icon--key" aria-hidden="true"><i class="fa-solid fa-route"></i></span>
                <div>
                    <span class="trust-strip__label">Order Tracking</span>
                    <span class="trust-strip__sub">Follow your job to pickup</span>
                </div>
            </li>
        </ul>
    </div>
</section>

{{-- ============ SERVICES OFFERED (kept, restyled) ============ --}}
<section class="landing-section" aria-labelledby="services-heading">
    <div class="landing-container">
        <header class="section-head">
            <h2 class="section-title" id="services-heading">Services Offered</h2>
            <p class="section-sub">Professional printing services engineered with CMYK precision</p>
        </header>

        <div class="service-grid">
            <a class="service-card service-card--cyan" href="{{ route('products.index', ['category' => 'Apparel']) }}">
                <span class="service-card__icon" aria-hidden="true"><i class="fa-solid fa-shirt"></i></span>
                <h3 class="service-card__title">T-Shirt Printing</h3>
                <p class="service-card__desc">High quality sublimation &amp; heat transfer with size and color options.</p>
                <span class="service-card__price">From ₱250.00 <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </a>

            <a class="service-card service-card--magenta" href="{{ route('products.index', ['category' => 'Identification']) }}">
                <span class="service-card__icon" aria-hidden="true"><i class="fa-solid fa-id-card"></i></span>
                <h3 class="service-card__title">ID Printing</h3>
                <p class="service-card__desc">Durable 30mil PVC ID cards with single/double-sided print options.</p>
                <span class="service-card__price">From ₱50.00 <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </a>

            <a class="service-card service-card--yellow" href="{{ route('products.index', ['category' => 'Marketing']) }}">
                <span class="service-card__icon" aria-hidden="true"><i class="fa-solid fa-address-card"></i></span>
                <h3 class="service-card__title">Business Card Printing</h3>
                <p class="service-card__desc">Heavy 300gsm glossy or matte cardstock with premium corner cuts (Pack of 100).</p>
                <span class="service-card__price">From ₱350.00 / pack <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </a>

            <a class="service-card service-card--key" href="{{ route('products.index', ['category' => 'Services']) }}">
                <span class="service-card__icon" aria-hidden="true"><i class="fa-solid fa-file-contract"></i></span>
                <h3 class="service-card__title">Lamination Services</h3>
                <p class="service-card__desc">Waterproof sealed document thermal lamination (A4 &amp; A3 sizes, heavy pouches).</p>
                <span class="service-card__price">From ₱25.00 / sheet <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </a>
        </div>
    </div>
</section>

{{-- ============ HOW IT WORKS (new) ============ --}}
<section class="landing-section landing-section--band" aria-labelledby="how-heading">
    <div class="landing-container">
        <header class="section-head">
            <h2 class="section-title" id="how-heading">How it works</h2>
            <p class="section-sub">From configuration to pickup in three steps</p>
        </header>

        <ol class="steps">
            <li class="step">
                <span class="step__num" aria-hidden="true">01</span>
                <span class="step__icon" aria-hidden="true"><i class="fa-solid fa-sliders"></i></span>
                <h3 class="step__title">Configure</h3>
                <p class="step__desc">Choose size, material and finish. Options price themselves as you go.</p>
            </li>
            <li class="step">
                <span class="step__num" aria-hidden="true">02</span>
                <span class="step__icon" aria-hidden="true"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                <h3 class="step__title">Upload artwork</h3>
                <p class="step__desc">Send your design file with your order &mdash; our print team reviews it before press.</p>
            </li>
            <li class="step">
                <span class="step__num" aria-hidden="true">03</span>
                <span class="step__icon" aria-hidden="true"><i class="fa-solid fa-receipt"></i></span>
                <h3 class="step__title">Order &amp; track</h3>
                <p class="step__desc">Pay by QR and follow your job from confirmation to pickup.</p>
            </li>
        </ol>
    </div>
</section>

{{-- ============ WHY US (new) ============ --}}
<section class="landing-section" aria-labelledby="why-heading">
    <div class="landing-container">
        <header class="section-head">
            <h2 class="section-title" id="why-heading">Why Grafika South</h2>
            <p class="section-sub">A print shop with the tooling of an online store</p>
        </header>

        <div class="value-grid">
            <article class="value-card">
                <span class="value-card__icon" aria-hidden="true"><i class="fa-solid fa-gauge-high"></i></span>
                <h3 class="value-card__title">High-definition CMYK precision</h3>
                <p class="value-card__desc">Commercial 4-process colour, calibrated for apparel, PVC and cardstock.</p>
            </article>
            <article class="value-card">
                <span class="value-card__icon" aria-hidden="true"><i class="fa-solid fa-diagram-project"></i></span>
                <h3 class="value-card__title">Automated BOM engine</h3>
                <p class="value-card__desc">Every order is costed from real material usage, so pricing stays accurate.</p>
            </article>
            <article class="value-card">
                <span class="value-card__icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass-chart"></i></span>
                <h3 class="value-card__title">Real-time artwork review</h3>
                <p class="value-card__desc">Your file is checked before it reaches the press, not after.</p>
            </article>
            <article class="value-card">
                <span class="value-card__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                <h3 class="value-card__title">Produced locally</h3>
                <p class="value-card__desc">Printed in San Pedro, Laguna &mdash; and collectable in person.</p>
            </article>
        </div>
    </div>
</section>

{{-- ============ PRODUCTS CATALOG (kept) ============ --}}
<section class="landing-section" aria-labelledby="products-heading">
    <div class="landing-container">
        <header class="section-head section-head--split">
            <div>
                <h2 class="section-title" id="products-heading">
                    <i class="fa-solid fa-print section-title__icon" aria-hidden="true"></i>
                    Print Products
                </h2>
                <p class="section-sub">Select dynamic options, view live pricing, and upload custom design artwork</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-outline section-head__action">
                View Full Catalog <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </header>

        <div class="product-grid">
            @foreach($products as $product)
                @php
                    $targetUrl = route('products.show', $product->slug ?: $product->id);
                @endphp
                <a class="product-storefront-card" href="{{ $targetUrl }}">
                    <span class="product-storefront-thumb">
                        @if($product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="product-storefront-img" loading="lazy">
                        @else
                            <span class="product-storefront-placeholder">
                                <span class="product-storefront-placeholder__icon" aria-hidden="true"><i class="fa-solid fa-print"></i></span>
                                <span class="product-storefront-placeholder__text">Grafika South Print</span>
                            </span>
                        @endif

                        <span class="product-storefront-badges">
                            <span class="product-storefront-badge product-storefront-badge--category">{{ $product->category }}</span>
                            @if($product->is_featured ?? false)
                                <span class="product-storefront-badge product-storefront-badge--featured">★ Featured</span>
                            @endif
                        </span>

                        @if(!$product->has_sufficient_stock)
                            <span class="product-storefront-badge product-storefront-badge--oos">
                                <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> Out of Stock
                            </span>
                        @endif
                    </span>

                    <span class="product-storefront-body">
                        <span class="product-storefront-title">{{ $product->name }}</span>
                        <span class="product-storefront-desc">{{ $product->description ?: 'High quality custom print product with customizable options and fast turnaround.' }}</span>

                        <span class="product-storefront-footer">
                            <span class="product-storefront-price">
                                <span class="product-storefront-price-label">Starting Price</span>
                                <span class="product-storefront-price-val">₱{{ number_format($product->price ?: $product->base_price, 2) }}</span>
                            </span>
                            @if($product->has_sufficient_stock)
                                <span class="product-storefront-btn btn btn-primary">Select Options</span>
                            @else
                                <span class="product-storefront-btn product-storefront-btn--disabled">Out of Stock</span>
                            @endif
                        </span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ CLOSING CTA (new) ============ --}}
<section class="landing-section landing-section--cta" aria-labelledby="cta-heading">
    <div class="landing-container">
        <div class="cta-band">
            <h2 class="cta-band__title" id="cta-heading">Ready to put your design on press?</h2>
            <p class="cta-band__sub">Configure your print, upload your artwork, and we will take it from there.</p>
            <div class="cta-band__actions">
                <a href="{{ route('products.index') }}" class="btn btn-primary hero-cta-primary">
                    <i class="fa-solid fa-shop" aria-hidden="true"></i> Browse the catalog
                </a>
                <a href="{{ route('orders.track') }}" class="btn hero-cta-ghost hero-cta-ghost--onlight">
                    <i class="fa-solid fa-magnifying-glass-location" aria-hidden="true"></i> Track your order
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
{{-- Carousel + floating search controllers (kept verbatim in behaviour). --}}
@endpush
```

- [ ] **Step 4: Write the landing CSS into the `@push('styles')` block**

Replace the `@push('styles') ... @endpush` block with:

```css
    /* ==========================================================
       LANDING PAGE — "Clean Studio"
       Every colour resolves through a semantic token so dark mode
       needs no per-component overrides.
       ========================================================== */
    .landing-container {
        max-width: 1200px;
        width: 100%;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    @media (max-width: 639.98px) {
        .landing-container { padding: 0 1.25rem; }
    }

    .landing-section { padding: 6rem 0; }
    .landing-section--tight { padding: 2.5rem 0 0; }
    .landing-section--band {
        background: var(--bg-surface-2);
        border-top: 1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
    }
    @media (max-width: 767.98px) {
        .landing-section { padding: 3.5rem 0; }
        .landing-section--tight { padding: 1.75rem 0 0; }
    }

    /* ----- Section headings ----- */
    .section-head { margin-bottom: 3rem; text-align: center; max-width: 42rem; margin-left: auto; margin-right: auto; }
    .section-head--split {
        display: flex; align-items: flex-end; justify-content: space-between;
        gap: 1.5rem; text-align: left; max-width: none; flex-wrap: wrap;
    }
    .section-title {
        font-family: 'Outfit', sans-serif;
        font-size: clamp(1.75rem, 3vw, 2.5rem);
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.02em;
        color: var(--text-main);
        margin: 0 0 0.5rem;
        display: flex; align-items: center; gap: 0.6rem; justify-content: inherit;
    }
    .section-title__icon { color: var(--accent-color); font-size: 0.85em; }
    .section-sub { color: var(--text-muted); font-size: 1rem; line-height: 1.65; margin: 0; }
    .section-head__action { flex-shrink: 0; }

    /* ----- Announcements ----- */
    .landing-announcement {
        display: flex; align-items: center; gap: 0.75rem;
        background: var(--accent-soft);
        border: 1px solid var(--accent-color);
        color: var(--text-accent);
        border-radius: var(--radius-md);
        padding: 0.8rem 1.1rem;
        margin-bottom: 2rem;
    }
    .landing-announcement > i { color: var(--accent-color); font-size: 1rem; flex-shrink: 0; }
    .landing-announcement__text { display: flex; flex-wrap: wrap; gap: 0.25rem 1.5rem; font-size: 0.9rem; font-weight: 600; }

    /* ----- HERO — carousel ----- */
    .hero-carousel {
        position: relative;
        width: 100%;
        border-radius: var(--radius-xl);
        margin-bottom: 2.5rem;
        overflow: hidden;
        background: var(--p-ink-950);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
    }
    .hero-carousel-track { position: relative; width: 100%; height: 560px; overflow: hidden; }
    .hero-slide {
        position: absolute; inset: 0; width: 100%; height: 100%;
        opacity: 0; transform: scale(1.05);
        transition: opacity 0.9s ease-in-out, transform 9s ease-out;
    }
    .hero-slide.active { opacity: 1; transform: scale(1); z-index: 2; }
    .hero-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .hero-slide__link { display: block; width: 100%; height: 100%; position: relative; }
    .hero-slide-overlay {
        position: absolute; inset: 0; pointer-events: none;
        background:
            linear-gradient(100deg, rgba(8, 13, 24, 0.90) 0%, rgba(8, 13, 24, 0.62) 38%, rgba(8, 13, 24, 0.12) 72%, rgba(8, 13, 24, 0) 100%),
            linear-gradient(0deg, rgba(8, 13, 24, 0.55) 0%, rgba(8, 13, 24, 0) 45%);
    }
    .hero-slide-content {
        position: absolute; z-index: 3;
        left: clamp(1.5rem, 4vw, 3.5rem);
        right: clamp(1.5rem, 4vw, 3.5rem);
        bottom: clamp(2rem, 5vw, 3.75rem);
        max-width: 40rem;
        color: var(--p-white);
    }
    .hero-slide-title {
        font-family: 'Outfit', sans-serif;
        font-size: clamp(2rem, 4vw, 3.25rem);
        font-weight: 800;
        line-height: 1.08;
        letter-spacing: -0.025em;
        margin-bottom: 0.9rem;
        color: var(--p-white);
        text-shadow: 0 2px 20px rgba(0, 0, 0, 0.35);
    }
    .hero-slide-link {
        display: inline-flex; align-items: center; gap: 0.5rem;
        margin-top: 1rem; padding: 0.7rem 1.6rem;
        background: var(--cmyk-cyan); color: var(--p-white);
        font-weight: 700; font-size: 0.92rem;
        border-radius: var(--radius-sm);
        text-decoration: none;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 4px 16px -4px rgba(0, 153, 255, 0.5);
        cursor: pointer;
    }
    .hero-slide-link:hover { background: var(--cmyk-cyan-hover); box-shadow: 0 8px 22px -6px rgba(0, 153, 255, 0.6); }

    .carousel-arrow {
        position: absolute; top: 50%; transform: translateY(-50%);
        z-index: 10; width: 46px; height: 46px; border-radius: 50%;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: var(--p-white);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 1rem;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }
    .carousel-arrow:hover { background: var(--cmyk-cyan); border-color: var(--cmyk-cyan); }
    .carousel-arrow.prev { left: 1.25rem; }
    .carousel-arrow.next { right: 1.25rem; }

    .carousel-dots {
        position: absolute; bottom: 1.25rem; left: 50%;
        transform: translateX(-50%); z-index: 10;
        display: flex; align-items: center; gap: 0.45rem;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(10px);
        padding: 0.35rem 0.75rem; border-radius: var(--radius-pill);
        border: 1px solid rgba(255, 255, 255, 0.16);
    }
    .carousel-dot {
        width: 8px; height: 8px; border-radius: var(--radius-pill);
        background: rgba(255, 255, 255, 0.45);
        cursor: pointer; transition: background-color 0.3s ease, width 0.3s ease;
        border: none; padding: 0; outline: none;
    }
    .carousel-dot:hover { background: rgba(255, 255, 255, 0.85); }
    .carousel-dot.active { background: var(--cmyk-cyan); width: 24px; }

    .hero-carousel .super-admin-edit-btn {
        position: absolute; top: 1.25rem; right: 1.25rem; z-index: 15;
        font-size: 0.82rem; padding: 0.5rem 1rem; border-radius: var(--radius-sm);
    }

    /* ----- HERO — fallback ----- */
    .hero-fallback {
        position: relative; overflow: hidden;
        background: var(--p-ink-950);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
        padding: 7rem 2.5rem 6.5rem;
        margin-bottom: 2.5rem;
        text-align: center;
    }
    .hero-fallback::before {
        content: ""; position: absolute; inset: 0;
        background-image:
            radial-gradient(rgba(255, 255, 255, 0.055) 1.5px, transparent 1.6px),
            radial-gradient(rgba(0, 153, 255, 0.05) 1.5px, transparent 1.6px);
        background-size: 22px 22px, 22px 22px;
        background-position: 0 0, 11px 11px;
    }
    .hero-fallback::after {
        content: ""; position: absolute; inset: 0;
        background: radial-gradient(70% 60% at 50% 0%, rgba(0, 153, 255, 0.16) 0%, transparent 70%);
    }
    .hero-fallback > * { position: relative; z-index: 2; }
    .hero-fallback__inner { max-width: 48rem; margin: 0 auto; }
    .hero-eyebrow {
        display: inline-flex; align-items: center; gap: 0.65rem;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(10px);
        color: var(--p-ink-300);
        padding: 0.45rem 1.05rem;
        border-radius: var(--radius-pill);
        font-size: 0.72rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.14em;
        margin-bottom: 1.75rem;
    }
    .hero-fallback__title {
        font-family: 'Outfit', sans-serif;
        font-size: clamp(2.25rem, 5vw, 3.75rem);
        font-weight: 800; line-height: 1.06; letter-spacing: -0.03em;
        color: var(--p-white); margin: 0 0 1.25rem;
    }
    .hero-fallback__sub {
        font-size: clamp(1rem, 1.4vw, 1.125rem);
        color: var(--p-ink-300); line-height: 1.7;
        max-width: 38rem; margin: 0 auto 2.25rem;
    }
    .hero-cta-row { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
    .hero-cta-primary {
        padding: 0.9rem 2rem; font-size: 1rem; font-weight: 700;
        border-radius: var(--radius-md);
        background: var(--cmyk-cyan); border: none; color: var(--p-white);
        display: inline-flex; align-items: center; gap: 0.5rem;
        cursor: pointer;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 6px 20px -6px rgba(0, 153, 255, 0.55);
    }
    .hero-cta-primary:hover { background: var(--cmyk-cyan-hover); box-shadow: 0 10px 26px -8px rgba(0, 153, 255, 0.65); }
    .hero-cta-ghost {
        padding: 0.9rem 2rem; font-size: 1rem; font-weight: 700;
        border-radius: var(--radius-md);
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.26);
        color: var(--p-white);
        display: inline-flex; align-items: center; gap: 0.5rem;
        cursor: pointer;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }
    .hero-cta-ghost:hover { background: rgba(255, 255, 255, 0.12); border-color: rgba(255, 255, 255, 0.45); }
    .hero-cta-ghost--onlight {
        background: var(--bg-surface); border-color: var(--border-strong); color: var(--text-main);
    }
    .hero-cta-ghost--onlight:hover { background: var(--bg-subtle); border-color: var(--accent-color); color: var(--text-accent); }
    @media (max-width: 640px) {
        .hero-fallback { padding: 4rem 1.25rem 3.5rem; }
        .hero-cta-row { flex-direction: column; }
        .hero-cta-row a { width: 100%; justify-content: center; }
    }

    /* ----- TRUST STRIP ----- */
    .trust-strip {
        list-style: none; margin: 0; padding: 0;
        display: grid; grid-template-columns: repeat(4, 1fr);
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }
    .trust-strip__item {
        display: flex; align-items: center; gap: 0.9rem;
        padding: 1.35rem 1.5rem;
        border-right: 1px solid var(--border-color);
    }
    .trust-strip__item:last-child { border-right: none; }
    .trust-strip__icon {
        width: 44px; height: 44px; flex-shrink: 0;
        border-radius: var(--radius-md);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.05rem;
    }
    .trust-strip__icon--cyan { background: var(--accent-soft); color: var(--cmyk-cyan); }
    .trust-strip__icon--magenta { background: var(--p-magenta-050); color: var(--cmyk-magenta); }
    .trust-strip__icon--yellow { background: var(--p-yellow-050); color: var(--cmyk-yellow-dark); }
    .trust-strip__icon--key { background: var(--bg-subtle); color: var(--cmyk-key); }
    .trust-strip__label { display: block; font-weight: 700; font-size: 0.95rem; color: var(--text-main); line-height: 1.3; }
    .trust-strip__sub { display: block; font-size: 0.82rem; color: var(--text-muted); margin-top: 0.15rem; line-height: 1.4; }
    @media (max-width: 1023.98px) {
        .trust-strip { grid-template-columns: repeat(2, 1fr); }
        .trust-strip__item:nth-child(2n) { border-right: none; }
        .trust-strip__item:nth-child(-n+2) { border-bottom: 1px solid var(--border-color); }
    }
    @media (max-width: 575.98px) {
        .trust-strip { grid-template-columns: 1fr; }
        .trust-strip__item { border-right: none; border-bottom: 1px solid var(--border-color); }
        .trust-strip__item:last-child { border-bottom: none; }
    }

    /* ----- SERVICES ----- */
    .service-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
    .service-card {
        display: flex; flex-direction: column; text-align: left;
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-top: 3px solid var(--border-strong);
        border-radius: var(--radius-lg);
        padding: 2rem 1.5rem;
        text-decoration: none;
        cursor: pointer;
        box-shadow: var(--card-shadow);
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .service-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
    .service-card--cyan:hover { border-top-color: var(--cmyk-cyan); border-color: var(--accent-color); }
    .service-card--magenta:hover { border-top-color: var(--cmyk-magenta); border-color: var(--cmyk-magenta); }
    .service-card--yellow:hover { border-top-color: var(--cmyk-yellow); border-color: var(--cmyk-yellow); }
    .service-card--key:hover { border-top-color: var(--cmyk-key); border-color: var(--border-strong); }
    .service-card__icon {
        width: 52px; height: 52px; border-radius: var(--radius-md);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.35rem; margin-bottom: 1.25rem;
        transition: background-color 0.25s ease, color 0.25s ease;
    }
    .service-card--cyan .service-card__icon { background: var(--accent-soft); color: var(--cmyk-cyan); }
    .service-card--magenta .service-card__icon { background: var(--p-magenta-050); color: var(--cmyk-magenta); }
    .service-card--yellow .service-card__icon { background: var(--p-yellow-050); color: var(--cmyk-yellow-dark); }
    .service-card--key .service-card__icon { background: var(--bg-subtle); color: var(--cmyk-key); }
    .service-card__title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.125rem; font-weight: 700; letter-spacing: -0.01em;
        color: var(--text-main); margin: 0 0 0.5rem;
    }
    .service-card__desc { font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin: 0 0 1.5rem; flex: 1; }
    .service-card__price {
        display: inline-flex; align-items: center; gap: 0.35rem;
        font-weight: 700; font-size: 0.9rem; color: var(--text-accent);
    }
    .service-card:hover .service-card__price i { transform: translateX(3px); }
    .service-card__price i { transition: transform 0.2s ease; }
    @media (max-width: 1023.98px) { .service-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575.98px) { .service-grid { grid-template-columns: 1fr; gap: 1rem; } }

    /* ----- HOW IT WORKS ----- */
    .steps { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 2.5rem; }
    .step { position: relative; padding-left: 4.5rem; }
    .step__num {
        position: absolute; left: 0; top: 0;
        font-family: 'Outfit', sans-serif;
        font-size: 0.8rem; font-weight: 800; letter-spacing: 0.1em;
        color: var(--text-accent);
    }
    .step__icon {
        width: 44px; height: 44px; border-radius: var(--radius-md);
        background: var(--accent-soft); color: var(--cmyk-cyan);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.05rem; margin-bottom: 1rem;
    }
    .step__title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.125rem; font-weight: 700; letter-spacing: -0.01em;
        color: var(--text-main); margin: 0 0 0.5rem;
    }
    .step__desc { font-size: 0.95rem; color: var(--text-muted); line-height: 1.65; margin: 0; }
    @media (max-width: 767.98px) { .steps { grid-template-columns: 1fr; gap: 2rem; } }

    /* ----- WHY US ----- */
    .value-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
    .value-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        padding: 2.25rem 2rem;
        box-shadow: var(--card-shadow);
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .value-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); border-color: var(--border-strong); }
    .value-card__icon {
        width: 56px; height: 56px; border-radius: var(--radius-lg);
        background: var(--accent-soft); color: var(--cmyk-cyan);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.4rem; margin-bottom: 1.25rem;
    }
    .value-card__title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem; font-weight: 700; letter-spacing: -0.01em;
        color: var(--text-main); margin: 0 0 0.6rem;
    }
    .value-card__desc { font-size: 0.95rem; color: var(--text-muted); line-height: 1.65; margin: 0; }
    @media (max-width: 767.98px) { .value-grid { grid-template-columns: 1fr; gap: 1rem; } }

    /* ----- PRODUCTS ----- */
    .product-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    .product-storefront-card {
        display: flex; flex-direction: column; justify-content: space-between;
        overflow: hidden; text-decoration: none;
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--card-shadow);
        cursor: pointer;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .product-storefront-card:hover {
        transform: translateY(-5px);
        border-color: var(--accent-color);
        box-shadow: var(--shadow-lg);
    }
    .product-storefront-thumb {
        position: relative; display: flex; align-items: center; justify-content: center;
        width: 100%; height: 200px; overflow: hidden;
        background: var(--bg-surface-2);
        border-bottom: 1px solid var(--border-color);
    }
    .product-storefront-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease; }
    .product-storefront-card:hover .product-storefront-img { transform: scale(1.05); }
    .product-storefront-placeholder { display: flex; flex-direction: column; align-items: center; gap: 0.6rem; }
    .product-storefront-placeholder__icon {
        width: 52px; height: 52px; border-radius: var(--radius-md);
        background: var(--accent-soft); border: 1px solid var(--border-color);
        color: var(--cmyk-cyan);
        display: inline-flex; align-items: center; justify-content: center; font-size: 1.35rem;
    }
    .product-storefront-placeholder__text { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); }
    .product-storefront-badges {
        position: absolute; top: 0.75rem; left: 0.75rem; right: 0.75rem;
        display: flex; justify-content: space-between; align-items: flex-start;
        pointer-events: none; z-index: 5;
    }
    .product-storefront-badge {
        font-size: 0.7rem; font-weight: 700; padding: 0.25rem 0.6rem;
        border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05px;
        white-space: nowrap;
    }
    .product-storefront-badge--category {
        background: var(--bg-surface); color: var(--text-accent);
        border: 1px solid var(--border-color);
        max-width: 65%; overflow: hidden; text-overflow: ellipsis;
    }
    .product-storefront-badge--featured { background: var(--warning-color); color: var(--p-white); }
    .product-storefront-badge--oos {
        position: absolute; bottom: 0.75rem; left: 0.75rem; z-index: 5;
        background: var(--danger-color); color: var(--p-white);
        display: inline-flex; align-items: center; gap: 0.35rem;
    }
    .product-storefront-body {
        display: flex; flex-direction: column; flex: 1; justify-content: space-between;
        padding: 1.35rem;
    }
    .product-storefront-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.1rem; font-weight: 700; letter-spacing: -0.01em;
        color: var(--text-main); margin: 0 0 0.4rem;
        transition: color 0.2s ease;
    }
    .product-storefront-card:hover .product-storefront-title { color: var(--text-accent); }
    .product-storefront-desc {
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        font-size: 0.9rem; color: var(--text-muted); line-height: 1.55; margin: 0 0 1.25rem;
    }
    .product-storefront-footer {
        display: flex; justify-content: between; align-items: center; flex-wrap: wrap; gap: 0.6rem;
        border-top: 1px solid var(--border-color); padding-top: 0.9rem;
    }
    .product-storefront-price-label { display: block; font-size: 0.72rem; color: var(--text-muted); font-weight: 500; margin-bottom: 2px; }
    .product-storefront-price-val { display: block; font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 700; color: var(--text-accent); }
    .product-storefront-btn {
        padding: 0.6rem 1.2rem; font-size: 0.85rem; font-weight: 700;
        border-radius: var(--radius-sm);
        background: var(--cmyk-cyan); color: var(--p-white); border: none;
        display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }
    .product-storefront-card:hover .product-storefront-btn { background: var(--cmyk-cyan-hover); box-shadow: 0 4px 12px rgba(0, 153, 255, 0.35); }
    .product-storefront-btn--disabled {
        background: var(--bg-subtle); color: var(--text-muted);
        border: 1px solid var(--border-color);
    }
    @media (max-width: 1023.98px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 767.98px) {
        .product-grid { gap: 0.85rem; }
        .product-storefront-thumb { height: 150px; }
        .product-storefront-body { padding: 1rem; }
        .product-storefront-title { font-size: 0.95rem; }
        .product-storefront-desc { font-size: 0.8rem; margin-bottom: 0.85rem; }
        .product-storefront-footer { flex-direction: column; align-items: stretch; gap: 0.5rem; }
        .product-storefront-btn { width: 100%; justify-content: center; }
    }

    /* ----- CLOSING CTA ----- */
    .landing-section--cta { padding-top: 0; }
    .cta-band {
        text-align: center;
        background: var(--bg-surface-2);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        padding: 4rem 2rem;
    }
    .cta-band__title {
        font-family: 'Outfit', sans-serif;
        font-size: clamp(1.6rem, 2.6vw, 2.25rem); font-weight: 800;
        letter-spacing: -0.02em; color: var(--text-main); margin: 0 0 0.75rem;
    }
    .cta-band__sub { color: var(--text-muted); font-size: 1rem; line-height: 1.65; margin: 0 0 2rem; }
    .cta-band__actions { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
    @media (max-width: 640px) {
        .cta-band { padding: 3rem 1.25rem; }
        .cta-band__actions { flex-direction: column; }
        .cta-band__actions a { width: 100%; justify-content: center; }
    }

    /* ----- FLOATING SEARCH BUBBLE (kept behaviour, restyled) ----- */
    .floating-search-bubble-wrap {
        position: fixed; right: 28px; top: 85px; z-index: 990;
        display: flex; align-items: center; justify-content: flex-end;
    }
    .bubble-search-tooltip {
        position: absolute; right: 68px; left: auto;
        background: var(--p-ink-900); color: var(--p-white);
        font-size: 0.78rem; font-weight: 700;
        padding: 0.38rem 0.75rem; border-radius: var(--radius-sm);
        white-space: nowrap; pointer-events: none;
        opacity: 0; transform: translateX(8px);
        transition: opacity 0.2s ease, transform 0.2s ease;
        box-shadow: var(--shadow-md);
    }
    .bubble-search-tooltip .tooltip-arrow {
        position: absolute; right: -4px; left: auto; top: 50%;
        transform: translateY(-50%); width: 0; height: 0;
        border-top: 4px solid transparent; border-bottom: 4px solid transparent;
        border-left: 4px solid var(--p-ink-900); border-right: none;
    }
    .floating-search-bubble-wrap:not(.is-expanded):hover .bubble-search-tooltip { opacity: 1; transform: translateX(0); }
    .floating-bubble-search {
        display: flex; align-items: center;
        width: 56px; height: 56px;
        background: var(--cmyk-cyan);
        border-radius: 56px;
        border: 2.5px solid var(--bg-surface);
        box-shadow: 0 8px 24px rgba(0, 153, 255, 0.4), 0 2px 8px rgba(15, 23, 42, 0.12);
        overflow: hidden; position: relative;
        transition: width 0.38s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }
    .floating-search-bubble-wrap:not(.is-expanded) .floating-bubble-search { animation: chatBubblePulse 3.2s infinite ease-in-out; }
    .floating-search-bubble-wrap:not(.is-expanded) .floating-bubble-search:hover {
        transform: scale(1.06) translateY(-2px);
        box-shadow: 0 12px 28px rgba(0, 153, 255, 0.5), 0 4px 12px rgba(15, 23, 42, 0.15);
    }
    .floating-search-bubble-wrap.is-expanded .floating-bubble-search {
        width: 420px; max-width: calc(100vw - 48px);
        background: var(--bg-surface);
        border-color: var(--accent-color);
        box-shadow: 0 14px 38px rgba(0, 153, 255, 0.22), 0 4px 16px rgba(15, 23, 42, 0.12);
        animation: none;
    }
    @keyframes chatBubblePulse {
        0%, 100% { box-shadow: 0 8px 24px rgba(0, 153, 255, 0.4), 0 2px 8px rgba(15, 23, 42, 0.12); }
        50% { box-shadow: 0 8px 30px rgba(0, 153, 255, 0.65), 0 0 0 6px rgba(0, 153, 255, 0.18); }
    }
    .bubble-toggle-btn {
        width: 51px; height: 51px; border-radius: 50%; border: none;
        background: transparent; color: var(--p-white); font-size: 1.25rem;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; flex-shrink: 0; padding: 0;
        transition: color 0.25s ease, transform 0.25s ease;
    }
    .floating-search-bubble-wrap.is-expanded .bubble-toggle-btn { color: var(--cmyk-cyan); cursor: default; }
    .bubble-input-wrap {
        display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 0;
        opacity: 0; transform: translateX(-10px); pointer-events: none;
        transition: opacity 0.25s ease 0.08s, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) 0.08s;
        padding-right: 0.5rem;
    }
    .floating-search-bubble-wrap.is-expanded .bubble-input-wrap { opacity: 1; transform: translateX(0); pointer-events: auto; }
    .bubble-search-input {
        flex: 1; min-width: 0; border: none; outline: none;
        background: transparent; font-size: 0.95rem;
        color: var(--input-text); font-family: inherit; padding: 0.2rem 0.25rem;
    }
    .bubble-search-input::placeholder { color: var(--input-placeholder); font-size: 0.88rem; }
    .bubble-submit-btn {
        width: 34px; height: 34px; border-radius: 50%; border: none;
        background: var(--cmyk-cyan); color: var(--p-white);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.82rem; cursor: pointer; flex-shrink: 0;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .bubble-submit-btn:hover { background: var(--cmyk-cyan-hover); transform: scale(1.08); }
    .bubble-close-btn {
        width: 28px; height: 28px; border-radius: 50%; border: none;
        background: var(--bg-subtle); color: var(--text-muted);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.78rem; cursor: pointer; flex-shrink: 0;
        transition: background-color 0.2s ease, color 0.2s ease, transform 0.25s ease;
    }
    .bubble-close-btn:hover { background: var(--danger-soft); color: var(--danger-color); transform: rotate(90deg); }
    @media (max-width: 768px) { .floating-search-bubble-wrap { display: none !important; } }

    /* ----- Reduced motion ----- */
    @media (prefers-reduced-motion: reduce) {
        .hero-slide, .hero-slide.active { transition: opacity 0.01ms !important; transform: none !important; }
        .floating-search-bubble-wrap:not(.is-expanded) .floating-bubble-search { animation: none !important; }
        .carousel-dot, .service-card, .product-storefront-card, .value-card { transition: none !important; }
    }
```

- [ ] **Step 5: Restore the carousel and bubble controllers in `@push('scripts')`**

The existing controller IIFEs are carried over unchanged (same ids, same behaviour). Paste the previous file's `@push('scripts')` block into the new file's `@push('scripts')` block, unchanged:

```blade
@push('scripts')
<script>
(function () {
    const carousel = document.getElementById('heroCarousel');
    if (!carousel) return;

    const slides = carousel.querySelectorAll('.hero-slide');
    const total = slides.length;
    if (total <= 1) return;

    const dotsContainer = document.getElementById('heroDots');
    const dots = dotsContainer ? dotsContainer.querySelectorAll('.carousel-dot') : [];
    const prevBtn = document.getElementById('heroPrevBtn');
    const nextBtn = document.getElementById('heroNextBtn');

    let current = 0;
    const INTERVAL_MS = 5000;
    let autoplayTimer = null;
    let isPaused = false;

    function goTo(index) {
        if (index < 0) index = total - 1;
        if (index >= total) index = 0;
        if (index === current) { restartAutoplay(); return; }

        slides[current].classList.remove('active');
        slides[index].classList.add('active');

        if (dots[current]) {
            dots[current].classList.remove('active');
            dots[current].setAttribute('aria-selected', 'false');
        }
        if (dots[index]) {
            dots[index].classList.add('active');
            dots[index].setAttribute('aria-selected', 'true');
        }

        current = index;
        restartAutoplay();
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startAutoplay() {
        stopAutoplay();
        autoplayTimer = setInterval(() => {
            if (!isPaused) next();
        }, INTERVAL_MS);
    }

    function stopAutoplay() {
        if (autoplayTimer) { clearInterval(autoplayTimer); autoplayTimer = null; }
    }

    function restartAutoplay() {
        startAutoplay();
    }

    if (prevBtn) prevBtn.addEventListener('click', () => { prev(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { next(); });
    dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));

    carousel.addEventListener('mouseenter', () => { isPaused = true; });
    carousel.addEventListener('mouseleave', () => { isPaused = false; restartAutoplay(); });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) stopAutoplay();
        else startAutoplay();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') prev();
        if (e.key === 'ArrowRight') next();
    });

    let touchStartX = 0, touchEndX = 0;
    carousel.addEventListener('touchstart', (e) => { touchStartX = e.changedTouches[0].screenX; isPaused = true; }, { passive: true });
    carousel.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        const diff = touchEndX - touchStartX;
        if (Math.abs(diff) > 50) { diff < 0 ? next() : prev(); }
        isPaused = false;
        restartAutoplay();
    }, { passive: true });

    startAutoplay();
})();

// Floating Chat Head / Bubble Search Bar
(function() {
    const widget = document.getElementById('floatingSearchWidget');
    const toggleBtn = document.getElementById('bubbleToggleBtn');
    const input = document.getElementById('bubbleSearchInput');
    const closeBtn = document.getElementById('bubbleCloseBtn');

    if (!widget || !toggleBtn) return;

    function openBubble() {
        widget.classList.add('is-expanded');
        setTimeout(() => {
            if (input) input.focus();
        }, 120);
    }

    function closeBubble() {
        widget.classList.remove('is-expanded');
        if (input) input.blur();
    }

    toggleBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (widget.classList.contains('is-expanded')) {
            closeBubble();
        } else {
            openBubble();
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            closeBubble();
        });
    }

    document.addEventListener('click', (e) => {
        if (widget.classList.contains('is-expanded') && !widget.contains(e.target)) {
            closeBubble();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && widget.classList.contains('is-expanded')) {
            closeBubble();
        }
    });
})();
</script>
@endpush
```

- [ ] **Step 6: Run the test**

Run: `php artisan test --filter=LandingPageSectionsTest`
Expected: PASS (6 tests). If `test_landing_uses_no_hardcoded_hex_colours_in_inline_styles` fails, locate the offending inline style and replace the hex with a token reference.

- [ ] **Step 7: Run the full test suite for regressions**

Run: `php artisan test`
Expected: all tests pass, including the pre-existing `test_navbar_contains_global_product_search`.

- [ ] **Step 8: Verify checkpoint**

Run: `php artisan view:clear`
Expected: compiled views cleared without error.

---

### Task 4: Browser verification

**Files:** none (verification only).

**Interfaces:**
- Consumes: the finished landing page.
- Produces: confirmed working light/dark themes and preserved interactions.

- [ ] **Step 1: Start the dev server**

Run: `php artisan serve --host=127.0.0.1 --port=8123`
Run in the background. Expect `Server running on http://127.0.0.1:8123`.

- [ ] **Step 2: Load the landing page in the browser**

Navigate to `http://127.0.0.1:8123/` and confirm the page renders with no server error.

- [ ] **Step 3: Verify light mode**

Confirm: hero renders (fallback, since `heroBanners` is empty), trust strip, services grid, how-it-works, why-us, product grid with 4 products, closing CTA, footer.

- [ ] **Step 4: Verify the theme toggle**

Click the toggle in the header. Confirm `html` gains the `theme-dark` class, the page surfaces turn dark, and the icon swaps from moon to sun.

- [ ] **Step 5: Verify persistence**

Reload the page. Confirm dark mode is still applied (localStorage `grafika-theme` = `dark`).

- [ ] **Step 6: Verify the system-preference default**

Clear localStorage, then reload with the browser emulating `prefers-color-scheme: dark`. Confirm dark is applied without any manual toggle. Emulate `prefers-color-scheme: light`, clear storage, reload, confirm light.

- [ ] **Step 7: Verify the functional surface**

- Hero carousel: confirm autoplay advances (watch for ~6s), arrows and dots work, ArrowLeft/ArrowRight move slides.
- Floating bubble: click to expand, type a query, submit → lands on `/products?q=…`; press Escape → collapses.
- Click a service card → lands on `/products?category=Apparel`.
- Click a product card → lands on `/products/<slug>`.
- Click "Track your order" in the closing CTA → opens the track-order modal (or navigates to `/track-order`).
- Toggle in the mobile drawer: open the drawer at 375px, confirm the toggle is present and works.

- [ ] **Step 8: Verify the console is clean**

Open the browser console. Expect zero errors and zero failed requests.

- [ ] **Step 9: Verify responsive layouts**

Capture screenshots at 1440px, 1024px, 768px and 375px in both themes. Confirm no horizontal scrollbar and that nothing is clipped.

- [ ] **Step 10: Verify regression on other routes**

Load `/products`, `/cart`, `/login`. Confirm each remains **light** (no `theme-dark` class), shows no toggle button, and still renders correctly.

- [ ] **Step 11: Stop the dev server**

Run: `taskkill /FI "IMAGENAME eq php.exe"` — or stop the background task via the task tooling.

---

## Self-Review

**Spec coverage**

| Spec requirement | Task |
|---|---|
| 3-layer token architecture (primitive → semantic → component) | Task 1 Step 3 |
| `html.theme-dark` semantic overrides | Task 1 Step 3 |
| No-FOUC theme initialisation | Task 1 Step 4 |
| Toggle controller: persist, aria sync, system-follow-when-unset | Task 1 Step 4, Task 2 Step 1 |
| `$darkModeEnabled` flag gating | Task 1 Step 4, Task 1 Step 7 |
| Toggle in header / mobile bar / drawer | Task 1 Step 7, Task 2 Step 2 |
| Remove Tailwind `bg-white` from `<header>` | Task 1 Step 5 |
| Tokenised shared chrome | Task 1 Step 6 |
| Focus-visible ring | Task 1 Step 8 |
| Hero restyle + fallback copy | Task 3 Step 3 |
| Trust strip (new) | Task 3 Step 3 |
| Services restyle + anchor upgrade | Task 3 Step 3 |
| How it works (new) | Task 3 Step 3 |
| Why Grafika South (new) | Task 3 Step 3 |
| Products restyle + anchor upgrade | Task 3 Step 3 |
| Closing CTA (new) | Task 3 Step 3 |
| Floating bubble restyle | Task 3 Step 4 |
| Reduced motion | Task 3 Step 4 |
| Carousel + bubble JS preserved | Task 3 Step 5 |
| Verification plan | Task 4 |

**Placeholder scan:** no TBD/TODO; every code step contains literal code. The only forward reference
is Task 2's partial, which Task 1 Step 9 explicitly calls out as the next step.

**Type consistency:** the token names produced in Task 1 Step 3 (`--bg-page`, `--text-main`,
`--accent-color`, `--p-cyan-500`, …) are exactly the names consumed in Task 3 Step 4 and in
the layout chrome in Task 1 Step 6. The `data-theme-toggle` attribute, `theme-dark` class and
`grafika-theme` storage key are identical across Tasks 1, 2 and 4.
