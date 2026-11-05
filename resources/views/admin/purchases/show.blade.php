@extends('layouts.admin')

@section('title', $purchase->reference_no . ' | Purchase Order')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Payment Date — Flatpickr altInput styling (matches the Record Payment modal) */
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

    /* Amount Paid — hide the native number spinner for a clean look (matches supplier modal) */
    input[name="amount_paid"]::-webkit-outer-spin-button,
    input[name="amount_paid"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[name="amount_paid"] { -moz-appearance: textfield; appearance: textfield; }

    /* Custom Flatpickr theme — blue accent */
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
<div x-data="{
    showPayment: false,
    deleteModal: { open: false, action: '', title: '', message: '', method: 'DELETE', extraName: '', extraValue: '' },
    confirmAction(action, title, message, method, extraName, extraValue) {
        this.deleteModal.action = action;
        this.deleteModal.title = title;
        this.deleteModal.message = message;
        this.deleteModal.method = method || 'DELETE';
        this.deleteModal.extraName = extraName || '';
        this.deleteModal.extraValue = extraValue || '';
        this.deleteModal.open = true;
    }
}">

    {{-- ── Header ── --}}
    <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
        <div>
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-primary" style="display: inline-flex; margin-bottom: 0.6rem;">
                <i class="fa-solid fa-arrow-left"></i> Back to Purchase Orders
            </a>
            <div class="flex flex-wrap items-center gap-3 mt-1">
                <h1 class="text-2xl font-extrabold" style="color: var(--text-main);">{{ $purchase->reference_no }}</h1>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize
                    {{ $purchase->status === 'received' ? 'bg-emerald-50 text-emerald-600' : ($purchase->status === 'cancelled' ? 'bg-slate-100 text-slate-500' : 'bg-amber-50 text-amber-600') }}">
                    {{ $purchase->status }}
                </span>
            </div>
            <p class="text-sm mt-1" style="color: var(--text-muted);">
                @if($purchase->supplier)
                    <a href="{{ route('admin.suppliers.show', $purchase->supplier) }}" class="font-semibold text-sky-600 hover:text-sky-700">
                        {{ $purchase->supplier->name }}
                    </a>
                @else
                    <span class="font-semibold text-slate-500">Unknown supplier</span>
                @endif
                · Created {{ $purchase->created_at->format('M d, Y g:i A') }}
                @if($purchase->due_date)
                    · Due
                    <span class="{{ $purchase->due_date->isPast() && $purchase->status === 'pending' ? 'text-rose-600 font-bold' : '' }}">
                        {{ $purchase->due_date->format('M d, Y') }}
                    </span>
                @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($purchase->status === 'pending')
                <button type="button" class="btn btn-primary"
                        @click="confirmAction(
                            '{{ route('admin.purchases.status', $purchase) }}',
                            'Mark as Received?',
                            'Linked inventory items will be restocked. This cannot be undone.',
                            'POST', 'status', 'received'
                        )">
                    <i class="fa-solid fa-boxes-packing"></i> Mark as Received
                </button>
            @endif

            @if($purchase->status !== 'received')
                <button type="button" class="btn"
                        style="background:var(--bg-surface); color:var(--text-danger); border:1px solid var(--danger-border);"
                        onmouseover="this.style.background='#FEF2F2'; this.style.borderColor='#DC2626';"
                        onmouseout="this.style.background='#FFFFFF'; this.style.borderColor='#FCA5A5';"
                        @click="confirmAction(
                            '{{ route('admin.purchases.destroy', $purchase) }}',
                            'Delete Purchase Order?',
                            'This permanently cancels and deletes {{ addslashes($purchase->reference_no) }}. This cannot be undone.',
                            'DELETE', '', ''
                        )">
                    <i class="fa-solid fa-trash-can" style="color:var(--text-danger);"></i> Delete
                </button>
            @endif
        </div>
    </div>

    {{-- ── Metrics ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Amount</p>
            <p class="text-2xl font-extrabold text-[var(--text-main)] mt-2">₱{{ number_format($purchase->total_amount, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Paid</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-2">₱{{ number_format($purchase->paid_amount, 2) }}</p>
        </div>
        <div class="rounded-xl border p-5 shadow-sm {{ $purchase->balance > 0 ? 'border-rose-200 bg-rose-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-bold uppercase tracking-wider {{ $purchase->balance > 0 ? 'text-rose-500' : 'text-slate-400' }}">Balance</p>
            <p class="text-2xl font-extrabold mt-2 {{ $purchase->balance > 0 ? 'text-rose-600' : 'text-[var(--text-main)]' }}">
                ₱{{ number_format($purchase->balance, 2) }}
            </p>
            <p class="text-xs mt-1 text-slate-500">{{ $purchase->balance > 0 ? 'Outstanding on this PO' : 'Fully settled' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

        {{-- ══ Line items ══ --}}
        <div class="xl:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100">
                <h2 class="font-bold text-slate-800"><i class="fa-solid fa-list-check text-sky-500"></i> Ordered Items</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-left">
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Item</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Package</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-center">Qty</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-right">Unit Cost</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-right">Subtotal</th>
                            <th class="px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider">Added to Inventory</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($purchase->items as $item)
                            @php $catalog = $item->supplierMaterial; @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $item->item_name }}</p>
                                    @if($catalog?->inventoryMaterial)
                                        <p class="text-xs text-slate-400">{{ $catalog->inventoryMaterial->name }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($catalog)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-sky-50 text-sky-600 text-xs font-bold">
                                            <i class="fa-solid fa-cube"></i> {{ $catalog->supplier_unit_name }}
                                        </span>
                                        <p class="text-[11px] text-slate-400 mt-1">{{ $catalog->package_label }}</p>
                                    @else
                                        <span class="text-xs text-slate-400">Catalog entry removed</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-slate-700">
                                    {{ $item->quantity }}
                                    @if($catalog)
                                        <span class="block text-[11px] text-slate-400">{{ Str::plural($catalog->supplier_unit_name, $item->quantity) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-slate-600">₱{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-slate-800">₱{{ number_format($item->subtotal, 2) }}</td>
                                <td class="px-4 py-3">
                                    @if($catalog?->inventoryMaterial)
                                        <p class="font-semibold text-slate-700">
                                            +{{ (float) $catalog->toBaseUnits($item->quantity) }} {{ $catalog->base_unit }}
                                        </p>
                                        <p class="text-[11px] text-slate-400">{{ $catalog->inventoryMaterial->name }}</p>
                                        @if($purchase->status === 'received')
                                            <span class="block text-[11px] text-emerald-600 mt-1"><i class="fa-solid fa-check"></i> Restocked</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">No inventory link</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No items on this purchase order.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50">
                            <td colspan="4" class="px-4 py-3 text-right font-bold text-slate-600">Grand Total</td>
                            <td class="px-4 py-3 text-right text-base font-extrabold text-[var(--text-main)]">₱{{ number_format($purchase->total_amount, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($purchase->notes)
                <div class="px-5 py-4 border-t border-slate-100">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Notes</p>
                    <p class="text-sm text-slate-600 whitespace-pre-line">{{ $purchase->notes }}</p>
                </div>
            @endif
        </div>

        {{-- ══ Payments ══ --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-800"><i class="fa-solid fa-money-check-dollar text-sky-500"></i> Payments</h2>
                @if($purchase->status !== 'cancelled')
                    <button type="button" class="btn btn-primary text-xs" @click="showPayment = true">
                        <i class="fa-solid fa-plus"></i> Add
                    </button>
                @endif
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($purchase->payments as $payment)
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <div>
                            <p class="font-bold text-slate-800">₱{{ number_format($payment->amount_paid, 2) }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $payment->payment_method }}
                                @if($payment->reference_no) · {{ $payment->reference_no }} @endif
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                {{ ($payment->paid_at ?? $payment->created_at)->format('M d, Y') }}
                            </p>
                            @if($payment->notes)
                                <p class="text-xs text-slate-500 mt-1 italic">{{ $payment->notes }}</p>
                            @endif
                        </div>
                        <button type="button"
                                class="w-7 h-7 rounded-lg flex items-center justify-center bg-rose-50 text-rose-600 hover:bg-rose-100 border-0 outline-none cursor-pointer"
                                title="Delete payment"
                                @click="confirmAction(
                                    '{{ route('admin.suppliers.payments.destroy', $payment) }}',
                                    'Delete Payment?',
                                    'This payment record will be permanently deleted.',
                                    'DELETE', '', ''
                                )">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <i class="fa-solid fa-receipt text-3xl text-slate-300"></i>
                        <p class="mt-2 text-sm font-semibold text-slate-500">No payments recorded</p>
                        <p class="text-xs text-slate-400">Record a payment to lower this PO's balance.</p>
                    </div>
                @endforelse
            </div>

            @if(($paymentMethods ?? null) && $purchase->supplier)
                {{-- Add payment modal --}}
                <div x-show="showPayment" x-cloak
                     class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                     style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);"
                     @keydown.escape.window="showPayment = false">
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

                        <form action="{{ route('admin.suppliers.payments.store', $purchase->supplier) }}" method="POST"
                              style="overflow-y: auto; flex: 1; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                            @csrf
                            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

                            {{-- Supplier / outstanding summary --}}
                            <div style="background: var(--bg-page); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.85rem 1rem; font-size: 0.85rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: var(--text-muted);">Supplier</span>
                                    <span style="font-weight: 800; color: var(--text-main);">{{ $purchase->supplier?->name }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.35rem;">
                                    <span style="color: var(--text-muted);">Outstanding balance</span>
                                    <span style="font-weight: 800; color: {{ ($purchase->supplier?->balance ?? 0) > 0 ? 'var(--text-danger)' : 'var(--text-success)' }};">₱{{ number_format($purchase->supplier?->balance ?? 0, 2) }}</span>
                                </div>
                            </div>

                            {{-- Amount Paid --}}
                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                                    Amount Paid (₱) <span style="color: var(--text-danger);">*</span>
                                </label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-accent); font-size: 0.95rem; font-weight: 800;">₱</span>
                                    <input type="number" step="0.01" min="0.01" name="amount_paid" required placeholder="0.00"
                                           value="{{ $purchase->balance > 0 ? number_format($purchase->balance, 2, '.', '') : '' }}"
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
                                        <select name="payment_method" class="custom-combo" data-icon="fa-credit-card"
                                                style="width: 100%; padding: 0.65rem 0.75rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box; cursor: pointer;"
                                                onfocus="this.style.borderColor='#0099FF'; this.style.background='#FFFFFF';"
                                                onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
                                            @foreach($paymentMethods as $method)
                                                <option value="{{ $method }}">{{ $method }}</option>
                                            @endforeach
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
                                        <input type="text" id="paid_at_picker" name="paid_at" value="{{ now()->format('Y-m-d') }}" autocomplete="off" class="sp-date-field">
                                    </div>
                                </div>
                            </div>

                            {{-- Reference No --}}
                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">
                                    Reference No.
                                </label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem; pointer-events: none;">
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
            @endif
        </div>
    </div>

    {{-- ── Generic confirm modal (mark received / cancel PO / delete PO / delete payment) ── --}}
    <div x-show="deleteModal.open" x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);"
         @keydown.escape.window="deleteModal.open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.outside="deleteModal.open = false">
            <div class="px-6 pt-6 pb-4 text-center">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-rose-100 mb-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-2xl"></i>
                </span>
                <h3 class="text-lg font-extrabold text-[var(--text-main)]" x-text="deleteModal.title"></h3>
                <p class="text-sm text-slate-500 mt-1" x-text="deleteModal.message"></p>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" class="btn btn-outline" @click="deleteModal.open = false">Cancel</button>
                <button type="button"
                        class="btn btn-primary"
                        style="background: var(--danger-color); border-color: var(--danger-color);"
                        @click="$refs.confirmActionForm.submit()">
                    <i class="fa-solid fa-check"></i> Confirm
                </button>
            </div>
        </div>
    </div>
    <form x-ref="confirmActionForm" method="POST" x-bind:action="deleteModal.action" class="hidden">
        @csrf
        <input type="hidden" x-bind:name="deleteModal.method === 'DELETE' ? '_method' : ''" value="DELETE">
        <input type="hidden" x-bind:name="deleteModal.extraName" x-bind:value="deleteModal.extraValue">
    </form>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
/* ── Generic custom combobox (mirrors the supplier Record Payment modal) ── */
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
        registry.push(select);
    }

    document.querySelectorAll('select.custom-combo').forEach(enhanceCombo);

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

    /* Payment Date Flatpickr */
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#paid_at_picker', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'M j, Y',
            altInputClass: 'sp-date-field',
            animate: true,
            disableMobile: true
        });
    }
});
</script>
@endpush
