@extends('layouts.app')

@section('title', $product->name . ' - Grafika South Print Shop')

@section('content')

{{-- BREADCRUMB --}}
<nav class="flex items-center gap-1 text-xs text-gray-500 overflow-hidden whitespace-nowrap" style="gap:0.45rem; margin-bottom:1.75rem; font-size:0.83rem; color:var(--text-muted);">
    <a href="{{ route('landing') }}" class="gs-breadcrumb-link">Home</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.6rem; color:var(--text-disabled);"></i>
    <a href="{{ route('products.index') }}" class="gs-breadcrumb-link">Catalog</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.6rem; color:var(--text-disabled);"></i>
    <a href="{{ route('products.index', ['category' => $product->category]) }}" class="gs-breadcrumb-link">{{ $product->category }}</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.6rem; color:var(--text-disabled);"></i>
    <span class="truncate max-w-[120px] sm:max-w-none" style="color:var(--text-main); font-weight:700;">{{ $product->name }}</span>
</nav>

{{-- PAGE STYLES --}}
<style>
    .gs-breadcrumb-link { color:var(--text-muted); text-decoration:none; transition:color 0.15s; }
    .gs-breadcrumb-link:hover { color:var(--brand-cyan-fg); }

    /* Main product layout now uses Tailwind's 12-column grid utilities directly in the markup
       (grid-cols-1 stacked on mobile, lg:grid-cols-12 on desktop) — legacy .pd-grid/.pd-image-col CSS removed. */

    .pd-image-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    .pd-image-wrap {
        width: 100%;
        aspect-ratio: 1 / 1;
        max-height: 350px;
        background: var(--bg-page);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        border-radius: 0.75rem;
        border: 1px solid var(--border-color);
    }
    .pd-image-wrap img { width:100%; max-width:100%; height:100%; object-fit:contain; transition:transform 0.4s ease; background: var(--bg-page); }
    .pd-image-wrap img:hover { transform: scale(1.03); }

    .pd-trust-strip {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        background: var(--accent-soft);
        border-top: 1px solid var(--border-strong);
    }
    .pd-trust-item { padding: 1rem 0.6rem; text-align: center; }
    .pd-trust-item:not(:last-child) { border-right: 1px solid var(--border-strong); }

    /* .pd-detail-card wrapper removed — the details column sits directly inside the shared white card */

    @media (max-width: 640px) {
        .pd-trust-item {
            padding: 0.75rem 0.25rem !important;
        }
        .pd-trust-item div {
            font-size: 0.72rem !important;
        }
    }

    /* ── Option picker panel (redesigned) ───────────────────────────── */
    .pd-price-card {
        background: var(--bg-surface);
        border: none;
        border-radius: 16px;
        padding: 1rem 1.25rem;
        margin: 0 0 1.1rem 0;
        box-shadow: none;
    }
    /* ── Horizontal separator line ─────────────────────────────────────── */
    .pd-separator {
        height: 1px;
        width: 100%;
        background: var(--border-color);
        border: none;
        margin: 0 0 1.1rem 0;
    }
    .pd-price-row {
        display: flex;
        align-items: baseline;
        gap: 0.6rem;
        flex-wrap: wrap;
    }
    .pd-price-amount {
        font-size: 2.15rem;
        font-weight: 800;
        color: var(--text-main);
        font-family: 'Outfit', sans-serif;
        letter-spacing: -0.02em;
        line-height: 1.1;
    }
    .pd-price-unit {
        font-size: 0.82rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    .pd-options-panel {
        border: none;
        border-radius: 16px;
        background: var(--accent-soft);
        padding: 1rem 1.15rem 0.4rem;
        margin-bottom: 1.15rem;
    }
    .pd-panel-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding-bottom: 0.65rem;
        border-bottom: 1px solid var(--border-strong);
    }
    .pd-panel-title {
        font-size: 0.88rem;
        font-weight: 800;
        color: var(--text-main);
        font-family: 'Outfit', sans-serif;
    }

    .pd-option-group {
        padding: 0.9rem 0;
        border-bottom: none;
    }
    .pd-option-group:last-child { border-bottom: none; }
    .pd-option-group-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.6rem;
    }
    .pd-option-group-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.09em;
        color: var(--text-muted);
    }
    .pd-option-values {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }

    .option-pill {
        position: relative;
        border: 1.5px solid var(--border-strong);
        padding: 0.5rem 0.85rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-main);
        background: var(--bg-surface);
        transition: all 0.18s ease;
        user-select: none;
        max-width: 100%;
    }
    .option-pill:hover {
        border-color: var(--cmyk-cyan);
        background: var(--accent-soft);
        transform: translateY(-1px);
    }
    .option-pill input[type="radio"] { position:absolute; opacity:0; width:0; height:0; }
    .option-pill.is-selected {
        border-color: var(--cmyk-cyan);
        background: var(--accent-soft);
        color: var(--text-main);
        box-shadow: 0 0 0 3px rgba(0,153,255,0.12);
    }
    .option-pill.is-disabled {
        opacity: 0.5;
        cursor: not-allowed !important;
        background: var(--bg-page) !important;
        border-color: var(--border-color) !important;
        color: var(--text-subtle) !important;
    }
    .option-pill.is-disabled:hover {
        border-color: var(--border-color) !important;
        background: var(--bg-page) !important;
        transform: none;
    }
    .option-pill.is-disabled input[type="radio"] {
        cursor: not-allowed !important;
        pointer-events: none;
    }

    /* Chip internals */
    .pd-chip-swatch {
        width: 17px;
        height: 17px;
        border-radius: 50%;
        border: 1px solid rgba(15,23,42,0.18);
        box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--bg-surface) 65%, transparent);
        flex-shrink: 0;
    }
    /* Optional reference image attached to an option value (wins over the colour dot) */
    .pd-chip-img {
        width: 22px;
        height: 22px;
        border-radius: 5px;
        object-fit: cover;
        border: 1px solid rgba(15,23,42,0.14);
        background: var(--bg-page);
        flex-shrink: 0;
    }
    .pd-chip-text {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .pd-chip-mod {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--brand-cyan-fg);
        white-space: nowrap;
    }
    .pd-chip-check {
        display: none;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: var(--cmyk-cyan);
        color: var(--text-inverse);
        font-size: 0.55rem;
        flex-shrink: 0;
    }
    .option-pill.is-selected .pd-chip-check { display: inline-flex; }

    .badge-opt-oos {
        font-size: 0.62rem;
        font-weight: 700;
        background: var(--danger-soft);
        color: var(--text-danger);
        padding: 0.1rem 0.4rem;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: inline-flex;
        align-items: center;
        gap: 0.22rem;
        white-space: nowrap;
    }

    .pd-artwork-box {
        background: var(--accent-soft);
        border: 1.5px solid var(--border-strong);
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
        margin-bottom: 1.25rem;
    }

    .artwork-dropzone {
        border: 2px dashed var(--border-strong);
        border-radius: 12px;
        background: var(--accent-soft);
        padding: 1rem 1.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: center;
        gap: 0.6rem;
    }
    .artwork-dropzone:hover {
        border-color: var(--cmyk-cyan);
        background: var(--accent-soft);
    }

    /* ── Attached artwork files list ─────────────────────────────── */
    .artwork-file-list {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        width: 100%;
    }
    .artwork-file-item {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        width: 100%;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0.45rem 0.7rem;
    }
    .artwork-file-item-icon {
        color: var(--brand-cyan-fg);
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .artwork-file-item-name {
        flex: 1;
        min-width: 0;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-main);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .artwork-file-item-size {
        font-size: 0.72rem;
        color: var(--text-muted);
        flex-shrink: 0;
    }
    .artwork-file-remove {
        background: var(--danger-soft);
        border: 1px solid var(--danger-border);
        color: var(--text-danger);
        font-size: 0.7rem;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        padding: 0.22rem 0.5rem;
        border-radius: 6px;
        flex-shrink: 0;
        transition: background 0.15s ease;
    }
    .artwork-file-remove:hover { background: var(--danger-soft); }
    .artwork-add-more {
        align-self: flex-start;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: var(--bg-surface);
        border: 1.5px solid var(--cmyk-cyan);
        color: var(--brand-cyan-fg);
        font-size: 0.78rem;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        padding: 0.35rem 0.8rem;
        border-radius: 9999px;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .artwork-add-more:hover { background: var(--cmyk-cyan); color: var(--text-inverse); }

    .pd-thumb-wrap {
        width: 64px;
        height: 64px;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid var(--border-strong);
        cursor: pointer;
        transition: all 0.2s ease;
        background: var(--bg-page);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .pd-thumb-wrap:hover {
        transform: translateY(-2px);
        border-color: var(--info-border);
    }
    .pd-thumb-wrap.is-active {
        border-color: var(--cmyk-cyan) !important;
        box-shadow: 0 0 0 2px rgba(0, 153, 255, 0.25);
    }
    .pd-thumb-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pd-qty-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 0.9rem 0;
        border-bottom: none;
    }
    .pd-qty-meta {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }
    .pd-qty-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.09em;
        color: var(--text-muted);
    }
    .pd-qty-hint {
        font-size: 0.74rem;
        color: var(--text-muted);
    }

    .pd-qty-wrap {
        display: inline-flex;
        align-items: center;
        border: none;
        border-radius: 9999px;
        overflow: hidden;
        background: var(--bg-surface);
    }
    .pd-qty-btn {
        border: none;
        background: var(--bg-page);
        width: 44px; height: 44px;
        cursor: pointer;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
        transition: background 0.15s;
    }
    .pd-qty-btn:hover { background: var(--text-disabled); }
    .pd-qty-input {
        width: 58px;
        border: none;
        text-align: center;
        font-weight: 800;
        font-size: 0.95rem;
        outline: none;
        color: var(--text-main);
        background: transparent;
    }

    .pd-specs-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 1.25rem;
        font-size: 0.8rem;
        background: var(--bg-page);
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: 1px solid var(--border-strong);
    }
    .pd-spec-item strong { color: var(--text-main); }
    .pd-spec-item span   { color: var(--text-muted); }

    .pd-total-box {
        background: linear-gradient(135deg, var(--bg-surface) 0%, var(--accent-soft) 100%);
        border: 1.5px solid var(--border-strong);
        border-radius: 16px;
        padding: 1.1rem 1.4rem;
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        box-shadow: none;
        position: sticky;
        bottom: 1rem;
        z-index: 25;
    }
    .pd-total-left {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        min-width: 0;
    }
    .pd-total-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text-muted);
    }
    .pd-total-value {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--text-main);
        font-family: 'Outfit', sans-serif;
        line-height: 1.1;
        white-space: nowrap;
    }
    .pd-total-cta { flex-shrink: 0; }

    @media (max-width: 640px) {
        .pd-total-box {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
            padding: 1rem;
        }
        .pd-total-cta .pd-cta {
            width: 100% !important;
        }
    }

    .badge-stock-in  { background:var(--success-soft); color:var(--text-success); border:1px solid var(--border-strong); }
    .badge-stock-out { background:var(--danger-soft); color:var(--text-danger); border:1px solid var(--border-strong); }
    .pd-badge {
        font-size: 0.73rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .pd-cta {
        width: 100%;
        min-width: 230px;
        padding: 0.85rem 1.75rem;
        font-size: 0.95rem;
        white-space: nowrap;
        font-weight: 700;
        border-radius: 9999px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Outfit', sans-serif;
    }
    .pd-cta-active { background:var(--cmyk-cyan); color:var(--text-inverse); box-shadow:none; }
    .pd-cta-active:hover { background:var(--cmyk-cyan-hover); transform:translateY(-1px); box-shadow:none; }
    .pd-cta-disabled { background:var(--bg-subtle); color:var(--text-subtle); border:1px solid var(--border-color); cursor:not-allowed; }

    .pd-text-input {
        width: 100%;
        padding: 0.65rem 0.9rem;
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        font-size: 0.85rem;
        outline: none;
        transition: border-color 0.2s;
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
        color: var(--text-main);
    }
    .pd-text-input:focus { border-color: var(--cmyk-cyan); }

    /* Textarea variant — multi-line instructions that wrap instead of scrolling sideways */
    textarea.pd-text-input {
        min-height: 110px;
        max-height: 320px;
        resize: vertical;
        line-height: 1.6;
        overflow-y: auto;
    }

    .pd-label {
        display: block;
        font-weight: 700;
        font-size: 0.83rem;
        color: var(--text-main);
        margin-bottom: 0.5rem;
        letter-spacing: 0.01em;
    }

    .pd-img-placeholder {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 3rem 2rem; text-align: center;
        width: 100%; height: 100%;
    }
    .pd-img-placeholder-icon {
        width: 80px; height: 80px;
        background: var(--accent-soft); border: 1px solid var(--info-border);
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin-bottom: 1rem; color: var(--brand-cyan-fg); font-size: 2rem;
    }

    /* ── Artwork required validation states ── */
    .pd-artwork-box.artwork-missing {
        border-color: var(--danger-color);
        background: var(--danger-soft);
        animation: artwork-shake 0.4s ease;
    }
    .pd-artwork-box.artwork-missing .artwork-dropzone {
        border-color: var(--danger-color);
        background: var(--danger-soft);
    }
    @keyframes artwork-shake {
        0%, 100% { transform: translateX(0); }
        20% { transform: translateX(-6px); }
        40% { transform: translateX(6px); }
        60% { transform: translateX(-4px); }
        80% { transform: translateX(4px); }
    }
    .artwork-error-msg {
        display: none;
        color: var(--text-danger);
        font-size: 0.82rem;
        font-weight: 700;
        margin-top: 0.55rem;
        align-items: center;
        gap: 0.4rem;
    }
    .artwork-error-msg.is-visible { display: flex; }
</style>

{{-- Unified media (Main-first: video + images) shared by the display, thumbnails, and lightbox --}}
@php
    $productVideos = $product->relationLoaded('videos') ? $product->videos : $product->videos()->get();

    // Build the media list in the admin's unified drag-order: Main first, then by position
    $mediaRaw = $productVideos
        ->toBase()
        ->map(function ($v) {
            return ['type' => 'video', 'url' => $v->url, 'poster' => null, 'is_primary' => (bool) $v->is_primary, 'pos' => (int) ($v->position ?: 0), 'so' => (int) $v->sort_order, 'id' => (int) $v->id];
        })
        ->merge($product->images->map(function ($i) {
            return ['type' => 'image', 'url' => asset('storage/' . $i->image_path), 'poster' => null, 'is_primary' => (bool) $i->is_primary, 'pos' => (int) ($i->position ?: 0), 'so' => (int) $i->sort_order, 'id' => (int) $i->id];
        }))
        ->sortBy([['is_primary', 'desc'], ['pos', 'asc'], ['so', 'asc'], ['id', 'asc']])
        ->values();
    if ($mediaRaw->isEmpty() && $product->image_path) {
        $mediaRaw = collect([['type' => 'image', 'url' => asset('storage/' . $product->image_path), 'poster' => null, 'is_primary' => false, 'pos' => 0, 'so' => 0, 'id' => 0]]);
    }

    // Lightbox navigates images only — map each media item to its image index
    $lightboxImgUrls = $mediaRaw->where('type', 'image')->values()->map->url->all();
    $mediaItems = $mediaRaw->values()->map(function ($m) use ($lightboxImgUrls) {
        $m['lbIndex'] = $m['type'] === 'image' ? array_search($m['url'], $lightboxImgUrls, true) : null;
        return $m;
    })->all();

    // Legacy variables (referenced elsewhere / lightbox fallback)
    $allGalleryImages = $product->images->pluck('image_path');
    $mainImage = collect($lightboxImgUrls)->first();
    $galleryUrls = $lightboxImgUrls;
@endphp

{{-- MAIN SPLIT: SHOPEE/LAZADA-STYLE WHITE CARD — stacked on mobile, 12-col grid from lg: up --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 bg-white p-4 sm:p-6 rounded-2xl border border-gray-100 shadow-sm" style="margin-bottom:3rem;">

    {{-- LEFT: GALLERY COLUMN (5/12 on desktop, full-width stack on mobile) — owns the lightbox Alpine state --}}
    <div class="lg:col-span-5 min-w-0"
         x-data="{ openLightbox: false, activeImageIndex: 0, activeMedia: 0, media: {{ Illuminate\Support\Js::from($mediaItems) }}, images: {{ Illuminate\Support\Js::from($galleryUrls) }} }">
        <div class="w-full flex flex-col gap-3 mb-4">

            {{-- UNIFIED MEDIA DISPLAY — shows the active media (video or image); thumbnails below switch it --}}
            <div class="pd-image-wrap w-full aspect-square max-h-[350px] rounded-xl border border-gray-100 overflow-hidden"
                 style="position:relative; background:var(--text-main);">

                {{-- Video slide --}}
                <template x-for="(m, i) in media" :key="'v'+i">
                    <video x-show="media[activeMedia].type === 'video' && activeMedia === i"
                           :src="m.url" controls preload="metadata" playsinline
                           style="position:absolute; inset:0; width:100%; height:100%; object-fit:contain; background:var(--text-main);"></video>
                </template>

                {{-- Image slide (click to zoom) --}}
                <img x-show="media.length && media[activeMedia].type === 'image'"
                     :src="media.length ? media[activeMedia].url : ''"
                     alt="{{ $product->name }}"
                     id="main-product-img"
                     title="Click to zoom"
                     @click="if (media.length && media[activeMedia].type === 'image') { activeImageIndex = media[activeMedia].lbIndex; openLightbox = true; }"
                     class="w-full h-full object-contain cursor-pointer"
                     style="position:absolute; inset:0; background:var(--bg-surface); transition: opacity 0.2s ease, transform 0.4s ease;">

                {{-- Placeholder when no media --}}
                @if(empty($mediaItems))
                    <div class="pd-img-placeholder">
                        <div class="pd-img-placeholder-icon"><i class="fa-solid fa-print"></i></div>
                        <span style="font-size:1rem; font-weight:700; color:var(--text-main); font-family:'Outfit',sans-serif;">{{ $product->name }}</span>
                        <span style="font-size:0.79rem; color:var(--text-muted); margin-top:0.3rem;">High-Definition CMYK Print</span>
                    </div>
                @endif

                {{-- Featured overlay badge --}}
                @if($product->is_featured)
                    <div style="position:absolute; top:0.9rem; right:0.9rem; display:flex; flex-direction:column; gap:0.4rem; z-index:6;">
                        <span style="background:var(--warning-soft); color:var(--text-warning); border:1px solid var(--warning-border); font-size:0.71rem; font-weight:800; padding:0.28rem 0.65rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.3rem;">
                            <i class="fa-solid fa-star"></i> Featured
                        </span>
                    </div>
                @endif
            </div>

            {{-- UNIFIED THUMBNAIL STRIP — video + images, Main-first; click to switch the display --}}
            @if(count($mediaItems) > 1)
                <div class="pd-gallery-thumbnails flex items-center gap-2 overflow-x-auto pb-2 w-full max-w-full">
                    <template x-for="(m, i) in media" :key="i">
                        <div class="pd-thumb-wrap" :class="activeMedia === i ? 'is-active' : ''"
                             @click="activeMedia = i"
                             :title="m.type === 'video' ? 'Play video' : ('View product image ' + (i + 1))"
                             :style="'width:64px; height:64px; border-radius:10px; overflow:hidden; position:relative; cursor:pointer; transition:all 0.2s ease; background:var(--text-main); border:2px solid ' + (activeMedia === i ? 'var(--cmyk-cyan)' : 'var(--border-color)')">
                            {{-- Image thumb --}}
                            <img x-show="m.type === 'image'" :src="m.url"
                                 style="width:100%; height:100%; object-fit:contain; padding:2px; background:var(--bg-surface);">
                            {{-- Video thumb: real element so the frame shows (not a black box) --}}
                            <video x-show="m.type === 'video'" :src="m.url + '#t=0.5'" preload="metadata" muted playsinline
                                   style="width:100%; height:100%; object-fit:cover; display:block; pointer-events:none;"></video>
                            <span x-show="m.type === 'video'"
                                  style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(15,23,42,0.25); pointer-events:none;">
                                <i class="fa-solid fa-circle-play" style="color:var(--text-inverse); font-size:1.4rem; filter:drop-shadow(0 1px 2px rgba(0,0,0,0.4));"></i>
                            </span>
                        </div>
                    </template>
                </div>
            @endif


        </div>

        {{-- SHOPEE/LAZADA-STYLE FULLSCREEN IMAGE LIGHTBOX (Alpine.js — lives inside the x-data scope so position:fixed still covers the viewport) --}}
        <div x-show="openLightbox"
             x-cloak
             x-transition.opacity
             @keydown.escape.window="openLightbox = false"
             x-effect="document.body.style.overflow = openLightbox ? 'hidden' : ''"
             class="fixed inset-0 z-[10005] bg-black/80 backdrop-blur-sm flex items-center justify-center p-0 sm:p-6">

            {{-- Modal Card Container — fullscreen on mobile, centered rounded card from sm: up --}}
            <div class="bg-gray-950 sm:bg-white w-full h-full sm:h-auto sm:max-h-[90vh] sm:max-w-5xl sm:rounded-2xl overflow-hidden flex flex-col md:flex-row relative shadow-2xl" @click.away="openLightbox = false">

                {{-- Modern Floating Close Button --}}
                <button @click="openLightbox = false" aria-label="Close"
                    class="absolute top-4 right-4 z-50 w-10 h-10 bg-black/60 hover:bg-black/80 text-white rounded-full flex items-center justify-center backdrop-blur-md border border-white/20 shadow-lg transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                {{-- Left Main Image View with Navigation Controls --}}
                <div class="flex-1 min-h-0 bg-gray-900 flex items-center justify-center relative md:min-h-[500px]">
                    {{-- Modern Floating Prev Arrow --}}
                    <button @click="activeImageIndex = (activeImageIndex === 0) ? images.length - 1 : activeImageIndex - 1"
                            aria-label="Previous image"
                            class="absolute left-3 top-1/2 -translate-y-1/2 z-40 w-10 h-10 bg-black/50 hover:bg-black/80 text-white rounded-full flex items-center justify-center backdrop-blur-md border border-white/20 transition-all active:scale-95 shadow-md">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    {{-- Main Zoomed Image --}}
                    <img :src="images[activeImageIndex]" class="max-h-[85vh] sm:max-h-[75vh] w-full object-contain" alt="Enlarged product image">

                    {{-- Modern Floating Next Arrow --}}
                    <button @click="activeImageIndex = (activeImageIndex === images.length - 1) ? 0 : activeImageIndex + 1"
                            aria-label="Next image"
                            class="absolute right-3 top-1/2 -translate-y-1/2 z-40 w-10 h-10 bg-black/50 hover:bg-black/80 text-white rounded-full flex items-center justify-center backdrop-blur-md border border-white/20 transition-all active:scale-95 shadow-md">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    {{-- Mobile image counter (desktop counter lives in the side panel) --}}
                    <div class="md:hidden absolute bottom-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-black/60 text-white text-xs backdrop-blur-md border border-white/20" x-text="(activeImageIndex + 1) + ' / ' + images.length"></div>
                </div>

                {{-- Responsive Thumbnail Gallery Strip — horizontal scroller on mobile, vertical panel on desktop --}}
                <div class="w-full md:w-80 bg-gray-900 md:bg-white p-3 flex flex-row md:flex-col overflow-x-auto md:overflow-y-auto gap-2 border-t md:border-t-0 md:border-l border-gray-800 md:border-gray-100 shrink-0">
                    <h3 class="hidden md:block text-sm font-bold text-gray-800 mb-3 truncate">{{ $product->name }}</h3>
                    <div class="flex md:grid md:grid-cols-3 gap-2">
                        <template x-for="(img, idx) in images" :key="idx">
                            <button @click="activeImageIndex = idx"
                                    :class="activeImageIndex === idx ? 'ring-2 ring-sky-500 border-transparent' : 'border-gray-200 hover:opacity-80'"
                                    class="w-14 h-14 md:w-auto md:h-auto aspect-square shrink-0 rounded-lg overflow-hidden border transition-all">
                                <img :src="img" class="w-full h-full object-cover" alt="Product thumbnail">
                            </button>
                        </template>
                    </div>
                    <div class="hidden md:block mt-auto pt-3 text-xs text-gray-400 text-center" x-text="(activeImageIndex + 1) + ' / ' + images.length"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: DETAILS AND ORDER FORM (7/12 on desktop, stacked below gallery on mobile) --}}
    <div class="lg:col-span-7 flex flex-col gap-4 min-w-0">

        @php
                $isCustomProduct = (bool) ($product->is_customizable ?? false);
                $baseUnitProdTime = (int) ($product->getEstimatedProductionTime(1) ?: ($product->custom_production_time_minutes ?? 0));
                $showProductionTime = $isCustomProduct || $baseUnitProdTime > 0;
                if ($showProductionTime && $baseUnitProdTime <= 0) {
                    $baseUnitProdTime = 30;
                }
            @endphp

            {{-- Category label + turnaround badge + stock badge --}}
            <div class="flex flex-wrap items-center gap-1.5 w-full max-w-full overflow-hidden" style="justify-content:space-between; margin-bottom:0.55rem;">
                <span style="color:var(--text-main); font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:0.07em;">
                    {{ $product->category }}
                </span>
                <div class="flex flex-wrap items-center gap-1.5 w-full max-w-full min-w-0">
                    {{-- Est. Processing Time badge only shows when Custom Item mode is ON --}}
                    @if($product->is_customizable && $baseUnitProdTime > 0)
                        <span id="header-turnaround-badge" class="shrink text-xs whitespace-nowrap inline-flex items-center gap-1 px-2.5 py-1 font-semibold text-amber-800 bg-amber-50 rounded-full border border-amber-200" title="Estimated processing turnaround time">
                            <i class="fa-regular fa-clock w-3.5 h-3.5" style="font-size:0.72rem;"></i>
                            <span>Est. Processing Time: <strong id="header-turnaround-text">{{ \App\Models\Product::formatMinutes($baseUnitProdTime) ?: '30 mins' }}</strong></span>
                        </span>
                    @endif
                    {{-- Stock badge — only shown when OUT of stock (informative warning); "In Stock" is not displayed --}}
                    @if(!$product->has_sufficient_stock)
                        <span class="pd-badge badge-stock-out shrink text-xs whitespace-nowrap">
                            <i class="fa-solid fa-circle-xmark"></i> Out of Stock
                        </span>
                    @endif
                </div>
            </div>

            {{-- Product title --}}
            <h1 style="font-size:1.75rem; font-weight:800; color:var(--text-main); margin:0 0 1rem 0; font-family:'Outfit',sans-serif; line-height:1.2;">
                {{ $product->name }}
            </h1>

        {{-- Price — highlighted card with live unit price --}}
        <div class="pd-price-card">
            <div class="pd-price-row">
                <span class="pd-price-amount">
                    &#8369;<span id="unit-price-display">{{ number_format($product->effective_price, 2) }}</span>
                </span>
                <span class="pd-price-unit">per item</span>
            </div>
        </div>

        {{-- Separator line between price and quantity --}}
        <hr class="pd-separator">

            {{-- Description moved to the bottom of the page (see PRODUCT DESCRIPTION section below) --}}

            {{-- ORDER FORM --}}
            {{-- novalidate: custom JS validation handles the required artwork check
                 (a hidden `required` file input would block submission with an
                 "not focusable" console error and skip our red indicator) --}}
            <form action="{{ route('cart.add') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                {{-- Quantity (horizontal row on desktop, stacked on mobile) --}}
                <div class="pd-qty-row">
                    <div class="pd-qty-meta">
                        <span class="pd-qty-label">Quantity</span>
                        <span class="pd-qty-hint">
                            Max {{ max(1, $product->max_producible_quantity) }} per order
                        </span>
                    </div>
                    <div class="pd-qty-wrap">
                        <button type="button" class="pd-qty-btn" onclick="decrementQuantity()" aria-label="Decrease quantity">&#8722;</button>
                        <input type="number"
                               name="quantity"
                               id="quantity-input"
                               class="pd-qty-input"
                               value="1" min="1"
                               max="{{ max(1, $product->max_producible_quantity) }}"
                               {{ !$product->has_sufficient_stock ? 'disabled' : '' }}
                               onchange="calculateTotalPrice()"
                               oninput="calculateTotalPrice()"
                               onkeyup="calculateTotalPrice()">
                        <button type="button" class="pd-qty-btn" onclick="incrementQuantity()" aria-label="Increase quantity">+</button>
                    </div>
                </div>

                {{-- Dynamic option picker — grouped, with live "current selection" readout --}}
                @if(isset($groupedOptions) && $groupedOptions->count() > 0)
                    @php
                        // Swatch map for colour-type option groups (falls back to no swatch)
                        $gsSwatches = [
                            'white' => '#FFFFFF', 'off-white' => '#F8FAFC', 'black' => '#111827',
                            'red' => '#EF4444', 'crimson' => '#DC143C', 'maroon' => '#7F1D1D',
                            'burgundy' => '#800020', 'wine' => '#722F37', 'pink' => '#EC4899',
                            'rose' => '#F43F5E', 'coral' => '#FF7F50', 'salmon' => '#FA8072',
                            'orange' => '#F97316', 'peach' => '#FED7AA', 'brown' => '#92400E',
                            'tan' => '#D2B48C', 'beige' => '#F5F5DC', 'cream' => '#FFFDD0',
                            'ivory' => '#FFFFF0', 'gold' => '#D4AF37', 'yellow' => '#EAB308',
                            'khaki' => '#F0E68C', 'olive' => '#65A30D', 'lime' => '#84CC16',
                            'green' => '#22C55E', 'mint' => '#98FF98', 'teal' => '#14B8A6',
                            'cyan' => '#06B6D4', 'turquoise' => '#40E0D0', 'sky' => '#38BDF8',
                            'blue' => '#3B82F6', 'navy' => '#1E3A8A', 'indigo' => '#4F46E5',
                            'violet' => '#8B5CF6', 'purple' => '#A855F7', 'magenta' => '#EC008C',
                            'lavender' => '#E6E6FA', 'gray' => '#9CA3AF', 'grey' => '#9CA3AF',
                            'silver' => '#C0C0C0', 'sand' => '#C2B280',
                        ];
                    @endphp

                    <div class="pd-options-panel">
                        <div class="pd-panel-head">
                            <span class="pd-panel-title">Options</span>
                        </div>

                        @foreach($groupedOptions as $optionName => $optionsGroup)
                            @php
                                // Determine the first option in this group that has sufficient stock (for 1 unit)
                                $firstInStockIndex = null;
                                foreach ($optionsGroup as $idx => $o) {
                                    if ($product->checkBOMStock(1, [$optionName => $o->option_value])) {
                                        $firstInStockIndex = $idx;
                                        break;
                                    }
                                }
                                $isColorGroup = str_contains(strtolower($optionName), 'color');
                            @endphp

                            <div class="pd-option-group" data-group-name="{{ $optionName }}">
                                <div class="pd-option-group-head">
                                    <span class="pd-option-group-label">{{ $optionName }}</span>
                                </div>
                                <div class="pd-option-values">
                                    @foreach($optionsGroup as $idx => $opt)
                                        @php
                                            $optInStock = $product->checkBOMStock(1, [$optionName => $opt->option_value]);
                                            $isSelected = ($firstInStockIndex !== null && $idx === $firstInStockIndex) || ($firstInStockIndex === null && $loop->first);
                                            $swatch = $isColorGroup ? ($gsSwatches[strtolower(trim($opt->option_value))] ?? null) : null;
                                        @endphp
                                        <label class="option-pill {{ $isSelected && $optInStock ? 'is-selected' : '' }} {{ !$optInStock ? 'is-disabled is-out-of-stock' : '' }}"
                                               data-option-id="{{ $opt->id }}"
                                               data-option-name="{{ $optionName }}"
                                               data-option-value="{{ $opt->option_value }}"
                                               title="{{ !$optInStock ? 'Out of stock for this variant' : $opt->option_value }}">
                                            <input type="radio"
                                                   name="options[{{ $optionName }}]"
                                                   value="{{ $opt->option_value }}"
                                                   data-option-id="{{ $opt->id }}"
                                                   data-option-name="{{ $optionName }}"
                                                   data-modifier="{{ (float)$opt->price_modifier }}"
                                                   {{ $isSelected && $optInStock ? 'checked' : '' }}
                                                   {{ !$optInStock ? 'disabled' : '' }}
                                                   onchange="handleOptionChange(this)">
                                            @if($opt->image_url)
                                                <img src="{{ $opt->image_url }}" alt="{{ $opt->option_value }}" class="pd-chip-img" loading="lazy">
                                            @elseif($swatch)
                                                <span class="pd-chip-swatch" style="background:{{ $swatch }};"></span>
                                            @endif
                                            <span class="pd-chip-text">{{ $opt->option_value }}</span>
                                            @if($opt->price_modifier > 0)
                                                <span class="pd-chip-mod">+&#8369;{{ number_format($opt->price_modifier, 2) }}</span>
                                            @endif
                                            <span class="pd-chip-check"><i class="fa-solid fa-check"></i></span>
                                            <span class="badge-opt-oos" style="{{ !$optInStock ? 'display:inline-flex;' : 'display:none;' }}">
                                                <i class="fa-solid fa-circle-xmark" style="font-size:0.6rem;"></i> Out of Stock
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ARTWORK / DESIGN FILE UPLOAD SECTION --}}
                {{-- Only shown when the admin has enabled "requires_design_file" for this product --}}
                @if($product->requires_design_file)
                <div class="pd-artwork-box" id="artwork-required-box">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.65rem; flex-wrap:wrap; gap:0.35rem;">
                        <label for="design_file_input"
                               style="font-weight:700; font-size:0.88rem; color:var(--text-main); display:inline-flex; align-items:center; gap:0.45rem; cursor:pointer;">
                            <i class="fa-solid fa-cloud-arrow-up" id="artwork-title-icon" style="color:var(--brand-cyan-fg); font-size:1.05rem;"></i>
                            Upload Artwork / Design File
                        </label>
                    </div>

                    <div class="artwork-dropzone" id="artwork-dropzone" onclick="document.getElementById('design_file_input').click()">
                        <input type="file"
                               id="design_file_input"
                               name="design_files[]"
                               multiple
                               accept=".jpg,.jpeg,.png,.pdf,.ai,.psd,.svg,.zip,.eps,.cdr"
                               style="display:none;"
                               onchange="handleArtworkFileSelect(this)">

                        <div id="artwork-empty-state" style="display:flex; flex-direction:column; align-items:center; text-align:center; padding:0.4rem;">
                            <i class="fa-solid fa-file-arrow-up" style="font-size:1.6rem; color:var(--brand-cyan-fg); margin-bottom:0.35rem;"></i>
                            <span style="font-size:0.86rem; font-weight:700; color:var(--text-main);">
                                Click to browse or drag &amp; drop artwork
                            </span>
                            <span style="font-size:0.74rem; color:var(--text-muted); margin-top:0.25rem;">
                                You may attach multiple files · PNG, JPG, PDF, AI, PSD, SVG, ZIP, EPS, CDR (Max 20MB each)
                            </span>
                        </div>

                        <div id="artwork-file-selected" style="display:none; align-items:center; justify-content:space-between; gap:0.6rem; width:100%; background:var(--bg-surface); border:1.5px solid var(--border-strong); border-radius:10px; padding:0.6rem 0.85rem;">
                            <div style="display:flex; align-items:center; gap:0.6rem; min-width:0;">
                                <i class="fa-solid fa-file-circle-check" style="color:var(--text-success); font-size:1.25rem; flex-shrink:0;"></i>
                                <div style="min-width:0;">
                                    <div id="artwork-filename" style="font-size:0.84rem; font-weight:700; color:var(--text-main); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:320px;">file.pdf</div>
                                    <div id="artwork-filesize" style="font-size:0.72rem; color:var(--text-muted);">0 KB</div>
                                </div>
                            </div>
                            <button type="button" onclick="event.stopPropagation(); clearArtworkFile();" style="background:var(--danger-soft); border:1px solid var(--danger-border); color:var(--text-danger); font-size:0.8rem; font-weight:700; cursor:pointer; padding:0.3rem 0.65rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.3rem; flex-shrink:0; transition:all 0.15s ease;" onmouseover="this.style.background='#FEE2E2'" onmouseout="this.style.background='#FEF2F2'">
                                <i class="fa-solid fa-xmark"></i> Remove all
                            </button>
                        </div>

                        {{-- List of the individual attached files (multiple uploads) --}}
                        <div id="artwork-filelist" class="artwork-file-list" style="display:none;"></div>

                        {{-- Add more files without losing the current selection --}}
                        <button type="button" id="artwork-add-more" class="artwork-add-more" style="display:none;"
                                onclick="event.stopPropagation(); document.getElementById('design_file_input').click();">
                            <i class="fa-solid fa-plus"></i> Add more files
                        </button>
                    </div>

                    <div class="artwork-error-msg" id="artwork-error-msg">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        Please upload your design/artwork file first before proceeding.
                    </div>

                    @error('design_file')
                        <div style="color: var(--text-danger); font-size: 0.8rem; font-weight: 600; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </div>
                    @enderror
                    @error('design_files')
                        <div style="color: var(--text-danger); font-size: 0.8rem; font-weight: 600; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </div>
                    @enderror
                    @error('design_files.*')
                        <div style="color: var(--text-danger); font-size: 0.8rem; font-weight: 600; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
                @endif
                {{-- END ARTWORK UPLOAD --}}

                {{-- Special instructions (custom items only) --}}
                @if($product->is_customizable)
                <div style="margin-bottom:1.2rem;" class="w-full max-w-full min-w-0 overflow-hidden">
                    <label class="pd-label" for="special_instructions_input">
                        Notes / Instructions
                        <span style="font-weight:400; color:var(--text-muted); font-size:0.79rem; margin-left:0.3rem;">(Optional)</span>
                    </label>
                    <textarea id="special_instructions_input"
                              name="special_instructions"
                              rows="4"
                              class="pd-text-input w-full min-w-0 text-xs sm:text-sm rounded-lg border-black focus:ring-sky-500 focus:border-sky-500"
                              placeholder="e.g. Sizing details, Pantone/CMYK codes..."></textarea>
                </div>
                @endif

                {{-- NOTE: The detailed "Insufficient Stock for Selected Options" material-shortage alert box
                     was removed by design — out-of-stock state is communicated only through the simple
                     disabled "Out of Stock for Selected Options" button and the per-option badges. --}}

                {{-- Bottom Sticky Checkout Summary Card --}}
                <div class="pd-total-box">
                    {{-- Left: Price block cleanly aligned without text overlap --}}
                    <div class="pd-total-left">
                        <span class="pd-total-label">Total Estimated Price</span>
                        <span class="pd-total-value">
                            &#8369;<span id="total-price-display">0.00</span>
                        </span>
                    </div>


                    {{-- Right: CTA Button properly sized and aligned to the right --}}
                    <div class="pd-total-cta">
                        @if($product->has_sufficient_stock)
                            @php $needsArtwork = (bool) $product->requires_design_file; @endphp
                            <button type="submit" class="pd-cta pd-cta-active" id="add-to-cart-btn">
                                <i class="fa-solid fa-cart-shopping"></i>
                                {{ $needsArtwork ? 'Upload Artwork & Add to Cart' : 'Add to Cart & Proceed' }}
                            </button>
                        @else
                            <button type="button" disabled class="pd-cta pd-cta-disabled" id="add-to-cart-btn">
                                <i class="fa-solid fa-ban" style="color:var(--text-danger);"></i>
                                Out of Stock — Cannot Order
                            </button>
                        @endif
                    </div>
                </div>

            </form>
    </div>

