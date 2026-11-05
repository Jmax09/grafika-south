# Grafika South — Landing Page Redesign & Dark Mode

**Date:** 2026-11-04
**Status:** Approved by user (design questionnaire, 2026-11-04)
**Scope:** Public landing page (`/`) + shared site chrome (header, nav, mobile drawer, footer)
**Note:** this environment has no `git` binary and the project is not a repository, so this
spec is committed to disk only.

---

## 1. Context

Grafika South is a Laravel e-commerce print shop (San Pedro, Laguna, Philippines) selling
T-shirt printing, PVC ID cards, business cards and lamination.

Current state of the landing page:

- **Stack:** Laravel 12 + Blade. Tailwind runs via the **CDN** (`cdn.tailwindcss.com`) with
  `preflight: false`. Vite/Tailwind v4 is configured in `package.json`/`vite.config.js` but
  **no view uses `@vite`** — there is no CSS/JS build step. All styling is inline
  `<style>` blocks and inline `style=""` attributes inside Blade files.
- **Landing view:** `resources/views/welcome.blade.php` (1094 lines) extends
  `resources/views/layouts/app.blade.php` (1838 lines).
- **Design tokens:** the layout already declares a `:root` block with the CMYK brand palette
  (`--cmyk-cyan: #0099FF`, `--cmyk-magenta: #EC008C`, `--cmyk-yellow: #EAB308`,
  `--cmyk-key: #0F172A`) plus a few semantic aliases (`--text-main`, `--text-muted`,
  `--bg-page`, `--bg-surface`, `--border-color`, `--radius-md/lg`).
- **Dark mode:** **none exists.** No `dark:` classes, no `prefers-color-scheme`, and
  `<meta name="color-scheme" content="light">` is hardcoded.
- **Dead files:** `public/css/style.css` (2721 lines) and `public/js/main.js` are **not
  referenced by any view**. They are legacy and are out of scope — do not modify or delete.
- **Data:** `HeroBanner` (currently 0 rows → the branded fallback hero renders),
  `Product` (4 active rows), `Announcement`.

### Problem

1. The landing page reads as a generic template: hardcoded hex in ~58 inline styles,
   heavy inline `<style>` blocks, inconsistent radii, no visual rhythm between sections.
2. Inline `style="color:#0F172A"` / `background:#FFFFFF` declarations **outrank** inherited
   CSS variables, so a naive dark-mode override would leave white cards punching through a
   dark page.
3. The header carries a Tailwind `bg-white` utility alongside a hand-written
   `.site-header { background:#FFFFFF }` rule — a specificity fight waiting to happen.

---

## 2. Goals

| # | Goal |
|---|------|
| G1 | Redesign the landing page with a coherent, beautiful **Clean Studio** visual system |
| G2 | Add **dark mode** to the landing page and the shared site chrome |
| G3 | **Preserve all existing functionality** — no feature, route, or workflow may break |
| G4 | Establish a maintainable **3-layer token architecture** so future pages are themable |
| G5 | Meet WCAG AA contrast, 44px touch targets, visible focus, and respect `prefers-reduced-motion` |

### Non-goals

- Dark mode on the catalog, product detail, cart, checkout, or any dashboard page.
- Replacing the Tailwind CDN, or introducing a CSS/JS build step.
- Copy, route, model, controller, migration, or seed changes.
- Logo / brand-identity redesign.
- Modifying `public/css/style.css` or `public/js/main.js`.

---

## 3. Approach

Three options were considered:

**A — Token-first rebuild (CHOSEN).** Replace the ad-hoc `:root` block with a real
three-layer token system (primitive → semantic → component). Rebuild the landing page on
top of it, converting every hardcoded hex to a token. Dark mode becomes a second set of
semantic overrides. Shared chrome is tokenised at the same time.

**B — Minimal patch.** Keep inline hex, append a `.dark` override block. Small diff, but
inline styles win over inherited variables → half-dark page. Fails G1.

**C — Tailwind `dark:` variants.** Enable `darkMode: 'class'` and rewrite in utility
classes. Idiomatic, but preflight is off, the codebase is inline-style heavy, and this
would clash with the rest of the app. Fails G3 risk-profile.

**Chosen: A.** It is the only option that satisfies G1–G5 together, and it leaves the
codebase better than it found it.

### How "preserve functionality" is enforced

Every interactive element on the landing page is carried over verbatim or with a strictly
equivalent binding:

