@extends('layouts.admin')

@section('title', 'Raw Materials & Ink Inventory Management')

@section('content')

<style>
    .inventory-row {
        transition: all 0.2s ease !important;
    }
    .inventory-row:hover {
        background-color: var(--accent-soft) !important;
        box-shadow: inset 0 0 0 2px var(--accent-color) !important;
        position: relative;
        z-index: 5;
    }
    .inventory-row:active {
        background-color: var(--accent-soft) !important;
        box-shadow: inset 0 0 0 3px var(--accent-color) !important;
    }
</style>

<!-- PAGE HEADER -->
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.25rem; display: flex; align-items: center; gap: 0.6rem;">
            <i class="fa-solid fa-boxes-stacked" style="color: var(--brand-cyan-fg);"></i> Materials Stock
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0;">Track raw materials and ink inventory, restock supplies, and watch low-stock levels.</p>
    </div>
</div>

<!-- SEARCH & FILTER BAR CARD -->
<div class="card" style="padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.06), 0 2px 4px -1px rgba(0, 0, 0, 0.04); border: 1px solid var(--border-color); border-bottom: 2px solid var(--cmyk-cyan); background: var(--bg-surface);">
    <form action="{{ route('admin.materials.index') }}" method="GET" style="display: flex; gap: 0.85rem; align-items: center; flex-wrap: wrap;" id="inventory-filter-form">

        {{-- Search Input + Button (fused pair) --}}
        <div style="flex: 2; min-width: 220px; display: flex; align-items: center;">
            <input type="text" name="search" id="inventory-search-input" value="{{ request('search') }}"
                placeholder="Search by material name..."
                style="flex: 1; padding: 0.65rem 1rem; border: 1px solid var(--border-color); border-right: none; border-radius: 10px 0 0 10px; font-size: 0.92rem; outline: none; background: var(--bg-surface); color: var(--text-main); transition: border-color 0.2s;"
                onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='';">
            <button type="submit"
                style="padding: 0.65rem 1.1rem; background: var(--accent-color); color: var(--text-inverse); border: 1px solid var(--accent-color); border-radius: 0 10px 10px 0; font-size: 0.88rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; transition: background 0.15s;"
                onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
        </div>

        {{-- Category Dropdown Filter --}}
        <div style="flex: 1; min-width: 160px;">
            <select name="category" id="inventory-category-select" class="custom-combo"
                style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.92rem; outline: none; background: var(--bg-surface); color: var(--text-main); cursor: pointer; transition: border-color 0.2s;"
                onchange="this.form.submit()" onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='';">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
        </div>

        {{-- Stock Status Dropdown Filter --}}
        <div style="flex: 1; min-width: 170px;">
            <select name="stock_status" id="inventory-stock-select" class="custom-combo"
                style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.92rem; outline: none; background: var(--bg-surface); color: var(--text-main); cursor: pointer; transition: border-color 0.2s;"
                onchange="this.form.submit()" onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='';">
                <option value="">All Stock Levels</option>
                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock (Orange)</option>
                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock (Red)</option>
                <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>Sufficient Stock Only</option>
            </select>
        </div>

        {{-- Reset button (only when filters active) --}}
        @if(request()->hasAny(['search', 'category', 'stock_status']))
            <a href="{{ route('admin.materials.index') }}"
                style="padding: 0.65rem 1.1rem; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.88rem; font-weight: 700; color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; transition: all 0.15s;"
                onmouseover="this.style.borderColor='var(--border-strong)'; this.style.color='var(--text-main)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-muted)';">
                <i class="fa-solid fa-rotate-left" style="color: var(--text-muted);"></i> Reset
            </a>
        @endif

    </form>
</div>

@php
    // Query string builders shared by sort links and category chips.
    $withSort = function ($col) use ($sort, $direction) {
        $newDir = ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
        return array_merge(request()->query(), ['sort' => $col, 'direction' => $newDir]);
    };
    $sortIcon = function ($col) use ($sort, $direction) {
        if ($sort !== $col) return '<i class="fa-solid fa-sort" style="color:var(--text-subtle); font-size:0.7rem;"></i>';
        return $direction === 'asc'
            ? '<i class="fa-solid fa-arrow-up-wide-short" style="color:var(--text-accent); font-size:0.75rem;"></i>'
            : '<i class="fa-solid fa-arrow-down-wide-short" style="color:var(--text-accent); font-size:0.75rem;"></i>';
    };
@endphp

<!-- METRIC CARDS -->
@php
    $cardStyles = 'flex: 1; min-width: 150px; padding: 1rem 1.25rem; border-radius: 14px; border: 1px solid var(--border-color); background: var(--bg-surface); box-shadow: 0 4px 14px rgba(0,0,0,0.04);';
@endphp
<div style="display: flex; gap: 0.85rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
    <div style="{{ $cardStyles }}">
        <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--text-muted);">Total Materials</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); font-family: 'Outfit', sans-serif;">{{ $metrics['total'] }}</div>
    </div>
    <div style="{{ $cardStyles }} {{ $metrics['low_stock'] > 0 ? 'border-color:var(--warning-border); background:var(--warning-soft);' : '' }}">
        <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: {{ $metrics['low_stock'] > 0 ? 'var(--text-warning)' : 'var(--text-muted)' }};">Low Stock</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: {{ $metrics['low_stock'] > 0 ? 'var(--text-warning)' : 'var(--text-success)' }}; font-family: 'Outfit', sans-serif;">{{ $metrics['low_stock'] }}</div>
    </div>
    <div style="{{ $cardStyles }} {{ $metrics['out_of_stock'] > 0 ? 'border-color:var(--danger-border); background:var(--danger-soft);' : '' }}">
        <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: {{ $metrics['out_of_stock'] > 0 ? 'var(--text-danger)' : 'var(--text-muted)' }};">Out of Stock</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: {{ $metrics['out_of_stock'] > 0 ? 'var(--text-danger)' : 'var(--text-main)' }}; font-family: 'Outfit', sans-serif;">{{ $metrics['out_of_stock'] }}</div>
    </div>
</div>

