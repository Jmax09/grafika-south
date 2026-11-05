@extends('layouts.admin')

@section('title', $supplier->name . ' · Supplier Ledger | Grafika South')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Payment Date — Flatpickr altInput styling (matches the blue Record Payment modal) */
    .sp-date-field {
        width: 100% !important;
        padding: 0.65rem 0.75rem 0.65rem 2.2rem !important;
        border: 1.5px solid var(--border-color) !important;
        border-radius: 10px !important;
        font-size: 0.875rem !important;
        font-family: inherit !important;
        outline: none !important;
        background: var(--bg-page) !important;
        color: var(--text-main) !important;
        box-sizing: border-box !important;
        cursor: pointer !important;
        transition: border-color 0.2s, background 0.2s !important;
    }
    .sp-date-field:focus {
        border-color: var(--accent-color) !important;
        background: var(--bg-surface) !important;
    }

    /* Amount Paid — hide the native number spinner for a clean look */
    input[name="amount_paid"]::-webkit-outer-spin-button,
    input[name="amount_paid"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[name="amount_paid"] { -moz-appearance: textfield; appearance: textfield; }

    /* Custom Flatpickr theme — blue accent to match the Record Payment modal */
    .flatpickr-calendar {
        background: var(--bg-surface) !important;
        border-radius: 16px !important;
        border: 1px solid var(--border-color) !important;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08) !important;
        font-family: 'Inter', sans-serif !important;
        padding: 0.75rem !important;
        width: 320px !important;
    }
    .flatpickr-innerContainer, .flatpickr-rContainer, .flatpickr-days { width: 100% !important; }
    .dayContainer { width: 100% !important; min-width: 100% !important; max-width: 100% !important; justify-content: space-between !important; }
    .flatpickr-calendar:before, .flatpickr-calendar:after { border-bottom-color: var(--border-color) !important; }
    .flatpickr-months {
        position: relative !important; background: var(--bg-page) !important; border-radius: 12px !important;
        padding: 0.6rem 0.5rem !important; display: flex !important; align-items: center !important;
        justify-content: center !important; min-height: 44px !important;
    }
    .flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month {
        position: absolute !important; top: 50% !important; transform: translateY(-50%) !important;
        width: 30px !important; height: 30px !important; display: flex !important; align-items: center !important;
        justify-content: center !important; border-radius: 8px !important; padding: 0 !important;
        cursor: pointer !important; transition: all 0.15s ease !important;
    }
    .flatpickr-months .flatpickr-prev-month { left: 8px !important; }
    .flatpickr-months .flatpickr-next-month { right: 8px !important; }
    .flatpickr-months .flatpickr-prev-month:hover, .flatpickr-months .flatpickr-next-month:hover { background: var(--accent-soft) !important; }
    .flatpickr-months .flatpickr-prev-month svg, .flatpickr-months .flatpickr-next-month svg { width: 14px !important; height: 14px !important; vertical-align: middle !important; }
    .flatpickr-months .flatpickr-prev-month:hover svg, .flatpickr-months .flatpickr-next-month:hover svg { fill: var(--accent-color) !important; }
    .flatpickr-current-month {
        position: static !important; width: auto !important; height: auto !important; display: flex !important;
        align-items: center !important; justify-content: center !important; font-size: 0.95rem !important;
        font-weight: 800 !important; color: var(--text-main) !important; padding: 0 !important; left: auto !important; transform: none !important;
    }
    .flatpickr-current-month .cur-month, .flatpickr-monthDropdown-months { font-weight: 800 !important; color: var(--text-main) !important; }
    .flatpickr-weekdays { background: transparent !important; margin-top: 0.5rem !important; width: 100% !important; }
    .flatpickr-weekdaycontainer { display: flex !important; width: 100% !important; justify-content: space-between !important; }
    span.flatpickr-weekday { color: var(--text-muted) !important; font-size: 0.72rem !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 0.04em !important; flex: 1 !important; }
    .flatpickr-day {
        border-radius: 8px !important; font-weight: 600 !important; color: var(--text-main) !important;
        height: 38px !important; line-height: 38px !important; max-width: 38px !important;
        flex-basis: 14.28% !important; transition: all 0.15s ease !important; margin: 1px 0 !important;
    }
    .flatpickr-day:hover { background: var(--accent-soft) !important; color: var(--text-accent) !important; border-color: transparent !important; }
    .flatpickr-day.today { border-color: var(--accent-color) !important; color: var(--text-accent) !important; background: var(--accent-soft) !important; font-weight: 800 !important; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover {
        background: var(--accent-color) !important; color: var(--text-inverse) !important; border-color: var(--accent-color) !important;
        font-weight: 800 !important; box-shadow: 0 4px 10px rgba(0, 153, 255, 0.3) !important;
    }
    .flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay { color: var(--text-disabled) !important; }
</style>
@endpush

@section('content')

<style>
    /* Clickable catalog rows — same hover style as the Orders table (.order-row); click opens the Edit Catalog Item modal */
    tr.row-clickable { cursor: pointer; transition: all 0.2s ease !important; }
    tr.row-clickable:hover {
        background-color: var(--accent-soft) !important;
        box-shadow: inset 0 0 0 2px var(--accent-color) !important;
        position: relative;
        z-index: 5;
    }
    /* Timeline events — no list numbers, hover highlight like the Orders table */
    .timeline-list, .timeline-list li { list-style: none; }
    li.timeline-clickable { border-radius: 10px; cursor: pointer; transition: background 0.15s ease; padding: 0.35rem 0.5rem; }
    li.timeline-clickable:hover { background: var(--accent-soft); }
</style>

@push('scripts')
<script>
    function openRowLink(event, url) {
        // Ignore clicks on buttons, forms, links, and their children inside the row
        if (event.target.closest('button, form, a, input, label, select, textarea')) return;
        window.location = url;
    }
</script>
@endpush
@php
    // Catalog rows are inventory packages: name + base unit come from the material.
    $catalogForEdit = $catalogItems->map(fn ($i) => [
        'id' => $i->id,
        'inventory_material_id' => $i->inventory_material_id,
        'item_name' => $i->item_name,
        'supplier_unit_name' => $i->supplier_unit_name,
        'conversion_qty' => (float) $i->conversion_qty,
        'unit_cost' => (float) $i->unit_cost,
        'base_unit' => $i->base_unit,
        'package_label' => $i->package_label,
    ])->values();

    // Packages already mapped, per inventory material, so the form can point them out.
    $packagesByMaterial = $catalogItems->groupBy('inventory_material_id')
        ->map(fn ($group) => $group->map(fn ($row) => ['id' => $row->id, 'label' => $row->package_label])->values()->all());
    $cataloguedMaterialIds = $packagesByMaterial->keys()->all();

    // The full inventory list is always offered, with each item's live stock.
    $materialOptions = $materials->map(fn ($m) => [
        'id' => $m->id,
        'name' => $m->name,
        'base_unit' => $m->unit_label,
        'stock' => (float) $m->current_stock,
        'low_stock_threshold' => (float) $m->low_stock_threshold,
        'is_low' => $m->isLowStock(),
        'catalogued_as' => $packagesByMaterial[$m->id] ?? [],
        'inventory_url' => route('admin.materials.index', ['search' => $m->name]),
    ])->values();

    $openPurchases = $purchases->where('status', '!=', 'cancelled');
@endphp

<script type="application/json" id="catalogData">@json($catalogForEdit)</script>
<script type="application/json" id="materialData">@json($materialOptions)</script>

<div x-data="ledgerState()">

    {{-- ── Header ── --}}
    <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
        <div>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-primary" style="display: inline-flex; margin-bottom: 0.6rem;">
                <i class="fa-solid fa-arrow-left"></i> Back to Suppliers
            </a>
            <h1 class="text-2xl font-extrabold mt-1" style="color: var(--text-main);">{{ $supplier->name }}</h1>
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm mt-1" style="color: var(--text-muted);">
                @if($supplier->contact_person)<span><i class="fa-solid fa-user text-slate-400"></i> {{ $supplier->contact_person }}</span>@endif
                @if($supplier->phone)<span><i class="fa-solid fa-phone text-slate-400"></i> {{ $supplier->phone }}</span>@endif
                @if($supplier->email)<span><i class="fa-solid fa-envelope text-slate-400"></i> {{ $supplier->email }}</span>@endif
            </div>
            @if($supplier->address)
                <p class="text-sm mt-1" style="color: var(--text-muted);"><i class="fa-solid fa-location-dot text-slate-400"></i> {{ $supplier->address }}</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline" @click="showSupplierEdit = true">
                <i class="fa-solid fa-pen"></i> Edit Profile
            </button>
            <a href="{{ route('admin.purchases.create', ['supplier' => $supplier->id]) }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> New Purchase Order
            </a>
        </div>
    </div>

    {{-- ── Validation feedback ── --}}
    @if($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-bold mb-1"><i class="fa-solid fa-circle-exclamation"></i> Could not save — please check:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Ledger metrics ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Purchases</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-2">₱{{ number_format($totalPurchases, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Paid</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-2">₱{{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="rounded-xl border p-5 shadow-sm {{ $balance > 0 ? 'border-rose-200 bg-rose-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-bold uppercase tracking-wider {{ $balance > 0 ? 'text-rose-500' : 'text-slate-400' }}">Remaining Balance</p>
            <p class="text-2xl font-extrabold mt-2 {{ $balance > 0 ? 'text-rose-600' : 'text-slate-900' }}">₱{{ number_format($balance, 2) }}</p>
            <p class="text-xs mt-1 text-slate-500">{{ $balance > 0 ? 'Accounts payable outstanding' : 'Fully settled' }}</p>
        </div>
    </div>

    {{-- ── Tabs ── --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex flex-wrap gap-1 px-3 pt-3 border-b border-slate-100">
            <template x-for="t in tabs" :key="t.key">
                <button type="button"
                        class="px-4 py-2.5 text-sm font-bold rounded-t-lg border-b-2 transition"
                        :class="tab === t.key ? 'border-sky-500 text-sky-600 bg-sky-50' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        @click="tab = t.key"
                        x-text="t.label"></button>
            </template>
        </div>

        {{-- CATALOG --}}
        <div x-show="tab === 'catalog'" class="p-4">
            <div class="flex items-center justify-between gap-3 mb-3">
                <p class="text-sm" style="color: var(--text-muted);">
                    Each entry is a supplier package of an existing inventory item, priced per package.
                </p>
                <button type="button" class="btn btn-primary" @click="showItem = true">
                    <i class="fa-solid fa-plus"></i> Add Catalog Item
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-left">
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Inventory Item</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Supplier Package</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Conversion</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-right">Cost / Package</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-right">Cost / Base Unit</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($catalogItems as $item)
                            @php $linkedMaterial = $item->inventoryMaterial; @endphp
                            <tr class="hover:bg-slate-50 row-clickable" @click="if (!$event.target.closest('button, a, input, label, select, textarea')) openEdit({{ $item->id }})">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $item->item_name }}</p>
                                    @if($linkedMaterial)
                                        <p class="text-xs {{ $linkedMaterial->isLowStock() ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                                            In stock: {{ (float) $linkedMaterial->current_stock }} {{ $item->base_unit }}
                                            @if($linkedMaterial->isLowStock()) · Low stock @endif
                                        </p>
                                        <a href="{{ route('admin.materials.index', ['search' => $linkedMaterial->name]) }}"
                                           class="text-[11px] text-sky-600 font-semibold no-underline hover:text-sky-700">View in inventory</a>
                                    @else
                                        <p class="text-xs text-rose-600 font-bold">Removed from inventory</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-sky-50 text-sky-600 text-xs font-bold">
                                        <i class="fa-solid fa-cube"></i> {{ $item->supplier_unit_name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 font-semibold">{{ $item->package_label }}</td>
                                <td class="px-4 py-3 text-right font-bold text-slate-800">
                                    ₱{{ number_format($item->unit_cost, 2) }}
                                    <span class="block text-[11px] font-normal text-slate-400">per {{ $item->supplier_unit_name }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-600">
                                    ₱{{ number_format($item->base_unit_cost, 2) }}
                                    <span class="block text-[11px] text-slate-400">per {{ $item->base_unit }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-slate-700 hover:text-white transition-all border-0 outline-none cursor-pointer"
                                                title="Edit package" @click="openEdit({{ $item->id }})">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </button>
                                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all border-0 outline-none cursor-pointer"
                                                title="Remove entry"
                                                @click="confirmDelete('{{ route('admin.suppliers.catalog.destroy', $item) }}', '{{ addslashes($item->item_name) }}', 'Existing PO history will be preserved.')">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <i class="fa-solid fa-box-open text-3xl text-slate-300"></i>
                                    <p class="mt-2 font-semibold text-slate-600">No catalog entries yet</p>
                                    <p class="text-sm text-slate-400">Map the inventory items this supplier sells, with their package size and price.</p>
                                    <button type="button" class="btn btn-primary mt-4" @click="showItem = true">
                                        <i class="fa-solid fa-plus"></i> Add Catalog Item
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PURCHASES --}}
        <div x-show="tab === 'purchases'" x-cloak class="p-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-left">
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Reference</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Date</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-center">Items</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-right">Total</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-right">Paid</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-center">View</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($purchases as $po)
                            <tr class="hover:bg-slate-50 row-clickable" onclick="openRowLink(event, '{{ route('admin.purchases.show', $po) }}')">
                                <td class="px-4 py-3 font-bold text-slate-800">{{ $po->reference_no }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $po->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-center text-slate-600">{{ $po->items_count }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-800">₱{{ number_format($po->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right text-emerald-600">₱{{ number_format($po->paid_amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold capitalize
                                        {{ $po->status === 'received' ? 'bg-emerald-50 text-emerald-600' : ($po->status === 'cancelled' ? 'bg-slate-100 text-slate-500' : 'bg-amber-50 text-amber-600') }}">
                                        {{ $po->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('admin.purchases.show', $po) }}" class="text-sky-600 hover:text-sky-700">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No purchase orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAYMENTS --}}
        <div x-show="tab === 'payments'" x-cloak class="p-4">
            <div class="flex justify-end mb-3">
                <button type="button" class="btn btn-primary" @click="showPayment = true">
                    <i class="fa-solid fa-money-check-dollar"></i> Add Payment
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-left">
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Date</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Method</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Reference</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Purchase Order</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-right">Amount</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-600">{{ ($payment->paid_at ?? $payment->created_at)->format('M d, Y') }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $payment->payment_method }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $payment->reference_no ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($payment->purchase)
                                        <a href="{{ route('admin.purchases.show', $payment->purchase) }}" class="text-sky-600 hover:text-sky-700 font-semibold">
                                            {{ $payment->purchase->reference_no }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">General</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-600">₱{{ number_format($payment->amount_paid, 2) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center bg-rose-50 text-rose-600 hover:bg-rose-100 border-0 outline-none cursor-pointer"
                                            title="Delete payment"
                                            @click="confirmDelete('{{ route('admin.suppliers.payments.destroy', $payment) }}', '{{ $payment->reference_no ?: 'this payment' }}', 'This payment record will be permanently deleted.')">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TIMELINE --}}
        <div x-show="tab === 'timeline'" x-cloak class="p-5">
            @if($timeline->isEmpty())
                <p class="text-center text-slate-400 py-8">No transactions yet.</p>
            @else
                <ol class="timeline-list relative border-l-2 border-slate-100 ml-3 space-y-5">
                    @foreach($timeline as $event)
                        <li class="ml-6 flex items-start gap-3{{ $event['url'] ? ' timeline-clickable' : '' }}"@if($event['url']) onclick="openRowLink(event, '{{ $event['url'] }}')"@endif>
                            <span class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-[10px] text-white -ml-[2.125rem] translate-y-0.5
                                {{ $event['type'] === 'payment' ? 'bg-emerald-500' : ($event['status'] === 'cancelled' ? 'bg-slate-400' : 'bg-sky-500') }}">
                                <i class="fa-solid {{ $event['type'] === 'payment' ? 'fa-money-check-dollar' : 'fa-file-invoice' }}"></i>
                            </span>
                            <div class="flex-1 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    @if($event['url'])
                                        <a href="{{ $event['url'] }}" class="font-bold text-slate-800 hover:text-sky-600">{{ $event['title'] }}</a>
                                    @else
                                        <span class="font-bold text-slate-800">{{ $event['title'] }}</span>
                                    @endif
                                    <p class="text-xs text-slate-400">
                                        {{ \Illuminate\Support\Carbon::parse($event['date'])->format('M d, Y') }}
                                        @if($event['meta']) · {{ $event['meta'] }} @endif
                                        @if($event['status'] && $event['type'] === 'purchase') · <span class="capitalize">{{ $event['status'] }}</span> @endif
                                    </p>
                                </div>
                                <span class="font-extrabold {{ $event['type'] === 'payment' ? 'text-emerald-600' : 'text-slate-800' }}">
                                    {{ $event['type'] === 'payment' ? '−' : '' }}₱{{ number_format($event['amount'], 2) }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>

    {{-- ══ Add Catalog Item modal ══ --}}
    <div x-show="showItem" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="background: rgba(15,23,42,.65); backdrop-filter: blur(6px);" @keydown.escape.window="showItem = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" @click.outside="showItem = false"
             style="max-height: 90vh; display: flex; flex-direction: column;">

            {{-- Modal Header --}}
            <div style="background: var(--bg-surface); padding: 1.25rem 1.5rem; flex-shrink: 0; border-bottom: 1px solid var(--border-color);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--accent-soft);">
                            <i class="fa-solid fa-box-open text-sm" style="color: var(--text-accent);"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-extrabold leading-tight" style="color: var(--text-main);">Add Catalog Item</h3>
                            <p class="text-xs" style="color: var(--text-muted);">Map an inventory material to a supplier package</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="showItem = false"
                            class="w-8 h-8 rounded-lg flex items-center justify-center border-0 cursor-pointer transition-all"
                            style="background: var(--bg-subtle); color: var(--text-muted);"
                            onmouseover="this.style.background='#E2E8F0'; this.style.color='#0F172A'" onmouseout="this.style.background='#F1F5F9'; this.style.color='#64748B'">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            @if($materials->isEmpty())
                <div style="padding: 2rem 1.5rem; text-align: center;">
                    <span class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 inline-flex items-center justify-center text-xl mb-3">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </span>
                    <h4 class="font-extrabold text-slate-800 text-base mb-1">Inventory is empty</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                        Catalog items are packages of inventory stock. Add the material under
                        <span class="font-semibold text-slate-700">Raw Materials &amp; Inventory</span> first, then map the package this supplier sells it in.
                    </p>
                    <div class="flex justify-center gap-2 pt-5">
                        <button type="button" class="btn btn-outline" @click="showItem = false">
                            <i class="fa-solid fa-xmark"></i> Close
                        </button>
                    </div>
                </div>
            @else
                <form action="{{ route('admin.suppliers.catalog.store', $supplier) }}" method="POST"
                      style="overflow-y: auto; flex: 1; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                    @csrf

                    {{-- Inventory Material --}}
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                            Select Inventory Material <span style="color: var(--text-danger);">*</span>
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem; pointer-events: none;">
                                <i class="fa-solid fa-warehouse"></i>
                            </span>
                            <select name="inventory_material_id" class="custom-combo" data-icon="fa-warehouse" x-model="create.inventory_material_id"
                                    style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box; cursor: pointer;"
                                    onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                    onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                                <option value="">— Choose an inventory item —</option>
                                @foreach($materials as $material)
                                    <option value="{{ $material->id }}">
                                        {{ $material->name }} · stock {{ (float) $material->current_stock }} {{ $material->unit_label }}{{ $material->isLowStock() ? ' (low)' : '' }}{{ in_array($material->id, $cataloguedMaterialIds) ? ' · in catalog' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p style="font-size: 0.7rem; color: var(--text-subtle); margin-top: 0.3rem;">
                            <i class="fa-solid fa-circle-info"></i> Item names come from inventory; they cannot be typed here.
                        </p>

                        {{-- Stock Info Preview Card --}}
                        <div class="mt-2.5 rounded-xl border border-slate-200 bg-slate-50 p-3"
                             x-show="createStock().show" x-cloak>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                    <i class="fa-solid fa-boxes-stacked text-sky-500"></i> Linked inventory stock
                                </span>
                                <span class="text-xs font-extrabold px-2 py-0.5 rounded-full"
                                      :class="createStock().low ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-700'"
                                      x-text="createStock().label"></span>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-600" x-text="createStock().note"></p>
                            <p class="mt-1 text-xs text-amber-700 font-medium" x-show="createStock().packages.length > 0">
                                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Already in this catalog:
                                <span class="font-bold text-slate-800" x-text="createStock().packages.join(', ')"></span>
                            </p>
                            <div class="mt-2 pt-2 border-t border-slate-200/60 text-[11px] text-slate-500">
                                <span>Low-stock alert at: <strong class="text-slate-700" x-text="createStock().threshold"></strong></span>
                            </div>
                        </div>
                    </div>

                    {{-- Supplier Unit Name + Conversion Value --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.875rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                                Supplier Unit Name <span style="color: var(--text-danger);">*</span>
                            </label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem;">
                                    <i class="fa-solid fa-cube"></i>
                                </span>
                                <input type="text" name="supplier_unit_name" x-model="create.supplier_unit_name" required
                                       maxlength="50" placeholder="e.g. Box, Gallon, Bundle"
                                       style="width: 100%; padding: 0.65rem 0.75rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                                       onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                       onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                                Conversion Value <span style="color: var(--text-danger);">*</span>
                            </label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem;">
                                    <i class="fa-solid fa-calculator"></i>
                                </span>
                                <input type="number" name="conversion_qty" x-model="create.conversion_qty" required
                                       step="0.001" min="0.001" placeholder="e.g. 5"
                                       style="width: 100%; padding: 0.65rem 0.75rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                                       onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                       onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                            </div>
                        </div>
                    </div>

                    {{-- Conversion Hint Banner --}}
                    <div style="background: var(--accent-soft); border: 1px solid var(--info-border); border-radius: 10px; padding: 0.7rem 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-arrow-right-arrow-left" style="color: var(--text-accent); font-size: 0.85rem; flex-shrink: 0;"></i>
                        <span style="font-size: 0.825rem; font-weight: 700; color: var(--text-accent);" x-text="createHint()"></span>
                    </div>

                    {{-- Unit Cost --}}
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                            Unit Cost per Supplier Package (₱) <span style="color: var(--text-danger);">*</span>
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-accent); font-size: 0.9rem; font-weight: 800;">
                                ₱
                            </span>
                            <input type="number" step="0.01" min="0" name="unit_cost" required placeholder="0.00"
                                   style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                                   onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                   onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                        </div>
                        <p style="font-size: 0.7rem; color: var(--text-subtle); margin-top: 0.3rem;">
                            <i class="fa-solid fa-tag"></i> Price the supplier charges for one <span class="font-bold text-slate-600" x-text="create.supplier_unit_name || 'package'"></span>.
                        </p>
                    </div>

                    {{-- Footer Actions --}}
                    <div style="display: flex; justify-content: flex-end; gap: 0.625rem; padding-top: 0.5rem; border-top: 1px solid var(--border-color); margin-top: 0.25rem;">
                        <button type="button" class="btn btn-outline" @click="showItem = false">
                            <i class="fa-solid fa-xmark"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Add Item
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    {{-- ══ Edit Catalog Item modal ══ --}}
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="background: rgba(15,23,42,.65); backdrop-filter: blur(6px);" @keydown.escape.window="showEdit = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" @click.outside="showEdit = false"
             style="max-height: 90vh; display: flex; flex-direction: column;">

            {{-- Modal Header --}}
            <div style="background: var(--bg-surface); padding: 1.25rem 1.5rem; flex-shrink: 0; border-bottom: 1px solid var(--border-color);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--accent-soft);">
                            <i class="fa-solid fa-pen-to-square text-sm" style="color: var(--text-accent);"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-extrabold leading-tight" style="color: var(--text-main);">Edit Catalog Item</h3>
                            <p class="text-xs" style="color: var(--text-muted);">Update package and pricing details</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="showEdit = false"
                            class="w-8 h-8 rounded-lg flex items-center justify-center border-0 cursor-pointer transition-all"
                            style="background: var(--bg-subtle); color: var(--text-muted);"
                            onmouseover="this.style.background='#E2E8F0'; this.style.color='#0F172A'" onmouseout="this.style.background='#F1F5F9'; this.style.color='#64748B'">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            <form :action="'{{ url('/admin/suppliers/catalog') }}/' + edit.id" method="POST"
                  style="overflow-y: auto; flex: 1; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                @csrf
                @method('PUT')

                {{-- Inventory Material --}}
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                        Select Inventory Material <span style="color: var(--text-danger);">*</span>
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem; pointer-events: none;">
                            <i class="fa-solid fa-warehouse"></i>
                        </span>
                        <select name="inventory_material_id" class="custom-combo" data-icon="fa-warehouse" x-model="edit.inventory_material_id"
                                style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box; cursor: pointer;"
                                onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                            <option value="">— Choose an inventory item —</option>
                            @foreach($materials as $material)
                                <option value="{{ $material->id }}">
                                    {{ $material->name }} · stock {{ (float) $material->current_stock }} {{ $material->unit_label }}{{ $material->isLowStock() ? ' (low)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Stock Info Preview Card --}}
                    <div class="mt-2.5 rounded-xl border border-slate-200 bg-slate-50 p-3"
                         x-show="editStock().show" x-cloak>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-boxes-stacked text-sky-500"></i> Linked inventory stock
                            </span>
                            <span class="text-xs font-extrabold px-2 py-0.5 rounded-full"
                                  :class="editStock().low ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-700'"
                                  x-text="editStock().label"></span>
                        </div>
                        <p class="mt-1.5 text-xs text-slate-600" x-text="editStock().note"></p>
                        <p class="mt-1 text-xs text-slate-500" x-show="editStock().packages.length > 0">
                            Other packages of this item: <strong class="text-slate-800" x-text="editStock().packages.join(', ')"></strong>
                        </p>
                        <div class="mt-2 pt-2 border-t border-slate-200/60 text-[11px] text-slate-500">
                            <span>Low-stock alert at: <strong class="text-slate-700" x-text="editStock().threshold"></strong></span>
                        </div>
                    </div>
                </div>

                {{-- Supplier Unit Name + Conversion Value --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.875rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                            Supplier Unit Name <span style="color: var(--text-danger);">*</span>
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem;">
                                <i class="fa-solid fa-cube"></i>
                            </span>
                            <input type="text" name="supplier_unit_name" x-model="edit.supplier_unit_name" required maxlength="50"
                                   style="width: 100%; padding: 0.65rem 0.75rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                                   onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                   onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                            Conversion Value <span style="color: var(--text-danger);">*</span>
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem;">
                                <i class="fa-solid fa-calculator"></i>
                            </span>
                            <input type="number" name="conversion_qty" x-model="edit.conversion_qty" required step="0.001" min="0.001"
                                   style="width: 100%; padding: 0.65rem 0.75rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                                   onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                   onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                        </div>
                    </div>
                </div>

                {{-- Conversion Hint Banner --}}
                <div style="background: var(--accent-soft); border: 1px solid var(--info-border); border-radius: 10px; padding: 0.7rem 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-arrow-right-arrow-left" style="color: var(--text-accent); font-size: 0.85rem; flex-shrink: 0;"></i>
                    <span style="font-size: 0.825rem; font-weight: 700; color: var(--text-accent);" x-text="editHint()"></span>
                </div>

                {{-- Unit Cost --}}
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                        Unit Cost per Supplier Package (₱) <span style="color: var(--text-danger);">*</span>
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-accent); font-size: 0.9rem; font-weight: 800;">
                            ₱
                        </span>
                        <input type="number" step="0.01" min="0" name="unit_cost" x-model="edit.unit_cost" required
                               style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                               onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                               onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div style="display: flex; justify-content: flex-end; gap: 0.625rem; padding-top: 0.5rem; border-top: 1px solid var(--border-color); margin-top: 0.25rem;">
                    <button type="button" class="btn btn-outline" @click="showEdit = false">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ Add Payment modal ══ --}}
    <div x-show="showPayment" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="background: rgba(15,23,42,.65); backdrop-filter: blur(6px);" @keydown.escape.window="showPayment = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" @click.outside="showPayment = false"
             style="max-height: 90vh; display: flex; flex-direction: column;">

            {{-- Modal Header --}}
            <div style="background: var(--bg-surface); padding: 1.25rem 1.5rem; flex-shrink: 0; border-bottom: 1px solid var(--border-color);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--accent-soft);">
                            <i class="fa-solid fa-money-check-dollar text-sm" style="color: var(--text-accent);"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-extrabold leading-tight" style="color: var(--text-main);">Record Payment</h3>
                            <p class="text-xs" style="color: var(--text-muted);">Log an outgoing supplier payment</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="showPayment = false"
                            class="w-8 h-8 rounded-lg flex items-center justify-center border-0 cursor-pointer transition-all"
                            style="background: var(--bg-subtle); color: var(--text-muted);"
                            onmouseover="this.style.background='#E2E8F0'; this.style.color='#0F172A'" onmouseout="this.style.background='#F1F5F9'; this.style.color='#64748B'">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.suppliers.payments.store', $supplier) }}" method="POST"
                  style="overflow-y: auto; flex: 1; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                @csrf

                {{-- Supplier / outstanding summary --}}
                <div style="background: var(--bg-page); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.85rem 1rem; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);">Supplier</span>
                        <span style="font-weight: 800; color: var(--text-main);">{{ $supplier->name }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.35rem;">
                        <span style="color: var(--text-muted);">Outstanding balance</span>
                        <span style="font-weight: 800; color: {{ $supplier->balance > 0 ? 'var(--text-danger)' : 'var(--text-success)' }};">₱{{ number_format($supplier->balance, 2) }}</span>
                    </div>
                </div>

                {{-- Amount Paid --}}
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                        Amount Paid (₱) <span style="color: var(--text-danger);">*</span>
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-accent); font-size: 0.95rem; font-weight: 800;">
                            ₱
                        </span>
                        <input type="number" step="0.01" min="0.01" name="amount_paid" required placeholder="0.00"
                               style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box; font-weight: 600;"
                               onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                               onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                    </div>
                </div>

                {{-- Payment Method & Date --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.875rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                            Payment Method <span style="color: var(--text-danger);">*</span>
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem; pointer-events: none;">
                                <i class="fa-solid fa-credit-card"></i>
                            </span>
                            <select name="payment_method" class="custom-combo" data-icon="fa-credit-card" data-accent="#0099FF"
                                    style="width: 100%; padding: 0.65rem 0.75rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box; cursor: pointer;"
                                    onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                    onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="GCash">GCash</option>
                                <option value="Maya">Maya</option>
                                <option value="Check">Check</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                            Payment Date
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem; pointer-events: none; z-index: 1;">
                                <i class="fa-solid fa-calendar-day"></i>
                            </span>
                            <input type="text" id="paid_at_picker" name="paid_at" value="{{ now()->toDateString() }}" autocomplete="off" class="sp-date-field">
                        </div>
                    </div>
                </div>

                {{-- Apply to PO --}}
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                        Apply to Purchase Order (optional)
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem; pointer-events: none;">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </span>
                        <select name="purchase_id" class="custom-combo" data-icon="fa-file-invoice-dollar"
                                style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box; cursor: pointer;"
                                onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                            <option value="">General payment (no specific PO)</option>
                            @foreach($openPurchases as $po)
                                <option value="{{ $po->id }}">{{ $po->reference_no }} — ₱{{ number_format($po->balance, 2) }} due</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Reference No --}}
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                        Reference No.
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem;">
                            <i class="fa-solid fa-hashtag"></i>
                        </span>
                        <input type="text" name="reference_no" placeholder="Receipt / transaction reference"
                               style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                               onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                               onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                        Notes
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 0.7rem; color: var(--text-subtle); font-size: 0.8rem;">
                            <i class="fa-solid fa-note-sticky"></i>
                        </span>
                        <textarea name="notes" rows="2" placeholder="Optional payment notes"
                                  style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; resize: vertical; transition: border-color 0.2s, background 0.2s; box-sizing: border-box; font-family: inherit;"
                                  onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                  onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';"></textarea>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div style="display: flex; justify-content: flex-end; gap: 0.625rem; padding-top: 0.5rem; border-top: 1px solid var(--border-color); margin-top: 0.25rem;">
                    <button type="button" class="btn btn-outline" @click="showPayment = false">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ Edit Supplier modal ══ --}}
    <div x-show="showSupplierEdit" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="background: rgba(15,23,42,.65); backdrop-filter: blur(6px);" @keydown.escape.window="showSupplierEdit = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" @click.outside="showSupplierEdit = false"
             style="max-height: 90vh; display: flex; flex-direction: column;">

            {{-- Modal Header --}}
            <div style="background: var(--bg-surface); padding: 1.25rem 1.5rem; flex-shrink: 0; border-bottom: 1px solid var(--border-color);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--accent-soft);">
                            <i class="fa-solid fa-pen text-sm" style="color: var(--text-accent);"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-extrabold leading-tight" style="color: var(--text-main);">Edit Supplier</h3>
                            <p class="text-xs" style="color: var(--text-muted);">Update the supplier's details</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="showSupplierEdit = false"
                            class="w-8 h-8 rounded-lg flex items-center justify-center border-0 cursor-pointer transition-all"
                            style="background: var(--bg-subtle); color: var(--text-muted);"
                            onmouseover="this.style.background='#E2E8F0'; this.style.color='#0F172A'" onmouseout="this.style.background='#F1F5F9'; this.style.color='#64748B'">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST"
                  style="overflow-y: auto; flex: 1; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                @csrf
                @method('PUT')

                {{-- Supplier Name --}}
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem; letter-spacing: 0.01em;">
                        Supplier Name <span style="color: var(--text-danger);">*</span>
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem;">
                            <i class="fa-solid fa-building"></i>
                        </span>
                        <input type="text" name="name" required value="{{ $supplier->name }}"
                               style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                               onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                               onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                    </div>
                </div>

                {{-- Contact Person + Phone --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.875rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">Contact Person</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem;">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <input type="text" name="contact_person" value="{{ $supplier->contact_person }}"
                                   placeholder="Full name"
                                   style="width: 100%; padding: 0.65rem 0.75rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                                   onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                   onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">Phone</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem;">
                                <i class="fa-solid fa-phone"></i>
                            </span>
                            <input type="tel" name="phone"
                                   value="{{ $supplier->phone ?: '09' }}"
                                   maxlength="11"
                                   placeholder="09XXXXXXXXX"
                                   inputmode="numeric"
                                   style="width: 100%; padding: 0.65rem 0.75rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box; letter-spacing: 0.03em;"
                                   onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                   onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';"
                                   oninput="
                                       var v = this.value.replace(/\D/g, '');
                                       if (!v.startsWith('09')) v = '09' + v.replace(/^0*9*/, '');
                                       this.value = v.slice(0, 11);
                                   "
                                   onkeydown="
                                       if ((this.selectionStart < 2 || (this.selectionStart === 0 && this.selectionEnd >= 2)) &&
                                           (event.key === 'Backspace' || event.key === 'Delete')) {
                                           event.preventDefault();
                                       }
                                   ">
                        </div>
                        <p style="font-size: 0.7rem; color: var(--text-subtle); margin-top: 0.3rem;">
                            <i class="fa-solid fa-circle-info"></i> 11-digit PH mobile number (starts with 09)
                        </p>
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">Email</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem;">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" name="email" value="{{ $supplier->email }}"
                               placeholder="supplier@email.com"
                               style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                               onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                               onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                    </div>
                </div>

                {{-- Address --}}
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">Address</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 0.7rem; color: var(--text-subtle); font-size: 0.8rem;">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                        <textarea name="address" rows="2" placeholder="Complete address"
                                  style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; resize: vertical; transition: border-color 0.2s, background 0.2s; box-sizing: border-box; font-family: inherit;"
                                  onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                  onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">{{ $supplier->address }}</textarea>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div style="display: flex; justify-content: flex-end; gap: 0.625rem; padding-top: 0.5rem; border-top: 1px solid var(--border-color); margin-top: 0.25rem;">
                    <button type="button" class="btn btn-outline" @click="showSupplierEdit = false">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Generic delete confirm modal ── --}}
    <div x-show="deleteModal.open" x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);"
         @keydown.escape.window="deleteModal.open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.outside="deleteModal.open = false">
            <div class="px-6 pt-6 pb-4 text-center">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-rose-100 mb-3">
                    <i class="fa-solid fa-trash-can text-rose-600 text-2xl"></i>
                </span>
                <h3 class="text-lg font-extrabold text-slate-900" x-text="deleteModal.label"></h3>
                <p class="text-sm text-slate-500 mt-1" x-text="deleteModal.message"></p>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" class="btn btn-outline" @click="deleteModal.open = false">Cancel</button>
                <button type="button"
                        class="btn btn-primary"
                        style="background: var(--danger-color); border-color: var(--danger-color);"
                        @click="$refs.deleteActionForm.submit()">
                    <i class="fa-solid fa-trash-can"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
    <form x-ref="deleteActionForm" method="POST" x-bind:action="deleteModal.action" class="hidden">
        @csrf
        @method('DELETE')
    </form>

</div>
@endsection

@push('scripts')
<script>
    function ledgerState() {
        var catalog = [];
        var materials = [];
        try {
            catalog = JSON.parse(document.getElementById('catalogData').textContent || '[]');
        } catch (e) {
            catalog = [];
        }
        try {
            materials = JSON.parse(document.getElementById('materialData').textContent || '[]');
        } catch (e) {
            materials = [];
        }

        return {
            tab: 'catalog',
            tabs: [
                { key: 'catalog', label: 'Catalog' },
                { key: 'purchases', label: 'Purchases' },
                { key: 'payments', label: 'Payments' },
                { key: 'timeline', label: 'Timeline' },
            ],
            showItem: false,
            showEdit: false,
            showPayment: false,
            showSupplierEdit: false,
            deleteModal: { open: false, action: '', label: '', message: '' },
            confirmDelete(action, label, message) {
                this.deleteModal.action = action;
                this.deleteModal.label = label;
                this.deleteModal.message = message || 'This action cannot be undone.';
                this.deleteModal.open = true;
            },
            catalog: catalog,
            materials: materials,
            create: { inventory_material_id: '', supplier_unit_name: '', conversion_qty: '' },
            edit: { id: null, inventory_material_id: '', supplier_unit_name: '', conversion_qty: 1, unit_cost: 0, base_unit: '' },

            baseUnitOf(materialId) {
                var found = this.materials.find(function (m) { return String(m.id) === String(materialId); });
                return found ? found.base_unit : 'base units';
            },

            materialById(materialId) {
                return this.materials.find(function (m) { return String(m.id) === String(materialId); }) || null;
            },

            /* Package labels already mapped for an inventory item, optionally skipping one row. */
            packageLabels(materialId, excludeId) {
                var material = this.materialById(materialId);
                if (!material) return [];

                return (material.catalogued_as || [])
                    .filter(function (entry) { return String(entry.id) !== String(excludeId); })
                    .map(function (entry) { return entry.label; });
            },

            /* Tie the package back to real stock: what inventory holds now, and what one package adds. */
            stockInfo(materialId, unitName, conversion, excludeId) {
                var material = this.materialById(materialId);

                if (!material) {
                    return { show: false, low: false, label: '', note: '', threshold: '', url: '', packages: [] };
                }

                var base = material.base_unit || 'units';
                var stock = Number(material.stock) || 0;
                var threshold = Number(material.low_stock_threshold) || 0;
                var pkg = (unitName || 'Package').trim() || 'Package';
                var perPackage = parseFloat(conversion);

                var info = {
                    show: true,
                    low: material.is_low === true,
                    label: this.trim(stock) + ' ' + base + ' in stock',
                    threshold: this.trim(threshold),
                    url: material.inventory_url,
                    packages: this.packageLabels(materialId, excludeId),
                    note: '',
                };

                if (isNaN(perPackage) || perPackage <= 0) {
                    info.note = 'Enter a conversion value to preview how much stock each ' + pkg + ' adds.';
                    return info;
                }

                var after = Math.round((stock + perPackage) * 1000) / 1000;
                info.note = 'Each ' + pkg + ' restocks +' + this.trim(perPackage) + ' ' + base
                    + ' — inventory goes from ' + this.trim(stock) + ' to ' + this.trim(after) + ' ' + base + ' on receipt.';
                return info;
            },

            createStock() {
                return this.stockInfo(this.create.inventory_material_id, this.create.supplier_unit_name, this.create.conversion_qty, null);
            },

            editStock() {
                return this.stockInfo(this.edit.inventory_material_id, this.edit.supplier_unit_name, this.edit.conversion_qty, this.edit.id);
            },

            trim(value) {
                var n = parseFloat(value);
                return isNaN(n) ? '' : String(Math.round(n * 1000) / 1000);
            },

            /* "1 Box = 5 L in Inventory" — the label the admin needs to trust the entry. */
            hint(materialId, unitName, conversion) {
                var unit = this.trim(conversion);
                var pkg = (unitName || 'Package').trim();

                if (!materialId) {
                    return 'Select an inventory item to see how 1 ' + pkg + ' converts to stock units.';
                }

                if (!unit) {
                    return '1 ' + pkg + ' = ? ' + this.baseUnitOf(materialId) + ' in Inventory — enter the conversion value.';
                }

                return '1 ' + pkg + ' = ' + unit + ' ' + this.baseUnitOf(materialId) + ' in Inventory';
            },

            createHint() {
                return this.hint(this.create.inventory_material_id, this.create.supplier_unit_name, this.create.conversion_qty);
            },

            editHint() {
                return this.hint(this.edit.inventory_material_id, this.edit.supplier_unit_name, this.edit.conversion_qty);
            },

            openEdit(id) {
                var item = this.catalog.find(function (i) { return i.id === id; });
                if (!item) return;
                this.edit = {
                    id: item.id,
                    inventory_material_id: String(item.inventory_material_id ?? ''),
                    supplier_unit_name: item.supplier_unit_name || '',
                    conversion_qty: Number(item.conversion_qty || 0),
                    unit_cost: Number(item.unit_cost || 0),
                    base_unit: item.base_unit || '',
                };
                this.showEdit = true;
            }
        };
    }
</script>

{{-- ─── CUSTOM COMBOBOX FOR INVENTORY MATERIAL SELECTS (syncs Alpine x-model; re-syncs on modal open) ─── --}}
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

    function syncCombos() { registry.forEach(function (s) { if (s.__comboRender) s.__comboRender(); }); }

    // Re-sync the button label when a modal opens (Alpine prefills edit.inventory_material_id)
    ['[x-show="showItem"]', '[x-show="showEdit"]'].forEach(function (sel) {
        const el = document.querySelector(sel);
        if (!el) return;
        new MutationObserver(function () {
            if (el.style.display !== 'none') { requestAnimationFrame(syncCombos); }
        }).observe(el, { attributes: true, attributeFilter: ['style'] });
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

{{-- ─── PAYMENT DATE FLATPICKR (blue theme, matches Record Payment modal) ─── --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof flatpickr === 'undefined') return;
        flatpickr('#paid_at_picker', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'M j, Y',
            altInputClass: 'sp-date-field',
            animate: true,
            disableMobile: true
        });
    });
</script>
@endpush
