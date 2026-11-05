@extends('layouts.app')

@section('title', 'Shopping Cart - Grafika South')

@push('styles')
<style>
    .cart-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    .cart-header-section {
        margin-bottom: 2rem;
    }
    /* Layout is handled by Tailwind utilities on the markup (Tailwind v4: custom CSS would override utilities) */
    @media (max-width: 640px) {
        .summary-card {
            padding: 1.25rem 1rem !important;
        }
    }
    .cart-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .cart-item-row {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .cart-item-row.removing {
        opacity: 0;
        transform: translateX(30px);
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
        margin-top: 0;
        margin-bottom: 0;
        border: none;
        overflow: hidden;
    }
    .cart-item-img-wrap {
        background: var(--bg-page);
        border: 1.5px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    .cart-stepper {
        display: inline-flex;
        align-items: center;
        background: var(--bg-page);
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        overflow: hidden;
        transition: border-color 0.2s;
    }
    .cart-stepper:focus-within {
        border-color: var(--cmyk-cyan);
        box-shadow: 0 0 0 3px rgba(0, 153, 255, 0.15);
    }
    .cart-stepper-btn {
        background: transparent;
        border: none;
        width: 32px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.15s, color 0.15s;
    }
    .cart-stepper-btn:hover:not(:disabled) {
        background: var(--text-disabled);
        color: var(--text-main);
    }
    .cart-stepper-input {
        width: 52px;
        height: 34px;
        border: none;
        background: transparent;
        text-align: center;
        font-weight: 700;
        font-size: 0.92rem;
        color: var(--text-main);
        outline: none;
        -moz-appearance: textfield;
    }
    .cart-stepper-input::-webkit-outer-spin-button,
    .cart-stepper-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .summary-card {
        background: var(--bg-surface);
        border: 1.5px solid var(--border-color);
        border-radius: 16px;
        padding: 1.6rem;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.05);
        position: sticky;
        top: 90px;
    }
    .empty-cart-state {
        text-align: center;
        padding: 4.5rem 2rem;
        background: var(--bg-surface);
        border-radius: 20px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
        max-width: 680px;
        margin: 1.5rem auto 3rem auto;
    }
    .empty-icon-circle {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent-soft) 0%, var(--accent-soft) 100%);
        border: 2px dashed var(--accent-color);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem auto;
        color: var(--brand-cyan-fg);
        font-size: 2.2rem;
    }
    /* Clear Cart button — appearance:none kills the native Windows button
       bevel/shadow that renders as a grey offset ghost. */
    .clear-cart-btn {
        appearance: none;
        -webkit-appearance: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.42rem 0.8rem;
        background: transparent;
        color: var(--text-danger);
        border: 1px solid var(--danger-border);
        border-radius: 9px;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1.2;
        font-family: inherit;
        cursor: pointer;
        box-shadow: none;
        transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        white-space: nowrap;
    }
    .clear-cart-btn:hover {
        background: var(--danger-soft);
        border-color: var(--danger-border);
        color: var(--text-danger);
    }
    .clear-cart-btn:active {
        background: var(--danger-soft);
    }
    .clear-cart-btn:focus-visible {
        outline: 2px solid var(--accent-color);
        outline-offset: 2px;
    }
    .clear-cart-btn svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }
    .cart-checkbox-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        cursor: pointer;
        user-select: none;
    }
    .cart-checkbox {
        appearance: none;
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid var(--border-color);
        border-radius: 6px;
        background: var(--bg-surface);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        margin: 0;
        position: relative;
    }
    .cart-checkbox:hover {
        border-color: var(--cmyk-cyan, var(--accent-color));
        background: var(--accent-soft);
    }
    .cart-checkbox:checked {
        background-color: var(--cmyk-cyan, var(--accent-color));
        border-color: var(--cmyk-cyan, var(--accent-color));
        box-shadow: none;
    }
    .cart-checkbox:checked::after {
        content: '';
        display: block;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2.5px 2.5px 0;
        transform: rotate(45deg) translate(-1px, -1px);
    }
    .cart-checkbox:indeterminate {
        background-color: var(--cmyk-cyan, var(--accent-color));
        border-color: var(--cmyk-cyan, var(--accent-color));
        box-shadow: none;
    }
    .cart-checkbox:indeterminate::after {
        content: '';
        display: block;
        width: 10px;
        height: 2.5px;
        background: white;
        border-radius: 1px;
    }
    .cart-item-row.unselected {
        background: var(--bg-page);
        opacity: 0.68;
    }
    .cart-item-row.unselected:hover {
        opacity: 0.9;
    }
    .btn-checkout.disabled, .btn-checkout:disabled {
        background: var(--text-subtle) !important;
        border-color: var(--border-strong) !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
        opacity: 0.7;
    }
    .cart-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: var(--text-main);
        color: var(--text-inverse);
        padding: 0.85rem 1.25rem;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .cart-toast.show {
        transform: translateY(0);
        opacity: 1;
    }
    .cart-toast.error {
        background: var(--danger-color);
    }
    .cart-toast.warning {
        background: var(--warning-color);
    }
    .cart-toast.success {
        background: var(--success-color);
    }
    /* Clear Cart confirmation modal — branded replacement for the native confirm() dialog */
    .cart-modal-overlay {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 10000;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .cart-modal-overlay.show { opacity: 1; }
    .cart-modal {
        background: var(--bg-surface);
        width: 100%;
        max-width: 420px;
        border-radius: 18px;
        padding: 1.75rem 1.6rem 1.4rem;
        text-align: center;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
        transform: translateY(12px) scale(0.98);
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .cart-modal-overlay.show .cart-modal { transform: translateY(0) scale(1); }
    .cart-modal-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: var(--danger-soft);
        color: var(--text-danger);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .cart-modal-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--text-main);
        margin: 0 0 0.4rem;
        font-family: 'Outfit', sans-serif;
    }
    .cart-modal-text {
        font-size: 0.9rem;
        color: var(--text-muted);
        line-height: 1.5;
        margin: 0 0 1.5rem;
    }
    .cart-modal-actions { display: flex; gap: 0.75rem; }
    .cart-modal-btn {
        flex: 1;
        appearance: none;
        -webkit-appearance: none;
        border-radius: 10px;
        padding: 0.65rem 1rem;
        font-size: 0.9rem;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        box-shadow: none;
        transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    }
    .cart-modal-btn.cancel {
        background: var(--bg-surface);
        color: var(--text-muted);
        border: 1.5px solid var(--border-color);
    }
    .cart-modal-btn.cancel:hover { background: var(--bg-page); border-color: var(--border-color); color: var(--text-main); }
    .cart-modal-btn.confirm {
        background: var(--danger-color);
        color: var(--text-inverse);
        border: 1.5px solid var(--danger-color);
    }
    .cart-modal-btn.confirm:hover { background: var(--danger-color); border-color: var(--danger-color); }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-6px); }
        40%, 80% { transform: translateX(6px); }
    }