<!-- INVENTORY RAW MATERIALS TABLE -->
<div class="card" style="padding: 1.5rem; margin-bottom: 2rem; border-radius: 16px; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04); border: 1px solid var(--border-color);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.85rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 0.6rem;">
                <i class="fa-solid fa-boxes-stacked" style="color: var(--text-accent);"></i> All Materials
            </h3>

        </div>
        <button type="button" onclick="document.getElementById('add-material-modal').style.display='flex';" class="btn" style="background: var(--accent-color); color: var(--text-inverse); padding: 0.65rem 1.25rem; font-size: 0.92rem; border-radius: 12px; font-weight: 700; border: none; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
            <i class="fa-solid fa-plus-circle"></i> Add New Material
        </button>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.9rem;">
            <thead>
                <tr style="background: var(--bg-page); text-align: left; color: var(--text-muted);">
                    <th style="padding: 0.85rem 1.25rem; border-bottom: 2px solid var(--border-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; border-top-left-radius: 10px; white-space: nowrap;">
                        <a href="{{ route('admin.materials.index', $withSort('name')) }}" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">Material Name {!! $sortIcon('name') !!}</a>
                    </th>
                    <th style="padding: 0.85rem 1.25rem; border-bottom: 2px solid var(--border-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; white-space: nowrap;">
                        <a href="{{ route('admin.materials.index', $withSort('category')) }}" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">Category {!! $sortIcon('category') !!}</a>
                    </th>
                    <th style="padding: 0.85rem 1.25rem; border-bottom: 2px solid var(--border-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; white-space: nowrap;">
                        <a href="{{ route('admin.materials.index', $withSort('available_stock')) }}" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" title="Total physical stock on hand; the line below shows what is still available after reserved units">Total Stock {!! $sortIcon('available_stock') !!}</a>
                    </th>
                    <th style="padding: 0.85rem 1.25rem; border-bottom: 2px solid var(--border-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; white-space: nowrap;">
                        <a href="{{ route('admin.materials.index', $withSort('low_stock_threshold')) }}" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">Low Stock Threshold {!! $sortIcon('low_stock_threshold') !!}</a>
                    </th>
                    <th style="padding: 0.85rem 1.25rem; border-bottom: 2px solid var(--border-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; text-align: right; border-top-right-radius: 10px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($materials as $mat)
                    @php
                        $stockQty = (float) $mat->current_stock;
                        $threshold = (float) $mat->low_stock_threshold;
                        $reserved = (float) $mat->reserved_stock;
                        // Sellable stock = physical stock minus reserved units.
                        // Reserved units are already spoken for by pending orders,
                        // so the badge/bar must follow what can still be sold.
                        $available = max(0, (float) $mat->available_stock);
                        $isOut = $available <= 0;
                        $isLow = !$isOut && $available <= $threshold;
                        $barPct = $threshold > 0 ? min(100, round(($available / $threshold) * 100)) : 100;
                        $rowBg = $isOut ? 'var(--danger-soft)' : ($isLow ? 'var(--warning-soft)' : 'var(--bg-surface)');
                    @endphp
                    <tr id="material-row-{{ $mat->id }}" class="inventory-row" style="border-bottom: 1px solid var(--border-color); background: {{ $rowBg }};">
                        <td style="padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                <span style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">{{ $mat->name }}</span>
                                <div id="badge-cell-{{ $mat->id }}">
                                    @if($isOut && $stockQty > 0)
                                        <span id="badge-reserved-{{ $mat->id }}" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.55rem; background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.02em;"
                                              title="All {{ $stockQty }} {{ $mat->unit }} on the shelf are reserved by pending orders — nothing left to sell.">
                                            <i class="fa-solid fa-lock" style="font-size: 0.68rem;"></i> All Reserved
                                        </span>
                                    @elseif($isOut)
                                        <span id="badge-out-{{ $mat->id }}" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.55rem; background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.02em;">
                                            <i class="fa-solid fa-triangle-exclamation" style="font-size: 0.68rem;"></i> Out of Stock
                                        </span>
                                    @elseif($isLow)
                                        <span id="badge-low-{{ $mat->id }}" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.55rem; background: var(--warning-soft); color: var(--text-warning); border: 1px solid var(--warning-border); border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.02em;">
                                            <i class="fa-solid fa-triangle-exclamation" style="font-size: 0.68rem;"></i> Low Stock
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid var(--border-color);">
                            <span style="display: inline-block; padding: 0.25rem 0.65rem; background: var(--bg-subtle); color: var(--text-muted); border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-transform: capitalize;">
                                {{ $mat->category }}
                            </span>
                        </td>
                        <td style="padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid var(--border-color);">
                            <div style="font-weight: 800; font-size: 1.1rem; color: {{ $isOut ? 'var(--text-danger)' : ($isLow ? 'var(--text-warning)' : 'var(--text-main)') }}; font-family: 'Outfit', sans-serif;" id="stock-display-{{ $mat->id }}">
                                {{ $stockQty }} <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-muted);">{{ $mat->unit }}</span>
                            </div>
                            <div id="reserved-display-{{ $mat->id }}" style="{{ $reserved > 0 ? '' : 'display:none;' }} font-size: 0.75rem; font-weight: 600; color: var(--text-warning); margin-top: 0.15rem;"
                                 data-unit="{{ $mat->unit }}">
                                <i class="fa-solid fa-lock" style="font-size: 0.68rem;"></i>
                                {{ $reserved }} {{ $mat->unit }} reserved · <span style="color: var(--text-accent);">{{ $available }} available</span>
                            </div>
                            <div id="available-display-{{ $mat->id }}" style="{{ $reserved <= 0 ? '' : 'display:none;' }} font-size: 0.72rem; font-weight: 600; color: var(--text-accent); margin-top: 0.15rem;">
                                {{ $available }} {{ $mat->unit }} available
                            </div>
                            <div style="margin-top: 0.4rem; max-width: 150px; height: 5px; background: var(--text-disabled); border-radius: 9999px; overflow: hidden;">
                                <div id="stock-bar-{{ $mat->id }}" style="width: {{ $barPct }}%; height: 100%; border-radius: 9999px; background: {{ $isOut ? 'var(--danger-color)' : ($isLow ? 'var(--warning-color)' : 'var(--success-color)') }}; transition: width 0.4s, background 0.4s;"></div>
                            </div>
                            @if(!empty($mat->estimated_prints))
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.3rem;"
                                     title="@foreach($mat->estimated_prints as $est){{ $est['product_name'] }}: ~{{ $est['prints_remaining'] }} prints ({{ $est['avg_ml_per_print'] }} ml/print)\n @endforeach">
                                    <i class="fa-solid fa-print" style="font-size: 0.68rem;"></i>
                                    ~{{ $mat->estimated_prints[0]['prints_remaining'] }} prints
                                    @if(count($mat->estimated_prints) > 1)<span style="color:var(--text-subtle);">({{ $mat->estimated_prints[0]['product_name'] }} · hover for all)</span>@endif
                                </div>
                            @endif
                        </td>
                        <td style="padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-weight: 500; font-size: 0.88rem;">
                            {{ (float)$mat->low_stock_threshold }} <span style="font-size: 0.8rem; color: var(--text-subtle);">{{ $mat->unit }}</span>
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: right; vertical-align: middle; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; white-space: nowrap;">
                                <!-- RESTOCK INLINE INPUT GROUP -->
                                <form class="restock-form" data-material-id="{{ $mat->id }}"
                                    data-url="{{ route('admin.materials.restock', $mat->id) }}"
                                    style="display: flex; align-items: center; gap: 0;"
                                    onsubmit="return false;">
                                    @csrf
                                    <input type="number" step="0.01" name="restock_quantity" placeholder="Qty" required
                                        style="width: 65px; padding: 0.4rem 0.5rem; border: 1px solid var(--border-color); border-right: none; border-radius: 6px 0 0 6px; font-size: 0.85rem; outline: none; background: var(--bg-surface);"
                                        class="restock-qty-input">
                                    <button type="button" class="btn btn-primary restock-btn"
                                        style="padding: 0.4rem 0.75rem; font-size: 0.8rem; border-radius: 0 6px 6px 0; font-weight: 700;"
                                        title="Restock Material"
                                        onclick="submitRestock(this)">
                                        <i class="fa-solid fa-plus"></i> Restock
                                    </button>
                                </form>

                                <!-- EDIT BUTTON -->
                                <button type="button" onclick="document.getElementById('edit-material-modal-{{ $mat->id }}').style.display='flex';" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; border-radius: 6px; color: var(--text-muted); border: 1px solid var(--border-color); font-weight: 600;" title="Edit Material">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                            </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Stock Movements now lives in its own module: route('admin.materials.movements') -->

@endsection

