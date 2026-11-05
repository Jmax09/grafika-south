@extends('layouts.app')

@section('title', 'Account Settings & Profile - Grafika South')

@section('content')
<style>
    /* ======================================================================
       GRAFIKA SOUTH — CUSTOMER ACCOUNT HUB
       Redesign of /customer/profile
       Brand tokens: Cyan #0099FF · Magenta #EC008C · Yellow #EAB308 · Key #0F172A
       ====================================================================== */

    body {
        background-color: var(--bg-subtle) !important;
    }

    .gs-profile-page {
        --gs-cyan: #0099FF;
        --gs-rail-w: 290px;
        --gs-cyan-hover: #0080FF;
        --gs-cyan-light: #E6F4FF;
        --gs-cyan-soft: #F2F9FF;
        --gs-magenta: #EC008C;
        --gs-magenta-light: #FDE8F5;
        --gs-yellow: #EAB308;
        --gs-key: #0F172A;
        --gs-text: #1F2937;
        --gs-muted: #64748B;
        --gs-faint: #94A3B8;
        --gs-border: #E6EDF5;
        --gs-border-2: #D8E2EE;
        --gs-surface: #FFFFFF;

        max-width: 1280px;
        margin: 0 auto;
        padding-bottom: 4rem;
        color: var(--gs-text);
    }

    /* Dark mode: flip the self-contained gs-* palette to dark surfaces/text so
       cards, titles and descriptions stay readable. */
    html.theme-dark .gs-profile-page {
        --gs-cyan: #38BDF8;
        --gs-cyan-hover: #0EA5E9;
        --gs-cyan-light: rgba(56, 189, 248, 0.16);
        --gs-cyan-soft: rgba(56, 189, 248, 0.10);
        --gs-magenta: #F472B6;
        --gs-magenta-light: rgba(236, 0, 140, 0.16);
        --gs-yellow: #FACC15;
        --gs-key: #E6EDF7;
        --gs-text: #D7E0EC;
        --gs-muted: #94A3B8;
        --gs-faint: #64748B;
        --gs-border: #1E293B;
        --gs-border-2: #334155;
        --gs-surface: #0E1626;
    }

    /* ---------- Page header ---------- */
    .gs-page-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        flex-wrap: wrap;
        margin-bottom: 1.15rem;
    }

    .gs-page-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.7rem;
        font-weight: 800;
        color: var(--gs-key);
        letter-spacing: -0.02em;
        line-height: 1.15;
        margin: 0;
    }

    .gs-btn-edit {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1.05rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.82rem;
        font-family: inherit;
        border: 1.5px solid var(--gs-cyan);
        background: var(--gs-cyan);
        color: var(--text-inverse);
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: none;
        white-space: nowrap;
    }

    .gs-btn-edit:hover {
        background: var(--gs-cyan-hover);
        border-color: var(--gs-cyan-hover);
        transform: translateY(-1px);
        box-shadow: none;
    }

    .gs-btn-edit.is-editing {
        background: var(--bg-surface);
        color: var(--text-muted);
        border-color: var(--gs-border-2);
        box-shadow: none;
    }

    .gs-btn-edit.is-editing:hover {
        background: var(--bg-subtle);
        color: var(--gs-key);
        border-color: var(--border-strong);
    }

    /* The Edit button appears either in the page header (desktop) or in the
       Account Security panel header (mobile). Only one is visible at a time. */
    /* The Edit Profile button lives in the page header on EVERY breakpoint.
       On small screens the page head turns sticky so the button is always
       reachable, whatever tab is open. */

    /* ---------- Two column layout ---------- */
    .gs-layout {
        display: grid;
        grid-template-columns: var(--gs-rail-w) minmax(0, 1fr);
        gap: 1.15rem;
        align-items: start;
    }

    @media (max-width: 1023.98px) {
        .gs-layout {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    /* ---------- Left rail ---------- */
    .gs-rail {
        position: sticky;
        top: 78px;
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }

    @media (max-width: 1023.98px) {
        .gs-rail {
            position: static;
        }
    }

    .gs-rail-card {
        background: var(--gs-surface);
        border: 1px solid var(--gs-border);
        border-radius: 18px;
        padding: 1.15rem 1rem;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
        text-align: center;
    }

    /* ---------- Avatar ---------- */
    .gs-avatar-ring {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        padding: 3px;
        background: linear-gradient(135deg, var(--gs-cyan) 0%, var(--gs-magenta) 60%, var(--gs-yellow) 100%);
        margin: 0 auto 0.7rem;
        position: relative;
        cursor: default;
        box-shadow: 0 8px 20px rgba(0, 153, 255, 0.2);
        transition: transform 0.2s ease;
    }

    .gs-avatar-ring.is-editable {
        cursor: pointer;
    }

    .gs-avatar-ring.is-editable:hover {
        transform: scale(1.03);
    }

    .gs-avatar-circle {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent-color), var(--brand-magenta-fg));
        color: var(--text-inverse);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.55rem;
        font-weight: 800;
        overflow: hidden;
        position: relative;
    }

    .gs-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        display: block;
    }

    .gs-avatar-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.66);
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--text-inverse);
        opacity: 0;
        transition: opacity 0.2s ease;
        pointer-events: none;
    }

    .gs-avatar-ring.is-editable:hover .gs-avatar-overlay {
        opacity: 1;
    }

    .gs-avatar-overlay i {
        font-size: 0.85rem;
        margin-bottom: 2px;
    }

    .gs-avatar-overlay span {
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
    }

    .gs-avatar-badge {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--gs-cyan);
        color: var(--text-inverse);
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 0.68rem;
        border: 2.5px solid var(--border-color);
        box-shadow: 0 3px 8px rgba(0, 153, 255, 0.35);
        transition: all 0.2s ease;
        cursor: pointer;
        z-index: 5;
    }

    .gs-avatar-ring.is-editable .gs-avatar-badge {
        display: flex;
    }

    .gs-avatar-ring.is-editable:hover .gs-avatar-badge {
        background: var(--gs-cyan-hover);
        transform: scale(1.08);
    }

    .gs-avatar-spinner {
        position: absolute;
        inset: 0;
        background: color-mix(in srgb, var(--bg-surface) 88%, transparent);
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10;
        color: var(--gs-cyan);
        font-size: 1.4rem;
    }

    .gs-rail-name {
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--gs-key);
        line-height: 1.25;
        word-break: break-word;
    }

    .gs-rail-email {
        font-size: 0.76rem;
        color: var(--gs-muted);
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.2rem;
        word-break: break-all;
    }

    .gs-rail-email i {
        color: var(--gs-faint);
        flex-shrink: 0;
    }

    .gs-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background: var(--gs-cyan-light);
        color: var(--gs-cyan-hover);
        font-size: 0.62rem;
        font-weight: 800;
        padding: 0.2rem 0.55rem;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-top: 0.55rem;
    }

    /* ---------- Rail stats ---------- */
    .gs-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.3rem;
        margin-top: 0.85rem;
        padding-top: 0.8rem;
        border-top: 1px dashed var(--gs-border);
    }

    .gs-stat {
        text-align: center;
        min-width: 0;
    }

    .gs-stat-value {
        font-family: 'Outfit', sans-serif;
        font-size: 0.98rem;
        font-weight: 800;
        color: var(--gs-key);
        line-height: 1.15;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .gs-stat {
        text-align: center;
        min-width: 0;
        overflow: hidden;
    }

    .gs-stat-label {
        font-size: 0.57rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--gs-faint);
        margin-top: 0.12rem;
    }

    /* ---------- Photo actions ---------- */
    .gs-photo-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        flex-wrap: wrap;
        margin-top: 0.8rem;
    }

    .gs-photo-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-family: inherit;
        font-size: 0.76rem;
        font-weight: 700;
        padding: 0.42rem 0.8rem;
        border-radius: 9px;
        cursor: pointer;
        text-decoration: none;
        border: 1.5px solid var(--gs-border-2);
        background: var(--bg-surface);
        color: var(--text-muted);
        transition: all 0.18s ease;
    }

    .gs-photo-btn:hover {
        border-color: var(--gs-cyan);
        color: var(--gs-cyan);
        background: var(--gs-cyan-soft);
    }

    .gs-photo-btn i {
        color: var(--gs-cyan);
    }

    .gs-photo-btn.is-danger {
        color: var(--text-danger);
        border-color: var(--danger-border);
        background: var(--danger-soft);
    }

    .gs-photo-btn.is-danger:hover {
        border-color: var(--danger-color);
        color: var(--text-inverse);
        background: var(--danger-color);
    }

    .gs-photo-btn.is-danger i {
        color: inherit;
    }

    /* ---------- Tab navigation (vertical on desktop, scrollable on mobile) ---------- */
    .gs-tabs-nav {
        background: var(--gs-surface);
        border: 1px solid var(--gs-border);
        border-radius: 18px;
        padding: 0.4rem;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .gs-tab-link {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        width: 100%;
        padding: 0.5rem 0.6rem;
        border: none;
        background: transparent;
        border-radius: 12px;
        cursor: pointer;
        text-align: left;
        font-family: inherit;
        transition: background 0.18s ease;
        position: relative;
    }

    .gs-tab-link:hover {
        background: var(--gs-cyan-soft);
    }

    .gs-tab-link.is-active {
        background: var(--gs-cyan-light);
    }

    .gs-tab-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: var(--bg-subtle);
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        flex-shrink: 0;
        transition: all 0.18s ease;
    }

    .gs-tab-link.is-active .gs-tab-icon {
        background: var(--gs-cyan);
        color: var(--text-inverse);
        box-shadow: 0 3px 10px rgba(0, 153, 255, 0.3);
    }

    .gs-tab-text {
        display: flex;
        flex-direction: column;
        gap: 0.05rem;
        min-width: 0;
        flex: 1;
    }

    .gs-tab-text strong {
        font-size: 0.83rem;
        font-weight: 700;
        color: var(--gs-key);
        line-height: 1.25;
    }

    /* Short mobile-only tab labels; full label shows on desktop, short one below the tablet breakpoint */
    .gs-tab-label-short {
        display: none;
    }

    .gs-tab-link.is-active .gs-tab-text strong {
        color: var(--gs-cyan-hover);
    }

    .gs-tab-text small {
        font-size: 0.69rem;
        color: var(--gs-muted);
        line-height: 1.3;
    }

    .gs-tab-check {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.5rem;
        flex-shrink: 0;
    }

    .gs-tab-check.is-done {
        background: var(--success-soft);
        color: var(--text-success);
    }

    .gs-tab-check.is-todo {
        background: var(--bg-subtle);
        color: var(--text-disabled);
    }

    @media (max-width: 1023.98px) {
        .gs-tabs-nav {
            flex-direction: row;
            overflow-x: auto;
            gap: 0.35rem;
            scrollbar-width: thin;
        }

        .gs-tab-link {
            flex: 0 0 auto;
        }

        .gs-tab-text small,
        .gs-tab-check {
            display: none;
        }
    }

    /* ---------- Panels ---------- */
    .gs-panel {
        background: var(--gs-surface);
        border: 1px solid var(--gs-border);
        border-radius: 18px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .gs-panel[hidden] {
        display: none !important;
    }

    .gs-panel-head {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        flex-wrap: wrap;
        padding: 0.9rem 1.15rem;
        border-bottom: 1px solid var(--gs-border);
        background: linear-gradient(180deg, var(--accent-soft) 0%, var(--bg-surface) 100%);
    }

    .gs-panel-head > div:not(.gs-panel-icon) {
        min-width: 0;
    }

    .gs-panel-icon {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.92rem;
        color: var(--text-inverse);
        flex-shrink: 0;
        background: linear-gradient(135deg, var(--accent-color), var(--accent-color));
    }

    .gs-panel-icon.is-key {
        background: linear-gradient(135deg, var(--text-main), var(--text-muted));
    }

    .gs-panel-title {
        font-family: 'Outfit', sans-serif;
        font-size: 0.98rem;
        font-weight: 800;
        color: var(--gs-key);
        margin: 0;
        line-height: 1.25;
    }

    .gs-panel-desc {
        font-size: 0.78rem;
        color: var(--gs-muted);
        margin: 0.1rem 0 0;
        line-height: 1.4;
    }

    .gs-panel-body {
        padding: 1.05rem 1.15rem;
    }

    /* ---------- Fields ---------- */
    .gs-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.85rem;
    }

    .gs-field {
        margin-bottom: 0.85rem;
        min-width: 0;
    }

    .gs-grid-2 .gs-field {
        margin-bottom: 0;
    }

    .gs-field-full {
        grid-column: 1 / -1;
    }

    .gs-label {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.7rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.38rem;
    }

    .gs-req {
        color: var(--gs-magenta);
        font-size: 0.78rem;
        line-height: 1;
    }

    .gs-input-wrap {
        position: relative;
        display: block;
    }

    .gs-input-icon {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gs-faint);
        font-size: 0.8rem;
        pointer-events: none;
        z-index: 2;
        transition: color 0.18s ease;
    }

    .gs-input-wrap:focus-within .gs-input-icon {
        color: var(--gs-cyan);
    }

    .gs-input {
        width: 100%;
        padding: 0.6rem 0.9rem 0.6rem 2.4rem;
        border: 1.5px solid var(--gs-border-2);
        border-radius: 10px;
        font-size: 0.88rem;
        font-family: inherit;
        color: var(--gs-key);
        background: var(--bg-surface);
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        outline: none;
    }

    .gs-textarea {
        min-height: 2.85rem;
        max-height: 12rem;
        line-height: 1.5;
        padding-top: 0.7rem;
        padding-bottom: 0.7rem;
        resize: vertical;
        field-sizing: content;
        overflow: auto;
    }

    .gs-input-wrap:has(.gs-textarea) .gs-input-icon {
        top: 0.95rem;
        transform: none;
    }

    .gs-input::placeholder {
        color: var(--text-subtle);
    }

    .gs-input:focus {
        border-color: var(--gs-cyan);
        box-shadow: 0 0 0 4px rgba(0, 153, 255, 0.12);
    }

    .gs-input.is-locked {
        background: var(--bg-page) !important;
        border-color: var(--border-color) !important;
        color: var(--text-muted) !important;
        cursor: default !important;
    }

    .gs-input.is-locked:focus {
        border-color: var(--border-color) !important;
        box-shadow: none !important;
    }

    .gs-input:disabled {
        background: var(--bg-page);
        color: var(--text-subtle);
        cursor: not-allowed;
    }

    .gs-select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.85rem center;
        background-size: 0.8em;
        padding-right: 2.35rem;
        cursor: pointer;
    }

    .gs-select:disabled {
        background-color: var(--bg-page);
        color: var(--text-subtle);
        cursor: not-allowed;
    }

    .profile-dropdown-wrap.is-locked {
        pointer-events: none;
        opacity: 0.75;
    }

    /* ---------- Searchable address combobox (mirrors a hidden <select>) ---------- */
    .addr-combo-mirror {
        display: none !important;
    }

    .gs-combo-input {
        padding-right: 2.35rem;
        cursor: pointer;
    }

    .gs-combo-input::placeholder {
        color: var(--gs-faint);
    }

    .gs-combo-input:disabled {
        background: var(--bg-page);
        color: var(--text-subtle);
        cursor: not-allowed;
    }

    .gs-combo-caret {
        position: absolute;
        right: 0.9rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-subtle);
        font-size: 0.8rem;
        pointer-events: none;
        transition: transform 0.18s ease;
    }

    .addr-combo.is-open .gs-combo-caret {
        transform: translateY(-50%) rotate(180deg);
    }

    /* Popup is appended to <body> (outside .gs-profile-page), so it MUST use literal colors, not scoped vars */
    .addr-combo-popup {
        position: fixed;
        z-index: 100000;
        display: none;
        background: var(--bg-surface);
        border: 1px solid var(--border-strong);
        border-radius: 12px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18);
        max-height: 260px;
        overflow-y: auto;
        padding: 0.35rem;
    }

    .addr-combo-popup.is-open {
        display: block;
    }

    .addr-combo-opt {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.65rem;
        border-radius: 8px;
        font-size: 0.85rem;
        color: var(--text-main);
        cursor: pointer;
    }

    .addr-combo-opt:hover,
    .addr-combo-opt.is-active {
        background: var(--accent-soft);
    }

    .addr-combo-opt.is-selected {
        background: var(--accent-color);
        color: var(--text-inverse);
    }

    .addr-combo-opt .fa-check {
        margin-left: auto;
    }

    .addr-combo-empty {
        padding: 0.6rem 0.65rem;
        font-size: 0.82rem;
        color: var(--text-subtle);
    }

    .gs-hint {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.74rem;
        color: var(--gs-faint);
        margin-top: 0.32rem;
        line-height: 1.4;
    }

    .gs-hint i {
        font-size: 0.7rem;
    }

    .field-error {
        color: var(--text-danger);
        font-size: 0.79rem;
        margin-top: 0.35rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    /* ---------- Section divider inside a panel ---------- */
    .gs-divider {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin: 1.6rem 0 1.35rem;
        padding-bottom: 0.7rem;
        border-bottom: 1px dashed var(--gs-border);
    }

    .gs-divider h3 {
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--gs-muted);
        margin: 0;
    }

    .gs-divider i {
        color: var(--gs-magenta);
        font-size: 0.85rem;
    }

    /* ---------- Buttons ---------- */
    .gs-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.52rem 1rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.82rem;
        font-family: inherit;
        line-height: 1.2;
        cursor: pointer;
        border: 1.5px solid transparent;
        text-decoration: none;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
    }

    .gs-btn-sm {
        padding: 0.42rem 0.75rem;
        font-size: 0.76rem;
        border-radius: 9px;
    }

    .gs-btn-primary {
        background: var(--gs-cyan);
        color: var(--text-inverse);
        box-shadow: none;
    }

    .gs-btn-primary:hover {
        background: var(--gs-cyan-hover);
        transform: translateY(-1px);
        box-shadow: none;
    }

    .gs-btn-ghost {
        background: var(--bg-surface);
        color: var(--text-muted);
        border-color: var(--gs-border-2);
    }

    .gs-btn-ghost:hover {
        background: var(--bg-subtle);
        color: var(--gs-key);
        border-color: var(--border-strong);
    }

    .gs-btn-danger {
        background: var(--danger-color);
        color: var(--text-inverse);
        box-shadow: 0 5px 14px rgba(220, 38, 38, 0.25);
    }

    .gs-btn-danger:hover {
        background: var(--danger-color);
        transform: translateY(-1px);
    }

    .gs-btn-danger-soft {
        background: var(--danger-soft);
        color: var(--text-danger);
        border-color: var(--danger-border);
    }

    .gs-btn-danger-soft:hover {
        background: var(--danger-soft);
        border-color: var(--danger-border);
    }

    /* ---------- Sticky save bar ---------- */
    .gs-savebar {
        position: sticky;
        bottom: 0.85rem;
        z-index: 20;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        flex-wrap: wrap;
        margin-top: 0.85rem;
        padding: 0.6rem 0.85rem;
        background: color-mix(in srgb, var(--bg-surface) 94%, transparent);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--gs-border);
        border-radius: 14px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.09);
    }

    .gs-savebar[data-editing="1"] {
        display: flex;
    }

    .gs-savebar.is-hidden-tab {
        display: none !important;
    }

    .gs-savebar-note {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        color: var(--gs-muted);
        font-weight: 600;
    }

    .gs-savebar-note i {
        color: var(--gs-cyan);
    }

    .gs-savebar-actions {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    /* ---------- Security rows (compact single-line layout) ---------- */
    .gs-sec-list {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }

    .gs-sec-row {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        border: 1px solid var(--gs-border);
        border-radius: 14px;
        padding: 0.7rem 0.85rem;
        background: var(--bg-surface);
        transition: border-color 0.18s ease, background 0.18s ease;
    }

    .gs-sec-row:hover {
        border-color: var(--gs-border-2);
    }

    .gs-sec-row.is-danger {
        background: var(--bg-page);
        border-color: var(--danger-border);
    }

    .gs-sec-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
        background: var(--gs-cyan-light);
        color: var(--gs-cyan);
    }

    .gs-sec-row.is-danger .gs-sec-icon {
        background: var(--danger-soft);
        color: var(--text-danger);
    }

    .gs-sec-text {
        flex: 1;
        min-width: 0;
    }

    .gs-sec-title {
        font-size: 0.85rem;
        font-weight: 800;
        color: var(--gs-key);
        margin: 0 0 0.1rem;
        line-height: 1.3;
    }

    .gs-sec-row.is-danger .gs-sec-title {
        color: var(--text-danger);
    }

    .gs-sec-desc {
        font-size: 0.75rem;
        color: var(--gs-muted);
        line-height: 1.4;
        margin: 0;
    }

    .gs-sec-row.is-danger .gs-sec-desc {
        color: var(--text-danger);
    }

    .gs-sec-action {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-shrink: 0;
    }

    /* ---------- Locked state (actions require Edit Profile) ---------- */
    .gs-sec-locked,
    .gs-sec-locked:hover {
        opacity: 0.45;
        filter: grayscale(0.65);
        cursor: not-allowed !important;
        background: var(--bg-subtle) !important;
        border-color: var(--border-color) !important;
        color: var(--text-subtle) !important;
        box-shadow: none !important;
        transform: none !important;
    }

    .gs-lock-ico {
        display: none;
        font-size: 0.72em;
    }

    .gs-sec-locked .gs-lock-ico {
        display: inline-block;
    }

    .gs-sec-locknote {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-left: auto;
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-warning);
        background: var(--warning-soft);
        border: 1px solid var(--warning-border);
        padding: 0.22rem 0.6rem;
        border-radius: 9999px;
        white-space: nowrap;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.18s ease;
    }

    .gs-sec-locknote:hover {
        background: var(--warning-soft);
        border-color: var(--warning-color);
        color: var(--text-warning);
    }

    .gs-sec-locknote.is-unlocked {
        display: none;
    }

    /* ---------- Toast ---------- */
    .profile-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: var(--gs-key, var(--text-main));
        color: var(--text-inverse);
        padding: 0.9rem 1.35rem;
        border-radius: 14px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.28);
        font-size: 0.9rem;
        font-weight: 600;
        display: none;
        align-items: center;
        gap: 0.7rem;
        z-index: 99999;
        animation: gsToastIn 0.3s ease forwards;
        cursor: pointer;
        max-width: 380px;
        line-height: 1.4;
    }

    .profile-toast.toast-success {
        border-left: 4px solid var(--success-color);
    }

    .profile-toast.toast-error {
        border-left: 4px solid var(--danger-color);
    }

    @keyframes gsToastIn {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* ---------- Modals ---------- */
    .gs-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.66);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .gs-modal-card {
        background: var(--bg-surface);
        border-radius: 18px;
        width: 100%;
        max-width: 460px;
        box-shadow: 0 24px 48px -12px rgba(15, 23, 42, 0.3);
        border: 1px solid var(--border-color);
        overflow: hidden;
        animation: gsModalIn 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes gsModalIn {
        from { transform: translateY(12px) scale(0.98); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }

    .gs-modal-head {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-surface);
    }

    .gs-modal-head.is-danger {
        background: var(--danger-soft);
        border-bottom-color: var(--danger-border);
    }

    .gs-modal-head.is-warn {
        background: var(--warning-soft);
        border-bottom-color: var(--warning-border);
    }

    .gs-modal-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        background: var(--danger-soft);
        color: var(--text-danger);
    }

    .gs-modal-head.is-warn .gs-modal-icon {
        background: var(--warning-soft);
        color: var(--text-warning);
    }

    .gs-modal-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        font-weight: 800;
        margin: 0 0 0.15rem;
        color: var(--text-danger);
    }

    .gs-modal-head.is-warn .gs-modal-title {
        color: var(--text-warning);
    }

    .gs-modal-sub {
        font-size: 0.8rem;
        margin: 0;
        color: var(--text-danger);
    }

    .gs-modal-head.is-warn .gs-modal-sub {
        color: var(--text-warning);
    }

    .gs-modal-close {
        margin-left: auto;
        background: none;
        border: none;
        color: var(--text-subtle);
        font-size: 1.1rem;
        cursor: pointer;
        padding: 6px;
        border-radius: 8px;
        transition: all 0.15s ease;
        line-height: 1;
    }

    .gs-modal-close:hover {
        background: rgba(15, 23, 42, 0.06);
        color: var(--text-main);
    }

    .gs-modal-body {
        padding: 1.5rem;
    }

    .gs-modal-text {
        font-size: 0.88rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin: 0 0 1.25rem;
    }

    .gs-alert {
        border-radius: 10px;
        padding: 0.8rem 1rem;
        margin-bottom: 1.15rem;
        font-size: 0.83rem;
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        line-height: 1.5;
    }

    .gs-alert.is-error {
        background: var(--danger-soft);
        border: 1px solid var(--danger-border);
        color: var(--text-danger);
    }

    .gs-alert.is-warn {
        background: var(--warning-soft);
        border: 1px solid var(--warning-border);
        color: var(--text-warning);
    }

    .gs-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 1023.98px) {
        /* Keep the Edit Profile button reachable on every tab. */
        .gs-page-title {
            font-size: 1.1rem;
        }

        .gs-btn-edit {
            width: auto;
            padding: 0.45rem 0.85rem;
            font-size: 0.78rem;
        }

        /* On tablet/mobile the rail becomes a stacked column.
           Keep the profile card + tab nav side by side so the tab nav
           stays visible instead of being pushed far down the page. */
        .gs-rail {
            display: grid;
            grid-template-columns: minmax(0, 260px) minmax(0, 1fr);
            gap: 0.75rem;
            align-items: start;
        }

        .gs-rail-card {
            padding: 0.9rem 0.75rem;
        }

        /* Compact avatar block for the stacked layout */
        .gs-avatar-ring {
            width: 64px;
            height: 64px;
            margin-bottom: 0.55rem;
        }

        .gs-avatar-circle {
            font-size: 1.25rem;
        }

        .gs-rail-name {
            font-size: 0.95rem;
        }

        .gs-rail-email {
            font-size: 0.7rem;
        }

        .gs-badge {
            font-size: 0.56rem;
            padding: 0.16rem 0.45rem;
            margin-top: 0.4rem;
        }

        .gs-stats {
            margin-top: 0.6rem;
            padding-top: 0.6rem;
        }

        .gs-stat-value {
            font-size: 0.85rem;
        }

        .gs-stat-label {
            font-size: 0.52rem;
        }

        .gs-photo-actions {
            margin-top: 0.6rem;
        }

        /* Mobile/tablet: compact segmented bar — icon on top, label centered below, 3 equal columns */
        .gs-tabs-nav {
            flex-direction: row;
            overflow-x: hidden;
            gap: 0.4rem;
            padding: 0.4rem;
        }

        .gs-tab-link {
            flex: 1 1 0;
            width: auto;
            min-width: 0;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.55rem 0.35rem;
            border-radius: 14px;
            text-align: center;
        }

        .gs-tab-icon {
            width: 34px;
            height: 34px;
            font-size: 0.9rem;
        }

        .gs-tab-text {
            flex: none;
            width: 100%;
            align-items: center;
            text-align: center;
        }

        .gs-tab-text strong {
            font-size: 0.72rem;
            line-height: 1.2;
        }

        .gs-tab-label-full {
            display: none;
        }

        .gs-tab-label-short {
            display: inline;
        }

        .gs-tab-text small {
            font-size: 0.65rem;
        }

        .gs-tab-check {
            width: 16px;
            height: 16px;
            font-size: 0.45rem;
        }
    }

    @media (max-width: 767.98px) {
        /* Hide the header Edit button on mobile — use the card button instead */
        #btnToggleProfileEdit {
            display: none !important;
        }

        /* Show the card Edit button on mobile */
        #btnToggleProfileEditMobile {
            display: inline-flex !important;
        }

        /* Remove sticky on mobile — just a normal title */
        .gs-page-head {
            position: static;
            margin-bottom: 0.85rem;
            padding: 0.25rem 0;
            background: transparent;
            box-shadow: none;
            flex-wrap: nowrap;
            align-items: center;
            gap: 0.6rem;
        }

        .gs-page-head > div:first-child {
            flex: 1 1 auto;
            min-width: 0;
        }

        .gs-page-title {
            font-size: 1.05rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gs-btn-edit {
            flex: 0 0 auto;
            width: auto;
            justify-content: center;
            padding: 0.45rem 0.85rem;
        }

        /* Very narrow phones: shrink both so the row still fits. */
        @media (max-width: 380px) {
            .gs-page-title {
                font-size: 0.95rem;
            }

            .gs-btn-edit {
                font-size: 0.72rem;
                padding: 0.4rem 0.65rem;
                gap: 0.35rem;
            }
        }

        /* Single column again — stack the card above the tab nav */
        .gs-rail {
            grid-template-columns: minmax(0, 1fr);
            gap: 0.6rem;
        }

        /* Prevent horizontal overflow of the rail card + stats row */
        .gs-rail-card {
            padding: 0.9rem 0.75rem;
            overflow: hidden;
        }

        .gs-stats {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.25rem;
        }

        .gs-stat {
            min-width: 0;
            overflow: hidden;
        }

        .gs-stat-value {
            font-size: 0.8rem;
        }

        .gs-stat-label {
            font-size: 0.48rem;
            letter-spacing: 0.03em;
        }

        /* Panel header: icon + title share row 1; the lock note and the
           Edit button each take their own full-width row below. */
        .gs-panel-head {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 0.45rem 0.7rem;
            align-items: center;
        }

        .gs-panel-icon {
            grid-column: 1;
            grid-row: 1;
        }

        .gs-panel-head > div:not(.gs-panel-icon) {
            grid-column: 2;
            grid-row: 1;
        }

        /* the lock badge itself opens edit mode */
        .gs-sec-locknote {
            grid-column: 1 / -1;
            grid-row: 2;
            margin-left: 0;
            justify-self: start;
        }

        .gs-grid-2 {
            grid-template-columns: 1fr !important;
            gap: 0.85rem !important;
        }

        .gs-field {
            margin-bottom: 0 !important;
        }

        .gs-panel-head {
            padding: 0.85rem 1rem;
        }

        .gs-panel-body {
            padding: 0.9rem 1rem;
        }

        .gs-sec-row {
            flex-wrap: wrap;
            gap: 0.6rem;
            padding: 0.65rem 0.75rem;
        }

        .gs-sec-icon {
            width: 30px;
            height: 30px;
            font-size: 0.78rem;
        }

        .gs-sec-title {
            font-size: 0.8rem;
        }

        .gs-sec-desc {
            font-size: 0.7rem;
        }

        .gs-sec-locknote {
            margin-left: 0;
            font-size: 0.64rem;
            padding: 0.18rem 0.5rem;
        }

        .gs-sec-action {
            width: 100%;
            justify-content: flex-end;
        }

        .gs-savebar {
            flex-direction: column;
            align-items: stretch;
        }

        .gs-savebar-actions {
            justify-content: flex-end;
        }

        .gs-savebar-actions .gs-btn {
            flex: 1;
        }

        .profile-toast {
            left: 1rem;
            right: 1rem;
            bottom: 1rem;
            max-width: none;
        }
    }
