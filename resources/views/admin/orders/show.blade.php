@extends('layouts.admin')

@section('title', 'Manage Order #' . $order->order_number . ' - Staff Panel')

@section('content')

{{-- PAGE HEADER --}}
<div style="margin-bottom:1.25rem; display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
    <div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline" style="background:var(--accent-color); color:var(--text-inverse); border-color:var(--accent-color); text-decoration:none; font-size:0.85rem; margin-bottom:0.75rem;">
            <i class="fa-solid fa-arrow-left" style="color:var(--text-inverse);"></i> Back to Orders Pipeline
        </a>
        <h1 style="font-size:1.75rem; font-weight:800; color:var(--text-main); margin:0; font-family:'Outfit',sans-serif;">
            Order ID <span style="color:var(--text-accent);">#{{ $order->order_number }}</span>
        </h1>
        <div style="font-size:0.85rem; color:var(--text-muted); margin-top:0.25rem;">
            Customer: <strong style="color:var(--text-main);">{{ $order->customer_name }}</strong>
            @if($order->is_walkin)
                <span style="background:var(--success-soft); color:var(--text-success); border:1px solid var(--success-border); font-size:0.7rem; font-weight:700; padding:0.1rem 0.5rem; border-radius:9999px; margin-left:0.35rem;">
                    <i class="fa-solid fa-person-walking"></i> Walk-in
                </span>
            @endif
            @if($order->is_parent)
                <span style="background:var(--accent-soft); color:var(--text-accent); border:1px solid var(--info-border); font-size:0.7rem; font-weight:700; padding:0.1rem 0.5rem; border-radius:9999px; margin-left:0.35rem;">
                    <i class="fa-solid fa-code-fork"></i> Split Order
                </span>
            @endif
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
        {{-- Pickup/Delivery badge (normal orders only — split orders show it on the tab cards) --}}
        @if(!($order->is_parent && $order->subOrders->isNotEmpty()))
            @php $hdrPickup = $order->fulfillment_type !== 'delivery'; @endphp
            <span style="display:inline-flex; align-items:center; gap:0.35rem; font-size:0.75rem; font-weight:800; padding:0.4rem 0.85rem; border-radius:9999px; background:{{ $hdrPickup ? 'var(--success-soft)' : 'var(--accent-soft)' }}; color:{{ $hdrPickup ? 'var(--text-success)' : 'var(--text-accent)' }}; border:1.5px solid {{ $hdrPickup ? 'var(--success-border)' : 'var(--info-border)' }}; white-space:nowrap;">
                <i class="fa-solid {{ $hdrPickup ? 'fa-store' : 'fa-truck-fast' }}" style="font-size:0.7rem;"></i>
                {{ $hdrPickup ? 'Pickup' : 'Delivery' }}
            </span>
        @endif
        <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn btn-outline" style="background:var(--accent-color); color:var(--text-inverse); border-color:var(--accent-color); text-decoration:none; font-size:0.85rem;">
            <i class="fa-solid fa-print" style="color:var(--text-inverse);"></i> Print Invoice
        </a>
    </div>
</div>

{{-- ─── DETERMINE WHICH ORDERS TO SHOW AS TABS ─────────────────────────────── --}}
@php
    $isSplit   = $order->is_parent && $order->subOrders->isNotEmpty();
    $tabOrders = $isSplit ? $order->subOrders : collect([$order]);
    $firstId   = $tabOrders->first()->id;
@endphp

{{-- ─── TAB BAR (only for split/parent orders) — redesigned card tabs ─────── --}}
<style>
.gsub-tab {
    display: inline-flex; flex-direction: column; align-items: flex-start; gap: 0.35rem;
    padding: 0.7rem 1.1rem; background: var(--bg-surface);
    border: 1.5px solid var(--border-color); border-radius: 12px;
    cursor: pointer; min-width: 215px; text-align: left;
    transition: all 0.18s ease; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.gsub-tab:hover {
    border-color: var(--accent-color); transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0, 153, 255, 0.10);
}
.gsub-tab.active {
    border-color: var(--accent-color);
    background: linear-gradient(135deg, var(--accent-soft) 0%, var(--accent-soft) 100%);
    box-shadow: 0 0 0 3px rgba(0, 153, 255, 0.14), 0 4px 10px rgba(0, 153, 255, 0.10);
}
.gsub-tab .gt-row { display: flex; align-items: center; gap: 0.45rem; width: 100%; }
.gsub-tab .gt-num { font-weight: 800; font-size: 0.85rem; color: var(--text-main); font-family: 'Outfit', sans-serif; letter-spacing: 0.01em; }
.gsub-tab.active .gt-num { color: var(--text-accent); }
.gsub-tab .gt-badge { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.66rem; font-weight: 800; padding: 0.14rem 0.55rem; border-radius: 9999px; white-space: nowrap; }
.gsub-tab .gt-status { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.68rem; font-weight: 700; color: var(--text-muted); white-space: nowrap; }
.gsub-tab .gt-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
</style>
@if($isSplit)
@php
    $tabStatusMeta = [
        'pending'       => ['#D97706', '#FEF3C7'],
        'design_review' => ['#7C3AED', '#EDE9FE'],
        'approved'      => ['#2563EB', '#DBEAFE'],
        'printing'      => ['#0284C7', '#E0F2FE'],
        'ready'         => ['#059669', '#D1FAE5'],
        'shipped'       => ['#059669', '#D1FAE5'],
        'completed'     => ['#059669', '#D1FAE5'],
        'cancelled'     => ['#DC2626', '#FEE2E2'],
    ];
@endphp
<div style="margin-bottom:1.5rem;">
    <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:0.65rem;">
        <span style="font-size:0.72rem; font-weight:800; color:var(--text-subtle); text-transform:uppercase; letter-spacing:0.08em; display:inline-flex; align-items:center; gap:0.4rem; white-space:nowrap;">
            <i class="fa-solid fa-code-fork" style="color:var(--text-accent);"></i> Sub-Orders
        </span>
        <span style="flex:1; height:1px; background:var(--text-disabled);"></span>
    </div>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        @foreach($tabOrders as $i => $tab)
        @php
            list($sc, $sbg) = $tabStatusMeta[$tab->status] ?? ['#64748B', '#F1F5F9'];
            $isPickup = $tab->fulfillment_type !== 'delivery';
        @endphp
        <button id="tab-btn-{{ $tab->id }}" onclick="showTab({{ $tab->id }})" type="button"
            class="gsub-tab {{ $i === 0 ? 'active' : '' }}">
            <span class="gt-row">
                <span style="font-size:0.95rem; line-height:1;">{{ $tab->is_custom_order ? '🎨' : '📦' }}</span>
                <span class="gt-num">#{{ $tab->order_number }}</span>
            </span>
            <span class="gt-row">
                <span class="gt-badge" style="background:{{ $isPickup ? 'var(--success-soft)' : 'var(--accent-soft)' }}; color:{{ $isPickup ? 'var(--text-success)' : 'var(--text-accent)' }}; border:1px solid {{ $isPickup ? 'var(--success-border)' : 'var(--info-border)' }};">
                    <i class="fa-solid {{ $isPickup ? 'fa-store' : 'fa-truck-fast' }}" style="font-size:0.6rem;"></i>
                    {{ $isPickup ? 'Pickup' : 'Delivery' }}
                </span>
                <span class="gt-status">
                    <span class="gt-dot" style="background:{{ $sc }}; box-shadow:0 0 0 3px {{ $sbg }};"></span>
                    {{ $tab->is_custom_order ? 'Custom' : 'Regular' }} · {{ ucwords(str_replace('_', ' ', $tab->status)) }}
                </span>
            </span>
        </button>
        @endforeach
    </div>
