@extends('layouts.app')

@section('title', 'Order #' . $order->order_number . ' Tracking & Design Review')

@push('styles')
<style>
    /* Chat + summary split layout: stack into one column on tablets/phones */
    .order-detail-split-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        align-items: start;
    }
    @media (max-width: 1023.98px) {
        .order-detail-split-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('customer.dashboard') }}" style="display: inline-flex; align-items: center; gap: 0.45rem; color: var(--text-inverse); text-decoration: none; font-size: 0.85rem; font-weight: 700; background: var(--accent-color); border: 1.5px solid var(--accent-color); border-radius: 10px; padding: 0.5rem 0.9rem; transition: all 0.2s;" onmouseover="this.style.background='#0080FF';this.style.borderColor='#0080FF'" onmouseout="this.style.background='#0099FF';this.style.borderColor='#0099FF'">
            <i class="fa-solid fa-arrow-left"></i> Back to My Orders
        </a>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-top: 0.25rem;">
            Order #{{ $order->order_number }}
        </h1>
        <span style="font-size: 0.88rem; color: var(--text-muted);">Placed on {{ $order->created_at->timezone('Asia/Manila')->format('F d, Y h:i A') }}</span>
    </div>

    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        @if($order->canBeCancelledByCustomer())
            <button type="button" onclick="openCancelOrderModal()" class="btn" style="background: var(--bg-surface); border: 1.5px solid var(--danger-color); color: var(--text-danger); padding: 0.6rem 1.1rem; font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; transition: all 0.2s;" onmouseover="this.style.background='#FEF2F2'" onmouseout="this.style.background='#FFF'">
                <i class="fa-solid fa-ban"></i> Cancel Order
            </button>
        @endif
        <a href="{{ route('customer.orders.invoice', $order->id) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 0.45rem; color: var(--text-inverse); text-decoration: none; font-size: 0.9rem; font-weight: 700; background: var(--accent-color); border: 1.5px solid var(--accent-color); border-radius: 10px; padding: 0.6rem 1.1rem; transition: all 0.2s;" onmouseover="this.style.background='#0080FF';this.style.borderColor='#0080FF'" onmouseout="this.style.background='#0099FF';this.style.borderColor='#0099FF'">
            <i class="fa-solid fa-print"></i> Download Receipt
        </a>
    </div>
</div>

