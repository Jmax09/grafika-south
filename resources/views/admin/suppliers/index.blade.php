@extends('layouts.admin')

@section('title', 'Suppliers | Grafika South')

@section('content')

@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════════════
   EXECUTIVE DESIGN — Suppliers Management Page
   Brand: Cyan #0099FF · Blue #2563EB · Emerald #10B981 · Rose #EF4444
   ══════════════════════════════════════════════════════════════════════ */
.sup-page {
    --sup-cyan: #0099FF;
    --sup-cyan-hover: #0080FF;
    --sup-cyan-light: #E6F4FF;
    --sup-dark: #0F172A;
    --sup-text: #1E293B;
    --sup-muted: #64748B;
    --sup-faint: #94A3B8;
    --sup-border: #E2E8F0;
    --sup-bg-soft: #F8FAFC;
    --sup-card-radius: 16px;
    --sup-shadow: 0 1px 3px rgba(15,23,42,0.03), 0 4px 12px rgba(15,23,42,0.03);
    --sup-shadow-hover: 0 10px 25px -5px rgba(15,23,42,0.08), 0 8px 10px -6px rgba(15,23,42,0.04);
}

/* Dark mode: flip the self-contained --sup-* palette to dark surfaces/text. */
html.theme-dark .sup-page {
    --sup-cyan-light: rgba(56, 189, 248, 0.16);
    --sup-dark: #E6EDF7;
    --sup-text: #D7E0EC;
    --sup-muted: #94A3B8;
    --sup-faint: #64748B;
    --sup-border: #1E293B;
    --sup-bg-soft: #131D31;
}

/* ── Page Header ── */
.sup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.sup-title-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.sup-title {
    font-family: 'Outfit', sans-serif;
    font-size: 1.85rem;
    font-weight: 800;
    color: var(--sup-dark);
    letter-spacing: -0.025em;
    line-height: 1.15;
    margin: 0;
}
.sup-subtitle {
    color: var(--sup-muted);
    font-size: 0.88rem;
    margin: 0;
    line-height: 1.45;
}
.sup-header-actions {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}
.sup-btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 1rem;
    border: 1.5px solid var(--sup-border);
    border-radius: 12px;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--sup-text);
    background: var(--bg-surface);
    cursor: pointer;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.sup-btn-outline:hover {
    border-color: var(--border-color);
    background: var(--sup-bg-soft);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.sup-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.15rem;
    border: none;
    border-radius: 12px;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-inverse);
    background: linear-gradient(135deg, var(--sup-cyan) 0%, var(--sup-cyan-hover) 100%);
    cursor: pointer;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(0, 153, 255, 0.25);
}
.sup-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(0, 153, 255, 0.35);
}

/* ── KPI Metric Cards Grid ── */
.sup-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.9rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 1200px) {
    .sup-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 480px) {
    .sup-kpi-grid { grid-template-columns: minmax(0, 1fr); }
}
.sup-kpi-card {
    position: relative;
    background: var(--bg-surface);
    border: 1px solid var(--sup-border);
    border-radius: var(--sup-card-radius);
    padding: 1.15rem 1.25rem;
    box-shadow: var(--sup-shadow);
    transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
    overflow: hidden;
}
.sup-kpi-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--kpi-accent, var(--sup-cyan));
    opacity: 0.85;
    transition: height 0.2s ease, opacity 0.2s ease;
}
.sup-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--sup-shadow-hover);
    border-color: var(--border-color);
}
.sup-kpi-card:hover::after {
    height: 4px;
    opacity: 1;
}
.sup-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.6rem;
}
.sup-kpi-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.sup-kpi-label {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--sup-muted);
}
.sup-kpi-value {
    font-family: 'Outfit', sans-serif;
    font-size: clamp(1.35rem, 1.5vw, 1.75rem);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.025em;
    font-variant-numeric: tabular-nums;
    margin-top: 0.15rem;
}
.sup-kpi-value .cents {
    font-size: 0.72em;
    opacity: 0.75;
}
.sup-kpi-sub {
    font-size: 0.72rem;
    color: var(--sup-faint);
    font-weight: 600;
    margin-top: 0.35rem;
    line-height: 1.35;
}