</style>
@endpush

@section('content')
<div class="cart-container">
    <div class="cart-header-section">
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.4rem;">
            <a href="{{ route('products.index') }}" style="color:var(--brand-cyan-fg); font-weight:700; font-size:0.84rem; text-decoration:none; display:inline-flex; align-items:center; gap:0.35rem;">
                <i class="fa-solid fa-arrow-left"></i> Catalog
            </a>
            <span style="color:var(--text-disabled);">/</span>
            <span style="color:var(--text-muted); font-size:0.84rem; font-weight:600;">Shopping Cart</span>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:1rem;">
            <div>
                <h1 style="font-size: 2.1rem; font-weight: 800; color: var(--text-main); margin:0 0 0.35rem 0; font-family:'Outfit', sans-serif; letter-spacing:-0.02em;">
                    Shopping Cart
                </h1>
            </div>
            @if(count($cart) > 0)
                <div id="cart-item-count-badge" style="background:var(--bg-subtle); border:1px solid var(--border-color); padding:0.4rem 0.85rem; border-radius:999px; font-size:0.84rem; font-weight:700; color:var(--text-main);">
                    <i class="fa-solid fa-layer-group" style="color:var(--brand-cyan-fg); margin-right:0.3rem;"></i>
                    <span id="header-items-count">{{ count($cart) }}</span> {{ count($cart) === 1 ? 'Product' : 'Products' }}
                </div>
            @endif
        </div>
    </div>

    {{-- CART CONTENT SECTION --}}
    <div id="cart-content-wrapper" style="{{ count($cart) === 0 ? 'display: none;' : '' }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- CART ITEMS LIST -->
            <div class="cart-card lg:col-span-2 min-w-0">
                <div class="flex items-center justify-between p-3 bg-[var(--bg-subtle)] rounded-xl mb-4 border border-[var(--border-color)] gap-2">
                    <!-- Left: Select All + count on one line -->
                    <div class="flex items-center gap-2 text-xs font-semibold text-[var(--text-main)] min-w-0">
                        <label class="cart-checkbox-wrapper" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin: 0;" title="Select or deselect all items in cart">
                            <input type="checkbox" id="select-all-checkbox" class="cart-checkbox" {{ ($selectedCount ?? count($cart)) === count($cart) && count($cart) > 0 ? 'checked' : '' }} onchange="toggleSelectAll(this)">
                            <span>Select All (<span id="selected-items-count">{{ $selectedCount ?? count($cart) }}</span>/<span id="cart-list-count">{{ count($cart) }}</span>)</span>
                        </label>
                    </div>

                    <!-- Right: Clear Cart -->
                    <button type="button" onclick="openClearCartModal()" class="clear-cart-btn"
                            title="Remove selected items from your cart">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Remove Selected</span>
                    </button>
                </div>

                <div id="cart-items-container" class="flex flex-col gap-3 p-3 sm:p-4">
                    @foreach($cart as $key => $item)
                        @php
                            $unitPrice = (float) $item['price'];
                            $qty = (int) $item['quantity'];
                            $lineTotal = $unitPrice * $qty;
                            $productId = $item['product_id'] ?? null;
                            $imgPath = $item['image_path'] ?? null;
                            $isSelected = !isset($item['selected']) || $item['selected'] === true;
                        @endphp
                        <div class="cart-item-row flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-[var(--bg-surface)] rounded-xl border border-[var(--border-color)] shadow-sm relative {{ !$isSelected ? 'unselected' : '' }}" id="cart-row-{{ $key }}" data-cart-key="{{ $key }}" data-unit-price="{{ $unitPrice }}">
                            <!-- LEFT GROUP: checkbox + image + details -->
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <!-- Item Selection Checkbox -->
                                <label class="cart-checkbox-wrapper shrink-0" title="Select this item for checkout">
                                    <input type="checkbox"
                                           class="cart-checkbox cart-item-checkbox"
                                           id="checkbox-item-{{ $key }}"
                                           data-cart-key="{{ $key }}"
                                           {{ $isSelected ? 'checked' : '' }}
                                           onchange="toggleItemSelection('{{ $key }}', this)">
                                </label>

                                <!-- Thumbnail / Icon -->
                                <div class="cart-item-img-wrap w-16 h-16 rounded-lg shrink-0">
                                    @if(!empty($imgPath))
                                        <img src="{{ asset('storage/' . $imgPath) }}" alt="{{ $item['name'] }}" class="w-full h-full rounded-lg object-cover">
                                    @else
                                        <i class="fa-solid fa-print" style="font-size:1.6rem; color:var(--brand-cyan-fg);"></i>
                                    @endif
                                </div>

                                <!-- Product Details -->
                                <div class="flex-1 min-w-0">
                                    <div style="display:flex; align-items:baseline; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.25rem;">
                                        <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin:0; overflow-wrap: break-word;">
                                            @if($productId)
                                                <a href="{{ route('products.show', $productId) }}" style="color:inherit; text-decoration:none;" onmouseover="this.style.color='var(--cmyk-cyan)'" onmouseout="this.style.color='var(--text-main)'">
                                                    {{ $item['name'] }}
                                                </a>
                                            @else
                                                {{ $item['name'] }}
                                            @endif
                                        </h3>
                                        @if(!empty($item['category']))
                                            <span style="font-size:0.72rem; font-weight:700; color:var(--text-inverse); background:var(--text-main); padding:0.15rem 0.45rem; border-radius:4px; text-transform:uppercase;">
                                                {{ $item['category'] }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- SELECTED OPTIONS BREAKDOWN -->
                                    @if(!empty($item['options']))
                                        @php
                                            // Optional per-option reference image (cached per product)
                                            $optionImages = !empty($item['product_id'])
                                                ? \App\Models\ProductOption::imageMapFor((int) $item['product_id'])
                                                : [];
                                        @endphp
                                        <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-bottom: 0.4rem;">
                                            @foreach($item['options'] as $optKey => $optVal)
                                                @php $optImg = $optionImages[\App\Models\ProductOption::imageMapKey($optKey, $optVal)] ?? null; @endphp
                                                 <span style="display:inline-flex; align-items:center; gap:0.3rem; background: var(--bg-subtle); color: var(--text-muted); font-size: 0.76rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 6px; border:1px solid var(--border-color);">
                                                    @if($optImg)
                                                        <img src="{{ $optImg }}" alt="" loading="lazy" style="width:18px; height:18px; border-radius:4px; object-fit:cover; border:1px solid var(--border-color); background:var(--bg-surface); flex-shrink:0;">
                                                    @endif
                                                    <span style="color:var(--text-muted);">{{ $optKey }}:</span> {{ $optVal }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- FILE ATTACHMENT & SPECIAL INSTRUCTIONS -->
                                    <div style="display:flex; flex-direction:column; gap:0.2rem;">
                                        @php
                                            $cartFiles = $item['design_file_paths'] ?? [];
                                            if (!is_array($cartFiles)) { $cartFiles = []; }
                                            if (empty($cartFiles) && !empty($item['design_file_path'])) {
                                                $cartFiles = [$item['design_file_path']];
                                            }
                                        @endphp
                                        @if(!empty($cartFiles))
                                            <div style="font-size: 0.78rem; color: var(--text-success); font-weight: 700; display:inline-flex; align-items:center; gap:0.35rem;">
                                                <i class="fa-solid fa-circle-check"></i>
                                                {{ count($cartFiles) > 1 ? count($cartFiles) . ' artwork files attached' : 'Artwork file attached' }}
                                            </div>
                                            @foreach($cartFiles as $cartFile)
                                                <a href="{{ asset('storage/' . $cartFile) }}" target="_blank" style="font-size:0.74rem; color:var(--text-success); font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem; word-break:break-all;">
                                                    <i class="fa-solid fa-paperclip" style="font-size:0.68rem;"></i> {{ basename($cartFile) }}
                                                </a>
                                            @endforeach
                                        @endif

                                        @if(!empty($item['special_instructions']))
                                            <div style="font-size: 0.78rem; color: var(--text-muted); font-style: italic; display:flex; align-items:baseline; gap:0.3rem;">
                                                <i class="fa-solid fa-message" style="font-size:0.7rem; color:var(--text-subtle);"></i>
                                                <span>"{{ Str::limit($item['special_instructions'], 75) }}"</span>
                                            </div>
                                        @endif

                                        {{-- Production Time Badge (custom items only) --}}
                                        @php
                                            $cartItemIsCustom = !empty($item['design_file_path'])
                                                || !empty($item['requires_design_file'])
                                                || !empty($item['requires_custom_terms']);
                                            $cartItemProdTime = $item['formatted_production_time'] ?? null;
                                        @endphp
                                        @if($cartItemIsCustom && $cartItemProdTime)
                                            <div id="item-prod-time-badge-{{ $key }}" style="display:inline-flex; align-items:center; gap:0.35rem; font-size:0.76rem; font-weight:700; color:var(--text-warning); background:var(--warning-soft); border:1px solid var(--warning-border); padding:0.2rem 0.55rem; border-radius:9999px; margin-top:0.2rem; width:fit-content;">
                                                <i class="fa-solid fa-stopwatch" style="font-size:0.68rem;"></i>
                                                <span id="item-prod-time-text-{{ $key }}">Est. Production: {{ $cartItemProdTime }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT GROUP: price + quantity selector + delete icon -->
                            <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-4 shrink-0 mt-2 sm:mt-0 pt-2 sm:pt-0 border-t sm:border-0 border-[var(--border-color)]">
                                <!-- Price block -->
                                <div class="text-right font-bold text-base" style="color:var(--text-main);">
                                    <div style="white-space: nowrap; font-family:'Outfit', sans-serif;" id="item-total-{{ $key }}">
                                        &#8369;{{ number_format($lineTotal, 2) }}
                                    </div>
                                    <div style="font-size: 0.74rem; color: var(--text-muted); font-weight:500; white-space: nowrap;">
                                        &#8369;{{ number_format($unitPrice, 2) }} / unit
                                    </div>
                                </div>

                                <!-- Quantity Selector -->
                                <div class="cart-stepper shrink-0">
                                    <button type="button" class="cart-stepper-btn" onclick="stepQty('{{ $key }}', -1)" title="Decrease Quantity">&#8722;</button>
                                    <input type="number"
                                           id="qty-input-{{ $key }}"
                                           value="{{ $qty }}"
                                           min="1"
                                           class="cart-stepper-input"
                                           data-key="{{ $key }}"
                                           data-price="{{ $unitPrice }}"
                                           data-last-val="{{ $qty }}"
                                           oninput="handleQtyInput('{{ $key }}', this)"
                                           onchange="commitQtyChange('{{ $key }}', this)">
                                    <button type="button" class="cart-stepper-btn" onclick="stepQty('{{ $key }}', 1)" title="Increase Quantity">+</button>
                                </div>

                                <!-- Trash icon — anchored far right -->
                                <button type="button"
                                        onclick="removeCartItem('{{ $key }}')"
                                        title="Remove item"
                                        aria-label="Remove item"
                                        class="w-9 h-9 rounded-xl bg-transparent border border-[var(--border-strong)] hover:bg-[var(--danger-soft)] hover:border-red-500 text-red-500 hover:text-red-600 flex items-center justify-center transition-all duration-200 outline-none focus:outline-none shrink-0 active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- ORDER SUMMARY & CHECKOUT BUTTON -->
            <div class="lg:col-span-1 min-w-0">
                <div class="summary-card">
                    <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin:0 0 1.25rem 0; border-bottom: 1.5px solid var(--border-color); padding-bottom: 0.85rem; font-family:'Outfit', sans-serif;">
                        Order Estimate Summary
                    </h2>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.85rem; font-size: 0.92rem; color: var(--text-muted);">
                        <span>Selected Subtotal (<span id="summary-items-count">{{ $selectedCount ?? count($cart) }}</span> <span id="summary-items-unit">{{ ($selectedCount ?? count($cart)) === 1 ? 'item' : 'items' }}</span>):</span>
                        <strong style="color: var(--text-main); font-weight:800;" id="summary-subtotal">
                            &#8369;{{ number_format($subtotal, 2) }}
                        </strong>
                    </div>

                    <div style="border-top: 1.5px solid var(--border-color); padding-top: 1.1rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="font-weight: 800; font-size: 1.05rem; color: var(--text-main); display:block;">Total Estimate:</span>
                            <span style="font-size: 0.72rem; color: var(--text-muted);" id="summary-helper-text">
                                {{ ($selectedCount ?? count($cart)) > 0 ? 'Final payment on checkout' : 'No items selected' }}
                            </span>
                        </div>
                        <span style="font-size: 1.7rem; font-weight: 800; color: var(--text-main); font-family:'Outfit',sans-serif;" id="summary-total">
                            &#8369;{{ number_format($total ?? $subtotal, 2) }}
                        </span>
                    </div>

                    <a href="{{ route('checkout.index') }}" 
                       id="btn-proceed-checkout" 
                       class="btn btn-primary btn-checkout w-full py-3 text-sm font-bold rounded-xl {{ ($selectedCount ?? count($cart)) === 0 ? 'disabled' : '' }}" 
                       onclick="handleCheckoutClick(event)"
                       style="width: 100%; padding: 0.9rem 1rem; font-size: 1rem; font-weight: 700; text-align: center; border-radius: 12px; display:flex; align-items:center; justify-content:center; gap:0.5rem; box-shadow: none;">
                        <span>Proceed to Checkout</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <div id="checkout-warning-msg" style="display: {{ ($selectedCount ?? count($cart)) === 0 ? 'block' : 'none' }}; font-size: 0.78rem; color: var(--text-danger); font-weight: 600; text-align: center; margin-top: 0.5rem;">
                        <i class="fa-solid fa-circle-exclamation"></i> Select at least 1 item to proceed to checkout
                    </div>

                    <a href="{{ route('products.index') }}" class="btn btn-outline" style="width: 100%; padding: 0.7rem 1rem; font-size: 0.88rem; font-weight: 600; text-align: center; margin-top: 0.75rem; border-radius: 12px; display:flex; align-items:center; justify-content:center; gap:0.4rem; color:var(--text-muted); border-color:var(--border-color);">
                        <i class="fa-solid fa-shop"></i>
                        <span>Continue Browsing Products</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- EMPTY CART STATE --}}
    <div id="empty-cart-container" class="empty-cart-state" style="{{ count($cart) > 0 ? 'display: none;' : '' }}">
        <div class="empty-icon-circle">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
        <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem; font-family:'Outfit', sans-serif;">
            Your shopping cart is currently empty
        </h2>
        <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 440px; margin: 0 auto 1.75rem auto; line-height: 1.6;">
            Looks like you haven't added any custom print projects yet. Browse our print catalog to configure sizes, materials, and upload your artwork.
        </p>
        <a href="{{ route('products.index') }}" class="btn btn-primary" style="padding: 0.85rem 2rem; font-size: 1rem; font-weight: 700; border-radius: 12px; display:inline-flex; align-items:center; gap:0.55rem;">
            <i class="fa-solid fa-layer-group"></i>
            <span>Browse Products Catalog</span>
        </a>
    </div>
</div>

<!-- Toast notification box -->
<div id="cart-toast" class="cart-toast">
    <i id="cart-toast-icon" class="fa-solid fa-circle-check"></i>
    <span id="cart-toast-message">Notification</span>
</div>

<!-- Clear Cart confirmation modal (replaces native confirm dialog) -->
<div id="clear-cart-modal" class="cart-modal-overlay" onclick="if(event.target===this) closeClearCartModal()">
    <div class="cart-modal" role="dialog" aria-modal="true" aria-labelledby="clear-cart-modal-title">
        <div class="cart-modal-icon"><i class="fa-solid fa-trash-can"></i></div>
        <h3 id="clear-cart-modal-title" class="cart-modal-title">Remove selected items?</h3>
        <p class="cart-modal-text">Are you sure you want to remove the <strong id="remove-selected-count" style="color:var(--text-danger);"></strong> selected item(s) from your cart? This action cannot be undone.</p>
        <div class="cart-modal-actions">
            <button type="button" class="cart-modal-btn cancel" onclick="closeClearCartModal()">Cancel</button>
            <button type="button" class="cart-modal-btn confirm" onclick="clearCartNow()">Yes, Remove</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let debounceTimers = {};

    function showToast(message, isError = false) {
        const toast = document.getElementById('cart-toast');
        const icon = document.getElementById('cart-toast-icon');
        const msg = document.getElementById('cart-toast-message');

        toast.className = 'cart-toast ' + (isError ? 'error' : 'success');
        icon.className = 'fa-solid ' + (isError ? 'fa-circle-exclamation' : 'fa-circle-check');
        msg.textContent = message;

        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3500);
    }

    function formatPeso(amount) {
        return '₱' + parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateBadgeCount(count) {
        const badges = document.querySelectorAll('.nav-cart-badge');
        badges.forEach(badge => {
            badge.textContent = count;
            if (count > 0) {
                badge.classList.add('has-items');
            } else {
                badge.classList.remove('has-items');
            }
        });

        const headerCount = document.getElementById('header-items-count');
        if (headerCount) headerCount.textContent = count;

        const listCount = document.getElementById('cart-list-count');
        if (listCount) listCount.textContent = count;
    }

    function updateMasterCheckboxState() {
        const checkboxes = document.querySelectorAll('.cart-item-checkbox');
        const masterCheckbox = document.getElementById('select-all-checkbox');
        if (!masterCheckbox || checkboxes.length === 0) return;

        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        if (checkedCount === 0) {
            masterCheckbox.checked = false;
            masterCheckbox.indeterminate = false;
        } else if (checkedCount === checkboxes.length) {
            masterCheckbox.checked = true;
            masterCheckbox.indeterminate = false;
        } else {
            masterCheckbox.checked = false;
            masterCheckbox.indeterminate = true;
        }
    }

    function recalculateClientSubtotal() {
        let total = 0;
        let selectedCount = 0;
        const rows = document.querySelectorAll('.cart-item-row:not(.removing)');
        
        rows.forEach(row => {
            const key = row.dataset.cartKey;
            const price = parseFloat(row.dataset.unitPrice) || 0;
            const input = document.getElementById(`qty-input-${key}`);
            const qty = parseInt(input?.value) || 0;
            const checkbox = document.getElementById(`checkbox-item-${key}`);

            if (checkbox && checkbox.checked) {
                total += (price * qty);
                selectedCount++;
            }
        });

        const formatted = formatPeso(total);
        const subtotalEl = document.getElementById('summary-subtotal');
        const totalEl = document.getElementById('summary-total');
        const selectedCountEl = document.getElementById('selected-items-count');
        const summaryCountEl = document.getElementById('summary-items-count');
        const summaryUnitEl = document.getElementById('summary-items-unit');
        const helperTextEl = document.getElementById('summary-helper-text');
        const checkoutBtn = document.getElementById('btn-proceed-checkout');
        const warningMsg = document.getElementById('checkout-warning-msg');

        if (subtotalEl) subtotalEl.textContent = formatted;
        if (totalEl) totalEl.textContent = formatted;
        if (selectedCountEl) selectedCountEl.textContent = selectedCount;
        if (summaryCountEl) summaryCountEl.textContent = selectedCount;
        if (summaryUnitEl) summaryUnitEl.textContent = selectedCount === 1 ? 'item' : 'items';

        if (helperTextEl) {
            helperTextEl.textContent = selectedCount > 0 ? 'Final payment on checkout' : 'No items selected';
        }

        // Checkout button validation & warning toggle
        if (checkoutBtn) {
            if (selectedCount === 0) {
                checkoutBtn.classList.add('disabled');
                checkoutBtn.setAttribute('aria-disabled', 'true');
            } else {
                checkoutBtn.classList.remove('disabled');
                checkoutBtn.removeAttribute('aria-disabled');
            }
        }

        if (warningMsg) {
            warningMsg.style.display = selectedCount === 0 ? 'block' : 'none';
        }

        updateMasterCheckboxState();

        return { total, selectedCount, discount, finalTotal };
    }

    function handleCheckoutClick(event) {
        const { selectedCount } = recalculateClientSubtotal();
        if (selectedCount === 0) {
            event.preventDefault();
            showToast('Please select at least one item to proceed to checkout.', true);
            const warningMsg = document.getElementById('checkout-warning-msg');
            if (warningMsg) {
                warningMsg.style.animation = 'none';
                warningMsg.offsetHeight; // Trigger reflow
                warningMsg.style.animation = 'shake 0.4s ease';
            }
        }
    }

    async function toggleItemSelection(key, checkboxElem) {
        const row = document.getElementById(`cart-row-${key}`);
        if (row) {
            row.classList.toggle('unselected', !checkboxElem.checked);
        }

        recalculateClientSubtotal();

        try {
            const response = await fetch("{{ route('cart.toggle-select') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": CSRF_TOKEN,
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({
                    key: key,
                    selected: checkboxElem.checked
                })
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                // Revert checkbox state on error
                checkboxElem.checked = !checkboxElem.checked;
                if (row) row.classList.toggle('unselected', !checkboxElem.checked);
                recalculateClientSubtotal();
                showToast(data.message || 'Failed to update selection.', true);
            }
        } catch (err) {
            console.error(err);
            checkboxElem.checked = !checkboxElem.checked;
            if (row) row.classList.toggle('unselected', !checkboxElem.checked);
            recalculateClientSubtotal();
            showToast('Network error while updating selection.', true);
        }
    }

    async function toggleSelectAll(masterCheckbox) {
        const isChecked = masterCheckbox.checked;
        const checkboxes = document.querySelectorAll('.cart-item-checkbox');
        
        checkboxes.forEach(cb => {
            cb.checked = isChecked;
            const row = cb.closest('.cart-item-row');
            if (row) {
                row.classList.toggle('unselected', !isChecked);
            }
        });

        recalculateClientSubtotal();

        try {
            const response = await fetch("{{ route('cart.select-all') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": CSRF_TOKEN,
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({
                    selected: isChecked
                })
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                showToast(data.message || 'Failed to update all selections.', true);
            }
        } catch (err) {
            console.error(err);
            showToast('Network error while updating all selections.', true);
        }
    }

    function handleQtyInput(key, inputElem) {
        let qty = parseInt(inputElem.value);
        if (isNaN(qty) || qty < 1) {
            qty = 1;
        }

        const price = parseFloat(inputElem.dataset.price) || 0;
        const lineTotal = price * qty;

        const lineEl = document.getElementById(`item-total-${key}`);
        if (lineEl) {
            lineEl.textContent = formatPeso(lineTotal);
        }

        recalculateClientSubtotal();

        // Debounce server update
        if (debounceTimers[key]) clearTimeout(debounceTimers[key]);
        debounceTimers[key] = setTimeout(() => {
            sendUpdateToServer(key, qty, inputElem);
        }, 450);
    }

    function commitQtyChange(key, inputElem) {
        let qty = parseInt(inputElem.value);
        if (isNaN(qty) || qty < 1) {
            qty = 1;
            inputElem.value = 1;
        }
        if (debounceTimers[key]) clearTimeout(debounceTimers[key]);
        sendUpdateToServer(key, qty, inputElem);
    }

    function stepQty(key, delta) {
        const input = document.getElementById(`qty-input-${key}`);
        if (!input) return;

        let currentVal = parseInt(input.value) || 1;
        let newVal = Math.max(1, currentVal + delta);
        if (newVal === currentVal) return;

        input.value = newVal;
        handleQtyInput(key, input);
    }

    async function sendUpdateToServer(key, quantity, inputElem) {
        try {
            const response = await fetch("{{ route('cart.update') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": CSRF_TOKEN,
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({
                    key: key,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                // Revert to last valid value if stock check failed
                const lastVal = inputElem.dataset.lastVal || 1;
                inputElem.value = lastVal;
                handleQtyInput(key, inputElem);
                showToast(data.message || 'Unable to update quantity due to stock constraints.', true);
                return;
            }

            inputElem.dataset.lastVal = quantity;
            if (data.formatted_item_total) {
                const lineEl = document.getElementById(`item-total-${key}`);
                if (lineEl) lineEl.textContent = data.formatted_item_total;
            }
            if (data.formatted_production_time) {
                const prodTextEl = document.getElementById(`item-prod-time-text-${key}`);
                if (prodTextEl) {
                    prodTextEl.textContent = 'Est. Production: ' + data.formatted_production_time;
                }
            }
            if (typeof data.cart_count !== 'undefined') {
                updateBadgeCount(data.cart_count);
            }
            recalculateClientSubtotal();
        } catch (err) {
            console.error(err);
            showToast('Network error while updating cart.', true);
        }
    }

    async function removeCartItem(key) {
        const row = document.getElementById(`cart-row-${key}`);
        if (!row) return;

        // Visual remove animation
        row.classList.add('removing');

        try {
            const removeUrl = "{{ url('/cart/remove') }}/" + encodeURIComponent(key);
            const response = await fetch(removeUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": CSRF_TOKEN,
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                row.classList.remove('removing');
                showToast(data.message || 'Failed to remove item.', true);
                return;
            }

            // Remove from DOM after animation
            setTimeout(() => {
                row.remove();
                updateBadgeCount(data.cart_count);

                if (data.empty || data.cart_count === 0) {
                    showEmptyCartState();
                } else {
                    recalculateClientSubtotal();
                }
            }, 300);

            showToast('Item removed from cart.');
        } catch (err) {
            row.classList.remove('removing');
            console.error(err);
            showToast('Network error while removing item.', true);
        }
    }

    function openClearCartModal() {
        const checked = document.querySelectorAll('.cart-item-checkbox:checked');
        if (checked.length === 0) {
            showToast('Please select the items you want to remove.', true);
            return;
        }
        const m = document.getElementById('clear-cart-modal');
        if (!m) return;
        const countEl = document.getElementById('remove-selected-count');
        if (countEl) countEl.textContent = checked.length;
        m.style.display = 'flex';
        requestAnimationFrame(() => m.classList.add('show'));
        document.addEventListener('keydown', clearCartEscHandler);
    }

    function closeClearCartModal() {
        const m = document.getElementById('clear-cart-modal');
        if (!m) return;
        m.classList.remove('show');
        setTimeout(() => { m.style.display = 'none'; }, 200);
        document.removeEventListener('keydown', clearCartEscHandler);
    }

    function clearCartEscHandler(e) {
        if (e.key === 'Escape') closeClearCartModal();
    }

    async function clearCartNow() {
        const keys = Array.from(document.querySelectorAll('.cart-item-checkbox:checked')).map(cb => cb.dataset.cartKey);
        if (keys.length === 0) {
            closeClearCartModal();
            showToast('Please select the items you want to remove.', true);
            return;
        }

        const rows = keys.map(k => document.getElementById(`cart-row-${k}`)).filter(Boolean);
        rows.forEach(r => r.classList.add('removing'));

        try {
            const response = await fetch("{{ route('cart.remove-selected') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": CSRF_TOKEN,
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({ keys: keys })
            });

            const data = await response.json();
            if (response.ok && data.success) {
                closeClearCartModal();
                setTimeout(() => {
                    rows.forEach(r => r.remove());
                    updateBadgeCount(data.cart_count);
                    if (data.empty || data.cart_count === 0) {
                        showEmptyCartState();
                    } else {
                        recalculateClientSubtotal();
                    }
                }, 300);
                showToast(data.message || 'Selected items removed from cart.');
            } else {
                rows.forEach(r => r.classList.remove('removing'));
                showToast(data.message || 'Failed to remove selected items.', true);
            }
        } catch (err) {
            rows.forEach(r => r.classList.remove('removing'));
            console.error(err);
            showToast('Network error while removing selected items.', true);
        }
    }

    function showEmptyCartState() {
        const contentWrapper = document.getElementById('cart-content-wrapper');
        const emptyState = document.getElementById('empty-cart-container');
        const countBadge = document.getElementById('cart-item-count-badge');

        if (contentWrapper) contentWrapper.style.display = 'none';
        if (countBadge) countBadge.style.display = 'none';
        if (emptyState) emptyState.style.display = 'block';
    }

    // Initialize master checkbox state + surface incoming flash messages as the bottom-right toast (same as remove/clear)
    document.addEventListener('DOMContentLoaded', () => {
        updateMasterCheckboxState();

        @if(session('success'))
        showToast(@json(session('success')));
        @endif
        @if(session('error'))
        showToast(@json(session('error')), true);
        @endif
        @if(session('warning'))
        showToast(@json(session('warning')), true);
        @endif
    });
</script>
@endpush
