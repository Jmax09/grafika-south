@extends('layouts.admin')

@section('title', 'Stock Movements')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Date field styling — applied to BOTH the original input and the Flatpickr altInput
       (same class), so the rounded look survives even if the CDN fails to load. */
    .smf-date-field {
        width: 100% !important;
        padding: 0.65rem 2.2rem 0.65rem 1rem !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 10px !important;
        font-size: 0.92rem !important;
        font-family: inherit !important;
        outline: none !important;
        background: var(--bg-surface) !important;
        color: var(--text-main) !important;
        box-sizing: border-box !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        display: block !important;
        height: 41px;
    }
    .smf-date-field::placeholder { color: var(--text-subtle); }
    .smf-date-field:focus {
        border-color: var(--accent-color) !important;
        box-shadow: 0 0 0 3px rgba(0, 153, 255, 0.15) !important;
    }

    /* Modern Custom Flatpickr Theme matching Grafika South Design (same as New Purchase Order) */
    .flatpickr-calendar {
        background: var(--bg-surface) !important;
        border-radius: 16px !important;
        border: 1px solid var(--border-color) !important;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08) !important;
        font-family: 'Inter', sans-serif !important;
        padding: 0.75rem !important;
        width: 320px !important;
    }
    .flatpickr-innerContainer { width: 100% !important; }
    .flatpickr-rContainer { width: 100% !important; }
    .flatpickr-days { width: 100% !important; }
    .dayContainer {
        width: 100% !important; min-width: 100% !important; max-width: 100% !important;
        justify-content: space-between !important;
    }
    .flatpickr-calendar:before, .flatpickr-calendar:after { border-bottom-color: var(--border-color) !important; }
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
        width: 30px !important; height: 30px !important;
        display: flex !important; align-items: center !important; justify-content: center !important;
        border-radius: 8px !important; padding: 0 !important; cursor: pointer !important;
        transition: all 0.15s ease !important;
    }
    .flatpickr-months .flatpickr-prev-month { left: 8px !important; }
    .flatpickr-months .flatpickr-next-month { right: 8px !important; }
    .flatpickr-months .flatpickr-prev-month:hover,
    .flatpickr-months .flatpickr-next-month:hover { background: var(--accent-soft) !important; }
    .flatpickr-months .flatpickr-prev-month svg,
    .flatpickr-months .flatpickr-next-month svg { width: 14px !important; height: 14px !important; vertical-align: middle !important; }
    .flatpickr-current-month {
        position: static !important; width: auto !important; height: auto !important;
        display: flex !important; align-items: center !important; justify-content: center !important;
        font-size: 0.95rem !important; font-weight: 800 !important; color: var(--text-main) !important;
        padding: 0 !important; left: auto !important; transform: none !important;
    }
    .flatpickr-current-month .cur-month { font-weight: 800 !important; color: var(--text-main) !important; }
    .flatpickr-monthDropdown-months { font-weight: 800 !important; color: var(--text-main) !important; }
    .flatpickr-weekdays { background: transparent !important; margin-top: 0.5rem !important; width: 100% !important; }
    .flatpickr-weekdaycontainer { display: flex !important; width: 100% !important; justify-content: space-between !important; }
    span.flatpickr-weekday {
        color: var(--text-muted) !important; font-size: 0.72rem !important; font-weight: 700 !important;
        text-transform: uppercase !important; letter-spacing: 0.04em !important; flex: 1 !important;
    }
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
    .flatpickr-months .flatpickr-prev-month:hover svg,
    .flatpickr-months .flatpickr-next-month:hover svg { fill: var(--accent-color) !important; }
</style>
@endpush

@section('content')

@php
    $typeStyles = [
        'restock' => ['bg'=>'#F0FDF4', 'text'=>'#16A34A', 'border'=>'#BBF7D0'],
        'manual_adjustment' => ['bg'=>'#FFF7ED', 'text'=>'#EA580C', 'border'=>'#FED7AA'],
        'auto_deduction' => ['bg'=>'#FEF2F2', 'text'=>'#DC2626', 'border'=>'#FCA5A5'],
        'order_reversal' => ['bg'=>'#EFF6FF', 'text'=>'#2563EB', 'border'=>'#BFDBFE'],
        'reservation' => ['bg'=>'#F5F3FF', 'text'=>'#7C3AED', 'border'=>'#DDD6FE'],
        'reservation_release' => ['bg'=>'#ECFDF5', 'text'=>'#059669', 'border'=>'#A7F3D0'],
    ];
@endphp

