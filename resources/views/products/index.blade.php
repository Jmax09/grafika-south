@extends('layouts.app')

@section('title', 'Products Catalog - Grafika South Print Shop')

@push('styles')
<style>
    .catalog-header {
        margin-bottom: 2rem;
    }
    .catalog-title {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
    }
    .sticky-filter-bar {
        position: sticky;
        top: 65px;
        z-index: 40;
        background-color: var(--bg-page);
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        margin-bottom: 1rem;
    }
    /* Product card layout is pure Tailwind (Lazada-style) — no custom CSS here,
       because unlayered custom CSS would override utility classes in Tailwind v4 */
</style>
@endpush

@section('content')
<div class="catalog-header">
    <h1 class="catalog-title">Print Products &amp; Services Catalog</h1>
</div>

<!-- Filter Container -->
<div class="sticky-filter-bar">
    <div class="bg-[var(--bg-elevated)] p-3 sm:p-4 rounded-2xl border border-[var(--border-color)] shadow-md" style="border-bottom: 2px solid var(--cmyk-cyan);">
        <form action="{{ route('products.index') }}" method="GET" style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.85rem;">

        <!-- 1. Fused Search Input + Button (matches Purchase Orders filter bar) -->
        <div style="flex: 2; min-width: 220px; display: flex; align-items: center;">
            <input type="text" name="q" value="{{ request('q', request('search')) }}" placeholder="Search products by name, description..." autocomplete="off"
                   style="flex: 1; padding: 0.65rem 1rem; border: 1px solid var(--border-strong); border-right: none; border-radius: 10px 0 0 10px; font-size: 0.92rem; outline: none; background: var(--bg-surface); color: var(--text-main); transition: border-color 0.2s;"
                   onfocus="this.style.borderColor='var(--accent-color)';" onblur="this.style.borderColor='';">
            <button type="submit"
                    style="padding: 0.65rem 1.1rem; background: var(--accent-color); color: var(--text-inverse); border: 1px solid var(--accent-color); border-radius: 0 10px 10px 0; font-size: 0.88rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; transition: background 0.15s;"
                    onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
        </div>

        <!-- 2. Category Dropdown -->
        <div style="flex: 1; min-width: 170px;">
            <select name="category" 
                    onchange="this.form.submit()" 
                    class="custom-combo w-full px-2.5 py-2 bg-[var(--bg-surface)] border border-[var(--border-strong)] rounded-xl text-[11px] sm:text-xs font-semibold text-[var(--text-main)] focus:outline-none focus:outline-hidden focus:ring-2 focus:ring-sky-500 cursor-pointer"
                    data-icon="fa-layer-group" data-accent="#0099FF">
                <option value="">All Categories ({{ $totalProductsCount ?? count($categories) }})</option>
                @foreach($categories as $cat)
                    @php
                        $catVal = is_object($cat) ? ($cat->id ?? $cat->name) : $cat;
                        $catName = is_object($cat) ? ($cat->name ?? $cat->category ?? $cat->id) : $cat;
                        $count = isset($categoryCounts[$catName]) ? ' (' . $categoryCounts[$catName] . ')' : '';
                    @endphp
                    <option value="{{ $catVal }}" {{ request('category') == $catVal ? 'selected' : '' }}>
                        {{ $catName }}{{ $count }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- 3. Sort Dropdown -->
        <div style="flex: 1; min-width: 170px;">
            <select name="sort" 
                    onchange="this.form.submit()" 
                    class="custom-combo w-full px-2.5 py-2 bg-[var(--bg-surface)] border border-[var(--border-strong)] rounded-xl text-[11px] sm:text-xs font-semibold text-[var(--text-main)] focus:outline-none focus:outline-hidden focus:ring-2 focus:ring-sky-500 cursor-pointer"
                    data-icon="fa-arrow-down-wide-short" data-accent="#0099FF">
                <option value="latest" {{ request('sort') == 'latest' || !request('sort') ? 'selected' : '' }}>Sort: Latest</option>
                <option value="price_low" {{ request('sort') == 'price_low' || request('sort') == 'price-low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high" {{ request('sort') == 'price_high' || request('sort') == 'price-high' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="name_asc" {{ request('sort') == 'name_asc' || request('sort') == 'name-asc' ? 'selected' : '' }}>Name: A to Z</option>
                <option value="name_desc" {{ request('sort') == 'name_desc' || request('sort') == 'name-desc' ? 'selected' : '' }}>Name: Z to A</option>
            </select>
        </div>

        <!-- 4. Clear filters -->
        @if(request()->hasAny(['q', 'search', 'category', 'sort']) && ((request('category') !== 'all' && request()->filled('category')) || (!in_array(request('sort'), ['latest', '']) && request()->filled('sort')) || request()->filled('q') || request()->filled('search')))
            <a href="{{ route('products.index') }}" 
               style="padding: 0.65rem 1rem; background: var(--bg-surface); border: 1px solid var(--border-strong); border-radius: 10px; font-size: 0.88rem; font-weight: 700; color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; transition: all 0.15s;"
               onmouseover="this.style.color='var(--text-main)';" onmouseout="this.style.color='';"
               title="Clear filters">
                <i class="fa-solid fa-xmark" style="color:var(--text-muted);"></i> Clear
            </a>
        @endif
    </form>
</div>

@if(request()->filled('q'))
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem; background: var(--accent-soft); padding: 0.75rem 1rem; border-radius: 12px; border: 1.5px solid var(--border-strong);">
        <span style="font-size: 0.88rem; color: var(--text-accent); font-weight: 600;">
            Showing results for <strong style="color: var(--text-main);">"{{ request('q') }}"</strong> ({{ $products->count() }} found)
        </span>
        <a href="{{ route('products.index', ['category' => request('category'), 'sort' => request('sort')]) }}" style="font-size: 0.82rem; color: var(--text-accent); text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
            <i class="fa-solid fa-xmark"></i> Clear search
        </a>
    </div>
@endif
</div>

<!-- PRODUCTS GRID (Lazada-style responsive grid) -->
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5 sm:gap-4">
    @forelse($products as $product)
        @php
            $targetUrl = route('products.show', $product->slug ?: $product->id);
            $hasStock = $product->has_sufficient_stock ?? true;
            // Real ratings from the reviews system (withCount/withAvg in ProductController::index)
            $reviewCount = (int) ($product->reviews_count ?? 0);
            $avgRating = $reviewCount > 0 ? round((float) $product->reviews_avg_rating, 1) : 0;
        @endphp
        <a href="{{ $targetUrl }}" class="group block bg-[var(--bg-surface)] rounded-lg border border-[var(--border-color)] overflow-hidden shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-full no-underline hover:no-underline focus:no-underline">
            <!-- Product Image Container (Square Aspect Ratio) -->
            <div class="relative w-full aspect-square bg-[var(--bg-surface-2)] overflow-hidden">
                @if($product->image_path)
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center gap-1.5 bg-[var(--accent-soft)]">
                        <i class="fa-solid fa-print text-2xl text-sky-500"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wide text-[var(--text-muted)]">Grafika South Print</span>
                    </div>
                @endif

                @if($product->category)
                    <span class="absolute top-1.5 left-1.5 bg-white text-[#0099FF] border border-[#0099FF]/20 text-[10px] font-bold px-1.5 py-0.5 rounded shadow-xs uppercase tracking-wide">
                        {{ $product->category }}
                    </span>
                @endif

                @if($product->is_featured)
                    <span class="absolute top-1.5 right-1.5 bg-amber-400 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-xs" title="Featured">
                        <i class="fa-solid fa-star"></i>
                    </span>
                @endif

                @if(!$hasStock)
                    <span class="absolute bottom-1.5 left-1.5 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-xs uppercase tracking-wide">
                        Out of Stock
                    </span>
                @endif
            </div>

            <!-- Product Content Details -->
            <div class="p-2.5 flex flex-col justify-between flex-1">
                <div>
                    <!-- Product Title (Limited to 2 lines) -->
                    <h3 class="text-xs sm:text-sm font-medium text-[var(--text-main)] line-clamp-2 leading-snug group-hover:text-sky-600 transition-colors">
                        {{ $product->name }}
                    </h3>
                </div>

                <div class="mt-2">
                    <!-- Price Line -->
                    <div class="flex items-baseline gap-1.5 flex-wrap">
                        <div class="text-base sm:text-lg font-extrabold text-[#0080FF] no-underline my-1">
                            &#8369;{{ number_format($product->price ?: $product->base_price, 2) }}
                        </div>
                    </div>

                    <!-- Rating & Sold Count -->
                    <div class="flex items-center gap-1.5 mt-1 text-[11px] text-[var(--text-muted)] no-underline">
                        @if($reviewCount > 0)
                            <div class="flex text-[10px]">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="{{ $i <= round($avgRating) ? 'text-amber-400' : 'text-[var(--border-strong)]' }}">★</span>
                                @endfor
                            </div>
                            <span class="no-underline">{{ number_format($avgRating, 1) }} ({{ $reviewCount }})</span>
                        @else
                            <span class="text-[var(--text-muted)] no-underline">No ratings yet</span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1.5rem; background: var(--bg-surface); border-radius: 16px; border: 1.5px dashed var(--border-color);">
            <div style="width: 64px; height: 64px; background: var(--bg-subtle); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; font-size: 1.8rem; color: var(--text-subtle);">
                <i class="fa-solid fa-box-open"></i>
            </div>
            @if(request()->filled('q'))
                <h3 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">No products found matching "{{ request('q') }}"</h3>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem; max-width: 460px; margin-left: auto; margin-right: auto; line-height: 1.55;">
                    We couldn't find any print services matching your search keyword. Try searching with common terms like "T-Shirt", "PVC ID", or "Business Cards".
                </p>
            @else
                <h3 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">No products found</h3>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.55;">Try adjusting your category or sorting filters to find available print services.</p>
            @endif
            <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('products.index') }}" class="btn btn-primary" style="padding: 0.65rem 1.5rem; border-radius: 10px; font-weight: 700; background: var(--accent-color);">
                    <i class="fa-solid fa-rotate-left"></i> View All Products
                </a>
            </div>
        </div>
    @endforelse
</div>
@endsection

{{-- ─── CUSTOM COMBOBOX FOR CATALOG CATEGORY & SORT DROPDOWNS ─── --}}
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
        caret.style.cssText = 'font-size:0.72rem; color:#94A3B8; transition:transform 0.15s; flex-shrink:0;';
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
            btn.style.cssText = 'width:100%; display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.65rem 1rem; border:1px solid ' + (isOpen ? accent : 'var(--border-strong)') + '; border-radius:10px; font-size:0.92rem; color:var(--text-main); background:var(--bg-surface); outline:none; cursor:pointer; box-sizing:border-box; transition:border-color 0.2s;';
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