/* ── Filters Card ── */
.sup-filter-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--sup-card-radius);
    padding: 1.15rem 1.35rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
}
.sup-filter-form {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.85rem;
}
.sup-search-group {
    flex: 2;
    min-width: 240px;
    display: flex;
    align-items: center;
}
.sup-search-input {
    flex: 1;
    padding: 0.65rem 1rem;
    border: 1.5px solid var(--border-color);
    border-right: none;
    border-radius: 11px 0 0 11px;
    font-size: 0.88rem;
    outline: none;
    background: var(--bg-surface);
    color: var(--sup-dark);
    transition: border-color 0.2s;
    font-family: inherit;
}
.sup-search-input:focus { border-color: var(--sup-cyan); }
.sup-search-submit {
    padding: 0.65rem 1.1rem;
    background: var(--sup-cyan);
    color: var(--text-inverse);
    border: 1.5px solid var(--sup-cyan);
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
.sup-search-submit:hover { background: var(--sup-cyan-hover); }
.sup-filter-select-wrap {
    flex: 1;
    min-width: 170px;
}
.sup-reset-btn {
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
.sup-reset-btn:hover { border-color: var(--border-strong); color: var(--sup-dark); }

/* ── Table ── */
.sup-table-wrap {
    background: var(--bg-surface);
    border: 1px solid var(--sup-border);
    border-radius: var(--sup-card-radius);
    box-shadow: var(--sup-shadow);
    overflow: hidden;
}
.sup-table {
    width: 100%;
    font-size: 0.85rem;
    border-collapse: collapse;
}
.sup-table thead tr {
    background: var(--sup-bg-soft);
    border-bottom: 1px solid var(--sup-border);
}
.sup-table thead th {
    padding: 0.75rem 1.15rem;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--sup-faint);
    text-align: left;
}
.sup-table thead th.text-right { text-align: right; }
.sup-table thead th.text-center { text-align: center; }
.sup-table tbody tr {
    border-bottom: 1px solid var(--border-color);
    transition: all 0.15s ease;
    cursor: pointer;
}
.sup-table tbody tr:last-child { border-bottom: none; }
.sup-table tbody tr:hover {
    background: var(--accent-soft);
    box-shadow: inset 3px 0 0 0 var(--sup-cyan);
}
.sup-table td {
    padding: 0.9rem 1.15rem;
    color: var(--sup-text);
}
.sup-table td.text-right { text-align: right; }
.sup-table td.text-center { text-align: center; }
.sup-action-icon {
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
.sup-action-icon.view { background: var(--accent-soft); color: var(--text-accent); }
.sup-action-icon.view:hover { background: var(--accent-color); color: var(--text-inverse); }
.sup-action-icon.delete { background: var(--danger-soft); color: var(--text-danger); }
.sup-action-icon.delete:hover { background: var(--danger-color); color: var(--text-inverse); }

/* ── Recent PO List ── */
.sup-recent-card {
    margin-top: 1.5rem;
    background: var(--bg-surface);
    border: 1px solid var(--sup-border);
    border-radius: var(--sup-card-radius);
    box-shadow: var(--sup-shadow);
    overflow: hidden;
}
.sup-recent-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.sup-recent-title {
    font-family: 'Outfit', sans-serif;
    font-size: 1rem;
    font-weight: 800;
    color: var(--sup-dark);
    margin: 0;
}
.sup-recent-sub {
    font-size: 0.75rem;
    color: var(--sup-faint);
    margin: 0.15rem 0 0 0;
}
.sup-recent-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid var(--border-color);
    text-decoration: none;
    transition: background 0.15s;
}
.sup-recent-row:last-child { border-bottom: none; }
.sup-recent-row:hover { background: var(--bg-page); }
.sup-status-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.18rem 0.55rem;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: capitalize;
}
.sup-status-pill.received { background: var(--success-soft); color: var(--text-success); }
.sup-status-pill.cancelled { background: var(--bg-subtle); color: var(--text-muted); }
.sup-status-pill.pending { background: var(--warning-soft); color: var(--text-warning); }