@if($order->status === 'cancelled' || $order->isCancellationActive())
    <!-- CANCELLATION & REFUND PROGRESS TRACKER -->
    <div class="card p-4 sm:p-6 max-w-full overflow-hidden" style="margin-bottom: 2rem; background: var(--bg-surface); border-radius: 12px; border: 1.5px solid var(--danger-border); box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-danger); font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 0.6rem; margin: 0;">
                <i class="fa-solid fa-rotate-left" style="color: var(--text-danger); font-size: 1.25rem;"></i> Cancellation &amp; Refund Progress Tracker
            </h3>
            <div>
                @if($order->refund_status === 'refunded')
                    <span class="role-badge" style="background: var(--success-soft); color: var(--text-success); font-size: 0.8rem; font-weight: 800; border: 1px solid var(--success-border); padding: 0.35rem 0.75rem;">
                        <i class="fa-solid fa-circle-check"></i> {{ $order->formatted_refund_status }}
                    </span>
                @elseif($order->refund_status === 'in_progress')
                    <span class="role-badge" style="background: var(--warning-soft); color: var(--text-warning); font-size: 0.8rem; font-weight: 800; border: 1px solid var(--warning-border); padding: 0.35rem 0.75rem;">
                        <i class="fa-solid fa-spinner fa-spin"></i> Refund in Progress
                    </span>
                @elseif($order->refund_status === 'not_applicable')
                    <span class="role-badge" style="background: var(--bg-subtle); color: var(--text-muted); font-size: 0.8rem; font-weight: 700; border: 1px solid var(--border-color); padding: 0.35rem 0.75rem;">
                        <i class="fa-solid fa-ban"></i> No Refund Required (Unpaid)
                    </span>
                @else
                    <span class="role-badge" style="background: var(--danger-soft); color: var(--text-danger); font-size: 0.8rem; font-weight: 800; border: 1px solid var(--danger-border); padding: 0.35rem 0.75rem;">
                        <i class="fa-solid fa-ban"></i> Order Cancelled
                    </span>
                @endif
            </div>
        </div>

        @php
            $isPaidOrder = in_array($order->payment_status, ['paid', 'partially_paid', 'refunded']) || $order->refund_status === 'in_progress' || $order->refund_status === 'refunded';
            
            $cancellationStages = [
                1 => ['key' => 'requested', 'label' => 'CANCELLATION REQUESTED', 'desc' => 'Request logged'],
                2 => ['key' => 'review', 'label' => 'UNDER REVIEW', 'desc' => 'Staff acknowledged'],
                3 => ['key' => 'refund_progress', 'label' => $isPaidOrder ? 'REFUND IN PROGRESS' : 'SETTLED', 'desc' => $isPaidOrder ? 'Processing payout' : 'No payout required'],
                4 => ['key' => 'completed', 'label' => $isPaidOrder ? 'REFUND COMPLETED' : 'CLOSED', 'desc' => $isPaidOrder ? 'Amount returned' : 'Order closed'],
            ];

            $cancelStep = 1;
            if ($order->cancellation_status === 'approved' || $order->status === 'cancelled') {
                $cancelStep = 2;
            }
            if ($order->refund_status === 'in_progress') {
                $cancelStep = 3;
            }
            if ($order->refund_status === 'refunded' || ($order->status === 'cancelled' && $order->refund_status === 'not_applicable')) {
                $cancelStep = 4;
            }
        @endphp

        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.5rem; text-align: center; justify-items: center; position: relative;">
            @foreach($cancellationStages as $stepNum => $cStage)
                @php
                    $isPast = $stepNum < $cancelStep;
                    $isCurrent = $stepNum === $cancelStep;
                    $isFuture = $stepNum > $cancelStep;
                @endphp
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; width: 100%;">
                    @if($isPast)
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--success-color); color: var(--text-inverse); display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: 800; margin-bottom: 0.65rem;">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="text-[10px] sm:text-xs px-1" style="font-weight: 800; color: var(--text-success); text-transform: uppercase;">
                            {{ $cStage['label'] }}
                        </div>
                        <div class="text-[10px] sm:text-xs px-1" style="color: var(--text-muted); margin-top: 0.15rem;">{{ $cStage['desc'] }}</div>
                    @elseif($isCurrent)
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--danger-color); color: var(--text-inverse); display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: 800; margin-bottom: 0.65rem; box-shadow: 0 0 0 5px rgba(220, 38, 38, 0.2);">
                            {{ $stepNum }}
                        </div>
                        <div class="text-[10px] sm:text-xs px-1" style="font-weight: 800; color: var(--text-danger); text-transform: uppercase;">
                            {{ $cStage['label'] }}
                        </div>
                        <div class="text-[10px] sm:text-xs px-1" style="color: var(--text-danger); font-weight: 600; margin-top: 0.15rem;">{{ $cStage['desc'] }}</div>
                    @else
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--text-disabled); color: var(--text-inverse); display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: 700; margin-bottom: 0.65rem;">
                            {{ $stepNum }}
                        </div>
                        <div class="text-[10px] sm:text-xs px-1" style="font-weight: 700; color: var(--text-subtle); text-transform: uppercase;">
                            {{ $cStage['label'] }}
                        </div>
                        <div class="text-[10px] sm:text-xs px-1" style="color: var(--text-subtle); margin-top: 0.15rem;">{{ $cStage['desc'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Cancellation Details Callout Banner -->
        <div style="margin-top: 1.5rem; background: var(--danger-soft); border: 1px solid var(--danger-border); border-radius: 8px; padding: 1rem 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <strong style="color: var(--text-danger); font-size: 0.88rem; display: block; margin-bottom: 0.25rem;">
                        <i class="fa-solid fa-circle-info"></i> Reason for Cancellation:
                    </strong>
                    <p style="font-size: 0.85rem; color: var(--text-danger); line-height: 1.45; margin: 0;">
                        {{ $order->cancellation_reason ?: ($order->notes ?: 'Customer requested cancellation.') }}
                    </p>
                </div>
                @if($order->cancelled_at || $order->cancellation_requested_at)
                    <div style="font-size: 0.78rem; color: var(--text-danger); text-align: right;">
                        <strong>Cancelled Date:</strong><br>
                        {{ ($order->cancelled_at ?: $order->cancellation_requested_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                    </div>
                @endif
            </div>

            @if($order->refund_status === 'in_progress' || $order->refund_status === 'refunded')
                <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px dashed var(--danger-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; font-size: 0.84rem;">
                    <span style="color: var(--text-danger);">
                        <strong>Refund Method:</strong> {{ $order->refund_method ?: 'Original Payment Method' }}
                        @if($order->refund_reference)
                            &bull; <strong>Ref #:</strong> {{ $order->refund_reference }}
                        @endif
                    </span>
                    <strong style="color: var(--text-danger); font-size: 0.95rem;">
                        Refund Amount: ₱{{ number_format($order->refund_amount ?: $order->payable_now_amount, 2) }}
                    </strong>
                </div>
            @endif
        </div>
    </div>
@else
    @if(!empty($order->parent_id) && $order->parentOrder)
        <!-- SUB-ORDER BANNER LINKING TO PARENT -->
        <div style="background: var(--bg-page); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div style="width: 42px; height: 42px; border-radius: 50%; background: var(--accent-soft); color: var(--text-accent); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-code-fork"></i>
                </div>
                <div>
                    <strong style="color: var(--text-accent); font-size: 1rem; display: block;">Part of Split Order #{{ $order->parentOrder->order_number }}</strong>
                    <span style="font-size: 0.82rem; color: var(--text-accent);">This is an independent sub-order for {{ $order->is_custom_order ? 'Customized Print Items' : 'Ready-Made Products' }}.</span>
                </div>
            </div>
            <a href="{{ route('customer.orders.show', $order->parentOrder->id) }}" class="btn btn-primary" style="padding: 0.45rem 1rem; font-size: 0.82rem;">
                <i class="fa-solid fa-layer-group"></i> View Parent Split Order
            </a>
        </div>
    @endif

    @if($order->is_parent || $order->subOrders->isNotEmpty())
        <!-- PARENT SPLIT ORDER HUB: RENDER BOTH SUB-ORDERS -->
        <div class="card p-4 sm:p-6 max-w-full overflow-hidden" style="margin-bottom: 2rem; background: var(--bg-surface); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                <div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); font-family: 'Outfit', sans-serif; margin: 0; display: flex; align-items: center; gap: 0.6rem;">
                        <i class="fa-solid fa-code-fork" style="color: var(--text-accent);"></i> Split Order Tracking Hub ({{ $order->subOrders->count() }} Sub-Orders)
                    </h3>
                    <div style="font-size: 0.84rem; color: var(--text-muted); margin-top: 0.25rem;">
                        Your items were split so regular products can be picked up or shipped immediately while custom items undergo artwork review and production.
                    </div>
                </div>
                <span class="role-badge" style="background: var(--success-soft); color: var(--text-success); border: 1px solid var(--success-border); font-size: 0.8rem; font-weight: 800;">
                    <i class="fa-solid fa-circle-check"></i> Multi-Status Tracking Active
                </span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1.75rem;">
                @foreach($order->subOrders as $childOrder)
                    @php
                        $childCustom = (bool) $childOrder->is_custom_order;
                        $cStages = $childOrder->workflow_stages;
                        $cStep = $childOrder->current_workflow_step;
                        $cDelivery = $childOrder->fulfillment_type === 'delivery';
                    @endphp
                    <div style="background: var(--bg-page); border: 1.5px solid {{ $childCustom ? 'var(--brand-magenta-soft)' : 'var(--info-border)' }}; border-radius: 10px; padding: 1.25rem 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                    <span class="role-badge" style="background: {{ $childCustom ? 'var(--brand-magenta-soft)' : 'var(--accent-soft)' }}; color: {{ $childCustom ? 'var(--text-magenta)' : 'var(--text-accent)' }}; font-weight: 800; font-size: 0.75rem;">
                                        <i class="fa-solid {{ $childCustom ? 'fa-palette' : 'fa-box' }}"></i>
                                        {{ $childCustom ? 'Custom Print Sub-Order' : 'Ready-Made Sub-Order' }}
                                    </span>
                                    <span style="font-size: 0.75rem; background: var(--bg-surface); border: 1px solid var(--border-color); padding: 1px 7px; border-radius: 9999px; color: var(--text-muted); font-weight: 600;">
                                        <i class="fa-solid {{ $cDelivery ? 'fa-truck-fast' : 'fa-store' }}"></i>
                                        {{ $cDelivery ? 'Courier Delivery' : 'Store Pick-up' }}
                                    </span>
                                    @if($childCustom && $childOrder->formatted_production_time)
                                        <span style="font-size: 0.75rem; background: var(--warning-soft); border: 1px solid var(--warning-border); padding: 1px 8px; border-radius: 9999px; color: var(--text-warning); font-weight: 700;">
                                            <i class="fa-solid fa-stopwatch"></i> Est. Production: {{ $childOrder->formatted_production_time }}
                                        </span>
                                    @endif
                                </div>

                                <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); font-family: monospace; margin: 0;">
                                    #{{ $childOrder->order_number }}
                                </h4>
                            </div>

                            <div style="text-align: right;">
                                <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted);">Current Status:</div>
                                <span style="font-size: 0.85rem; font-weight: 800; color: {{ $childCustom ? 'var(--cmyk-magenta)' : 'var(--cmyk-cyan)' }};">
                                    {{ ucwords(str_replace('_', ' ', $childOrder->status)) }}
                                </span>
                                <div style="margin-top: 0.35rem;">
                                    <a href="{{ route('customer.orders.show', $childOrder->id) }}" class="btn btn-outline" style="font-size: 0.78rem; padding: 0.3rem 0.7rem;">
                                        View Details &amp; Chat &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar for child order -->
                        <div style="display: grid; grid-template-columns: repeat({{ count($cStages) }}, minmax(0, 1fr)); gap: 0.5rem; text-align: center; justify-items: center; position: relative;">
                            @foreach($cStages as $sKey => $sVal)
                                @php
                                    $stepIdx = $sVal['step'];
                                    $isPast = $cStep !== -1 && $stepIdx < $cStep;
                                    $isCurr = $cStep !== -1 && $stepIdx === $cStep;
                                @endphp
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; width: 100%;">
                                    @if($isPast)
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--success-color); color: var(--text-inverse); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800; margin-bottom: 0.4rem;">
                                            <i class="fa-solid fa-check"></i>
                                        </div>
                                        <div class="text-[10px] sm:text-xs px-1" style="font-weight: 800; color: var(--text-success); text-transform: uppercase;">
                                            {{ $sVal['label'] }}
                                        </div>
                                    @elseif($isCurr)
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $childCustom ? 'var(--cmyk-magenta)' : 'var(--cmyk-cyan)' }}; color: var(--text-inverse); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800; margin-bottom: 0.4rem; box-shadow: 0 0 0 4px {{ $childCustom ? 'rgba(236, 0, 140, 0.25)' : 'rgba(0, 153, 255, 0.25)' }};">
                                            {{ $stepIdx }}
                                        </div>
                                        <div class="text-[10px] sm:text-xs px-1" style="font-weight: 800; color: {{ $childCustom ? 'var(--cmyk-magenta)' : 'var(--cmyk-cyan)' }}; text-transform: uppercase;">
                                            {{ $sVal['label'] }}
                                        </div>
                                    @else
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--text-disabled); color: var(--text-subtle); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem;">
                                            {{ $stepIdx }}
                                        </div>
                                        <div class="text-[10px] sm:text-xs px-1" style="font-weight: 700; color: var(--text-subtle); text-transform: uppercase;">
                                            {{ $sVal['label'] }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- REAL-TIME ORDER STATUS TRACKER COMPONENT (STANDALONE OR SUB-ORDER) -->
        <div class="card p-4 sm:p-6 max-w-full overflow-hidden" style="margin-bottom: 2rem; background: var(--bg-surface); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 0.5rem;">
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); font-family: 'Outfit', sans-serif; margin: 0; display: flex; align-items: center; gap: 0.6rem;">
                    <i class="fa-solid fa-diagram-project" style="color: {{ $order->is_custom_order ? 'var(--cmyk-magenta)' : 'var(--cmyk-cyan)' }}; font-size: 1.2rem;"></i>
                    Real-Time Order Status Tracker
                </h3>
                <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 700;">
                    Workflow: <strong style="color: {{ $order->is_custom_order ? 'var(--cmyk-magenta)' : 'var(--text-main)' }};">{{ $order->is_custom_order ? 'Custom Production' : 'Store Pickup / Delivery' }}</strong>
                </span>
            </div>

            @php
                $stages = $order->workflow_stages;
                $currentIdx = $order->current_workflow_step;
            @endphp

            @if($order->is_custom_order && $order->formatted_production_time)
                <div style="margin-bottom: 1.5rem; background: var(--warning-soft); border: 1px solid var(--warning-border); border-radius: 8px; padding: 0.65rem 1rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                    <span style="font-size: 0.82rem; font-weight: 700; color: var(--text-warning); display: inline-flex; align-items: center; gap: 0.4rem;">
                        <i class="fa-solid fa-stopwatch" style="color: var(--text-warning);"></i> Estimated Production Turnaround:
                    </span>
                    <span style="font-size: 0.84rem; font-weight: 800; color: var(--text-warning); background: var(--warning-soft); padding: 0.2rem 0.65rem; border-radius: 9999px;">
                        {{ $order->formatted_production_time }}
                    </span>
                </div>
            @endif

            <div style="display: grid; grid-template-columns: repeat({{ count($stages) }}, minmax(0, 1fr)); gap: 0.5rem; text-align: center; justify-items: center; position: relative;">
                @foreach($stages as $key => $stageInfo)
                    @php
                        $stepNum = $stageInfo['step'];
                        $isCompleted = $currentIdx !== -1 && $stepNum < $currentIdx;
                        $isCurrent = $currentIdx !== -1 && $stepNum === $currentIdx;
                        $isUpcoming = !$isCompleted && !$isCurrent;
                    @endphp

                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; width: 100%;">
                        <!-- CIRCULAR STEP BADGE -->
                        @if($isCompleted)
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--success-color); color: var(--text-inverse); display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: 800; margin-bottom: 0.65rem;">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="text-[10px] sm:text-xs px-1" style="font-weight: 800; color: var(--text-success); text-transform: uppercase; letter-spacing: 0.02em;">
                                {{ $stageInfo['label'] }}
                            </div>
                        @elseif($isCurrent)
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $order->is_custom_order ? 'var(--cmyk-magenta)' : 'var(--cmyk-cyan)' }}; color: var(--text-inverse); display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: 800; margin-bottom: 0.65rem; box-shadow: 0 0 0 5px {{ $order->is_custom_order ? 'rgba(236, 0, 140, 0.25)' : 'rgba(0, 153, 255, 0.25)' }};">
                                {{ $stepNum }}
                            </div>
                            <div class="text-[10px] sm:text-xs px-1" style="font-weight: 800; color: {{ $order->is_custom_order ? 'var(--cmyk-magenta)' : 'var(--cmyk-cyan)' }}; text-transform: uppercase; letter-spacing: 0.02em;">
                                {{ $stageInfo['label'] }}
                            </div>
                        @else
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--text-disabled); color: var(--text-inverse); display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: 700; margin-bottom: 0.65rem;">
                                {{ $stepNum }}
                            </div>
                            <div class="text-[10px] sm:text-xs px-1" style="font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.02em;">
                                {{ $stageInfo['label'] }}
                            </div>
                        @endif
                        <div class="text-[10px] sm:text-xs px-1" style="color: var(--text-muted); margin-top: 0.2rem; line-height: 1.3;">
                            {{ $stageInfo['desc'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif

<div class="order-detail-split-grid">
    <!-- LEFT COLUMN: CHAT THREAD -->
    <div>
        <!-- ORDER-SPECIFIC CHAT THREAD (CUSTOMER <-> ADMIN STAFF) -->
        <div class="card p-4 sm:p-6 max-w-full overflow-hidden">
            <h3 class="text-base sm:text-lg font-bold break-words leading-tight" style="color: var(--text-main); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                <i class="fa-solid fa-comments" style="color: var(--accent-color);"></i> Order Support Messages &amp; Discussion
            </h3>

            <div id="orderChatMessages" style="max-height: 350px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 1.25rem; padding-right: 0.5rem; scroll-behavior: smooth;">
                @forelse($order->messages as $msg)
                    @php $isMe = $msg->user_id === Auth::id(); @endphp
                    <div style="align-self: {{ $isMe ? 'flex-end' : 'flex-start' }}; max-width: 80%; background: {{ $isMe ? 'var(--accent-soft)' : 'var(--bg-subtle)' }}; border: 1px solid {{ $isMe ? 'var(--info-border)' : 'var(--border-color)' }}; padding: 0.75rem 1rem; border-radius: var(--radius-md);">
                        <div style="font-size: 0.78rem; font-weight: 700; color: {{ $isMe ? 'var(--accent-color)' : 'var(--primary-dark)' }}; margin-bottom: 0.2rem;">
                            {{ $isMe ? 'You' : ($msg->sender ? $msg->sender->name . ' (Staff)' : 'Grafika Staff') }} • {{ $msg->created_at->timezone('Asia/Manila')->format('M d, h:i A') }}
                        </div>
                        @if($msg->message)
                            <div style="font-size: 0.9rem; color: var(--text-main); line-height: 1.4; word-break: break-word;">{{ $msg->message }}</div>
                        @endif
                        @if($msg->attachment_path)
                            <div style="margin-top: {{ $msg->message ? '0.5rem' : '0' }};">
                                <a href="{{ asset('storage/' . $msg->attachment_path) }}" target="_blank" title="Click to view full image">
                                    <img src="{{ asset('storage/' . $msg->attachment_path) }}" alt="Attached image" style="max-width: 100%; max-height: 220px; border-radius: 8px; object-fit: contain; background: rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.08); display: block;">
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <p id="chatEmptyState" style="font-size: 0.85rem; color: var(--text-muted); text-align: center;">No messages yet. Send a message to staff regarding this order below.</p>
                @endforelse
            </div>

            <!-- SEND MESSAGE FORM -->
            <form id="orderChatForm" action="{{ route('customer.orders.chat', $order->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- PREVIEW CONTAINER FOR SELECTED ATTACHMENT -->
                <div id="attachmentPreviewContainer" style="display: none; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; background: var(--bg-subtle); padding: 0.35rem 0.65rem; border-radius: 8px; width: fit-content; border: 1px solid var(--border-color);">
                    <img id="attachmentPreviewImg" src="" alt="Preview" style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;">
                    <span id="attachmentFileName" style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
                    <button type="button" id="removeAttachmentBtn" style="background: none; border: none; color: var(--text-danger); cursor: pointer; font-size: 0.9rem; padding: 0 0.25rem; display: flex; align-items: center;" title="Remove image">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2 w-full mt-3">
                    <input type="file" id="chatAttachmentInput" name="attachment" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" style="display: none;">
                    {{-- Compact attachment chip (JS on chatAttachmentBtn opens the hidden input) --}}
                    <button type="button" id="chatAttachmentBtn" title="Attach Image" class="w-10 h-10 shrink-0 rounded-xl border-solid border-0 outline-none focus:outline-none flex items-center justify-center transition-colors shadow-2xs active:scale-95 cursor-pointer" style="background: var(--accent-soft); color: var(--text-accent);" onmouseover="this.style.background='#DBEAFE'" onmouseout="this.style.background='#EFF6FF'">
                        <i class="fa-solid fa-paperclip"></i>
                    </button>
                    {{-- Flexible text input: min-w-0 keeps it inside the flex row on narrow screens --}}
                    <input type="text" id="chatMessageInput" name="message" placeholder="Type message or question here..."
                           class="flex-1 min-w-0 h-10 px-3 bg-gray-50 border border-solid border-gray-200 rounded-xl text-xs sm:text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:bg-white transition-all">
                    {{-- Compact send button: icon-only on phones, icon + label from sm: up --}}
                    <button type="submit" id="chatSendBtn" title="Send message"
                            class="h-10 px-3.5 sm:px-5 font-bold text-xs rounded-xl flex items-center gap-1.5 shrink-0 border-solid border-0 outline-none focus:outline-none transition-all active:scale-95 shadow-2xs cursor-pointer" style="background: var(--accent-color); color: var(--text-inverse);" onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span class="hidden sm:inline">Send</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- RIGHT COLUMN: ORDER SUMMARY & INVOICE & PAYMENT PROOF -->
    <div>
        <div class="card p-4 sm:p-6 max-w-full overflow-hidden" style="margin-bottom: 1.5rem; {{ $order->status === 'cancelled' ? 'border: 1.5px solid var(--danger-border); background: var(--bg-surface);' : '' }}">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: {{ $order->status === 'cancelled' ? 'var(--text-danger)' : 'var(--primary-dark)' }}; margin: 0;">
                        @if($order->status === 'cancelled')
                            Cancelled Items Breakdown
                        @elseif($order->is_parent)
                            Parent Order Breakdown
                        @elseif($order->parent_id)
                            Sub-Order Breakdown
                        @else
                            Order Breakdown
                        @endif
                    </h3>
                    @if($order->parent_id && $order->parentOrder)
                        <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 2px;">
                            Part of Parent Order <a href="{{ route('customer.orders.show', $order->parentOrder->id) }}" style="color: var(--text-accent); font-weight: 700;">#{{ $order->parentOrder->order_number }}</a>
                        </div>
                    @endif
                </div>
                @if($order->status === 'cancelled')
                    <span style="font-size: 0.72rem; font-weight: 800; background: var(--danger-soft); color: var(--text-danger); padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid var(--danger-border);">
                        <i class="fa-solid fa-ban"></i> CANCELLED
                    </span>
                @elseif($order->parent_id)
                    <span style="font-size: 0.72rem; font-weight: 800; background: var(--accent-soft); color: var(--text-accent); padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid var(--info-border);">
                        <i class="fa-solid fa-code-fork"></i> SUB-ORDER
                    </span>
                @endif
            </div>

            <!-- ITEMS LIST SUMMARY -->
            <div style="margin-bottom: 1rem;">
                @foreach($order->items as $item)
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.6rem; padding-bottom: 0.6rem; border-bottom: 1px dashed var(--border-color);">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                <strong style="color: var(--text-main); font-size: 0.92rem;">{{ $item->product ? $item->product->name : 'Product Item' }}</strong>
                                @if($order->status === 'cancelled')
                                    <span style="font-size: 0.68rem; font-weight: 800; background: var(--danger-soft); color: var(--text-danger); padding: 0.1rem 0.4rem; border-radius: 4px;">CANCELLED</span>
                                @endif
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <span>Qty: {{ $item->quantity }}</span>
                                @if($item->formatted_production_time)
                                    <span style="font-size: 0.72rem; background: var(--warning-soft); color: var(--text-warning); border: 1px solid var(--warning-border); border-radius: 9999px; padding: 1px 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <i class="fa-solid fa-stopwatch"></i> Est. Production: {{ $item->formatted_production_time }}
                                    </span>
                                @endif
                            </div>
                            @if(!empty($item->options_selected))
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">
                                    @foreach($item->options_selected as $oK => $oV)
                                        <span><strong>{{ $oK }}:</strong> {{ $oV }}</span>{{ !$loop->last ? ' | ' : '' }}
                                    @endforeach
                                </div>
                            @endif

                            {{-- Rate & Review action for completed orders --}}
                            @if($order->status === 'completed' && $item->product)
                                @php
                                    $hasReviewedItem = $order->reviews->firstWhere('product_id', $item->product_id) !== null;
                                @endphp
                                <div style="margin-top: 0.5rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    @if($hasReviewedItem)
                                        <span style="font-size: 0.72rem; font-weight: 700; background: var(--success-soft); color: var(--text-success); border: 1px solid var(--success-border); padding: 0.2rem 0.55rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                            <i class="fa-solid fa-circle-check"></i> Reviewed
                                        </span>
                                    @else
                                        <button type="button" onclick="openReviewModal({{ $item->id }}, @js($item->product->name), @js($item->product->image_path ? asset('storage/' . $item->product->image_path) : null), @js($order->order_number))" class="btn-rate-review"
                                            style="font-size: 0.75rem; font-weight: 700; background: var(--warning-soft); color: var(--text-warning); border: 1.5px solid var(--warning-border); padding: 0.3rem 0.7rem; border-radius: 9999px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.2s;"
                                            onmouseover="this.style.background='#FFEDD5'" onmouseout="this.style.background='#FFF7ED'">
                                            <i class="fa-solid fa-star"></i> Rate &amp; Review Product
                                        </button>
                                    @endif
                                </div>
                            @endif

                        </div>
                        <div style="font-weight: 700; color: {{ $order->status === 'cancelled' ? 'var(--text-danger)' : 'var(--primary-dark)' }}; font-size: 0.9rem;">
                            ₱{{ number_format($item->subtotal ?: ($item->unit_price * $item->quantity), 2) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.9rem;">
                <span style="color: var(--text-muted);">Status:</span>
                <strong style="text-transform: uppercase; color: {{ $order->status === 'cancelled' ? 'var(--text-danger)' : 'var(--text-main)' }};">
                    {{ str_replace('_', ' ', $order->status) }}
                </strong>
            </div>

            @if($order->refund_status && $order->refund_status !== 'none')
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.9rem;">
                    <span style="color: var(--text-muted);">Refund Status:</span>
                    <strong style="color: {{ $order->refund_status === 'refunded' ? 'var(--text-success)' : ($order->refund_status === 'in_progress' ? 'var(--text-warning)' : 'var(--text-muted)') }};">
                        {{ $order->formatted_refund_status }}
                    </strong>
                </div>
            @endif

            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.9rem;">
                <span style="color: var(--text-muted);">Payment Term:</span>
                <strong style="color: var(--text-main);">{{ $order->formatted_payment_plan }}</strong>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.9rem;">
                <span style="color: var(--text-muted);">Payment Status:</span>
                <strong style="text-transform: uppercase; color: {{ $order->payment_status === 'paid' ? 'var(--text-success)' : ($order->payment_status === 'partially_paid' ? 'var(--text-accent)' : ($order->payment_status === 'refunded' ? 'var(--text-success)' : ($order->payment_status === 'cancelled' ? 'var(--text-muted)' : 'var(--text-warning)'))) }};">
                    {{ str_replace('_', ' ', $order->payment_status) }}
                </strong>
            </div>

            @if($order->is_downpayment)
                <div style="background: var(--accent-soft); border: 1px solid var(--info-border); border-radius: 8px; padding: 0.75rem 0.85rem; margin-bottom: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.35rem;">
                        <span style="color: var(--text-accent); font-weight: 600;">Amount Paid (50% Downpayment):</span>
                        <strong style="color: var(--text-accent);">₱{{ number_format($order->downpayment_amount, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                        <div>
                            <span style="color: var(--text-accent); font-weight: 700;">Remaining Balance Due:</span>
                            <div style="font-size: 0.74rem; color: var(--text-accent); font-weight: 600;">Remaining Balance to be Paid on Delivery/Pickup:</div>
                        </div>
                        <strong style="color: var(--accent-color); font-weight: 800;">₱{{ number_format($order->remaining_balance, 2) }}</strong>
                    </div>
                </div>
            @endif

            @if($order->custom_terms_agreed)
            <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.8rem; color:var(--text-accent); font-weight:600; margin-bottom:0.75rem;">
                <i class="fa-solid fa-circle-check" style="color:var(--text-accent);"></i> Custom Order Terms &amp; Conditions Agreed
            </div>
            @endif

            <div style="border-top: 1px solid var(--border-color); padding-top: 0.75rem; margin-top: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 700; color: var(--text-main);">
                    {{ $order->parent_id ? 'Sub-Order Total:' : ($order->is_parent ? 'Combined Parent Total:' : 'Total Order Value:') }}
                </span>
                <span style="font-size: 1.5rem; font-weight: 800; color: {{ $order->status === 'cancelled' ? 'var(--text-danger)' : 'var(--text-main)' }};">
                    ₱{{ number_format($order->total_price ?: $order->total_amount, 2) }}
                </span>
            </div>
        </div>

        <!-- PAYMENT METHOD & PROOF SCREENSHOT DISPLAY -->
        <div class="card p-4 sm:p-6 max-w-full overflow-hidden" style="border: 1px solid var(--border-color); background: var(--bg-surface);">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-credit-card" style="color: var(--brand-cyan-fg);"></i> Payment & Receipt Screenshot
            </h3>

            @php
                $paymentMethod = $order->effective_payment_method;
                $proofScreenshot = $order->payment_proof_path;
                $isCashPayment = str_contains(strtolower($paymentMethod), 'cash')
                    || str_contains(strtolower($paymentMethod), 'cod')
                    || str_contains(strtolower($paymentMethod), 'pickup')
                    || str_contains(strtolower($paymentMethod), 'store');
            @endphp

            <div style="font-size: 0.9rem; margin-bottom: 0.75rem;">
                <span style="color: var(--text-muted);">Mode of Payment Selected:</span><br>
                <strong style="font-size: 1.05rem; color: var(--text-main);"><i class="fa-solid fa-wallet" style="color: var(--brand-cyan-fg);"></i> {{ $paymentMethod }}</strong>
            </div>

            @if($proofScreenshot || !$isCashPayment)
            <div style="margin-bottom: 1rem;">
                <span style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 0.4rem;">
                    Payment Screenshot:
                </span>
                @if($proofScreenshot)
                    <div style="border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; background: var(--bg-surface); text-align: center;">
                        <a href="{{ asset('storage/' . $proofScreenshot) }}" target="_blank" title="Click to view full screenshot">
                            <img src="{{ asset('storage/' . $proofScreenshot) }}" alt="Payment Receipt Screenshot" style="width: 100%; max-height: 200px; object-fit: contain; background: var(--text-main); padding: 0.4rem;">
                        </a>
                        <div style="padding: 0.5rem; background: var(--bg-page); border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-success);"><i class="fa-solid fa-circle-check"></i> Screenshot Uploaded</span>
                            <a href="{{ asset('storage/' . $proofScreenshot) }}" download class="btn btn-outline" style="font-size: 0.72rem; padding: 0.2rem 0.5rem;">
                                <i class="fa-solid fa-download"></i> View
                            </a>
                        </div>
                    </div>
                @else
                    <div style="background: var(--warning-soft); border: 1px dashed var(--warning-border); padding: 1rem; border-radius: 8px; text-align: center; color: var(--text-warning); font-size: 0.82rem;">
                        <i class="fa-solid fa-clock" style="margin-right: 0.25rem;"></i>
                        No payment screenshot attached yet. Please upload your receipt if you have already paid.
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Review submission feedback + shared Rate & Review modal (with product picture) --}}
@include('customer.partials.review-modal')

@if($order->canBeCancelledByCustomer())
<!-- CANCEL ORDER MODAL -->
<div id="cancelOrderModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(15,23,42,0.7); z-index:99999; align-items:center; justify-content:center; padding:1.5rem 1rem; backdrop-filter:blur(4px);">
    <div style="background:var(--bg-surface); border-radius:16px; max-width:540px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); border:1px solid var(--border-color); overflow:hidden;">
        <!-- Modal Header -->
        <div style="background:var(--danger-soft); border-bottom:1px solid var(--danger-border); padding:1.25rem 1.5rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.6rem;">
                <div style="width:36px; height:36px; border-radius:50%; background:var(--danger-soft); color:var(--text-danger); display:flex; align-items:center; justify-content:center; font-size:1rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 style="font-size:1.1rem; font-weight:800; color:var(--text-danger); margin:0;">Cancel Order #{{ $order->order_number }}</h3>
                    <div style="font-size:0.75rem; color:var(--text-danger);">This action cannot be undone once confirmed</div>
                </div>
            </div>
            <button type="button" onclick="closeCancelOrderModal()" style="background:none; border:none; color:var(--text-danger); font-size:1.2rem; cursor:pointer; padding:0.25rem;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('customer.orders.cancel', $order->id) }}" method="POST" style="padding:1.5rem;">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.85rem; font-weight:700; color:var(--text-main); display:block; margin-bottom:0.4rem;">
                    Reason for Cancellation <span style="color:var(--text-danger);">*</span>
                </label>
                <select id="cancellation_reason_select" onchange="handleReasonSelectChange(this)" style="width:100%; padding:0.6rem 0.8rem; border:1.5px solid var(--border-color); border-radius:8px; font-size:0.88rem; margin-bottom:0.5rem;" required>
                    <option value="">-- Select a reason --</option>
                    <option value="Changed mind / No longer needed">Changed mind / No longer needed</option>
                    <option value="Need to change order details or artwork file">Need to change order details or artwork file</option>
                    <option value="Ordered duplicate / placed by mistake">Ordered duplicate / placed by mistake</option>
                    <option value="Turnaround time / delivery timeline is too long">Turnaround time / delivery timeline is too long</option>
                    <option value="Other">Other reason (specify below)</option>
                </select>
                <textarea name="cancellation_reason" id="cancellation_reason_textarea" rows="3" placeholder="Please provide additional details regarding your cancellation reason..." style="width:100%; padding:0.6rem 0.8rem; border:1.5px solid var(--border-color); border-radius:8px; font-size:0.88rem; resize:vertical;" required minlength="5"></textarea>
            </div>

            @if(in_array($order->payment_status, ['paid', 'partially_paid']))
                <div style="background:var(--success-soft); border:1px solid var(--success-border); border-radius:8px; padding:0.85rem 1rem; margin-bottom:1rem;">
                    <div style="font-weight:700; font-size:0.85rem; color:var(--text-success); display:flex; align-items:center; gap:0.4rem; margin-bottom:0.25rem;">
                        <i class="fa-solid fa-money-bill-transfer"></i> Refund Information
                    </div>
                    <div style="font-size:0.8rem; color:var(--text-success); line-height:1.4;">
                        You have paid <strong>₱{{ number_format($order->payable_now_amount, 2) }}</strong>. Upon cancellation, our billing staff will process your refund to your GCash / payment account.
                    </div>
                    <div style="margin-top:0.5rem;">
                        <label style="font-size:0.78rem; font-weight:700; color:var(--text-success); display:block; margin-bottom:0.2rem;">
                            Preferred Refund Channel &amp; Account / GCash (Optional):
                        </label>
                        <input type="text" name="refund_method" placeholder="e.g., GCash - 09171234567 (Juan Dela Cruz)" style="width:100%; padding:0.45rem 0.7rem; border:1px solid var(--success-border); border-radius:6px; font-size:0.82rem; background:var(--bg-surface);">
                    </div>
                </div>
            @endif

            <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem;">
                <button type="button" onclick="closeCancelOrderModal()" class="btn btn-outline" style="padding:0.55rem 1.1rem; font-size:0.88rem;">
                    Keep Order
                </button>
                <button type="submit" class="btn" style="background:var(--danger-color); color:var(--text-inverse); font-weight:700; padding:0.55rem 1.25rem; font-size:0.88rem; border-radius:8px;">
                    <i class="fa-solid fa-ban"></i> Confirm Cancellation
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatBox = document.getElementById('orderChatMessages');
    const chatForm = document.getElementById('orderChatForm');
    const chatInput = document.getElementById('chatMessageInput');
    const chatBtn = document.getElementById('chatSendBtn');

    const attachmentBtn = document.getElementById('chatAttachmentBtn');
    const attachmentInput = document.getElementById('chatAttachmentInput');
    const previewContainer = document.getElementById('attachmentPreviewContainer');
    const previewImg = document.getElementById('attachmentPreviewImg');
    const previewName = document.getElementById('attachmentFileName');
    const removeAttachmentBtn = document.getElementById('removeAttachmentBtn');

    if (attachmentBtn && attachmentInput) {
        attachmentBtn.addEventListener('click', function () {
            attachmentInput.click();
        });

        attachmentInput.addEventListener('change', function () {
            if (attachmentInput.files && attachmentInput.files[0]) {
                const file = attachmentInput.files[0];
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewName.textContent = file.name;
                    previewContainer.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            }
        });

        if (removeAttachmentBtn) {
            removeAttachmentBtn.addEventListener('click', function () {
                attachmentInput.value = '';
                previewImg.src = '';
                previewName.textContent = '';
                previewContainer.style.display = 'none';
            });
        }
    }

    function scrollToBottom(smooth = false) {
        if (!chatBox) return;
        if (smooth) {
            chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
        } else {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }

    // Auto-scroll on initial page load
    scrollToBottom(false);

    if (chatForm) {
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const message = chatInput.value.trim();
            const hasAttachment = attachmentInput && attachmentInput.files && attachmentInput.files.length > 0;
            
            if (!message && !hasAttachment) return;

            const formData = new FormData(chatForm);
            if (!formData.get('message') && message) {
                formData.set('message', message);
            }

            const originalBtnHtml = chatBtn.innerHTML;
            chatBtn.disabled = true;
            chatInput.disabled = true;
            if (attachmentBtn) attachmentBtn.disabled = true;
            chatBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]') 
                ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                : (chatForm.querySelector('input[name="_token"]') ? chatForm.querySelector('input[name="_token"]').value : '');

            fetch(chatForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    const emptyState = document.getElementById('chatEmptyState');
                    if (emptyState) {
                        emptyState.remove();
                    }

                    const bubble = document.createElement('div');
                    bubble.style.cssText = "align-self: flex-end; max-width: 80%; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 0.75rem 1rem; border-radius: var(--radius-md);";
                    
                    const header = document.createElement('div');
                    header.style.cssText = "font-size: 0.78rem; font-weight: 700; color: var(--accent-color); margin-bottom: 0.2rem;";
                    header.textContent = `You • ${data.data.formatted_time}`;
                    bubble.appendChild(header);

                    if (data.data.message) {
                        const body = document.createElement('div');
                        body.style.cssText = "font-size: 0.9rem; color: var(--text-main); line-height: 1.4; word-break: break-word;";
                        body.textContent = data.data.message;
                        bubble.appendChild(body);
                    }

                    if (data.data.attachment_url) {
                        const imgContainer = document.createElement('div');
                        imgContainer.style.cssText = "margin-top: " + (data.data.message ? "0.5rem" : "0") + ";";
                        imgContainer.innerHTML = `<a href="${data.data.attachment_url}" target="_blank" title="Click to view full image"><img src="${data.data.attachment_url}" alt="Attached image" style="max-width: 100%; max-height: 220px; border-radius: 8px; object-fit: contain; background: rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.08); display: block;"></a>`;
                        bubble.appendChild(imgContainer);
                    }

                    chatBox.appendChild(bubble);

                    chatInput.value = '';
                    if (attachmentInput) {
                        attachmentInput.value = '';
                        if (previewContainer) previewContainer.style.display = 'none';
                        if (previewImg) previewImg.src = '';
                        if (previewName) previewName.textContent = '';
                    }

                    scrollToBottom(true);
                }
            })
            .catch(error => {
                console.error('Chat error:', error);
                const errMsg = (error && error.message) || (error && error.errors && Object.values(error.errors).flat().join('\n')) || 'Failed to send message. Please try again.';
                alert(errMsg);
            })
            .finally(() => {
                chatBtn.disabled = false;
                chatInput.disabled = false;
                if (attachmentBtn) attachmentBtn.disabled = false;
                chatBtn.innerHTML = originalBtnHtml;
                chatInput.focus();
            });
        });
    }
});