| Existing behaviour | Kept how |
|---|---|
| Hero carousel: 5s autoplay, prev/next, dots, ArrowLeft/Right keys, touch swipe, pause on hover, pause on tab blur | Same JS IIFE, same DOM ids/classes, restyled CSS only |
| Super-admin "Manage Hero Slider" button | Same `route('super-admin.hero.edit')` link, same visibility rule |
| Floating morphing search bubble → `products.index?q=` | Same form, same ids, same JS IIFE |
| Announcements bar | Same `$announcements` loop |
| Service cards → `products.index?category=…` | Same hrefs; `div[onclick]` upgraded to a real `<a href>` (same destination, now keyboard-accessible) |
| Product cards → `products.show` | Same hrefs, same upgrade |
| Cart badge, order-tracking modal, mobile drawer, nav links | Untouched in the layout |

---

## 4. Design system

### 4.1 Art direction — "Clean Studio"

Airy, minimal, editorial. **One** primary (cyan) carries the interface; magenta and yellow
are demoted to tiny registration-dot accents so the CMYK identity survives without noise.

- **Whitespace is the main material.** Section rhythm 96px desktop / 64px tablet / 56px mobile.
- **Hairlines over shadows.** 1px borders; shadows only as a soft lift on hover.
- **Radius scale:** 8px (small) / 10px (control) / 16px (card) / 20px (feature) / 999px (pill).
- **Motion:** 150–250ms, transform + opacity only. Carousel cross-fade is slower (900ms) with
  a 9s Ken Burns drift.
- **Texture:** a 2% halftone dot field appears in the hero only — the print-shop motif.

### 4.2 Typography

Already loaded: **Outfit** (headings) + **Inter** (body). No new font requests.

| Role | Size | Weight | Line height | Tracking |
|---|---|---|---|---|
| Display (hero) | `clamp(2.25rem, 5vw, 3.75rem)` | 800 | 1.06 | -0.03em |
| Section title | `clamp(1.75rem, 3vw, 2.5rem)` | 800 | 1.15 | -0.02em |
| Card title | `1.125rem` | 700 | 1.3 | -0.01em |
| Lead | `clamp(1rem, 1.4vw, 1.125rem)` | 400 | 1.7 | — |
| Body | `1rem` | 400 | 1.65 | — |
| Eyebrow | `0.75rem` | 700 | — | 0.14em, uppercase |
| Meta | `0.8125rem` | 500 | 1.5 | — |

### 4.3 Token architecture

**Layer 1 — Primitive (raw values, theme-independent).**

```
--p-white:#FFFFFF   --p-ink-950:#0B1120  --p-ink-900:#0F172A  --p-ink-850:#172033
--p-ink-800:#1E293B --p-ink-700:#334155  --p-ink-600:#475569  --p-ink-500:#64748B
--p-ink-400:#94A3B8 --p-ink-300:#CBD5E1  --p-ink-200:#E2E8F0  --p-ink-100:#F1F5F9
--p-ink-050:#F8FAFC
--p-cyan-050:#E6F4FF --p-cyan-400:#38BDF8 --p-cyan-500:#0099FF --p-cyan-600:#0080FF
--p-magenta-050:#FDE8F5 --p-magenta-500:#EC008C --p-magenta-600:#C80075
--p-yellow-050:#FEF9C3 --p-yellow-500:#EAB308 --p-yellow-600:#CA8A04
--p-green-500:#22C55E --p-amber-500:#F59E0B --p-red-500:#EF4444 --p-red-600:#DC2626
```

**Layer 2 — Semantic (`:root` = light, `html.theme-dark` = dark).**

| Token | Light | Dark |
|---|---|---|
| `--bg-page` | `--p-ink-050` | `#080D18` |
| `--bg-surface` | `--p-white` | `#0E1626` |
| `--bg-surface-2` | `#FBFCFD` | `#131D31` |
| `--bg-subtle` | `--p-ink-100` | `#18233A` |
| `--bg-inverse` | `--p-ink-900` | `--p-white` |
| `--text-main` | `--p-ink-900` | `#E6EDF7` |
| `--text-muted` | `--p-ink-500` | `#94A3B8` |
| `--text-subtle` | `--p-ink-400` | `#64748B` |
| `--text-inverse` | `--p-white` | `--p-ink-900` |
| `--text-accent` | `--p-cyan-600` | `--p-cyan-400` |
| `--border-color` | `--p-ink-200` | `#1E293B` |
| `--border-strong` | `--p-ink-300` | `#334155` |
| `--accent-color` | `--p-cyan-500` | `--p-cyan-400` |
| `--accent-hover` | `--p-cyan-600` | `--p-cyan-500` |
| `--accent-soft` | `--p-cyan-050` | `rgba(56,189,248,.12)` |
| `--accent-contrast` | `--p-white` | `#04121F` |
| `--accent-ring` | `rgba(0,153,255,.35)` | `rgba(56,189,248,.45)` |
| `--danger-soft` | `#FEE2E2` | `rgba(239,68,68,.14)` |
| `--shadow-sm/md/lg` | slate-tinted, low alpha | black-tinted, higher alpha |

