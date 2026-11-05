@extends('layouts.admin')

@section('title', 'Business Analytics & Sales — Grafika South')

@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════════════
   EXECUTIVE DESIGN SYSTEM — Grafika South "Sales & Operations"
   Brand: Cyan #0099FF · Magenta #EC008C · Yellow #EAB308 · Slate #0F172A
   ══════════════════════════════════════════════════════════════════════ */
.gs-analytics {
    --gs-cyan: #0099FF;
    --gs-cyan-hover: #0080FF;
    --gs-cyan-light: #E6F4FF;
    --gs-cyan-border: #BAE0FF;
    --gs-magenta: #EC008C;
    --gs-magenta-light: #FDE8F5;
    --gs-magenta-border: #FBCFE8;
    --gs-amber: #F59E0B;
    --gs-amber-light: #FEF3C7;
    --gs-amber-border: #FDE68A;
    --gs-emerald: #10B981;
    --gs-emerald-light: #DCFCE7;
    --gs-emerald-border: #BBF7D0;
    --gs-rose: #EF4444;
    --gs-rose-light: #FEE2E2;
    --gs-rose-border: #FECACA;
    --gs-violet: #8B5CF6;
    --gs-violet-light: #EDE9FE;
    --gs-violet-border: #DDD6FE;
    --gs-dark: #0F172A;
    --gs-dark-card: #1E293B;
    --gs-text: #1E293B;
    --gs-muted: #64748B;
    --gs-faint: #94A3B8;
    --gs-border: #E2E8F0;
    --gs-bg-soft: #F8FAFC;
    --gs-card-radius: 16px;
    --gs-shadow-card: 0 1px 3px rgba(15,23,42,0.03), 0 4px 12px rgba(15,23,42,0.03);
    --gs-shadow-hover: 0 10px 25px -5px rgba(15,23,42,0.08), 0 8px 10px -6px rgba(15,23,42,0.04);
}

/* ── Dark mode: flip the self-contained gs-* palette, and keep the "dark-slate"
   accent cards/button navy (they otherwise use --text-main, which turns light). ── */
html.theme-dark .gs-analytics {
    --gs-dark: #E6EDF7;
    --gs-text: #D7E0EC;
    --gs-muted: #94A3B8;
    --gs-faint: #64748B;
    --gs-border: #1E293B;
    --gs-bg-soft: #131D31;
    --gs-cyan-light: rgba(56, 189, 248, 0.14);
    --gs-cyan-border: rgba(56, 189, 248, 0.30);
    --gs-magenta-light: rgba(236, 0, 140, 0.16);
    --gs-magenta-border: rgba(236, 0, 140, 0.32);
    --gs-amber-light: rgba(245, 158, 11, 0.16);
    --gs-amber-border: rgba(245, 158, 11, 0.32);
    --gs-emerald-light: rgba(16, 185, 129, 0.16);
    --gs-emerald-border: rgba(16, 185, 129, 0.32);
    --gs-rose-light: rgba(239, 68, 68, 0.16);
    --gs-rose-border: rgba(239, 68, 68, 0.32);
    --gs-violet-light: rgba(139, 92, 246, 0.16);
    --gs-violet-border: rgba(139, 92, 246, 0.32);
}
html.theme-dark .gs-kpi-card.dark,
html.theme-dark .gs-btn-print-executive {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    color: #FFFFFF;
}
html.theme-dark .gs-btn-print-executive:hover {
    background: linear-gradient(135deg, #1E293B 0%, #334155 100%);
}
html.theme-dark .gs-kpi-card.dark .gs-kpi-value,
html.theme-dark .gs-print-text-title { color: #FFFFFF; }
html.theme-dark .gs-kpi-card.dark .gs-kpi-label,
html.theme-dark .gs-kpi-card.dark .gs-kpi-meta,
html.theme-dark .gs-print-text-sub { color: #94A3B8; }

/* ── Top Header Toolbar ── */
.gs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
    padding-bottom: 0.25rem;
}
.gs-title-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.gs-title-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.gs-title {
    font-family: 'Outfit', sans-serif;
    font-size: 1.85rem;
    font-weight: 800;
    color: var(--gs-dark);
    letter-spacing: -0.025em;
    line-height: 1.15;
    margin: 0;
}
.gs-live-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.65rem;
    background: var(--success-soft);
    border: 1px solid var(--success-border);
    color: var(--text-success);
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}
.gs-live-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--success-color);
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: gsPulse 2s infinite;
}
@keyframes gsPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
.gs-subtitle {
    color: var(--gs-muted);
    font-size: 0.88rem;
    margin: 0;
    line-height: 1.45;
}

/* ── Right-side action group (print) ── */
.gs-header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-left: auto;
}

/* ── Executive Print / Export Button (Dark-Slate) ── */
.gs-btn-print-executive {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 1.15rem 0.55rem 0.7rem;
    border: none;
    border-radius: 13px;
    background: linear-gradient(135deg, var(--text-main) 0%, var(--text-main) 100%);
    color: var(--text-inverse);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
    text-decoration: none;
    white-space: nowrap;
}
.gs-btn-print-executive:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.28);
    background: linear-gradient(135deg, var(--text-main) 0%, var(--text-muted) 100%);
}
.gs-btn-print-executive:active {
    transform: translateY(0);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
}
.gs-print-icon-wrap {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: rgba(56, 189, 248, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.gs-print-icon-wrap i {
    font-size: 0.85rem;
    color: var(--text-accent);
}
.gs-print-text {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}
.gs-print-text-title {
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.01em;
}
.gs-print-text-sub {
    font-size: 0.62rem;
    font-weight: 600;
    color: var(--text-subtle);
    letter-spacing: 0.02em;
}
.gs-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.48rem 0.85rem;
    border-radius: 11px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid var(--gs-border);
    background: var(--bg-surface);
    color: var(--gs-text);
    transition: all 0.16s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    text-decoration: none;
}
.gs-action-btn:hover {
    background: var(--gs-bg-soft);
    border-color: var(--border-color);
    transform: translateY(-1px);
}
.gs-action-btn:active {
    transform: translateY(0);
}
.gs-action-btn i {
    font-size: 0.82rem;
    color: var(--gs-muted);
}
.gs-action-btn.refreshing i {
    animation: gsSpin 0.8s linear infinite;
}

/* ── KPI Grid & Executive Cards ── */
.gs-kpi-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 0.9rem;
    margin-bottom: 1.25rem;
}
@media (max-width: 1499px) {
    .gs-kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.85rem; }
}
@media (max-width: 768px) {
    .gs-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.65rem; }
}
@media (max-width: 480px) {
    .gs-kpi-grid { grid-template-columns: minmax(0, 1fr); }
}

.gs-kpi-card {
    position: relative;
    background: var(--bg-surface);
    border: 1px solid var(--gs-border);
    border-radius: var(--gs-card-radius);
    padding: 1.05rem 1.15rem;
    box-shadow: var(--gs-shadow-card);
    transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
    cursor: pointer;
    text-align: left;
    width: 100%;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.gs-kpi-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--kpi-accent, var(--gs-cyan));
    opacity: 0.85;
    transition: height 0.2s ease, opacity 0.2s ease;
}
.gs-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--gs-shadow-hover);
    border-color: var(--border-color);
}
.gs-kpi-card:hover::after {
    height: 4px;
    opacity: 1;
}
.gs-kpi-card.is-active-tab {
    border-color: var(--kpi-accent, var(--gs-cyan));
    background: linear-gradient(180deg, var(--bg-surface) 0%, var(--kpi-bg, var(--bg-page)) 100%);
    box-shadow: 0 0 0 2px rgba(0, 153, 255, 0.15), var(--gs-shadow-card);
}
.gs-kpi-card.dark {
    background: linear-gradient(135deg, var(--text-main) 0%, var(--text-main) 100%);
    border-color: var(--border-strong);
    color: var(--text-inverse);
}
.gs-kpi-card.dark::after {
    background: linear-gradient(90deg, var(--accent-color), var(--brand-magenta-fg));
    height: 3px;
}
.gs-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.55rem;
}
.gs-kpi-icon-pill {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    background: var(--kpi-bg, var(--gs-cyan-light));
    color: var(--kpi-accent, var(--gs-cyan));
    flex-shrink: 0;
}
.gs-kpi-card.dark .gs-kpi-icon-pill {
    background: rgba(255, 255, 255, 0.12);
    color: var(--text-accent);
}
.gs-kpi-badge {
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
    background: var(--gs-bg-soft);
    color: var(--gs-muted);
}
.gs-kpi-card.dark .gs-kpi-badge {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-subtle);
}
.gs-kpi-label {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gs-muted);
    margin-bottom: 0.25rem;
    display: block;
}
.gs-kpi-card.dark .gs-kpi-label {
    color: var(--text-subtle);
}
.gs-kpi-value {
    font-family: 'Outfit', sans-serif;
    font-size: clamp(1.25rem, 1.35vw, 1.55rem);
    font-weight: 800;
    color: var(--gs-dark);
    line-height: 1.15;
    letter-spacing: -0.025em;
    overflow: hidden;
    text-overflow: ellipsis;
    font-variant-numeric: tabular-nums;
}
.gs-val-cents {
    font-size: 0.75em;
    opacity: 0.85;
}
.gs-kpi-card.dark .gs-kpi-value {
    color: var(--text-inverse);
}
.gs-kpi-meta {
    font-size: 0.72rem;
    color: var(--gs-faint);
    font-weight: 600;
    margin-top: 0.35rem;
    line-height: 1.35;
    min-height: 2.2em;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.gs-kpi-card.dark .gs-kpi-meta {
    color: var(--text-subtle);
}

/* ── Navigation Tabs ── */
.gs-nav {
    display: flex;
    gap: 0.35rem;
    overflow-x: auto;
    padding: 0.35rem;
    background: var(--bg-surface);
    border: 1px solid var(--gs-border);
    border-radius: 14px;
    margin-bottom: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    scrollbar-width: thin;
    -webkit-overflow-scrolling: touch;
}
.gs-nav-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    background: transparent;
    font-family: inherit;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--gs-muted);
    padding: 0.55rem 0.95rem;
    border-radius: 10px;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.16s ease;
}
.gs-nav-btn i {
    font-size: 0.85rem;
}
.gs-nav-btn:hover {
    background: var(--gs-bg-soft);
    color: var(--gs-dark);
}
.gs-nav-btn.is-active {
    background: var(--gs-cyan-light);
    color: var(--gs-cyan-hover);
    box-shadow: 0 1px 2px rgba(0, 153, 255, 0.08);
}
.gs-nav-btn.is-active::after {
    content: '';
    position: absolute;
    bottom: -0.35rem;
    left: 50%;
    transform: translateX(-50%);
    width: 22px;
    height: 3px;
    border-radius: 99px;
    background: var(--gs-cyan);
}
.gs-nav-badge {
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    font-size: 0.63rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--gs-bg-soft);
    color: var(--gs-muted);
}
.gs-nav-badge.hot { background: var(--gs-magenta-light); color: var(--gs-magenta); }
.gs-nav-badge.warn { background: var(--gs-amber-light); color: var(--text-warning); }
.gs-nav-badge.red { background: var(--gs-rose-light); color: var(--gs-rose); }