</style>

{{-- Customer drawer / logout nav component (provides the header burger menu behaviour) --}}
<x-customer-sidebar />

<div class="gs-profile-page">

    {{-- ============================ PAGE HEADER ============================ --}}
    <header class="gs-page-head">
        <div>
            <h1 class="gs-page-title">Account Settings &amp; Profile</h1>
        </div>

        {{-- The Edit Profile button is always visible in the page header
             (desktop + mobile). On small screens the header sticks, so it
             never scrolls out of reach. --}}
        <button type="button" id="btnToggleProfileEdit" onclick="toggleProfileEdit()" class="gs-btn-edit"
                title="Click to unlock and edit profile fields">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Edit Profile</span>
        </button>
    </header>

    {{-- ============================ MAIN LAYOUT ============================ --}}
    <div class="gs-layout">

        {{-- ---------------------------- LEFT RAIL ---------------------------- --}}
        <aside class="gs-rail">

            {{-- Account summary card --}}
            <div class="gs-rail-card" style="position: relative;">

                {{-- Edit button inside card — visible on mobile only --}}
                <button type="button" id="btnToggleProfileEditMobile"
                    onclick="toggleProfileEdit()"
                    class="gs-btn-edit"
                    title="Click to unlock and edit profile fields"
                    style="position: absolute; top: 0.85rem; right: 0.85rem; padding: 0.3rem 0.62rem; font-size: 0.7rem; gap: 0.3rem; display: none;">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Edit</span>
                </button>
                <div class="gs-avatar-ring" id="avatar-container-trigger" title="Click to change profile picture">
                    <div class="gs-avatar-circle" id="avatar-circle-display">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="gs-avatar-img" id="avatar-img-element">
                        @else
                            <span id="avatar-initials-element">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        @endif
                        <div class="gs-avatar-overlay">
                            <i class="fa-solid fa-camera"></i>
                            <span>Change</span>
                        </div>
                        <div class="gs-avatar-spinner" id="avatar-upload-spinner">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                        </div>
                    </div>
                    <div class="gs-avatar-badge" title="Change photo">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                </div>

                <div class="gs-rail-name">{{ $user->name }}</div>
                <div class="gs-rail-email">
                    <i class="fa-regular fa-envelope"></i>
                    <span>{{ $user->email }}</span>
                </div>

                <div class="gs-stats">
                    <div class="gs-stat">
                        <div class="gs-stat-value">{{ $totalOrdersCount ?? 0 }}</div>
                        <div class="gs-stat-label">Orders</div>
                    </div>
                    <div class="gs-stat">
                        <div class="gs-stat-value">{{ $unreadNotificationsCount ?? 0 }}</div>
                        <div class="gs-stat-label">Unread</div>
                    </div>
                    <div class="gs-stat">
                        <div class="gs-stat-value">{{ $user->created_at ? $user->created_at->format('M Y') : '—' }}</div>
                        <div class="gs-stat-label">Member</div>
                    </div>
                </div>

                <div class="gs-photo-actions">
                    <button type="button" class="gs-photo-btn" id="btn-trigger-upload" style="display: none;">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload Photo
                    </button>
                    <button type="button" class="gs-photo-btn is-danger" id="btn-trigger-remove" style="display: none;">
                        <i class="fa-regular fa-trash-can"></i> Remove
                    </button>
                </div>
            </div>

            {{-- Section navigation (doubles as the tab switcher) --}}
            <nav class="gs-tabs-nav" role="tablist" aria-label="Account settings sections">
                <button type="button" class="gs-tab-link is-active" id="tab-personal"
                        data-tab-target="personal" role="tab"
                        aria-controls="panel-personal" aria-selected="true">
                    <span class="gs-tab-icon"><i class="fa-solid fa-id-card"></i></span>
                    <span class="gs-tab-text">
                        <strong><span class="gs-tab-label-full">Personal Information</span><span class="gs-tab-label-short">Information</span></strong>
                        <small>Name, email &amp; phone number</small>
                    </span>
                </button>

                <button type="button" class="gs-tab-link" id="tab-address"
                        data-tab-target="address" role="tab"
                        aria-controls="panel-address" aria-selected="false">
                    <span class="gs-tab-icon"><i class="fa-solid fa-location-dot"></i></span>
                    <span class="gs-tab-text">
                        <strong><span class="gs-tab-label-full">Delivery Address</span><span class="gs-tab-label-short">Address</span></strong>
                        <small>Where we deliver your orders</small>
                    </span>
                </button>

                <button type="button" class="gs-tab-link" id="tab-security"
                        data-tab-target="security" role="tab"
                        aria-controls="panel-security" aria-selected="false">
                    <span class="gs-tab-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    <span class="gs-tab-text">
                        <strong><span class="gs-tab-label-full">Account Security</span><span class="gs-tab-label-short">Security</span></strong>
                        <small>Password &amp; account deletion</small>
                    </span>
                </button>
            </nav>
        </aside>

        {{-- ---------------------------- RIGHT PANELS ---------------------------- --}}
        <div class="gs-panels">

            <form id="profile-edit-form" action="{{ route('customer.profile.update') }}" method="POST">
                @csrf

                @php
                    // Backfill first/middle/last from the combined name for legacy accounts
                    // that predate the name-parts feature, so the edit fields aren't blank.
                    $nameTokens = array_values(array_filter(preg_split('/\s+/', trim((string) $user->name))));
                    $fallbackFirst = $user->first_name ?: ($nameTokens[0] ?? '');
                    $fallbackLast = $user->last_name ?: (count($nameTokens) > 1 ? $nameTokens[count($nameTokens) - 1] : '');
                    $fallbackMiddle = $user->middle_name ?: (count($nameTokens) > 2 ? implode(' ', array_slice($nameTokens, 1, -1)) : '');
                @endphp

                {{-- =========================== TAB: PERSONAL =========================== --}}
                <section class="gs-panel" data-tab-panel="personal" id="panel-personal" role="tabpanel" aria-labelledby="tab-personal">
                    <div class="gs-panel-head">
                        <div class="gs-panel-icon"><i class="fa-solid fa-id-card"></i></div>
                        <div>
                            <h2 class="gs-panel-title">Personal Information</h2>
                            <p class="gs-panel-desc">How we identify and reach you about your orders.</p>
                        </div>
                    </div>

                    <div class="gs-panel-body">
                        <div class="gs-grid-2">
                            <div class="gs-field">
                                <label class="gs-label" for="profile-first-name">First Name</label>
                                <div class="gs-input-wrap">
                                    <i class="fa-regular fa-user gs-input-icon"></i>
                                    <input type="text" id="profile-first-name" name="first_name" class="gs-input" value="{{ old('first_name', $fallbackFirst) }}" required>
                                </div>
                                @if($errors->has('first_name'))
                                    <span class="field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('first_name') }}</span>
                                @endif
                            </div>

                            <div class="gs-field">
                                <label class="gs-label" for="profile-middle-name">Middle Name <span style="color: var(--gs-muted); font-weight: 600;">(optional)</span></label>
                                <div class="gs-input-wrap">
                                    <i class="fa-regular fa-user gs-input-icon"></i>
                                    <input type="text" id="profile-middle-name" name="middle_name" class="gs-input" value="{{ old('middle_name', $fallbackMiddle) }}">
                                </div>
                                @if($errors->has('middle_name'))
                                    <span class="field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('middle_name') }}</span>
                                @endif
                            </div>

                            <div class="gs-field">
                                <label class="gs-label" for="profile-last-name">Last Name</label>
                                <div class="gs-input-wrap">
                                    <i class="fa-regular fa-user gs-input-icon"></i>
                                    <input type="text" id="profile-last-name" name="last_name" class="gs-input" value="{{ old('last_name', $fallbackLast) }}" required>
                                </div>
                                @if($errors->has('last_name'))
                                    <span class="field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('last_name') }}</span>
                                @endif
                            </div>

                            <div class="gs-field">
                                <label class="gs-label" for="profile-email">Email Address</label>
                                <div class="gs-input-wrap">
                                    <i class="fa-regular fa-envelope gs-input-icon"></i>
                                    <input type="email" id="profile-email" class="gs-input is-locked" value="{{ $user->email }}" disabled>
                                </div>
                                <span class="gs-hint">
                                    <i class="fa-solid fa-lock"></i> Email cannot be changed here.
                                </span>
                            </div>
                        </div>

                        <div class="gs-field" style="margin-top: 1.15rem;">
                            <label class="gs-label" for="profile-contact">Phone Number</label>
                            <div class="gs-input-wrap">
                                <i class="fa-solid fa-mobile-screen-button gs-input-icon"></i>
                                <input type="tel"
                                       id="profile-contact"
                                       name="contact"
                                       class="gs-input"
                                       value="{{ old('contact', $user->contact ?: $user->phone) }}"
                                       placeholder="09XXXXXXXXX"
                                       maxlength="11"
                                       inputmode="numeric"
                                       pattern="09[0-9]{9}"
                                       autocomplete="tel">
                            </div>
                            @if($errors->has('contact'))
                                <span class="field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('contact') }}</span>
                            @else
                                <span class="gs-hint">
                                    <i class="fa-solid fa-circle-info"></i> Philippine mobile format — 09XXXXXXXXX.
                                </span>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- ============================ TAB: ADDRESS ============================ --}}
                <section class="gs-panel" data-tab-panel="address" id="panel-address" role="tabpanel" aria-labelledby="tab-address" hidden>
                    <div class="gs-panel-head">
                        <div class="gs-panel-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div>
                            <h2 class="gs-panel-title">Address</h2>
                        </div>
                    </div>

                    <div class="gs-panel-body">
                        <div class="gs-grid-2">
                            <div class="gs-field">
                                <label class="gs-label" for="profile-region-combo">Region <span class="gs-req">*</span></label>
                                <div class="gs-input-wrap profile-dropdown-wrap addr-combo" data-combo-for="profile-region">
                                    <i class="fa-solid fa-map gs-input-icon"></i>
                                    <input type="text" id="profile-region-combo" class="gs-input gs-combo-input" placeholder="Select Region" autocomplete="off" disabled>
                                    <i class="fa-solid fa-chevron-down gs-combo-caret"></i>
                                    <select id="profile-region" name="region" class="addr-combo-mirror">
                                        <option value="" disabled selected>Select Region</option>
                                    </select>
                                </div>
                                @if($errors->has('region'))
                                    <span class="field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('region') }}</span>
                                @endif
                            </div>

                            <div class="gs-field">
                                <label class="gs-label" for="profile-province-combo">Province <span class="gs-req">*</span></label>
                                <div class="gs-input-wrap profile-dropdown-wrap addr-combo" data-combo-for="profile-province">
                                    <i class="fa-solid fa-map-location-dot gs-input-icon"></i>
                                    <input type="text" id="profile-province-combo" class="gs-input gs-combo-input" placeholder="Select Province" autocomplete="off" disabled>
                                    <i class="fa-solid fa-chevron-down gs-combo-caret"></i>
                                    <select id="profile-province" name="province" class="addr-combo-mirror" disabled>
                                        <option value="" disabled selected>Select Province</option>
                                    </select>
                                </div>
                                @if($errors->has('province'))
                                    <span class="field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('province') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="gs-grid-2" style="margin-top: 1.15rem;">
                            <div class="gs-field">
                                <label class="gs-label" for="profile-city-combo">City / Municipality <span class="gs-req">*</span></label>
                                <div class="gs-input-wrap profile-dropdown-wrap addr-combo" data-combo-for="profile-city">
                                    <i class="fa-solid fa-city gs-input-icon"></i>
                                    <input type="text" id="profile-city-combo" class="gs-input gs-combo-input" placeholder="Select City / Municipality" autocomplete="off" disabled>
                                    <i class="fa-solid fa-chevron-down gs-combo-caret"></i>
                                    <select id="profile-city" name="city" class="addr-combo-mirror" disabled>
                                        <option value="" disabled selected>Select City</option>
                                    </select>
                                </div>
                                @if($errors->has('city'))
                                    <span class="field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('city') }}</span>
                                @endif
                            </div>

                            <div class="gs-field">
                                <label class="gs-label" for="profile-barangay-combo">Barangay <span class="gs-req">*</span></label>
                                <div class="gs-input-wrap profile-dropdown-wrap addr-combo" data-combo-for="profile-barangay">
                                    <i class="fa-solid fa-house-chimney gs-input-icon"></i>
                                    <input type="text" id="profile-barangay-combo" class="gs-input gs-combo-input" placeholder="Select Barangay" autocomplete="off" disabled>
                                    <i class="fa-solid fa-chevron-down gs-combo-caret"></i>
                                    <select id="profile-barangay" name="barangay" class="addr-combo-mirror" disabled>
                                        <option value="" disabled selected>Select Barangay</option>
                                    </select>
                                </div>
                                @if($errors->has('barangay'))
                                    <span class="field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('barangay') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="gs-field" style="margin-top: 1.15rem;">
                            <label class="gs-label" for="profile-street">Street / House / Unit / Building</label>
                            <div class="gs-input-wrap">
                                <i class="fa-solid fa-location-dot gs-input-icon"></i>
                                <textarea id="profile-street" name="street" class="gs-input gs-textarea" rows="2"
                                    placeholder="e.g. 123 Mabini St., Unit 4B, Green Heights Subd.">{{ old('street', $user->street) }}</textarea>
                            </div>
                            @if($errors->has('street'))
                                <span class="field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('street') }}</span>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- ======================= STICKY SAVE / CANCEL BAR ======================= --}}
                <div class="gs-savebar" id="profile-bottom-actions" data-editing="0">
                    <span class="gs-savebar-note">
                        <i class="fa-solid fa-circle-info"></i> Changes apply after you save.
                    </span>
                    <div class="gs-savebar-actions" id="profile-save-actions">
                        <button type="button" onclick="cancelProfileEdit()" class="gs-btn gs-btn-ghost">
                            <i class="fa-solid fa-rotate-left"></i> Cancel
                        </button>
                        <button type="submit" class="gs-btn gs-btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>

            {{-- =========================== TAB: SECURITY =========================== --}}
            <section class="gs-panel" data-tab-panel="security" id="panel-security" role="tabpanel" aria-labelledby="tab-security" hidden>
                <div class="gs-panel-head">
                    <div class="gs-panel-icon is-key"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <h2 class="gs-panel-title">Account Security</h2>
                        <p class="gs-panel-desc">Manage your password and account data.</p>
                    </div>
                </div>

                <div class="gs-panel-body">
                    <div class="gs-sec-list">

                        {{-- Password --}}
                        <div class="gs-sec-row">
                            <span class="gs-sec-icon"><i class="fa-solid fa-key"></i></span>
                            <div class="gs-sec-text">
                                <h3 class="gs-sec-title">Password &amp; Login</h3>
                                <p class="gs-sec-desc">One-time code sent to <strong>{{ $user->email }}</strong> before the change applies.</p>
                            </div>
                            <div class="gs-sec-action">
                                <a href="{{ route('password.request') }}"
                                   class="gs-btn gs-btn-ghost gs-btn-sm"
                                   data-edit-only
                                   title="Click Edit Profile to change your password"
                                   onclick="if (!guardEditOnlyAction(event)) return false;">
                                    <i class="fa-solid fa-key"></i> Change Password
                                    <i class="fa-solid fa-lock gs-lock-ico"></i>
                                </a>
                            </div>
                        </div>

                        {{-- Danger zone --}}
                        <div class="gs-sec-row is-danger">
                            <span class="gs-sec-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                            <div class="gs-sec-text">
                                <h3 class="gs-sec-title">Delete Account</h3>
                                <p class="gs-sec-desc">Erases your account, orders and files permanently. Cannot be undone.</p>
                            </div>
                            <div class="gs-sec-action">
                                <button type="button"
                                        class="gs-btn gs-btn-danger-soft gs-btn-sm"
                                        id="btn-delete-account"
                                        data-edit-only
                                        title="Click Edit Profile to delete your account"
                                        onclick="if (!guardEditOnlyAction(event)) return; openDeleteAccountModal();">
                                    <i class="fa-solid fa-trash-can"></i> Delete
                                    <i class="fa-solid fa-lock gs-lock-ico"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

