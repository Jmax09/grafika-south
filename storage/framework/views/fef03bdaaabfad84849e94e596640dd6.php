<?php $__env->startSection('title', 'Grafika South - CMYK Print Shop & Ordering Platform'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* ==========================================================
       LANDING PAGE — "Clean Studio"
       Every colour resolves through a semantic token, so dark
       mode needs no per-component overrides.
       ========================================================== */

    /* ----- Container & rhythm ----- */
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
    .section-head {
        margin: 0 auto 3rem;
        max-width: 42rem;
        text-align: center;
    }
    .section-head--split {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
        max-width: none;
        text-align: left;
    }
    .section-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-family: 'Outfit', sans-serif;
        font-size: clamp(1.75rem, 3vw, 2.5rem);
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.02em;
        color: var(--text-main);
        margin: 0 0 0.5rem;
    }
    .section-title__icon { color: var(--accent-color); font-size: 0.85em; }
    .section-sub {
        color: var(--text-muted);
        font-size: 1rem;
        line-height: 1.65;
        margin: 0;
    }
    .section-head__action { flex-shrink: 0; }

    /* ----- Announcements ----- */
    .landing-announcement {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: var(--accent-soft);
        border: 1px solid var(--accent-color);
        color: var(--text-accent);
        border-radius: var(--radius-md);
        padding: 0.8rem 1.1rem;
        margin-bottom: 2rem;
    }
    .landing-announcement > i { color: var(--accent-color); font-size: 1rem; flex-shrink: 0; }
    .landing-announcement__text {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem 1.5rem;
        font-size: 0.9rem;
        font-weight: 600;
    }

    /* ==========================================================
       HERO — carousel
       ========================================================== */
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
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transform: scale(1.05);
        transition: opacity 0.9s ease-in-out, transform 9s ease-out;
    }
    .hero-slide.active { opacity: 1; transform: scale(1); z-index: 2; }
    .hero-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .hero-slide__link { display: block; width: 100%; height: 100%; position: relative; }
    .hero-slide-overlay {
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            linear-gradient(100deg, rgba(8, 13, 24, 0.90) 0%, rgba(8, 13, 24, 0.62) 38%, rgba(8, 13, 24, 0.12) 72%, rgba(8, 13, 24, 0) 100%),
            linear-gradient(0deg, rgba(8, 13, 24, 0.55) 0%, rgba(8, 13, 24, 0) 45%);
    }
    .hero-slide-content {
        position: absolute;
        z-index: 3;
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
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
        padding: 0.7rem 1.6rem;
        background: var(--cmyk-cyan);
        color: var(--p-white);
        font-weight: 700;
        font-size: 0.92rem;
        border-radius: var(--radius-sm);
        text-decoration: none;
        cursor: pointer;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 4px 16px -4px rgba(0, 153, 255, 0.5);
    }
    .hero-slide-link:hover {
        background: var(--cmyk-cyan-hover);
        box-shadow: 0 8px 22px -6px rgba(0, 153, 255, 0.6);
    }

    .carousel-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: var(--p-white);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1rem;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }
    .carousel-arrow:hover { background: var(--cmyk-cyan); border-color: var(--cmyk-cyan); }
    .carousel-arrow.prev { left: 1.25rem; }
    .carousel-arrow.next { right: 1.25rem; }

    .carousel-dots {
        position: absolute;
        bottom: 1.25rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 0.45rem;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(10px);
        padding: 0.35rem 0.75rem;
        border-radius: var(--radius-pill);
        border: 1px solid rgba(255, 255, 255, 0.16);
    }
    .carousel-dot {
        width: 8px;
        height: 8px;
        border-radius: var(--radius-pill);
        background: color-mix(in srgb, var(--bg-surface) 45%, transparent);
        cursor: pointer;
        border: none;
        padding: 0;
        outline: none;
        transition: background-color 0.3s ease, width 0.3s ease;
    }
    .carousel-dot:hover { background: color-mix(in srgb, var(--bg-surface) 85%, transparent); }
    .carousel-dot.active { background: var(--cmyk-cyan); width: 24px; }

    .hero-carousel .super-admin-edit-btn {
        position: absolute;
        top: 1.25rem;
        right: 1.25rem;
        z-index: 15;
        font-size: 0.82rem;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-sm);
    }

    /* ==========================================================
       HERO — fallback (no banners uploaded)
       ========================================================== */
    .hero-fallback {
        position: relative;
        overflow: hidden;
        background: var(--p-ink-950);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
        padding: 7rem 2.5rem 6.5rem;
        margin-bottom: 2.5rem;
        text-align: center;
    }
    .hero-fallback::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            radial-gradient(rgba(255, 255, 255, 0.055) 1.5px, transparent 1.6px),
            radial-gradient(rgba(0, 153, 255, 0.05) 1.5px, transparent 1.6px);
        background-size: 22px 22px, 22px 22px;
        background-position: 0 0, 11px 11px;
    }
    .hero-fallback::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(70% 60% at 50% 0%, rgba(0, 153, 255, 0.16) 0%, transparent 70%);
    }
    .hero-fallback > * { position: relative; z-index: 2; }
    .hero-fallback__inner { max-width: 48rem; margin: 0 auto; }
    .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(10px);
        color: var(--p-ink-300);
        padding: 0.45rem 1.05rem;
        border-radius: var(--radius-pill);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.14em;
        margin-bottom: 1.75rem;
    }
    .hero-fallback__title {
        font-family: 'Outfit', sans-serif;
        font-size: clamp(2.25rem, 5vw, 3.75rem);
        font-weight: 800;
        line-height: 1.06;
        letter-spacing: -0.03em;
        color: var(--p-white);
        margin: 0 0 1.25rem;
    }
    .hero-accent { color: var(--p-cyan-400); }
    .hero-fallback__sub {
        font-size: clamp(1rem, 1.4vw, 1.125rem);
        color: var(--p-ink-300);
        line-height: 1.7;
        max-width: 38rem;
        margin: 0 auto 2.25rem;
    }
    .hero-cta-row { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
    .hero-cta-primary {
        padding: 0.9rem 2rem;
        font-size: 1rem;
        font-weight: 700;
        border-radius: var(--radius-md);
        background: var(--cmyk-cyan);
        border: none;
        color: var(--p-white);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.2s ease;
        box-shadow: none;
    }
    .hero-cta-primary:hover {
        background: var(--cmyk-cyan-hover);
        box-shadow: none;
    }
    .hero-cta-ghost {
        padding: 0.9rem 2rem;
        font-size: 1rem;
        font-weight: 700;
        border-radius: var(--radius-md);
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.26);
        color: var(--p-white);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }
    .hero-cta-ghost:hover {
        background: rgba(255, 255, 255, 0.12);
        border-color: color-mix(in srgb, var(--bg-surface) 45%, transparent);
    }
    .hero-cta-ghost--onlight {
        background: var(--bg-surface);
        border-color: var(--border-strong);
        color: var(--text-main);
    }
    .hero-cta-ghost--onlight:hover {
        background: var(--bg-subtle);
        border-color: var(--accent-color);
        color: var(--text-accent);
    }
    @media (max-width: 640px) {
        .hero-fallback { padding: 4rem 1.25rem 3.5rem; }
        .hero-cta-row { flex-direction: column; }
        .hero-cta-row a { width: 100%; justify-content: center; }
    }

    /* ==========================================================
       TRUST STRIP
       ========================================================== */
    .trust-strip {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }
    .trust-strip__item {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 1.35rem 1.5rem;
        border-right: 1px solid var(--border-color);
    }
    .trust-strip__item:last-child { border-right: none; }
    .trust-strip__icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border-radius: var(--radius-md);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }
    .trust-strip__icon--cyan { background: var(--accent-soft); color: var(--brand-cyan-fg); }
    .trust-strip__icon--magenta { background: var(--p-magenta-050); color: var(--brand-magenta-fg); }
    .trust-strip__icon--yellow { background: var(--p-yellow-050); color: var(--brand-yellow-fg); }
    .trust-strip__icon--key { background: var(--bg-subtle); color: var(--brand-key-fg); }
    .trust-strip__label {
        display: block;
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-main);
        line-height: 1.3;
    }
    .trust-strip__sub {
        display: block;
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-top: 0.15rem;
        line-height: 1.4;
    }
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

    /* ==========================================================
       SERVICES OFFERED
       ========================================================== */
    .service-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
    .service-card {
        display: flex;
        flex-direction: column;
        text-align: left;
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
        width: 52px;
        height: 52px;
        border-radius: var(--radius-md);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        margin-bottom: 1.25rem;
    }
    .service-card--cyan .service-card__icon { background: var(--accent-soft); color: var(--brand-cyan-fg); }
    .service-card--magenta .service-card__icon { background: var(--p-magenta-050); color: var(--brand-magenta-fg); }
    .service-card--yellow .service-card__icon { background: var(--p-yellow-050); color: var(--brand-yellow-fg); }
    .service-card--key .service-card__icon { background: var(--bg-subtle); color: var(--brand-key-fg); }
    .service-card__title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--text-main);
        margin: 0 0 0.5rem;
    }
    .service-card__desc {
        font-size: 0.9rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin: 0 0 1.5rem;
        flex: 1;
    }
    .service-card__price {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-accent);
    }
    .service-card__price i { transition: transform 0.2s ease; }
    .service-card:hover .service-card__price i { transform: translateX(3px); }
    @media (max-width: 1023.98px) { .service-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575.98px) { .service-grid { grid-template-columns: 1fr; gap: 1rem; } }

    /* ==========================================================
       HOW IT WORKS
       ========================================================== */
    .hiw-head { text-align: center; max-width: 44rem; margin: 0 auto 3rem; }
    .hiw-title {
        font-family: 'Outfit', sans-serif; font-weight: 800;
        font-size: clamp(2.2rem, 5vw, 3.2rem); letter-spacing: -0.02em;
        color: var(--text-main); margin: 0 0 0.5rem; line-height: 1.05;
    }
    .hiw-title em { font-style: italic; color: var(--accent-color); }
    .hiw-sub { color: var(--text-muted); font-size: 1.05rem; margin: 0; }

    .hiw-grid { display: flex; align-items: stretch; justify-content: center; gap: 0; }
    .hiw-card {
        position: relative; flex: 1 1 0; max-width: 360px;
        background: var(--bg-elevated); border: 1px solid var(--border-color);
        border-radius: var(--radius-xl); box-shadow: var(--shadow-md); padding: 1rem;
    }
    .hiw-arrow { flex: 0 0 auto; display: flex; align-items: center; justify-content: center; width: 48px; }
    .hiw-arrow span {
        width: 40px; height: 40px; border-radius: 50%; background: var(--bg-elevated);
        border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);
        display: flex; align-items: center; justify-content: center; color: var(--accent-color); font-size: 0.95rem;
    }
    .hiw-art {
        height: 190px; border-radius: var(--radius-lg); position: relative; overflow: hidden;
        background: linear-gradient(160deg, var(--accent-soft), var(--bg-elevated));
        border: 1px solid var(--border-color);
        display: flex; align-items: center; justify-content: center; padding: 1rem;
    }
    .hiw-card__body { padding: 1.1rem 0.6rem 0.6rem; position: relative; }
    .hiw-card__num { font-family: 'Outfit', sans-serif; font-size: 0.8rem; font-weight: 800; letter-spacing: 0.1em; color: var(--text-accent); }
    .hiw-card__title { font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 800; letter-spacing: -0.01em; color: var(--text-main); margin: 0.35rem 0 0.5rem; }
    .hiw-card__desc { font-size: 0.92rem; color: var(--text-muted); line-height: 1.6; margin: 0; }
    .hiw-card__wm { position: absolute; right: 0.9rem; bottom: 0.4rem; font-family: 'Outfit', sans-serif; font-size: 3.4rem; font-weight: 800; color: var(--text-main); opacity: 0.06; line-height: 1; pointer-events: none; }

    /* Card 1: shirt + pickers */
    .hiw-shirt { width: 105px; height: 105px; flex: 0 0 auto; }
    .hiw-shirt img { width: 100%; height: 100%; object-fit: contain; }
    .hiw-picker { background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); padding: 0.6rem 0.7rem; margin-left: 0.85rem; display: grid; gap: 0.55rem; align-self: center; }
    .hiw-picker__row { display: flex; align-items: center; gap: 0.5rem; }
    .hiw-picker__label { font-size: 0.72rem; font-weight: 700; color: var(--text-muted); width: 40px; flex: 0 0 40px; }
    .hiw-seg { display: inline-flex; gap: 2px; background: var(--bg-stripe); border-radius: 8px; padding: 2px; }
    .hiw-seg span { font-size: 0.68rem; font-weight: 700; color: var(--text-muted); padding: 2px 7px; border-radius: 6px; }
    .hiw-seg span.is-on { background: var(--accent-color); color: var(--accent-contrast); }
    .hiw-sw { width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--bg-elevated); box-shadow: 0 0 0 1px var(--border-color); display: inline-flex; align-items: center; justify-content: center; }
    .hiw-sw.is-on { box-shadow: 0 0 0 2px var(--accent-color); }
    .hiw-sw i { font-size: 8px; color: #fff; }

    /* Card 2: upload */
    .hiw-drop { width: 150px; height: 150px; border: 2px dashed var(--border-strong); border-radius: var(--radius-lg); background: var(--bg-elevated); display: flex; align-items: center; justify-content: center; }
    .hiw-file { width: 74px; height: 92px; background: #fff; border: 1px solid var(--border-color); border-radius: 8px; box-shadow: var(--shadow-md); position: relative; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding-bottom: 8px; }
    .hiw-file::before { content: "PDF"; position: absolute; top: 8px; left: 8px; background: #E11D48; color: #fff; font-size: 0.55rem; font-weight: 800; padding: 1px 5px; border-radius: 4px; }
    .hiw-file i { font-size: 1.4rem; color: var(--accent-color); margin-bottom: 4px; }
    .hiw-file span { font-size: 0.55rem; font-weight: 700; color: var(--text-muted); }
    .hiw-file__check { position: absolute; right: -8px; bottom: -8px; width: 20px; height: 20px; border-radius: 50%; background: var(--accent-color); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.6rem; border: 2px solid var(--bg-elevated); }
    .hiw-cloud { position: absolute; top: 10px; right: 10px; width: 34px; height: 34px; border-radius: 10px; background: var(--bg-elevated); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: center; color: var(--accent-color); font-size: 1rem; }

    /* Card 3: timeline + payment */
    .hiw-flow { width: 100%; display: flex; flex-direction: column; gap: 0.9rem; }
    .hiw-timeline { display: flex; align-items: flex-start; justify-content: space-between; position: relative; }
    .hiw-timeline::before { content: ""; position: absolute; top: 9px; left: 8%; right: 8%; height: 2px; background: var(--border-color); z-index: 0; }
    .hiw-node { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; width: 15%; }
    .hiw-node__dot { width: 18px; height: 18px; border-radius: 50%; background: var(--accent-color); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.55rem; border: 2px solid var(--bg-elevated); font-weight: 800; }
    .hiw-node__lbl { font-size: 0.5rem; font-weight: 800; letter-spacing: 0.02em; color: var(--text-main); text-align: center; text-transform: uppercase; }
    .hiw-pay-img { width: 100%; height: auto; display: block; }

    @media (max-width: 900px) {
        .hiw-grid { flex-direction: column; align-items: center; gap: 1.25rem; }
        .hiw-card { width: 100%; max-width: 420px; }
        .hiw-arrow { display: none; }
    }

    /* ==========================================================
       WHY GRAFIKA SOUTH
       ========================================================== */
    .value-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
    .value-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        padding: 2.25rem 2rem;
        box-shadow: var(--card-shadow);
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .value-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
        border-color: var(--border-strong);
    }
    .value-card__icon {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-lg);
        background: var(--accent-soft);
        color: var(--brand-cyan-fg);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 1.25rem;
    }
    .value-card__title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--text-main);
        margin: 0 0 0.6rem;
    }
    .value-card__desc { font-size: 0.95rem; color: var(--text-muted); line-height: 1.65; margin: 0; }
    @media (max-width: 767.98px) { .value-grid { grid-template-columns: 1fr; gap: 1rem; } }

    /* ==========================================================
       PRINT PRODUCTS CATALOG
       ========================================================== */
    .product-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    .product-storefront-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        text-decoration: none;
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
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: var(--bg-surface-2);
        border-bottom: 1px solid var(--border-color);
    }
    .product-storefront-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.35s ease;
    }
    .product-storefront-card:hover .product-storefront-img { transform: scale(1.05); }
    .product-storefront-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.6rem;
    }
    .product-storefront-placeholder__icon {
        width: 52px;
        height: 52px;
        border-radius: var(--radius-md);
        background: var(--accent-soft);
        border: 1px solid var(--border-color);
        color: var(--brand-cyan-fg);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }
    .product-storefront-placeholder__text {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
    }
    .product-storefront-badges {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        right: 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        pointer-events: none;
        z-index: 5;
    }
    .product-storefront-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    .product-storefront-badge--category {
        background: var(--bg-surface);
        color: var(--text-accent);
        border: 1px solid var(--border-color);
        max-width: 65%;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .product-storefront-badge--featured { background: var(--warning-color); color: var(--p-white); }
    .product-storefront-badge--oos {
        position: absolute;
        bottom: 0.75rem;
        left: 0.75rem;
        z-index: 5;
        background: var(--danger-color);
        color: var(--p-white);
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .product-storefront-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        justify-content: space-between;
        padding: 1.35rem;
    }
    .product-storefront-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--text-main);
        margin: 0 0 0.4rem;
        transition: color 0.2s ease;
    }
    .product-storefront-card:hover .product-storefront-title { color: var(--text-accent); }
    .product-storefront-desc {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-size: 0.9rem;
        color: var(--text-muted);
        line-height: 1.55;
        margin: 0 0 1.25rem;
    }
    .product-storefront-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.6rem;
        border-top: 1px solid var(--border-color);
        padding-top: 0.9rem;
    }
    .product-storefront-price-label {
        display: block;
        font-size: 0.72rem;
        color: var(--text-muted);
        font-weight: 500;
        margin-bottom: 2px;
    }
    .product-storefront-price-val {
        display: block;
        font-family: 'Outfit', sans-serif;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-accent);
    }
    .product-storefront-btn {
        padding: 0.6rem 1.2rem;
        font-size: 0.85rem;
        font-weight: 700;
        border-radius: var(--radius-sm);
        background: var(--cmyk-cyan);
        color: var(--p-white);
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }
    .product-storefront-card:hover .product-storefront-btn {
        background: var(--cmyk-cyan-hover);
        box-shadow: 0 4px 12px rgba(0, 153, 255, 0.35);
    }
    .product-storefront-btn--disabled {
        background: var(--bg-subtle);
        color: var(--text-muted);
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

    /* ==========================================================
       CLOSING CTA
       ========================================================== */
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
        font-size: clamp(1.6rem, 2.6vw, 2.25rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--text-main);
        margin: 0 0 0.75rem;
    }
    .cta-band__sub {
        color: var(--text-muted);
        font-size: 1rem;
        line-height: 1.65;
        margin: 0 0 2rem;
    }
    .cta-band__actions { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
    @media (max-width: 640px) {
        .cta-band { padding: 3rem 1.25rem; }
        .cta-band__actions { flex-direction: column; }
        .cta-band__actions a { width: 100%; justify-content: center; }
    }

    /* ==========================================================
       FLOATING SEARCH BUBBLE
       ========================================================== */
    .floating-search-bubble-wrap {
        position: fixed;
        right: 28px;
        top: 85px;
        z-index: 990;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }
    .bubble-search-tooltip {
        position: absolute;
        right: 68px;
        left: auto;
        background: var(--p-ink-900);
        color: var(--p-white);
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.38rem 0.75rem;
        border-radius: var(--radius-sm);
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        transform: translateX(8px);
        transition: opacity 0.2s ease, transform 0.2s ease;
        box-shadow: var(--shadow-md);
    }
    .bubble-search-tooltip .tooltip-arrow {
        position: absolute;
        right: -4px;
        left: auto;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-top: 4px solid transparent;
        border-bottom: 4px solid transparent;
        border-left: 4px solid var(--p-ink-900);
        border-right: none;
    }
    .floating-search-bubble-wrap:not(.is-expanded):hover .bubble-search-tooltip {
        opacity: 1;
        transform: translateX(0);
    }
    .floating-bubble-search {
        display: flex;
        align-items: center;
        width: 56px;
        height: 56px;
        background: var(--cmyk-cyan);
        border-radius: 56px;
        border: 2.5px solid var(--bg-surface);
        box-shadow: 0 8px 24px rgba(0, 153, 255, 0.4), 0 2px 8px rgba(15, 23, 42, 0.12);
        overflow: hidden;
        position: relative;
        transition: width 0.38s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }
    .floating-search-bubble-wrap:not(.is-expanded) .floating-bubble-search {
        animation: chatBubblePulse 3.2s infinite ease-in-out;
    }
    .floating-search-bubble-wrap:not(.is-expanded) .floating-bubble-search:hover {
        transform: scale(1.06) translateY(-2px);
        box-shadow: 0 12px 28px rgba(0, 153, 255, 0.5), 0 4px 12px rgba(15, 23, 42, 0.15);
    }
    .floating-search-bubble-wrap.is-expanded .floating-bubble-search {
        width: 420px;
        max-width: calc(100vw - 48px);
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
        width: 51px;
        height: 51px;
        border-radius: 50%;
        border: none;
        background: transparent;
        color: var(--p-white);
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        padding: 0;
        transition: color 0.25s ease, transform 0.25s ease;
    }
    .floating-search-bubble-wrap.is-expanded .bubble-toggle-btn {
        color: var(--brand-cyan-fg);
        cursor: default;
    }
    .bubble-input-wrap {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
        min-width: 0;
        opacity: 0;
        transform: translateX(-10px);
        pointer-events: none;
        transition: opacity 0.25s ease 0.08s, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) 0.08s;
        padding-right: 0.5rem;
    }
    .floating-search-bubble-wrap.is-expanded .bubble-input-wrap {
        opacity: 1;
        transform: translateX(0);
        pointer-events: auto;
    }
    .bubble-search-input {
        flex: 1;
        min-width: 0;
        border: none;
        outline: none;
        background: transparent;
        font-size: 0.95rem;
        color: var(--input-text);
        font-family: inherit;
        padding: 0.2rem 0.25rem;
    }
    .bubble-search-input::placeholder { color: var(--input-placeholder); font-size: 0.88rem; }
    .bubble-submit-btn {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: none;
        background: var(--cmyk-cyan);
        color: var(--p-white);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        cursor: pointer;
        flex-shrink: 0;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .bubble-submit-btn:hover { background: var(--cmyk-cyan-hover); transform: scale(1.08); }
    .bubble-close-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: none;
        background: var(--bg-subtle);
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        cursor: pointer;
        flex-shrink: 0;
        transition: background-color 0.2s ease, color 0.2s ease, transform 0.25s ease;
    }
    .bubble-close-btn:hover {
        background: var(--danger-soft);
        color: var(--danger-color);
        transform: rotate(90deg);
    }
    @media (max-width: 768px) {
        .floating-search-bubble-wrap { display: none !important; }
    }

    /* ==========================================================
       Reduced motion
       ========================================================== */
    @media (prefers-reduced-motion: reduce) {
        .hero-slide,
        .hero-slide.active {
            transition: opacity 0.01ms !important;
            transform: none !important;
        }
        .floating-search-bubble-wrap:not(.is-expanded) .floating-bubble-search {
            animation: none !important;
        }
        .carousel-dot,
        .service-card,
        .product-storefront-card,
        .value-card {
            transition: none !important;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>



<div class="floating-search-bubble-wrap" id="floatingSearchWidget">
    <div class="bubble-search-tooltip" id="bubbleSearchTooltip">
        <span>Search products</span>
        <div class="tooltip-arrow"></div>
    </div>

    <form action="<?php echo e(route('products.index')); ?>" method="GET" class="floating-bubble-search navbar-search-form" id="floatingBubbleForm">
        <button type="button" class="bubble-toggle-btn" id="bubbleToggleBtn" title="Search products" aria-label="Open product search">
            <i class="fa-solid fa-magnifying-glass bubble-icon-search"></i>
        </button>

        <div class="bubble-input-wrap">
            <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search products, services, prints..." class="bubble-search-input" id="bubbleSearchInput" autocomplete="off" aria-label="Search products, services, prints">
            <button type="submit" class="bubble-submit-btn" title="Search" aria-label="Submit search">
                <i class="fa-solid fa-arrow-right"></i>
            </button>
            <button type="button" class="bubble-close-btn" id="bubbleCloseBtn" title="Close search" aria-label="Close search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </form>
</div>


<?php if(isset($announcements) && $announcements->count() > 0): ?>
    <div class="landing-announcement">
        <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
        <div class="landing-announcement__text">
            <?php $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ann): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span><?php echo e($ann->message); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>


<?php
    $hasActiveBanners = isset($heroBanners) && $heroBanners->count() > 0;
?>

<?php if($hasActiveBanners): ?>
    <section class="hero-carousel" id="heroCarousel" aria-roledescription="carousel" aria-label="Grafika South Promotional Banner Slider">
        <?php if(Auth::check() && Auth::user()->isSuperAdmin()): ?>
            <a href="<?php echo e(route('super-admin.hero.edit')); ?>" class="btn btn-yellow super-admin-edit-btn" title="Super Admin Access Only - Manage Slider Banners">
                <i class="fa-solid fa-pen-to-square"></i> Manage Hero Slider
            </a>
        <?php endif; ?>

        <div class="hero-carousel-track" id="heroCarouselTrack">
            <?php $__currentLoopData = $heroBanners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $hasTitle = !empty(trim($banner->title ?? ''));
                    $hasLink = !empty(trim($banner->link_url ?? ''));
                ?>
                <div class="hero-slide <?php echo e($idx === 0 ? 'active' : ''); ?>"
                     data-index="<?php echo e($idx); ?>"
                     aria-roledescription="slide"
                     aria-label="Banner <?php echo e($idx + 1); ?> of <?php echo e($heroBanners->count()); ?><?php echo e($hasTitle ? ' - ' . $banner->title : ''); ?>">

                    <?php if($hasLink && !$hasTitle): ?>
                        <a href="<?php echo e($banner->link_url); ?>" class="hero-slide__link" aria-label="Go to banner promotion">
                            <img src="<?php echo e(asset('storage/' . $banner->image_path)); ?>"
                                 alt="<?php echo e($banner->title ?: 'Grafika South Promotional Banner ' . ($idx + 1)); ?>"
                                 loading="<?php echo e($idx === 0 ? 'eager' : 'lazy'); ?>">
                        </a>
                    <?php else: ?>
                        <img src="<?php echo e(asset('storage/' . $banner->image_path)); ?>"
                             alt="<?php echo e($banner->title ?: 'Grafika South Promotional Banner ' . ($idx + 1)); ?>"
                             loading="<?php echo e($idx === 0 ? 'eager' : 'lazy'); ?>">

                        <?php if($hasTitle || $hasLink): ?>
                            <div class="hero-slide-overlay" aria-hidden="true"></div>
                            <div class="hero-slide-content">
                                <?php if($hasTitle): ?>
                                    <h2 class="hero-slide-title"><?php echo e($banner->title); ?></h2>
                                <?php endif; ?>
                                <?php if($hasLink): ?>
                                    <a href="<?php echo e($banner->link_url); ?>" class="hero-slide-link">
                                        <span><?php echo e($hasTitle ? 'Learn More' : 'View Offer'); ?></span>
                                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if($heroBanners->count() > 1): ?>
            <button class="carousel-arrow prev" id="heroPrevBtn" type="button" aria-label="Previous promotional banner">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </button>
            <button class="carousel-arrow next" id="heroNextBtn" type="button" aria-label="Next promotional banner">
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>

            <div class="carousel-dots" id="heroDots" role="tablist">
                <?php $__currentLoopData = $heroBanners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button class="carousel-dot <?php echo e($idx === 0 ? 'active' : ''); ?>"
                            type="button"
                            data-slide="<?php echo e($idx); ?>"
                            role="tab"
                            aria-label="Go to banner <?php echo e($idx + 1); ?>"
                            aria-selected="<?php echo e($idx === 0 ? 'true' : 'false'); ?>"></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </section>
<?php else: ?>
    
    <section class="hero-fallback">
        <?php if(Auth::check() && Auth::user()->isSuperAdmin()): ?>
            <a href="<?php echo e(route('super-admin.hero.edit')); ?>" class="btn btn-yellow super-admin-edit-btn" title="Super Admin Access Only - Manage Slider Banners">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload Banners
            </a>
        <?php endif; ?>

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
                Custom prints, <span class="hero-accent">ordered online.</span>
            </h1>

            <p class="hero-fallback__sub">
                T-shirts, PVC ID cards, business cards and lamination &mdash; priced live as you
                configure, reviewed by our print team, and tracked from press to pickup.
            </p>

            <div class="hero-cta-row">
                <a href="<?php echo e(route('products.index')); ?>" class="hero-cta-primary">
                    <i class="fa-solid fa-shop" aria-hidden="true"></i>
                    Browse Catalog &amp; Order
                </a>
                <?php if(auth()->guard()->guest()): ?>
                    <a href="<?php echo e(route('register')); ?>" class="hero-cta-ghost">
                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                        Create Customer Account
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>


<section class="landing-section" aria-labelledby="services-heading">
    <div class="landing-container">
        <header class="section-head">
            <h2 class="section-title" id="services-heading">Services Offered</h2>
            <p class="section-sub">Professional printing services engineered with CMYK precision</p>
        </header>

        <div class="service-grid">
            <a class="service-card service-card--cyan" href="<?php echo e(route('products.index', ['category' => 'Apparel'])); ?>">
                <span class="service-card__icon" aria-hidden="true"><i class="fa-solid fa-shirt"></i></span>
                <h3 class="service-card__title">T-Shirt Printing</h3>
                <p class="service-card__desc">High quality sublimation &amp; heat transfer with size and color options.</p>
                <span class="service-card__price">From ₱250.00 <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </a>

            <a class="service-card service-card--magenta" href="<?php echo e(route('products.index', ['category' => 'Identification'])); ?>">
                <span class="service-card__icon" aria-hidden="true"><i class="fa-solid fa-id-card"></i></span>
                <h3 class="service-card__title">ID Printing</h3>
                <p class="service-card__desc">Durable 30mil PVC ID cards with single/double-sided print options.</p>
                <span class="service-card__price">From ₱50.00 <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </a>

            <a class="service-card service-card--yellow" href="<?php echo e(route('products.index', ['category' => 'Marketing'])); ?>">
                <span class="service-card__icon" aria-hidden="true"><i class="fa-solid fa-address-card"></i></span>
                <h3 class="service-card__title">Business Card Printing</h3>
                <p class="service-card__desc">Heavy 300gsm glossy or matte cardstock with premium corner cuts (Pack of 100).</p>
                <span class="service-card__price">From ₱350.00 / pack <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </a>

            <a class="service-card service-card--key" href="<?php echo e(route('products.index', ['category' => 'Services'])); ?>">
                <span class="service-card__icon" aria-hidden="true"><i class="fa-solid fa-file-contract"></i></span>
                <h3 class="service-card__title">Lamination Services</h3>
                <p class="service-card__desc">Waterproof sealed document thermal lamination (A4 &amp; A3 sizes, heavy pouches).</p>
                <span class="service-card__price">From ₱25.00 / sheet <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </a>
        </div>
    </div>
</section>


<section class="landing-section landing-section--band" aria-labelledby="how-heading">
    <div class="landing-container">
        <div class="hiw-head">
            <h2 class="hiw-title" id="how-heading">How <em>it</em> works</h2>
            <p class="hiw-sub">From design to delivery in three simple steps.</p>
        </div>

        <div class="hiw-grid">
            <article class="hiw-card">
                <div class="hiw-art">
                    <div class="hiw-shirt">
                        <img src="<?php echo e(asset('images/t-shirt.png')); ?>" alt="Custom t-shirt preview" loading="lazy">
                    </div>
                    <div class="hiw-picker">
                        <div class="hiw-picker__row">
                            <span class="hiw-picker__label">Size</span>
                            <span class="hiw-seg"><span>S</span><span class="is-on">M</span><span>L</span><span>XL</span></span>
                        </div>
                        <div class="hiw-picker__row">
                            <span class="hiw-picker__label">Color</span>
                            <span class="hiw-sw is-on" style="background:var(--accent-color)"><i class="fa-solid fa-check"></i></span>
                            <span class="hiw-sw" style="background:#94A3B8"></span>
                            <span class="hiw-sw" style="background:#1E293B"></span>
                            <span class="hiw-sw" style="background:#D6C7A8"></span>
                        </div>
                    </div>
                </div>
                <div class="hiw-card__body">
                    <span class="hiw-card__num">01</span>
                    <h3 class="hiw-card__title">Choose size &amp; color</h3>
                    <p class="hiw-card__desc">Pick your preferred size and color for your custom t-shirt.</p>
                    <span class="hiw-card__wm" aria-hidden="true">01</span>
                </div>
            </article>

            <div class="hiw-arrow" aria-hidden="true"><span><i class="fa-solid fa-arrow-right"></i></span></div>

            <article class="hiw-card">
                <div class="hiw-art">
                    <span class="hiw-cloud" aria-hidden="true"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                    <div class="hiw-drop" aria-hidden="true">
                        <div class="hiw-file">
                            <i class="fa-regular fa-file-lines"></i>
                            <span>artwork.pdf</span>
                            <span class="hiw-file__check"><i class="fa-solid fa-check"></i></span>
                        </div>
                    </div>
                </div>
                <div class="hiw-card__body">
                    <span class="hiw-card__num">02</span>
                    <h3 class="hiw-card__title">Upload artwork</h3>
                    <p class="hiw-card__desc">Send your design file with your order &mdash; our print team reviews it before press.</p>
                    <span class="hiw-card__wm" aria-hidden="true">02</span>
                </div>
            </article>

            <div class="hiw-arrow" aria-hidden="true"><span><i class="fa-solid fa-arrow-right"></i></span></div>

            <article class="hiw-card">
                <div class="hiw-art">
                    <div class="hiw-flow" aria-hidden="true">
                        <div class="hiw-timeline">
                            <div class="hiw-node"><span class="hiw-node__dot"><i class="fa-solid fa-check"></i></span><span class="hiw-node__lbl">Pending</span></div>
                            <div class="hiw-node"><span class="hiw-node__dot"><i class="fa-solid fa-check"></i></span><span class="hiw-node__lbl">Design</span></div>
                            <div class="hiw-node"><span class="hiw-node__dot"><i class="fa-solid fa-check"></i></span><span class="hiw-node__lbl">Approved</span></div>
                            <div class="hiw-node"><span class="hiw-node__dot"><i class="fa-solid fa-check"></i></span><span class="hiw-node__lbl">Printing</span></div>
                            <div class="hiw-node"><span class="hiw-node__dot"><i class="fa-solid fa-check"></i></span><span class="hiw-node__lbl">Ready</span></div>
                            <div class="hiw-node"><span class="hiw-node__dot">6</span><span class="hiw-node__lbl">Done</span></div>
                        </div>
                        <img class="hiw-pay-img" src="<?php echo e(asset('images/payment-row.png')); ?>" alt="Online payment: pay securely with your card, GCash, Maya, or other methods" loading="lazy">
                    </div>
                </div>
                <div class="hiw-card__body">
                    <span class="hiw-card__num">03</span>
                    <h3 class="hiw-card__title">Order &amp; track</h3>
                    <p class="hiw-card__desc">Pay securely online and follow your job from confirmation to delivery.</p>
                    <span class="hiw-card__wm" aria-hidden="true">03</span>
                </div>
            </article>
        </div>
    </div>
</section>


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
            <a href="<?php echo e(route('products.index')); ?>" class="btn btn-outline section-head__action">
                View Full Catalog <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </header>

        <div class="product-grid">
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $targetUrl = route('products.show', $product->slug ?: $product->id);
                ?>
                <a class="product-storefront-card" href="<?php echo e($targetUrl); ?>">
                    <span class="product-storefront-thumb">
                        <?php if($product->image_path): ?>
                            <img src="<?php echo e(asset('storage/' . $product->image_path)); ?>" alt="<?php echo e($product->name); ?>" class="product-storefront-img" loading="lazy">
                        <?php else: ?>
                            <span class="product-storefront-placeholder">
                                <span class="product-storefront-placeholder__icon" aria-hidden="true"><i class="fa-solid fa-print"></i></span>
                                <span class="product-storefront-placeholder__text">Grafika South Print</span>
                            </span>
                        <?php endif; ?>

                        <span class="product-storefront-badges">
                            <span class="product-storefront-badge product-storefront-badge--category"><?php echo e($product->category); ?></span>
                            <?php if($product->is_featured ?? false): ?>
                                <span class="product-storefront-badge product-storefront-badge--featured">★ Featured</span>
                            <?php endif; ?>
                        </span>

                        <?php if(!$product->has_sufficient_stock): ?>
                            <span class="product-storefront-badge product-storefront-badge--oos">
                                <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> Out of Stock
                            </span>
                        <?php endif; ?>
                    </span>

                    <span class="product-storefront-body">
                        <span class="product-storefront-title"><?php echo e($product->name); ?></span>
                        <span class="product-storefront-desc"><?php echo e($product->description ?: 'High quality custom print product with customizable options and fast turnaround.'); ?></span>

                        <span class="product-storefront-footer">
                            <span class="product-storefront-price">
                                <span class="product-storefront-price-label">Starting Price</span>
                                <span class="product-storefront-price-val">₱<?php echo e(number_format($product->price ?: $product->base_price, 2)); ?></span>
                            </span>
                            <?php if($product->has_sufficient_stock): ?>
                                <span class="product-storefront-btn btn btn-primary">Select Options</span>
                            <?php else: ?>
                                <span class="product-storefront-btn product-storefront-btn--disabled">Out of Stock</span>
                            <?php endif; ?>
                        </span>
                    </span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>


<section class="landing-section landing-section--cta" aria-labelledby="cta-heading">
    <div class="landing-container">
        <div class="cta-band">
            <h2 class="cta-band__title" id="cta-heading">Ready to put your design on press?</h2>
            <p class="cta-band__sub">Configure your print, upload your artwork, and we will take it from there.</p>
            <div class="cta-band__actions">
                <a href="<?php echo e(route('products.index')); ?>" class="hero-cta-primary">
                    <i class="fa-solid fa-shop" aria-hidden="true"></i> Browse the catalog
                </a>
                <a href="<?php echo e(route('orders.track')); ?>" class="hero-cta-ghost hero-cta-ghost--onlight">
                    <i class="fa-solid fa-magnifying-glass-location" aria-hidden="true"></i> Track your order
                </a>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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

    // Collapse back to bubble when clicking outside
    document.addEventListener('click', (e) => {
        if (widget.classList.contains('is-expanded') && !widget.contains(e.target)) {
            closeBubble();
        }
    });

    // Collapse back on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && widget.classList.contains('is-expanded')) {
            closeBubble();
        }
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\grafika-south\resources\views/welcome.blade.php ENDPATH**/ ?>