function openCancelOrderModal() {
    const modal = document.getElementById('cancelOrderModal');
    if (modal) {
        modal.style.display = 'flex';
        const select = document.getElementById('cancellation_reason_select');
        if (select) select.focus();
    }
}

function closeCancelOrderModal() {
    const modal = document.getElementById('cancelOrderModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function handleReasonSelectChange(select) {
    const textarea = document.getElementById('cancellation_reason_textarea');
    if (!textarea) return;
    if (select.value && select.value !== 'Other') {
        textarea.value = select.value;
    } else if (select.value === 'Other') {
        textarea.value = '';
        textarea.focus();
    }
}
</script>

<script>
    // ── Real-time status polling: auto-refresh when staff updates the order status ──
    // Keeps the status tracker and cancellation button in sync:
    // once the order moves past "Order Placed"/"Design Review", the cancellation option
    // disappears automatically without requiring a manual page refresh.
    (function () {
        const initialStatus = @json($order->status);
        const statusUrl = @json(route('customer.orders.status', $order->id));
        const POLL_INTERVAL_MS = 30000; // check every 30 seconds

        async function checkOrderStatus() {
            // Don't yank the page while the user is typing a chat message
            // or while the cancellation modal is open — retry on the next tick.
            const chatInput = document.getElementById('chatMessageInput');
            const cancelModal = document.getElementById('cancelOrderModal');
            const modalOpen = cancelModal && cancelModal.style.display === 'flex';
            if (modalOpen || (chatInput && chatInput.value.trim().length > 0)) return;

            try {
                const res = await fetch(statusUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data && data.status && data.status !== initialStatus) {
                    window.location.reload();
                }
            } catch (err) {
                // Ignore transient network errors; retry on the next tick
            }
        }

        setInterval(checkOrderStatus, POLL_INTERVAL_MS);
    })();
</script>
@endpush
