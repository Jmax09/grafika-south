@extends('layouts.admin')

@section('title', 'New Purchase Order | Grafika South')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Ensure the Flatpickr generated altInput (class from altInputClass) has full width & matching styling */
    .dd-date-alt {
        width: 100% !important;
        padding: 0.65rem 2.4rem 0.65rem 0.95rem !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 10px !important;
        font-size: 0.9rem !important;
        outline: none !important;
        background: var(--bg-surface) !important;
        color: var(--text-main) !important;
        box-sizing: border-box !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        display: block !important;
    }
    .dd-date-alt::placeholder {
        color: var(--text-subtle) !important;
    }
    .dd-date-alt:focus {
        border-color: var(--accent-color) !important;
        background: var(--bg-surface) !important;
        box-shadow: 0 0 0 3px rgba(0, 153, 255, 0.15) !important;
    }

    /* Modern Custom Flatpickr Theme matching Grafika South Design */
    .flatpickr-calendar {
        background: var(--bg-surface) !important;
        border-radius: 16px !important;
        border: 1px solid var(--border-color) !important;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08) !important;
        font-family: 'Inter', sans-serif !important;
        padding: 0.75rem !important;
        width: 320px !important;
    }
    .flatpickr-innerContainer {
        width: 100% !important;
    }
    .flatpickr-rContainer {
        width: 100% !important;
    }
    .flatpickr-days {
        width: 100% !important;
    }
    .dayContainer {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        justify-content: space-between !important;
    }
    .flatpickr-calendar:before, .flatpickr-calendar:after {
        border-bottom-color: var(--border-color) !important;
    }
    .flatpickr-months {
        position: relative !important;
        background: var(--bg-page) !important;
        border-radius: 12px !important;
        padding: 0.6rem 0.5rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: 44px !important;
    }
    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month {
        position: absolute !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 30px !important;
        height: 30px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 8px !important;
        padding: 0 !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
    }
    .flatpickr-months .flatpickr-prev-month {
        left: 8px !important;
    }
    .flatpickr-months .flatpickr-next-month {
        right: 8px !important;
    }
    .flatpickr-months .flatpickr-prev-month:hover,
    .flatpickr-months .flatpickr-next-month:hover {
        background: var(--accent-soft) !important;
    }
    .flatpickr-months .flatpickr-prev-month svg,
    .flatpickr-months .flatpickr-next-month svg {
        width: 14px !important;
        height: 14px !important;
        vertical-align: middle !important;
    }
    .flatpickr-current-month {
        position: static !important;
        width: auto !important;
        height: auto !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 0.95rem !important;
        font-weight: 800 !important;
        color: var(--text-main) !important;
        padding: 0 !important;
        left: auto !important;
        transform: none !important;
    }
    .flatpickr-current-month .cur-month {
        font-weight: 800 !important;
        color: var(--text-main) !important;
    }
    .flatpickr-monthDropdown-months {
        font-weight: 800 !important;
        color: var(--text-main) !important;
    }
    .flatpickr-weekdays {
        background: transparent !important;
        margin-top: 0.5rem !important;
        width: 100% !important;
    }
    .flatpickr-weekdaycontainer {
        display: flex !important;
        width: 100% !important;
        justify-content: space-between !important;
    }
    span.flatpickr-weekday {
        color: var(--text-muted) !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        flex: 1 !important;
    }
    .flatpickr-day {
        border-radius: 8px !important;
        font-weight: 600 !important;
        color: var(--text-main) !important;
        height: 38px !important;
        line-height: 38px !important;
        max-width: 38px !important;
        flex-basis: 14.28% !important;
        transition: all 0.15s ease !important;
        margin: 1px 0 !important;
    }
    .flatpickr-day:hover {
        background: var(--accent-soft) !important;
        color: var(--text-accent) !important;
        border-color: transparent !important;
    }
    .flatpickr-day.today {
        border-color: var(--accent-color) !important;
        color: var(--text-accent) !important;
        background: var(--accent-soft) !important;
        font-weight: 800 !important;
    }
    .flatpickr-day.selected, .flatpickr-day.selected:hover {
        background: var(--accent-color) !important;
        color: var(--text-inverse) !important;
        border-color: var(--accent-color) !important;
        font-weight: 800 !important;
        box-shadow: 0 4px 10px rgba(0, 153, 255, 0.3) !important;
    }
    .flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay {
        color: var(--text-disabled) !important;
    }
    .flatpickr-months .flatpickr-prev-month:hover svg, 
    .flatpickr-months .flatpickr-next-month:hover svg {
        fill: var(--accent-color) !important;
    }