{{-- ========================= FLOATING TOAST (SA GILID) ========================= --}}
<div id="profile-toast-box" class="profile-toast" onclick="this.style.display='none'" title="Click to dismiss">
    <i class="fa-solid fa-circle-check" id="toast-icon" style="color: var(--text-success); font-size: 1.15rem;"></i>
    <span id="toast-text">Success message</span>
    <i class="fa-solid fa-xmark" style="font-size: 0.85rem; opacity: 0.6; margin-left: 0.4rem; cursor: pointer;"></i>
</div>

{{-- ======================= DELETE ACCOUNT CONFIRMATION MODAL ======================= --}}
<div id="deleteAccountModal"
    class="gs-modal-backdrop"
    onclick="if(event.target === this) closeDeleteAccountModal()">
    <div class="gs-modal-card">

        <div class="gs-modal-head is-danger">
            <div class="gs-modal-icon">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <div>
                <h3 class="gs-modal-title">Delete Account Permanently</h3>
                <p class="gs-modal-sub">This action cannot be reversed.</p>
            </div>
            <button type="button" onclick="closeDeleteAccountModal()" class="gs-modal-close" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="gs-modal-body">
            <p class="gs-modal-text">
                You are about to <strong style="color: var(--text-danger);">permanently delete your Grafika South account</strong>.
                All your orders, profile data, and uploaded files will be removed forever.
            </p>

            @if($errors->has('delete_password'))
                <div class="gs-alert is-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first('delete_password') }}</span>
                </div>
            @endif

            <form id="deleteAccountForm" action="{{ route('customer.profile.delete') }}" method="POST">
                @csrf
                @method('DELETE')

                <div style="margin-bottom: 1.25rem;">
                    <label for="delete_password_input" style="display: block; font-size: 0.8rem; font-weight: 800; color: var(--text-muted); margin-bottom: 0.45rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        Confirm with your current password <span class="gs-req">*</span>
                    </label>
                    <div class="gs-input-wrap">
                        <i class="fa-solid fa-lock gs-input-icon"></i>
                        <input type="password"
                            name="password"
                            id="delete_password_input"
                            class="gs-input"
                            placeholder="Enter your account password..."
                            required>
                    </div>
                </div>

                <div class="gs-alert is-warn" style="margin-bottom: 1.4rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Once deleted, your account <strong>cannot be recovered</strong>. Please make sure you want to proceed.</span>
                </div>

                <div class="gs-modal-actions">
                    <button type="button" onclick="closeDeleteAccountModal()" class="gs-btn gs-btn-ghost">
                        Cancel
                    </button>
                    <button type="submit" class="gs-btn gs-btn-danger">
                        <i class="fa-solid fa-trash-can"></i> Yes, Delete My Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ======================= REMOVE PROFILE PICTURE MODAL ======================= --}}
