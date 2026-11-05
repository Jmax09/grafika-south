@extends('layouts.app')

@section('title', 'Track & Trace Order - Grafika South')

@push('styles')
<style>
    @media (max-width: 640px) {
        .card { padding: 1.25rem 1rem !important; }
    }
</style>
@endpush

@section('content')
<div style="max-width: 900px; margin: 0 auto; padding-bottom: 2rem;">

    <!-- TRACKING SEARCH HEADER CARD -->
    <div class="card" style="padding: 2rem; margin-bottom: 2rem; text-align: center; background: var(--bg-surface); border-top: 4px solid var(--cmyk-cyan);">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--accent-soft); color: var(--brand-cyan-fg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.6rem;">
            <i class="fa-solid fa-magnifying-glass-location"></i>
        </div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.4rem;">
            Track &amp; Trace Order Status
        </h1>
        <p style="font-size: 0.92rem; color: var(--text-muted); max-width: 540px; margin: 0 auto 1.5rem;">
            Enter your Grafika South order number below to check live print production progress, artwork approval, and delivery status.
        </p>

        <form action="{{ route('orders.track') }}" method="GET" style="max-width: 560px; margin: 0 auto; display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; min-width: 260px;">
                <i class="fa-solid fa-hashtag" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.95rem;"></i>
                <input type="text" name="order_number" value="{{ $orderNumber }}" placeholder="e.g. GS-20260817-6CB93" required style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.6rem; border: 2px solid var(--border-color); border-radius: 10px; font-size: 0.95rem; font-family: monospace; font-weight: 700; text-transform: uppercase; box-sizing: border-box; outline: none;" onfocus="this.style.borderColor='var(--cmyk-cyan)'" onblur="this.style.borderColor='var(--border-color)'">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem; font-size: 0.95rem; background: var(--cmyk-cyan); border-color: var(--cmyk-cyan);">
                <i class="fa-solid fa-magnifying-glass"></i> Track Order
            </button>
        </form>
    </div>

    @if($searched && !$order)
        <!-- NOT FOUND ALERT -->
        <div style="background: var(--danger-soft); border: 1px solid var(--danger-border); border-radius: 12px; padding: 1.5rem; text-align: center; color: var(--text-danger); margin-bottom: 2rem;">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 2rem; color: var(--text-danger); margin-bottom: 0.5rem; display: block;"></i>
            <strong style="font-size: 1.1rem; display: block; margin-bottom: 0.3rem;">Order Not Found</strong>
            <p style="font-size: 0.9rem; margin-bottom: 0.75rem;">
                We couldn't find an order matching "<strong>{{ $orderNumber }}</strong>". Please double check your order number on your receipt or confirmation message.
            </p>
            <span style="font-size: 0.8rem; color: var(--text-danger);">Example valid format: <code>GS-20260817-6CB93</code></span>
        </div>
    @endif

    @if($order)
        @php
            $hasSubOrders = $order->is_parent || $order->subOrders->isNotEmpty();
            $subOrdersList = $hasSubOrders ? $order->subOrders : collect([$order]);
            $isSubOrder = !empty($order->parent_id);
            $parentOrder = $isSubOrder ? $order->parentOrder : null;
        @endphp

        <!-- PARENT LINK BANNER IF TRACKING A SUB-ORDER DIRECTLY -->
        @if($isSubOrder && $parentOrder)
            <div style="background: var(--accent-soft); border: 1.5px solid var(--accent-color); border-radius: 12px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: var(--accent-soft); color: var(--text-accent); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fa-solid fa-code-fork"></i>
                    </div>
                    <div>
                        <strong style="color: var(--text-accent); font-size: 1rem; display: block;">Part of Split Order #{{ $parentOrder->order_number }}</strong>
                        <span style="font-size: 0.82rem; color: var(--text-accent);">This is an independent sub-order for {{ $order->is_custom_order ? 'Customized Print Items' : 'Ready-Made Products' }}.</span>
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="{{ route('orders.track', ['order_number' => $parentOrder->order_number]) }}" class="btn btn-primary" style="padding: 0.45rem 1rem; font-size: 0.82rem;">
                        <i class="fa-solid fa-layer-group"></i> Track Entire Split Order
                    </a>
                </div>
            </div>
        @endif

        @if($hasSubOrders)
            <!-- SPLIT CART PARENT ORDER OVERVIEW CARD -->
            <div class="card" style="padding: 1.75rem; margin-bottom: 1.5rem; background: var(--bg-surface); border-left: 5px solid var(--accent-color);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.35rem;">
                            <span class="role-badge" style="background: var(--accent-soft); color: var(--text-accent); font-size: 0.75rem; font-weight: 800;">
                                <i class="fa-solid fa-code-fork"></i> Split Order ({{ $order->subOrders->count() }} Sub-Orders)
                            </span>
                        </div>
                        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); font-family: monospace;">
                            #{{ $order->order_number }}
                        </h2>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">
                            Placed on {{ $order->created_at->timezone('Asia/Manila')->format('M d, Y @ h:i A') }} &bull; Combined Total: <strong style="color: var(--text-main);">₱{{ number_format($order->total_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <span style="background: var(--success-soft); color: var(--text-success); border: 1px solid var(--success-border); font-weight: 800; font-size: 0.82rem; padding: 0.35rem 0.85rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i class="fa-solid fa-circle-check"></i> Multi-Status Tracking Active
                        </span>
                        <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.4rem;">
                            Each sub-order below progresses independently
                        </div>
                    </div>
                </div>

                <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">
                    Your cart contained both ready-made products and custom print items. To provide the fastest fulfillment, your order was split into independent sub-orders. Track their individual progress below.
                </p>
            </div>
        @endif

        <!-- RENDER INDIVIDUAL SUB-ORDER OR STANDALONE ORDER TRACKING CARDS -->
        @foreach($subOrdersList as $subOrder)
            @php
                $isCustom = (bool) $subOrder->is_custom_order;
                $workflowStages = $subOrder->workflow_stages;
                $currentStep = $subOrder->current_workflow_step;
                $isDelivery = $subOrder->fulfillment_type === 'delivery';
            @endphp

            <div class="card" style="padding: 1.75rem; margin-bottom: 2rem; background: var(--bg-surface); border-top: 4px solid {{ $isCustom ? 'var(--cmyk-magenta)' : 'var(--cmyk-cyan)' }};">
                <!-- SUB-ORDER HEADER -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                            @if($isCustom)
                                <span class="role-badge" style="background: var(--brand-magenta-soft); color: var(--text-magenta); border: 1px solid var(--brand-magenta-soft); font-size: 0.75rem; font-weight: 800;">
                                    <i class="fa-solid fa-palette"></i> Customized Print Sub-Order
                                </span>
                            @else
                                <span class="role-badge" style="background: var(--accent-soft); color: var(--text-accent); border: 1px solid var(--info-border); font-size: 0.75rem; font-weight: 800;">
                                    <i class="fa-solid fa-box"></i> Ready-Made / Regular Sub-Order
                                </span>
                            @endif

                            <span style="font-size: 0.76rem; background: var(--bg-page); border: 1px solid var(--border-color); padding: 2px 8px; border-radius: 9999px; color: var(--text-muted); font-weight: 600;">
                                <i class="fa-solid {{ $isDelivery ? 'fa-truck-fast' : 'fa-store' }}"></i>
                                {{ $isDelivery ? 'Courier Delivery' : 'Store Pick-up (San Pedro)' }}
                            </span>

                            @if($isCustom && $subOrder->formatted_production_time)
                                <span style="font-size: 0.76rem; background: var(--warning-soft); border: 1px solid var(--warning-border); padding: 2px 8px; border-radius: 9999px; color: var(--text-warning); font-weight: 700;">
                                    <i class="fa-solid fa-stopwatch"></i> Est. Production: {{ $subOrder->formatted_production_time }}
                                </span>
                            @endif
                        </div>


                        <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--text-main); font-family: monospace; margin-bottom: 0.2rem;">
                            #{{ $subOrder->order_number }}
                        </h3>
                        <div style="font-size: 0.82rem; color: var(--text-muted);">
                            Placed on {{ $subOrder->created_at->timezone('Asia/Manila')->format('M d, Y @ h:i A') }}
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">
                            Payment Details
                        </div>
                        <div>
                            <span style="background: {{ $subOrder->payment_status === 'paid' ? 'var(--success-soft)' : ($subOrder->payment_status === 'partially_paid' ? 'var(--accent-soft)' : 'var(--warning-soft)') }}; color: {{ $subOrder->payment_status === 'paid' ? 'var(--text-success)' : ($subOrder->payment_status === 'partially_paid' ? 'var(--text-accent)' : 'var(--text-warning)') }}; font-weight: 800; font-size: 0.82rem; padding: 0.3rem 0.75rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid {{ $subOrder->payment_status === 'paid' ? 'var(--success-border)' : ($subOrder->payment_status === 'partially_paid' ? 'var(--info-border)' : 'var(--warning-border)') }};">
                                <i class="fa-solid {{ $subOrder->payment_status === 'paid' ? 'fa-circle-check' : 'fa-clock' }}"></i>
                                {{ strtoupper(str_replace('_', ' ', $subOrder->payment_status)) }}
                                @if($subOrder->payment)
                                    ({{ $subOrder->payment->payment_method }})
                                @endif
                            </span>
                        </div>
                        <div style="font-weight: 800; font-size: 1.2rem; color: var(--accent-color); margin-top: 0.35rem;">
                            ₱{{ number_format($subOrder->total_amount, 2) }}
                        </div>
                        @if($subOrder->is_downpayment)
                            <div style="font-size: 0.78rem; color: var(--text-accent); font-weight: 700; margin-top: 3px;">
                                Amount Paid: <strong>₱{{ number_format($subOrder->downpayment_amount, 2) }}</strong>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-danger); font-weight: 700; margin-top: 1px;">
                                Remaining Balance to be Paid on Delivery/Pickup: <strong>₱{{ number_format($subOrder->remaining_balance, 2) }}</strong>
                            </div>
                        @endif
                        @if($subOrder->payment_proof_path)
                            <div style="margin-top: 0.4rem;">
                                <a href="{{ asset('storage/' . $subOrder->payment_proof_path) }}" target="_blank" style="font-size: 0.76rem; font-weight: 700; color: var(--text-success); display: inline-flex; align-items: center; gap: 0.3rem; background: var(--success-soft); border: 1px solid var(--success-border); padding: 0.2rem 0.55rem; border-radius: 6px; text-decoration: none;">
                                    <i class="fa-solid fa-receipt"></i> View Payment Screenshot
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- FULFILLMENT & RECIPIENT DETAILS -->
                <div style="background: var(--bg-page); border: 1px solid var(--border-color); border-radius: 10px; padding: 1.25rem; margin-bottom: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    <div>
                        <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); display: block; text-transform: uppercase;">Fulfillment Type</span>
                        <strong style="font-size: 0.95rem; color: var(--text-main); display: flex; align-items: center; gap: 0.4rem; margin-top: 2px;">
                            <i class="fa-solid {{ $subOrder->fulfillment_type === 'delivery' ? 'fa-truck-fast' : 'fa-store' }}" style="color: var(--brand-cyan-fg);"></i>
                            {{ $subOrder->fulfillment_type === 'delivery' ? 'Courier Delivery' : 'Store Pick-up (San Pedro Branch)' }}
                        </strong>
                    </div>

                    <div>
                        <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); display: block; text-transform: uppercase;">Customer Name</span>
                        <strong style="font-size: 0.95rem; color: var(--text-main); display: block; margin-top: 2px;">
                            {{ $subOrder->customer_name }}
                        </strong>
                    </div>

                    <div>
                        <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); display: block; text-transform: uppercase;">Contact Number</span>
                        <span style="font-size: 0.9rem; color: var(--text-main); display: block; margin-top: 2px;">
                            {{ $subOrder->customer_contact ?: 'Not provided' }}
                        </span>
                    </div>

                    <div>
                        <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); display: block; text-transform: uppercase;">Destination / Address</span>
                        <span style="font-size: 0.88rem; color: var(--text-main); display: block; margin-top: 2px;">
                            {{ $subOrder->customer_address ?: 'San Pedro, Laguna Branch Counter Pick-up' }}
                        </span>
                    </div>
                </div>

                <!-- INDEPENDENT REAL-TIME WORKFLOW PROGRESS TRACKER -->
                <div style="background: var(--bg-page); border-radius: 12px; border: 1px solid var(--border-color); padding: 1.5rem 1.75rem; margin-bottom: 1.75rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 0.5rem;">
                        <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); font-family: 'Outfit', sans-serif; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-diagram-project" style="color: {{ $isCustom ? 'var(--cmyk-magenta)' : 'var(--cmyk-cyan)' }};"></i>
                            Live Status Tracker: {{ $isCustom ? 'Custom Production Workflow' : 'Store Pickup / Delivery Workflow' }}
                        </h4>
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">
                            Current Status: <strong style="color: {{ $isCustom ? 'var(--cmyk-magenta)' : 'var(--cmyk-cyan)' }};">{{ ucwords(str_replace('_', ' ', $subOrder->status)) }}</strong>
                        </span>
                    </div>

                    @if($isCustom && $subOrder->formatted_production_time)
                        <div style="margin-bottom: 1.5rem; background: var(--warning-soft); border: 1px solid var(--warning-border); border-radius: 8px; padding: 0.65rem 1rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                            <span style="font-size: 0.82rem; font-weight: 700; color: var(--text-warning); display: inline-flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-stopwatch" style="color: var(--text-warning);"></i> Estimated Production Turnaround:
                            </span>
                            <span style="font-size: 0.84rem; font-weight: 800; color: var(--text-warning); background: var(--warning-soft); padding: 0.2rem 0.65rem; border-radius: 9999px;">
                                {{ $subOrder->formatted_production_time }}
                            </span>
                        </div>
                    @endif

                    @if($subOrder->status === 'cancelled')

                        <div class="alert alert-error" style="margin-bottom: 0;">
                            <i class="fa-solid fa-circle-xmark"></i> This sub-order was <strong>CANCELLED</strong>. Notes: {{ $subOrder->notes ?: 'No details provided.' }}
                        </div>
                    @else
                        @php
                            $totalStages = count($workflowStages);
                        @endphp
                        <div style="display: grid; grid-template-columns: repeat({{ $totalStages }}, 1fr); gap: 0.5rem; text-align: center; justify-items: center; position: relative;">
                            @foreach($workflowStages as $key => $stageInfo)
                                @php
                                    $stepIdx = $stageInfo['step'];
                                    $isCompleted = $currentStep !== -1 && $stepIdx < $currentStep;
                                    $isCurrent = $currentStep !== -1 && $stepIdx === $currentStep;
                                    $isUpcoming = !$isCompleted && !$isCurrent;
                                @endphp

                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; width: 100%;">
                                    <!-- CIRCULAR STEP BADGE -->
                                    @if($isCompleted)
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--success-color); color: var(--text-inverse); display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: 800; margin-bottom: 0.65rem;">
                                            <i class="fa-solid fa-check"></i>
                                        </div>
                                        <div style="font-size: 0.74rem; font-weight: 800; color: var(--text-success); text-transform: uppercase; letter-spacing: 0.02em;">
                                            {{ strtoupper($stageInfo['label']) }}
                                        </div>
                                    @elseif($isCurrent)
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $isCustom ? 'var(--cmyk-magenta)' : 'var(--cmyk-cyan)' }}; color: var(--text-inverse); display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: 800; margin-bottom: 0.65rem; box-shadow: 0 0 0 5px {{ $isCustom ? 'rgba(236, 0, 140, 0.25)' : 'rgba(0, 153, 255, 0.25)' }};">
                                            {{ $stepIdx }}
                                        </div>
                                        <div style="font-size: 0.74rem; font-weight: 800; color: {{ $isCustom ? 'var(--cmyk-magenta)' : 'var(--cmyk-cyan)' }}; text-transform: uppercase; letter-spacing: 0.02em;">
                                            {{ strtoupper($stageInfo['label']) }}
                                        </div>
                                    @else
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--text-disabled); color: var(--text-subtle); display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: 700; margin-bottom: 0.65rem;">
                                            {{ $stepIdx }}
                                        </div>
                                        <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.02em;">
                                            {{ strtoupper($stageInfo['label']) }}
                                        </div>
                                    @endif

                                    <div style="font-size: 0.70rem; color: var(--text-muted); margin-top: 0.25rem; line-height: 1.3;">
                                        {{ $stageInfo['desc'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- SUB-ORDER ITEMS LIST -->
                <div style="background: var(--bg-surface); border-radius: 10px; border: 1px solid var(--border-color); padding: 1.25rem;">
                    <div style="font-size: 0.82rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.85rem;">
                        Items in this Sub-Order
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($subOrder->items as $item)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-page); border: 1px solid var(--border-color); border-radius: 8px;">
                                <div>
                                    <strong style="font-size: 0.95rem; color: var(--text-main);">{{ $item->product ? $item->product->name : 'Item' }}</strong>
                                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                        Qty: <strong>{{ $item->quantity }}</strong> | Unit Price: ₱{{ number_format($item->unit_price, 2) }}
                                    </div>
                                    @if(!empty($item->options_selected))
                                        <div style="margin-top: 4px; display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                            @foreach($item->options_selected as $optK => $optV)
                                                <span style="background: var(--text-disabled); color: var(--text-muted); font-size: 0.72rem; font-weight: 600; padding: 1px 6px; border-radius: 4px;">
                                                    {{ $optK }}: {{ $optV }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div style="text-align: right;">
                                    <span style="font-weight: 800; font-size: 1rem; color: var(--text-main);">
                                        ₱{{ number_format($item->subtotal, 2) }}
                                    </span>
                                    @if($item->files->count() > 0)
                                        <div style="font-size: 0.74rem; color: var(--text-success); font-weight: 700; margin-top: 3px;">
                                            <i class="fa-solid fa-paperclip"></i> {{ $item->files->count() }} Artwork file(s) attached
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @auth
                    @if($subOrder->user_id === Auth::id())
                        <div style="margin-top: 1rem; text-align: right;">
                            <a href="{{ route('customer.orders.show', $subOrder->id) }}" class="btn btn-outline" style="font-size: 0.82rem; padding: 0.4rem 0.85rem;">
                                <i class="fa-solid fa-circle-info"></i> View Sub-Order Details &amp; Discussion
                            </a>
                        </div>
                    @endif
                @endauth
            </div>
        @endforeach

        <!-- ACTION BUTTONS -->
        <div style="border-top: 1px solid var(--border-color); margin-top: 1.5rem; padding-top: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <a href="{{ route('orders.track') }}" class="btn btn-outline" style="font-size: 0.85rem;">
                <i class="fa-solid fa-rotate"></i> Track Another Order
            </a>

            @auth
                @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-primary" style="font-size: 0.88rem; background: var(--success-color); border-color: var(--success-color);">
                        <i class="fa-solid fa-briefcase"></i> View Operational Orders Dashboard
                    </a>
                @elseif($order->user_id === Auth::id())
                    <a href="{{ route('customer.orders.index') }}" class="btn btn-primary" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-list-check"></i> Return to My Orders Dashboard
                    </a>
                @endif
            @endauth
        </div>
    @endif

</div>
@endsection