/* ── Module Container & Fade ── */
.gs-module {
    animation: gsModuleFade 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.gs-module[hidden] {
    display: none !important;
}
@keyframes gsModuleFade {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ── Proportional Layout Cards ── */
.gs-card {
    background: var(--bg-surface);
    border: 1px solid var(--gs-border);
    border-radius: var(--gs-card-radius);
    padding: 1.25rem;
    box-shadow: var(--gs-shadow-card);
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: border-color 0.15s ease;
}
.gs-card:hover {
    border-color: var(--border-color);
}
.gs-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1.1rem;
    padding-bottom: 0.7rem;
    border-bottom: 1px solid var(--border-color);
    flex-wrap: wrap;
}
.gs-card-title {
    font-size: 0.98rem;
    font-weight: 800;
    color: var(--gs-dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}
.gs-card-title i {
    font-size: 0.95rem;
    color: var(--gs-cyan);
}
.gs-card-hint {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--gs-faint);
}

/* ── Proportional Grids ── */
.gs-grid-split {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr);
    gap: 1.15rem;
    margin-bottom: 1.15rem;
}
.gs-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.15rem;
    margin-bottom: 1.15rem;
}
.gs-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.15rem;
    margin-bottom: 1.15rem;
}
@media (max-width: 1100px) {
    .gs-grid-split, .gs-grid-2, .gs-grid-3 {
        grid-template-columns: minmax(0, 1fr);
    }
}

/* ── Chart Container with Proportions ── */
.gs-chart-box {
    position: relative;
    width: 100%;
    flex: 1;
    min-height: 240px;
}
.gs-chart-spinner {
    position: absolute;
    inset: 0;
    background: color-mix(in srgb, var(--bg-surface) 85%, transparent);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    z-index: 10;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.18s ease;
}
.gs-chart-spinner.is-visible {
    opacity: 1;
    pointer-events: auto;
}
.gs-chart-spinner i {
    font-size: 1.6rem;
    color: var(--gs-cyan);
    animation: gsSpin 0.8s linear infinite;
}
@keyframes gsSpin {
    to { transform: rotate(360deg); }
}

/* ── Chart Donut with Center Stats ── */
.gs-donut-layout {
    display: grid;
    grid-template-columns: 210px minmax(0, 1fr);
    gap: 1.25rem;
    align-items: center;
}
@media (max-width: 640px) {
    .gs-donut-layout { grid-template-columns: minmax(0, 1fr); }
}
.gs-donut-wrap {
    position: relative;
    width: 100%;
    height: 195px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.gs-donut-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
}
.gs-donut-center-val {
    font-family: 'Outfit', sans-serif;
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--gs-dark);
    line-height: 1.1;
}
.gs-donut-center-lbl {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--gs-muted);
    letter-spacing: 0.04em;
}

/* ── Interactive Drilldown Drawer ── */
.gs-drilldown-box {
    margin-top: 1rem;
    padding-top: 0.9rem;
    border-top: 1px dashed var(--gs-border);
    display: none;
    animation: gsModuleFade 0.2s ease;
}
.gs-drilldown-box.is-open {
    display: block;
}
.gs-drilldown-tag {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gs-muted);
    margin-bottom: 0.45rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* ── Needs Action Center Items ── */
.gs-action-item {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.75rem 0.9rem;
    border: 1px solid var(--gs-border);
    border-radius: 12px;
    background: var(--bg-surface);
    text-decoration: none;
    transition: all 0.16s ease;
    margin-bottom: 0.55rem;
}
.gs-action-item:last-child {
    margin-bottom: 0;
}
.gs-action-item:hover {
    border-color: var(--item-accent, var(--gs-cyan));
    background: var(--item-bg, var(--gs-cyan-light));
    transform: translateX(3px);
}
.gs-action-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    background: var(--item-bg, var(--gs-cyan-light));
    color: var(--item-accent, var(--gs-cyan));
    flex-shrink: 0;
}
.gs-action-info {
    flex: 1;
    min-width: 0;
}
.gs-action-text {
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--gs-dark);
    line-height: 1.3;
}
.gs-action-sub {
    font-size: 0.71rem;
    color: var(--gs-faint);
    font-weight: 600;
    margin-top: 0.1rem;
}
.gs-action-badge {
    font-family: 'Outfit', sans-serif;
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--item-accent, var(--gs-cyan));
}

/* ── Interactive Order Pipeline Stages ── */
.gs-pipeline {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.gs-pipeline-item {
    display: grid;
    grid-template-columns: 120px minmax(0, 1fr) 42px;
    align-items: center;
    gap: 0.75rem;
    padding: 0.45rem 0.6rem;
    border-radius: 9px;
    text-decoration: none;
    color: inherit;
    transition: background 0.15s ease;
}
.gs-pipeline-item:hover {
    background: var(--gs-bg-soft);
}
.gs-pipeline-lbl {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--gs-text);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.gs-pipeline-num {
    font-family: 'Outfit', sans-serif;
    font-size: 0.92rem;
    font-weight: 800;
    text-align: right;
    color: var(--gs-dark);
}
.gs-bar-track {
    background: var(--bg-subtle);
    border-radius: 999px;
    height: 8px;
    overflow: hidden;
    position: relative;
}
.gs-bar-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ── Stat Mini Metric Cards ── */
.gs-stat-card {
    border: 1px solid var(--gs-border);
    border-radius: 14px;
    padding: 0.95rem 1.1rem;
    background: var(--bg-surface);
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}
.gs-stat-lbl {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gs-muted);
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.gs-stat-val {
    font-family: 'Outfit', sans-serif;
    font-size: 1.45rem;
    font-weight: 800;
    color: var(--gs-dark);
    line-height: 1.15;
}
.gs-stat-sub {
    font-size: 0.72rem;
    color: var(--gs-faint);
    font-weight: 600;
}

/* ── Modern Tables ── */
.gs-table-wrap {
    overflow-x: auto;
    width: 100%;
}
.gs-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.gs-table thead th {
    background: var(--bg-page);
    color: var(--gs-muted);
    font-size: 0.67rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 800;
    padding: 0.65rem 0.8rem;
    text-align: left;
    white-space: nowrap;
    border-bottom: 1px solid var(--gs-border);
}
.gs-table tbody td {
    padding: 0.65rem 0.8rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}
.gs-table tbody tr {
    transition: background 0.12s;
}
.gs-table tbody tr:hover {
    background: var(--bg-page);
}
.gs-table tbody tr:last-child td {
    border-bottom: none;
}
.gs-table-search {
    position: relative;
    max-width: 220px;
}
.gs-table-search input {
    width: 100%;
    padding: 0.35rem 0.65rem 0.35rem 1.85rem;
    font-size: 0.76rem;
    border: 1px solid var(--gs-border);
    border-radius: 8px;
    outline: none;
    font-family: inherit;
    transition: border-color 0.15s;
}
.gs-table-search input:focus {
    border-color: var(--gs-cyan);
}
.gs-table-search i {
    position: absolute;
    left: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.72rem;
    color: var(--gs-faint);
}

/* ── Badges & Pills ── */
.gs-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 800;
    white-space: nowrap;
}
.gs-pill.cyan    { background: var(--gs-cyan-light); color: var(--gs-cyan-hover); }
.gs-pill.magenta { background: var(--gs-magenta-light); color: var(--text-magenta); }
.gs-pill.emerald { background: var(--gs-emerald-light); color: var(--text-success); }
.gs-pill.amber   { background: var(--gs-amber-light); color: var(--text-warning); }
.gs-pill.rose    { background: var(--gs-rose-light); color: var(--text-danger); }
.gs-pill.violet  { background: var(--gs-violet-light); color: var(--text-violet); }
.gs-pill.gray    { background: var(--gs-bg-soft); color: var(--text-muted); }

.gs-rank-medal {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 800;
}
.gs-rank-medal.gold { background: var(--warning-soft); color: var(--text-warning); }
.gs-rank-medal.silver { background: var(--bg-subtle); color: var(--text-muted); }
.gs-rank-medal.bronze { background: var(--warning-soft); color: var(--text-warning); }
.gs-rank-medal.default { background: transparent; color: var(--gs-muted); }

/* ── Alert Banner ── */
.gs-alert-banner {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.85rem 1.15rem;
    border-radius: 14px;
    margin-bottom: 1.15rem;
    background: var(--gs-rose-light);
    border: 1px solid var(--gs-rose-border);
}
.gs-alert-banner.warn {
    background: var(--gs-amber-light);
    border-color: var(--gs-amber-border);
}
.gs-alert-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
    background: color-mix(in srgb, var(--bg-surface) 75%, transparent);
    color: var(--gs-rose);
}
.gs-alert-banner.warn .gs-alert-icon {
    color: var(--gs-amber);
}
.gs-alert-content {
    flex: 1;
    min-width: 0;
}
.gs-alert-title {
    font-weight: 800;
    font-size: 0.88rem;
    color: var(--gs-dark);
}
.gs-alert-desc {
    font-size: 0.78rem;
    color: var(--gs-muted);
    margin-top: 0.1rem;
}

/* ── Empty States ── */
.gs-empty-state {
    text-align: center;
    padding: 2.25rem 1rem;
    color: var(--gs-muted);
    font-size: 0.84rem;
}
.gs-empty-state i {
    display: block;
    font-size: 1.85rem;
    margin-bottom: 0.65rem;
    color: var(--gs-faint);
}