@push('modals')
<!-- ADD NEW MATERIAL MODAL -->
<div id="add-material-modal" onclick="if(event.target === this) this.style.display='none';" style="display: none; position: fixed; inset: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.7); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); padding: 1.25rem; box-sizing: border-box;">
    <div style="background: var(--bg-surface); border-radius: 16px; max-width: 580px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; text-align: left; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid var(--border-color); overflow: hidden; position: relative; z-index: 100000;">
        <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
            <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: 'Outfit', sans-serif;">
                <i class="fa-solid fa-plus-circle" style="color: var(--accent-color);"></i> Add New Raw Material / Stock Item
            </h4>
            <button type="button" onclick="document.getElementById('add-material-modal').style.display='none';" style="background: none; border: none; font-size: 1.35rem; cursor: pointer; color: var(--text-muted); line-height: 1; padding: 0.2rem;">&times;</button>
        </div>

        <form action="{{ route('admin.materials.store') }}" method="POST" style="padding: 1.5rem 1.75rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1.15rem; margin: 0;">
            @csrf

            {{-- Row 1: Material Name (Full width) --}}
            <div>
                <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.35rem;">Material Name *</label>
                <input type="text" name="name" required placeholder="e.g. Sublimation Ink Black" style="width: 100%; padding: 0.65rem 0.95rem; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.92rem; box-sizing: border-box;">
            </div>

            {{-- Row 2: Category & Unit (Side-by-side / 2-column grid) --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.45rem;">Category *</label>
                    <input type="hidden" name="category" id="category-hidden-add" required>

                    {{-- Combobox Trigger --}}
                    <div style="position: relative;" id="category-combobox-add">
                        <button type="button" id="category-trigger-add" onclick="toggleCategoryCombo('add')"
                            style="width: 100%; height: 44px; min-height: 44px; max-height: 44px; padding: 0 1rem; border: 1.5px solid var(--border-color); border-radius: 10px; background: var(--bg-surface); display: flex; align-items: center; justify-content: space-between; cursor: pointer; font-size: 0.92rem; font-family: inherit; color: var(--text-subtle); transition: border-color 0.15s; box-sizing: border-box;">
                            <span id="category-trigger-label-add" style="display: flex; align-items: center; gap: 0.5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0;">
                                <span style="font-size: 1rem; flex-shrink: 0;">🏷️</span>
                                <span style="color: var(--text-subtle); font-style: italic; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Select a category…</span>
                            </span>
                            <svg id="category-caret-add" style="width:16px;height:16px;color:var(--text-subtle);transition:transform 0.2s;flex-shrink:0;margin-left:0.5rem;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        {{-- Dropdown Panel --}}
                        <div id="category-dropdown-add"
                            style="display:none; position: absolute; top: calc(100% + 6px); left: 0; width: 100%; background: var(--bg-surface); border: 1.5px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.12); z-index: 9999; overflow: hidden;">
                            {{-- Search --}}
                            <div style="padding: 0.6rem 0.75rem; border-bottom: 1px solid var(--border-color);">
                                <div style="position: relative;">
                                    <svg style="position:absolute;left:0.6rem;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-subtle);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                                    <input type="text" id="category-search-add" placeholder="Search category…" oninput="filterCategoryCombo('add')"
                                        style="width:100%; padding: 0.45rem 0.75rem 0.45rem 2rem; border: 1px solid var(--border-color); border-radius: 7px; font-size: 0.82rem; font-family: inherit; color: var(--text-muted); background: var(--bg-page); box-sizing: border-box; outline: none;">
                                </div>
                            </div>
                            {{-- Options List --}}
                            <ul id="category-options-add" style="list-style:none; margin:0; padding: 0.4rem; max-height: 220px; overflow-y: auto;">
                                @forelse($categories as $cat)
                                <li data-value="{{ $cat }}" data-label="🏷️ {{ $cat }}" data-search="{{ strtolower($cat) }}"
                                    onclick="selectCategoryCombo('add','{{ addslashes($cat) }}','🏷️ {{ addslashes($cat) }}')"
                                    style="display:flex; align-items:center; gap:0.6rem; padding: 0.5rem 0.65rem; border-radius: 8px; cursor: pointer; transition: background 0.12s;" class="cat-opt-add"
                                    onmouseover="this.style.background='#F0F9FF'" onmouseout="this.style.background='transparent'">
                                    <span style="font-size:1.1rem; width:1.4rem; text-align:center; flex-shrink:0;">🏷️</span>
                                    <span style="flex:1;">
                                        <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">{{ $cat }}</span>
                                    </span>
                                </li>
                                @empty
                                <li style="padding: 0.75rem; text-align:center; font-size:0.82rem; color:var(--text-subtle);">No categories yet</li>
                                @endforelse
                                {{-- Divider + Custom --}}
                                <li style="border-top: 1px solid var(--border-color); margin: 0.3rem 0; padding: 0;"></li>
                                <li data-value="other" data-label="✏️ Custom Category" data-search="custom other"
                                    onclick="selectCategoryCombo('add','other','✏️ Custom Category')"
                                    style="display:flex; align-items:center; gap:0.6rem; padding: 0.5rem 0.65rem; border-radius: 8px; cursor: pointer; transition: background 0.12s;" class="cat-opt-add"
                                    onmouseover="this.style.background='var(--warning-soft)'" onmouseout="this.style.background='transparent'">
                                    <span style="font-size:1.1rem; width:1.4rem; text-align:center; flex-shrink:0;">✏️</span>
                                    <span>
                                        <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-warning);">Custom Category</span>
                                        <span id="category-custom-desc-add" style="font-size: 0.75rem; color: var(--text-subtle); margin-left: 0.3rem;">Type your own</span>
                                    </span>
                                </li>
                                <li id="category-no-results-add" style="display:none; padding: 0.75rem; text-align:center; font-size:0.82rem; color:var(--text-subtle);">No match found</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Custom category input (shown when "other" selected) --}}
                    <div id="custom-category-container-add" style="display: none; margin-top: 0.55rem; background: var(--warning-soft); padding: 0.75rem; border-radius: 10px; border-left: 3px solid var(--warning-color);">
                        <label style="font-size: 0.78rem; font-weight: 700; color: var(--text-warning); display: block; margin-bottom: 0.3rem;">
                            <i class="fa-solid fa-pen"></i> Specify Custom Category *
                        </label>
                        <input type="text" name="custom_category" id="custom-category-input-add" placeholder="e.g. Stickers, Vinyl, Apparel, Packaging" style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid var(--warning-border); border-radius: 6px; font-size: 0.88rem; background: var(--bg-surface); box-sizing: border-box;">
                    </div>
                </div>

                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.45rem;">Unit *</label>
                    <input type="hidden" name="unit" id="unit-hidden-add" required>
                    @php
                        $knownUnitOptions = ['ml', 'pcs', 'sheets', 'rolls', 'kg', 'liters', 'meters', 'packs', 'boxes', 'sets'];
                        $extraUnits = $existingUnits->reject(fn ($u) => in_array(strtolower((string) $u), $knownUnitOptions, true) || strtolower((string) $u) === 'other')->values();
                    @endphp

                    {{-- Combobox Trigger --}}
                    <div style="position: relative;" id="unit-combobox-add">
                        <button type="button" id="unit-trigger-add" onclick="toggleUnitCombo('add')"
                            style="width: 100%; height: 44px; min-height: 44px; max-height: 44px; padding: 0 1rem; border: 1.5px solid var(--border-color); border-radius: 10px; background: var(--bg-surface); display: flex; align-items: center; justify-content: space-between; cursor: pointer; font-size: 0.92rem; font-family: inherit; color: var(--text-subtle); transition: border-color 0.15s; box-sizing: border-box;">
                            <span id="unit-trigger-label-add" style="display: flex; align-items: center; gap: 0.5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0;">
                                <span style="font-size: 1rem; flex-shrink: 0;">⚡</span>
                                <span style="color: var(--text-subtle); font-style: italic; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Select a unit…</span>
                            </span>
                            <svg id="unit-caret-add" style="width:16px;height:16px;color:var(--text-subtle);transition:transform 0.2s;flex-shrink:0;margin-left:0.5rem;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        {{-- Dropdown Panel --}}
                        <div id="unit-dropdown-add"
                            style="display:none; position: absolute; top: calc(100% + 6px); left: 0; width: 100%; background: var(--bg-surface); border: 1.5px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.12); z-index: 9999; overflow: hidden;">
                            {{-- Search --}}
                            <div style="padding: 0.6rem 0.75rem; border-bottom: 1px solid var(--border-color);">
                                <div style="position: relative;">
                                    <svg style="position:absolute;left:0.6rem;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-subtle);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                                    <input type="text" id="unit-search-add" placeholder="Search unit…" oninput="filterUnitCombo('add')"
                                        style="width:100%; padding: 0.45rem 0.75rem 0.45rem 2rem; border: 1px solid var(--border-color); border-radius: 7px; font-size: 0.82rem; font-family: inherit; color: var(--text-muted); background: var(--bg-page); box-sizing: border-box; outline: none;">
                                </div>
                            </div>
                            {{-- Options List --}}
                            <ul id="unit-options-add" style="list-style:none; margin:0; padding: 0.4rem; max-height: 220px; overflow-y: auto;">
                                @foreach([['ml','ml','💧','Milliliters'],['pcs','pcs','📦','Pieces'],['sheets','sheets','📄','Sheets'],['rolls','rolls','🧻','Rolls'],['kg','kg','⚖️','Kilograms'],['liters','liters','🫙','Liters'],['meters','meters','📏','Meters'],['packs','packs','🗂️','Packs'],['boxes','boxes','📫','Boxes'],['sets','sets','🎁','Sets']] as [$val,$label,$icon,$desc])
                                <li data-value="{{ $val }}" data-label="{{ $icon }} {{ $label }}" data-search="{{ strtolower($label . ' ' . $desc) }}"
                                    onclick="selectUnitCombo('add','{{ $val }}','{{ $icon }} {{ $label }}')"
                                    style="display:flex; align-items:center; gap:0.6rem; padding: 0.5rem 0.65rem; border-radius: 8px; cursor: pointer; transition: background 0.12s;" class="unit-opt-add"
                                    onmouseover="this.style.background='#F0F9FF'" onmouseout="this.style.background='transparent'">
                                    <span style="font-size:1.1rem; width:1.4rem; text-align:center; flex-shrink:0;">{{ $icon }}</span>
                                    <span>
                                        <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">{{ $label }}</span>
                                        <span style="font-size: 0.75rem; color: var(--text-subtle); margin-left: 0.3rem;">{{ $desc }}</span>
                                    </span>
                                </li>
                                @endforeach
                                @foreach($extraUnits as $u)
                                <li data-value="{{ $u }}" data-label="🏷️ {{ $u }}" data-search="{{ strtolower($u) }}"
                                    onclick="selectUnitCombo('add','{{ $u }}','🏷️ {{ $u }}')"
                                    style="display:flex; align-items:center; gap:0.6rem; padding: 0.5rem 0.65rem; border-radius: 8px; cursor: pointer; transition: background 0.12s;" class="unit-opt-add"
                                    onmouseover="this.style.background='#F0F9FF'" onmouseout="this.style.background='transparent'">
                                    <span style="font-size:1.1rem; width:1.4rem; text-align:center; flex-shrink:0;">🏷️</span>
                                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">{{ $u }}</span>
                                </li>
                                @endforeach
                                {{-- Divider + Custom --}}
                                <li style="border-top: 1px solid var(--border-color); margin: 0.3rem 0; padding: 0;"></li>
                                <li data-value="other" data-label="✏️ Custom Unit" data-search="custom other"
                                    onclick="selectUnitCombo('add','other','✏️ Custom Unit')"
                                    style="display:flex; align-items:center; gap:0.6rem; padding: 0.5rem 0.65rem; border-radius: 8px; cursor: pointer; transition: background 0.12s;" class="unit-opt-add"
                                    onmouseover="this.style.background='var(--warning-soft)'" onmouseout="this.style.background='transparent'">
                                    <span style="font-size:1.1rem; width:1.4rem; text-align:center; flex-shrink:0;">✏️</span>
                                    <span>
                                        <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-warning);">Custom Unit</span>
                                        <span style="font-size: 0.75rem; color: var(--text-subtle); margin-left: 0.3rem;">Type your own</span>
                                    </span>
                                </li>
                                <li id="unit-no-results-add" style="display:none; padding: 0.75rem; text-align:center; font-size:0.82rem; color:var(--text-subtle);">No match found</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Custom unit input (shown when "other" selected) --}}
                    <div id="custom-unit-container-add" style="display: none; margin-top: 0.55rem; background: var(--warning-soft); padding: 0.75rem; border-radius: 10px; border-left: 3px solid var(--warning-color);">
                        <label style="font-size: 0.78rem; font-weight: 700; color: var(--text-warning); display: block; margin-bottom: 0.3rem;">
                            <i class="fa-solid fa-pen"></i> Specify Custom Unit *
                        </label>
                        <input type="text" name="custom_unit" id="custom-unit-input-add" placeholder="e.g. bottles, reams, cartridges, tubes" style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid var(--warning-border); border-radius: 6px; font-size: 0.88rem; background: var(--bg-surface); box-sizing: border-box;">
                    </div>
                </div>
            </div>

            {{-- Row 3: Current Stock Quantity & Low Stock Threshold (Side-by-side / 2-column grid) --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.35rem;">Initial Stock *</label>
                    <input type="number" step="0.01" name="current_stock" required placeholder="e.g. 500" style="width: 100%; padding: 0.65rem 0.95rem; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.92rem; box-sizing: border-box;">
                    <small style="color: var(--text-subtle); font-size: 0.7rem; margin-top: 0.25rem; display: block;">Physical count only — pending-order reservations are tracked separately.</small>
                </div>

                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.35rem;">Low Stock Threshold *</label>
                    <input type="number" step="0.01" name="low_stock_threshold" required placeholder="e.g. 50" style="width: 100%; padding: 0.65rem 0.95rem; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.92rem; box-sizing: border-box;">
                </div>
            </div>

            {{-- Modal Footer Actions --}}
            <div style="display: flex; justify-content: flex-end; gap: 0.65rem; margin-top: 0.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <button type="button" onclick="document.getElementById('add-material-modal').style.display='none';" class="btn btn-outline" style="padding: 0.65rem 1.25rem; font-weight: 600;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.35rem; font-weight: 700;">
                    <i class="fa-solid fa-plus"></i> Add Material to Stock
                </button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT & DELETE MODALS FOR EACH MATERIAL --}}
