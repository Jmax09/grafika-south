@extends('layouts.admin')

@section('title', 'Purchase Orders | Grafika South')

@section('content')

@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════════════
   EXECUTIVE DESIGN — Purchase Orders Page
   Brand: Cyan #0099FF · Amber #F59E0B · Emerald #10B981 · Slate #0F172A
   ══════════════════════════════════════════════════════════════════════ */
.po-page {
    --po-cyan: #0099FF;
    --po-cyan-hover: #0080FF;
    --po-cyan-light: #E6F4FF;
    --po-dark: #0F172A;
    --po-text: #1E293B;
    --po-muted: #64748B;
    --po-faint: #94A3B8;
    --po-border: #E2E8F0;
    --po-bg-soft: #F8FAFC;
    --po-card-radius: 16px;
    --po-shadow: 0 1px 3px rgba(15,23,42,0.03), 0 4px 12px rgba(15,23,42,0.03);
    --po-shadow-hover: 0 10px 25px -5px rgba(15,23,42,0.08), 0 8px 10px -6px rgba(15,23,42,0.04);
}

/* Dark mode: flip the self-contained --po-* palette to dark surfaces/text. */
html.theme-dark .po-page {
    --po-cyan-light: rgba(56, 189, 248, 0.16);
    --po-dark: #E6EDF7;
    --po-text: #D7E0EC;
    --po-muted: #94A3B8;
    --po-faint: #64748B;
    --po-border: #1E293B;
    --po-bg-soft: #131D31;
}

/* ── Header ── */
.po-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.po-title-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.po-title {
    font-family: 'Outfit', sans-serif;
    font-size: 1.85rem;
    font-weight: 800;
    color: var(--po-dark);
    letter-spacing: -0.025em;
    line-height: 1.15;
    margin: 0;
}
.po-subtitle {
    color: var(--po-muted);
    font-size: 0.88rem;
    margin: 0;
    line-height: 1.45;
}
.po-header-actions {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}
.po-btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 1rem;
    border: 1.5px solid var(--po-border);
    border-radius: 12px;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--po-text);
    background: var(--bg-surface);
    cursor: pointer;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.po-btn-outline:hover {
    border-color: var(--border-color);
    background: var(--po-bg-soft);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.po-btn-outline i { color: var(--po-muted); }
.po-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.15rem;
    border: none;
    border-radius: 12px;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-inverse);
    background: linear-gradient(135deg, var(--po-cyan) 0%, var(--po-cyan-hover) 100%);
    cursor: pointer;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(0, 153, 255, 0.25);
}
.po-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(0, 153, 255, 0.35);
}

/* ── KPI Metric Grid ── */
.po-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.9rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 1200px) {
    .po-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 480px) {
    .po-kpi-grid { grid-template-columns: minmax(0, 1fr); }
}
.po-kpi-card {
    position: relative;
    background: var(--bg-surface);
    border: 1px solid var(--po-border);
    border-radius: var(--po-card-radius);
    padding: 1.15rem 1.25rem;
    box-shadow: var(--po-shadow);
    transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
    overflow: hidden;
}
.po-kpi-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--kpi-accent, var(--po-cyan));
    opacity: 0.85;
    transition: height 0.2s ease, opacity 0.2s ease;
}
.po-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--po-shadow-hover);
    border-color: var(--border-color);
}
.po-kpi-card:hover::after {
    height: 4px;
    opacity: 1;
}
.po-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.6rem;
}
.po-kpi-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.po-kpi-label {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--po-muted);
}
.po-kpi-value {
    font-family: 'Outfit', sans-serif;
    font-size: clamp(1.35rem, 1.5vw, 1.75rem);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.025em;
    font-variant-numeric: tabular-nums;
    margin-top: 0.15rem;
}
.po-kpi-value .cents {
    font-size: 0.72em;
    opacity: 0.75;
}