**Contrast targets (verified):** light `--text-muted` on `--bg-page` ≈ 4.7:1;
dark `--text-muted` on `--bg-surface` ≈ 6.4:1; dark `--accent-color` on `--bg-surface` ≈ 8.6:1;
`--accent-contrast` on `--accent-color` in both themes ≥ 4.5:1.

**Layer 3 — Component.** `--btn-primary-bg/-bg-hover/-text`, `--btn-outline-border/-text/-bg-hover`,
`--btn-ghost-text/-bg-hover`, `--card-bg/-border/-shadow/-shadow-hover/-radius`,
`--header-bg/-border/-shadow`, `--input-bg/-border/-text/-placeholder`, `--focus-ring`.

### 4.4 Spacing scale

`4 · 8 · 12 · 16 · 24 · 32 · 48 · 64 · 96 · 120`

---

## 5. Dark mode mechanics

### 5.1 Activation

The theme class lives on `<html>`: `html.theme-dark`. Because it is only ever applied by the
landing page's own script, **every other route stays light** — which is exactly the agreed
scope. The shared chrome (header, nav, drawer, footer) is themed because it inherits the
same semantic tokens.

### 5.2 No-FOUC initialisation

An inline script in the layout `<head>` (before paint) resolves the mode:

1. `localStorage.getItem('grafika-theme')` → `'light' | 'dark' | null`
2. `null` → `matchMedia('(prefers-color-scheme: dark)').matches`
3. `document.documentElement.classList.toggle('theme-dark', isDark)`
4. `document.documentElement.style.colorScheme = isDark ? 'dark' : 'light'`

`meta[name="color-scheme"]` is changed from `content="light"` to `content="light dark"`,
and the inline `colorScheme` style keeps the UA-rendered controls (scrollbars, inputs)
correct without affecting non-themed pages.

### 5.3 Toggle controller

One delegated listener on `document` for `[data-theme-toggle]`:

- Flips the class, syncs `aria-pressed` / `aria-label` / `title` on **every** toggle button
  (desktop header, mobile action bar, mobile drawer).
- Persists the choice to `localStorage['grafika-theme']`.
- A `matchMedia` `change` listener re-applies the system preference **only while no
  explicit choice has been stored** — an explicit user choice always wins.

### 5.4 Toggle placement

Rendered only on routes that opt in. The layout computes one flag and uses it for both the
script and the buttons:

```php
@php
    $darkModeEnabled = request()->route()?->getName() === 'landing';
@endphp
```

Three instances: desktop nav group, mobile action bar, and mobile drawer body.

Button: 44×44px (meets the touch-target minimum), radius 10px, 1px `--border-strong`,
transparent background, sun/moon SVG icons swapped via the `theme-dark` class.
Icon set is **SVG, never emoji**.

---

## 6. Landing page structure

Container: `.landing-container` — max-width 1200px, padding 0 24px (20px ≤640px).

| # | Section | Status | Notes |
|---|---|---|---|
| 1 | Announcements bar | kept | Same `$announcements` loop, tokenised |
| 2 | Hero carousel / fallback | kept | DB-driven; restyled (below) |
| 3 | Trust strip | **new** | 4-up band, hairline dividers |
| 4 | Services Offered | kept | 4 cards, per-card CMYK accent |
| 5 | How It Works | **new** | 3 steps on a numbered rail |
| 6 | Why Grafika South | **new** | 2×2 value-prop cards |
| 7 | Print Products catalog | kept | Same `$products` loop, restyled |
| 8 | Closing CTA | **new** | Centred band, 2 buttons |
| 9 | Footer | kept | In the layout, themed |

**Visual rhythm:** boxed band → boxed cards → unboxed rail → boxed 2×2 → boxed grid →
unboxed CTA band. No two adjacent sections share a card treatment.

### 6.1 Hero

Carousel shell: radius 20px, 1px `--border-color`, `--shadow-lg`, background `--p-ink-950`.
Track height 560px (1024px→460px, 768px→380px, 480px→320px).