<div id="deleteAvatarModal"
    class="gs-modal-backdrop"
    onclick="if(event.target === this) closeDeleteAvatarModal()">
    <div class="gs-modal-card">

        <div class="gs-modal-head is-warn">
            <div class="gs-modal-icon">
                <i class="fa-solid fa-camera-rotate"></i>
            </div>
            <div>
                <h3 class="gs-modal-title">Remove Profile Picture</h3>
                <p class="gs-modal-sub">Revert to default letter initials</p>
            </div>
            <button type="button" onclick="closeDeleteAvatarModal()" class="gs-modal-close" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="gs-modal-body">
            <p class="gs-modal-text">
                Are you sure you want to remove your current profile picture? Your profile will revert
                to displaying your default letter initials
                (<strong>{{ strtoupper(substr($user->name, 0, 1)) }}</strong>).
            </p>

            <div class="gs-modal-actions">
                <button type="button" onclick="closeDeleteAvatarModal()" class="gs-btn gs-btn-ghost">
                    Cancel
                </button>
                <button type="button" id="btn-confirm-delete-avatar" class="gs-btn gs-btn-danger" style="background:var(--warning-color); box-shadow:0 5px 14px rgba(234,88,12,.25);">
                    <i class="fa-regular fa-trash-can"></i> Yes, Remove Photo
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden avatar upload form --}}
<form id="avatar-hidden-form" action="{{ route('customer.profile.avatar.update') }}" method="POST" enctype="multipart/form-data" style="display: none;">
    @csrf
    <input type="file" id="avatar-file-input" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif">