</div>

{{-- CUSTOMER REVIEWS & RATINGS — Shopee/Lazada style rating header with JS filter pills --}}
@php
    $productReviews = $product->reviews; // eager-loaded with user in ProductController::show
    $reviewsCount = $productReviews->count();
    $avgRating = $reviewsCount ? round($productReviews->avg('rating'), 1) : 0;
    $reviewsWithComments = $productReviews->filter(fn($r) => !empty($r->comment))->count();
    $reviewsWithMedia = $productReviews->filter(fn($r) => !empty($r->photo_path))->count();
@endphp
<section id="customer-reviews" class="mt-8 bg-white p-4 sm:p-6 rounded-2xl border border-black shadow-sm w-full max-w-full overflow-hidden">
    {{-- Section header --}}
    <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
        <h2 class="text-lg font-bold text-gray-800" style="font-family:'Outfit',sans-serif;">Product Ratings</h2>
    </div>

    {{-- Rating Summary Banner — Grafika South theme --}}
    <div class="rounded-xl p-5 flex flex-col md:flex-row md:items-center gap-6 {{ $reviewsCount === 0 ? '' : 'mb-5' }}"
         style="background-color: rgba(0, 153, 255, 0.05); border: none;">
        {{-- Score Summary (Left) --}}
        <div class="text-center md:text-left shrink-0">
            <div class="text-2xl font-bold" style="color: var(--text-accent);">
                <span class="text-3xl font-extrabold">{{ number_format($avgRating, 1) }}</span> <span class="text-xs font-normal" style="color: var(--text-accent); opacity: 0.8;">out of 5</span>
            </div>
            <div class="flex items-center justify-center md:justify-start gap-1 text-amber-400 text-lg mt-1">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fa-solid fa-star {{ $i <= round($avgRating) ? '' : 'text-gray-200' }}"></i>
                @endfor
            </div>
            <div class="text-xs text-gray-500 mt-1.5 whitespace-nowrap">{{ $reviewsCount }} rating{{ $reviewsCount === 1 ? '' : 's' }}</div>
        </div>

        {{-- Filter Pills (Right) --}}
        <div class="flex flex-wrap items-center gap-2 flex-1">
            <button type="button" onclick="filterProductReviews('all', this)"
                class="review-filter-pill px-4 py-1.5 text-xs rounded transition-colors shadow-xs font-semibold"
                style="background-color: var(--accent-color); color: var(--text-inverse); border: none;">
                All ({{ $reviewsCount }})
            </button>

            @foreach([5, 4, 3, 2, 1] as $star)
                <button type="button" onclick="filterProductReviews('{{ $star }}', this)"
                    class="review-filter-pill px-3.5 py-1.5 text-xs font-medium rounded transition-colors bg-white text-gray-700"
                    style="border: none;"
                    onmouseover="this.style.color='#0099FF';"
                    onmouseout="if(!this.classList.contains('active-pill')){ this.style.color='#374151'; }">
                    {{ $star }} Star ({{ $productReviews->where('rating', $star)->count() }})
                </button>
            @endforeach

            <button type="button" onclick="filterProductReviews('comments', this)"
                class="review-filter-pill px-3.5 py-1.5 text-xs font-medium rounded transition-colors bg-white text-gray-700"
                style="border: none;"
                onmouseover="this.style.color='#0099FF';"
                onmouseout="if(!this.classList.contains('active-pill')){ this.style.color='#374151'; }">
                With Comments ({{ $reviewsWithComments }})
            </button>
            <button type="button" onclick="filterProductReviews('media', this)"
                class="review-filter-pill px-3.5 py-1.5 text-xs font-medium rounded transition-colors bg-white text-gray-700"
                style="border: none;"
                onmouseover="this.style.color='#0099FF';"
                onmouseout="if(!this.classList.contains('active-pill')){ this.style.color='#374151'; }">
                With Media ({{ $reviewsWithMedia }})
            </button>
        </div>
    </div>

    @if($reviewsCount === 0)
        {{-- Empty State Container --}}
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="w-16 h-16 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center text-3xl mb-3">
                <i class="fa-regular fa-star"></i>
            </div>
            <p class="text-gray-500 font-medium text-sm">No ratings yet</p>
            <p class="text-gray-400 text-xs mt-1 max-w-md">Be the first to rate this product after your order is marked as completed!</p>
        </div>
    @else
        <div id="product-reviews-list" class="flex flex-col list-none w-full max-w-full">
            @foreach($productReviews as $review)
                <div class="review-item p-3.5 bg-gray-50/80 rounded-xl mb-3 w-full max-w-full overflow-hidden border border-black"
                     data-rating="{{ (int)$review->rating }}"
                     data-has-comment="{{ !empty($review->comment) ? '1' : '0' }}"
                     data-has-media="{{ !empty($review->photo_path) ? '1' : '0' }}">
                    <!-- Review Header: identity group (left) + rating/date (right) -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <!-- Left: Avatar + Name + Verified Buyer badge -->
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-full font-bold text-sm flex items-center justify-center shrink-0 uppercase" style="background-color: var(--accent-soft); color: var(--text-accent);">
                                {{ substr($review->buyer_name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <span class="text-sm font-semibold text-gray-900 break-words">{{ $review->buyer_name }}</span>
                                <span class="ml-1.5 align-middle text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-black rounded-full px-1.5 py-0.5 whitespace-nowrap">
                                    <i class="fa-solid fa-circle-check"></i> VERIFIED BUYER
                                </span>
                            </div>
                        </div>
                        <!-- Right: Star rating + date -->
                        <div class="text-xs text-gray-500 whitespace-nowrap shrink-0 sm:text-right">
                            <div class="flex sm:justify-end text-amber-400 text-xs gap-0.5 mb-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-solid fa-star {{ $i <= $review->rating ? '' : 'text-gray-200' }}"></i>
                                @endfor
                            </div>
                            {{ $review->created_at->timezone('Asia/Manila')->format('M d, Y') }}
                        </div>
                    </div>

                    <!-- Review Comment -->
                    @if($review->comment)
                        <p class="mt-2.5 w-full max-w-full text-sm text-gray-700 leading-relaxed break-words overflow-hidden">{{ $review->comment }}</p>
                    @endif

                    <!-- Optional Review Photo -->
                    @if($review->photo_url)
                        <img src="{{ $review->photo_url }}" alt="Review photo by {{ $review->buyer_name }}" class="mt-2.5 max-h-44 max-w-full rounded-lg border border-black object-cover shadow-sm">
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Shown by JS when a filter pill matches zero reviews --}}
        <div id="reviews-filter-empty" class="flex-col items-center justify-center py-10 text-center" style="display:none;">
            <div class="w-14 h-14 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center text-2xl mb-3">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <p class="text-gray-500 font-medium text-sm">No reviews match this filter</p>
        </div>
    @endif
</section>

{{-- PRODUCT DESCRIPTION — moved to the bottom of the page (Shopee-style placement) --}}
@if($product->description)
<section id="product-description" class="mt-6 bg-white p-4 sm:p-6 rounded-2xl border border-gray-100 shadow-sm w-full max-w-full overflow-hidden">
    <h2 class="text-lg font-bold text-gray-800 mb-3" style="font-family:'Outfit',sans-serif;">Description</h2>
    <div style="font-size:0.9rem; color:var(--text-muted); line-height:1.8; max-width:1000px; overflow-wrap:break-word;">
        {!! nl2br(e($product->description)) !!}
    </div>
</section>
@endif

<script>
    // Shopee/Lazada-style review filter pills (plain JS — no Livewire in this project)
    function filterProductReviews(type, btn) {
        document.querySelectorAll('.review-filter-pill').forEach(pill => {
            pill.classList.remove('shadow-xs', 'font-semibold', 'active-pill');
            pill.style.backgroundColor = '#FFFFFF';
            pill.style.color = '#374151';
        });
        if (btn) {
            btn.classList.add('shadow-xs', 'font-semibold', 'active-pill');
            btn.style.backgroundColor = '#0099FF';
            btn.style.color = '#FFFFFF';
        }

        let visible = 0;
        document.querySelectorAll('#product-reviews-list .review-item').forEach(item => {
            let show;
            if (type === 'all') {
                show = true;
            } else if (type === 'comments') {
                show = item.dataset.hasComment === '1';
            } else if (type === 'media') {
                show = item.dataset.hasMedia === '1';
            } else {
                show = parseInt(item.dataset.rating, 10) === parseInt(type, 10);
            }
            item.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const emptyEl = document.getElementById('reviews-filter-empty');
        if (emptyEl) emptyEl.style.display = visible === 0 ? 'flex' : 'none';
    }
</script>

@push('scripts')
<script>
    const basePrice = {{ (float)($product->effective_price) }};
    const maxQty    = {{ (int)max(1, $product->max_producible_quantity) }};
    const baseUnitProdTime = {{ (int)$baseUnitProdTime }};
    const isCustomProduct  = {{ $showProductionTime ? 'true' : 'false' }};
    const needsArtwork     = {{ $product->requires_design_file ? 'true' : 'false' }};

@php
    $baseMaterialsList = $product->materials->map(fn($m) => [
        'material_id'   => $m->id,
        'name'          => $m->name,
        'unit'          => $m->unit,
        'available_stock' => (float)($m->available_stock ?? 0),
        'quantity_used' => (float)$m->pivot->quantity_used,
    ])->values()->toArray();

    $optionsBOMList = $product->options->map(fn($o) => [
        'id'             => $o->id,
        'option_name'    => $o->option_name,
        'option_value'   => $o->option_value,
        'price_modifier' => (float)$o->price_modifier,
        'has_custom_bom' => $o->materials->isNotEmpty(),
        'materials'      => $o->materials->map(fn($m) => [
            'material_id'   => $m->id,
            'name'          => $m->name,
            'unit'          => $m->unit,
            'available_stock' => (float)($m->available_stock ?? 0),
            'quantity_used' => (float)$m->pivot->quantity_used,
        ])->values()->toArray(),
    ])->values()->toArray();
@endphp
    const baseMaterials = {!! json_encode($baseMaterialsList) !!};
    const optionsBOM = {!! json_encode($optionsBOMList) !!};

    function getSelectedOptions() {
        const selections = {};
        document.querySelectorAll('input[type="radio"]:checked').forEach(r => {
            const name = r.getAttribute('data-option-name') || r.name.replace(/^options\[/, '').replace(/\]$/, '');
            selections[name] = r.value;
        });
        return selections;
    }

    function evaluateStock(qty, selectedOptionsMap) {
        qty = Math.max(1, parseInt(qty) || 1);
        const reqs = {};

        // 1. Base BOM requirements
        baseMaterials.forEach(m => {
            reqs[m.material_id] = {
                material_id: m.material_id,
                name: m.name,
                unit: m.unit,
                required: m.quantity_used * qty,
                available_stock: m.available_stock
            };
        });

        // 2. Extra Material Add-on BOM from selected options
        if (selectedOptionsMap) {
            for (const optName in selectedOptionsMap) {
                const optVal = selectedOptionsMap[optName];
                const optObj = optionsBOM.find(o => 
                    o.option_name.toLowerCase() === optName.toLowerCase() && 
                    o.option_value.toLowerCase() === optVal.toLowerCase()
                );
                if (optObj && optObj.has_custom_bom && optObj.materials.length > 0) {
                    optObj.materials.forEach(m => {
                        const needed = m.quantity_used * qty;
                        if (reqs[m.material_id]) {
                            reqs[m.material_id].required += needed;
                        } else {
                            reqs[m.material_id] = {
                                material_id: m.material_id,
                                name: m.name,
                                unit: m.unit,
                                required: needed,
                                available_stock: m.available_stock
                            };
                        }
                    });
                }
            }
        }

        let isAvailable = true;
        const shortages = [];
        for (const matId in reqs) {
            const item = reqs[matId];
            if (item.required > 0) {
                if (item.available_stock <= 0 || item.available_stock < item.required) {
                    isAvailable = false;
                    shortages.push({
                        name: item.name,
                        unit: item.unit,
                        required: item.required,
                        available: item.available_stock,
                        deficit: Math.max(0, item.required - item.available_stock)
                    });
                }
            }
        }

        return { isAvailable, shortages, requirements: reqs };
    }

    function updateOptionStockStatus() {
        const input = document.getElementById('quantity-input');
        const qty = parseInt(input ? input.value : 1) || 1;
        const currentSelections = getSelectedOptions();

        // Check each option button in all groups
        document.querySelectorAll('.pd-option-group').forEach(groupEl => {
            const groupName = groupEl.getAttribute('data-group-name');

            groupEl.querySelectorAll('.option-pill').forEach(pill => {
                const radio = pill.querySelector('input[type="radio"]');
                if (!radio) return;

                const optVal = radio.value;
                const testSelections = Object.assign({}, currentSelections);
                testSelections[groupName] = optVal;

                const testResult = evaluateStock(qty, testSelections);
                const badge = pill.querySelector('.badge-opt-oos');

                if (!testResult.isAvailable) {
                    pill.classList.add('is-disabled', 'is-out-of-stock');
                    radio.disabled = true;
                    if (badge) badge.style.display = 'inline-flex';
                    pill.title = 'Out of stock for this variant';
                } else {
                    pill.classList.remove('is-disabled', 'is-out-of-stock');
                    radio.disabled = false;
                    if (badge) badge.style.display = 'none';
                    pill.title = '';
                }
            });
        });

        updateCartButtonState(qty, currentSelections);
    }

    function updateCartButtonState(qty, currentSelections) {
        qty = qty || (parseInt(document.getElementById('quantity-input')?.value) || 1);
        currentSelections = currentSelections || getSelectedOptions();

        const currentResult = evaluateStock(qty, currentSelections);
        const btn = document.getElementById('add-to-cart-btn');

        if (!currentResult.isAvailable) {
            if (btn) {
                btn.disabled = true;
                btn.classList.remove('pd-cta-active');
                btn.classList.add('pd-cta-disabled');
                btn.style.opacity = '0.6';
                btn.style.cursor = 'not-allowed';
                btn.style.background = '#F1F5F9';
                btn.style.color = '#64748B';
                btn.style.boxShadow = 'none';
                btn.innerHTML = '<i class="fa-solid fa-ban" style="color:var(--text-danger);"></i> Out of Stock for Selected Options';
            }
        } else {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('pd-cta-disabled');
                btn.classList.add('pd-cta-active');
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
                btn.style.background = 'var(--cmyk-cyan)';
                btn.style.color = '#FFF';
                btn.style.boxShadow = 'none';
                btn.innerHTML = '<i class="fa-solid fa-cart-shopping"></i> ' +
                    (needsArtwork ? 'Upload Artwork & Add to Cart' : 'Add to Cart & Proceed');
            }
        }
    }



    function formatMinutesJs(mins) {
        if (!mins || mins <= 0) return '30 mins';
        if (mins < 60) return mins + (mins === 1 ? ' min' : ' mins');
        const h = Math.floor(mins / 60);
        const rem = mins % 60;
        if (h < 24) {
            if (rem > 0) return h + (h === 1 ? ' hr' : ' hrs') + ' ' + rem + (rem === 1 ? ' min' : ' mins');
            return h + (h === 1 ? ' hr' : ' hrs');
        }
        const d = Math.floor(h / 24);
        const remH = h % 24;
        if (remH > 0) return d + (d === 1 ? ' day' : ' days') + ' ' + remH + (remH === 1 ? ' hr' : ' hrs');
        return d + (d === 1 ? ' day' : ' days');
    }

    function updateProductionTime(qty) {
        if (!isCustomProduct || baseUnitProdTime <= 0) return;
        const totalMinutes = baseUnitProdTime * qty;
        const formatted = formatMinutesJs(totalMinutes);
        const topEl = document.getElementById('header-turnaround-text');
        if (topEl) topEl.innerText = formatted;
    }

    function calculateTotalPrice() {
        let totalModifier = 0;
        document.querySelectorAll('input[type="radio"]:checked').forEach(r => {
            totalModifier += parseFloat(r.getAttribute('data-modifier')) || 0;
        });

        const input = document.getElementById('quantity-input');
        let qty = parseInt(input.value) || 1;
        if (qty < 1) qty = 1;
        if (qty > maxQty) {
            qty = maxQty;
            input.value = qty;
        }

        const unitPrice  = basePrice + totalModifier;
        const totalPrice = unitPrice * qty;

        const fmt = n => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const unitEl  = document.getElementById('unit-price-display');
        const totalEl = document.getElementById('total-price-display');
        if (unitEl)  unitEl.innerText  = fmt(unitPrice);
        if (totalEl) totalEl.innerText = fmt(totalPrice);

        updateProductionTime(qty);
        updateOptionStockStatus();
    }

    function incrementQuantity() {
        const input = document.getElementById('quantity-input');
        let val = parseInt(input.value) || 1;
        if (val < maxQty) {
            input.value = val + 1;
            calculateTotalPrice();
        }
    }

    function decrementQuantity() {
        const input = document.getElementById('quantity-input');
        let val = parseInt(input.value) || 1;
        if (val > 1) {
            input.value = val - 1;
            calculateTotalPrice();
        }
    }

    function handleOptionChange(radio) {
        if (radio.disabled) return;
        const groupName = radio.getAttribute('data-option-name') || radio.name;
        document.querySelectorAll('input[name="' + CSS.escape(radio.name) + '"]').forEach(r => {
            r.closest('.option-pill')?.classList.remove('is-selected');
        });
        radio.closest('.option-pill')?.classList.add('is-selected');
        calculateTotalPrice();
    }

    function toggleWishlist(btn) {
        const icon = btn.querySelector('i');
        if (icon.classList.contains('fa-regular')) {
            icon.classList.replace('fa-regular', 'fa-solid');
            icon.style.color = 'var(--cmyk-magenta)';
        } else {
            icon.classList.replace('fa-solid', 'fa-regular');
            icon.style.color = 'var(--cmyk-magenta)';
        }
    }

    function changeProductImage(imgUrl, thumbWrap) {
        const mainImg = document.getElementById('main-product-img');
        if (mainImg) {
            mainImg.style.opacity = '0.35';
            setTimeout(() => {
                mainImg.src = imgUrl;
                mainImg.style.opacity = '1';
            }, 120);
        }
        document.querySelectorAll('.pd-thumb-wrap').forEach(el => {
            el.style.borderColor = '#E2E8F0';
            el.classList.remove('is-active');
        });
        if (thumbWrap) {
            thumbWrap.style.borderColor = 'var(--cmyk-cyan)';
            thumbWrap.classList.add('is-active');
        }
    }

    // ── Artwork uploads (multiple files) ────────────────────────────
    let artworkFiles = [];

    function syncArtworkInput() {
        const input = document.getElementById('design_file_input');
        if (!input) { return; }
        try {
            const dt = new DataTransfer();
            artworkFiles.forEach((f) => dt.items.add(f));
            input.files = dt.files;
        } catch (e) {
            // Older browsers without DataTransfer — fall back to the browser's own selection
        }
    }

    function removeArtworkFile(index) {
        artworkFiles.splice(index, 1);
        syncArtworkInput();
        renderArtworkFiles();
    }

    function handleArtworkFileSelect(input) {
        const incoming = input.files ? Array.from(input.files) : [];
        incoming.forEach((f) => {
            const dup = artworkFiles.some((x) => x.name === f.name && x.size === f.size && x.lastModified === f.lastModified);
            if (!dup) { artworkFiles.push(f); }
        });
        syncArtworkInput();
        renderArtworkFiles();
    }

    function renderArtworkFiles() {
        const emptyState = document.getElementById('artwork-empty-state');
        const selectedState = document.getElementById('artwork-file-selected');
        const nameEl = document.getElementById('artwork-filename');
        const sizeEl = document.getElementById('artwork-filesize');
        const listEl = document.getElementById('artwork-filelist');
        const addMoreBtn = document.getElementById('artwork-add-more');

        if (!artworkFiles.length) {
            if (emptyState) emptyState.style.display = 'flex';
            if (selectedState) selectedState.style.display = 'none';
            if (listEl) { listEl.innerHTML = ''; listEl.style.display = 'none'; }
            if (addMoreBtn) addMoreBtn.style.display = 'none';
            return;
        }

        if (emptyState) emptyState.style.display = 'none';
        if (selectedState) selectedState.style.display = 'flex';
        if (addMoreBtn) addMoreBtn.style.display = 'inline-flex';

        const totalBytes = artworkFiles.reduce((sum, f) => sum + (f.size || 0), 0);
        const totalKb = (totalBytes / 1024).toFixed(1);
        const totalMb = (totalBytes / (1024 * 1024)).toFixed(2);

        if (nameEl) {
            nameEl.textContent = artworkFiles.length === 1
                ? artworkFiles[0].name
                : `${artworkFiles.length} files attached`;
        }
        if (sizeEl) {
            sizeEl.textContent = artworkFiles.length === 1
                ? (artworkFiles[0].size > 1024 * 1024
                    ? `${(artworkFiles[0].size / (1024 * 1024)).toFixed(2)} MB`
                    : `${(artworkFiles[0].size / 1024).toFixed(1)} KB`)
                : `Total ${totalBytes > 1024 * 1024 ? totalMb + ' MB' : totalKb + ' KB'}`;
        }

        if (listEl) {
            listEl.innerHTML = '';
            listEl.style.display = 'flex';
            artworkFiles.forEach((f, idx) => {
                const row = document.createElement('div');
                row.className = 'artwork-file-item';

                const icon = document.createElement('i');
                icon.className = 'fa-solid fa-file-lines artwork-file-item-icon';

                const name = document.createElement('span');
                name.className = 'artwork-file-item-name';
                name.textContent = f.name;
                name.title = f.name;

                const size = document.createElement('span');
                size.className = 'artwork-file-item-size';
                size.textContent = f.size > 1024 * 1024
                    ? `${(f.size / (1024 * 1024)).toFixed(2)} MB`
                    : `${Math.max(1, Math.round(f.size / 1024))} KB`;

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'artwork-file-remove';
                remove.innerHTML = '<i class="fa-solid fa-xmark"></i> Remove';
                remove.addEventListener('click', (e) => {
                    e.stopPropagation();
                    removeArtworkFile(idx);
                });

                row.appendChild(icon);
                row.appendChild(name);
                row.appendChild(size);
                row.appendChild(remove);
                listEl.appendChild(row);
            });
        }

        clearArtworkValidationError();
    }

    function clearArtworkFile() {
        artworkFiles = [];
        const input = document.getElementById('design_file_input');
        if (input) input.value = '';
        renderArtworkFiles();
        showArtworkValidationError();
    }

    // ── Artwork required validation ──────────────────────
    function showArtworkValidationError() {
        const box = document.getElementById('artwork-required-box');
        const msg = document.getElementById('artwork-error-msg');
        if (box) {
            box.classList.remove('artwork-missing');
            // Restart the shake animation
            void box.offsetWidth;
            box.classList.add('artwork-missing');
        }
        if (msg) msg.classList.add('is-visible');
        if (box) box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function clearArtworkValidationError() {
        const box = document.getElementById('artwork-required-box');
        const msg = document.getElementById('artwork-error-msg');
        if (box) box.classList.remove('artwork-missing');
        if (msg) msg.classList.remove('is-visible');
    }

    // ── Block submission when stock is insufficient or required artwork is missing ──
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('form[action*="cart.add"], form[enctype="multipart/form-data"]');
        const fileInput = document.getElementById('design_file_input');
        if (form) {
            form.addEventListener('submit', (e) => {
                // 1. Dynamic raw material stock validation
                const input = document.getElementById('quantity-input');
                const qty = parseInt(input ? input.value : 1) || 1;
                const status = evaluateStock(qty, getSelectedOptions());
                if (!status.isAvailable) {
                    e.preventDefault();
                    updateOptionStockStatus();
                    // Scroll to the simple disabled "Out of Stock for Selected Options" button (detail alert box removed)
                    const cartBtn = document.getElementById('add-to-cart-btn');
                    if (cartBtn) {
                        cartBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return false;
                }

                // 2. Artwork file check
                if (needsArtwork && fileInput && (!fileInput.files || !fileInput.files.length)) {
                    e.preventDefault();
                    showArtworkValidationError();
                    return false;
                }
            });
        }
    });

    // Drag and drop dropzone support
    document.addEventListener('DOMContentLoaded', () => {
        calculateTotalPrice();

        const dropzone = document.getElementById('artwork-dropzone');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = 'var(--cmyk-cyan)';
                    dropzone.style.background = '#E6F4FF';
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#93C5FD';
                    dropzone.style.background = '#F0F9FF';
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files.length) {
                    const fileInput = document.getElementById('design_file_input');
                    fileInput.files = files;
                    handleArtworkFileSelect(fileInput);
                }
            }, false);
        }
    });
</script>
@endpush
@endsection