/* ── Filter Card ── */
.po-filter-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--po-card-radius);
    padding: 1.15rem 1.35rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
}
.po-filter-form {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.85rem;
}
.po-search-group {
    flex: 2;
    min-width: 220px;
    display: flex;
    align-items: center;
}
.po-search-input {
    flex: 1;
    padding: 0.65rem 1rem;
    border: 1.5px solid var(--border-color);
    border-right: none;
    border-radius: 11px 0 0 11px;
    font-size: 0.88rem;
    outline: none;
    background: var(--bg-surface);
    color: var(--po-dark);
    transition: border-color 0.2s;
    font-family: inherit;
}
.po-search-input:focus { border-color: var(--po-cyan); }
.po-search-submit {
    padding: 0.65rem 1.1rem;
    background: var(--po-cyan);
    color: var(--text-inverse);
    border: 1.5px solid var(--po-cyan);
    border-radius: 0 11px 11px 0;
    font-size: 0.84rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    white-space: nowrap;
    transition: background 0.15s;
    font-family: inherit;
}
.po-search-submit:hover { background: var(--po-cyan-hover); }
.po-filter-select-wrap {
    flex: 1;
    min-width: 160px;
}
.po-reset-btn {
    padding: 0.65rem 1.1rem;
    background: var(--bg-surface);
    border: 1.5px solid var(--border-color);
    border-radius: 11px;
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--text-muted);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    white-space: nowrap;
    transition: all 0.15s;
}
.po-reset-btn:hover { border-color: var(--border-strong); color: var(--po-dark); }

/* ── Table Container ── */
.po-table-wrap {
    background: var(--bg-surface);
    border: 1px solid var(--po-border);
    border-radius: var(--po-card-radius);
    box-shadow: var(--po-shadow);
    overflow: hidden;
}
.po-table {
    width: 100%;
    font-size: 0.85rem;
    border-collapse: collapse;
}
.po-table thead tr {
    background: var(--po-bg-soft);
    border-bottom: 1px solid var(--po-border);
}
.po-table thead th {
    padding: 0.75rem 1rem;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--po-faint);
    text-align: left;
}
.po-table thead th.text-right { text-align: right; }
.po-table thead th.text-center { text-align: center; }
.po-table tbody tr {
    border-bottom: 1px solid var(--border-color);
    transition: all 0.15s ease;
    cursor: pointer;
}
.po-table tbody tr:last-child { border-bottom: none; }
.po-table tbody tr:hover {
    background: var(--accent-soft);
    box-shadow: inset 3px 0 0 0 var(--po-cyan);
}
.po-table td {
    padding: 0.85rem 1rem;
    color: var(--po-text);
}
.po-table td.text-right { text-align: right; }
.po-table td.text-center { text-align: center; }
.po-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: capitalize;
}
.po-status-badge.received { background: var(--success-soft); color: var(--text-success); }
.po-status-badge.cancelled { background: var(--bg-subtle); color: var(--text-muted); }
.po-status-badge.pending { background: var(--warning-soft); color: var(--text-warning); }
.po-action-icon {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
    font-size: 0.75rem;
    text-decoration: none;
}
.po-action-icon.view { background: var(--accent-soft); color: var(--text-accent); }
.po-action-icon.view:hover { background: var(--accent-color); color: var(--text-inverse); }
.po-action-icon.delete { background: var(--danger-soft); color: var(--text-danger); }
.po-action-icon.delete:hover { background: var(--danger-color); color: var(--text-inverse); }

/* Empty state */
.po-empty-state {
    padding: 3.5rem 1rem;
    text-align: center;
}
.po-empty-state i { font-size: 2.5rem; color: var(--text-disabled); }
.po-empty-state p { font-weight: 700; color: var(--po-text); margin-top: 0.75rem; }
</style>
@endpush

@push('scripts')
<script>
    function openRowLink(event, url) {
        if (event.target.closest('button, form, a, input, label, select, textarea')) return;
        window.location = url;
    }
</script>
@endpush

{{-- ─── CUSTOM COMBOBOX FOR FILTER DROPDOWNS (auto-submits on pick) ─── --}}
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
            btn.style.cssText = 'width:100%; display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.65rem 1rem; border:1.5px solid ' + (isOpen ? accent : 'var(--border-strong)') + '; border-radius:11px; font-size:0.88rem; color:var(--text-main); background:var(--bg-surface); outline:none; cursor:pointer; box-sizing:border-box; transition:border-color 0.2s; font-family:inherit;';
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
});
</script>
@endpush