</form>

@push('scripts')
<script>
// ─────────────────────────────────────────────────────────────────────────────
// TOAST NOTIFICATION (SA GILID)
// ─────────────────────────────────────────────────────────────────────────────
let _toastTimer = null;
function showToast(message, isError = false) {
    const toastBox  = document.getElementById('profile-toast-box');
    const toastText = document.getElementById('toast-text');
    const toastIcon = document.getElementById('toast-icon');
    if (!toastBox || !toastText) return;
    if (_toastTimer) clearTimeout(_toastTimer);
    toastText.textContent = message;
    if (isError) {
        toastBox.className = 'profile-toast toast-error';
        if (toastIcon) {
            toastIcon.className = 'fa-solid fa-triangle-exclamation';
            toastIcon.style.color = '#E11930';
        }
    } else {
        toastBox.className = 'profile-toast toast-success';
        if (toastIcon) {
            toastIcon.className = 'fa-solid fa-circle-check';
            toastIcon.style.color = '#10B981';
        }
    }
    toastBox.style.display = 'inline-flex';
    _toastTimer = setTimeout(() => {
        toastBox.style.display = 'none';
    }, 4500);
}

// ─────────────────────────────────────────────────────────────────────────────
// TAB SYSTEM (Account Hub sections)
// ─────────────────────────────────────────────────────────────────────────────
const GS_TABS = ['personal', 'address', 'security'];

