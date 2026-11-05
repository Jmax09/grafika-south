{{--
    Shared Order Management Table Component
    Props:
        $orders      - Paginated Order collection
        $isSuperAdmin - bool — true when rendered in Super Admin context
        $indexRoute  - string — route name for filter/reset actions
--}}

{{-- ─── SEARCH & FILTER BAR ─────────────────────────────────────────── --}}
<style>
    .order-row {
        transition: all 0.2s ease !important;
    }
    .order-row:hover {
        background-color: var(--accent-soft) !important;
        box-shadow: inset 0 0 0 2px var(--accent-color) !important;
        position: relative;
        z-index: 5;
    }
    .order-row:active {
        background-color: var(--accent-soft) !important;
        box-shadow: inset 0 0 0 3px var(--accent-color) !important;
    }
</style>
<div class="card filter-card-sticky" style="padding: 0.75rem 1.15rem; margin-bottom: 1.1rem; position: sticky; top: 60px; z-index: 40; background: var(--bg-surface); border-radius: 14px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04); border: 1px solid var(--border-color); border-bottom: 2px solid var(--cmyk-cyan);">

    <!-- Top Row: Title + Total Counter -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.55rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.45rem; flex-wrap: wrap; gap: 0.5rem;">
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 8px; background: var(--accent-soft); color: var(--text-accent); font-size: 0.85rem;">
                <i class="fa-solid fa-receipt"></i>
            </span>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <h2 style="font-size: 1.12rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: 'Outfit', sans-serif; line-height: 1.2;">
                    Orders
                </h2>
                <span style="background: var(--bg-subtle); color: var(--text-muted); font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.5rem; border-radius: 9999px; border: 1px solid var(--border-color);">
                    {{ $orders->total() }} total
                </span>
            </div>
        </div>

        @if(request()->hasAny(['search', 'status', 'payment_status', 'fulfillment_type', 'order_type']))
            <a href="{{ route($indexRoute) }}" 
               style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.3rem 0.65rem; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-decoration: none; transition: all 0.15s;"
               onmouseover="this.style.borderColor='#94A3B8'; this.style.color='#0F172A';" 
               onmouseout="this.style.borderColor='#CBD5E1'; this.style.color='#475569';">
                <i class="fa-solid fa-rotate-left" style="color: var(--text-muted); font-size: 0.68rem;"></i> Clear Filters
            </a>
        @endif
    </div>

    <!-- Filter Controls Form (Unified Single Row, Auto-submit) -->
    <form action="{{ route($indexRoute) }}" method="GET"
          style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">

        <!-- Search Input with Search Button -->
        <div style="flex: 2; min-width: 210px; display: flex; align-items: center;">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search Order # or Customer..."
                   style="flex: 1; padding: 0.42rem 0.85rem; border: 1px solid {{ request('search') ? 'var(--accent-color)' : 'var(--border-color)' }}; border-left: {{ request('search') ? '3px solid var(--accent-color)' : '1px solid var(--border-color)' }}; border-right: none; border-radius: 8px 0 0 8px; font-size: 0.82rem; outline: none; background: {{ request('search') ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; color: var(--text-main); transition: all 0.2s;"
                   onfocus="this.style.borderColor='#0099FF'; this.style.borderLeft='3px solid #0099FF'; this.style.boxShadow='0 0 0 2px rgba(0, 153, 255, 0.15)';"
                   onblur="this.style.borderColor='{{ request('search') ? '#0099FF' : '#CBD5E1' }}'; this.style.borderLeft='{{ request('search') ? '3px solid #0099FF' : '1px solid #CBD5E1' }}'; this.style.boxShadow='none';">
            <button type="submit" 
                    style="padding: 0.42rem 0.95rem; background: var(--accent-color); color: var(--text-inverse); border: 1px solid var(--accent-color); border-radius: 0 8px 8px 0; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; white-space: nowrap; transition: background 0.15s;"
                    onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                <i class="fa-solid fa-magnifying-glass" style="font-size: 0.72rem;"></i> Search
            </button>
        </div>

        <!-- Status Filter -->
        <div style="flex: 1; min-width: 125px;">
            <select name="status" class="custom-combo"
                    style="width: 100%; padding: 0.42rem 0.75rem; border: 1px solid {{ request('status') ? 'var(--accent-color)' : 'var(--border-color)' }}; border-left: {{ request('status') ? '3px solid var(--accent-color)' : '1px solid var(--border-color)' }}; border-radius: 8px; font-size: 0.8rem; outline: none; background: {{ request('status') ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; color: {{ request('status') ? 'var(--text-accent)' : 'var(--text-main)' }}; {{ request('status') ? 'font-weight: 700;' : '' }} cursor: pointer; transition: all 0.2s;"
                    onchange="this.form.submit()"
                    onfocus="this.style.borderColor='#0099FF'; this.style.borderLeft='3px solid #0099FF'; this.style.boxShadow='0 0 0 2px rgba(0, 153, 255, 0.15)';"
                    onblur="this.style.borderColor='{{ request('status') ? '#0099FF' : '#CBD5E1' }}'; this.style.borderLeft='{{ request('status') ? '3px solid #0099FF' : '1px solid #CBD5E1' }}'; this.style.boxShadow='none';">
                <option value="">All Statuses</option>
                <option value="pending"       {{ request('status') == 'pending'       ? 'selected' : '' }}>Pending</option>
                <option value="design_review" {{ request('status') == 'design_review' ? 'selected' : '' }}>Design Review</option>
                <option value="approved"      {{ request('status') == 'approved'      ? 'selected' : '' }}>Approved</option>
                <option value="printing"      {{ request('status') == 'printing'      ? 'selected' : '' }}>Printing</option>
                <option value="ready"         {{ request('status') == 'ready'         ? 'selected' : '' }}>Ready / Shipped</option>
                <option value="completed"     {{ request('status') == 'completed'     ? 'selected' : '' }}>Completed</option>
                <option value="cancelled"     {{ request('status') == 'cancelled'     ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <!-- Payment Status Filter -->
        <div style="flex: 1; min-width: 125px;">
            <select name="payment_status" class="custom-combo"
                    style="width: 100%; padding: 0.42rem 0.75rem; border: 1px solid {{ request('payment_status') ? 'var(--accent-color)' : 'var(--border-color)' }}; border-left: {{ request('payment_status') ? '3px solid var(--accent-color)' : '1px solid var(--border-color)' }}; border-radius: 8px; font-size: 0.8rem; outline: none; background: {{ request('payment_status') ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; color: {{ request('payment_status') ? 'var(--text-accent)' : 'var(--text-main)' }}; {{ request('payment_status') ? 'font-weight: 700;' : '' }} cursor: pointer; transition: all 0.2s;"
                    onchange="this.form.submit()"
                    onfocus="this.style.borderColor='#0099FF'; this.style.borderLeft='3px solid #0099FF'; this.style.boxShadow='0 0 0 2px rgba(0, 153, 255, 0.15)';"
                    onblur="this.style.borderColor='{{ request('payment_status') ? '#0099FF' : '#CBD5E1' }}'; this.style.borderLeft='{{ request('payment_status') ? '3px solid #0099FF' : '1px solid #CBD5E1' }}'; this.style.boxShadow='none';">
                <option value="">All Payments</option>
                <option value="paid"           {{ request('payment_status') == 'paid'           ? 'selected' : '' }}>Paid</option>
                <option value="partially_paid" {{ request('payment_status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                <option value="unpaid"         {{ request('payment_status') == 'unpaid'         ? 'selected' : '' }}>Unpaid</option>
                <option value="refunded"       {{ request('payment_status') == 'refunded'       ? 'selected' : '' }}>Refunded</option>
                <option value="cancelled"      {{ request('payment_status') == 'cancelled'      ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <!-- Fulfillment Filter -->
        <div style="flex: 1; min-width: 125px;">
            <select name="fulfillment_type" class="custom-combo"
                    style="width: 100%; padding: 0.42rem 0.75rem; border: 1px solid {{ request('fulfillment_type') ? 'var(--accent-color)' : 'var(--border-color)' }}; border-left: {{ request('fulfillment_type') ? '3px solid var(--accent-color)' : '1px solid var(--border-color)' }}; border-radius: 8px; font-size: 0.8rem; outline: none; background: {{ request('fulfillment_type') ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; color: {{ request('fulfillment_type') ? 'var(--text-accent)' : 'var(--text-main)' }}; {{ request('fulfillment_type') ? 'font-weight: 700;' : '' }} cursor: pointer; transition: all 0.2s;"
                    onchange="this.form.submit()"
                    onfocus="this.style.borderColor='#0099FF'; this.style.borderLeft='3px solid #0099FF'; this.style.boxShadow='0 0 0 2px rgba(0, 153, 255, 0.15)';"
                    onblur="this.style.borderColor='{{ request('fulfillment_type') ? '#0099FF' : '#CBD5E1' }}'; this.style.borderLeft='{{ request('fulfillment_type') ? '3px solid #0099FF' : '1px solid #CBD5E1' }}'; this.style.boxShadow='none';">
                <option value="">All Fulfillment</option>
                <option value="delivery" {{ request('fulfillment_type') == 'delivery' ? 'selected' : '' }}>Delivery</option>
                <option value="pickup"   {{ request('fulfillment_type') == 'pickup'   ? 'selected' : '' }}>Store Pickup</option>
            </select>
        </div>

        <!-- Order Type Filter -->
        <div style="flex: 1; min-width: 115px;">
            <select name="order_type" class="custom-combo"
                    style="width: 100%; padding: 0.42rem 0.75rem; border: 1px solid {{ request('order_type') ? 'var(--accent-color)' : 'var(--border-color)' }}; border-left: {{ request('order_type') ? '3px solid var(--accent-color)' : '1px solid var(--border-color)' }}; border-radius: 8px; font-size: 0.8rem; outline: none; background: {{ request('order_type') ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; color: {{ request('order_type') ? 'var(--text-accent)' : 'var(--text-main)' }}; {{ request('order_type') ? 'font-weight: 700;' : '' }} cursor: pointer; transition: all 0.2s;"
                    onchange="this.form.submit()"
                    onfocus="this.style.borderColor='#0099FF'; this.style.borderLeft='3px solid #0099FF'; this.style.boxShadow='0 0 0 2px rgba(0, 153, 255, 0.15)';"
                    onblur="this.style.borderColor='{{ request('order_type') ? '#0099FF' : '#CBD5E1' }}'; this.style.borderLeft='{{ request('order_type') ? '3px solid #0099FF' : '1px solid #CBD5E1' }}'; this.style.boxShadow='none';">
                <option value="">All Types</option>
                <option value="regular" {{ request('order_type') == 'regular' ? 'selected' : '' }}>Regular</option>
                <option value="custom"  {{ request('order_type') == 'custom'  ? 'selected' : '' }}>Custom</option>
            </select>
        </div>

    </form>
</div>

{{-- ─── ORDERS TABLE ────────────────────────────────────────────────── --}}
<div class="card" style="padding: 1.25rem; overflow: hidden; margin-bottom: 2rem;">
    <div style="overflow-x: auto; width: 100%;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.85rem;">
            <thead>
                <tr style="background: var(--bg-page); text-align: left;">
                    <th style="padding: 0.75rem 0.85rem; border-bottom: 2px solid var(--border-color); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--text-muted);">Order</th>
                    <th style="padding: 0.75rem 0.85rem; border-bottom: 2px solid var(--border-color); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--text-muted);">Customer</th>
                    <th style="padding: 0.75rem 0.65rem; border-bottom: 2px solid var(--border-color); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--text-muted); text-align: center;">Items</th>
                    <th style="padding: 0.75rem 0.85rem; border-bottom: 2px solid var(--border-color); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--text-muted);">Amount</th>
                    <th style="padding: 0.75rem 0.85rem; border-bottom: 2px solid var(--border-color); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--text-muted);">Status & Type</th>
                    <th style="padding: 0.75rem 0.85rem; border-bottom: 2px solid var(--border-color); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--text-muted);">Payment</th>
                    <th style="padding: 0.75rem 0.85rem; border-bottom: 2px solid var(--border-color); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--text-muted); text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Payment TYPE (cash vs online) reference for the chips below —
                    // loaded once, keyed by payment method name.
                    $pmCatalog = \App\Models\PaymentMethod::pluck('is_online_payment', 'name');
                @endphp
                @forelse($orders as $ord)
                    @php
                        $payment        = $ord->payment;
                        $paymentMethod  = $payment ? $payment->payment_method : 'GCash / Cash';
                        $proofScreenshot = $payment ? $payment->proof_of_payment_path : null;

                        $pmIsOnline = $pmCatalog->has($paymentMethod)
                            ? (bool) $pmCatalog->get($paymentMethod)
                            : $pmCatalog->get(ucfirst($paymentMethod));

                        $statusColors = [
                            'pending'       => ['bg' => '#FEF9C3', 'color' => '#854D0E', 'dot' => '#CA8A04'],
                            'design_review' => ['bg' => '#EDE9FE', 'color' => '#5B21B6', 'dot' => '#7C3AED'],
                            'approved'      => ['bg' => '#DBEAFE', 'color' => '#1D4ED8', 'dot' => '#3B82F6'],
                            'printing'      => ['bg' => '#E0F2FE', 'color' => '#0369A1', 'dot' => '#0099FF'],
                            'ready'         => ['bg' => '#D1FAE5', 'color' => '#065F46', 'dot' => '#10B981'],
                            'shipped'       => ['bg' => '#CCFBF1', 'color' => '#0F766E', 'dot' => '#14B8A6'],
                            'completed'     => ['bg' => '#DCFCE7', 'color' => '#14532D', 'dot' => '#16A34A'],
                            'cancelled'     => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'dot' => '#EF4444'],
                        ];
                        $sc = $statusColors[$ord->status] ?? ['bg' => '#F1F5F9', 'color' => '#475569', 'dot' => '#94A3B8'];

                        $payColors = [
                            'paid'           => ['bg' => '#DCFCE7', 'color' => '#15803D'],
                            'partially_paid' => ['bg' => '#E0F2FE', 'color' => '#0369A1'],
                            'unpaid'         => ['bg' => '#FEF3C7', 'color' => '#92400E'],
                            'refunded'       => ['bg' => '#FEE2E2', 'color' => '#991B1B'],
                            'cancelled'      => ['bg' => '#F1F5F9', 'color' => '#475569'],
                        ];
                        $pc = $payColors[$ord->payment_status] ?? ['bg' => '#F1F5F9', 'color' => '#475569'];
                    @endphp
                    <tr class="order-row" 
                         style="border-bottom: 1px solid var(--border-color); cursor: pointer;"
                         onclick="window.location='{{ route('admin.orders.show', $ord->id) }}'">

                        {{-- ORDER # + DATE --}}
                        <td style="padding: 0.85rem 0.85rem; vertical-align: middle;">
                            <div style="font-weight: 800; color: var(--text-main); font-size: 0.86rem; font-family: 'Outfit', monospace; letter-spacing: -0.2px; white-space: nowrap;">
                                #{{ $ord->order_number }}
                            </div>
                            <div style="font-size: 0.72rem; color: var(--text-subtle); margin-top: 0.15rem; font-weight: 500; white-space: nowrap;">
                                {{ $ord->created_at->timezone('Asia/Manila')->format('M d, Y · g:i A') }}
                            </div>
                            <div style="display: flex; gap: 0.25rem; flex-wrap: wrap; margin-top: 0.25rem;">
                                @if($ord->is_parent)
                                    <span style="font-size:0.62rem; font-weight:800; background:var(--accent-soft); color:var(--text-accent); border:1px solid var(--info-border); padding:1px 5px; border-radius:4px; text-transform:uppercase; letter-spacing:0.04em;">
                                        Parent Split
                                    </span>
                                @elseif($ord->parent_id)
                                    <span style="font-size:0.62rem; font-weight:800; background:var(--brand-violet-soft); color:var(--text-violet); border:1px solid var(--brand-violet-soft); padding:1px 5px; border-radius:4px; text-transform:uppercase; letter-spacing:0.04em;">
                                        Sub-Order
                                    </span>
                                @endif
                                @if($ord->is_downpayment)
                                    <span style="font-size:0.62rem; font-weight:800; background:var(--warning-soft); color:var(--text-warning); border:1px solid var(--warning-border); padding:1px 5px; border-radius:4px; text-transform:uppercase; letter-spacing:0.04em;">
                                        50% DP
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- CUSTOMER --}}
                        <td style="padding: 0.85rem 0.85rem; vertical-align: middle;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.86rem; line-height: 1.2;">
                                {{ $ord->customer_name }}
                            </div>
                            <div style="font-size: 0.74rem; color: var(--text-subtle); margin-top: 0.12rem; word-break: break-all;">
                                {{ $ord->customer_email ?? '' }}
                            </div>
                            @if($ord->fulfillment_type)
                                <div style="display:inline-flex; align-items:center; gap:0.25rem; margin-top:0.2rem; font-size:0.68rem; font-weight:700; color:{{ $ord->fulfillment_type === 'delivery' ? 'var(--text-accent)' : 'var(--text-success)' }};">
                                    <i class="fa-solid {{ $ord->fulfillment_type === 'delivery' ? 'fa-truck-fast' : 'fa-store' }}" style="font-size:0.65rem;"></i>
                                    {{ $ord->fulfillment_type === 'delivery' ? 'Delivery' : 'Pickup' }}
                                </div>
                            @endif
                        </td>

                        {{-- ITEMS COUNT --}}
                        <td style="padding: 0.85rem 0.65rem; vertical-align: middle; text-align: center; white-space: nowrap;">
                            <span style="background:var(--bg-subtle); color:var(--text-muted); padding:0.22rem 0.5rem; border-radius:5px; font-weight:700; font-size:0.78rem;">
                                {{ $ord->items->count() }} item(s)
                            </span>
                        </td>

                        {{-- AMOUNT --}}
                        <td style="padding: 0.85rem 0.85rem; vertical-align: middle; white-space: nowrap;">
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-accent); font-family: 'Outfit', sans-serif;">
                                ₱{{ number_format($ord->total_price ?: $ord->total_amount, 2) }}
                            </div>
                            @if($ord->is_downpayment)
                                <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.1rem; font-weight: 600;">
                                    Bal: ₱{{ number_format($ord->remaining_balance, 2) }}
                                </div>
                            @endif
                        </td>

                        {{-- ORDER STATUS & TYPE --}}
                        <td style="padding: 0.85rem 0.85rem; vertical-align: middle;">
                            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.35rem;">
                                <span style="display:inline-flex; align-items:center; gap:0.35rem; background:{{ $sc['bg'] }}; color:{{ $sc['color'] }}; font-size:0.7rem; font-weight:800; padding:0.22rem 0.55rem; border-radius:9999px; text-transform:uppercase; letter-spacing:0.03em; white-space: nowrap;">
                                    <span style="width:5px; height:5px; border-radius:50%; background:{{ $sc['dot'] }}; flex-shrink:0;"></span>
                                    {{ str_replace('_', ' ', $ord->status) }}
                                </span>
                                @if($ord->is_custom_order)
                                    <span style="display:inline-flex; align-items:center; gap:0.25rem; background:var(--brand-magenta-soft); color:var(--text-magenta); border:1px solid var(--brand-magenta-soft); font-size:0.67rem; font-weight:800; padding:0.15rem 0.45rem; border-radius:5px; text-transform:uppercase; white-space: nowrap;">
                                        <i class="fa-solid fa-palette" style="font-size:0.6rem;"></i> Custom
                                    </span>
                                @else
                                    <span style="display:inline-flex; align-items:center; gap:0.25rem; background:var(--success-soft); color:var(--text-success); border:1px solid var(--success-border); font-size:0.67rem; font-weight:800; padding:0.15rem 0.45rem; border-radius:5px; text-transform:uppercase; white-space: nowrap;">
                                        <i class="fa-solid fa-box" style="font-size:0.6rem;"></i> Regular
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- PAYMENT STATUS + METHOD --}}
                        <td style="padding: 0.85rem 0.85rem; vertical-align: middle;">
                            <span style="display:inline-block; background:{{ $pc['bg'] }}; color:{{ $pc['color'] }}; font-size:0.7rem; font-weight:800; padding:0.2rem 0.55rem; border-radius:9999px; text-transform:uppercase; letter-spacing:0.03em; white-space: nowrap;">
                                {{ str_replace('_', ' ', $ord->payment_status) }}
                            </span>
                            <div style="font-size:0.72rem; color:var(--text-subtle); margin-top:0.2rem; font-weight:500; white-space: nowrap;">
                                <i class="fa-solid fa-wallet" style="font-size:0.65rem;"></i> {{ Str::limit($paymentMethod, 18) }}
                                @if($pmIsOnline === true)
                                    <span style="display:inline-block; background:var(--accent-soft); color:var(--text-accent); font-size:0.6rem; font-weight:800; padding:0.05rem 0.35rem; border-radius:4px; margin-left:0.25rem; text-transform:uppercase;">Online</span>
                                @elseif($pmIsOnline === false)
                                    <span style="display:inline-block; background:var(--warning-soft); color:var(--text-warning); font-size:0.6rem; font-weight:800; padding:0.05rem 0.35rem; border-radius:4px; margin-left:0.25rem; text-transform:uppercase;">Cash</span>
                                @endif
                            </div>
                        </td>

                        {{-- ACTIONS --}}
                        <td style="padding: 0.85rem 0.85rem; text-align: right; vertical-align: middle; white-space: nowrap;">
                            <div style="display:inline-flex; align-items:center; gap:0.35rem;">

                                {{-- Manage --}}
                                <a href="{{ route('admin.orders.show', $ord->id) }}"
                                    onclick="event.stopPropagation();"
                                    style="background:var(--accent-color); color:var(--text-inverse); border:1px solid var(--accent-color); padding:0.38rem 0.75rem; border-radius:7px; font-size:0.76rem; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem; transition:all 0.15s;"
                                    onmouseover="this.style.background='#007ACC'; this.style.borderColor='#007ACC';"
                                    onmouseout="this.style.background='#0099FF'; this.style.borderColor='#0099FF';">
                                    <i class="fa-solid fa-sliders"></i> Manage
                                </a>

                                {{-- Delete (Super Admin only; allowed only for settled/closed orders) --}}
                                @if(Auth::user()?->isSuperAdmin())
                                    @php $canDeleteOrder = $ord->canBeDeleted(); @endphp
                                    @if($canDeleteOrder)
                                        <form action="{{ route('super-admin.orders.destroy', $ord->id) }}" method="POST"
                                              style="display:inline; margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                onclick="event.stopPropagation(); openDeleteOrderModal('{{ $ord->order_number }}', this.closest('form'))"
                                                style="background:var(--bg-surface); color:var(--text-subtle); border:1px solid var(--border-color); padding:0.38rem 0.55rem; border-radius:7px; font-size:0.78rem; cursor:pointer; display:inline-flex; align-items:center; transition:all 0.15s;"
                                                onmouseover="this.style.color='#DC2626'; this.style.borderColor='#FECACA'; this.style.background='#FEF2F2';"
                                                onmouseout="this.style.color='#94A3B8'; this.style.borderColor='#E2E8F0'; this.style.background='#FFF';"
                                                title="Delete order permanently">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled
                                            onclick="event.stopPropagation();"
                                            style="background:var(--bg-page); color:var(--text-disabled); border:1px solid var(--border-color); padding:0.38rem 0.55rem; border-radius:7px; font-size:0.78rem; cursor:not-allowed; display:inline-flex; align-items:center; opacity:0.7;"
                                            title="Only completed, cancelled, paid, or refunded orders can be deleted">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    @endif
                                @endif

                            </div>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:3rem; color:var(--text-muted);">
                            <i class="fa-solid fa-box-open" style="font-size:2rem; color:var(--text-disabled); display:block; margin-bottom:0.75rem;"></i>
                            No orders match your filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- ─── PAGINATION ─────────────────────────────────────────────────── --}}
        <div style="margin-top:1.25rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem; padding-top:0.75rem; border-top:1px solid var(--border-color);">
            <div style="font-size:0.82rem; color:var(--text-muted); font-weight:500;">
                Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders
            </div>
            <div style="display:flex; align-items:center; gap:0.35rem;">
                @if($orders->onFirstPage())
                    <span style="padding:0.45rem 0.85rem; border:1px solid var(--border-color); border-radius:7px; font-size:0.82rem; color:var(--text-disabled); font-weight:600; cursor:default;">← Prev</span>
                @else
                    <a href="{{ $orders->previousPageUrl() }}"
                       style="padding:0.45rem 0.85rem; border:1px solid var(--border-color); border-radius:7px; font-size:0.82rem; color:var(--text-muted); font-weight:600; text-decoration:none; transition:all 0.15s;"
                       onmouseover="this.style.borderColor='#0099FF'; this.style.color='#0099FF';"
                       onmouseout="this.style.borderColor='#E2E8F0'; this.style.color='#475569';">← Prev</a>
                @endif

                @foreach($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                    @if($page == $orders->currentPage())
                        <span style="padding:0.45rem 0.75rem; background:var(--accent-color); color:var(--text-inverse); border:1px solid var(--accent-color); border-radius:7px; font-size:0.82rem; font-weight:700; min-width:36px; text-align:center;">{{ $page }}</span>
                    @elseif($page == 1 || $page == $orders->lastPage() || abs($page - $orders->currentPage()) <= 1)
                        <a href="{{ $url }}"
                           style="padding:0.45rem 0.75rem; border:1px solid var(--border-color); border-radius:7px; font-size:0.82rem; color:var(--text-muted); font-weight:600; text-decoration:none; min-width:36px; text-align:center; transition:all 0.15s;"
                           onmouseover="this.style.borderColor='#0099FF'; this.style.color='#0099FF';"
                           onmouseout="this.style.borderColor='#E2E8F0'; this.style.color='#475569';">{{ $page }}</a>
                    @elseif($page == $orders->currentPage() - 2 || $page == $orders->currentPage() + 2)
                        <span style="padding:0.45rem 0.5rem; color:var(--text-subtle); font-size:0.82rem;">…</span>
                    @endif
                @endforeach

                @if($orders->hasMorePages())
                    <a href="{{ $orders->nextPageUrl() }}"
                       style="padding:0.45rem 0.85rem; border:1px solid var(--border-color); border-radius:7px; font-size:0.82rem; color:var(--text-muted); font-weight:600; text-decoration:none; transition:all 0.15s;"
                       onmouseover="this.style.borderColor='#0099FF'; this.style.color='#0099FF';"
                       onmouseout="this.style.borderColor='#E2E8F0'; this.style.color='#475569';">Next →</a>
                @else
                    <span style="padding:0.45rem 0.85rem; border:1px solid var(--border-color); border-radius:7px; font-size:0.82rem; color:var(--text-disabled); font-weight:600; cursor:default;">Next →</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ─── DELETE ORDER CONFIRMATION MODAL (replaces native confirm) ─────────── --}}