</style>
@endpush

@section('content')
@php
    $oldItems = collect(old('items', []))->map(fn ($row, $i) => [
        'key' => $i,
        'supplier_material_id' => (string) ($row['supplier_material_id'] ?? ''),
        'quantity' => (int) ($row['quantity'] ?? 1),
    ])->values();

    $initial = [
        'supplierId' => (string) old('supplier_id', request('supplier', '')),
        'rows' => $oldItems->all(),
    ];
@endphp

<script type="application/json" id="poCatalog">@json($catalog)</script>

<div x-data="poForm(@js($initial))" style="padding-bottom: 2rem;">

    {{-- ── Top Navigation & Header ── --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="margin-bottom: 0.75rem;">
                <a href="{{ route('admin.purchases.index') }}" class="btn btn-primary"
                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; font-size: 0.92rem; font-weight: 700; border-radius: 12px; text-decoration: none; box-shadow: none;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Purchase Orders
                </a>
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-file-invoice text-sky-500" style="color: var(--text-accent);"></i> New Purchase Order
            </h1>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 0.25rem;">
                Pick a supplier, then choose items from their catalog — prices and conversions fill in automatically.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline" style="border-radius: 10px; padding: 0.55rem 1.1rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-truck-field" style="color: var(--text-accent);"></i> Suppliers
            </a>
        </div>
    </div>

    @if($errors->any())
        <div style="background: var(--danger-soft); border: 1.5px solid var(--danger-border); border-radius: 14px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
            <p style="font-weight: 800; color: var(--text-danger); font-size: 0.88rem; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-triangle-exclamation"></i> Please fix the following:
            </p>
            <ul style="margin: 0; padding-left: 1.2rem; color: var(--text-danger); font-size: 0.85rem; line-height: 1.5;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.purchases.store') }}" method="POST" id="poForm">
        @csrf

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;" class="po-layout-grid">

            {{-- ══ Left Column: Order Details & Line Items ══ --}}
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">

                {{-- 1. Order Details Card --}}
                <div style="background: var(--bg-surface); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04); padding: 1.35rem 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1.2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: var(--accent-soft); color: var(--text-accent); font-size: 0.95rem;">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                        </span>
                        <h2 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: 'Outfit', sans-serif;">
                            Order Details
                        </h2>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        {{-- Supplier Selector --}}
                        <div>
                            <label for="supplier_id_select" style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem;">
                                Supplier <span style="color: var(--text-danger);">*</span>
                            </label>

                            <div style="position: relative;">
                                <select id="supplier_id_select" name="supplier_id" class="custom-combo" data-icon="fa-truck-field"
                                        x-model="supplierId"
                                        @change="changeSupplier()">
                                    <option value="" disabled :selected="!supplierId">— Select a supplier —</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">
                                            {{ $supplier->name }} ({{ $supplier->materials->count() }} catalog item{{ $supplier->materials->count() === 1 ? '' : 's' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if($suppliers->isEmpty())
                                <p style="font-size: 0.78rem; color: var(--text-warning); margin-top: 0.4rem;">
                                    <i class="fa-solid fa-circle-info"></i> No suppliers yet.
                                    <a href="{{ route('admin.suppliers.index') }}" style="color: var(--text-accent); font-weight: 700; text-decoration: underline;">Add a supplier first</a>.
                                </p>
                            @endif
                        </div>

                        {{-- Due Date & Reference No --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem;">
                                    Due Date
                                </label>
                                <div style="position: relative;">
                                    <input type="text" id="due_date_picker" name="due_date" value="{{ old('due_date') }}"
                                           placeholder="Select expected due date"
                                           style="width: 100%; padding: 0.65rem 2.4rem 0.65rem 0.95rem; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.9rem; outline: none; background: var(--bg-surface); color: var(--text-main); box-sizing: border-box; transition: all 0.2s; cursor: pointer;"
                                           onfocus="this.style.borderColor='#0099FF'; this.style.boxShadow='0 0 0 3px rgba(0, 153, 255, 0.15)';" 
                                           onblur="this.style.borderColor=''; this.style.boxShadow='none';">
                                    <span style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--text-accent); pointer-events: none; font-size: 0.95rem;">
                                        <i class="fa-regular fa-calendar-days"></i>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem;">
                                    Reference No.
                                </label>
                                <input type="text" value="Auto-generated on save" disabled
                                       style="width: 100%; padding: 0.65rem 0.95rem; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.9rem; background: var(--bg-page); color: var(--text-subtle); box-sizing: border-box; cursor: not-allowed;">
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem;">
                                Notes / Instructions
                            </label>
                            <textarea name="notes" rows="3" placeholder="Delivery instructions, payment terms, reference info..."
                                      style="width: 100%; padding: 0.65rem 0.95rem; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.9rem; outline: none; background: var(--bg-surface); color: var(--text-main); resize: vertical; box-sizing: border-box; transition: border-color 0.2s;"
                                      onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='';">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 2. Line Items Card --}}
                <div style="background: var(--bg-surface); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);">
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--border-color); background: var(--bg-subtle); border-radius: 16px 16px 0 0;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: var(--accent-soft); color: var(--text-accent); font-size: 0.95rem;">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </span>
                            <h2 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: 'Outfit', sans-serif;">
                                Order Items
                            </h2>
                        </div>
                        <button type="button" class="btn btn-primary po-add-item" @click="addRow()"
                                :disabled="!supplierId"
                                style="padding: 0.5rem 1rem; border-radius: 9px; font-size: 0.82rem; font-weight: 700;">
                            <i class="fa-solid fa-plus"></i> Add Item
                        </button>
                    </div>

                    {{-- Empty State: No Supplier selected --}}
                    <div x-show="!supplierId" style="padding: 3rem 1.5rem; text-align: center;">
                        <div style="width: 52px; height: 52px; border-radius: 50%; background: var(--accent-soft); color: var(--text-accent); display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 0.75rem;">
                            <i class="fa-solid fa-arrow-up"></i>
                        </div>
                        <p style="font-weight: 700; color: var(--text-muted); font-size: 0.92rem; margin: 0;">Please select a supplier first</p>
                        <p style="font-size: 0.8rem; color: var(--text-subtle); margin-top: 0.25rem;">Items will automatically load based on the supplier's material catalog.</p>
                    </div>

                    {{-- Empty State: Supplier has 0 catalog items --}}
                    <div x-show="supplierId && items.length === 0" x-cloak style="padding: 3rem 1.5rem; text-align: center;">
                        <div style="width: 52px; height: 52px; border-radius: 50%; background: var(--warning-soft); color: var(--text-warning); display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 0.75rem;">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <p style="font-weight: 700; color: var(--text-warning); font-size: 0.95rem; margin: 0;">This supplier has no catalog items yet</p>
                        <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.25rem;">Map the materials they supply with package sizes and prices before creating orders.</p>
                        <a :href="supplierId ? '{{ url('admin/suppliers') }}/' + supplierId : '#'" class="btn btn-outline" style="margin-top: 1rem; display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 9px; font-weight: 700;">
                            <i class="fa-solid fa-gear"></i> Configure Supplier Catalog
                        </a>
                    </div>

                    {{-- Dynamic Line Items List --}}
                    <div id="po-line-items" x-show="items.length > 0" x-cloak style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.85rem;">
                        <template x-for="(row, index) in rows" :key="row.key">
                            <div style="border: 1px solid var(--border-color); border-radius: 12px; padding: 1rem; background: var(--bg-page); transition: all 0.2s;"
                                 onmouseover="this.style.borderColor='var(--border-strong)'; this.style.background='var(--bg-elevated)';"
                                 onmouseout="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-page)';">

                                <div style="display: grid; grid-template-columns: 3fr 1.2fr 1.2fr 1fr 36px; gap: 0.75rem; align-items: center;">

                                    {{-- Catalog item selector --}}
                                    <div>
                                        <label style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); margin-bottom: 0.35rem;">
                                            Catalog Item
                                        </label>
                                        <select :name="'items[' + index + '][supplier_material_id]'"
                                                class="custom-combo"
                                                x-model="row.supplier_material_id"
                                                @change="pickItem(row)">
                                            <option value="">— Choose material item —</option>
                                            <template x-for="item in items" :key="item.id">
                                                <option :value="String(item.id)"
                                                        x-text="item.item_name + ' (' + item.package_label + ') · Stock: ' + formatQty(item.current_stock) + ' ' + item.base_unit"></option>
                                            </template>
                                        </select>
                                        <div style="display: flex; gap: 0.5rem; align-items: center; margin-top: 0.35rem; flex-wrap: wrap;">
                                            <span style="font-size: 0.75rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 0.35rem;" x-show="find(row)">
                                                <i class="fa-solid fa-tag" style="font-size: 0.68rem; color: var(--text-accent);"></i> Sells per <strong x-text="unitOf(row) || 'pkg'"></strong> at <strong>₱<span x-text="format(costOf(row))"></span></strong>
                                            </span>
                                            <span style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem;"
                                                  :style="isLowStock(row) ? 'color:var(--text-danger); font-weight:700;' : 'color:var(--text-muted);'"
                                                  x-show="find(row)">
                                                <i class="fa-solid fa-cubes-stacked" style="font-size: 0.68rem;"></i> Current: <span x-text="formatQty(stockOf(row)) + ' ' + baseUnitOf(row)"></span>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Quantity --}}
                                    <div>
                                        <label style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); margin-bottom: 0.35rem;">
                                            Qty (<span x-text="unitOf(row) || 'pkgs'"></span>)
                                        </label>
                                        <input type="number" min="1" step="1"
                                               :name="'items[' + index + '][quantity]'"
                                               x-model.number="row.quantity"
                                               style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid var(--border-color); border-radius: 9px; font-size: 0.9rem; text-align: right; outline: none; background: var(--bg-surface); color: var(--text-main); box-sizing: border-box;"
                                               onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='';">
                                        <div style="font-size: 0.73rem; color: var(--text-accent); font-weight: 700; text-align: right; margin-top: 0.35rem;" x-show="baseUnits(row) > 0">
                                            = <span x-text="formatQty(baseUnits(row)) + ' ' + baseUnitOf(row)"></span>
                                        </div>
                                    </div>

                                    {{-- Unit Cost --}}
                                    <div>
                                        <label style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); margin-bottom: 0.35rem;">
                                            Unit Price
                                        </label>
                                        <div style="padding: 0.55rem 0.75rem; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 9px; font-size: 0.88rem; text-align: right; color: var(--text-muted); font-weight: 600;">
                                            ₱<span x-text="format(costOf(row))"></span>
                                        </div>
                                    </div>

                                    {{-- Subtotal --}}
                                    <div>
                                        <label style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); margin-bottom: 0.35rem; text-align: right;">
                                            Subtotal
                                        </label>
                                        <div style="font-size: 0.95rem; font-weight: 800; color: var(--text-accent); text-align: right; padding: 0.55rem 0; font-family: 'Outfit', sans-serif;">
                                            ₱<span x-text="format(subtotal(row))"></span>
                                        </div>
                                    </div>

                                    {{-- Remove Button --}}
                                    <div style="display: flex; justify-content: flex-end; padding-top: 1rem;">
                                        <button type="button" @click="removeRow(index)"
                                                style="width: 32px; height: 32px; border-radius: 8px; border: none; background: var(--danger-soft); color: var(--text-danger); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s;"
                                                onmouseover="this.style.background='#FCA5A5'; this.style.color='#991B1B';"
                                                onmouseout="this.style.background='#FEE2E2'; this.style.color='#DC2626';"
                                                title="Remove this item">
                                            <i class="fa-solid fa-trash-can" style="font-size: 0.78rem;"></i>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            {{-- ══ Right Column: Sticky Summary & Action ══ --}}
            <div style="position: sticky; top: 75px; z-index: 30;">
                <div style="background: var(--bg-surface); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05); padding: 1.5rem; border-top: 3px solid var(--accent-color);">

                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.6rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 9px; background: var(--accent-soft); color: var(--text-accent); font-size: 1rem;">
                            <i class="fa-solid fa-receipt"></i>
                        </span>
                        <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: 'Outfit', sans-serif;">
                            Order Summary
                        </h2>
                    </div>

                    {{-- Metrics List --}}
                    <div style="display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.88rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted);">Total Line Items</span>
                            <span style="font-weight: 800; color: var(--text-main); background: var(--bg-subtle); padding: 0.15rem 0.6rem; border-radius: 9999px; font-size: 0.8rem;" x-text="validRows().length"></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted);">Packages Ordered</span>
                            <span style="font-weight: 800; color: var(--text-main);" x-text="totalQty()"></span>
                        </div>
                        <div style="border-top: 1px dashed var(--border-color); padding-top: 0.85rem; margin-top: 0.2rem; display: flex; justify-content: space-between; align-items: baseline;">
                            <span style="font-weight: 700; color: var(--text-muted);">Grand Total</span>
                            <div style="font-size: 1.4rem; font-weight: 900; color: var(--text-main); font-family: 'Outfit', sans-serif;">
                                ₱<span x-text="format(grandTotal())"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Live Restock Preview --}}
                    <div x-show="validRows().length > 0" x-cloak 
                         style="margin-top: 1.25rem; border-radius: 12px; border: 1px solid var(--info-border); background: var(--accent-soft); padding: 0.85rem 1rem;">
                        <p style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-accent); margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 0.35rem;">
                            <i class="fa-solid fa-boxes-packing"></i> Stock Addition On Receipt
                        </p>
                        <div style="display: flex; flex-direction: column; gap: 0.35rem; max-height: 180px; overflow-y: auto;">
                            <template x-for="row in validRows()" :key="row.key">
                                <div style="display: flex; justify-content: space-between; font-size: 0.78rem; line-height: 1.4;">
                                    <span style="color: var(--text-accent); font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 0.5rem;" x-text="nameOf(row)"></span>
                                    <span style="font-weight: 800; color: var(--text-accent); white-space: nowrap;"
                                          x-text="'+' + formatQty(baseUnits(row)) + ' ' + baseUnitOf(row)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <p style="font-size: 0.74rem; color: var(--text-subtle); margin-top: 1rem; line-height: 1.45;">
                        <i class="fa-solid fa-circle-info"></i> Inventory units will automatically be updated once this purchase order status changes to <strong>Received</strong>.
                    </p>

                    {{-- Action Buttons --}}
                    <div style="display: flex; flex-direction: column; gap: 0.6rem; margin-top: 1.25rem; padding-top: 1.1rem; border-top: 1px solid var(--border-color);">
                        <button type="submit" class="btn btn-primary po-submit"
                                :disabled="!supplierId || validRows().length === 0"
                                style="padding: 0.75rem 1.25rem; border-radius: 10px; font-weight: 800; font-size: 0.92rem;">
                            <i class="fa-solid fa-circle-check"></i> Create Purchase Order
                        </button>
                        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline" 
                           style="border-radius: 10px; padding: 0.65rem; font-weight: 700; font-size: 0.85rem; text-align: center; justify-content: center; color: var(--text-muted);">
                            Cancel
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </form>
</div>