function activateTab(key, opts = {}) {
    if (!GS_TABS.includes(key)) key = 'personal';

    document.querySelectorAll('[data-tab-target]').forEach(btn => {
        const active = btn.dataset.tabTarget === key;
        btn.classList.toggle('is-active', active);
        btn.setAttribute('aria-selected', active ? 'true' : 'false');
        btn.setAttribute('tabindex', active ? '0' : '-1');
    });

    document.querySelectorAll('[data-tab-panel]').forEach(panel => {
        const active = panel.dataset.tabPanel === key;
        panel.hidden = !active;
        panel.classList.toggle('is-active', active);
    });

    // The save bar only makes sense for the editable profile sections.
    const savebar = document.getElementById('profile-bottom-actions');
    if (savebar) {
        savebar.classList.toggle('is-hidden-tab', key === 'security');
    }

    if (!opts.fromHistory) {
        try { history.replaceState(null, '', '#' + key); } catch (e) { /* ignore */ }
    }

    document.dispatchEvent(new CustomEvent('gs:tabchange', { detail: { tab: key } }));
}

function initTabSystem() {
    document.querySelectorAll('[data-tab-target]').forEach(btn => {
        btn.addEventListener('click', () => activateTab(btn.dataset.tabTarget));
        btn.addEventListener('keydown', (e) => {
            if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
            e.preventDefault();
            const idx = GS_TABS.indexOf(btn.dataset.tabTarget);
            const next = e.key === 'ArrowDown'
                ? (idx + 1) % GS_TABS.length
                : (idx - 1 + GS_TABS.length) % GS_TABS.length;
            activateTab(GS_TABS[next]);
            const target = document.querySelector('[data-tab-target="' + GS_TABS[next] + '"]');
            if (target) target.focus();
        });
    });

    // Which tab should open on load?
    let initial = 'personal';
    const hash = (window.location.hash || '').replace('#', '');
    if (GS_TABS.includes(hash)) {
        initial = hash;
    } else {
        const addressError = {{ $errors->has('region') || $errors->has('province') || $errors->has('city') || $errors->has('barangay') || $errors->has('street') ? 'true' : 'false' }};
        const personalError = {{ $errors->has('first_name') || $errors->has('last_name') || $errors->has('middle_name') || $errors->has('contact') ? 'true' : 'false' }};
        if (addressError) initial = 'address';
        else if (personalError) initial = 'personal';
    }

    activateTab(initial, { fromHistory: true });
}

// ─────────────────────────────────────────────────────────────────────────────
// PROFILE SAFE-EDIT TOGGLE SYSTEM
// ─────────────────────────────────────────────────────────────────────────────
let isProfileEditMode = false;

const initialProfileValues = {
    firstName: @json(old('first_name', $fallbackFirst)),
    middleName: @json(old('middle_name', $fallbackMiddle)),
    lastName: @json(old('last_name', $fallbackLast)),
    contact: @json(old('contact', $user->contact ?: $user->phone)),
    street: @json(old('street', $user->street))
};

// Actions marked with data-edit-only are inert until Edit Profile is enabled.
function guardEditOnlyAction(e) {
    if (isProfileEditMode) return true;
    if (e) e.preventDefault();
    showToast('Click "Edit Profile" first to manage account security.', true);
    return false;
}

function refreshEditOnlyState(enable) {
    document.querySelectorAll('[data-edit-only]').forEach(el => {
        el.classList.toggle('gs-sec-locked', !enable);
        if (enable) {
            el.removeAttribute('aria-disabled');
        } else {
            el.setAttribute('aria-disabled', 'true');
        }
    });

    const lockNote = document.getElementById('security-locknote');
    if (lockNote) lockNote.classList.toggle('is-unlocked', enable);
}

function setProfileEditMode(enable) {
    isProfileEditMode = enable;
    const nameInputs = [
        document.getElementById('profile-first-name'),
        document.getElementById('profile-middle-name'),
        document.getElementById('profile-last-name')
    ].filter(Boolean);
    const contactInput = document.getElementById('profile-contact');
    const streetInput = document.getElementById('profile-street');
    const dropdownWraps = document.querySelectorAll('.profile-dropdown-wrap');
    const regionSelect = document.getElementById('profile-region');
    const provinceSelect = document.getElementById('profile-province');
    const citySelect = document.getElementById('profile-city');
    const barangaySelect = document.getElementById('profile-barangay');

    const saveActions = document.getElementById('profile-save-actions');
    const savebar = document.getElementById('profile-bottom-actions');
    const topBtns = [
        document.getElementById('btnToggleProfileEdit'),
        document.getElementById('btnToggleProfileEditMobile')
    ].filter(Boolean);
    const uploadBtn = document.getElementById('btn-trigger-upload');
    const removeBtn2 = document.getElementById('btn-trigger-remove');
    const avatarBox = document.getElementById('avatar-container-trigger');
    const hasImg = !!document.getElementById('avatar-img-element');

    if (enable) {
        // Unlock inputs
        nameInputs.forEach(el => {
            el.readOnly = false;
            el.classList.remove('is-locked');
        });
        if (contactInput) {
            contactInput.readOnly = false;
            contactInput.classList.remove('is-locked');
            if (!contactInput.value || !contactInput.value.startsWith('09')) {
                let digits = (contactInput.value || '').replace(/\D/g, '');
                if (digits.startsWith('639')) digits = '09' + digits.slice(3);
                else if (digits.startsWith('9')) digits = '09' + digits.slice(1);
                else digits = '09' + digits.replace(/^0*/, '');
                contactInput.value = digits.slice(0, 11);
            }
        }
        if (streetInput) {
            streetInput.readOnly = false;
            streetInput.classList.remove('is-locked');
        }

        dropdownWraps.forEach(wrap => wrap.classList.remove('is-locked'));
        document.querySelectorAll('.gs-combo-input').forEach(i => { i.disabled = false; });
        if (regionSelect) regionSelect.disabled = false;
        if (provinceSelect && provinceSelect.options.length > 1) provinceSelect.disabled = false;
        if (citySelect && citySelect.options.length > 1) citySelect.disabled = false;
        if (barangaySelect && barangaySelect.options.length > 1) barangaySelect.disabled = false;

        if (savebar) savebar.dataset.editing = '1';
        if (uploadBtn) uploadBtn.style.display = 'inline-flex';
        if (removeBtn2) removeBtn2.style.display = hasImg ? 'inline-flex' : 'none';
        if (avatarBox) avatarBox.classList.add('is-editable');

        topBtns.forEach(btn => {
            const isMobileBtn = btn.id === 'btnToggleProfileEditMobile';
            btn.innerHTML = '<i class="fa-solid fa-xmark"></i> <span>' + (isMobileBtn ? 'Cancel' : 'Cancel Edit') + '</span>';
            btn.classList.add('is-editing');
            btn.title = 'Cancel editing and lock fields';
        });

        // Focus the first editable field of the ACTIVE panel (not a hidden one).
        const activePanel = document.querySelector('[data-tab-panel].is-active');
        const firstEditable = activePanel
            ? activePanel.querySelector('input:not([disabled]):not([type="hidden"]), select:not([disabled])')
            : null;
        if (firstEditable) firstEditable.focus();
    } else {
        // Lock inputs
        nameInputs.forEach(el => {
            el.readOnly = true;
            el.classList.add('is-locked');
        });
        if (contactInput) {
            contactInput.readOnly = true;
            contactInput.classList.add('is-locked');
        }
        if (streetInput) {
            streetInput.readOnly = true;
            streetInput.classList.add('is-locked');
        }

        dropdownWraps.forEach(wrap => wrap.classList.add('is-locked'));
        document.querySelectorAll('.gs-combo-input').forEach(i => { i.disabled = true; });
        if (regionSelect) regionSelect.disabled = true;
        if (provinceSelect) provinceSelect.disabled = true;
        if (citySelect) citySelect.disabled = true;
        if (barangaySelect) barangaySelect.disabled = true;

        if (savebar) savebar.dataset.editing = '0';
        if (uploadBtn) uploadBtn.style.display = 'none';
        if (removeBtn2) removeBtn2.style.display = 'none';
        if (avatarBox) avatarBox.classList.remove('is-editable');

        topBtns.forEach(btn => {
            const isMobileBtn = btn.id === 'btnToggleProfileEditMobile';
            btn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> <span>' + (isMobileBtn ? 'Edit' : 'Edit Profile') + '</span>';
            btn.classList.remove('is-editing');
            btn.title = 'Click to unlock and edit profile fields';
        });
    }

    // Security actions follow the same Edit lock as the form fields.
    refreshEditOnlyState(enable);
}

function toggleProfileEdit() {
    if (isProfileEditMode) {
        cancelProfileEdit();
    } else {
        setProfileEditMode(true);
    }
}

function cancelProfileEdit() {
    const firstNameInput = document.getElementById('profile-first-name');
    const middleNameInput = document.getElementById('profile-middle-name');
    const lastNameInput = document.getElementById('profile-last-name');
    const contactInput = document.getElementById('profile-contact');
    const streetInput = document.getElementById('profile-street');
    if (firstNameInput) firstNameInput.value = initialProfileValues.firstName || '';
    if (middleNameInput) middleNameInput.value = initialProfileValues.middleName || '';
    if (lastNameInput) lastNameInput.value = initialProfileValues.lastName || '';
    if (contactInput) contactInput.value = initialProfileValues.contact || '';
    if (streetInput) streetInput.value = initialProfileValues.street || '';

    setProfileEditMode(false);
}