<div class="po-page" x-data="{
    deleteModal: { open: false, action: '', ref: '' },
    confirmDelete(action, ref) {
        this.deleteModal.action = action;
        this.deleteModal.ref = ref;
        this.deleteModal.open = true;
    }
}">

    {{-- ── Page Header ── --}}
    <header class="po-header">
        <div class="po-title-wrap">
            <h1 class="po-title">Purchase Orders</h1>
            <p class="po-subtitle">Create, track, and receive purchase orders from your suppliers.</p>
        </div>
        <div class="po-header-actions">
            <a href="{{ route('admin.suppliers.index') }}" class="po-btn-outline">
                <i class="fa-solid fa-truck-field"></i> Suppliers
            </a>
            <a href="{{ route('admin.purchases.create') }}" class="po-btn-primary">
                <i class="fa-solid fa-plus"></i> New Purchase Order
            </a>
        </div>
    </header>

    {{-- ── KPI Metric Cards ── --}}
    <section class="po-kpi-grid">
        {{-- Pending --}}
        <div class="po-kpi-card" style="--kpi-accent: #F59E0B;">
            <div class="po-kpi-top">
                <span class="po-kpi-label">Pending</span>
                <span class="po-kpi-icon" style="background: var(--warning-soft); color: var(--text-warning);">
                    <i class="fa-solid fa-clock"></i>
                </span>
            </div>
            <div class="po-kpi-value" style="color: var(--text-warning);">{{ $metrics['pending'] }}</div>
        </div>

        {{-- Received --}}
        <div class="po-kpi-card" style="--kpi-accent: #10B981;">
            <div class="po-kpi-top">
                <span class="po-kpi-label">Received</span>
                <span class="po-kpi-icon" style="background: var(--success-soft); color: var(--text-success);">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>
            <div class="po-kpi-value" style="color: var(--text-success);">{{ $metrics['received'] }}</div>
        </div>

        {{-- Cancelled --}}
        <div class="po-kpi-card" style="--kpi-accent: #64748B;">
            <div class="po-kpi-top">
                <span class="po-kpi-label">Cancelled</span>
                <span class="po-kpi-icon" style="background: var(--bg-subtle); color: var(--text-muted);">
                    <i class="fa-solid fa-ban"></i>
                </span>
            </div>
            <div class="po-kpi-value" style="color: var(--text-muted);">{{ $metrics['cancelled'] }}</div>
        </div>

        {{-- Payables --}}
        <div class="po-kpi-card" style="--kpi-accent: {{ $metrics['payables'] > 0 ? '#EF4444' : '#64748B' }}; {{ $metrics['payables'] > 0 ? 'border-color: var(--danger-border); background: linear-gradient(180deg, var(--bg-surface) 0%, var(--danger-soft) 100%);' : '' }}">
            <div class="po-kpi-top">
                <span class="po-kpi-label" style="{{ $metrics['payables'] > 0 ? 'color: var(--text-danger);' : '' }}">Payables</span>
                <span class="po-kpi-icon" style="background: {{ $metrics['payables'] > 0 ? 'var(--danger-soft)' : 'var(--bg-subtle)' }}; color: {{ $metrics['payables'] > 0 ? 'var(--text-danger)' : 'var(--text-muted)' }};">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </span>
            </div>
            @php $payWhole = number_format(floor($metrics['payables']), 0); $payCents = str_pad(($metrics['payables'] * 100) % 100, 2, '0', STR_PAD_LEFT); @endphp
            <div class="po-kpi-value" style="color: {{ $metrics['payables'] > 0 ? 'var(--text-danger)' : 'var(--text-main)' }};">₱{{ $payWhole }}<span class="cents">.{{ $payCents }}</span></div>
        </div>
    </section>

    {{-- ── Filters ── --}}
    <div class="po-filter-card">
        <form method="GET" action="{{ route('admin.purchases.index') }}" class="po-filter-form">
            <div class="po-search-group">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reference or supplier..."
                       class="po-search-input">
                <button type="submit" class="po-search-submit">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
            </div>
            <div class="po-filter-select-wrap">
                <select name="status" onchange="this.form.submit()" class="custom-combo" data-icon="fa-filter">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="po-filter-select-wrap" style="min-width: 180px;">
                <select name="supplier_id" onchange="this.form.submit()" class="custom-combo" data-icon="fa-truck-field">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ (string) request('supplier_id') === (string) $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['search', 'status', 'supplier_id']))
                <a href="{{ route('admin.purchases.index') }}" class="po-reset-btn">
                    <i class="fa-solid fa-rotate-left" style="color: var(--text-muted);"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ── Table ── --}}
    <div class="po-table-wrap">
        <div style="overflow-x: auto;">
            <table class="po-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Due</th>
                        <th class="text-center">Items</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Balance</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $po)
                        <tr onclick="openRowLink(event, '{{ route('admin.purchases.show', $po) }}')">
                            <td><span style="font-weight: 700; color: var(--po-dark);">{{ $po->reference_no }}</span></td>
                            <td>
                                @if($po->supplier)
                                    <a href="{{ route('admin.suppliers.show', $po->supplier) }}" style="color: var(--po-text); font-weight: 600; text-decoration: none;" onmouseover="this.style.color='#0099FF'" onmouseout="this.style.color='var(--po-text)'">
                                        {{ $po->supplier->name }}
                                    </a>
                                @else
                                    <span style="color: var(--po-faint); font-style: italic;">Unknown supplier</span>
                                @endif
                            </td>
                            <td style="color: var(--po-muted);">{{ $po->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($po->due_date)
                                    <span style="{{ $po->due_date->isPast() && $po->status === 'pending' ? 'color: var(--text-danger); font-weight: 700;' : 'color: var(--po-muted);' }}">
                                        {{ $po->due_date->format('M d, Y') }}
                                    </span>
                                @else
                                    <span style="color: var(--po-faint);">—</span>
                                @endif
                            </td>
                            <td class="text-center" style="color: var(--po-muted);">{{ $po->items_count }}</td>
                            <td class="text-right" style="font-weight: 700;">₱{{ number_format($po->total_amount, 2) }}</td>
                            <td class="text-right">
                                <span style="font-weight: 700; color: {{ $po->balance > 0 ? 'var(--text-danger)' : 'var(--text-success)' }};">
                                    ₱{{ number_format($po->balance, 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="po-status-badge {{ $po->status }}">
                                    {{ $po->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                                    <a href="{{ route('admin.purchases.show', $po) }}" class="po-action-icon view" title="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <button type="button"
                                            @click="confirmDelete('{{ route('admin.purchases.destroy', $po) }}', '{{ addslashes($po->reference_no) }}')"
                                            class="po-action-icon delete"
                                            title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="po-empty-state">
                                    <i class="fa-solid fa-file-invoice"></i>
                                    <p>No purchase orders found</p>
                                    <a href="{{ route('admin.purchases.create') }}" class="po-btn-primary" style="margin-top: 1rem; display: inline-flex;">
                                        <i class="fa-solid fa-plus"></i> Create Purchase Order
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Delete PO confirm modal ── --}}
    <div x-show="deleteModal.open" x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);"
         @keydown.escape.window="deleteModal.open = false">
        <div class="bg-[var(--bg-surface)] rounded-2xl shadow-2xl w-full max-w-md" @click.outside="deleteModal.open = false">
            <div class="px-6 pt-6 pb-4 text-center">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-rose-100 mb-3">
                    <i class="fa-solid fa-trash-can text-rose-600 text-2xl"></i>
                </span>
                <h3 class="text-lg font-extrabold text-slate-900">Delete Purchase Order?</h3>
                <p class="text-sm text-slate-500 mt-1">
                    You are about to delete <span class="font-bold text-slate-800" x-text="deleteModal.ref"></span>.
                    This action cannot be undone.
                </p>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" class="po-btn-outline" @click="deleteModal.open = false">Cancel</button>
                <button type="button"
                        class="po-btn-primary"
                        style="background: var(--danger-color); box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);"
                        @click="$refs.deletePOForm.submit()">
                    <i class="fa-solid fa-trash-can"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
    <form x-ref="deletePOForm" method="POST" x-bind:action="deleteModal.action" class="hidden">
        @csrf
        @method('DELETE')
    </form>

</div>
@endsection