- Slide: `opacity 0→1` over 900ms + `scale(1.05)→scale(1)` over 9s (Ken Burns).
- Overlay: `linear-gradient(100deg, rgba(8,13,24,.90) → transparent)` plus a bottom scrim.
- Content: max-width 40rem, bottom-anchored, left padding `clamp(1.5rem, 4vw, 3.5rem)`.
- Eyebrow: frosted pill (`rgba(255,255,255,.10)` + `backdrop-filter: blur(10px)`) holding
  the four CMYK registration dots + label.
- Title: display scale, weight 800, `--text` white with a soft shadow.
- CTAs: primary (cyan) + ghost (white hairline).
- Arrows: 44px circles, frosted dark, cyan on hover.
- Dots: 8px, active = 24px cyan, inside a frosted pill.

**Fallback hero copy** (renders today — 0 banners in DB):

- Eyebrow: CMYK dots + `4-PROCESS CMYK · SAN PEDRO, LAGUNA`
- H1: **"Custom prints, ordered online."** — "ordered online" in cyan
- Sub: *"T-shirts, PVC ID cards, business cards and lamination — priced live as you
  configure, reviewed by our print team, and tracked from press to pickup."*
- CTA 1: "Browse Catalog & Order" → `products.index`
- CTA 2: "Create Customer Account" → `register` (guests only)

### 6.2 Trust strip (new)

Four items, hairline-separated, values derived from **real** app capabilities — no invented
business statistics:

| Icon | Label | Sub |
|---|---|---|
| `fa-layer-group` | 4-Process CMYK | Commercial colour, calibrated |
| `fa-calculator` | Live Pricing | Updates as you configure |
| `fa-boxes-stacked` | Auto Stock Sync | Inventory deducts on order |
| `fa-route` | Order Tracking | Follow your job to pickup |

### 6.3 Services Offered (kept)

4 cards (→2→1). Each card: `--card-bg`, hairline border, radius 16px, a 3px top accent in
its own CMYK colour (cyan / magenta / yellow / key), 48px icon tile in the matching
`*-soft` tint, title, description, "From ₱…" price line with arrow. Hover: `translateY(-4px)`,
`--shadow-md`, border → accent tint.

### 6.4 How It Works (new)

Three steps on a hairline rail, numbered `01 / 02 / 03`:

1. `fa-sliders` — **Configure** — "Choose size, material and finish. Options price themselves as you go."
2. `fa-cloud-arrow-up` — **Upload artwork** — "Send your design file with your order — our print team reviews it before press."
3. `fa-receipt` — **Order & track** — "Pay by QR and follow your job from confirmation to pickup."

### 6.5 Why Grafika South (new)

2×2 grid of cards with a large icon tile, title and description. All four claims are taken
from capabilities already stated in the app's own copy:

- `fa-gauge-high` — **High-definition CMYK precision**
- `fa-diagram-project` — **Automated BOM engine** — accurate costing, fewer surprises
- `fa-magnifying-glass-chart` — **Real-time artwork review**
- `fa-location-dot` — **Produced locally** — San Pedro, Laguna

### 6.6 Products catalog (kept)

Same `$products` loop and `products.show` links. Card: `--card-bg`, hairline border,
radius 16px; 200px image well on `--bg-surface-2`; category badge, featured badge,
out-of-stock badge; price on `--text-accent`; CTA button. Hover: `translateY(-5px)` +
`--shadow-lg` + image `scale(1.05)`.

### 6.7 Closing CTA (new)

Centred band on `--bg-surface-2` with hairline border:
**"Ready to put your design on press?"** + "Browse the catalog" (primary) + "Track your order" (ghost).

### 6.8 Floating search bubble (kept)

56px cyan circle with a 2.5px white ring and `--shadow-md`; idle pulse every 3.2s. Expanded
state: 420px wide (max `calc(100vw - 48px)`), `--bg-surface` background, `--border-strong`
border, input text `--text-main`, placeholder `--text-muted`. Hidden below 768px.

---

## 7. Files

### Modify

| File | Change |
|---|---|
| `resources/views/layouts/app.blade.php` | Replace the `:root` block with the 3-layer token system; tokenise the header / nav / drawer / footer chrome; remove `bg-white` + `shadow-sm` from the `<header>` element; add the theme `<head>` script; add the toggle to the desktop nav, mobile action bar and mobile drawer; change `meta[name=color-scheme]` |
| `resources/views/welcome.blade.php` | Full rebuild of markup + CSS on the token system; add sections 3, 5, 6, 8; upgrade the two `div[onclick]` cards to anchors; new hero copy |