// ─────────────────────────────────────────────────────────────────────────────
// MODALS
// ─────────────────────────────────────────────────────────────────────────────
function openDeleteAccountModal() {
    const modal = document.getElementById('deleteAccountModal');
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        const input = document.getElementById('delete_password_input');
        if (input) input.focus();
    }, 250);
}

function closeDeleteAccountModal() {
    const modal = document.getElementById('deleteAccountModal');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
    const input = document.getElementById('delete_password_input');
    if (input) input.value = '';
}

function openDeleteAvatarModal() {
    const modal = document.getElementById('deleteAvatarModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeDeleteAvatarModal() {
    const modal = document.getElementById('deleteAvatarModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeDeleteAccountModal();
        closeDeleteAvatarModal();
    }
});

// Auto-open modal if validation returned a delete_password error
@if($errors->has('delete_password') || session('show_delete_modal'))
document.addEventListener('DOMContentLoaded', function () {
    openDeleteAccountModal();
});
@endif

// ─────────────────────────────────────────────────────────────────────────────
// FORM VALIDATION ACROSS TABS
// Required fields that live inside a hidden tab are barred from constraint
// validation by the browser, so we temporarily reveal every panel, find the
// first invalid control, then jump the user to the tab that owns it.
// ─────────────────────────────────────────────────────────────────────────────
function findFirstInvalidField() {
    const panels = Array.from(document.querySelectorAll('[data-tab-panel]'));
    const previousHidden = panels.map(p => p.hidden);

    // Reveal all panels so hidden required fields can be validated too.
    panels.forEach(p => { p.hidden = false; });

    let found = null;
    for (const panel of panels) {
        if (panel.dataset.tabPanel === 'security') continue;
        const controls = Array.from(panel.querySelectorAll('input, select, textarea'));
        for (const control of controls) {
            if (!control.willValidate) continue;
            if (!control.checkValidity()) {
                found = { tab: panel.dataset.tabPanel, el: control };
                break;
            }
        }
        if (found) break;
    }

    // Restore the original visibility.
    panels.forEach((panel, i) => { panel.hidden = previousHidden[i]; });

    return found;
}

// ─────────────────────────────────────────────────────────────────────────────
// BOOT
// ─────────────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    initTabSystem();

    const form = document.getElementById('profile-edit-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            // Locked mode — never submit.
            if (!isProfileEditMode) {
                e.preventDefault();
                return;
            }

            const invalid = findFirstInvalidField();
            if (invalid) {
                e.preventDefault();
                activateTab(invalid.tab);
                setTimeout(() => {
                    invalid.el.focus();
                    invalid.el.reportValidity();
                }, 30);
                return;
            }

            // Re-enable selects right before submit so values are included in POST
            const selects = form.querySelectorAll('select');
            selects.forEach(s => s.disabled = false);
        });
    }

    @if($errors->any() && !$errors->has('delete_password') && !$errors->has('avatar'))
        setProfileEditMode(true);
    @else
        setProfileEditMode(false);
    @endif

    {{-- Corner Toast Flash Notifications (Sa Gilid) --}}
    @if(session('success'))
        showToast(@json(session('success')));
    @endif
    @if(session('error'))
        showToast(@json(session('error')), true);
    @endif
    @if(session('warning'))
        showToast(@json(session('warning')), true);
    @endif
    @if($errors->has('avatar'))
        showToast(@json($errors->first('avatar')), true);
    @endif
});

// ─────────────────────────────────────────────────────────────────────────────
// PHILIPPINE MOBILE NUMBER ENFORCEMENT (Starts with 09, 11 digits max, no letters)
// ─────────────────────────────────────────────────────────────────────────────
(function () {
    const phoneInput = document.getElementById('profile-contact');
    if (!phoneInput) return;

    function sanitizeToPhilippineMobile(val) {
        if (!val) return '09';
        let digits = String(val).replace(/\D/g, '');
        if (!digits) return '09';
        if (digits.startsWith('639')) {
            digits = '09' + digits.substring(3);
        } else if (digits.startsWith('9')) {
            digits = '09' + digits.substring(1);
        } else if (!digits.startsWith('09')) {
            digits = '09' + digits.replace(/^0*/, '');
        }
        return digits.slice(0, 11);
    }

    // Sanitize existing value on load
    if (phoneInput.value) {
        phoneInput.value = sanitizeToPhilippineMobile(phoneInput.value);
    }

    phoneInput.addEventListener('keydown', function (e) {
        if (!isProfileEditMode || this.readOnly) return;

        // Allow navigation, modifier shortcuts (Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+Z, etc.)
        const navKeys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Enter', 'Home', 'End'];
        if (navKeys.includes(e.key) || e.ctrlKey || e.metaKey || e.altKey) {
            return;
        }

        // Handle Backspace / Delete
        if (e.key === 'Backspace') {
            if (this.selectionStart === 0 && this.selectionEnd >= 2) {
                e.preventDefault();
                this.value = '09' + this.value.substring(this.selectionEnd);
                this.setSelectionRange(2, 2);
                return;
            }
            if (this.selectionStart <= 2 && this.selectionEnd <= 2) {
                e.preventDefault();
                return;
            }
            return;
        }

        if (e.key === 'Delete') {
            if (this.selectionStart < 2) {
                e.preventDefault();
                return;
            }
            return;
        }

        // Block all non-numeric characters (letters, symbols, spaces)
        if (!/^[0-9]$/.test(e.key)) {
            e.preventDefault();
            return;
        }

        // Limit to 11 digits
        const digits = this.value.replace(/\D/g, '');
        if (digits.length >= 11 && this.selectionStart === this.selectionEnd) {
            e.preventDefault();
        }
    });

    phoneInput.addEventListener('input', function () {
        if (!isProfileEditMode || this.readOnly) return;
        const curPos = this.selectionStart;
        const formatted = sanitizeToPhilippineMobile(this.value);
        this.value = formatted;
        if (curPos < 2) {
            this.setSelectionRange(2, 2);
        }
    });

    phoneInput.addEventListener('focus', function () {
        if (!isProfileEditMode || this.readOnly) return;
        if (!this.value || this.value.trim() === '' || !this.value.startsWith('09')) {
            this.value = sanitizeToPhilippineMobile(this.value);
        }
        setTimeout(() => {
            if (this.selectionStart < 2) {
                this.setSelectionRange(2, 2);
            }
        }, 10);
    });

    phoneInput.addEventListener('paste', function (e) {
        if (!isProfileEditMode || this.readOnly) return;
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text') || '';
        this.value = sanitizeToPhilippineMobile(pasted);
    });
})();

// ─────────────────────────────────────────────────────────────────────────────
// PHILIPPINE ADDRESS CASCADE (Region → Province → City → Barangay)
// ─────────────────────────────────────────────────────────────────────────────
(function () {
    const regionSelect   = document.getElementById('profile-region');
    const provinceSelect = document.getElementById('profile-province');
    const citySelect     = document.getElementById('profile-city');
    const barangaySelect = document.getElementById('profile-barangay');

    if (!regionSelect || !provinceSelect || !citySelect || !barangaySelect) return;

    const userRegion   = @json(old('region', $user->region));
    const userProvince = @json(old('province', $user->province));
    const userCity     = @json(old('city', $user->city));
    const userBarangay = @json(old('barangay', $user->barangay));

    let currentRegionCode   = null;
    let currentProvinceCode = null;
    let currentCityName     = null;

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
            regionSelect.disabled = !isProfileEditMode;

            if (userRegion) {
                const match = regions.find(r => r.code === userRegion || r.name === userRegion);
                const code = match ? match.code : userRegion;
                regionSelect.value = code;
                currentRegionCode = code;
                if (code) await loadProvinces(code, userProvince);
            }
            document.dispatchEvent(new Event('address:sync'));
        } catch (err) {
            console.error('Failed to load regions:', err);
        }
    }

    async function loadProvinces(regionCode, preselectProvince) {
        resetSelect(provinceSelect, 'Select Province');
        resetSelect(citySelect, 'Select City');
        resetSelect(barangaySelect, 'Select Barangay');

        currentRegionCode = regionCode;
        currentProvinceCode = null;
        currentCityName = null;

        try {
            const res = await fetch(`{{ url('/') }}/api/address/regions/${encodeURIComponent(regionCode)}/provinces`);
            const provinces = await res.json();
            provinceSelect.innerHTML = buildOptions(provinces, 'code', 'name', 'Select Province');
            provinceSelect.disabled = !isProfileEditMode;

            if (preselectProvince) {
                const match = provinces.find(p => p.code === preselectProvince || p.name === preselectProvince);
                const code = match ? match.code : preselectProvince;
                provinceSelect.value = code;
                currentProvinceCode = code;
                if (code) await loadCities(regionCode, code, userCity);
            }
            document.dispatchEvent(new Event('address:sync'));
        } catch (err) {
            console.error('Failed to load provinces:', err);
        }
    }

    async function loadCities(regionCode, provinceCode, preselectCity) {
        resetSelect(citySelect, 'Select City');
        resetSelect(barangaySelect, 'Select Barangay');

        currentProvinceCode = provinceCode;
        currentCityName = null;

        try {
            const res = await fetch(`{{ url('/') }}/api/address/regions/${encodeURIComponent(regionCode)}/provinces/${encodeURIComponent(provinceCode)}/cities`);
            const cities = await res.json();
            citySelect.innerHTML = buildOptions(cities, null, 'name', 'Select City / Municipality');
            citySelect.disabled = !isProfileEditMode;

            if (preselectCity) {
                const match = cities.find(c => c.name === preselectCity);
                const name = match ? match.name : preselectCity;
                citySelect.value = name;
                currentCityName = name;
                if (name) await loadBarangays(regionCode, provinceCode, name, userBarangay);
            }
            document.dispatchEvent(new Event('address:sync'));
        } catch (err) {
            console.error('Failed to load cities:', err);
        }
    }

    async function loadBarangays(regionCode, provinceCode, cityName, preselectBarangay) {
        resetSelect(barangaySelect, 'Select Barangay');
        currentCityName = cityName;

        try {
            const res = await fetch(`{{ url('/') }}/api/address/regions/${encodeURIComponent(regionCode)}/provinces/${encodeURIComponent(provinceCode)}/cities/${encodeURIComponent(cityName)}/barangays`);
            const barangays = await res.json();
            barangaySelect.innerHTML = buildOptions(barangays, null, 'name', 'Select Barangay');
            barangaySelect.disabled = !isProfileEditMode;

            if (preselectBarangay) {
                const match = barangays.find(b => b.name === preselectBarangay);
                barangaySelect.value = match ? match.name : preselectBarangay;
            }
            document.dispatchEvent(new Event('address:sync'));
        } catch (err) {
            console.error('Failed to load barangays:', err);
        }
    }

    regionSelect.addEventListener('change', function () {
        if (this.value) loadProvinces(this.value, null);
    });

    provinceSelect.addEventListener('change', function () {
        if (this.value && currentRegionCode) loadCities(currentRegionCode, this.value, null);
    });

    citySelect.addEventListener('change', function () {
        if (this.value && currentRegionCode && currentProvinceCode) {
            loadBarangays(currentRegionCode, currentProvinceCode, this.value, null);
        }
    });

    loadRegions();
})();