</div>
@endif

{{-- ─── PANELS (one per tab order) ─────────────────────────────────────────── --}}
@foreach($tabOrders as $i => $sub)
<div id="tab-panel-{{ $sub->id }}" style="display:{{ $i === 0 ? 'block' : 'none' }};">

    <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.75rem; align-items:start;">

        {{-- ══ LEFT COLUMN ══ --}}
        <div>

            {{-- TRACKER --}}
            <div class="card" style="padding:1.5rem 1.75rem; margin-bottom:1.25rem;">
                <h3 style="font-size:0.95rem; font-weight:800; color:var(--text-main); margin:0 0 1.5rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="fa-solid fa-diagram-project" style="color:var(--text-accent);"></i> Status Tracker
                    <span style="margin-left:auto; font-size:0.7rem; font-weight:700; padding:0.18rem 0.6rem; border-radius:9999px; background:{{ $sub->is_custom_order ? 'var(--brand-magenta-soft)' : 'var(--success-soft)' }}; color:{{ $sub->is_custom_order ? 'var(--text-magenta)' : 'var(--text-success)' }}; border:1px solid {{ $sub->is_custom_order ? 'var(--brand-magenta-soft)' : 'var(--success-border)' }};">
                        {{ $sub->is_custom_order ? '🎨 Custom' : '📦 Regular' }}
                    </span>
                </h3>
                @php
                    $tDelivery = $sub->fulfillment_type === 'delivery';
                    $tCustom   = (bool) $sub->is_custom_order;
                    if ($tCustom) {
                        $tStages = [
                            ['label'=>'Pending',       'desc'=>'Awaiting payment',          'bom'=>false],
                            ['label'=>'Design Review', 'desc'=>'Artwork under review',       'bom'=>false],
                            ['label'=>'Approved',      'desc'=>'Stock Deducted',           'bom'=>true],
                            ['label'=>'Printing',      'desc'=>'In production',              'bom'=>false],
                            ['label'=>$tDelivery?'Ready to Ship':'Ready for Pickup', 'desc'=>$tDelivery?'Courier dispatch':'Store counter', 'bom'=>false],
                            ['label'=>'Completed',     'desc'=>'Order fulfilled',            'bom'=>false],
                        ];
                        $tStepMap = ['pending'=>0,'design_review'=>1,'approved'=>2,'printing'=>3,'in_production'=>3,'ready'=>4,'ready_for_pickup'=>4,'shipped'=>4,'completed'=>5];
                    } else {
                        $tStages = [
                            ['label'=>'Pending',  'desc'=>'Order received',      'bom'=>false],
                            ['label'=>'Approved', 'desc'=>'Stock Deducted',    'bom'=>true],
                            ['label'=>$tDelivery?'Ready for Delivery':'Ready for Pickup', 'desc'=>$tDelivery?'Out for delivery':'Store counter', 'bom'=>false],
                            ['label'=>'Completed','desc'=>'Order fulfilled',     'bom'=>false],
                        ];
                        $tStepMap = ['pending'=>0,'approved'=>1,'printing'=>1,'in_production'=>1,'ready'=>2,'ready_for_pickup'=>2,'shipped'=>2,'completed'=>3];
                    }
                    $tCurrent = $sub->status === 'cancelled' ? -1 : ($tStepMap[$sub->status] ?? 0);
                    $tTotal   = count($tStages);
                @endphp

                @if($sub->status === 'cancelled')
                    @php
                        $refundPill = match ($sub->refund_status) {
                            'refunded'      => ['bg' => '#F0FDF4', 'color' => '#15803D', 'border' => '#BBF7D0', 'label' => 'Refunded'],
                            'in_progress'   => ['bg' => '#FEF9C3', 'color' => '#854D0E', 'border' => '#FDE68A', 'label' => 'Refund in progress'],
                            'requested'     => ['bg' => '#FFEDD5', 'color' => '#9A3412', 'border' => '#FED7AA', 'label' => 'Refund requested'],
                            'rejected'      => ['bg' => '#FDF2F8', 'color' => '#BE185D', 'border' => '#FBCFE8', 'label' => 'Refund rejected'],
                            default         => null,
                        };

                        $reqAt = $sub->cancellation_requested_at;
                        $canAt = $sub->cancelled_at;
                        $cancelRows = [];
                        if ($canAt) $cancelRows[] = ['Cancelled on', $canAt->format('M d, Y h:i A')];
                        if ($reqAt && (!$canAt || $reqAt->diffInSeconds($canAt) > 60)) $cancelRows[] = ['Requested on', $reqAt->format('M d, Y h:i A')];
                        $cancelRows[] = ['Payment', ucwords(str_replace('_', ' ', (string) $sub->payment_status))];
                        if (!empty($sub->refund_amount) && $sub->refund_amount > 0) $cancelRows[] = ['Refund amount', '₱' . number_format((float) $sub->refund_amount, 2)];
                        if ($sub->cancellation_admin_notes) $cancelRows[] = ['Staff notes', $sub->cancellation_admin_notes];
                    @endphp
                    <div style="border:1.5px solid var(--danger-border); border-radius:10px; overflow:hidden;">
                        <div style="background:var(--danger-soft); padding:0.85rem 1.1rem; display:flex; align-items:center; gap:0.7rem; flex-wrap:wrap;">
                            <span style="width:32px; height:32px; border-radius:50%; background:var(--danger-color); color:var(--text-inverse); display:flex; align-items:center; justify-content:center; flex:none;">
                                <i class="fa-solid fa-circle-xmark" style="font-size:0.95rem;"></i>
                            </span>
                            <div style="flex:1; min-width:150px;">
                                <div style="font-size:0.95rem; font-weight:800; color:var(--text-danger); line-height:1.3;">Order cancelled</div>
                                <div style="font-size:0.78rem; font-weight:600; color:var(--text-danger); line-height:1.35;">
                                    {{ $sub->cancellation_reason ?: 'No reason recorded' }}
                                </div>
                            </div>
                            <div style="display:flex; gap:0.4rem; flex-wrap:wrap; justify-content:flex-end;">
                                @if($refundPill)
                                    <span style="font-size:0.68rem; font-weight:800; text-transform:uppercase; letter-spacing:0.03em; padding:0.2rem 0.55rem; border-radius:9999px; background:{{ $refundPill['bg'] }}; color:{{ $refundPill['color'] }}; border:1px solid {{ $refundPill['border'] }};">{{ $refundPill['label'] }}</span>
                                @endif
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:0.7rem 1rem; padding:0.9rem 1.1rem; background:var(--bg-surface);">
                            @foreach($cancelRows as $crow)
                                <div>
                                    <div style="font-size:0.65rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:var(--text-subtle); margin-bottom:0.15rem;">{{ $crow[0] }}</div>
                                    <div style="font-size:0.82rem; font-weight:700; color:var(--text-main); line-height:1.35;">{{ $crow[1] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div style="display:grid; grid-template-columns:repeat({{ $tTotal }},1fr); gap:0.3rem; text-align:center; position:relative;">
                        @foreach($tStages as $ti => $ts)
                            @php $tdone = $tCurrent > $ti; $tcurr = $tCurrent === $ti; @endphp
                            <div style="display:flex; flex-direction:column; align-items:center; position:relative;">
                                @if($ti < $tTotal - 1)
                                    <div style="position:absolute; top:17px; left:calc(50% + 17px); width:calc(100% - 34px); height:2px; background:{{ $tdone ? 'var(--accent-color)' : 'var(--text-disabled)' }}; z-index:0;"></div>
                                @endif
                                <div style="position:relative; z-index:1; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.82rem; font-weight:800; margin-bottom:0.4rem; background:{{ ($tdone||$tcurr) ? 'var(--accent-color)' : 'var(--text-disabled)' }}; color:{{ ($tdone||$tcurr) ? 'var(--text-inverse)' : 'var(--text-subtle)' }}; {{ $tcurr ? 'box-shadow:0 0 0 5px rgba(0,153,255,0.15);' : '' }}">
                                    @if($tdone)<i class="fa-solid fa-check" style="font-size:0.75rem;"></i>@else{{ $ti+1 }}@endif
                                </div>
                                <div style="font-size:0.63rem; font-weight:{{ $tcurr?'800':'600' }}; text-transform:uppercase; letter-spacing:0.03em; color:{{ ($tdone||$tcurr)?'var(--text-accent)':'var(--text-subtle)' }}; line-height:1.25; margin-bottom:0.15rem;">{{ $ts['label'] }}</div>
                                @if($ts['bom'])
                                    <span style="font-size:0.55rem; font-weight:800; background:var(--warning-soft); color:var(--text-warning); border:1px solid var(--warning-border); padding:0.05rem 0.3rem; border-radius:3px; white-space:nowrap;">⚡ Stock Deducted</span>
                                @else
                                    <div style="font-size:0.6rem; color:var(--text-disabled);">{{ $ts['desc'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- UPDATE STATUS --}}
            <div class="card" style="padding:1.5rem; margin-bottom:1.25rem;">
                <h3 style="font-size:0.95rem; font-weight:800; color:var(--text-main); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem; padding-bottom:0.75rem; border-bottom:1px solid var(--border-color);">
                    <i class="fa-solid fa-arrows-rotate" style="color:var(--text-accent);"></i> Update Order Status
                </h3>
                <form action="{{ route('admin.orders.status', $sub->id) }}" method="POST" style="display:flex; gap:0.85rem; align-items:flex-end; flex-wrap:wrap;">
                    @csrf
                    @php
                        // A cancelled order is operationally closed — only Super Admin may reopen it.
                        $statusLocked = $sub->status === 'cancelled' && !Auth::user()->isSuperAdmin();
                    @endphp
                    <div style="flex:1; min-width:180px;">
                        <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Order Status</label>
                        @if($statusLocked)
                            {{-- disabled controls are not submitted, so carry the value in a hidden field --}}
                            <input type="hidden" name="status" value="cancelled">
                        @endif
                        <select name="status" class="custom-combo" {{ $statusLocked ? 'disabled' : '' }} style="width:100%; padding:0.6rem 0.85rem; border:1px solid var(--border-color); border-radius:9px; font-size:0.9rem; font-weight:700; background:var(--bg-surface); outline:none;">
                            <option value="pending" {{ $sub->status==='pending'?'selected':'' }}>Pending</option>
                            @if($sub->is_custom_order)
                            <option value="design_review" {{ $sub->status==='design_review'?'selected':'' }}>Design Review</option>
                            @endif
                            <option value="approved" {{ $sub->status==='approved'?'selected':'' }}>Approved ⚡ (Stock Deducted)</option>
                            @if($sub->is_custom_order)
                            <option value="printing" {{ $sub->status==='printing'?'selected':'' }}>Printing</option>
                            @endif
                            <option value="ready" {{ $sub->status==='ready'?'selected':'' }}>{{ $sub->fulfillment_type==='delivery'?'Ready for Delivery':'Ready for Pickup' }}</option>
                            <option value="completed" {{ $sub->status==='completed'?'selected':'' }}>Completed</option>
                            <option value="cancelled" {{ $sub->status==='cancelled'?'selected':'' }}>Cancelled</option>
                        </select>
                    </div>
                    <div style="min-width:160px;">
                        <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Payment Status</label>
                        <select name="payment_status" class="custom-combo" style="width:100%; padding:0.6rem 0.85rem; border:1px solid var(--border-color); border-radius:9px; font-size:0.9rem; font-weight:700; background:var(--bg-surface); outline:none;">
                            <option value="unpaid" {{ $sub->payment_status==='unpaid'?'selected':'' }}>Unpaid</option>
                            <option value="partially_paid" {{ $sub->payment_status==='partially_paid'?'selected':'' }}>Partially Paid</option>
                            <option value="paid" {{ $sub->payment_status==='paid'?'selected':'' }}>Paid (Full)</option>
                            <option value="refunded" {{ $sub->payment_status==='refunded'?'selected':'' }}>Refunded</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding:0.6rem 1.25rem; white-space:nowrap;">
                        Update Status
                    </button>
                </form>
                @if($sub->status === 'cancelled')
                    <div style="margin-top:0.7rem; font-size:0.76rem; font-weight:700; color:{{ $statusLocked ? 'var(--text-subtle)' : 'var(--text-accent)' }}; display:flex; align-items:center; gap:0.4rem;">
                        <i class="fa-solid {{ $statusLocked ? 'fa-lock' : 'fa-unlock' }}"></i>
                        {{ $statusLocked
                            ? 'Cancelled — the order status is locked. Use Payment Status to record a refund.'
                            : 'Cancelled — as Super Admin you can still change this status.' }}
                    </div>
                @endif
            </div>

            {{-- ARTWORK REVIEW (custom orders only — regular orders don't need customer uploads) --}}
            @if($sub->is_custom_order)
            <div class="card" style="padding:1.5rem; margin-bottom:1.25rem;">
                <h3 style="font-size:0.95rem; font-weight:800; color:var(--text-main); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem; padding-bottom:0.75rem; border-bottom:1px solid var(--border-color);">
                    <i class="fa-solid fa-file-image" style="color:var(--text-accent);"></i> Artwork Files Review
                </h3>
                @foreach($sub->items as $item)
                @php
                    $awImg = null;
                    if ($item->product) {
                        $awImg = optional($item->product->primaryImage)->image_path
                            ?? optional($item->product->images()->first())->image_path
                            ?? $item->product->image_path;
                    }
                @endphp
                <div style="background:var(--bg-page); border:1px solid var(--border-color); border-radius:10px; padding:1rem 1.1rem; margin-bottom:0.75rem;">
                    <div style="display:flex; align-items:center; gap:0.65rem; margin-bottom:0.35rem;">
                        <div style="width:44px; height:44px; border-radius:8px; overflow:hidden; background:var(--bg-surface); border:1px solid var(--border-color); flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                            @if($awImg)
                                <img src="{{ asset('storage/' . $awImg) }}" alt="{{ $item->product?->name }}" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <i class="fa-solid fa-image" style="color:var(--text-disabled); font-size:1rem;"></i>
                            @endif
                        </div>
                        <div>
                            <strong style="color:var(--text-main);">{{ $item->product?->name ?? 'Item' }}</strong>
                            <span style="font-size:0.8rem; color:var(--text-subtle);"> × {{ $item->quantity }}</span>
                        </div>
                    </div>
                    @forelse($item->files as $file)
                        <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:8px; padding:0.85rem; margin-top:0.65rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.6rem;">
                                <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" class="btn btn-outline" style="font-size:0.78rem; padding:0.3rem 0.7rem;">
                                    <i class="fa-solid fa-download"></i> File #{{ $file->id }}
                                </a>
                                <span style="font-size:0.72rem; font-weight:800; padding:0.2rem 0.55rem; border-radius:9999px; background:{{ $file->status==='needs_revision'?'var(--danger-soft)':'var(--warning-soft)' }}; color:{{ $file->status==='needs_revision'?'var(--text-danger)':'var(--text-warning)' }};">
                                    {{ str_replace('_',' ', $file->status) }}
                                </span>
                            </div>
                            <form action="{{ route('admin.orders.files.review', $file->id) }}" method="POST">
                                @csrf
                                <div style="display:grid; grid-template-columns:1fr 2fr auto; gap:0.6rem; align-items:center;">
                                    <select name="status" style="padding:0.45rem 0.65rem; border:1px solid var(--border-color); border-radius:7px; font-size:0.82rem; background:var(--bg-surface);">
                                        <option value="needs_revision" {{ $file->status==='needs_revision'?'selected':'' }}>Needs Revision</option>
                                        <option value="pending" {{ $file->status==='pending'?'selected':'' }}>Pending</option>
                                    </select>
                                    <input type="text" name="admin_reply" value="{{ $file->admin_reply }}" placeholder="Staff reply / concern..." required style="padding:0.45rem 0.7rem; border:1px solid var(--border-color); border-radius:7px; font-size:0.82rem; width:100%; outline:none;">
                                    <button type="submit" class="btn btn-primary" style="padding:0.45rem 0.85rem; font-size:0.8rem; white-space:nowrap;">Send</button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <p style="font-size:0.82rem; color:var(--text-subtle); margin:0.5rem 0 0;">No artwork file uploaded yet.</p>
                    @endforelse
                </div>
                @endforeach
            </div>
            @endif

            {{-- CHAT (hidden for walk-in orders) --}}
            @if(!$order->is_walkin)
            <div class="card" style="padding:1.5rem;">
                <h3 style="font-size:0.95rem; font-weight:800; color:var(--text-main); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem; padding-bottom:0.75rem; border-bottom:1px solid var(--border-color);">
                    <i class="fa-solid fa-comments" style="color:var(--text-accent);"></i> Customer Support Chat
                </h3>
                <div id="chat-box-{{ $sub->id }}" style="max-height:280px; overflow-y:auto; display:flex; flex-direction:column; gap:0.75rem; margin-bottom:1rem; scroll-behavior:smooth;">
                    @forelse($sub->messages as $msg)
                        @php $isMe = $msg->user_id===Auth::id()||($msg->sender&&in_array($msg->sender->role,['admin','super_admin'])); @endphp
                        <div style="align-self:{{ $isMe?'flex-end':'flex-start' }}; max-width:80%; background:{{ $isMe?'var(--accent-soft)':'var(--bg-page)' }}; border:1px solid {{ $isMe?'var(--accent-color)':'var(--border-color)' }}; padding:0.65rem 0.9rem; border-radius:10px;">
                            <div style="font-size:0.72rem; font-weight:700; color:{{ $isMe?'var(--text-accent)':'var(--text-muted)' }}; margin-bottom:0.2rem;">
                                {{ $msg->sender?->name ?? 'User' }} · {{ $msg->created_at->timezone('Asia/Manila')->format('M d, h:i A') }}
                            </div>
                            @if($msg->message)<div style="font-size:0.88rem; color:var(--text-main); line-height:1.4; word-break:break-word;">{{ $msg->message }}</div>@endif
                            @if($msg->attachment_path)
                                <a href="{{ asset('storage/'.$msg->attachment_path) }}" target="_blank">
                                    <img src="{{ asset('storage/'.$msg->attachment_path) }}" style="max-width:100%; max-height:180px; border-radius:7px; margin-top:0.4rem; object-fit:contain; display:block; border:1px solid var(--border-color);">
                                </a>
                            @endif
                        </div>
                    @empty
                        <p id="chat-empty-{{ $sub->id }}" style="font-size:0.85rem; color:var(--text-subtle); text-align:center; margin:0;">No messages yet.</p>
                    @endforelse
                </div>
                <form id="chat-form-{{ $sub->id }}" action="{{ route('admin.orders.chat', $sub->id) }}" method="POST" enctype="multipart/form-data" data-order-id="{{ $sub->id }}">
                    @csrf
                    <div id="chat-file-preview-{{ $sub->id }}" style="display:none; align-items:center; gap:0.5rem; background:var(--bg-page); border:1px solid var(--border-color); border-radius:8px; padding:0.4rem 0.6rem; margin-bottom:0.5rem; font-size:0.78rem; color:var(--text-muted);">
                        <i class="fa-solid fa-paperclip" style="color:var(--text-accent);"></i>
                        <span id="chat-file-name-{{ $sub->id }}" style="flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                        <button type="button" id="chat-file-clear-{{ $sub->id }}" title="Remove file" style="background:none; border:none; cursor:pointer; color:var(--text-danger); padding:0 0.25rem; font-size:0.85rem;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div style="display:flex; gap:0.5rem; align-items:center;">
                        <label for="chat-file-{{ $sub->id }}" title="Attach an image or file" style="display:flex; align-items:center; justify-content:center; width:42px; height:42px; border:1px solid var(--accent-color); border-radius:9px; cursor:pointer; color:var(--text-inverse); background:var(--accent-color); flex-shrink:0; transition:all 0.15s;" onmouseover="this.style.background='#0080FF'; this.style.borderColor='#0080FF';" onmouseout="this.style.background='#0099FF'; this.style.borderColor='#0099FF';">
                            <i class="fa-solid fa-paperclip" style="color:var(--text-inverse);"></i>
                        </label>
                        <input id="chat-file-{{ $sub->id }}" type="file" name="attachment" accept="image/png,image/jpeg,image/gif,image/webp,application/pdf,application/zip" style="display:none;">
                        <input id="chat-input-{{ $sub->id }}" type="text" name="message" placeholder="Reply to customer..." style="flex:1; padding:0.6rem 0.9rem; border:1px solid var(--border-color); border-radius:9px; font-size:0.88rem; outline:none;">
                        <button id="chat-btn-{{ $sub->id }}" type="submit" class="btn btn-primary" style="padding:0.6rem 1.1rem; white-space:nowrap;">
                            <i class="fa-solid fa-paper-plane"></i> Reply
                        </button>
                    </div>
                </form>
            </div>
            @endif

        </div>{{-- end left --}}

        {{-- ══ RIGHT COLUMN ══ --}}
        <div>

            {{-- ORDER ITEMS --}}
            <div class="card" style="padding:1.25rem; margin-bottom:1.25rem;">
                <h3 style="font-size:0.92rem; font-weight:800; color:var(--text-main); margin:0 0 0.85rem; display:flex; align-items:center; gap:0.45rem; padding-bottom:0.65rem; border-bottom:1px solid var(--border-color);">
                    <i class="fa-solid fa-boxes-stacked" style="color:var(--text-accent);"></i> Order Items
                </h3>
                @foreach($sub->items as $item)
                @php
                    $pImg = null;
                    if ($item->product) {
                        $pImg = optional($item->product->primaryImage)->image_path
                            ?? optional($item->product->images()->first())->image_path
                            ?? $item->product->image_path;
                    }
                @endphp
                <div style="background:var(--bg-page); border:1px solid var(--border-color); border-radius:9px; padding:0.75rem 0.9rem; margin-bottom:0.5rem; display:flex; gap:0.75rem; align-items:flex-start;">
                    <div style="width:52px; height:52px; border-radius:8px; overflow:hidden; background:var(--bg-surface); border:1px solid var(--border-color); flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                        @if($pImg)
                            <img src="{{ asset('storage/' . $pImg) }}" alt="{{ $item->product?->name }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <i class="fa-solid fa-image" style="color:var(--text-disabled); font-size:1.1rem;"></i>
                        @endif
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; justify-content:space-between; gap:0.5rem;">
                            <div style="flex:1; min-width:0;">
                                <div style="font-weight:800; color:var(--text-main); font-size:0.88rem;">{{ $item->product?->name ?? 'Product' }}</div>
                                @if(!empty($item->options_selected))
                                    <div style="display:flex; flex-wrap:wrap; gap:0.2rem; margin-top:0.25rem;">
                                        @foreach($item->options_selected as $k=>$v)
                                            <span style="background:var(--accent-soft); color:var(--text-accent); font-size:0.65rem; font-weight:700; padding:0.1rem 0.4rem; border-radius:4px;">{{ $k }}: {{ $v }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <div style="font-size:0.72rem; color:var(--text-subtle); margin-top:0.2rem;">₱{{ number_format($item->unit_price,2) }} × {{ $item->quantity }}</div>
                            </div>
                            <div style="font-weight:800; color:var(--text-main); font-size:0.92rem; white-space:nowrap;">₱{{ number_format($item->subtotal,2) }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
                {{-- Total --}}
                <div style="background:var(--bg-page); border:1px solid var(--border-color); border-radius:9px; padding:0.75rem 0.9rem; margin-top:0.5rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.82rem; color:var(--text-muted); font-weight:600;">Total:</span>
                        <span style="font-weight:800; color:var(--text-main); font-family:'Outfit',sans-serif;">₱{{ number_format($sub->total_price ?: $sub->total_amount, 2) }}</span>
                    </div>
                    @if($sub->is_downpayment)
                    <div style="display:flex; justify-content:space-between; font-size:0.78rem; margin-top:0.3rem; color:var(--text-accent);">
                        <span>Downpayment (50%):</span><span style="font-weight:700;">₱{{ number_format($sub->downpayment_amount,2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:0.78rem; margin-top:0.15rem; padding-top:0.25rem; border-top:1px dashed var(--border-color); color:var(--text-danger);">
                        <span style="font-weight:700;">Remaining Balance:</span><span style="font-weight:800;">₱{{ number_format($sub->remaining_balance,2) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- PAYMENT --}}
            <div class="card" style="padding:1.25rem; margin-bottom:1.25rem; border:1.5px solid var(--info-border); background:var(--accent-soft);">
                <h3 style="font-size:0.92rem; font-weight:800; color:var(--text-main); margin:0 0 0.85rem; display:flex; align-items:center; gap:0.45rem; padding-bottom:0.65rem; border-bottom:1px solid var(--info-border);">
                    <i class="fa-solid fa-credit-card" style="color:var(--text-accent);"></i> Payment & Receipt
                </h3>
                @php
                    $pm = $sub->effective_payment_method; $pp = $sub->payment_proof_path;
                    $pmLower = strtolower($pm);
                    $isCash = (str_contains($pmLower, 'cash on') || str_contains($pmLower, 'cod') || trim($pmLower) === 'cash' || str_contains($pmLower, 'pickup') || str_contains($pmLower, 'cop'));
                @endphp
                <div style="font-size:0.85rem; margin-bottom:0.6rem; color:var(--text-muted);">
                    Mode: <strong style="color:var(--text-main);"><i class="fa-solid fa-wallet" style="color:var(--text-accent);"></i> {{ $pm }}</strong>
                </div>
                <div style="font-size:0.85rem; margin-bottom:0.6rem; color:var(--text-muted);">
                    Term: <strong style="color:var(--text-main);">{{ $sub->formatted_payment_plan }}</strong>
                </div>
                <div style="font-size:0.85rem; margin-bottom:0.85rem; color:var(--text-muted);">
                    Payment: <strong style="color:{{ $sub->payment_status==='paid'?'var(--text-success)':($sub->payment_status==='partially_paid'?'var(--text-accent)':($sub->payment_status==='cancelled'?'var(--text-muted)':'var(--text-warning)')) }}; text-transform:uppercase;">
                        {{ str_replace('_',' ',$sub->payment_status) }}
                    </strong>
                </div>
                {{-- Proof screenshot --}}
                @if($pp)
                    @if(!$isCash)
                    <div style="border:1px solid var(--border-color); border-radius:8px; overflow:hidden; background:var(--bg-surface); text-align:center;">
                        <a href="{{ asset('storage/'.$pp) }}" target="_blank">
                            <img src="{{ asset('storage/'.$pp) }}" alt="Receipt" style="width:100%; max-height:180px; object-fit:contain; background:var(--text-main); padding:0.4rem; display:block;">
                        </a>
                        <div style="padding:0.45rem 0.75rem; background:var(--bg-page); border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:0.72rem; font-weight:700; color:var(--text-success);"><i class="fa-solid fa-circle-check"></i> Attached Receipt</span>
                            <a href="{{ asset('storage/'.$pp) }}" download class="btn btn-outline" style="font-size:0.7rem; padding:0.2rem 0.5rem;"><i class="fa-solid fa-download"></i></a>
                        </div>
                    </div>
                    {{-- Staff may replace the uploaded screenshot (admin / super admin) --}}
                    <form action="{{ route('admin.orders.payment-proof', $sub->id) }}" method="POST" enctype="multipart/form-data" style="margin-top:0.7rem;">
                        @csrf
                        <input type="file" name="proof_of_payment" id="proof_input-{{ $sub->id }}" accept=".jpg,.jpeg,.jfif,.png,.gif,.webp,.bmp" required style="display:none;">
                        <div id="proof_drop-{{ $sub->id }}" role="button" tabindex="0" style="border:1.5px dashed var(--info-border); border-radius:8px; padding:0.85rem 0.6rem; background:var(--accent-soft); text-align:center; cursor:pointer; transition:all 0.15s;">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.15rem; color:var(--text-accent);"></i>
                            <div class="proof-drop-text" style="font-size:0.74rem; font-weight:700; color:var(--text-accent); margin-top:0.3rem;">Click to upload or drag &amp; drop</div>
                            <div style="font-size:0.65rem; color:var(--text-subtle); margin-top:0.15rem;">JPG / PNG / WEBP · max 10 MB · replaces the receipt above</div>
                        </div>
                        <img id="proof_preview-{{ $sub->id }}" alt="" style="display:none; max-height:110px; width:100%; object-fit:contain; margin-top:0.5rem; border:1px solid var(--border-color); border-radius:6px; background:var(--bg-surface); padding:0.25rem;">
                        <button type="submit" style="margin-top:0.5rem; width:100%; padding:0.45rem 0.8rem; background:var(--accent-color); color:var(--text-inverse); border:none; border-radius:7px; font-size:0.75rem; font-weight:800; cursor:pointer;"><i class="fa-solid fa-upload"></i> Upload Screenshot</button>
                    </form>
                    @else
                    <div style="background:var(--success-soft); border:1px solid var(--success-border); border-left:3px solid var(--success-color); border-radius:8px; padding:0.7rem 0.9rem; display:flex; align-items:center; gap:0.5rem;">
                        <i class="fa-solid fa-money-bill-wave" style="color:var(--text-success);"></i>
                        <div style="font-size:0.8rem; color:var(--text-success); font-weight:700;">Cash — settle upon pickup/delivery.</div>
                    </div>
                    @endif
                @else
                    @if($isCash)
                    {{-- Cash: no payment screenshot box shown --}}
                    @else
                    {{-- Staff may upload the online payment receipt on behalf of the customer --}}
                    <form action="{{ route('admin.orders.payment-proof', $sub->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="proof_of_payment" id="proof_input-{{ $sub->id }}" accept=".jpg,.jpeg,.jfif,.png,.gif,.webp,.bmp" required style="display:none;">
                        <div id="proof_drop-{{ $sub->id }}" role="button" tabindex="0" style="border:1.5px dashed var(--info-border); border-radius:8px; padding:0.9rem 0.6rem; background:var(--accent-soft); text-align:center; cursor:pointer; transition:all 0.15s;">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.15rem; color:var(--text-accent);"></i>
                            <div class="proof-drop-text" style="font-size:0.74rem; font-weight:700; color:var(--text-accent); margin-top:0.3rem;">Click to upload or drag &amp; drop</div>
                            <div style="font-size:0.65rem; color:var(--text-subtle); margin-top:0.15rem;">No payment screenshot yet · JPG / PNG / WEBP · max 10 MB</div>
                        </div>
                        <img id="proof_preview-{{ $sub->id }}" alt="" style="display:none; max-height:110px; width:100%; object-fit:contain; margin-top:0.5rem; border:1px solid var(--border-color); border-radius:6px; background:var(--bg-surface); padding:0.25rem;">
                        <button type="submit" style="margin-top:0.5rem; width:100%; padding:0.45rem 0.8rem; background:var(--accent-color); color:var(--text-inverse); border:none; border-radius:7px; font-size:0.75rem; font-weight:800; cursor:pointer;"><i class="fa-solid fa-upload"></i> Upload Screenshot</button>
                    </form>
                    @endif
                @endif

                {{-- Refund & Cancel — Super Admin only --}}
                @if(Auth::user()->isSuperAdmin() && !in_array($sub->status,['cancelled','completed']))
                <div style="margin-top:0.9rem; padding-top:0.85rem; border-top:1px solid var(--info-border);">
                    <form id="refundForm-{{ $sub->id }}" action="{{ route('super-admin.orders.refund', $sub->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="refund">
                        <button type="button" onclick="openRefundConfirmModal('{{ $sub->id }}', '{{ $sub->order_number }}')" style="width:100%; padding:0.6rem 1rem; background:var(--danger-soft); color:var(--text-danger); border:1.5px solid var(--danger-border); border-radius:9px; font-size:0.82rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.4rem; transition:all 0.15s;"
                            onmouseover="this.style.background='#FEE2E2';" onmouseout="this.style.background='#FEF2F2';">
                            <i class="fa-solid fa-rotate-left"></i> Refund & Cancel Order
                        </button>
                    </form>
                </div>
                @endif
            </div>

            {{-- BOM LOG --}}
            <div class="card" style="padding:1.25rem; margin-bottom:1.25rem;">
                <h3 style="font-size:0.92rem; font-weight:800; color:var(--text-main); margin:0 0 0.85rem; display:flex; align-items:center; gap:0.45rem; padding-bottom:0.65rem; border-bottom:1px solid var(--border-color);">
                    <i class="fa-solid fa-boxes-packing" style="color:var(--text-accent);"></i> Materials Log
                </h3>
                @forelse($sub->materialLogs->whereIn('type', ['auto_deduction', 'order_reversal'])->sortByDesc('created_at') as $log)
                    @php
                        $isReversal = $log->type === 'order_reversal';
                        $qty = (float) $log->quantity_deducted;
                    @endphp
                    <div style="border-bottom:1px solid var(--border-color); padding:0.5rem 0; font-size:0.82rem;">
                        <div style="font-weight:700; color:var(--text-main);">
                            {{ $log->material?->name ?? 'Material' }}
                            @if($isReversal)
                                <span style="font-size:0.6rem; font-weight:800; background:var(--success-soft); color:var(--text-success); border:1px solid var(--success-border); padding:0.05rem 0.35rem; border-radius:3px; margin-left:0.25rem;">REVERSED</span>
                            @endif
                        </div>
                        <div style="color:{{ $isReversal ? 'var(--text-success)' : 'var(--text-danger)' }}; font-weight:700;">
                            {{ $isReversal ? '+' : '-' }}{{ abs($qty) }} {{ $log->material?->unit ?? '' }}
                        </div>
                        <div style="font-size:0.72rem; color:var(--text-subtle);">{{ $log->created_at->timezone('Asia/Manila')->format('M d, h:i A') }}</div>
                    </div>
                @empty
                    <p style="font-size:0.82rem; color:var(--text-subtle); margin:0;">No deductions yet. Triggers on <strong>Approved</strong>.</p>
                @endforelse
            </div>

            {{-- CUSTOMER CONTACT --}}
            <div class="card" style="padding:1.25rem;">
                <h3 style="font-size:0.92rem; font-weight:800; color:var(--text-main); margin:0 0 0.85rem; display:flex; align-items:center; gap:0.45rem; padding-bottom:0.65rem; border-bottom:1px solid var(--border-color);">
                    <i class="fa-solid fa-user" style="color:var(--text-accent);"></i> Customer Contact
                </h3>
                <div style="font-size:0.88rem; line-height:1.85; color:var(--text-muted);">
                    <strong style="color:var(--text-main);">{{ $order->customer_name }}</strong><br>
                    @if($order->customer_email)<i class="fa-solid fa-envelope" style="color:var(--text-subtle); width:14px;"></i> {{ $order->customer_email }}<br>@endif
                    @if($order->customer_contact)<i class="fa-solid fa-phone" style="color:var(--text-subtle); width:14px;"></i> {{ $order->customer_contact }}<br>@endif
                    @if($order->customer_address)<i class="fa-solid fa-location-dot" style="color:var(--text-subtle); width:14px;"></i> {{ $order->customer_address }}@endif
                </div>
            </div>

        </div>{{-- end right --}}

    </div>{{-- end grid --}}

</div>{{-- end panel --}}
@endforeach

{{-- ── CUSTOM MODERN MODAL: CANCEL & REFUND ORDER ────────────────────────── --}}
<div id="refundConfirmModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1.25rem;">
    <div style="background: var(--bg-surface); border-radius: 16px; width: 100%; max-width: 440px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.08); overflow: hidden; border: 1px solid var(--border-color); animation: modalSlideIn 0.2s ease-out;">
        
        <div style="padding: 1.75rem 1.75rem 1.25rem; text-align: center;">
            {{-- Warning / Alert Icon (Red / Amber) --}}
            <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--danger-soft); border: 2px solid var(--danger-border); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.25rem;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.75rem; color: var(--text-danger);"></i>
            </div>

            {{-- Title --}}
            <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.6rem; font-family: 'Outfit', sans-serif;">
                Cancel &amp; Refund Order
            </h3>

            {{-- Body Text --}}
            <p id="refundModalBodyText" style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.55; margin: 0;">
                Are you sure you want to issue a refund and cancel order <strong id="refundModalOrderNum" style="color: var(--text-main);">#GS-00000</strong>? This action cannot be undone.
            </p>
        </div>

        {{-- Modal Actions --}}
        <div style="background: var(--bg-page); border-top: 1px solid var(--border-color); padding: 1rem 1.5rem; display: flex; gap: 0.75rem; justify-content: flex-end;">
            {{-- Gray / Outline Button: No, Keep Order --}}
            <button type="button" onclick="closeRefundConfirmModal()" style="flex: 1; padding: 0.7rem 1.1rem; background: var(--bg-surface); color: var(--text-muted); border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.background='var(--bg-subtle)';" onmouseout="this.style.background='var(--bg-surface)';">
                No, Keep Order
            </button>

            {{-- Primary Danger Button: Yes, Refund & Cancel --}}
            <button type="button" id="confirmRefundSubmitBtn" onclick="submitRefundConfirmed()" style="flex: 1; padding: 0.7rem 1.1rem; background: var(--danger-color); color: var(--text-inverse); border: 1.5px solid var(--danger-color); border-radius: 10px; font-size: 0.9rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.45rem; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25); transition: all 0.15s;" onmouseover="this.style.background='#B91C1C';" onmouseout="this.style.background='#DC2626';">
                <i class="fa-solid fa-rotate-left"></i> Yes, Refund &amp; Cancel
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(-12px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

@endsection

@push('scripts')
<script>
var activeRefundOrderId = null;

function openRefundConfirmModal(orderId, orderNumber) {
    activeRefundOrderId = orderId;
    var orderNumEl = document.getElementById('refundModalOrderNum');
    if (orderNumEl) {
        orderNumEl.innerText = '#' + (orderNumber || orderId);
    }
    var modal = document.getElementById('refundConfirmModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeRefundConfirmModal() {
    var modal = document.getElementById('refundConfirmModal');
    if (modal) {
        modal.style.display = 'none';
    }
    activeRefundOrderId = null;
}

function submitRefundConfirmed() {
    if (!activeRefundOrderId) return;
    var form = document.getElementById('refundForm-' + activeRefundOrderId);
    var submitBtn = document.getElementById('confirmRefundSubmitBtn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing Refund...';
    }
    if (form) {
        form.submit();
    }
}

// Close on backdrop click or escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRefundConfirmModal();
});
document.addEventListener('click', function(e) {
    var modal = document.getElementById('refundConfirmModal');
    if (modal && e.target === modal) closeRefundConfirmModal();
});

function showTab(subId) {
    document.querySelectorAll('.tab-panel-item').forEach(function(p) { p.style.display = 'none'; });
    document.querySelectorAll('.gsub-tab').forEach(function(b) { b.classList.remove('active'); });
    var panel = document.getElementById('tab-panel-' + subId);
    var btn   = document.getElementById('tab-btn-' + subId);
    if (panel) panel.style.display = 'block';
    if (btn)   btn.classList.add('active');
}

// Fix: alias div IDs to class names for the tab switch
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[id^="tab-panel-"]').forEach(function(p) { p.classList.add('tab-panel-item'); });
    document.querySelectorAll('[id^="tab-btn-"]').forEach(function(b)   { b.classList.add('tab-btn-item'); });

    // Auto-scroll all chat boxes
    document.querySelectorAll('[id^="chat-box-"]').forEach(function(box) { box.scrollTop = box.scrollHeight; });

    // AJAX chat per sub-order
    document.querySelectorAll('form[id^="chat-form-"]').forEach(function(form) {
        var orderId = form.getAttribute('data-order-id');
        if (!orderId) return;
        var box   = document.getElementById('chat-box-' + orderId);
        var input = document.getElementById('chat-input-' + orderId);
        var btn   = document.getElementById('chat-btn-' + orderId);
        var fileInput   = document.getElementById('chat-file-' + orderId);
        var filePreview = document.getElementById('chat-file-preview-' + orderId);
        var fileName    = document.getElementById('chat-file-name-' + orderId);
        if (!box || !input || !btn) return;

        // Show/hide the selected file name preview
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (!filePreview || !fileName) return;
                if (fileInput.files.length > 0) {
                    fileName.textContent = fileInput.files[0].name;
                    filePreview.style.display = 'flex';
                } else {
                    filePreview.style.display = 'none';
                    fileName.textContent = '';
                }
            });
        }
        var clearChatFile = function() {
            if (fileInput)   fileInput.value = '';
            if (filePreview) filePreview.style.display = 'none';
            if (fileName)    fileName.textContent = '';
        };
        var fileClearBtn = document.getElementById('chat-file-clear-' + orderId);
        if (fileClearBtn) fileClearBtn.addEventListener('click', clearChatFile);

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var msg = input.value.trim();
            var hasFile = fileInput && fileInput.files.length > 0;
            if (!msg && !hasFile) return;
            var fd = new FormData(form);
            var orig = btn.innerHTML;
            btn.disabled = true; input.disabled = true; if (fileInput) fileInput.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('[name="_token"]')?.value || '';
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: fd
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    var empty = document.getElementById('chat-empty-' + orderId);
                    if (empty) empty.remove();
                    var bubble = document.createElement('div');
                    bubble.style.cssText = 'align-self:flex-end; max-width:80%; background:#EFF6FF; border:1px solid #93C5FD; padding:0.65rem 0.9rem; border-radius:10px;';
                    var html = '<div style="font-size:0.72rem; font-weight:700; color:var(--text-accent); margin-bottom:0.2rem;">' + data.data.sender_name + ' · ' + data.data.formatted_time + '</div>';
                    if (data.data.message) {
                        html += '<div style="font-size:0.88rem; color:var(--text-main); word-break:break-word;">' + data.data.message + '</div>';
                    }
                    if (data.data.attachment_url) {
                        var url = data.data.attachment_url;
                        if (/\.(png|jpe?g|gif|webp|bmp|svg)(\?.*)?$/i.test(url)) {
                            html += '<a href="' + url + '" target="_blank"><img src="' + url + '" style="max-width:100%; max-height:180px; border-radius:7px; margin-top:0.4rem; object-fit:contain; display:block; border:1px solid var(--border-color);"></a>';
                        } else {
                            html += '<a href="' + url + '" target="_blank" style="display:inline-flex; align-items:center; gap:0.35rem; margin-top:0.4rem; font-size:0.8rem; color:var(--text-accent); font-weight:700; text-decoration:none;"><i class="fa-solid fa-paperclip"></i> View attachment</a>';
                        }
                    }
                    bubble.innerHTML = html;
                    box.appendChild(bubble);
                    box.scrollTo({ top: box.scrollHeight, behavior: 'smooth' });
                    input.value = '';
                    clearChatFile();
                }
            })
            .catch(function() { alert('Failed to send. Please try again.'); })
            .finally(function() {
                btn.disabled = false; input.disabled = false; if (fileInput) fileInput.disabled = false;
                btn.innerHTML = orig; input.focus();
            });
        });
    });
});