<style>
@media (max-width: 1024px) {
    .po-layout-grid {
        grid-template-columns: 1fr !important;
    }
}
/* Add Item button — solid blue (from .btn-primary); dim only when no supplier is chosen */
.po-add-item:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    box-shadow: none;
}
.po-add-item:disabled:hover {
    background-color: var(--accent-color);
    border-color: var(--accent-color);
}
/* Create Purchase Order button — solid blue (from .btn-primary); dim only while incomplete */
.po-submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    box-shadow: none;
}
.po-submit:disabled:hover {
    background-color: var(--accent-color);
    border-color: var(--accent-color);
}
.po-submit:not(:disabled):hover {
    transform: translateY(-1px);
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    function poForm(initial) {
        return {
            supplierId: initial.supplierId || '',
            rows: initial.rows && initial.rows.length ? initial.rows : [],
            catalog: {},
            nextKey: 1000,

            init() {
                try {
                    this.catalog = JSON.parse(document.getElementById('poCatalog').textContent) || {};
                } catch (e) {
                    this.catalog = {};
                }

                if (!this.rows.length) {
                    this.rows = [{ key: this.nextKey++, supplier_material_id: '', quantity: 1 }];
                }
            },

            get items() {
                return this.supplierId ? (this.catalog[this.supplierId] || []) : [];
            },

            changeSupplier() {
                this.rows = [{ key: this.nextKey++, supplier_material_id: '', quantity: 1 }];
            },

            addRow() {
                if (!this.supplierId) return;
                this.rows.push({ key: this.nextKey++, supplier_material_id: '', quantity: 1 });
            },

            removeRow(index) {
                this.rows.splice(index, 1);
                if (!this.rows.length) {
                    this.changeSupplier();
                }
            },

            find(row) {
                return this.items.find(i => String(i.id) === String(row.supplier_material_id));
            },

            pickItem(row) {
                const item = this.find(row);
                if (item && (!row.quantity || row.quantity < 1)) {
                    row.quantity = 1;
                }
            },

            nameOf(row) {
                const item = this.find(row);
                return item ? item.item_name : '';
            },

            unitOf(row) {
                const item = this.find(row);
                return item ? (item.supplier_unit || '') : '';
            },

            baseUnitOf(row) {
                const item = this.find(row);
                return item ? (item.base_unit || 'units') : 'units';
            },

            costOf(row) {
                const item = this.find(row);
                return item ? parseFloat(item.unit_cost || 0) : 0;
            },

            subtotal(row) {
                const qty = parseInt(row.quantity, 10);
                if (!qty || qty < 1) return 0;
                return Math.round(this.costOf(row) * qty * 100) / 100;
            },

            baseUnits(row) {
                const item = this.find(row);
                const qty = parseInt(row.quantity, 10);
                if (!item || !qty || qty < 1) return 0;
                return Math.round(qty * parseFloat(item.conversion_qty || 0) * 1000) / 1000;
            },

            stockOf(row) {
                const item = this.find(row);
                return item ? parseFloat(item.current_stock || 0) : 0;
            },

            isLowStock(row) {
                const item = this.find(row);
                return item ? item.is_low_stock === true : false;
            },

            grandTotal() {
                return Math.round(this.rows.reduce((sum, r) => sum + this.subtotal(r), 0) * 100) / 100;
            },

            validRows() {
                return this.rows.filter(r => r.supplier_material_id && parseInt(r.quantity, 10) > 0);
            },

            totalQty() {
                return this.validRows().reduce((sum, r) => sum + parseInt(r.quantity, 10), 0);
            },

            format(value) {
                return (Math.round((parseFloat(value) || 0) * 100) / 100)
                    .toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            formatQty(value) {
                const n = parseFloat(value) || 0;
                return n.toLocaleString('en-PH', { maximumFractionDigits: 3 });
            },
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#due_date_picker', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'M j, Y',
                altInputClass: 'dd-date-alt',
                minDate: 'today',
                animate: true,
                disableMobile: true
            });
        }
    });