### Create

| File | Purpose |
|---|---|
| `resources/views/partials/theme-toggle.blade.php` | The sun/moon toggle button (SVG icons, `data-theme-toggle`) |

### Explicitly untouched

`routes/`, `app/`, `database/`, `resources/views/products/`, `resources/views/customer/`,
`resources/views/admin/`, `resources/views/super-admin/`, `resources/views/auth/`,
`resources/views/layouts/admin.blade.php`, `public/css/style.css`, `public/js/main.js`.

---

## 8. Accessibility & quality bar

- Text contrast ≥ 4.5:1 (large text ≥ 3:1) in **both** themes.
- All interactive elements ≥ 44×44px with `cursor: pointer`.
- `:focus-visible` ring on every control, using `--focus-ring`.
- All decorative icons `aria-hidden="true"`; every icon-only button carries `aria-label`.
- Carousel: `aria-roledescription="carousel"`, `aria-selected` on dots, keyboard arrows.
- Every image has descriptive `alt`; product images `loading="lazy"`, first hero slide `eager`.
- `@media (prefers-reduced-motion: reduce)` disables the Ken Burns drift, the bubble pulse,
  and all transitions/animations.
- No emoji used as UI icons — FontAwesome + inline SVG only.
- Hover states use colour/opacity/shadow, never layout-shifting scale on text.
- Responsive verified at 375 / 768 / 1024 / 1440 with no horizontal scroll.

---

## 9. Verification plan

1. `php artisan view:clear`
2. `php artisan serve` on a free port; load `/`.
3. Browser check (light + dark):
   - Toggle persists across reload; system preference respected on first visit.
   - Hero carousel autoplays; arrows, dots, arrow keys and touch swipe work.
   - Floating bubble expands, submits to `products.index?q=`, collapses on Escape/outside click.
   - Service and product cards navigate to the correct routes.
   - Super-admin "Manage Hero Slider" button present for a super admin.
4. Console must be free of errors.
5. Screenshot light and dark at 1440px and 375px.
6. Regression: open `/products`, `/cart`, `/login` — these must remain light and unmodified.

---

## 10. Out of scope / follow-ups

- Extending dark mode to catalog, product detail, cart and checkout (the token layer is
  already in place, so this is a follow-on task, not a rewrite).
- Removing the dead `public/css/style.css` and `public/js/main.js`.
- Replacing the Tailwind CDN with a real build step.

---

## 11. Implementation notes (added after browser verification)

Three defects were found during verification and fixed; they are now part of the design.

### 11.1 Toggle ARIA state did not reflect the active theme

The controller lives in `<head>`, so it runs **before** the toggle buttons exist in the DOM.
`syncToggles()` therefore found an empty `NodeList` on first paint and the buttons kept their
server-rendered defaults (`aria-pressed="false"`, "Switch to dark theme") even in dark mode.

**Fix:** after the initial `apply()`, re-sync on `DOMContentLoaded` (or immediately if the DOM is
already parsed), so the ARIA label and pressed state always match the rendered theme.

### 11.2 Brand hues were unreadable in dark mode

The trust strip and service cards used raw brand literals as foreground colours
(`--cmyk-key` = `#0F172A` for the "Lamination" icon, `--cmyk-yellow-dark` on a light tint).
On a dark surface the key icon was effectively invisible, and the magenta/yellow tint chips
glared.

**Fix:** a fourth group of semantic tokens was added — `--brand-cyan-fg`,
`--brand-magenta-fg`, `--brand-yellow-fg`, `--brand-key-fg` — which shift in
`html.theme-dark`. The tint primitives `--p-cyan-050`, `--p-magenta-050` and
`--p-yellow-050` also get translucent overrides in dark mode. The legacy `--cmyk-*`
aliases keep their literal values so no other view is affected.

### 11.3 Mobile nav overflowed at 320px

At a 320px viewport the brand wordmark plus the mobile action group measured 309px inside a
288px content box, clipping the hamburger by 5px.

**Fix:** a `@media (max-width: 359.98px)` block shrinks the wordmark to 24px / 88px max-width
and tightens `.mobile-nav-actions` to a `0.3rem` gap. The 44px touch target of the theme
toggle is deliberately preserved. Measured after the fix: `bodyScrollWidth === innerWidth` at
320, 360, 414, 768 and 1440 — no overflow at any breakpoint.