/* ── Empty State ── */
.sup-empty-state {
    padding: 3.5rem 1rem;
    text-align: center;
}
.sup-empty-state i { font-size: 2.5rem; color: var(--text-disabled); }
.sup-empty-state p { font-weight: 700; color: var(--sup-text); margin-top: 0.75rem; }
</style>
@endpush

@push('scripts')
<script>
    function openRowLink(event, url) {
        if (event.target.closest('button, form, a, input, label, select, textarea')) return;
        window.location = url;
    }
</script>

{{-- ─── CUSTOM COMBOBOX FOR FILTER SELECTS ─── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    function enhanceFilterCombo(select) {
        if (select.dataset.comboDone) return;
        select.dataset.comboDone = '1';

        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        select.style.display = 'none';

        const btn = document.createElement('button');
        btn.type = 'button';
        const label = document.createElement('span');
        label.style.cssText = 'flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-align:left;';
        const caret = document.createElement('i');
        caret.className = 'fa-solid fa-chevron-down';
        caret.style.cssText = 'font-size:0.72rem; transition:transform 0.15s;';
        btn.appendChild(label); btn.appendChild(caret);
        wrapper.appendChild(btn);

        const panel = document.createElement('div');
        panel.className = 'combo-panel';
        panel.style.cssText = 'display:none; position:absolute; top:calc(100% + 4px); left:0; right:0; background:var(--bg-elevated); border:1px solid var(--border-color); border-radius:10px; box-shadow:0 8px 20px rgba(15,23,42,0.12); max-height:260px; overflow-y:auto; z-index:80; padding:0.25rem;';
        wrapper.appendChild(panel);

        function styleBtn() {
            const active = select.value !== '';
            btn.style.cssText = 'width:100%; display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.65rem 1rem; border:1.5px solid ' + (active ? 'var(--accent-color)' : 'var(--border-strong)') + '; border-radius:11px; font-size:0.88rem; font-weight:' + (active ? '700' : '500') + '; background:' + (active ? 'var(--accent-soft)' : 'var(--bg-surface)') + '; color:' + (active ? 'var(--accent-hover)' : 'var(--text-main)') + '; cursor:pointer; transition:all 0.2s; font-family:inherit;';
            caret.style.color = active ? 'var(--accent-hover)' : 'var(--text-muted)';
        }
        function render() {
            const o = select.options[select.selectedIndex];
            label.textContent = o ? o.textContent.trim() : '';
            styleBtn();
            const cur = select.value;
            panel.innerHTML = '';
            Array.from(select.options).forEach(function (opt) {
                const item = document.createElement('div');
                const selected = opt.value === cur;
                item.textContent = opt.textContent.trim();
                item.style.cssText = 'display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.5rem 0.65rem; border-radius:7px; font-size:0.84rem; font-weight:' + (selected ? '700' : '500') + '; cursor:pointer; color:' + (selected ? 'var(--accent-contrast)' : 'var(--text-main)') + '; background:' + (selected ? 'var(--accent-color)' : 'var(--bg-elevated)') + ';';
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
                    close();
                });
                panel.appendChild(item);
            });
        }
        function open() { panel.style.display = 'block'; caret.style.transform = 'rotate(180deg)'; }
        function close() { panel.style.display = 'none'; caret.style.transform = 'rotate(0deg)'; }
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (panel.style.display === 'block') { close(); } else { open(); }
        });
        render();
    }

    document.querySelectorAll('select.custom-combo').forEach(enhanceFilterCombo);

    document.addEventListener('click', function (e) {
        document.querySelectorAll('select.custom-combo').forEach(function (sel) {
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

<div class="sup-page" x-data="{
    showAdd: false,
    deleteModal: {
        open: false,
        action: '',
        name: ''
    },
    confirmDelete(action, name) {
        this.deleteModal.action = action;
        this.deleteModal.name = name;
        this.deleteModal.open = true;
    }
}">

    {{-- ── Page Header ── --}}
    <header class="sup-header">
        <div class="sup-title-wrap">
            <h1 class="sup-title">Suppliers</h1>
            <p class="sup-subtitle">Manage your suppliers and purchase orders.</p>
        </div>
        <div class="sup-header-actions">
            <a href="{{ route('admin.suppliers.export', request()->query()) }}" class="sup-btn-outline" title="Download Excel / CSV Report">
                <i class="fa-solid fa-file-excel" style="color: var(--text-success);"></i> Download Report
            </a>
            <button type="button" class="sup-btn-primary" @click="showAdd = true">
                <i class="fa-solid fa-plus"></i> Add Supplier
            </button>
        </div>
    </header>

    {{-- ── Summary Metric Cards (4 Tiles Proportional) ── --}}
    <section class="sup-kpi-grid">
        {{-- Total Suppliers --}}
        <div class="sup-kpi-card" style="--kpi-accent: #0099FF;">
            <div class="sup-kpi-top">
                <span class="sup-kpi-label">Total Suppliers</span>
                <span class="sup-kpi-icon" style="background: var(--accent-soft); color: var(--text-accent);">
                    <i class="fa-solid fa-truck-field"></i>
                </span>
            </div>
            <div class="sup-kpi-value" style="color: var(--text-main);">{{ $metrics['total_suppliers'] }}</div>
        </div>

        {{-- Total Purchases --}}
        <div class="sup-kpi-card" style="--kpi-accent: #2563EB;">
            <div class="sup-kpi-top">
                <span class="sup-kpi-label">Total Purchases</span>
                <span class="sup-kpi-icon" style="background: var(--accent-soft); color: var(--text-accent);">
                    <i class="fa-solid fa-cart-flatbed"></i>
                </span>
            </div>
            @php $purWhole = number_format(floor($metrics['total_purchased']), 0); $purCents = str_pad(($metrics['total_purchased'] * 100) % 100, 2, '0', STR_PAD_LEFT); @endphp
            <div class="sup-kpi-value" style="color: var(--text-main);">₱{{ $purWhole }}<span class="cents">.{{ $purCents }}</span></div>
        </div>

        {{-- Total Paid --}}
        <div class="sup-kpi-card" style="--kpi-accent: #10B981;">
            <div class="sup-kpi-top">
                <span class="sup-kpi-label">Total Paid</span>
                <span class="sup-kpi-icon" style="background: var(--success-soft); color: var(--text-success);">
                    <i class="fa-solid fa-money-check-dollar"></i>
                </span>
            </div>
            @php $paidWhole = number_format(floor($metrics['total_paid']), 0); $paidCents = str_pad(($metrics['total_paid'] * 100) % 100, 2, '0', STR_PAD_LEFT); @endphp
            <div class="sup-kpi-value" style="color: var(--text-success);">₱{{ $paidWhole }}<span class="cents">.{{ $paidCents }}</span></div>
        </div>

        {{-- Total Payables --}}
        <div class="sup-kpi-card" style="--kpi-accent: {{ $metrics['total_payables'] > 0 ? '#EF4444' : '#64748B' }}; {{ $metrics['total_payables'] > 0 ? 'border-color: var(--danger-border); background: linear-gradient(180deg, var(--bg-surface) 0%, var(--danger-soft) 100%);' : '' }}">
            <div class="sup-kpi-top">
                <span class="sup-kpi-label" style="{{ $metrics['total_payables'] > 0 ? 'color: var(--text-danger);' : '' }}">Total Payables</span>
                <span class="sup-kpi-icon" style="background: {{ $metrics['total_payables'] > 0 ? 'var(--danger-soft)' : 'var(--bg-subtle)' }}; color: {{ $metrics['total_payables'] > 0 ? 'var(--text-danger)' : 'var(--text-muted)' }};">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </span>
            </div>
            @php $payWhole = number_format(floor($metrics['total_payables']), 0); $payCents = str_pad(($metrics['total_payables'] * 100) % 100, 2, '0', STR_PAD_LEFT); @endphp
            <div class="sup-kpi-value" style="color: {{ $metrics['total_payables'] > 0 ? 'var(--text-danger)' : 'var(--text-main)' }};">₱{{ $payWhole }}<span class="cents">.{{ $payCents }}</span></div>
            <div class="sup-kpi-sub">{{ $metrics['pending_pos'] }} pending purchase order(s)</div>
        </div>
    </section>

    {{-- ── Search & Filters ── --}}
    <div class="sup-filter-card">
        <form action="{{ route('admin.suppliers.index') }}" method="GET" class="sup-filter-form" id="supplier-filter-form">
            {{-- Search Input + Button (fused pair) --}}
            <div class="sup-search-group">
                <input type="text" name="search" id="supplier-search-input" value="{{ request('search') }}"
                    placeholder="Search supplier, contact, email, phone, or item offered..."
                    class="sup-search-input">
                <button type="submit" class="sup-search-submit">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
            </div>

            {{-- Category Dropdown Filter --}}
            <div class="sup-filter-select-wrap">
                <select name="category" id="supplier-category-select" class="custom-combo">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Balance Status Dropdown Filter --}}
            <div class="sup-filter-select-wrap">
                <select name="balance_status" id="supplier-balance-select" class="custom-combo">
                    <option value="">All Balances</option>
                    <option value="with_balance" {{ request('balance_status') == 'with_balance' ? 'selected' : '' }}>Has Balance (Payables)</option>
                    <option value="settled" {{ request('balance_status') == 'settled' ? 'selected' : '' }}>Fully Settled</option>
                    <option value="no_purchases" {{ request('balance_status') == 'no_purchases' ? 'selected' : '' }}>No Purchases Yet</option>
                </select>
            </div>

            {{-- Reset button (only when filters active) --}}
            @if(request()->hasAny(['search', 'category', 'balance_status']))
                <a href="{{ route('admin.suppliers.index') }}" class="sup-reset-btn">
                    <i class="fa-solid fa-rotate-left" style="color: var(--text-muted);"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ── Supplier Table ── --}}
    <div class="sup-table-wrap">
        <div style="overflow-x: auto;">
            <table class="sup-table">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th class="text-right">Purchases</th>
                        <th class="text-right">Total Paid</th>
                        <th class="text-right">Balance</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        @php $balance = $supplier->balance; @endphp
                        <tr onclick="openRowLink(event, '{{ route('admin.suppliers.show', $supplier) }}')">
                            <td>
                                <a href="{{ route('admin.suppliers.show', $supplier) }}" style="font-weight: 700; color: var(--sup-dark); text-decoration: none; display: block;" onmouseover="this.style.color='#0099FF'" onmouseout="this.style.color='var(--sup-dark)'">
                                    {{ $supplier->name }}
                                </a>
                                <p style="font-size: 0.72rem; color: var(--sup-faint); margin: 0.2rem 0 0 0;">{{ $supplier->purchases_count }} purchase order(s)</p>
                            </td>
                            <td>
                                <p style="margin: 0; color: var(--sup-text); font-weight: 500;">{{ $supplier->contact_person ?: '—' }}</p>
                                @if($supplier->phone || $supplier->email)
                                    <p style="font-size: 0.72rem; color: var(--sup-faint); margin: 0.2rem 0 0 0;">
                                        @if($supplier->phone)<span>{{ $supplier->phone }}</span>@endif
                                        @if($supplier->phone && $supplier->email) · @endif
                                        @if($supplier->email)<span>{{ $supplier->email }}</span>@endif
                                    </p>
                                @endif
                            </td>
                            <td class="text-right" style="font-weight: 600; color: var(--sup-text);">₱{{ number_format($supplier->total_purchases, 2) }}</td>
                            <td class="text-right" style="font-weight: 600; color: var(--text-success);">₱{{ number_format($supplier->total_paid, 2) }}</td>
                            <td class="text-right">
                                <span style="font-weight: 800; color: {{ $balance > 0 ? 'var(--text-danger)' : ($balance < 0 ? 'var(--text-success)' : 'var(--text-muted)') }};">
                                    ₱{{ number_format($balance, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                                    <a href="{{ route('admin.suppliers.show', $supplier) }}"
                                       class="sup-action-icon view"
                                       title="View ledger">
                                        <i class="fa-solid fa-book-open"></i>
                                    </a>
                                    <button type="button"
                                            @click="confirmDelete('{{ route('admin.suppliers.destroy', $supplier) }}', '{{ addslashes($supplier->name) }}')"
                                            class="sup-action-icon delete"
                                            title="Remove supplier">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="sup-empty-state">
                                    <i class="fa-solid fa-truck-field"></i>
                                    @if(request()->hasAny(['search', 'category', 'balance_status']))
                                        <p>No matching suppliers found</p>
                                        <p style="font-size: 0.8rem; color: var(--sup-muted); margin-top: 0.25rem;">Try adjusting or resetting your search and filters.</p>
                                        <a href="{{ route('admin.suppliers.index') }}" class="sup-btn-outline" style="margin-top: 1rem; display: inline-flex;">
                                            <i class="fa-solid fa-rotate-left"></i> Clear Filters
                                        </a>
                                    @else
                                        <p>No suppliers yet</p>
                                        <p style="font-size: 0.8rem; color: var(--sup-muted); margin-top: 0.25rem;">Add your first supplier to start building purchase orders.</p>
                                        <button type="button" class="sup-btn-primary" style="margin-top: 1rem; display: inline-flex;" @click="showAdd = true">
                                            <i class="fa-solid fa-plus"></i> Add Supplier
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Recent Purchase Orders (Compact) ── --}}
    @if($recentPurchases->isNotEmpty())
        <div class="sup-recent-card">
            <div class="sup-recent-header">
                <div>
                    <h2 class="sup-recent-title">Recent Purchase Orders</h2>
                    <p class="sup-recent-sub">Latest purchase orders across all suppliers</p>
                </div>
                <a href="{{ route('admin.purchases.index') }}" class="sup-btn-outline" style="font-size: 0.75rem; padding: 0.4rem 0.8rem; border-radius: 9px;">
                    View all <i class="fa-solid fa-arrow-right" style="font-size: 0.65rem; margin-left: 0.2rem;"></i>
                </a>
            </div>
            <div>
                @foreach($recentPurchases as $po)
                    <a href="{{ route('admin.purchases.show', $po) }}" class="sup-recent-row">
                        <div style="min-width: 0; padding-right: 1rem;">
                            <p style="font-weight: 700; color: var(--sup-dark); margin: 0; font-size: 0.88rem;">{{ $po->reference_no }}</p>
                            <p style="font-size: 0.72rem; color: var(--sup-faint); margin: 0.2rem 0 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $po->supplier?->name ?? 'Unknown supplier' }} · {{ $po->created_at->format('M d, Y') }}</p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 1rem; flex-shrink: 0;">
                            <div style="text-align: right;">
                                <p style="font-weight: 700; color: var(--sup-dark); margin: 0; font-size: 0.88rem;">₱{{ number_format($po->total_amount, 2) }}</p>
                                <span class="sup-status-pill {{ $po->status }}" style="margin-top: 0.2rem;">
                                    {{ $po->status }}
                                </span>
                            </div>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.72rem; color: var(--text-disabled);"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Delete Supplier Confirm Modal ── --}}
    <div x-show="deleteModal.open" x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);"
         @keydown.escape.window="deleteModal.open = false">
        <div class="bg-[var(--bg-surface)] rounded-2xl shadow-2xl w-full max-w-md" @click.outside="deleteModal.open = false">
            <div class="px-6 pt-6 pb-4 text-center">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-rose-100 mb-3">
                    <i class="fa-solid fa-trash-can text-rose-600 text-2xl"></i>
                </span>
                <h3 class="text-lg font-extrabold text-[var(--text-main)]">Remove Supplier?</h3>
                <p class="text-sm text-[var(--text-muted)] mt-1">
                    You are about to remove <span class="font-bold text-[var(--text-main)]" x-text="deleteModal.name"></span>.
                    Purchase history will be preserved.
                </p>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" class="sup-btn-outline" @click="deleteModal.open = false">Cancel</button>
                <button type="button"
                        class="sup-btn-primary"
                        style="background: var(--danger-color); box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);"
                        @click="$refs.deleteSupplierForm.submit()">
                    <i class="fa-solid fa-trash-can"></i> Yes, Remove
                </button>
            </div>
        </div>
    </div>
    <form x-ref="deleteSupplierForm" method="POST" x-bind:action="deleteModal.action" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    {{-- ── Add Supplier Modal ── --}}
    <div x-show="showAdd" x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(6px);"
         @keydown.escape.window="showAdd = false">
        <div class="bg-[var(--bg-surface)] rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" @click.outside="showAdd = false"
             style="max-height: 90vh; display: flex; flex-direction: column;">

            {{-- Modal Header --}}
            <div style="background: var(--bg-surface); padding: 1.25rem 1.5rem; flex-shrink: 0; border-bottom: 1px solid var(--border-color);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--accent-soft);">
                            <i class="fa-solid fa-truck-field text-sm" style="color: var(--text-accent);"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-extrabold leading-tight" style="color: var(--text-main); font-family: 'Outfit', sans-serif;">Add Supplier</h3>
                            <p class="text-xs" style="color: var(--text-muted);">Fill in the supplier's basic details</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="showAdd = false"
                            class="w-8 h-8 rounded-lg flex items-center justify-center border-0 cursor-pointer transition-all"
                            style="background: var(--bg-subtle); color: var(--text-muted);"
                            onmouseover="this.style.background='var(--bg-stripe)'; this.style.color='var(--text-main)'" onmouseout="this.style.background='var(--bg-subtle)'; this.style.color='var(--text-muted)'">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <form action="{{ route('admin.suppliers.store') }}" method="POST"
                  style="overflow-y: auto; flex: 1; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                @csrf

                {{-- Supplier Name --}}
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem; letter-spacing: 0.01em;">
                        Supplier Name <span style="color: var(--text-danger);">*</span>
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem;">
                            <i class="fa-solid fa-building"></i>
                        </span>
                        <input type="text" name="name" required value="{{ old('name') }}"
                               placeholder="e.g. Inkwell Graphics Supply"
                               style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                               onfocus="this.style.borderColor='#0099FF'; this.style.background='var(--bg-surface)';"
                               onblur="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-surface)';">
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
                            <input type="text" name="contact_person" value="{{ old('contact_person') }}"
                                   placeholder="Full name"
                                   style="width: 100%; padding: 0.65rem 0.75rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                                   onfocus="this.style.borderColor='#0099FF'; this.style.background='var(--bg-surface)';"
                                   onblur="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-surface)';">
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.4rem;">Phone</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.8rem;">
                                <i class="fa-solid fa-phone"></i>
                            </span>
                            <input type="tel" name="phone"
                                   value="{{ old('phone') ?: '09' }}"
                                   maxlength="11"
                                   placeholder="09XXXXXXXXX"
                                   inputmode="numeric"
                                   style="width: 100%; padding: 0.65rem 0.75rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box; letter-spacing: 0.03em;"
                                   onfocus="this.style.borderColor='#0099FF'; this.style.background='var(--bg-surface)';"
                                   onblur="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-surface)';"
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
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="supplier@email.com"
                               style="width: 100%; padding: 0.65rem 0.875rem 0.65rem 2.2rem; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-page); outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box;"
                               onfocus="this.style.borderColor='#0099FF'; this.style.background='var(--bg-surface)';"
                               onblur="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-surface)';">
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
                                  onfocus="this.style.borderColor='#0099FF'; this.style.background='var(--bg-surface)';"
                                  onblur="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-surface)';">{{ old('address') }}</textarea>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div style="display: flex; justify-content: flex-end; gap: 0.625rem; padding-top: 0.5rem; border-top: 1px solid var(--border-color); margin-top: 0.25rem;">
                    <button type="button" class="sup-btn-outline" @click="showAdd = false">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </button>
                    <button type="submit" class="sup-btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