<div class="card" style="padding: 1.5rem; margin-bottom: 2rem; border-radius: 16px; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04); border: 1px solid var(--border-color);">
    <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.85rem; margin-bottom: 1rem;">
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 0.6rem;">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--text-accent);"></i> Stock Movements
        </h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.2rem; font-weight: 500;">Search, filter, and track all inventory activity — restocks, order deductions, adjustments, and reservations.</p>
    </div>

    {{-- SEARCH & FILTER BAR — same fused pattern as Suppliers / Materials Stock --}}
    <form action="{{ route('admin.materials.movements') }}" method="GET" style="display: flex; gap: 0.85rem; align-items: center; flex-wrap: wrap; margin-bottom: 1.5rem;">

        {{-- Search Input + Button (fused pair) --}}
        <div style="flex: 2; min-width: 240px; display: flex; align-items: center;">
            <input type="text" name="log_search" value="{{ $logSearch }}" placeholder="Search material, PO ref, order #, or product..."
                style="flex: 1; padding: 0.65rem 1rem; border: 1px solid var(--border-color); border-right: none; border-radius: 10px 0 0 10px; font-size: 0.92rem; outline: none; background: var(--bg-surface); color: var(--text-main); transition: border-color 0.2s;"
                onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='';">
            <button type="submit"
                style="padding: 0.65rem 1.1rem; background: var(--accent-color); color: var(--text-inverse); border: 1px solid var(--accent-color); border-radius: 0 10px 10px 0; font-size: 0.88rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; transition: background 0.15s;"
                onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
        </div>

        {{-- Type Dropdown Filter (custom combobox, auto-submit) --}}
        <div style="flex: 1; min-width: 170px;">
            <select name="log_type" class="custom-combo" data-icon="fa-filter" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="restock" {{ $logType === 'restock' ? 'selected' : '' }}>Restock</option>
                <option value="auto_deduction" {{ $logType === 'auto_deduction' ? 'selected' : '' }}>Order Deduction</option>
                <option value="manual_adjustment" {{ $logType === 'manual_adjustment' ? 'selected' : '' }}>Adjustment</option>
                <option value="order_reversal" {{ $logType === 'order_reversal' ? 'selected' : '' }}>Cancellation</option>
                <option value="reservation" {{ $logType === 'reservation' ? 'selected' : '' }}>Reservation</option>
                <option value="reservation_release" {{ $logType === 'reservation_release' ? 'selected' : '' }}>Reservation Release</option>
            </select>
        </div>

        {{-- Date Range (Flatpickr — same modern calendar as New Purchase Order) --}}
        <div style="flex: 1; min-width: 165px; position: relative;">
            <input type="text" id="log_start_picker" name="log_start" value="{{ $logStart }}" placeholder="From date" autocomplete="off" class="smf-date-field"
                style="width: 100%; padding: 0.65rem 2.2rem 0.65rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.92rem; outline: none; background: var(--bg-surface); color: var(--text-main); box-sizing: border-box; cursor: pointer; transition: all 0.2s;">
            <span style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--text-accent); pointer-events: none; font-size: 0.95rem;">
                <i class="fa-regular fa-calendar-days"></i>
            </span>
        </div>
        <div style="flex: 1; min-width: 165px; position: relative;">
            <input type="text" id="log_end_picker" name="log_end" value="{{ $logEnd }}" placeholder="To date" autocomplete="off" class="smf-date-field"
                style="width: 100%; padding: 0.65rem 2.2rem 0.65rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.92rem; outline: none; background: var(--bg-surface); color: var(--text-main); box-sizing: border-box; cursor: pointer; transition: all 0.2s;">
            <span style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--text-accent); pointer-events: none; font-size: 0.95rem;">
                <i class="fa-regular fa-calendar-days"></i>
            </span>
        </div>

        {{-- Reset button (only when filters active) --}}
        @if($logSearch || $logType || $logStart || $logEnd)
            <a href="{{ route('admin.materials.movements') }}"
                style="padding: 0.65rem 1.1rem; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.88rem; font-weight: 700; color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; transition: all 0.15s;"
                onmouseover="this.style.borderColor='var(--border-strong)'; this.style.color='var(--text-main)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-muted)';">
                <i class="fa-solid fa-rotate-left" style="color: var(--text-muted);"></i> Reset
            </a>
        @endif
    </form>

    {{-- TABLE --}}
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.82rem;">
            <thead>
                <tr style="background: var(--bg-page); text-align: left; color: var(--text-muted);">
                    <th style="padding: 0.6rem 0.85rem; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Material</th>
                    <th style="padding: 0.6rem 0.85rem; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Type</th>
                    <th style="padding: 0.6rem 0.85rem; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; text-align: right;">Qty</th>
                    <th style="padding: 0.6rem 0.85rem; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Source / Product Origin</th>
                    <th style="padding: 0.6rem 0.85rem; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; text-align: right; white-space: nowrap;">When</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $style = $typeStyles[$log->type] ?? ['bg'=>'#F8FAFC', 'text'=>'#475569', 'border'=>'#E2E8F0'];
                        $qty = (float) $log->quantity_deducted;
                        $unit = $log->material?->unit ?? '';
                        // Determine display: negative qty = addition (restock), positive = deduction
                        $displayQty = $qty <= 0 ? '+' . number_format(abs($qty), 2) : '-' . number_format($qty, 2);

                        // Type-aware qty color: reservations are soft holds (purple), releases/reversals restore (teal)
                        if ($log->type === 'reservation') {
                            $qtyColor = '#7C3AED';
                        } elseif ($log->type === 'reservation_release' || $log->type === 'order_reversal') {
                            $qtyColor = '#059669';
                        } else {
                            $qtyColor = $qty <= 0 ? '#16A34A' : '#DC2626';
                        }

                        // Build source/product origin display
                        $sourceLabel = '';
                        $sourceDetail = '';
                        $sourceUrl = null;

                        if (in_array($log->type, ['auto_deduction', 'reservation', 'order_reversal', 'reservation_release']) && $log->order) {
                            // Get product names from order items
                            $productNames = $log->order->items->map(fn ($item) => $item->product?->name)->filter()->unique()->take(2)->implode(', ');
                            $fallbackLabels = [
                                'auto_deduction' => 'Order Deduction',
                                'reservation' => 'Stock Reservation',
                                'order_reversal' => 'Order Reversal',
                                'reservation_release' => 'Reservation Release',
                            ];
                            $sourceLabel = $productNames ?: ($fallbackLabels[$log->type] ?? 'Order Activity');
                            $sourceDetail = 'Order #' . $log->order->order_number;
                            $sourceUrl = route('admin.orders.show', $log->order->id);
                        } elseif ($log->type === 'restock') {
                            if ($log->notes && preg_match('/PO receipt (\S+):/', $log->notes, $m)) {
                                $sourceLabel = $m[1];
                                // Extract item description after the colon
                                $desc = \Illuminate\Support\Str::after($log->notes, $m[1] . ': ');
                                $sourceDetail = $desc ?: 'Purchase Order receipt';
                            } else {
                                $sourceLabel = 'Restock';
                                $sourceDetail = $log->notes ? \Illuminate\Support\Str::limit($log->notes, 60) : 'Manual restock';
                            }
                        } elseif ($log->type === 'manual_adjustment') {
                            $sourceLabel = 'Manual Adjustment';
                            $sourceDetail = $log->notes ? \Illuminate\Support\Str::limit($log->notes, 70) : 'Stock level changed via edit';
                        } else {
                            $sourceLabel = $log->material?->name ?? 'System';
                            $sourceDetail = $log->notes ? \Illuminate\Support\Str::limit($log->notes, 60) : '—';
                        }
                    @endphp
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 0.6rem 0.85rem; font-weight: 700; color: var(--text-main); white-space: nowrap;">{{ $log->material?->name ?? 'Removed item' }}</td>
                        <td style="padding: 0.6rem 0.85rem;">
                            <span style="display: inline-block; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; background: {{ $style['bg'] }}; color: {{ $style['text'] }}; border: 1px solid {{ $style['border'] }};">{{ str_replace('_', ' ', $log->type) }}</span>
                        </td>
                        <td style="padding: 0.6rem 0.85rem; text-align: right; font-weight: 700; white-space: nowrap; color: {{ $qtyColor }};">{{ $displayQty }} <span style="font-weight: 500; font-size: 0.72rem; color: var(--text-subtle);">{{ $unit }}</span></td>
                        <td style="padding: 0.6rem 0.85rem;">
                            <div style="max-width: 320px; overflow: hidden;">
                            @if($sourceUrl)
                                <a href="{{ $sourceUrl }}" style="color: var(--text-main); text-decoration: none; font-weight: 600; font-size: 0.82rem; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $sourceLabel }} — {{ $sourceDetail }}">{{ $sourceLabel }}</a>
                                <span style="color: var(--text-accent); font-size: 0.72rem; font-weight: 500;">{{ $sourceDetail }}</span>
                            @else
                                <span style="color: var(--text-main); font-weight: 600; font-size: 0.82rem; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $sourceLabel }}">{{ $sourceLabel }}</span>
                                <span style="color: var(--text-muted); font-size: 0.72rem; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $sourceDetail }}">{{ $sourceDetail }}</span>
                            @endif
                            </div>
                        </td>
                        <td style="padding: 0.6rem 0.85rem; text-align: right; color: var(--text-subtle); white-space: nowrap;">{{ $log->created_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-subtle);">No stock movements found matching your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION — single branded bar (counter + page buttons), no duplicate text --}}
    @if($logs->hasPages())
    <div style="margin-top: 1rem;">
        {{ $logs->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof flatpickr === 'undefined') return;

        var common = {
            dateFormat: 'Y-m-d',   /* hidden original input submits Y-m-d (matches controller validation) */
            altInput: true,
            altFormat: 'M j, Y',   /* friendly display format, same as Purchase Order */
            altInputClass: 'smf-date-field',
            animate: true,
            disableMobile: true
        };

        var startFp = flatpickr('#log_start_picker', Object.assign({}, common, {
            maxDate: 'today',
            onChange: function (selectedDates) {
                if (window.endFp) window.endFp.set('minDate', selectedDates[0] || null);
            }
        }));

        window.endFp = flatpickr('#log_end_picker', Object.assign({}, common, {
            minDate: '2000-01-01',
            maxDate: 'today',
            onChange: function (selectedDates) {
                if (startFp) startFp.set('maxDate', selectedDates[0] || 'today');
            }
        }));
    });
</script>
@endpush

{{-- ─── CUSTOM COMBOBOX FOR FILTER DROPDOWNS ─── --}}
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