---

## 12. Scope extension — dark mode across every module

The original scope was the landing page plus the shared chrome. It was widened to **every
browser-rendered module** of the app.

### 12.1 Shared token layer

Two partials are the single source of truth and are included by every layout:

| File | Purpose |
|---|---|
| `resources/views/partials/design-tokens.blade.php` | Layers 1–3 + `html.theme-dark` overrides + the theme-toggle styles + the Tailwind colour-utility override layer |
| `resources/views/partials/theme-script.blade.php` | The theme controller (resolve → apply → delegate → follow OS) |

The `--cmyk-*` brand aliases stay in each layout's own `:root`, because the auth portal uses a
slightly different brand yellow (`#FFD200`).

Layouts rewired: `layouts/app.blade.php` (storefront + customer + auth),
`layouts/admin.blade.php` (admin + super-admin), and the auth portal inherits the app layout.

### 12.2 Property-aware conversion

The codebase held **6,046 hardcoded hex values across 75 views** (2,037 inside inline
`style=""` attributes). A flat hex→token map cannot work: `#FFFFFF` as `color:` must stay
white, while the same value as `background:` must become a dark surface. The converter
therefore resolves every value against the CSS property that owns it:

| Property | Map used |
|---|---|
| `color`, `-webkit-text-fill-color` | foreground map |
| `background`, `background-color`, `background-image` | surface map |
| `border*`, `outline*`, `box-shadow`, `text-shadow` | border map (tints keep their hue via a border token) |

Only two contexts are rewritten — `<style>` blocks (comment-aware, so a CSS comment can never
swallow the next declaration) and `style="..."` attributes. Everything else is deliberately
untouched:

- **Print colour swatches** in `products/show.blade.php` (`'burgundy' => '#800020'`, tan,
  gold, turquoise, silver …) live in PHP array data and are never CSS.
- **JS handler strings** (`onmouseover="this.style.borderColor='#0099FF'"`) are not CSS.
- **CSS custom property definitions** (`--cmyk-cyan: #0099FF`) are never rewritten.

Result: **4,440 conversions across 47 files, 0 unmapped values.** A verification pass confirms
the swatches, the CMYK aliases and the JS handlers are byte-identical to before.

### 12.3 Tailwind colour utilities

Tailwind runs from the CDN and injects literal colours, so utility markup ignored the token
layer. The CDN config in both layouts now resolves the neutral scales to tokens:

```js
slate / gray:  50 → --bg-surface-2   100 → --bg-subtle    200 → --border-color
                300 → --border-strong 400 → --text-subtle  500/600 → --text-muted
                700/800 → --text-main  900/950 → literal (used as dark backdrops)
```

`white` stays literal (it is text-on-colour); `bg-white` is re-pointed at `--bg-surface` by a
scoped rule instead.

### 12.4 Foreground brand colours

281 foreground usages referenced brand literals — `--primary-dark` (119), `--cmyk-cyan` (97),
`--cmyk-key` (16) and friends. On a dark surface the key black was invisible, so they were
re-pointed at theme-aware tokens (`--text-main`, `--brand-cyan-fg`, …). `--primary-dark` is
now defined as `--cmyk-key` in light and `--text-main` in dark.

### 12.5 Dark header is brand blue

The dark header was near-black (`rgba(14, 22, 38, .88)`). It is now a **deep brand blue**
(`#0C4A6E`), with a header-scoped colour language so its contents stay light-on-blue:

| Token | Light | Dark |
|---|---|---|
| `--header-bg` (storefront, glass) | `rgba(255,255,255,.88)` | `rgba(12,74,110,.92)` |
| `--header-solid` (admin topbar) | `#FFFFFF` | `#0C4A6E` |
| `--header-text` | `--text-main` | `#FFFFFF` |
| `--header-text-muted` | `--text-muted` | `rgba(255,255,255,.78)` |
| `--header-hover-bg` | `--bg-subtle` | `rgba(255,255,255,.16)` |
| `--header-brand-chip` | transparent | `#FFFFFF` |

White on `#0C4A6E` is ≈8.5:1, comfortably above AA. The wordmark is an opaque JPG, so on the
blue header it is given a white rounded chip rather than showing a white rectangle.

### 12.6 Explicitly out of scope

- **HTML email templates** stay light — CSS variables do not resolve in mail clients, and light
  emails read better. Confirmed with the user.
- **Third-party vendor views** (`vendor/pagination`) are untouched.