@if(Auth::user()?->isSuperAdmin())
<div id="delete-order-modal" onclick="if(event.target===this) closeDeleteOrderModal()"
    style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div style="background:var(--bg-surface); border-radius:16px; width:100%; max-width:420px; overflow:hidden; box-shadow:0 20px 50px rgba(15,23,42,0.3); animation:delOrderPop 0.18s ease;">
        <div style="padding:1.75rem 1.75rem 1.25rem; text-align:center;">
            <div style="width:56px; height:56px; border-radius:50%; background:var(--danger-soft); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                <i class="fa-solid fa-trash-can" style="font-size:1.4rem; color:var(--text-danger);"></i>
            </div>
            <h3 style="font-size:1.15rem; font-weight:800; color:var(--text-main); margin:0 0 0.4rem;">Delete this order?</h3>
            <p style="color:var(--text-muted); font-size:0.86rem; margin:0; line-height:1.5;">
                You are about to permanently delete order <strong style="color:var(--text-main);" id="delete-order-number">#&mdash;</strong>. This action <strong style="color:var(--text-danger);">cannot be undone</strong>.
            </p>
        </div>
        <div style="padding:1rem 1.75rem 1.5rem; display:flex; gap:0.75rem; border-top:1px solid var(--border-color); background:var(--bg-page);">
            <button type="button" onclick="closeDeleteOrderModal()" style="flex:1; background:var(--bg-surface); border:1px solid var(--border-color); color:var(--text-muted); padding:0.6rem 1rem; border-radius:9px; font-size:0.88rem; font-weight:700; cursor:pointer; transition:background 0.15s;" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='#FFF'">Cancel</button>
            <button type="button" onclick="confirmDeleteOrder()" style="flex:1; background:var(--danger-color); border:1px solid var(--danger-color); color:var(--text-inverse); padding:0.6rem 1rem; border-radius:9px; font-size:0.88rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; transition:background 0.15s;" onmouseover="this.style.background='#B91C1C'" onmouseout="this.style.background='#DC2626'"><i class="fa-solid fa-trash-can"></i> Yes, Delete</button>
        </div>
    </div>