</script>
@endpush

{{-- ─── CUSTOM COMBOBOX FOR SUPPLIER SELECT (syncs Alpine x-model + changeSupplier) ─── --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const registry = [];

    function enhanceCombo(select) {
        if (select.dataset.comboDone) return;
        select.dataset.comboDone = '1';

        const outer = select.parentElement;
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.width = '100%';
        outer.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        select.style.display = 'none';

        const iconClass = select.dataset.icon || '';
        const accent = select.dataset.accent || '#0099FF';

        const btn = document.createElement('button');
        btn.type = 'button';
        const label = document.createElement('span');
        label.style.cssText = 'flex:1; text-align:left; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;';
        const caret = document.createElement('i');
        caret.className = 'fa-solid fa-chevron-down';
        caret.style.cssText = 'font-size:0.72rem; color:#94A3B8; transition:transform 0.15s;';
        if (iconClass) {
            const lead = document.createElement('i');
            lead.className = 'fa-solid ' + iconClass;
            lead.style.cssText = 'font-size:0.8rem; color:#94A3B8; flex-shrink:0;';
            btn.appendChild(lead);
        }
        btn.appendChild(label); btn.appendChild(caret);
        wrapper.appendChild(btn);

        const panel = document.createElement('div');
        panel.className = 'combo-panel';
        panel.style.cssText = 'display:none; position:absolute; top:calc(100% + 4px); left:0; right:0; background:var(--bg-elevated); border:1px solid var(--border-color); border-radius:10px; box-shadow:0 8px 20px rgba(15,23,42,0.12); max-height:220px; overflow-y:auto; z-index:60; padding:0.25rem;';
        wrapper.appendChild(panel);

        function styleBtn(isOpen) {
            const hasVal = select.value !== '';
            btn.style.cssText = 'width:100%; display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.65rem 0.875rem; border:1.5px solid ' + (isOpen ? accent : 'var(--border-color)') + '; border-radius:10px; font-size:0.875rem; color:' + (hasVal ? 'var(--text-main)' : 'var(--text-muted)') + '; background:' + (isOpen ? 'var(--bg-surface)' : 'var(--bg-subtle)') + '; outline:none; cursor:pointer; box-sizing:border-box; transition:border-color 0.2s, background 0.2s;';
        }
        function render() {
            const o = select.options[select.selectedIndex];
            label.textContent = o ? o.textContent.trim() : '';
            styleBtn(false);
            const cur = select.value;
            panel.innerHTML = '';
            Array.from(select.options).forEach(function (opt) {
                if (opt.value === '') return;
                const item = document.createElement('div');
                const selected = opt.value === cur;
                item.textContent = opt.textContent.trim();
                item.style.cssText = 'display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.5rem 0.65rem; border-radius:7px; font-size:0.84rem; cursor:pointer; color:' + (selected ? 'var(--accent-contrast)' : 'var(--text-main)') + '; background:' + (selected ? accent : 'var(--bg-elevated)') + ';';
                if (!selected) {
                    item.addEventListener('mouseover', function () { item.style.background = 'var(--accent-soft)'; });
                    item.addEventListener('mouseout', function () { item.style.background = 'var(--bg-elevated)'; });
                } else {
                    const chk = document.createElement('i');
                    chk.className = 'fa-solid fa-check';
                    chk.style.cssText = 'font-size:0.72rem; color:var(--accent-contrast);';
                    item.appendChild(chk);
                }
                item.addEventListener('click', function (e) {
                    e.stopPropagation();
                    select.value = opt.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                    render();
                    close();
                });
                panel.appendChild(item);
            });
        }
        function open() { render(); panel.style.display = 'block'; caret.style.transform = 'rotate(180deg)'; styleBtn(true); }
        function close() { panel.style.display = 'none'; caret.style.transform = 'rotate(0deg)'; styleBtn(false); }
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (panel.style.display === 'block') { close(); } else { open(); }
        });
        render();
        select.__comboRender = render;
        registry.push(select);
    }

    document.querySelectorAll('select.custom-combo').forEach(enhanceCombo);

    // Enhance comboboxes in dynamically-added line-item rows (Alpine x-for)
    const lineItems = document.getElementById('po-line-items');
    if (lineItems) {
        new MutationObserver(function () {
            lineItems.querySelectorAll('select.custom-combo').forEach(enhanceCombo);
        }).observe(lineItems, { childList: true, subtree: true });
    }

    // Re-render once Alpine has applied the initial x-model value (old()/prefill)
    requestAnimationFrame(function () {
        registry.forEach(function (s) { if (s.__comboRender) s.__comboRender(); });
    });

    document.addEventListener('click', function (e) {
        registry.forEach(function (sel) {
            const wrap = sel.parentElement;
            const pnl = wrap && wrap.querySelector('.combo-panel');
            if (pnl && pnl.style.display === 'block' && !wrap.contains(e.target)) {
                pnl.style.display = 'none';
                const c = wrap.querySelector('.fa-chevron-down');
                if (c) c.style.transform = 'rotate(0deg)';
            }
        });
    });
});
</script>
@endpush