@foreach($materials as $mat)
    <!-- DELETE CONFIRMATION MODAL -->
    <div id="delete-material-modal-{{ $mat->id }}" onclick="if(event.target === this) this.style.display='none';" style="display: none; position: fixed; inset: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.7); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); padding: 1.25rem; box-sizing: border-box;">
        <div style="background: var(--bg-surface); border-radius: 16px; max-width: 440px; width: 90%; padding: 2rem 1.75rem; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid var(--border-color); position: relative; z-index: 100000;">
            <!-- Warning Icon -->
            <div style="width: 56px; height: 56px; background: var(--danger-soft); color: var(--text-danger); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1.25rem auto; border: 2px solid var(--danger-border);">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">
                Delete Material Stock?
            </h3>
            
            <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.75rem;">
                Are you sure you want to delete <strong style="color: var(--text-main);">"{{ $mat->name }}"</strong>? This action will permanently remove it from inventory stock and BOM tracking.
            </p>

            <div style="display: flex; gap: 0.75rem; justify-content: center;">
                <button type="button" onclick="document.getElementById('delete-material-modal-{{ $mat->id }}').style.display='none'; document.getElementById('edit-material-modal-{{ $mat->id }}').style.display='flex';" class="btn btn-outline" style="flex: 1; padding: 0.65rem 1rem; border-radius: 10px; font-weight: 600; color: var(--text-muted); border: 1px solid var(--border-color);">
                    Cancel
                </button>
                <form action="{{ route('admin.materials.destroy', $mat->id) }}" method="POST" style="flex: 1; margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn" style="width: 100%; padding: 0.65rem 1rem; border-radius: 10px; font-weight: 700; background: var(--danger-color); color: var(--text-inverse); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                        <i class="fa-solid fa-trash-can"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT MATERIAL MODAL -->
    <div id="edit-material-modal-{{ $mat->id }}" onclick="if(event.target === this) this.style.display='none';" style="display: none; position: fixed; inset: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.7); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); padding: 1.25rem; box-sizing: border-box;">
        <div style="background: var(--bg-surface); border-radius: 16px; max-width: 580px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; text-align: left; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid var(--border-color); overflow: hidden; position: relative; z-index: 100000;">
            <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: 'Outfit', sans-serif;">
                    <i class="fa-solid fa-pen-to-square" style="color: var(--accent-color);"></i> Edit Material: {{ $mat->name }}
                </h4>
                <button type="button" onclick="document.getElementById('edit-material-modal-{{ $mat->id }}').style.display='none';" style="background: none; border: none; font-size: 1.35rem; cursor: pointer; color: var(--text-muted); line-height: 1; padding: 0.2rem;">&times;</button>
            </div>

            <form action="{{ route('admin.materials.update', $mat->id) }}" method="POST" style="padding: 1.5rem 1.75rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1.15rem; margin: 0;">
                @csrf
                @method('PUT')

                {{-- Row 1: Material Name (Full width) --}}
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.35rem;">Material Name *</label>
                    <input type="text" name="name" value="{{ $mat->name }}" required style="width: 100%; padding: 0.65rem 0.95rem; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.92rem; box-sizing: border-box;">
                </div>

                {{-- Row 2: Category & Unit (Side-by-side / 2-column grid) --}}
                @php
                    $knownUnits = ['ml', 'pcs', 'sheets', 'rolls', 'kg', 'liters', 'meters', 'packs', 'boxes', 'sets'];
                    $extraUnitsEdit = $existingUnits->reject(fn ($u) => in_array(strtolower((string) $u), $knownUnits, true) || strtolower((string) $u) === 'other')->values();
                    $isCustomUnit = !in_array($mat->unit, $knownUnits, true) && !$extraUnitsEdit->contains(fn ($u) => strtolower((string) $u) === strtolower((string) $mat->unit));
                    $isCustomCategory = !in_array($mat->category, $categories->toArray(), true);
                @endphp
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.45rem;">Category *</label>
                        <input type="hidden" name="category" id="category-hidden-edit-{{ $mat->id }}" required value="{{ $isCustomCategory ? 'other' : $mat->category }}">

                        {{-- Combobox Trigger --}}
                        <div style="position:relative;" id="category-combobox-edit-{{ $mat->id }}">
                            <button type="button" id="category-trigger-edit-{{ $mat->id }}" onclick="toggleCategoryCombo('edit-{{ $mat->id }}')"
                                style="width: 100%; height: 44px; min-height: 44px; max-height: 44px; padding: 0 1rem; border: 1.5px solid var(--border-color); border-radius: 10px; background: var(--bg-surface); display: flex; align-items: center; justify-content: space-between; cursor: pointer; font-size: 0.92rem; font-family: inherit; color: var(--text-main); transition: border-color 0.15s; box-sizing: border-box;">
                                <span id="category-trigger-label-edit-{{ $mat->id }}" style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0;">
                                    <span style="font-size: 1rem; flex-shrink: 0;">🏷️</span>
                                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $mat->category }}</span>
                                </span>
                                <svg id="category-caret-edit-{{ $mat->id }}" style="width:16px;height:16px;color:var(--text-subtle);transition:transform 0.2s;flex-shrink:0;margin-left:0.5rem;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            {{-- Dropdown Panel --}}
                            <div id="category-dropdown-edit-{{ $mat->id }}"
                                style="display:none; position:absolute; top:calc(100% + 6px); left:0; width:100%; background:var(--bg-surface); border:1.5px solid var(--border-color); border-radius:12px; box-shadow:0 10px 30px -5px rgba(0,0,0,0.12); z-index:9999; overflow:hidden;">
                                {{-- Search --}}
                                <div style="padding:0.6rem 0.75rem; border-bottom:1px solid var(--border-color);">
                                    <div style="position:relative;">
                                        <svg style="position:absolute;left:0.6rem;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-subtle);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                                        <input type="text" id="category-search-edit-{{ $mat->id }}" placeholder="Search category…"
                                            oninput="filterCategoryCombo('edit-{{ $mat->id }}')"
                                            style="width:100%; padding:0.45rem 0.75rem 0.45rem 2rem; border:1px solid var(--border-color); border-radius:7px; font-size:0.82rem; font-family:inherit; color:var(--text-muted); background:var(--bg-page); box-sizing:border-box; outline:none;">
                                    </div>
                                </div>

                                {{-- Options List --}}
                                <ul id="category-options-edit-{{ $mat->id }}" style="list-style:none; margin:0; padding:0.4rem; max-height:200px; overflow-y:auto;">
                                    @forelse($categories as $cat)
                                    @php $isSelected = ($mat->category === $cat && !$isCustomCategory); @endphp
                                    <li data-value="{{ $cat }}" data-label="🏷️ {{ $cat }}" data-search="{{ strtolower($cat) }}"
                                        onclick="selectCategoryCombo('edit-{{ $mat->id }}','{{ addslashes($cat) }}','🏷️ {{ addslashes($cat) }}')"
                                        style="display:flex; align-items:center; gap:0.6rem; padding:0.5rem 0.65rem; border-radius:8px; cursor:pointer; background:{{ $isSelected ? 'var(--accent-soft)' : 'transparent' }};" class="cat-opt-edit-{{ $mat->id }}"
                                        onmouseover="this.style.background='#F0F9FF'" onmouseout="this.style.background='{{ $isSelected ? '#EFF6FF' : 'transparent' }}'">
                                        <span style="font-size:1.1rem; width:1.4rem; text-align:center; flex-shrink:0;">🏷️</span>
                                        <span style="flex:1;">
                                            <span style="font-size:0.88rem; font-weight:600; color:{{ $isSelected ? 'var(--text-accent)' : 'var(--text-main)' }};">{{ $cat }}</span>
                                        </span>
                                        @if($isSelected)
                                        <svg style="margin-left:auto;width:14px;height:14px;color:var(--text-accent);flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                    </li>
                                    @empty
                                    <li style="padding:0.75rem; text-align:center; font-size:0.82rem; color:var(--text-subtle);">No categories yet</li>
                                    @endforelse

                                    {{-- Divider + Custom --}}
                                    <li style="border-top:1px solid var(--border-color); margin:0.3rem 0; padding:0;"></li>
                                    <li data-value="other" data-label="✏️ Custom Category" data-search="custom other"
                                        onclick="selectCategoryCombo('edit-{{ $mat->id }}','other','✏️ Custom Category')"
                                        style="display:flex; align-items:center; gap:0.6rem; padding:0.5rem 0.65rem; border-radius:8px; cursor:pointer; background:{{ $isCustomCategory ? 'var(--warning-soft)' : 'transparent' }};" class="cat-opt-edit-{{ $mat->id }}"
                                        onmouseover="this.style.background='var(--warning-soft)'" onmouseout="this.style.background='{{ $isCustomCategory ? 'var(--warning-soft)' : 'transparent' }}'">
                                        <span style="font-size:1.1rem; width:1.4rem; text-align:center; flex-shrink:0;">✏️</span>
                                        <span>
                                            <span style="font-size:0.88rem; font-weight:600; color:var(--text-warning);">Custom Category</span>
                                            <span id="category-custom-desc-edit-{{ $mat->id }}" style="font-size:0.75rem; color:var(--text-subtle); margin-left:0.3rem;">Type your own</span>
                                        </span>
                                        @if($isCustomCategory)
                                        <svg style="margin-left:auto;width:14px;height:14px;color:var(--text-warning);flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                    </li>
                                    <li id="category-no-results-edit-{{ $mat->id }}" style="display:none; padding:0.75rem; text-align:center; font-size:0.82rem; color:var(--text-subtle);">No match found</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Custom category input (shown when "other" selected) --}}
                        <div id="custom-category-container-edit-{{ $mat->id }}" style="display: {{ $isCustomCategory ? 'block' : 'none' }}; margin-top: 0.55rem; background: var(--warning-soft); padding: 0.75rem; border-radius: 10px; border-left: 3px solid var(--warning-color);">
                            <label style="font-size: 0.78rem; font-weight: 700; color: var(--text-warning); display: block; margin-bottom: 0.3rem;">
                                <i class="fa-solid fa-pen"></i> Specify Custom Category *
                            </label>
                            <input type="text" name="custom_category" id="custom-category-input-edit-{{ $mat->id }}" value="{{ $isCustomCategory ? $mat->category : '' }}" placeholder="e.g. Stickers, Vinyl, Apparel, Packaging" style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid var(--warning-border); border-radius: 6px; font-size: 0.88rem; background: var(--bg-surface); box-sizing: border-box;">
                        </div>
                    </div>

                    <div>
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.45rem;">Unit *</label>
                        <input type="hidden" name="unit" id="unit-hidden-edit-{{ $mat->id }}" required value="{{ $isCustomUnit ? 'other' : $mat->unit }}">

                        {{-- Combobox Trigger --}}
                        <div style="position: relative;" id="unit-combobox-edit-{{ $mat->id }}">
                            @php
                                $currentLabel = $isCustomUnit ? '✏️ Custom Unit' : match($mat->unit) {
                                    'ml' => '💧 ml', 'pcs' => '📦 pcs', 'sheets' => '📄 sheets',
                                    'rolls' => '🧻 rolls', 'kg' => '⚖️ kg', 'liters' => '🫙 liters',
                                    'meters' => '📏 meters', 'packs' => '🗂️ packs', 'boxes' => '📫 boxes',
                                    'sets' => '🎁 sets', default => '🏷️ '.$mat->unit
                                };
                            @endphp
                            <button type="button" id="unit-trigger-edit-{{ $mat->id }}" onclick="toggleUnitCombo('edit-{{ $mat->id }}')"
                                style="width: 100%; height: 44px; min-height: 44px; max-height: 44px; padding: 0 1rem; border: 1.5px solid var(--border-color); border-radius: 10px; background: var(--bg-surface); display: flex; align-items: center; justify-content: space-between; cursor: pointer; font-size: 0.92rem; font-family: inherit; color: var(--text-main); transition: border-color 0.15s; box-sizing: border-box;">
                                <span id="unit-trigger-label-edit-{{ $mat->id }}" style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0;">
                                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $currentLabel }}</span>
                                </span>
                                <svg id="unit-caret-edit-{{ $mat->id }}" style="width:16px;height:16px;color:var(--text-subtle);transition:transform 0.2s;flex-shrink:0;margin-left:0.5rem;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div id="unit-dropdown-edit-{{ $mat->id }}"
                                style="display:none; position: absolute; top: calc(100% + 6px); left: 0; width: 100%; background: var(--bg-surface); border: 1.5px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.12); z-index: 9999; overflow: hidden;">
                                <div style="padding: 0.6rem 0.75rem; border-bottom: 1px solid var(--border-color);">
                                    <div style="position: relative;">
                                        <svg style="position:absolute;left:0.6rem;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-subtle);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                                        <input type="text" id="unit-search-edit-{{ $mat->id }}" placeholder="Search unit…" oninput="filterUnitCombo('edit-{{ $mat->id }}')"
                                            style="width:100%; padding: 0.45rem 0.75rem 0.45rem 2rem; border: 1px solid var(--border-color); border-radius: 7px; font-size: 0.82rem; font-family: inherit; color: var(--text-muted); background: var(--bg-page); box-sizing: border-box; outline: none;">
                                    </div>
                                </div>
                                <ul id="unit-options-edit-{{ $mat->id }}" style="list-style:none; margin:0; padding: 0.4rem; max-height: 200px; overflow-y: auto;">
                                    @foreach([['ml','ml','💧','Milliliters'],['pcs','pcs','📦','Pieces'],['sheets','sheets','📄','Sheets'],['rolls','rolls','🧻','Rolls'],['kg','kg','⚖️','Kilograms'],['liters','liters','🫙','Liters'],['meters','meters','📏','Meters'],['packs','packs','🗂️','Packs'],['boxes','boxes','📫','Boxes'],['sets','sets','🎁','Sets']] as [$val,$label,$icon,$desc])
                                    <li data-value="{{ $val }}" data-search="{{ strtolower($label . ' ' . $desc) }}"
                                        onclick="selectUnitCombo('edit-{{ $mat->id }}','{{ $val }}','{{ $icon }} {{ $label }}')"
                                        style="display:flex; align-items:center; gap:0.6rem; padding: 0.5rem 0.65rem; border-radius: 8px; cursor: pointer; background: {{ $mat->unit === $val && !$isCustomUnit ? 'var(--accent-soft)' : 'transparent' }};" class="unit-opt-edit-{{ $mat->id }}"
                                        onmouseover="this.style.background='#F0F9FF'" onmouseout="this.style.background='{{ $mat->unit === $val && !$isCustomUnit ? '#EFF6FF' : 'transparent' }}'">
                                        <span style="font-size:1.1rem; width:1.4rem; text-align:center; flex-shrink:0;">{{ $icon }}</span>
                                        <span>
                                            <span style="font-size: 0.88rem; font-weight: 600; color: {{ $mat->unit === $val && !$isCustomUnit ? 'var(--text-accent)' : 'var(--text-main)' }};">{{ $label }}</span>
                                            <span style="font-size: 0.75rem; color: var(--text-subtle); margin-left: 0.3rem;">{{ $desc }}</span>
                                        </span>
                                        @if($mat->unit === $val && !$isCustomUnit)
                                        <svg style="margin-left:auto;width:14px;height:14px;color:var(--text-accent);flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                    </li>
                                    @endforeach
                                    @foreach($extraUnitsEdit as $u)
                                    <li data-value="{{ $u }}" data-search="{{ strtolower($u) }}"
                                        onclick="selectUnitCombo('edit-{{ $mat->id }}','{{ $u }}','🏷️ {{ $u }}')"
                                        style="display:flex; align-items:center; gap:0.6rem; padding: 0.5rem 0.65rem; border-radius: 8px; cursor: pointer; background: {{ strtolower((string)$mat->unit) === strtolower((string)$u) && !$isCustomUnit ? 'var(--accent-soft)' : 'transparent' }};" class="unit-opt-edit-{{ $mat->id }}"
                                        onmouseover="this.style.background='#F0F9FF'" onmouseout="this.style.background='transparent'">
                                        <span style="font-size:1.1rem; width:1.4rem; text-align:center; flex-shrink:0;">🏷️</span>
                                        <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">{{ $u }}</span>
                                    </li>
                                    @endforeach
                                    <li style="border-top: 1px solid var(--border-color); margin: 0.3rem 0; padding: 0;"></li>
                                    <li data-value="other" data-search="custom other"
                                        onclick="selectUnitCombo('edit-{{ $mat->id }}','other','✏️ Custom Unit')"
                                        style="display:flex; align-items:center; gap:0.6rem; padding: 0.5rem 0.65rem; border-radius: 8px; cursor: pointer; background: {{ $isCustomUnit ? 'var(--warning-soft)' : 'transparent' }};" class="unit-opt-edit-{{ $mat->id }}"
                                        onmouseover="this.style.background='var(--warning-soft)'" onmouseout="this.style.background='{{ $isCustomUnit ? 'var(--warning-soft)' : 'transparent' }}'">
                                        <span style="font-size:1.1rem; width:1.4rem; text-align:center; flex-shrink:0;">✏️</span>
                                        <span>
                                            <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-warning);">Custom Unit</span>
                                            <span style="font-size: 0.75rem; color: var(--text-subtle); margin-left: 0.3rem;">Type your own</span>
                                        </span>
                                        @if($isCustomUnit)
                                        <svg style="margin-left:auto;width:14px;height:14px;color:var(--text-warning);flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                    </li>
                                    <li id="unit-no-results-edit-{{ $mat->id }}" style="display:none; padding: 0.75rem; text-align:center; font-size:0.82rem; color:var(--text-subtle);">No match found</li>
                                </ul>
                            </div>
                        </div>

                        <div id="custom-unit-container-edit-{{ $mat->id }}" style="display: {{ $isCustomUnit ? 'block' : 'none' }}; margin-top: 0.55rem; background: var(--warning-soft); padding: 0.75rem; border-radius: 10px; border-left: 3px solid var(--warning-color);">
                            <label style="font-size: 0.78rem; font-weight: 700; color: var(--text-warning); display: block; margin-bottom: 0.3rem;">
                                <i class="fa-solid fa-pen"></i> Specify Custom Unit *
                            </label>
                            <input type="text" name="custom_unit" id="custom-unit-input-edit-{{ $mat->id }}" value="{{ $isCustomUnit ? $mat->unit : '' }}" placeholder="e.g. bottles, reams, cartridges, tubes" style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid var(--warning-border); border-radius: 6px; font-size: 0.88rem; background: var(--bg-surface); box-sizing: border-box;">
                        </div>
                    </div>
                </div>

                {{-- Row 3: Current Stock Quantity & Low Stock Threshold (Side-by-side / 2-column grid) --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.35rem;">Current Stock Quantity *</label>
                        <input type="number" step="0.01" name="current_stock" value="{{ (float)$mat->current_stock }}" required placeholder="e.g. 100" style="width: 100%; padding: 0.65rem 0.95rem; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.92rem; box-sizing: border-box;">
                        <small style="color: var(--text-muted); font-size: 0.72rem; margin-top: 0.25rem; display: block;">Adjust/reduce stock if added by mistake.</small>
                    </div>

                    <div>
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.35rem;">Low Stock Threshold *</label>
                        <input type="number" step="0.01" name="low_stock_threshold" value="{{ (float)$mat->low_stock_threshold }}" required style="width: 100%; padding: 0.65rem 0.95rem; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.92rem; box-sizing: border-box;">
                    </div>
                </div>

                {{-- Footer Alignment: Delete Material on Left, Cancel and Save on Right --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color); flex-wrap: wrap; gap: 0.75rem;">
                    <!-- LEFT: DELETE BUTTON -->
                    <button type="button" onclick="document.getElementById('edit-material-modal-{{ $mat->id }}').style.display='none'; document.getElementById('delete-material-modal-{{ $mat->id }}').style.display='flex';" class="btn" style="padding: 0.65rem 1rem; font-size: 0.85rem; background: var(--danger-soft); color: var(--text-danger); border: 1.5px solid var(--danger-border); border-radius: 9px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; transition: background 0.15s;" onmouseover="this.style.background='#FEE2E2';" onmouseout="this.style.background='#FEF2F2';">
                        <i class="fa-solid fa-trash-can"></i> Delete Material
                    </button>

                    <!-- RIGHT: CANCEL & SAVE -->
                    <div style="display: flex; gap: 0.65rem; align-items: center;">
                        <button type="button" onclick="document.getElementById('edit-material-modal-{{ $mat->id }}').style.display='none';" class="btn btn-outline" style="padding: 0.65rem 1.25rem; font-weight: 600;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.35rem; font-weight: 700;">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endpush

@push('scripts')
<script>
// ── Category Combobox ─────────────────────────────────────────────────────────
const _catCsrf      = document.querySelector('meta[name="csrf-token"]')?.content || '';

function toggleCategoryCombo(key) {
    const dd  = document.getElementById('category-dropdown-' + key);
    const cr  = document.getElementById('category-caret-' + key);
    const tr  = document.getElementById('category-trigger-' + key);
    const open = dd.style.display === 'block';
    // close all other category & unit dropdowns
    document.querySelectorAll('[id^="category-dropdown-"]').forEach(d => { if (d !== dd) d.style.display = 'none'; });
    document.querySelectorAll('[id^="category-caret-"]').forEach(c   => { if (c !== cr) c.style.transform = 'rotate(0deg)'; });
    document.querySelectorAll('[id^="category-trigger-"]').forEach(t => { if (t !== tr) t.style.borderColor = 'var(--border-color)'; });
    document.querySelectorAll('[id^="unit-dropdown-"]').forEach(d => { d.style.display = 'none'; });
    document.querySelectorAll('[id^="unit-caret-"]').forEach(c => { c.style.transform = 'rotate(0deg)'; });
    document.querySelectorAll('[id^="unit-trigger-"]').forEach(t => { t.style.borderColor = 'var(--border-color)'; });
    dd.style.display = open ? 'none' : 'block';
    cr.style.transform = open ? 'rotate(0deg)' : 'rotate(180deg)';
    tr.style.borderColor = open ? '#CBD5E1' : '#0099FF';
    if (!open) {
        const s = document.getElementById('category-search-' + key);
        if (s) { s.value = ''; filterCategoryCombo(key); s.focus(); }
    }
}

function filterCategoryCombo(key) {
    const q       = (document.getElementById('category-search-' + key)?.value || '').trim().toLowerCase();
    const cls     = CSS.escape('cat-opt-' + key);
    let visible   = 0;
    document.querySelectorAll('.' + cls).forEach(li => {
        if (li.dataset.value === 'other') {
            li.style.display = 'flex';
            return;
        }
        const match = !q || (li.dataset.search || '').includes(q);
        li.style.display = match ? 'flex' : 'none';
        if (match) visible++;
    });
    const noRes = document.getElementById('category-no-results-' + key);
    if (noRes) noRes.style.display = (visible === 0 && q) ? 'list-item' : 'none';
    
    // Dynamic description for Custom Category
    const customDesc = document.getElementById('category-custom-desc-' + key);
    if (customDesc) {
        customDesc.textContent = q ? `Use "${q}"` : 'Type your own';
    }
}

function selectCategoryCombo(key, val, label) {
    const hiddenId = key === 'add' ? 'category-hidden-add' : 'category-hidden-' + key;
    const hidden = document.getElementById(hiddenId);
    if (hidden) hidden.value = val;

    // Update trigger label
    const displayLabel = label || (val === 'other' ? '✏️ Custom Category' : '🏷️ ' + val);
    const triggerLabel = document.getElementById('category-trigger-label-' + key);
    if (triggerLabel) {
        triggerLabel.innerHTML = '<span style="font-weight:600;font-size:0.92rem;color:var(--text-main);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + displayLabel + '</span>';
    }

    // Close dropdown
    const dd = document.getElementById('category-dropdown-' + key);
    const cr = document.getElementById('category-caret-' + key);
    const tr = document.getElementById('category-trigger-' + key);
    if (dd) dd.style.display = 'none';
    if (cr) cr.style.transform = 'rotate(0deg)';
    if (tr) { 
        tr.style.borderColor = '#10B981'; 
        setTimeout(() => { if (tr) tr.style.borderColor = 'var(--border-color)'; }, 1200); 
    }

    // Show/hide custom input
    const container = document.getElementById('custom-category-container-' + key);
    if (container) {
        container.style.display = val === 'other' ? 'block' : 'none';
        if (val === 'other') {
            const input = document.getElementById('custom-category-input-' + key);
            if (input) {
                const searchEl = document.getElementById('category-search-' + key);
                if (searchEl && searchEl.value.trim() && !input.value.trim()) {
                    input.value = searchEl.value.trim();
                }
                input.focus();
            }
        }
    }
}


// Close category combobox on outside click
document.addEventListener('click', function(e) {
    document.querySelectorAll('[id^="category-combobox-"]').forEach(box => {
        if (!box.contains(e.target)) {
            const key = box.id.replace('category-combobox-', '');
            const d = document.getElementById('category-dropdown-' + key);
            const c = document.getElementById('category-caret-'   + key);
            const t = document.getElementById('category-trigger-' + key);
            if (d) d.style.display = 'none';
            if (c) c.style.transform = 'rotate(0deg)';
            if (t) t.style.borderColor = 'var(--border-color)';
        }
    });
});

// ── Unit Combobox ─────────────────────────────────────────────────────────────
function toggleUnitCombo(key) {
    const dropdown = document.getElementById('unit-dropdown-' + key);
    const caret    = document.getElementById('unit-caret-' + key);
    const trigger  = document.getElementById('unit-trigger-' + key);
    const isOpen   = dropdown.style.display === 'block';
    // Close all other open dropdowns first (both unit & category)
    document.querySelectorAll('[id^="unit-dropdown-"]').forEach(d => { if (d !== dropdown) d.style.display = 'none'; });
    document.querySelectorAll('[id^="unit-caret-"]').forEach(c => { if (c !== caret) c.style.transform = 'rotate(0deg)'; });
    document.querySelectorAll('[id^="unit-trigger-"]').forEach(t => { if (t !== trigger) t.style.borderColor = 'var(--border-color)'; });
    document.querySelectorAll('[id^="category-dropdown-"]').forEach(d => { d.style.display = 'none'; });
    document.querySelectorAll('[id^="category-caret-"]').forEach(c => { c.style.transform = 'rotate(0deg)'; });
    document.querySelectorAll('[id^="category-trigger-"]').forEach(t => { t.style.borderColor = 'var(--border-color)'; });
    dropdown.style.display = isOpen ? 'none' : 'block';
    caret.style.transform  = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
    trigger.style.borderColor = isOpen ? '#CBD5E1' : '#0099FF';
    if (!isOpen) {
        const searchEl = document.getElementById('unit-search-' + key);
        if (searchEl) { searchEl.value = ''; filterUnitCombo(key); searchEl.focus(); }
    }
}

function filterUnitCombo(key) {
    const q = (document.getElementById('unit-search-' + key)?.value || '').toLowerCase().trim();
    const items = document.querySelectorAll('.unit-opt-' + key.replace('-', '-'));
    // support both 'add' and 'edit-{id}' keys for class name
    const cls = 'unit-opt-' + key;
    let visible = 0;
    document.querySelectorAll('.' + CSS.escape(cls)).forEach(li => {
        const match = !q || (li.dataset.search || '').includes(q);
        li.style.display = match ? 'flex' : 'none';
        if (match) visible++;
    });
    // divider li (no class) – always show
    const noResult = document.getElementById('unit-no-results-' + key);
    if (noResult) noResult.style.display = visible === 0 ? 'list-item' : 'none';
}

function selectUnitCombo(key, val, label) {
    document.getElementById('unit-hidden-' + (key === 'add' ? 'add' : 'edit-' + key.replace('edit-', ''))).value = val;
    // Update trigger label
    const triggerLabel = document.getElementById('unit-trigger-label-' + key);
    triggerLabel.innerHTML = '<span style="font-weight:600;font-size:0.92rem;color:var(--text-main);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + label + '</span>';
    // Close dropdown
    document.getElementById('unit-dropdown-' + key).style.display = 'none';
    document.getElementById('unit-caret-'    + key).style.transform = 'rotate(0deg)';
    document.getElementById('unit-trigger-'  + key).style.borderColor = '#10B981'; // green flash = selected
    setTimeout(() => {
        const t = document.getElementById('unit-trigger-' + key);
        if (t) t.style.borderColor = 'var(--border-color)';
    }, 1200);
    // Show/hide custom input
    const customKey = key === 'add' ? 'add' : key.replace('edit-', 'edit-');
    const container = document.getElementById('custom-unit-container-' + customKey);
    if (container) {
        container.style.display = val === 'other' ? 'block' : 'none';
        if (val === 'other') document.getElementById('custom-unit-input-' + customKey)?.focus();
    }
}

// Close combobox on outside click
document.addEventListener('click', function(e) {
    document.querySelectorAll('[id^="unit-combobox-"]').forEach(box => {
        if (!box.contains(e.target)) {
            const key = box.id.replace('unit-combobox-', '');
            const d = document.getElementById('unit-dropdown-' + key);
            const c = document.getElementById('unit-caret-' + key);
            const t = document.getElementById('unit-trigger-' + key);
            if (d) d.style.display = 'none';
            if (c) c.style.transform = 'rotate(0deg)';
            if (t) t.style.borderColor = 'var(--border-color)';
        }
    });
});

// ESC key closes any open modal or combobox dropdown
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const addM = document.getElementById('add-material-modal');
        if (addM && addM.style.display !== 'none') addM.style.display = 'none';
        document.querySelectorAll('[id^="edit-material-modal-"]').forEach(m => {
            if (m.style.display !== 'none') m.style.display = 'none';
        });
        document.querySelectorAll('[id^="delete-material-modal-"]').forEach(m => {
            if (m.style.display !== 'none') m.style.display = 'none';
        });
        document.querySelectorAll('[id^="category-dropdown-"]').forEach(d => d.style.display = 'none');
        document.querySelectorAll('[id^="category-caret-"]').forEach(c => c.style.transform = 'rotate(0deg)');
        document.querySelectorAll('[id^="unit-dropdown-"]').forEach(d => d.style.display = 'none');
        document.querySelectorAll('[id^="unit-caret-"]').forEach(c => c.style.transform = 'rotate(0deg)');
    }
});