</div>
<style>@@keyframes delOrderPop { from { transform: scale(0.95); opacity:0; } to { transform: scale(1); opacity:1; } }</style>
<script>
function openDeleteOrderModal(orderNumber, form) {
    window.__deleteOrderForm = form;
    var num = document.getElementById('delete-order-number');
    if (num) num.textContent = '#' + orderNumber;
    var m = document.getElementById('delete-order-modal');
    if (m) m.style.display = 'flex';
}
function closeDeleteOrderModal() {
    var m = document.getElementById('delete-order-modal');
    if (m) m.style.display = 'none';
    window.__deleteOrderForm = null;
}
function confirmDeleteOrder() {
    if (window.__deleteOrderForm) window.__deleteOrderForm.submit();
}
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDeleteOrderModal(); });
</script>
@endif

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
        panel.style.cssText = 'display:none; position:absolute; top:calc(100% + 4px); left:0; right:0; background:var(--bg-elevated); border:1px solid var(--border-color); border-radius:8px; box-shadow:0 6px 16px rgba(15,23,42,0.1); max-height:240px; overflow-y:auto; z-index:80; padding:0.2rem;';
        wrapper.appendChild(panel);

        function styleBtn() {
            const active = select.value !== '';
            btn.style.cssText = 'width:100%; display:flex; justify-content:space-between; align-items:center; gap:0.4rem; padding:0.42rem 0.75rem; border:1px solid ' + (active ? 'var(--accent-color)' : 'var(--border-strong)') + '; border-left:3px solid ' + (active ? 'var(--accent-color)' : 'var(--border-strong)') + '; border-radius:8px; font-size:0.8rem; font-weight:' + (active ? '700' : '500') + '; background:' + (active ? 'var(--accent-soft)' : 'var(--bg-surface)') + '; color:' + (active ? 'var(--accent-hover)' : 'var(--text-main)') + '; cursor:pointer; transition:all 0.2s; font-family:inherit;';
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
                item.style.cssText = 'display:flex; justify-content:space-between; align-items:center; gap:0.4rem; padding:0.35rem 0.55rem; border-radius:6px; font-size:0.78rem; font-weight:' + (selected ? '700' : '500') + '; cursor:pointer; color:' + (selected ? 'var(--accent-contrast)' : 'var(--text-main)') + '; background:' + (selected ? 'var(--accent-color)' : 'var(--bg-elevated)') + ';';
                if (!selected) {
                    item.addEventListener('mouseover', function () { item.style.background = 'var(--accent-soft)'; });
                    item.addEventListener('mouseout', function () { item.style.background = 'var(--bg-elevated)'; });
                } else {
                    const chk = document.createElement('i');
                    chk.className = 'fa-solid fa-check';
                    chk.style.cssText = 'font-size:0.65rem; color:var(--accent-contrast);';
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