// Click / drag-and-drop upload box for the staff payment-screenshot forms.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[id^="proof_input-"]').forEach(function (input) {
        var subId = input.id.replace('proof_input-', '');
        var drop = document.getElementById('proof_drop-' + subId);
        var preview = document.getElementById('proof_preview-' + subId);
        if (!drop) return;

        function showFile(file) {
            if (!file) return;
            var text = drop.querySelector('.proof-drop-text');
            if (text) text.textContent = file.name;
            if (preview && /^image\//.test(file.type)) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            } else if (preview) {
                preview.style.display = 'none';
                preview.removeAttribute('src');
            }
        }

        drop.addEventListener('click', function () { input.click(); });
        drop.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); }
        });
        input.addEventListener('change', function () { showFile(input.files && input.files[0]); });
        ['dragenter', 'dragover'].forEach(function (ev) {
            drop.addEventListener(ev, function (e) {
                e.preventDefault();
                drop.style.borderColor = '#0099FF';
                drop.style.background = '#EFF6FF';
            });
        });
        ['dragleave', 'dragend'].forEach(function (ev) {
            drop.addEventListener(ev, function () {
                drop.style.borderColor = '#BAE6FD';
                drop.style.background = '#F8FDFF';
            });
        });
        drop.addEventListener('drop', function (e) {
            e.preventDefault();
            drop.style.borderColor = '#BAE6FD';
            drop.style.background = '#F8FDFF';
            var file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (file) {
                input.files = e.dataTransfer.files;
                showFile(file);
            }
        });
    });
});
</script>
@if(session('auto_print_invoice'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    try { window.open("{{ session('auto_print_invoice') }}", "_blank"); } catch(e) {}
});
</script>
@endif