function submitRestock(btn) {
    const form = btn.closest('.restock-form');
    const input = form.querySelector('.restock-qty-input');
    const qty = parseFloat(input.value);
    const matId = form.dataset.materialId;
    const url = form.dataset.url;
    const csrf = form.querySelector('[name="_token"]').value;

    if (!qty || qty <= 0) {
        input.style.borderColor = '#DC2626';
        input.focus();
        return;
    }

    // Disable button — prevent double submit
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ restock_quantity: qty }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update stock display in-place (big number = total stock, sub-line = available)
            const isOut = typeof data.is_out !== 'undefined' ? data.is_out : (parseFloat(data.available_stock) <= 0);
            const isLow = !isOut && data.is_low;
            const available = typeof data.available_stock !== 'undefined'
                ? parseFloat(data.available_stock)
                : parseFloat(data.new_stock);
            const total = typeof data.new_stock !== 'undefined' ? parseFloat(data.new_stock) : available;
            const reserved = typeof data.reserved_stock !== 'undefined' ? parseFloat(data.reserved_stock) : Math.max(0, total - available);

            const stockEl = document.getElementById('stock-display-' + matId);
            if (stockEl) {
                stockEl.innerHTML = total.toLocaleString(undefined, {maximumFractionDigits: 4})
                    + ' <span style="font-size:0.82rem; font-weight:600; color:var(--text-muted);">' + data.unit + '</span>';
                stockEl.style.color = isOut ? '#DC2626' : (isLow ? '#EA580C' : '#0F172A');

                // Flash green briefly
                stockEl.style.transition = 'background 0.3s';
                stockEl.style.background = '#D1FAE5';
                stockEl.style.borderRadius = '6px';
                stockEl.style.padding = '2px 6px';
                setTimeout(() => { stockEl.style.background = ''; stockEl.style.padding = ''; }, 1500);
            }

            // Live-update: row background + badge
            const row = document.getElementById('material-row-' + matId);
            if (row) row.style.background = isOut ? '#FEF2F2' : (isLow ? '#FFF7ED' : '#FFFFFF');

            const badgeCell = document.getElementById('badge-cell-' + matId);
            if (badgeCell) {
                if (isOut && parseFloat(data.new_stock) > 0) {
                    badgeCell.innerHTML = '<span style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.55rem; background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.02em;"><i class="fa-solid fa-lock" style="font-size: 0.68rem;"></i> All Reserved</span>';
                } else if (isOut) {
                    badgeCell.innerHTML = '<span style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.55rem; background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.02em;"><i class="fa-solid fa-triangle-exclamation" style="font-size: 0.68rem;"></i> Out of Stock</span>';
                } else if (isLow) {
                    badgeCell.innerHTML = '<span style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.55rem; background: var(--warning-soft); color: var(--text-warning); border: 1px solid var(--warning-border); border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.02em;"><i class="fa-solid fa-triangle-exclamation" style="font-size: 0.68rem;"></i> Low Stock</span>';
                } else {
                    badgeCell.innerHTML = '';
                }
            }

            // Live-update: stock level progress bar (based on available stock)
            const bar = document.getElementById('stock-bar-' + matId);
            if (bar && typeof data.threshold !== 'undefined') {
                const pct = data.threshold > 0 ? Math.min(100, Math.round((available / data.threshold) * 100)) : 100;
                bar.style.width = pct + '%';
                bar.style.background = isOut ? '#DC2626' : (isLow ? '#EA580C' : '#16A34A');
            }

            // Live-update: reserved + available line
            const reservedEl = document.getElementById('reserved-display-' + matId);
            if (reservedEl && typeof data.reserved_stock !== 'undefined') {
                if (reserved > 0) {
                    reservedEl.innerHTML = '<i class="fa-solid fa-lock" style="font-size:0.68rem;"></i> '
                        + reserved + ' ' + data.unit + ' reserved · '
                        + '<span style="color:var(--text-accent);">' + available.toLocaleString(undefined, {maximumFractionDigits: 4}) + ' available</span>';
                    reservedEl.style.display = '';
                } else {
                    reservedEl.style.display = 'none';
                }
            }

            const availableEl = document.getElementById('available-display-' + matId);
            if (availableEl) {
                availableEl.textContent = available.toLocaleString(undefined, {maximumFractionDigits: 4}) + ' ' + data.unit + ' available';
                availableEl.style.color = '#0099FF';
                availableEl.style.display = reserved > 0 ? 'none' : '';
            }

            // Reset input
            input.value = '';
            input.style.borderColor = 'var(--border-color)';

            // Show brief success state on button
            btn.innerHTML = '<i class="fa-solid fa-check"></i>';
            btn.style.background = '#16A34A';
            setTimeout(() => {
                btn.innerHTML = '<i class="fa-solid fa-plus"></i> Restock';
                btn.style.background = '';
                btn.disabled = false;
            }, 1500);
        } else {
            btn.innerHTML = '<i class="fa-solid fa-plus"></i> Restock';
            btn.disabled = false;
        }
    })
    .catch(() => {
        btn.innerHTML = '<i class="fa-solid fa-plus"></i> Restock';
        btn.disabled = false;
        alert('Restock failed. Please try again.');
    });
}
</script>

{{-- ─── CUSTOM COMBOBOX FOR FILTER SELECTS (keeps hidden native select; auto-submits GET form on pick) ─── --}}
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
            btn.style.cssText = 'width:100%; display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.65rem 1rem; border:1px solid ' + (active ? 'var(--accent-color)' : 'var(--border-strong)') + '; border-left:3px solid ' + (active ? 'var(--accent-color)' : 'var(--border-strong)') + '; border-radius:10px; font-size:0.92rem; font-weight:' + (active ? '700' : '500') + '; background:' + (active ? 'var(--accent-soft)' : 'var(--bg-surface)') + '; color:' + (active ? 'var(--accent-hover)' : 'var(--text-main)') + '; cursor:pointer; transition:all 0.2s;';
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
                item.style.cssText = 'display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.5rem 0.65rem; border-radius:7px; font-size:0.86rem; font-weight:' + (selected ? '700' : '500') + '; cursor:pointer; color:' + (selected ? 'var(--accent-contrast)' : 'var(--text-main)') + '; background:' + (selected ? 'var(--accent-color)' : 'var(--bg-elevated)') + ';';
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