/* ── Micro Segmented Tabs Inside Cards ── */
.gs-micro-tabs {
    display: inline-flex;
    background: var(--bg-subtle);
    border-radius: 9px;
    padding: 3px;
    gap: 0.15rem;
}
.gs-micro-tabs button {
    border: none;
    background: transparent;
    font-family: inherit;
    font-size: 0.73rem;
    font-weight: 700;
    color: var(--gs-muted);
    padding: 0.3rem 0.65rem;
    border-radius: 7px;
    cursor: pointer;
    transition: all 0.15s;
}
.gs-micro-tabs button:hover:not(.is-active) {
    color: var(--gs-dark);
    background: color-mix(in srgb, var(--bg-surface) 60%, transparent);
}
.gs-micro-tabs button.is-active {
    background: var(--bg-surface);
    color: var(--gs-cyan-hover);
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

/* ── Print Specific Styles ── */
@media print {
    .admin-topbar, .admin-sidebar, .gs-nav, .gs-table-search, .gs-action-btn, .gs-btn-print-executive {
        display: none !important;
    }
    .gs-card {
        border: 1px solid var(--border-strong);
        box-shadow: none !important;
        break-inside: avoid;
    }
}
</style>
@endpush

@section('content')
<div class="gs-analytics" id="analyticsRoot">

    {{-- ══════════════════════════════════════════════════════════════════
         TOP ACTION & CONTROL BAR
         ══════════════════════════════════════════════════════════════════ --}}
    <header class="gs-header">
        <div class="gs-title-wrap">
            <div class="gs-title-row">
                <h1 class="gs-title">Business Analytics &amp; Sales</h1>
                <span class="gs-live-badge">
                    <span class="gs-live-dot"></span> Live Store Intelligence
                </span>
            </div>
            <p class="gs-subtitle">
                Real-time revenue metrics, order velocity, payment settlement health, and supply intelligence.
            </p>
        </div>

        <div class="gs-header-right">
            {{-- Print / Export Button (Executive Dark-Slate) --}}
            <button type="button" class="gs-btn-print-executive" onclick="window.print();" title="Print or save executive PDF report">
                <span class="gs-print-icon-wrap">
                    <i class="fa-solid fa-print"></i>
                </span>
                <span class="gs-print-text">
                    <span class="gs-print-text-title">Print Report</span>
                    <span class="gs-print-text-sub">Export PDF / Paper</span>
                </span>
            </button>
        </div>
    </header>

    {{-- ══════════════════════════════════════════════════════════════════
         EXECUTIVE KPI METRIC CARDS STRIP (6 PROPORTIONAL TILES)
         ══════════════════════════════════════════════════════════════════ --}}
    <section class="gs-kpi-grid" id="kpiStrip" aria-label="Key Performance Indicators">

        {{-- 1. Total Revenue --}}
        <button type="button" class="gs-kpi-card dark is-active-tab" data-target-module="revenue" style="--kpi-accent:#0099FF; --kpi-bg:#E6F4FF;" title="Click to view Revenue & Sales details">
            <div>
                <div class="gs-kpi-top">
                    <span class="gs-kpi-icon-pill"><i class="fa-solid fa-peso-sign"></i></span>
                    <span class="gs-kpi-badge"><i class="fa-solid fa-arrow-trend-up"></i> Sales</span>
                </div>
                <span class="gs-kpi-label">Total Revenue</span>
                <div class="gs-kpi-value" id="val-revenue">₱{{ number_format(floor($totalRevenue), 0) }}<span class="gs-val-cents">.{{ str_pad(($totalRevenue * 100) % 100, 2, '0', STR_PAD_LEFT) }}</span></div>
            </div>
            <div class="gs-kpi-meta" id="val-revenue-sub">
                <span id="val-orders-count">{{ $scopedOrderCount }}</span> orders · avg ₱<span id="val-avg-order">{{ number_format($averageOrderValue, 0) }}</span>
            </div>
        </button>

        {{-- 2. Awaiting Verification --}}
        <button type="button" class="gs-kpi-card" data-target-module="collections" style="--kpi-accent:#EC008C; --kpi-bg:#FDE8F5;" title="Click to review pending payment proofs">
            <div>
                <div class="gs-kpi-top">
                    <span class="gs-kpi-icon-pill"><i class="fa-solid fa-hourglass-half"></i></span>
                    <span class="gs-kpi-badge" style="color:var(--gs-magenta);"><i class="fa-solid fa-clock"></i> Pending</span>
                </div>
                <span class="gs-kpi-label">Awaiting Verification</span>
                <div class="gs-kpi-value" style="color:var(--gs-magenta);" id="val-pending">₱{{ number_format(floor($pendingVerificationTotal), 0) }}<span class="gs-val-cents">.{{ str_pad(($pendingVerificationTotal * 100) % 100, 2, '0', STR_PAD_LEFT) }}</span></div>
            </div>
            <div class="gs-kpi-meta" id="val-pending-sub">
                <span id="val-proofs-count">{{ $pendingVerificationCount }}</span> payment {{ $pendingVerificationCount === 1 ? 'proof' : 'proofs' }}
            </div>
        </button>

        {{-- 3. Collected (Verified) --}}
        <button type="button" class="gs-kpi-card" data-target-module="collections" style="--kpi-accent:#10B981; --kpi-bg:#DCFCE7;" title="Click to view Collections details">
            <div>
                <div class="gs-kpi-top">
                    <span class="gs-kpi-icon-pill"><i class="fa-solid fa-circle-check"></i></span>
                    <span class="gs-kpi-badge" style="color:var(--text-success);"><i class="fa-solid fa-shield-check"></i> Settled</span>
                </div>
                <span class="gs-kpi-label">Collected (Verified)</span>
                <div class="gs-kpi-value" style="color:var(--text-success);" id="val-collected">₱{{ number_format(floor($totalCollected), 0) }}<span class="gs-val-cents">.{{ str_pad(($totalCollected * 100) % 100, 2, '0', STR_PAD_LEFT) }}</span></div>
            </div>
            <div class="gs-kpi-meta" id="val-collected-sub">
                ₱<span id="val-collected-month">{{ number_format($collectedThisMonth, 2) }}</span> this month
            </div>
        </button>

        {{-- 4. Uncollected Balance --}}
        <button type="button" class="gs-kpi-card" data-target-module="collections" style="--kpi-accent:#F59E0B; --kpi-bg:#FEF3C7;" title="Click to view outstanding downpayments & pending proofs">
            <div>
                <div class="gs-kpi-top">
                    <span class="gs-kpi-icon-pill"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                    <span class="gs-kpi-badge" style="color:var(--text-warning);"><i class="fa-solid fa-wallet"></i> Receivable</span>
                </div>
                <span class="gs-kpi-label">Uncollected Total</span>
                <div class="gs-kpi-value" style="color:var(--text-warning);" id="val-uncollected">₱{{ number_format(floor($uncollectedTotal), 0) }}<span class="gs-val-cents">.{{ str_pad(($uncollectedTotal * 100) % 100, 2, '0', STR_PAD_LEFT) }}</span></div>
            </div>
            <div class="gs-kpi-meta">
                Bal: ₱{{ number_format($pendingDownpaymentBalance, 0) }} · Proofs: ₱{{ number_format($pendingVerificationTotal, 0) }}
            </div>
        </button>

        {{-- 5. Total Orders --}}
        <button type="button" class="gs-kpi-card" data-target-module="operations" style="--kpi-accent:#8B5CF6; --kpi-bg:#EDE9FE;" title="Click to view Operations & Pipeline">
            <div>
                <div class="gs-kpi-top">
                    <span class="gs-kpi-icon-pill"><i class="fa-solid fa-box-open"></i></span>
                    <span class="gs-kpi-badge" style="color:var(--text-violet);"><i class="fa-solid fa-dolly"></i> Velocity</span>
                </div>
                <span class="gs-kpi-label">Total Store Orders</span>
                <div class="gs-kpi-value" style="color:var(--text-violet);" id="val-orders">{{ $totalOrdersCount }}</div>
            </div>
            <div class="gs-kpi-meta" id="val-orders-sub">
                <span id="val-inprogress-count">{{ $inProgressOrdersCount }}</span> in progress · <span id="val-pending-orders-count">{{ $pendingOrdersCount }}</span> pending
            </div>
        </button>

        {{-- 6. Needs Action --}}
        <button type="button" class="gs-kpi-card" data-target-module="overview" style="--kpi-accent:#EF4444; --kpi-bg:#FEE2E2;" title="Click to jump to Action Center">
            <div>
                <div class="gs-kpi-top">
                    <span class="gs-kpi-icon-pill"><i class="fa-solid fa-bolt"></i></span>
                    <span class="gs-kpi-badge" style="color:var(--text-danger);"><i class="fa-solid fa-triangle-exclamation"></i> Tasks</span>
                </div>
                <span class="gs-kpi-label">Needs Action</span>
                <div class="gs-kpi-value" style="color:var(--text-danger);" id="val-action">{{ $actionItems->count() }}</div>
            </div>
            <div class="gs-kpi-meta">
                {{ $actionItems->count() === 1 ? '1 queue requires action' : $actionItems->count() . ' queues waiting on staff' }}
            </div>
        </button>
    </section>

    {{-- ══════════════════════════════════════════════════════════════════
         SEGMENTED MODULE NAVIGATION TABS
         ══════════════════════════════════════════════════════════════════ --}}
    <nav class="gs-nav" id="moduleNav" aria-label="Analytics Sections">
        <button type="button" class="gs-nav-btn is-active" data-module="overview">
            <i class="fa-solid fa-gauge-high"></i> Overview
        </button>
        <button type="button" class="gs-nav-btn" data-module="revenue">
            <i class="fa-solid fa-chart-line"></i> Revenue &amp; Sales
        </button>
        <button type="button" class="gs-nav-btn" data-module="collections">
            <i class="fa-solid fa-hand-holding-dollar"></i> Collections
            @if($pendingVerificationCount > 0)
                <span class="gs-nav-badge hot">{{ $pendingVerificationCount }}</span>
            @endif
        </button>
        <button type="button" class="gs-nav-btn" data-module="operations">
            <i class="fa-solid fa-diagram-project"></i> Operations
            @if($pendingOrdersCount > 0)
                <span class="gs-nav-badge warn">{{ $pendingOrdersCount }}</span>
            @endif
        </button>
        <button type="button" class="gs-nav-btn" data-module="inventory">
            <i class="fa-solid fa-warehouse"></i> Inventory
            @if($lowStockCount > 0)
                <span class="gs-nav-badge red">{{ $lowStockCount }}</span>
            @endif
        </button>
        <button type="button" class="gs-nav-btn" data-module="suppliers">
            <i class="fa-solid fa-truck-field"></i> Suppliers
            @if($payablesAging->where('bucket', 'overdue')->count() > 0)
                <span class="gs-nav-badge warn">{{ $payablesAging->where('bucket', 'overdue')->count() }}</span>
            @endif
        </button>
    </nav>

    {{-- ══════════════════════════════════════════════════════════════════
         MODULE 1: OVERVIEW
         ══════════════════════════════════════════════════════════════════ --}}
    <section class="gs-module" data-module-panel="overview">

        {{-- Row 1: Revenue & Sales Trends + Needs Action Center --}}
        <div class="gs-grid-split">

            {{-- Proportional Revenue Trend Chart --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-chart-line"></i> Revenue &amp; Sales Velocity
                        </h2>
                        <span class="gs-card-hint">Click any timeline point for detailed breakdown</span>
                    </div>

                    {{-- Period Granularity Switcher --}}
                    <div class="gs-micro-tabs" id="trendPeriodTabs">
                        <button type="button" data-period="daily">Daily</button>
                        <button type="button" data-period="weekly">Weekly</button>
                        <button type="button" data-period="monthly" class="is-active">Monthly</button>
                        <button type="button" data-period="yearly">Yearly</button>
                    </div>
                </div>

                {{-- Chart Canvas Box --}}
                <div class="gs-chart-box" style="height:270px;">
                    <canvas id="trendChart"></canvas>
                    <div class="gs-chart-spinner" id="trendSpinner">
                        <i class="fa-solid fa-circle-notch"></i>
                    </div>
                </div>

                {{-- Interactive Drilldown Reveal --}}
                <div class="gs-drilldown-box" id="trendDrillBox">
                    <div class="gs-drilldown-tag">
                        <span>Period Breakdown</span>
                        <button type="button" class="gs-action-btn" style="padding:0.2rem 0.5rem; font-size:0.7rem;" onclick="document.getElementById('trendDrillBox').classList.remove('is-open');">
                            Close
                        </button>
                    </div>
                    <div id="trendDrillContent"></div>
                </div>
            </div>

            {{-- Executive Action Center --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-bolt" style="color:var(--gs-magenta);"></i> Needs Action
                        </h2>
                        <span class="gs-card-hint">{{ $actionItems->count() }} active queues</span>
                    </div>
                    <a href="{{ route('super-admin.orders.index') }}" class="gs-action-btn" style="padding:0.35rem 0.65rem; font-size:0.73rem;">
                        View Orders <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div style="flex:1; display:flex; flex-direction:column; justify-content:center;">
                    @forelse($actionItems as $item)
                        @php
                            $toneConfigs = [
                                'magenta' => ['accent' => '#EC008C', 'bg' => '#FDE8F5'],
                                'amber'   => ['accent' => '#D97706', 'bg' => '#FEF3C7'],
                                'cyan'    => ['accent' => '#0099FF', 'bg' => '#E6F4FF'],
                                'red'     => ['accent' => '#DC2626', 'bg' => '#FEE2E2'],
                                'violet'  => ['accent' => '#7C3AED', 'bg' => '#EDE9FE'],
                            ];
                            $cfg = $toneConfigs[$item->tone] ?? ['accent' => '#64748B', 'bg' => '#F1F5F9'];
                        @endphp
                        <a href="{{ $item->url }}" class="gs-action-item" style="--item-accent:{{ $cfg['accent'] }}; --item-bg:{{ $cfg['bg'] }};">
                            <span class="gs-action-icon"><i class="fa-solid {{ $item->icon }}"></i></span>
                            <div class="gs-action-info">
                                <div class="gs-action-text">{{ $item->label }}</div>
                                <div class="gs-action-sub">{{ $item->meta }}</div>
                            </div>
                            <span class="gs-action-badge">{{ $item->count }}</span>
                        </a>
                    @empty
                        <div class="gs-empty-state">
                            <i class="fa-solid fa-circle-check" style="color:var(--gs-emerald);"></i>
                            <strong>All Caught Up!</strong>
                            <p style="margin:0.25rem 0 0; font-size:0.75rem; color:var(--gs-muted);">No urgent queues require your immediate attention.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Row 2: Cash Position (Proportional Donut + Breakdown) & Order Pipeline --}}
        <div class="gs-grid-2">

            {{-- Cash Position Card --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-wallet"></i> Cash Position &amp; Liquidity
                        </h2>
                        <span class="gs-card-hint">Settlement distribution of all tracked funds</span>
                    </div>
                    <button type="button" class="gs-action-btn" style="padding:0.3rem 0.6rem; font-size:0.72rem;" onclick="activateModule('collections');">
                        View Collections →
                    </button>
                </div>

                <div class="gs-donut-layout">
                    {{-- Proportional Donut with Center Metric --}}
                    <div class="gs-donut-wrap">
                        <canvas id="cashDonutOverview"></canvas>
                        <div class="gs-donut-center">
                            <div class="gs-donut-center-val" id="cashDonutCenterVal">₱{{ number_format(($totalCollected + $uncollectedTotal)/1000, 1) }}k</div>
                            <div class="gs-donut-center-lbl">Total Funds</div>
                        </div>
                    </div>

                    {{-- Breakdown Bars --}}
                    <div style="display:flex; flex-direction:column; gap:0.75rem;">
                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.78rem; font-weight:700; margin-bottom:0.25rem;">
                                <span style="display:flex; align-items:center; gap:0.35rem;">
                                    <span style="width:8px; height:8px; border-radius:50%; background:var(--gs-cyan);"></span>
                                    Verified Collected
                                </span>
                                <span>₱{{ number_format($totalCollected, 2) }}</span>
                            </div>
                            <div class="gs-bar-track">
                                <div class="gs-bar-fill" style="background:var(--gs-cyan); width:{{ ($totalCollected + $uncollectedTotal) > 0 ? max(3, round($totalCollected / ($totalCollected + $uncollectedTotal) * 100)) : 0 }}%;"></div>
                            </div>
                        </div>

                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.78rem; font-weight:700; margin-bottom:0.25rem;">
                                <span style="display:flex; align-items:center; gap:0.35rem;">
                                    <span style="width:8px; height:8px; border-radius:50%; background:var(--warning-color);"></span>
                                    Downpayment Balances
                                </span>
                                <span>₱{{ number_format($pendingDownpaymentBalance, 2) }}</span>
                            </div>
                            <div class="gs-bar-track">
                                <div class="gs-bar-fill" style="background:var(--warning-color); width:{{ ($totalCollected + $uncollectedTotal) > 0 ? max(3, round($pendingDownpaymentBalance / ($totalCollected + $uncollectedTotal) * 100)) : 0 }}%;"></div>
                            </div>
                        </div>

                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.78rem; font-weight:700; margin-bottom:0.25rem;">
                                <span style="display:flex; align-items:center; gap:0.35rem;">
                                    <span style="width:8px; height:8px; border-radius:50%; background:var(--danger-color);"></span>
                                    Awaiting Verification
                                </span>
                                <span>₱{{ number_format($pendingVerificationTotal, 2) }}</span>
                            </div>
                            <div class="gs-bar-track">
                                <div class="gs-bar-fill" style="background:var(--danger-color); width:{{ ($totalCollected + $uncollectedTotal) > 0 ? max(3, round($pendingVerificationTotal / ($totalCollected + $uncollectedTotal) * 100)) : 0 }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Interactive Order Pipeline Card --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-diagram-project"></i> Order Pipeline &amp; Fulfillment
                        </h2>
                        <span class="gs-card-hint">Click any stage to inspect filtered orders</span>
                    </div>
                    <span class="gs-pill gray">{{ $totalOrdersCount }} Total</span>
                </div>

                <div class="gs-pipeline">
                    @php
                        $pipeStages = [
                            ['status' => 'pending',        'label' => 'Pending',       'color' => '#EAB308', 'icon' => 'fa-clock'],
                            ['status' => 'design_review', 'label' => 'Design Review', 'color' => '#8B5CF6', 'icon' => 'fa-pen-ruler'],
                            ['status' => 'approved',      'label' => 'Approved',      'color' => '#0099FF', 'icon' => 'fa-check'],
                            ['status' => 'printing',      'label' => 'Printing',      'color' => '#EC008C', 'icon' => 'fa-print'],
                            ['status' => 'ready',         'label' => 'Ready for Pickup', 'color' => '#06B6D4', 'icon' => 'fa-box-circle-check'],
                            ['status' => 'shipped',       'label' => 'Shipped / In Transit', 'color' => '#6366F1', 'icon' => 'fa-truck-fast'],
                            ['status' => 'completed',     'label' => 'Completed',     'color' => '#10B981', 'icon' => 'fa-circle-check'],
                            ['status' => 'cancelled',     'label' => 'Cancelled',     'color' => '#DC2626', 'icon' => 'fa-ban'],
                        ];
                        $pipeMax = max(1, (int) $pipelineCounts->max());
                    @endphp

                    @foreach($pipeStages as $st)
                        @php $cnt = (int) ($pipelineCounts[$st['status']] ?? 0); @endphp
                        <a href="{{ route('super-admin.orders.index', ['status' => $st['status']]) }}" class="gs-pipeline-item" title="Click to view all {{ $st['label'] }} orders">
                            <span class="gs-pipeline-lbl">
                                <i class="fa-solid {{ $st['icon'] }}" style="color:{{ $st['color'] }}; font-size:0.75rem;"></i>
                                {{ $st['label'] }}
                            </span>
                            <div class="gs-bar-track">
                                <div class="gs-bar-fill" style="width:{{ $cnt > 0 ? max(4, round($cnt / $pipeMax * 100)) : 0 }}%; background:{{ $st['color'] }};"></div>
                            </div>
                            <span class="gs-pipeline-num">{{ $cnt }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

        </div>

    </section>

    {{-- ══════════════════════════════════════════════════════════════════
         MODULE 2: REVENUE & SALES (FIXED PROPORTIONS!)
         ══════════════════════════════════════════════════════════════════ --}}
    <section class="gs-module" data-module-panel="revenue" hidden>

        {{-- Row 1: Custom vs Regular Donut + Revenue by Product Category --}}
        <div class="gs-grid-2">

            {{-- Custom vs Regular Split --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-circle-half-stroke"></i> Custom vs Regular Catalog
                        </h2>
                        <span class="gs-card-hint">Order composition and revenue distribution</span>
                    </div>
                </div>

                <div class="gs-donut-layout">
                    <div class="gs-donut-wrap">
                        <canvas id="typeDonutChart"></canvas>
                        <div class="gs-donut-center">
                            @php
                                $totalOrderMix = $customOrdersCount + $regularOrdersCount;
                                $customShare = $totalOrderMix > 0 ? round(($customOrdersCount / $totalOrderMix) * 100) : 0;
                            @endphp
                            <div class="gs-donut-center-val">{{ $customShare }}%</div>
                            <div class="gs-donut-center-lbl">Custom Share</div>
                        </div>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:0.85rem;">
                        <div class="gs-stat-card" style="padding:0.75rem 0.9rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span class="gs-stat-lbl"><i class="fa-solid fa-palette" style="color:var(--gs-cyan);"></i> Custom Orders</span>
                                <span class="gs-pill cyan">{{ $customOrdersCount }} orders</span>
                            </div>
                            <div class="gs-stat-val" style="font-size:1.25rem; color:var(--gs-cyan);">₱{{ number_format($customRevenue, 2) }}</div>
                            <div class="gs-stat-sub">avg ₱{{ number_format($customOrdersCount > 0 ? $customRevenue / $customOrdersCount : 0, 0) }} per order</div>
                        </div>

                        <div class="gs-stat-card" style="padding:0.75rem 0.9rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span class="gs-stat-lbl"><i class="fa-solid fa-box" style="color:var(--gs-emerald);"></i> Regular / Ready-Made</span>
                                <span class="gs-pill emerald">{{ $regularOrdersCount }} orders</span>
                            </div>
                            <div class="gs-stat-val" style="font-size:1.25rem; color:var(--gs-emerald);">₱{{ number_format($regularRevenue, 2) }}</div>
                            <div class="gs-stat-sub">avg ₱{{ number_format($regularOrdersCount > 0 ? $regularRevenue / $regularOrdersCount : 0, 0) }} per order</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Revenue by Product Category (PROPORTIONALLY SIZED BARS!) --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-layer-group"></i> Revenue by Product Category
                        </h2>
                        <span class="gs-card-hint">Sales contribution across catalog categories</span>
                    </div>
                </div>

                <div class="gs-chart-box" style="height:210px;">
                    <canvas id="categoryBarChart"></canvas>
                </div>

                {{-- Interactive Category Breakdown Pills --}}
                <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:0.75rem; padding-top:0.6rem; border-top:1px dashed var(--gs-border);">
                    @foreach($categoryBreakdown as $cat)
                        <span class="gs-pill gray" style="font-size:0.73rem;">
                            <strong>{{ $cat->category ?: 'Uncategorized' }}</strong>: ₱{{ number_format($cat->total_revenue, 0) }} ({{ $cat->total_units }} units)
                        </span>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Row 2: Top-Selling Products (Controlled bar thickness!) + Ranking Table --}}
        <div class="gs-grid-2">

            {{-- Top Sellers Chart --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-trophy" style="color:var(--text-warning);"></i> Top-Selling Products
                        </h2>
                        <span class="gs-card-hint">Best performers ranked by financial volume &amp; units</span>
                    </div>

                    <div class="gs-micro-tabs" id="sellerModeTabs">
                        <button type="button" data-mode="revenue" class="is-active">By Revenue</button>
                        <button type="button" data-mode="units">By Units</button>
                    </div>
                </div>

                <div class="gs-chart-box" style="height:260px;">
                    <canvas id="topSellersChart"></canvas>
                </div>
            </div>

            {{-- Ranking Table with Instant Client Search --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-list-ol"></i> Product Ranking
                        </h2>
                        <span class="gs-card-hint">Performance directory</span>
                    </div>

                    <div class="gs-table-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="rankingSearch" placeholder="Search product..." autocomplete="off">
                    </div>
                </div>

                <div class="gs-table-wrap">
                    <table class="gs-table" id="rankTableRevenue">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Product</th>
                                <th style="text-align:right;">Sold</th>
                                <th style="text-align:right;">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topByRevenue as $i => $row)
                                <tr class="rank-row" data-name="{{ strtolower($row->product->name) }}">
                                    <td>
                                        <span class="gs-rank-medal {{ $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : 'default')) }}">
                                            {{ $i + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('super-admin.products.edit', $row->product->id) }}" class="db-link" style="font-weight:700; color:var(--gs-text);">
                                            {{ Str::limit($row->product->name, 28) }}
                                        </a>
                                    </td>
                                    <td style="text-align:right; font-weight:700;">{{ (int) $row->total_sold }}</td>
                                    <td style="text-align:right; font-weight:800; color:var(--gs-dark);">₱{{ number_format($row->total_revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="gs-empty-state">No product sales recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <table class="gs-table" id="rankTableUnits" style="display:none;">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Product</th>
                                <th style="text-align:right;">Sold</th>
                                <th style="text-align:right;">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topByUnits as $i => $row)
                                <tr class="rank-row" data-name="{{ strtolower($row->product->name) }}">
                                    <td>
                                        <span class="gs-rank-medal {{ $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : 'default')) }}">
                                            {{ $i + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('super-admin.products.edit', $row->product->id) }}" class="db-link" style="font-weight:700; color:var(--gs-text);">
                                            {{ Str::limit($row->product->name, 28) }}
                                        </a>
                                    </td>
                                    <td style="text-align:right; font-weight:800; color:var(--gs-dark);">{{ (int) $row->total_sold }} units</td>
                                    <td style="text-align:right; font-weight:700;">₱{{ number_format($row->total_revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="gs-empty-state">No product sales recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </section>

    {{-- ══════════════════════════════════════════════════════════════════
         MODULE 3: COLLECTIONS
         ══════════════════════════════════════════════════════════════════ --}}
    <section class="gs-module" data-module-panel="collections" hidden>

        {{-- Verification Alert Banner --}}
        @if($pendingVerificationCount > 0)
            <div class="gs-alert-banner">
                <span class="gs-alert-icon"><i class="fa-solid fa-receipt"></i></span>
                <div class="gs-alert-content">
                    <div class="gs-alert-title">{{ $pendingVerificationCount }} payment {{ $pendingVerificationCount === 1 ? 'proof is' : 'proofs are' }} awaiting verification</div>
                    <div class="gs-alert-desc">₱{{ number_format($pendingVerificationTotal, 2) }} in submitted payments require staff sign-off to finalize order production.</div>
                </div>
                <a href="{{ route('super-admin.orders.index') }}" class="gs-action-btn" style="background:var(--danger-color); color:var(--text-inverse); border-color:var(--danger-color); font-weight:800;">
                    Review Proofs Now <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        @endif

        {{-- Mini Metrics Row --}}
        <div class="gs-grid-3">
            <div class="gs-stat-card">
                <span class="gs-stat-lbl"><i class="fa-solid fa-circle-check" style="color:var(--gs-emerald);"></i> Collected (Verified)</span>
                <span class="gs-stat-val" style="color:var(--gs-emerald);">₱{{ number_format($totalCollected, 2) }}</span>
                <span class="gs-stat-sub">₱{{ number_format($collectedThisMonth, 2) }} verified this month</span>
            </div>

            <div class="gs-stat-card">
                <span class="gs-stat-lbl"><i class="fa-solid fa-hourglass-half" style="color:var(--text-warning);"></i> Downpayment Balances</span>
                <span class="gs-stat-val" style="color:var(--text-warning);">₱{{ number_format($pendingDownpaymentBalance, 2) }}</span>
                <span class="gs-stat-sub">Pending completion across active orders</span>
            </div>

            <div class="gs-stat-card">
                <span class="gs-stat-lbl"><i class="fa-solid fa-triangle-exclamation" style="color:var(--gs-rose);"></i> Awaiting Verification</span>
                <span class="gs-stat-val" style="color:var(--gs-rose);">₱{{ number_format($pendingVerificationTotal, 2) }}</span>
                <span class="gs-stat-sub">{{ $pendingVerificationCount }} unverified customer payment {{ $pendingVerificationCount === 1 ? 'proof' : 'proofs' }}</span>
            </div>
        </div>

        {{-- Cash Position Donut + Verification Queue --}}
        <div class="gs-grid-2">

            {{-- Donut Card --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-wallet"></i> Collections Health
                        </h2>
                        <span class="gs-card-hint">Settlement ratio</span>
                    </div>
                </div>

                <div class="gs-donut-layout">
                    <div class="gs-donut-wrap">
                        <canvas id="collectionChartModule"></canvas>
                        <div class="gs-donut-center">
                            @php
                                $totalFunds = $totalCollected + $uncollectedTotal;
                                $collRate = $totalFunds > 0 ? round(($totalCollected / $totalFunds) * 100) : 0;
                            @endphp
                            <div class="gs-donut-center-val">{{ $collRate }}%</div>
                            <div class="gs-donut-center-lbl">Collected</div>
                        </div>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:0.65rem;">
                        <div class="gs-stat-card" style="padding:0.75rem;">
                            <span class="gs-stat-lbl" style="color:var(--gs-cyan);">Total Tracked</span>
                            <span class="gs-stat-val" style="font-size:1.2rem;">₱{{ number_format($totalFunds, 2) }}</span>
                        </div>
                        <div class="gs-stat-card" style="padding:0.75rem;">
                            <span class="gs-stat-lbl" style="color:var(--gs-rose);">Uncollected Sum</span>
                            <span class="gs-stat-val" style="font-size:1.2rem; color:var(--gs-rose);">₱{{ number_format($uncollectedTotal, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Verification Queue --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-receipt" style="color:var(--gs-magenta);"></i> Verification Queue
                        </h2>
                        <span class="gs-card-hint">{{ $pendingVerificationCount }} proofs waiting</span>
                    </div>
                </div>

                <div class="gs-table-wrap">
                    <table class="gs-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Method</th>
                                <th>Plan</th>
                                <th style="text-align:right;">Amount</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingProofs as $proof)
                                <tr>
                                    <td>
                                        @if($proof->order)
                                            <a href="{{ route('admin.orders.show', $proof->order->id) }}" class="gs-action-btn" style="padding:0.25rem 0.5rem; font-size:0.72rem; color:var(--gs-cyan);">
                                                #{{ $proof->order->order_number }}
                                            </a>
                                        @else
                                            <span style="color:var(--gs-faint);">—</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $proof->payment_method)) }}</td>
                                    <td><span class="gs-pill gray">{{ ucfirst(str_replace('_', ' ', $proof->payment_plan)) }}</span></td>
                                    <td style="text-align:right; font-weight:800; color:var(--gs-dark);">₱{{ number_format($proof->amount, 2) }}</td>
                                    <td style="font-size:0.73rem; color:var(--gs-faint);">{{ $proof->created_at->timezone('Asia/Manila')->format('M d, h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="gs-empty-state">No payment proofs awaiting verification.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Outstanding Downpayments Table --}}
        <div class="gs-card">
            <div class="gs-card-header">
                <div>
                    <h2 class="gs-card-title">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Outstanding Downpayment Balances
                    </h2>
                    <span class="gs-card-hint">Orders with unpaid downpayment balances</span>
                </div>
            </div>

            <div class="gs-table-wrap">
                <table class="gs-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Age</th>
                            <th style="text-align:right;">Remaining</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($downpaymentAging as $o)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $o->id) }}" class="gs-action-btn" style="padding:0.25rem 0.5rem; font-size:0.72rem; color:var(--gs-cyan);">
                                        #{{ $o->order_number }}
                                    </a>
                                </td>
                                <td style="font-weight:700;">{{ $o->user?->name ?? 'Walk-in Customer' }}</td>
                                <td>
                                    <span class="gs-pill {{ $o->age_days > 30 ? 'rose' : ($o->age_days > 14 ? 'amber' : 'gray') }}">
                                        {{ $o->age_days }}d old
                                    </span>
                                </td>
                                <td style="text-align:right; font-weight:800; color:var(--gs-dark);">₱{{ number_format($o->remaining_balance, 2) }}</td>
                                <td><span class="gs-pill gray">{{ str_replace('_', ' ', $o->status) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="gs-empty-state">Every downpayment plan is currently settled!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </section>

    {{-- ══════════════════════════════════════════════════════════════════
         MODULE 4: OPERATIONS & PRODUCTION
         ══════════════════════════════════════════════════════════════════ --}}
    <section class="gs-module" data-module-panel="operations" hidden>

        {{-- Top Mini Metric Cards --}}
        <div class="gs-grid-2">
            <div class="gs-stat-card">
                <span class="gs-stat-lbl"><i class="fa-solid fa-clipboard-list" style="color:var(--text-warning);"></i> Awaiting Processing</span>
                <span class="gs-stat-val" style="color:var(--text-warning);">{{ $pendingOrdersCount }}</span>
                <span class="gs-stat-sub">Oldest pending order: {{ $oldestPendingDays }} days</span>
            </div>

            <div class="gs-stat-card">
                <span class="gs-stat-lbl"><i class="fa-solid fa-pen-ruler" style="color:var(--gs-violet);"></i> Design Review Queue</span>
                <span class="gs-stat-val" style="color:var(--gs-violet);">{{ (int) ($pipelineCounts['design_review'] ?? 0) }}</span>
                <span class="gs-stat-sub">Artwork designs pending staff review</span>
            </div>
        </div>

        {{-- Design Queue & Aging Breakdown --}}
        <div class="gs-grid-2">

            {{-- Design Review Queue --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-pen-ruler"></i> Design Review Queue
                        </h2>
                        <span class="gs-card-hint">Custom artwork files waiting for review</span>
                    </div>
                </div>

                <div class="gs-table-wrap">
                    <table class="gs-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Age</th>
                                <th>Amount</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($designReviewQueue as $o)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $o->id) }}" class="db-link">#{{ $o->order_number }}</a></td>
                                    <td style="font-weight:700;">{{ $o->user?->name ?? 'Walk-in' }}</td>
                                    <td>
                                        <span class="gs-pill {{ $o->age_days > 7 ? 'rose' : ($o->age_days > 3 ? 'amber' : 'gray') }}">
                                            {{ $o->age_days }}d
                                        </span>
                                    </td>
                                    <td style="font-weight:800;">₱{{ number_format($o->total_price, 2) }}</td>
                                    <td style="text-align:right;">
                                        <a href="{{ route('admin.orders.show', $o->id) }}" class="gs-action-btn" style="padding:0.25rem 0.6rem; font-size:0.72rem;">
                                            Review Artwork
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="gs-empty-state">
                                        <i class="fa-solid fa-circle-check" style="color:var(--gs-emerald);"></i>
                                        No designs waiting for review.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pending Order Aging & Fulfillment Mix --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-hourglass-half"></i> Pending Order Aging
                        </h2>
                        <span class="gs-card-hint">Days elapsed since order creation</span>
                    </div>
                </div>

                <div class="gs-pipeline">
                    @php
                        $agingColors = ['0–2 days' => '#10B981', '3–7 days' => '#0099FF', '8–14 days' => '#F59E0B', '15+ days' => '#EF4444'];
                        $agingMax = max(1, (int) max($agingBuckets ?: [0]));
                    @endphp
                    @foreach($agingBuckets as $bucket => $count)
                        <div class="gs-pipeline-item">
                            <span class="gs-pipeline-lbl">{{ $bucket }}</span>
                            <div class="gs-bar-track">
                                <div class="gs-bar-fill" style="width:{{ $count > 0 ? max(4, round($count / $agingMax * 100)) : 0 }}%; background:{{ $agingColors[$bucket] }};"></div>
                            </div>
                            <span class="gs-pipeline-num">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top:1.15rem; padding-top:1rem; border-top:1px dashed var(--gs-border);">
                    <div class="gs-card-hint" style="margin-bottom:0.6rem; font-weight:800; text-transform:uppercase;">Fulfillment Mix</div>
                    @forelse($fulfillmentSplit as $fs)
                        @php
                            $fsLabel = $fs->fulfillment_type === 'pickup' ? 'Pickup' : 'Delivery';
                            if ($fs->is_walkin) { $fsLabel .= ' (walk-in)'; }
                        @endphp
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.45rem 0; font-size:0.82rem; border-bottom:1px solid var(--border-color);">
                            <span style="font-weight:700;">{{ $fsLabel }}</span>
                            <span style="display:flex; align-items:center; gap:0.5rem;">
                                <span class="gs-pill {{ $fs->fulfillment_type === 'pickup' ? 'cyan' : 'violet' }}">{{ $fs->c }} orders</span>
                                <span style="font-weight:800; color:var(--gs-dark);">₱{{ number_format($fs->amount, 0) }}</span>
                            </span>
                        </div>
                    @empty
                        <div class="gs-empty-state" style="padding:1rem;">No order fulfillment data yet.</div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Support Queue Widget --}}
        <div class="gs-card">
            <div class="gs-card-header">
                <div>
                    <h2 class="gs-card-title">
                        <i class="fa-solid fa-headset"></i> Customer Support Tickets
                    </h2>
                    <span class="gs-card-hint">Open client inquiries and requests</span>
                </div>
            </div>

            <div class="gs-grid-3">
                <div class="gs-stat-card">
                    <span class="gs-stat-lbl">Open Tickets</span>
                    <span class="gs-stat-val">{{ $ticketStats['open'] }}</span>
                    <span class="gs-stat-sub">Pending or in progress</span>
                </div>

                <div class="gs-stat-card">
                    <span class="gs-stat-lbl">High / Urgent Priority</span>
                    <span class="gs-stat-val" style="color:{{ $ticketStats['urgent'] > 0 ? 'var(--gs-rose)' : 'var(--gs-dark)' }};">{{ $ticketStats['urgent'] }}</span>
                    <span class="gs-stat-sub">Requires priority attention</span>
                </div>

                <div class="gs-stat-card" style="justify-content:center; align-items:center;">
                    <a href="{{ route('admin.tickets.index') }}" class="gs-action-btn" style="background:var(--gs-cyan); color:var(--text-inverse); border-color:var(--gs-cyan); font-weight:800;">
                        Open Support Queue <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

    </section>

    {{-- ══════════════════════════════════════════════════════════════════
         MODULE 5: INVENTORY HEALTH
         ══════════════════════════════════════════════════════════════════ --}}
    <section class="gs-module" data-module-panel="inventory" hidden>

        @if($lowStockCount > 0)
            <div class="gs-alert-banner warn">
                <span class="gs-alert-icon"><i class="fa-solid fa-boxes-packing"></i></span>
                <div class="gs-alert-content">
                    <div class="gs-alert-title">{{ $lowStockCount }} material{{ $lowStockCount > 1 ? 's are' : ' is' }} at or below restock threshold</div>
                    <div class="gs-alert-desc">Order replenish stocks to avoid production halts. Total reserved units: {{ number_format($reservedStockTotal, 2) }}.</div>
                </div>
                <a href="{{ route('admin.materials.index', ['stock_status' => 'low_stock']) }}" class="gs-action-btn" style="background:var(--gs-amber); color:var(--text-inverse); border-color:var(--gs-amber); font-weight:800;">
                    Go to Inventory <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        @endif

        {{-- Mini Metrics --}}
        <div class="gs-grid-3">
            <div class="gs-stat-card">
                <span class="gs-stat-lbl">Registered Materials</span>
                <span class="gs-stat-val">{{ $materials->count() }}</span>
                <span class="gs-stat-sub">Across {{ $materials->pluck('category')->unique()->count() }} material categories</span>
            </div>

            <div class="gs-stat-card">
                <span class="gs-stat-lbl">Low Stock Triggers</span>
                <span class="gs-stat-val" style="color:{{ $lowStockCount > 0 ? 'var(--gs-rose)' : 'var(--gs-emerald)' }};">{{ $lowStockCount }}</span>
                <span class="gs-stat-sub">Materials needing purchase orders</span>
            </div>

            <div class="gs-stat-card">
                <span class="gs-stat-lbl">Committed Reserved Units</span>
                <span class="gs-stat-val">{{ number_format($reservedStockTotal, 2) }}</span>
                <span class="gs-stat-sub">Currently allocated to in-progress jobs</span>
            </div>
        </div>

        {{-- Stock Health Table with Live Search Filter --}}
        <div class="gs-card">
            <div class="gs-card-header">
                <div>
                    <h2 class="gs-card-title">
                        <i class="fa-solid fa-warehouse"></i> Material Stock Health &amp; Coverage
                    </h2>
                    <span class="gs-card-hint">Available supply vs restock trigger points</span>
                </div>

                <div class="gs-table-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="inventorySearch" placeholder="Search material..." autocomplete="off">
                </div>
            </div>

            <div class="gs-table-wrap">
                <table class="gs-table" id="stockHealthTable">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Category</th>
                            <th style="text-align:right;">On Hand</th>
                            <th style="text-align:right;">Reserved</th>
                            <th style="text-align:right;">Available</th>
                            <th style="text-align:right;">Trigger</th>
                            <th style="width:28%;">Coverage</th>
                            <th style="text-align:right;">Days Cover</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockHealth as $s)
                            @php
                                $toneColor = match ($s->tone) {
                                    'out'      => 'rose',
                                    'critical' => 'rose',
                                    'watch'    => 'amber',
                                    default    => 'emerald',
                                };
                            @endphp
                            <tr class="inv-row" data-name="{{ strtolower($s->material->name . ' ' . $s->material->category) }}">
                                <td style="font-weight:700;">{{ $s->material->name }}</td>
                                <td style="color:var(--gs-faint);">{{ $s->material->category }}</td>
                                <td style="text-align:right; color:var(--gs-faint);">{{ number_format($s->current, 2) }} {{ $s->material->unit }}</td>
                                <td style="text-align:right; color:var(--gs-faint);">{{ number_format($s->reserved, 2) }}</td>
                                <td style="text-align:right; font-weight:800; color:var(--gs-dark);">{{ number_format($s->available, 2) }} {{ $s->material->unit }}</td>
                                <td style="text-align:right; color:var(--gs-faint);">{{ number_format($s->threshold, 2) }}</td>
                                <td>
                                    <div class="gs-bar-track">
                                        <div class="gs-bar-fill" style="width:{{ max(2, min(100, $s->pct)) }}%; background:var(--gs-{{ $toneColor == 'rose' ? 'rose' : ($toneColor == 'amber' ? 'amber' : 'emerald') }});"></div>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-top:0.25rem;">
                                        <span class="gs-pill {{ $toneColor }}">{{ strtoupper($s->tone) }}</span>
                                        <span style="font-size:0.7rem; color:var(--gs-faint);">{{ $s->pct }}% of trigger</span>
                                    </div>
                                </td>
                                <td style="text-align:right; font-weight:800; color:var(--gs-dark);">
                                    {{ $s->cover !== null ? $s->cover . 'd' : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="gs-empty-state">No materials registered in inventory yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </section>

    {{-- ══════════════════════════════════════════════════════════════════
         MODULE 6: SUPPLIER ANALYTICS
         ══════════════════════════════════════════════════════════════════ --}}
    <section class="gs-module" data-module-panel="suppliers" hidden>

        {{-- Mini Metric Cards --}}
        <div class="gs-grid-3">
            <div class="gs-stat-card">
                <span class="gs-stat-lbl"><i class="fa-solid fa-truck-field" style="color:var(--gs-cyan);"></i> Active Suppliers</span>
                <span class="gs-stat-val">{{ $supplierStats->count() }}</span>
                <span class="gs-stat-sub">{{ $supplierStats->sum('po_count') }} purchase orders on file</span>
            </div>

            <div class="gs-stat-card">
                <span class="gs-stat-lbl"><i class="fa-solid fa-peso-sign" style="color:var(--gs-emerald);"></i> Total Supplier Spend</span>
                <span class="gs-stat-val" style="color:var(--gs-emerald);">₱{{ number_format($supplierStats->sum('total_spend'), 2) }}</span>
                <span class="gs-stat-sub">Excludes cancelled purchase orders</span>
            </div>

            <div class="gs-stat-card">
                <span class="gs-stat-lbl"><i class="fa-solid fa-hand-holding-dollar" style="color:var(--text-warning);"></i> Outstanding Payables</span>
                <span class="gs-stat-val" style="color:var(--text-warning);">₱{{ number_format(max(0, $supplierOutstanding), 2) }}</span>
                <span class="gs-stat-sub">₱{{ number_format($supplierPaidTotal, 2) }} settled to suppliers</span>
            </div>
        </div>

        {{-- Unit Price Comparison --}}
        <div class="gs-card" style="margin-bottom:1.15rem;">
            <div class="gs-card-header">
                <div>
                    <h2 class="gs-card-title">
                        <i class="fa-solid fa-scale-balanced" style="color:var(--gs-magenta);"></i> Unit Cost Comparison
                    </h2>
                    <span class="gs-card-hint">Normalised base costs across supplier catalogs</span>
                </div>
            </div>

            @if($supplierPriceComparison->isEmpty())
                <div class="gs-empty-state">
                    <i class="fa-solid fa-circle-info"></i>
                    No supplier material pricing records registered yet.
                </div>
            @else
                <div class="gs-grid-3">
                    @foreach($supplierPriceComparison as $cmp)
                        <div class="gs-stat-card">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                <strong style="font-size:0.9rem; color:var(--gs-dark);">{{ $cmp->material }}</strong>
                                @if($cmp->savings_pct > 0)
                                    <span class="gs-pill emerald">SAVE {{ $cmp->savings_pct }}%</span>
                                @else
                                    <span class="gs-pill gray">Single Vendor</span>
                                @endif
                            </div>

                            <div style="display:flex; flex-direction:column; gap:0.35rem;">
                                @foreach($cmp->options as $opt)
                                    <div style="display:flex; align-items:center; justify-content:space-between; font-size:0.78rem; padding:0.35rem 0.5rem; border-radius:7px; {{ $loop->first ? 'background:var(--success-soft); border:1px solid var(--success-border);' : '' }}">
                                        <div>
                                            <span style="font-weight:700;">{{ Str::limit($opt->supplier_name, 18) }}</span>
                                            <span style="font-size:0.68rem; color:var(--gs-faint); display:block;">{{ $opt->supplier_unit_name }} ({{ number_format($opt->conversion_qty, 0) }})</span>
                                        </div>
                                        <div style="font-weight:800; color:{{ $loop->first ? 'var(--text-success)' : 'var(--gs-dark)' }};">
                                            ₱{{ number_format($opt->normalized_cost, 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div style="font-size:0.71rem; color:var(--gs-faint); margin-top:0.45rem;">
                                Best option: <strong>{{ $cmp->cheapest }}</strong> (₱{{ number_format($cmp->cheapest_cost, 2) }}/{{ $cmp->unit ?: 'unit' }})
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Supplier Scorecard & Payables Aging --}}
        <div class="gs-grid-2">

            {{-- Scorecard --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-users"></i> Supplier Directory &amp; Spend
                        </h2>
                        <span class="gs-card-hint">Procurement history</span>
                    </div>
                </div>

                <div class="gs-table-wrap">
                    <table class="gs-table">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Contact</th>
                                <th style="text-align:right;">POs</th>
                                <th style="text-align:right;">Spend</th>
                                <th style="text-align:right;">Open</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supplierStats as $s)
                                <tr>
                                    <td style="font-weight:700;">{{ Str::limit($s->name, 22) }}</td>
                                    <td style="color:var(--gs-faint);">{{ $s->contact_person ?: '—' }}</td>
                                    <td style="text-align:right; font-weight:700;">{{ (int) $s->po_count }}</td>
                                    <td style="text-align:right; font-weight:800; color:var(--gs-dark);">₱{{ number_format($s->total_spend, 2) }}</td>
                                    <td style="text-align:right;">
                                        @if($s->open_po_value > 0)
                                            <span class="gs-pill amber">₱{{ number_format($s->open_po_value, 0) }}</span>
                                        @else
                                            <span class="gs-pill emerald">Settled</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="gs-empty-state">No suppliers registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Payables Aging --}}
            <div class="gs-card">
                <div class="gs-card-header">
                    <div>
                        <h2 class="gs-card-title">
                            <i class="fa-solid fa-calendar-day"></i> Outstanding Purchase Payables
                        </h2>
                        <span class="gs-card-hint">Supplier invoices due</span>
                    </div>
                </div>

                <div class="gs-table-wrap">
                    <table class="gs-table">
                        <thead>
                            <tr>
                                <th>PO Ref</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th style="text-align:right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payablesAging as $p)
                                @php
                                    $dueBadge = match ($p->bucket) {
                                        'overdue'    => ['rose', 'OVERDUE'],
                                        'due_soon'   => ['amber', 'DUE SOON'],
                                        'no_due_date'=> ['gray', 'NO DATE'],
                                        default      => ['cyan', 'SCHEDULED'],
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.purchases.show', $p->id) }}" class="db-link">
                                            {{ $p->reference_no }}
                                        </a>
                                    </td>
                                    <td style="font-weight:700;">{{ Str::limit($p->supplier_name, 18) }}</td>
                                    <td>
                                        <span class="gs-pill {{ $dueBadge[0] }}">{{ $dueBadge[1] }}</span>
                                        @if($p->days_to_due !== null)
                                            <span style="font-size:0.68rem; color:var(--gs-faint); margin-left:0.25rem;">
                                                {{ $p->days_to_due < 0 ? abs($p->days_to_due) . 'd late' : $p->days_to_due . 'd left' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align:right; font-weight:800; color:var(--gs-dark);">₱{{ number_format($p->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="gs-empty-state">No outstanding supplier payables.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:1.15rem; padding-top:0.85rem; border-top:1px dashed var(--gs-border);">
                    <div class="gs-card-hint" style="margin-bottom:0.5rem; font-weight:800; text-transform:uppercase;">Procurement Status Mix</div>
                    <div style="display:flex; gap:0.45rem; flex-wrap:wrap;">
                        @forelse($poPipeline as $st => $row)
                            <span class="gs-pill {{ $st === 'received' ? 'emerald' : ($st === 'cancelled' ? 'rose' : 'amber') }}">
                                {{ ucfirst($st) }}: {{ (int) $row->c }} · ₱{{ number_format($row->amount, 0) }}
                            </span>
                        @empty
                            <span style="font-size:0.75rem; color:var(--gs-faint);">No purchase orders recorded yet.</span>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

    </section>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ANALYTICS_URL = "{{ route('super-admin.analytics.data') }}";
    const MODULES = ['overview', 'revenue', 'collections', 'operations', 'inventory', 'suppliers'];
    const CHARTS = {};

    // ── PHP Injected Datasets ──────────────────────────────────────────
    const initTrendLabels  = @json($chartTrendLabels);
    const initTrendRevenue = @json($chartTrendRevenue);
    const initTrendOrders  = @json($chartTrendOrders);

    const revenueLabels = @json($topByRevenue->map(fn($s) => Str::limit($s->product->name, 22))->values());
    const revenueData   = @json($topByRevenue->pluck('total_revenue')->map(fn($v) => (float)$v)->values());
    const unitsLabels   = @json($topByUnits->map(fn($s) => Str::limit($s->product->name, 22))->values());
    const unitsData     = @json($topByUnits->pluck('total_sold')->map(fn($v) => (int)$v)->values());

    const donutData   = [{{ (float)$customRevenue }}, {{ (float)$regularRevenue }}];
    const donutCounts = [{{ $customOrdersCount }}, {{ $regularOrdersCount }}];
    const donutLabels = ['Custom Orders', 'Regular / Ready-Made'];

    const catLabels   = @json($categoryBreakdown->pluck('category')->values());
    const catRevenue  = @json($categoryBreakdown->pluck('total_revenue')->map(fn($v) => (float)$v)->values());
    const catUnits    = @json($categoryBreakdown->pluck('total_units')->map(fn($v) => (int)$v)->values());

    const cashData = [
        {{ (float)$totalCollected }},
        {{ (float)$pendingDownpaymentBalance }},
        {{ (float)$pendingVerificationTotal }},
    ];
    const cashLabels = ['Collected (Verified)', 'Pending Downpayments', 'Awaiting Verification'];

    // ── Global Chart.js Configuration ──────────────────────────────────
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size   = 11.5;
    Chart.defaults.color       = '#64748B';

    const peso = (val, dec = 2) => {
        const num = parseFloat(val || 0);
        // Smart formatting: drop decimals for amounts >= 1M
        const smartDec = num >= 1000000 ? 0 : dec;
        return '₱' + num.toLocaleString('en-PH', {
            minimumFractionDigits: smartDec,
            maximumFractionDigits: smartDec
        });
    };

    // ══════════════════════════════════════════════════════════════════
    //  1. MODULE SWITCHING & TAB SYNCHRONIZATION
    // ══════════════════════════════════════════════════════════════════
    function activateModule(name, opts = {}) {
        if (!MODULES.includes(name)) name = 'overview';

        // Update nav buttons
        document.querySelectorAll('#moduleNav .gs-nav-btn').forEach(btn => {
            btn.classList.toggle('is-active', btn.dataset.module === name);
        });

        // Update module panels
        document.querySelectorAll('[data-module-panel]').forEach(panel => {
            const active = panel.dataset.modulePanel === name;
            panel.hidden = !active;
            panel.classList.toggle('is-active', active);
        });

        // Update KPI card active indicators
        document.querySelectorAll('.gs-kpi-card').forEach(card => {
            card.classList.toggle('is-active-tab', card.dataset.targetModule === name);
        });

        if (!opts.fromHistory) {
            try { history.replaceState(null, '', '#' + name); } catch (e) {}
        }

        // Resize Chart.js canvases that were previously hidden
        requestAnimationFrame(() => {
            Object.values(CHARTS).forEach(chart => {
                if (chart && typeof chart.resize === 'function') {
                    try { chart.resize(); } catch (e) {}
                }
            });
        });
    }

    // Attach click listener to module navigation buttons
    document.querySelectorAll('#moduleNav .gs-nav-btn').forEach(btn => {
        btn.addEventListener('click', () => activateModule(btn.dataset.module));
    });

    // Attach click listener to KPI cards for smart tab navigation
    document.querySelectorAll('.gs-kpi-card').forEach(card => {
        card.addEventListener('click', () => {
            const mod = card.dataset.targetModule;
            if (mod) activateModule(mod);
        });
    });

    // ══════════════════════════════════════════════════════════════════
    //  2. PROPORTIONAL TREND CHART (Revenue & Order Velocity)
    // ══════════════════════════════════════════════════════════════════
    let currentPeriod = 'monthly';
    let currentRange  = 'all';

    function createRevenueGradient(ctx) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 270);
        gradient.addColorStop(0, 'rgba(0, 153, 255, 0.22)');
        gradient.addColorStop(1, 'rgba(0, 153, 255, 0.00)');
        return gradient;
    }

    function buildTrendChart(labels, revenue, orders) {
        if (CHARTS.trend) CHARTS.trend.destroy();
        const canvas = document.getElementById('trendChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        CHARTS.trend = new Chart(ctx, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Orders',
                        data: orders,
                        backgroundColor: 'rgba(0, 153, 255, 0.14)',
                        borderColor: 'rgba(0, 153, 255, 0.40)',
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 26,
                        barPercentage: 0.6,
                        categoryPercentage: 0.6,
                        yAxisID: 'yOrders',
                        order: 2,
                    },
                    {
                        type: 'line',
                        label: 'Revenue (₱)',
                        data: revenue,
                        borderColor: '#0099FF',
                        backgroundColor: createRevenueGradient(ctx),
                        borderWidth: 2.5,
                        tension: 0.38,
                        fill: true,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#0099FF',
                        pointBorderWidth: 2,
                        pointRadius: 4.5,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#0099FF',
                        pointHoverBorderColor: '#FFFFFF',
                        pointHoverBorderWidth: 2,
                        yAxisID: 'yRevenue',
                        order: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                onClick(evt, elements) {
                    if (!elements.length) return;
                    const idx = elements[0].index;
                    openTrendDrill(labels[idx], revenue[idx], orders[idx]);
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { weight: '600', size: 11 },
                            padding: 12,
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        titleFont: { weight: '700', size: 12 },
                        bodyFont: { size: 11.5 },
                        padding: 10,
                        cornerRadius: 9,
                        callbacks: {
                            label(context) {
                                if (context.dataset.label === 'Revenue (₱)') {
                                    return '  Revenue: ' + peso(context.raw);
                                }
                                return '  Orders: ' + context.raw + ' orders';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxTicksLimit: 12, font: { weight: '600' } },
                    },
                    yRevenue: {
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: v => '₱' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v)
                        },
                    },
                    yOrders: {
                        position: 'right',
                        beginAtZero: true,
                        grid: { display: false },
                        ticks: {
                            precision: 0,
                            callback: v => v + ' ord'
                        },
                    },
                },
            },
        });
    }

    function openTrendDrill(label, revenue, orders) {
        const box = document.getElementById('trendDrillBox');
        const body = document.getElementById('trendDrillContent');
        if (!box || !body) return;

        const avg = orders > 0 ? (revenue / orders) : 0;
        body.innerHTML = `
            <div style="display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:1rem;">
                <div class="gs-stat-card" style="padding:0.65rem 0.8rem;">
                    <span class="gs-stat-lbl">Timeline Window</span>
                    <span class="gs-stat-val" style="font-size:1.1rem;">${label}</span>
                </div>
                <div class="gs-stat-card" style="padding:0.65rem 0.8rem;">
                    <span class="gs-stat-lbl">Window Revenue</span>
                    <span class="gs-stat-val" style="font-size:1.1rem; color:var(--gs-cyan);">${peso(revenue)}</span>
                </div>
                <div class="gs-stat-card" style="padding:0.65rem 0.8rem;">
                    <span class="gs-stat-lbl">Order Volume</span>
                    <span class="gs-stat-val" style="font-size:1.1rem; color:var(--gs-dark);">${orders} orders</span>
                </div>
                <div class="gs-stat-card" style="padding:0.65rem 0.8rem;">
                    <span class="gs-stat-lbl">Average Ticket Size</span>
                    <span class="gs-stat-val" style="font-size:1.1rem; color:var(--text-success);">${peso(avg, 0)}</span>
                </div>
            </div>`;
        box.classList.add('is-open');
    }

    // ══════════════════════════════════════════════════════════════════
    //  3. DYNAMIC AJAX DATA FETCH (Period & Global Range Switcher)
    // ══════════════════════════════════════════════════════════════════
    async function fetchAnalyticsData() {
        const spinner = document.getElementById('trendSpinner');
        const refreshBtn = document.getElementById('btnRefreshAnalytics');
        if (spinner) spinner.classList.add('is-visible');
        if (refreshBtn) refreshBtn.classList.add('refreshing');

        try {
            const url = `${ANALYTICS_URL}?period=${currentPeriod}&range=${currentRange}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Network response error');
            const data = await res.json();

            // Re-render trend chart
            if (data.labels && data.revenue && data.orders) {
                buildTrendChart(data.labels, data.revenue, data.orders);
            }

            // Update KPI cards smoothly if data returned
            if (data.kpis) {
                updateKPIs(data.kpis);
            }
        } catch (err) {
            console.error('Analytics load error:', err);
        } finally {
            if (spinner) spinner.classList.remove('is-visible');
            if (refreshBtn) refreshBtn.classList.remove('refreshing');
        }
    }

    function updateKPIs(kpis) {
        const setVal = (id, txt) => {
            const el = document.getElementById(id);
            if (el) el.textContent = txt;
        };

        // Smart peso with cents span for KPI values
        const pesoHtml = (id, val) => {
            const el = document.getElementById(id);
            if (!el) return;
            const num = parseFloat(val || 0);
            const whole = Math.floor(num);
            const cents = String(Math.round((num - whole) * 100)).padStart(2, '0');
            const wholeStr = whole.toLocaleString('en-PH');
            el.innerHTML = '₱' + wholeStr + '<span class="gs-val-cents">.' + cents + '</span>';
        };

        if (kpis.totalRevenue !== undefined) pesoHtml('val-revenue', kpis.totalRevenue);
        if (kpis.scopedOrderCount !== undefined) setVal('val-orders-count', kpis.scopedOrderCount);
        if (kpis.averageOrderValue !== undefined) setVal('val-avg-order', Math.round(kpis.averageOrderValue).toLocaleString());
        if (kpis.pendingVerificationTotal !== undefined) pesoHtml('val-pending', kpis.pendingVerificationTotal);
        if (kpis.pendingVerificationCount !== undefined) setVal('val-proofs-count', kpis.pendingVerificationCount);
        if (kpis.totalCollected !== undefined) pesoHtml('val-collected', kpis.totalCollected);
        if (kpis.collectedThisMonth !== undefined) setVal('val-collected-month', parseFloat(kpis.collectedThisMonth || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 }));
        if (kpis.uncollectedTotal !== undefined) pesoHtml('val-uncollected', kpis.uncollectedTotal);
        if (kpis.totalOrdersCount !== undefined) setVal('val-orders', kpis.totalOrdersCount);
        if (kpis.inProgressOrdersCount !== undefined) setVal('val-inprogress-count', kpis.inProgressOrdersCount);
        if (kpis.pendingOrdersCount !== undefined) setVal('val-pending-orders-count', kpis.pendingOrdersCount);
    }

    // Attach listeners to Trend Period switcher
    document.querySelectorAll('#trendPeriodTabs button').forEach(btn => {
        btn.addEventListener('click', () => {
            const p = btn.dataset.period;
            if (p === currentPeriod) return;
            currentPeriod = p;

            document.querySelectorAll('#trendPeriodTabs button').forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            fetchAnalyticsData();
        });
    });

    // Attach listeners to Global Range selector
    document.querySelectorAll('#globalRangeSelector .gs-range-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const r = btn.dataset.range;
            if (r === currentRange) return;
            currentRange = r;

            document.querySelectorAll('#globalRangeSelector .gs-range-btn').forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            fetchAnalyticsData();
        });
    });

    // Attach Refresh button listener
    const refreshBtn = document.getElementById('btnRefreshAnalytics');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', fetchAnalyticsData);
    }

    // ══════════════════════════════════════════════════════════════════
    //  4. CASH POSITION DONUT CHARTS (Overview + Collections)
    // ══════════════════════════════════════════════════════════════════
    function buildCashDonut(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        return new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: cashLabels,
                datasets: [{
                    data: cashData,
                    backgroundColor: ['#0099FF', '#F59E0B', '#EF4444'],
                    borderColor: ['#FFFFFF', '#FFFFFF', '#FFFFFF'],
                    borderWidth: 3,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '74%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => `  ${ctx.label}: ${peso(ctx.raw)}`
                        }
                    }
                }
            }
        });
    }
    CHARTS.cashOverview = buildCashDonut('cashDonutOverview');
    CHARTS.cashModule   = buildCashDonut('collectionChartModule');

    // ══════════════════════════════════════════════════════════════════
    //  5. CUSTOM vs REGULAR ORDERS DONUT
    // ══════════════════════════════════════════════════════════════════
    function buildTypeDonut() {
        const canvas = document.getElementById('typeDonutChart');
        if (!canvas) return null;

        return new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: donutLabels,
                datasets: [{
                    data: donutData,
                    backgroundColor: ['#0099FF', '#10B981'],
                    borderColor: ['#FFFFFF', '#FFFFFF'],
                    borderWidth: 3,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '74%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        cornerRadius: 8,
                        callbacks: {
                            label(ctx) {
                                const cnt = donutCounts[ctx.dataIndex] || 0;
                                return `  ${ctx.label}: ${peso(ctx.raw)} (${cnt} orders)`;
                            }
                        }
                    }
                }
            }
        });
    }
    CHARTS.typeDonut = buildTypeDonut();

    // ══════════════════════════════════════════════════════════════════
    //  6. REVENUE BY PRODUCT CATEGORY (MAX BAR THICKNESS CONTROLLED!)
    // ══════════════════════════════════════════════════════════════════
    const categoryPalette = ['#0099FF', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#EC4899', '#06B6D4', '#84CC16', '#6366F1'];

    function buildCategoryChart() {
        const canvas = document.getElementById('categoryBarChart');
        if (!canvas) return null;

        return new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: catLabels,
                datasets: [{
                    label: 'Revenue',
                    data: catRevenue,
                    backgroundColor: catLabels.map((_, i) => categoryPalette[i % categoryPalette.length]),
                    borderRadius: 8,
                    maxBarThickness: 32,
                    barPercentage: 0.6,
                    categoryPercentage: 0.7,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => '  Revenue: ' + peso(ctx.raw)
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: '700' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: v => '₱' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v)
                        }
                    }
                }
            }
        });
    }
    CHARTS.category = buildCategoryChart();

    // ══════════════════════════════════════════════════════════════════
    //  7. TOP SELLERS CHART (PROPORTIONAL HORIZONTAL BARS!)
    // ══════════════════════════════════════════════════════════════════
    let currentSellerMode = 'revenue';

    function buildSellersChart(labels, data, mode) {
        if (CHARTS.sellers) CHARTS.sellers.destroy();
        const canvas = document.getElementById('topSellersChart');
        if (!canvas) return;

        CHARTS.sellers = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: mode === 'revenue' ? 'Revenue (₱)' : 'Units Sold',
                    data: data,
                    backgroundColor: labels.map((_, i) =>
                        i === 0 ? '#F59E0B' : (i === 1 ? '#94A3B8' : (i === 2 ? '#D97706' : '#0099FF'))
                    ),
                    borderRadius: 6,
                    maxBarThickness: 24,
                    barPercentage: 0.65,
                    categoryPercentage: 0.75,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        cornerRadius: 8,
                        callbacks: {
                            label(ctx) {
                                return mode === 'revenue'
                                    ? '  Revenue: ' + peso(ctx.raw)
                                    : '  Sold: ' + parseInt(ctx.raw).toLocaleString() + ' units';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: v => mode === 'revenue'
                                ? '₱' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v)
                                : v + ' u'
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { weight: '600' } }
                    }
                }
            }
        });
    }
    buildSellersChart(revenueLabels, revenueData, 'revenue');

    // Toggle seller mode By Revenue / By Units
    document.querySelectorAll('#sellerModeTabs button').forEach(btn => {
        btn.addEventListener('click', () => {
            const mode = btn.dataset.mode;
            if (mode === currentSellerMode) return;
            currentSellerMode = mode;

            document.querySelectorAll('#sellerModeTabs button').forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            const revTable = document.getElementById('rankTableRevenue');
            const unitTable = document.getElementById('rankTableUnits');
            if (revTable)  revTable.style.display  = mode === 'revenue' ? '' : 'none';
            if (unitTable) unitTable.style.display = mode === 'units'   ? '' : 'none';

            if (mode === 'revenue') {
                buildSellersChart(revenueLabels, revenueData, 'revenue');
            } else {
                buildSellersChart(unitsLabels, unitsData, 'units');
            }
        });
    });

    // ══════════════════════════════════════════════════════════════════
    //  8. LIVE CLIENT-SIDE TABLE SEARCH FILTERS
    // ══════════════════════════════════════════════════════════════════
    // Product ranking search
    const rankSearch = document.getElementById('rankingSearch');
    if (rankSearch) {
        rankSearch.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.rank-row').forEach(row => {
                const name = row.dataset.name || '';
                row.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }

    // Inventory search
    const invSearch = document.getElementById('inventorySearch');
    if (invSearch) {
        invSearch.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.inv-row').forEach(row => {
                const name = row.dataset.name || '';
                row.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }

    // ══════════════════════════════════════════════════════════════════
    //  9. INITIALIZATION
    // ══════════════════════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function () {
        const hash = (window.location.hash || '').replace('#', '');
        activateModule(MODULES.includes(hash) ? hash : 'overview', { fromHistory: true });
        buildTrendChart(initTrendLabels, initTrendRevenue, initTrendOrders);
    });

    window.activateModule = activateModule;
})();
</script>
@endpush