{{-- ─── CUSTOM COMBOBOX UPGRADE FOR STATUS SELECTS (keeps hidden native select for form submit) ─── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    function enhanceCombo(select) {
        if (select.dataset.comboDone) return;
        select.dataset.comboDone = '1';

        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        select.style.display = 'none';

        const btn = document.createElement('button');
        const locked = select.disabled;
        btn.type = 'button';
        btn.style.cssText = 'width:100%; display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.6rem 0.85rem; border:1px solid var(--border-strong); border-radius:9px; font-size:0.9rem; font-weight:700; background:var(--bg-surface); color:var(--text-main); cursor:pointer; text-align:left; transition:border-color 0.15s;' + (locked ? ' background:var(--bg-subtle); color:var(--text-muted); border-color:var(--border-color); cursor:not-allowed;' : '');
        const label = document.createElement('span');
        label.style.cssText = 'flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;';
        const caret = document.createElement('i');
        caret.className = locked ? 'fa-solid fa-lock' : 'fa-solid fa-chevron-down';
        caret.style.cssText = 'font-size:0.72rem; color:' + (locked ? 'var(--text-subtle)' : 'var(--text-muted)') + '; transition:transform 0.15s;';
        btn.appendChild(label); btn.appendChild(caret);
        wrapper.appendChild(btn);

        const panel = document.createElement('div');
        panel.className = 'combo-panel';
        panel.style.cssText = 'display:none; position:absolute; top:calc(100% + 4px); left:0; right:0; background:var(--bg-elevated); border:1px solid var(--border-color); border-radius:9px; box-shadow:0 8px 20px rgba(15,23,42,0.12); max-height:240px; overflow-y:auto; z-index:80; padding:0.25rem;';
        wrapper.appendChild(panel);

        function currentText() {
            const o = select.options[select.selectedIndex];
            return o ? o.textContent.trim() : '';
        }
        function render() {
            label.textContent = currentText();
            const cur = select.value;
            panel.innerHTML = '';
            Array.from(select.options).forEach(function (o) {
                const item = document.createElement('div');
                const selected = o.value === cur;
                item.textContent = o.textContent.trim();
                item.style.cssText = 'display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.5rem 0.65rem; border-radius:7px; font-size:0.88rem; font-weight:700; cursor:pointer; color:' + (selected ? 'var(--accent-contrast)' : 'var(--text-main)') + '; background:' + (selected ? 'var(--accent-color)' : 'var(--bg-elevated)') + ';';
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
                    select.value = o.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    render();
                    close();
                });
                panel.appendChild(item);
            });
        }
        function open() { panel.style.display = 'block'; caret.style.transform = 'rotate(180deg)'; btn.style.borderColor = 'var(--accent-color)'; }
        function close() { panel.style.display = 'none'; caret.style.transform = 'rotate(0deg)'; btn.style.borderColor = 'var(--border-strong)'; }
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (locked) return;
            if (panel.style.display === 'block') { close(); } else { open(); }
        });
        render();
    }

    document.querySelectorAll('select.custom-combo').forEach(enhanceCombo);

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