// ─────────────────────────────────────────────────────────────────────────────
// SEARCHABLE ADDRESS COMBOBOX (styled dropdown that mirrors the hidden <select>s)
// ─────────────────────────────────────────────────────────────────────────────
(function () {
    const PAIRS = [
        ['profile-region-combo', 'profile-region'],
        ['profile-province-combo', 'profile-province'],
        ['profile-city-combo', 'profile-city'],
        ['profile-barangay-combo', 'profile-barangay'],
    ];
    const combos = PAIRS.map(([iid, sid]) => {
        const inputEl = document.getElementById(iid);
        const selectEl = document.getElementById(sid);
        if (!inputEl || !selectEl) return null;
        return { inputEl, selectEl, wrap: inputEl.closest('.addr-combo') };
    }).filter(Boolean);
    if (!combos.length) return;

    let popup = document.getElementById('addr-combo-popup');
    if (!popup) {
        popup = document.createElement('div');
        popup.id = 'addr-combo-popup';
        popup.className = 'addr-combo-popup';
        document.body.appendChild(popup);
    }

    let active = null;
    let activeIndex = -1;
    const esc = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    function syncInput(c) {
        const o = c.selectEl.options[c.selectEl.selectedIndex];
        c.inputEl.value = (o && o.value !== '') ? o.textContent.trim() : '';
    }
    function syncAll() { combos.forEach(syncInput); }
    function optList(c) { return Array.from(c.selectEl.options).filter(o => o.value !== '' && !o.disabled); }
    function rows() { return Array.from(popup.querySelectorAll('.addr-combo-opt')); }

    function render(filter) {
        if (!active) return;
        const list = optList(active);
        const q = (filter || '').trim().toLowerCase();
        const matches = q ? list.filter(o => o.textContent.toLowerCase().includes(q)) : list;
        const cur = active.selectEl.value;
        activeIndex = -1;
        if (!matches.length) {
            popup.innerHTML = '<div class="addr-combo-empty">No matches found</div>';
            return;
        }
        popup.innerHTML = matches.map((o, i) => {
            const sel = o.value === cur;
            return `<div class="addr-combo-opt${sel ? ' is-selected' : ''}" data-index="${i}" data-value="${esc(o.value)}">${esc(o.textContent.trim())}${sel ? ' <i class="fa-solid fa-check"></i>' : ''}</div>`;
        }).join('');
    }

    function position() {
        if (!active) return;
        const r = active.inputEl.getBoundingClientRect();
        popup.style.left = r.left + 'px';
        popup.style.width = r.width + 'px';
        const below = window.innerHeight - r.bottom;
        if (below < 280 && r.top > below) {
            popup.style.top = 'auto';
            popup.style.bottom = (window.innerHeight - r.top + 6) + 'px';
        } else {
            popup.style.bottom = 'auto';
            popup.style.top = (r.bottom + 6) + 'px';
        }
    }

    function highlight(idx) {
        const rs = rows();
        if (!rs.length) return;
        activeIndex = (idx + rs.length) % rs.length;
        rs.forEach((el, i) => el.classList.toggle('is-active', i === activeIndex));
        rs[activeIndex].scrollIntoView({ block: 'nearest' });
    }

    function closeCombo() {
        if (active && active.wrap) active.wrap.classList.remove('is-open');
        active = null;
        activeIndex = -1;
        popup.classList.remove('is-open');
    }

    function openCombo(c) {
        if (active) closeCombo();
        if (c.selectEl.disabled) return;
        if (c.wrap && c.wrap.classList.contains('is-locked')) return;
        active = c;
        if (c.wrap) c.wrap.classList.add('is-open');
        render('');
        position();
        popup.classList.add('is-open');
    }

    function choose(value) {
        if (!active) return;
        const c = active;
        c.selectEl.value = value;
        syncInput(c);
        c.selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        closeCombo();
    }

    combos.forEach(c => {
        c.inputEl.addEventListener('focus', () => openCombo(c));
        c.inputEl.addEventListener('input', () => render(c.inputEl.value));
        c.inputEl.addEventListener('keydown', e => {
            const isOpen = popup.classList.contains('is-open');
            if (e.key === 'ArrowDown') { e.preventDefault(); if (!isOpen) { openCombo(c); } else { highlight(activeIndex + 1); } }
            else if (e.key === 'ArrowUp') { e.preventDefault(); highlight(activeIndex - 1); }
            else if (e.key === 'Enter') { const rs = rows(); const pick = rs[activeIndex] || rs[0]; if (isOpen && pick) { e.preventDefault(); choose(pick.dataset.value); } }
            else if (e.key === 'Escape') { syncInput(c); closeCombo(); c.inputEl.blur(); }
        });
        c.inputEl.addEventListener('blur', () => {
            setTimeout(() => { if (active === c) { syncInput(c); closeCombo(); } }, 130);
        });
    });

    popup.addEventListener('mousedown', e => {
        const row = e.target.closest('.addr-combo-opt');
        if (!row) return;
        e.preventDefault();
        choose(row.dataset.value);
    });

    document.addEventListener('click', e => {
        if (active && !popup.contains(e.target) && e.target !== active.inputEl) closeCombo();
    });
    window.addEventListener('scroll', () => { if (active) position(); }, true);
    window.addEventListener('resize', () => { if (active) position(); });

    document.addEventListener('address:sync', syncAll);
    syncAll();
    setTimeout(syncAll, 500);
})();

// ─────────────────────────────────────────────────────────────────────────────
// AVATAR UPLOAD & REMOVAL SYSTEM
// ─────────────────────────────────────────────────────────────────────────────
(function () {
    const avatarTrigger = document.getElementById('avatar-container-trigger');
    const uploadBtn     = document.getElementById('btn-trigger-upload');
    const removeBtn     = document.getElementById('btn-trigger-remove');
    const fileInput     = document.getElementById('avatar-file-input');
    const spinner       = document.getElementById('avatar-upload-spinner');
    const circleDisplay = document.getElementById('avatar-circle-display');

    function openFilePicker() {
        if (fileInput) fileInput.click();
    }

    if (avatarTrigger) {
        avatarTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            if (!isProfileEditMode) return;
            openFilePicker();
        });
    }

    if (uploadBtn) {
        uploadBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openFilePicker();
        });
    }

    // Rail photo buttons follow Edit mode.
    function syncRemoveButtons(hasImg) {
        const mode = hasImg ? 'inline-flex' : 'none';
        if (removeBtn) removeBtn.style.display = mode;
    }

    if (fileInput) {
        fileInput.addEventListener('change', async function () {
            const file = this.files && this.files[0];
            if (!file) return;

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                showToast('Please select a valid image file (JPG, PNG, WebP, or GIF).', true);
                this.value = '';
                return;
            }

            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                showToast('Image size must be less than 5MB.', true);
                this.value = '';
                return;
            }

            const formData = new FormData();
            formData.append('avatar', file);
            formData.append('_token', '{{ csrf_token() }}');

            // Show spinner
            if (spinner) spinner.style.display = 'flex';

            try {
                const response = await fetch('{{ route('customer.profile.avatar.update') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    let img = document.getElementById('avatar-img-element');
                    const initials = document.getElementById('avatar-initials-element');

                    if (initials) initials.remove();

                    if (!img) {
                        img = document.createElement('img');
                        img.id = 'avatar-img-element';
                        img.className = 'gs-avatar-img';
                        img.alt = '{{ $user->name }}';
                        circleDisplay.insertBefore(img, circleDisplay.firstChild);
                    }

                    img.src = data.avatar_url + '?t=' + new Date().getTime();

                    if (isProfileEditMode) syncRemoveButtons(true);
                    showToast(data.message || 'Profile picture updated successfully!');
                } else {
                    const errMsg = data.message || (data.errors && data.errors.avatar ? data.errors.avatar[0] : 'Failed to upload photo.');
                    showToast(errMsg, true);
                }
            } catch (err) {
                console.error('Avatar upload error:', err);
                showToast('An unexpected error occurred while uploading. Please try again.', true);
            } finally {
                if (spinner) spinner.style.display = 'none';
                fileInput.value = '';
            }
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openDeleteAvatarModal();
        });
    }

    const confirmDeleteAvatarBtn = document.getElementById('btn-confirm-delete-avatar');
    if (confirmDeleteAvatarBtn) {
        confirmDeleteAvatarBtn.addEventListener('click', async function () {
            closeDeleteAvatarModal();
            if (spinner) spinner.style.display = 'flex';

            try {
                const response = await fetch('{{ route('customer.profile.avatar.delete') }}', {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const img = document.getElementById('avatar-img-element');
                    if (img) img.remove();

                    let initials = document.getElementById('avatar-initials-element');
                    if (!initials) {
                        initials = document.createElement('span');
                        initials.id = 'avatar-initials-element';
                        initials.textContent = data.initials || '{{ strtoupper(substr($user->name, 0, 1)) }}';
                        circleDisplay.insertBefore(initials, circleDisplay.firstChild);
                    }

                    syncRemoveButtons(false);
                    showToast(data.message || 'Profile picture removed.');
                } else {
                    showToast(data.message || 'Failed to remove photo.', true);
                }
            } catch (err) {
                console.error('Avatar delete error:', err);
                showToast('An unexpected error occurred. Please try again.', true);
            } finally {
                if (spinner) spinner.style.display = 'none';
            }
        });
    }
})();
</script>
@endpush
@endsection
