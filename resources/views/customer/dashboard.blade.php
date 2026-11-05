@extends('layouts.app')

@section('title', 'Customer Dashboard - Grafika South')

@section('content')
<style>
    html {
        scroll-behavior: smooth;
    }

    /* Full Page Background for Customer Dashboard */
    body {
        background-color: var(--bg-subtle) !important;
    }

    .customer-dashboard-layout {
        display: flex;
        gap: 1.5rem;
        align-items: flex-start;
        margin-top: 0.5rem;
    }

    .customer-sidebar, aside.customer-sidebar {
        display: none !important;
    }

    @media (max-width: 767.98px) {
        .customer-dashboard-layout {
            display: block;
            gap: 0;
            margin-top: 0;
        }
    }

    /* Floating Back to Top button (bottom-left, like the reference) */
    .gs-back-to-top {
        position: fixed;
        left: 1.25rem;
        bottom: 1.25rem;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--text-main);
        color: var(--text-inverse);
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        cursor: pointer;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.28);
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s ease, background 0.2s ease;
        z-index: 9998;
    }

    .gs-back-to-top.is-visible {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .gs-back-to-top:hover {
        background: var(--text-main);
        transform: translateY(-2px);
    }

    /* Main Dashboard Content Area - Full Screen Width */
    .customer-main-content {
        width: 100% !important;
        max-width: 100% !important;
        flex: 1 1 100% !important;
    }

    /* Greeting Header */
    .dashboard-greeting-title {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 0.25rem;
        word-break: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
    }

    .dashboard-greeting-subtitle {
        color: var(--text-muted);
        font-size: 0.92rem;
        margin-bottom: 1.75rem;
        word-break: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
        line-height: 1.5;
    }

    /* 4 Top KPI Cards Grid */
    .kpi-cards-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .kpi-card {
        background: var(--bg-surface);
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
    }

    .kpi-card-cyan { border: 2px solid var(--accent-color); }
    .kpi-card-magenta { border: 2px solid var(--brand-magenta-fg); }
    .kpi-card-yellow { border: 2px solid var(--warning-border); }
    .kpi-card-slate { border: 2px solid var(--border-color); }

    .kpi-card-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--text-muted);
        margin-bottom: 0.75rem;
    }

    .kpi-card-value {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1;
    }

    .kpi-icon-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }
    .icon-cyan { background: var(--accent-soft); color: var(--text-accent); }
    .icon-magenta { background: var(--brand-magenta-soft); color: var(--text-magenta); }
    .icon-yellow { background: var(--warning-soft); color: var(--text-warning); }
    .icon-slate { background: var(--bg-subtle); color: var(--text-muted); }

    /* Middle Grid (Recent Orders & Side Cards) */
    .dashboard-two-col {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    /* Recent Orders Card */
    .recent-orders-card {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        padding: 1.5rem;
    }

    .orders-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.88rem;
    }

    .orders-table thead tr {
        background: var(--bg-subtle);
    }

    .orders-table th {
        padding: 0.85rem 1rem;
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .orders-table th:first-child { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
    .orders-table th:last-child { border-top-right-radius: 8px; border-bottom-right-radius: 8px; }

    .orders-table td {
        padding: 1.1rem 1rem;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .orders-table tbody tr.clickable-row {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .orders-table tbody tr.clickable-row:hover {
        background-color: var(--bg-page) !important;
    }

    .order-id-link {
        color: var(--text-main);
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: color 0.15s ease;
    }

    .orders-table tbody tr.clickable-row:hover .order-id-link {
        color: var(--text-accent);
        text-decoration: underline;
    }

    /* Polished Status Badges */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.8rem;
        border-radius: 9999px;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
        text-transform: capitalize;
        letter-spacing: 0.2px;
        line-height: 1;
    }
    .status-in-production { background: var(--accent-soft); color: var(--text-accent); border: 1.5px solid var(--info-border); }
    .status-ready { background: var(--warning-soft); color: var(--text-warning); border: 1.5px solid var(--warning-border); }
    .status-completed { background: var(--success-soft); color: var(--text-success); border: 1.5px solid var(--success-border); }
    .status-cancelled { background: var(--danger-soft); color: var(--text-danger); border: 1.5px solid var(--danger-border); }
    .status-pending { background: var(--bg-subtle); color: var(--text-muted); border: 1.5px solid var(--border-color); }

    /* Action Buttons Matching Picture */
    .btn-action-details {
        background: var(--bg-surface);
        border: 1.5px solid var(--accent-color);
        color: var(--text-accent);
        padding: 0.35rem 0.85rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s;
    }
    .btn-action-details:hover { background: var(--accent-color); color: var(--text-inverse); }

    .btn-action-track {
        background: var(--bg-surface);
        border: 1.5px solid var(--border-strong);
        color: var(--text-muted);
        padding: 0.35rem 0.85rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s;
    }
    .btn-action-track:hover { border-color: var(--accent-color); color: var(--text-accent); }

    /* Mobile Order Cards (matches the tickets mobile-card layout) */
    .order-mobile-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .order-mobile-card:active {
        border-color: var(--accent-color);
        box-shadow: 0 2px 8px rgba(0, 153, 255, 0.12);
    }

    /* Right Cards */
    .quick-actions-card {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1.5px solid var(--accent-color);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .btn-quick-start {
        background: var(--accent-color);
        color: var(--text-inverse);
        width: 100%;
        padding: 0.8rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.95rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        transition: background 0.2s;
    }
    .btn-quick-start:hover { background: var(--accent-color); }

    .btn-quick-outline {
        background: var(--bg-surface);
        border: 1.5px solid var(--accent-color);
        color: var(--text-accent);
        width: 100%;
        padding: 0.75rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s;
    }
    .btn-quick-outline:hover { background: var(--accent-soft); }

    .btn-quick-gray {
        background: var(--bg-surface);
        border: 1.5px solid var(--border-color);
        color: var(--text-muted);
        width: 100%;
        padding: 0.75rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }
    .btn-quick-gray:hover { border-color: var(--accent-color); color: var(--text-accent); }

    .notifications-side-card {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .account-panel-wrapper {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 1.75rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        margin-top: 1.5rem;
    }

    .notifications-profile-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 1024px) {
        .dashboard-two-col {
            grid-template-columns: 1fr !important;
        }
        .kpi-cards-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 992px) {
        .notifications-profile-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dashboard-greeting-title {
            font-size: 1.45rem !important;
            line-height: 1.25 !important;
            margin-bottom: 0.35rem !important;
        }
        .dashboard-greeting-subtitle {
            font-size: 0.85rem !important;
            margin-bottom: 1.25rem !important;
        }
        .kpi-cards-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 0.75rem !important;
            margin-bottom: 1.5rem !important;
        }
        .kpi-card {
            padding: 0.85rem 0.85rem !important;
            border-radius: 12px !important;
        }
        .kpi-card-title {
            font-size: 0.78rem !important;
            margin-bottom: 0.35rem !important;
            white-space: nowrap !important;
        }
        .kpi-card-value {
            font-size: 1.6rem !important;
        }
        .kpi-icon-circle {
            width: 28px !important;
            height: 28px !important;
            font-size: 0.8rem !important;
        }
        .recent-orders-card {
            padding: 1rem !important;
            border-radius: 12px !important;
        }
    }

    /* Clean Modern Pagination Controls */
    .orders-pagination-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1.25rem;
        border-top: 1.5px solid var(--border-color);
    }

    .pagination-info {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .pagination-info .fw-bold {
        font-weight: 700;
        color: var(--text-main);
    }

    .pagination-buttons {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .pagination-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.95rem;
        border-radius: 8px;
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--text-muted);
        background: var(--bg-surface);
        border: 1.5px solid var(--border-color);
        text-decoration: none;
        transition: all 0.2s ease;
        line-height: 1;
    }

    .pagination-btn:hover:not(.disabled) {
        border-color: var(--accent-color);
        color: var(--text-accent);
        background: var(--accent-soft);
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 153, 255, 0.15);
    }

    .pagination-btn.disabled {
        background: var(--bg-page);
        color: var(--text-disabled);
        border-color: var(--border-color);
        cursor: not-allowed;
        opacity: 0.7;
    }

    .pagination-numbers {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .pagination-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 0.5rem;
        border-radius: 8px;
        font-size: 0.86rem;
        font-weight: 700;
        color: var(--text-muted);
        background: var(--bg-surface);
        border: 1.5px solid var(--border-color);
        text-decoration: none;
        transition: all 0.2s ease;
        line-height: 1;
    }

    .pagination-num:hover:not(.active) {
        border-color: var(--accent-color);
        color: var(--text-accent);
        background: var(--accent-soft);
    }

    .pagination-num.active {
        background: var(--accent-color);
        color: var(--text-inverse);
        border-color: var(--accent-color);
        box-shadow: 0 2px 8px rgba(0, 153, 255, 0.28);
    }

    /* Prevent any unstyled giant pagination SVGs */
    .orders-pagination-container svg,
    .pagination svg,
    nav[role="navigation"] svg {
        width: 1.15rem !important;
        height: 1.15rem !important;
        max-width: 1.15rem !important;
        max-height: 1.15rem !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }
</style>

<div class="customer-dashboard-layout flex flex-col md:flex-row gap-6 items-start" id="dashboard-top">
    <!-- LEFT SIDEBAR NAVIGATION -->
    <x-customer-sidebar class="hidden md:block w-64 shrink-0 sticky top-20 z-30" />

    <!-- MAIN DASHBOARD CONTENT -->
    <main class="customer-main-content flex-1 w-full min-w-0">
        <!-- GREETING HEADER -->
        <div class="dashboard-greeting-title break-words max-w-full">
            Welcome back, {{ $user->first_name ?: trim(explode(' ', trim($user->name))[0]) }}
        </div>
        <div class="dashboard-greeting-subtitle break-words max-w-full text-wrap">
            Here is a summary of your recent print orders and account activity.
        </div>

        @php $isToRateTab = request('tab') === 'to-rate'; @endphp
        @if($isToRateTab)
        <!-- TO RATE TAB — opened only via the sidebar/drawer "To Rate" menu item -->
        <div class="recent-orders-card" id="to-rate" style="border:1.5px solid var(--warning-border); scroll-margin-top:6rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem;">
                <h3 style="font-size:1.15rem; font-weight:800; color:var(--text-main); font-family:'Outfit',sans-serif; margin:0; display:flex; align-items:center; gap:0.5rem;">
                    <i class="fa-solid fa-star" style="color:var(--text-warning);"></i> To Rate
                </h3>
                <div style="display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap;">
                    <span style="font-size:0.72rem; font-weight:800; background:{{ $toRateItems->count() > 0 ? 'var(--warning-soft)' : 'var(--success-soft)' }}; color:{{ $toRateItems->count() > 0 ? 'var(--text-warning)' : 'var(--text-success)' }}; border:1px solid {{ $toRateItems->count() > 0 ? 'var(--warning-border)' : 'var(--success-border)' }}; padding:0.2rem 0.6rem; border-radius:9999px; white-space:nowrap;">
                        {{ $toRateItems->count() > 0 ? $toRateItems->count() . ' pending review' . ($toRateItems->count() > 1 ? 's' : '') : 'All caught up!' }}
                    </span>
                    <a href="{{ route('customer.dashboard') }}" style="font-size:0.78rem; font-weight:700; color:var(--text-accent); text-decoration:none; white-space:nowrap;">
                        <i class="fa-solid fa-arrow-left"></i> Back to Orders
                    </a>
                </div>
            </div>

            @forelse($toRateItems as $entry)
                @php
                    $rateOrder = $entry['order'];
                    $rateItem = $entry['item'];
                    $rateImg = $rateItem->product->image_path ? asset('storage/' . $rateItem->product->image_path) : null;
                @endphp
                <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem; flex-wrap:wrap; padding:0.65rem 0; border-bottom:1px dashed var(--border-color);">
                    <div style="display:flex; align-items:center; gap:0.75rem; min-width:0; flex:1;">
                        <div style="width:44px; height:44px; border-radius:10px; overflow:hidden; background:var(--accent-soft); border:1px solid var(--border-color); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            @if($rateImg)
                                <img src="{{ $rateImg }}" alt="{{ $rateItem->product->name }}" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <i class="fa-solid fa-print" style="color:var(--text-accent);"></i>
                            @endif
                        </div>
                        <div style="min-width:0;">
                            <div style="font-weight:700; color:var(--text-main); font-size:0.9rem; overflow-wrap:break-word;">{{ $rateItem->product->name }}</div>
                            <div style="font-size:0.75rem; color:var(--text-muted);">
                                Order <a href="{{ route('customer.orders.show', $rateOrder->id) }}" style="color:var(--text-accent); font-weight:700; text-decoration:none;">#{{ $rateOrder->order_number }}</a> · Completed {{ $rateOrder->updated_at->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="openReviewModal({{ $rateItem->id }}, @js($rateItem->product->name), @js($rateImg), @js($rateOrder->order_number))"
                        style="background:var(--warning-soft); color:var(--text-warning); border:1.5px solid var(--warning-border); padding:0.4rem 0.9rem; border-radius:9999px; font-size:0.8rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:0.4rem; transition:all 0.2s; white-space:nowrap;" onmouseover="this.style.background='#FFEDD5'" onmouseout="this.style.background='#FFF7ED'">
                        <i class="fa-solid fa-star"></i> Rate Now
                    </button>
                </div>
            @empty
                <div style="text-align:center; padding:1.25rem 1rem; background:var(--success-soft); border:1px dashed var(--success-border); border-radius:12px;">
                    <i class="fa-solid fa-circle-check" style="color:var(--text-success); font-size:1.5rem; margin-bottom:0.4rem;"></i>
                    <div style="font-weight:700; color:var(--text-success); font-size:0.9rem;">You have no products waiting for a review.</div>
                    <div style="font-size:0.78rem; color:var(--text-success); margin-top:0.2rem;">Rate your completed orders as soon as they're done to help others choose!</div>
                </div>
            @endforelse
        </div>
        @else
        <!-- 4 TOP KPI CARDS GRID WITH CMYK BORDERS -->
        <div class="kpi-cards-grid grid grid-cols-2 gap-3 md:grid-cols-4">
            <!-- Active Orders (Cyan Border) -->
            <a href="{{ route('customer.dashboard', ['status' => 'active']) }}#orders-section" class="kpi-card kpi-card-cyan" title="View active orders">
                <div>
                    <div class="kpi-card-title">Active Orders</div>
                    <div class="kpi-card-value">{{ $activeOrdersCount }}</div>
                </div>
                <div class="kpi-icon-circle icon-cyan">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </div>
            </a>

            <!-- Completed (Magenta Border) -->
            <a href="{{ route('customer.dashboard', ['status' => 'completed']) }}#orders-section" class="kpi-card kpi-card-magenta" title="View completed & cancelled orders">
                <div>
                    <div class="kpi-card-title">Completed</div>
                    <div class="kpi-card-value">{{ $completedOrdersCount }}</div>
                </div>
                <div class="kpi-icon-circle icon-magenta">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </a>

            <!-- Pending (Yellow Border) -->
            <a href="{{ route('customer.dashboard', ['status' => 'pending']) }}#orders-section" class="kpi-card kpi-card-yellow" title="View pending orders">
                <div>
                    <div class="kpi-card-title">Pending</div>
                    <div class="kpi-card-value">{{ $pendingOrdersCount }}</div>
                </div>
                <div class="kpi-icon-circle icon-yellow">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
            </a>

            <!-- Total Orders (Slate Border) -->
            <a href="{{ route('customer.dashboard', ['status' => 'all']) }}#orders-section" class="kpi-card kpi-card-slate" title="View all orders">
                <div>
                    <div class="kpi-card-title">Total Orders</div>
                    <div class="kpi-card-value">{{ $totalOrdersCount }}</div>
                </div>
                <div class="kpi-icon-circle icon-slate">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </a>
        </div>

        <!-- RECENT ORDERS TABLE -->
        <div class="recent-orders-card" id="orders-section">
            <div style="margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); font-family: 'Outfit', sans-serif; margin: 0;">
                    Recent Orders
                </h3>
            </div>

            <!-- SEARCH AND FILTER BAR COMPONENT -->
            <form action="{{ route('customer.dashboard') }}" method="GET" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; margin-bottom: 1.5rem; background: var(--bg-page); padding: 1rem; border-radius: 12px; border: 1px solid var(--border-color);">
                <div style="flex: 1; min-width: 240px; display: flex; align-items: center;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by product name or order ID..." style="flex: 1; min-width: 0; padding: 0.6rem 1rem; border: 1.5px solid var(--border-color); border-right: none; border-radius: 10px 0 0 10px; font-size: 0.88rem; outline: none; background: var(--bg-surface); font-family: inherit; color: var(--text-main);" onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor='#CBD5E1'">
                    <button type="submit" style="background: var(--accent-color); color: var(--text-inverse); border: 1.5px solid var(--accent-color); padding: 0.6rem 1.25rem; border-radius: 0 10px 10px 0; font-weight: 700; font-size: 0.88rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; transition: background 0.2s, border-color 0.2s; white-space: nowrap;" onmouseover="this.style.background='#0080FF';this.style.borderColor='#0080FF'" onmouseout="this.style.background='#0099FF';this.style.borderColor='#0099FF'">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>
                </div>

                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    <select name="status" onchange="this.form.submit()" style="padding: 0.6rem 1rem; border: 1.5px solid var(--border-color); border-radius: 8px; font-size: 0.88rem; color: var(--text-muted); background: var(--bg-surface); font-weight: 600; cursor: pointer; outline: none;">
                        <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Product Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_production" {{ request('status') == 'in_production' ? 'selected' : '' }}>In Production</option>
                        <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready for Pickup</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>

                    @if(request('search') || (request('status') && request('status') !== 'all'))
                        <a href="{{ route('customer.dashboard') }}#orders-section" style="color: var(--text-danger); font-size: 0.85rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <i class="fa-solid fa-xmark"></i> Clear
                        </a>
                    @endif
                </div>
            </form>

            <!-- MOBILE CARD LAYOUT (md:hidden) -->
            <div class="block md:hidden space-y-3">
                @forelse($recentOrders as $order)
                    @php
                        $mFirstItem = $order->items->first();
                        $mProductName = $mFirstItem && $mFirstItem->product ? $mFirstItem->product->name : 'Custom Order';
                        if ($order->items->count() > 1) {
                            $mProductName .= ' (+' . ($order->items->count() - 1) . ' items)';
                        }
                        $mSt = strtolower($order->status);
                        $mFirstProd = $mFirstItem?->product;
                        $mFirstImg = $mFirstProd?->image_path ? asset('storage/' . $mFirstProd->image_path) : null;
                    @endphp
                    <div class="order-mobile-card" onclick="window.location.href='{{ route('customer.orders.show', $order->id) }}'" title="View order #{{ $order->order_number }}">
                        <!-- Top row: Order ID + Status -->
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-sm font-bold text-gray-900 break-all">#{{ $order->order_number }}</span>
                            @if(in_array($mSt, ['printing', 'approved', 'design_review', 'in_production']))
                                <span class="status-pill status-in-production"><i class="fa-solid fa-gears" style="font-size: 0.7rem;"></i> In Production</span>
                            @elseif(in_array($mSt, ['ready', 'shipped', 'ready_for_pickup']))
                                <span class="status-pill status-ready"><i class="fa-solid fa-box-archive" style="font-size: 0.7rem;"></i> Ready</span>
                            @elseif($mSt === 'completed')
                                <span class="status-pill status-completed"><i class="fa-solid fa-circle-check" style="font-size: 0.7rem;"></i> Completed</span>
                            @elseif($mSt === 'cancelled')
                                <span class="status-pill status-cancelled"><i class="fa-solid fa-circle-xmark" style="font-size: 0.7rem;"></i> Cancelled</span>
                            @else
                                <span class="status-pill status-pending"><i class="fa-solid fa-clock" style="font-size: 0.7rem;"></i> {{ ucwords(str_replace('_', ' ', $mSt)) }}</span>
                            @endif
                        </div>

                        <!-- Middle row: product thumbnail + name + amount -->
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-11 h-11 rounded-lg border border-gray-100 shrink-0 overflow-hidden bg-sky-50 flex items-center justify-center">
                                @if($mFirstImg)
                                    <img src="{{ $mFirstImg }}" alt="{{ $mProductName }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-print text-sky-400 text-sm"></i>
                                @endif
                            </div>
                            <div class="flex flex-col min-w-0 flex-1">
                                <span class="font-semibold text-gray-800 text-sm truncate">{{ $mProductName }}</span>
                                @if($order->is_custom_order)
                                    <span class="text-[10px] text-orange-500 font-bold">Custom Order</span>
                                @endif
                            </div>
                            <div class="font-extrabold text-gray-900 text-sm shrink-0">&#8369;{{ number_format($order->total_price ?: $order->total_amount, 2) }}</div>
                        </div>

                        <!-- Bottom row: date + Details action -->
                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-100 text-xs">
                            <div class="text-slate-500 font-medium">
                                <i class="fa-regular fa-clock text-slate-400 mr-1"></i>{{ $order->created_at->format('M d, Y') }}
                            </div>
                            <a href="{{ route('customer.orders.show', $order->id) }}" class="btn-action-details" onclick="event.stopPropagation();">
                                Details <i class="fa-solid fa-arrow-right" style="font-size: 0.7rem; margin-left: 0.35rem;"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 14px;">
                        <i class="fa-solid fa-box-open" style="font-size: 2.5rem; color: var(--text-disabled); margin-bottom: 0.5rem;"></i>
                        <div style="font-weight: 600;">You have not placed any print shop orders yet.</div>
                    </div>
                @endforelse
            </div>

            <!-- DESKTOP TABLE LAYOUT (hidden md:block) -->
            <div class="hidden md:block" style="overflow-x: auto; width: 100%; -webkit-overflow-scrolling: touch;">
                <table class="orders-table" style="min-width: 580px;">
                <thead>
                    <tr>
                        <th>ORDER ID</th>
                        <th>PRODUCT</th>
                        <th>DATE</th>
                        <th>AMOUNT</th>
                        <th>STATUS</th>
                        <th style="text-align: center;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        @php
                            $firstItem = $order->items->first();
                            $productName = $firstItem && $firstItem->product ? $firstItem->product->name : 'Custom Order';
                            if ($order->items->count() > 1) {
                                $productName .= ' (+' . ($order->items->count() - 1) . ' items)';
                            }
                            $st = strtolower($order->status);
                        @endphp
                        <tr class="clickable-row" onclick="window.location.href='{{ route('customer.orders.show', $order->id) }}'" title="Click to view order details for #{{ $order->order_number }}">
                            <td>
                                <a href="{{ route('customer.orders.show', $order->id) }}" class="order-id-link">
                                    #{{ $order->order_number }}
                                </a>
                                @if($order->is_parent)
                                    <div>
                                        <span class="role-badge" style="background: var(--accent-soft); color: var(--text-accent); font-size: 0.68rem; font-weight: 800; display: inline-block; margin-top: 2px;">
                                            <i class="fa-solid fa-code-fork"></i> Split Order
                                        </span>
                                    </div>
                                @elseif(!empty($order->parent_id))
                                    <div>
                                        <span class="role-badge" style="background: {{ $order->is_custom_order ? 'var(--brand-magenta-soft)' : 'var(--accent-soft)' }}; color: {{ $order->is_custom_order ? 'var(--text-magenta)' : 'var(--text-accent)' }}; font-size: 0.68rem; font-weight: 800; display: inline-block; margin-top: 2px;">
                                            <i class="fa-solid {{ $order->is_custom_order ? 'fa-palette' : 'fa-box' }}"></i>
                                            {{ $order->is_custom_order ? 'Sub-Order (Custom)' : 'Sub-Order (Regular)' }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td style="font-weight: 600; color: var(--text-muted);">
                                @if($order->is_parent)
                                    <span style="color: var(--text-accent);">Mixed Cart (Ready-Made &amp; Custom Sub-Orders)</span>
                                @else
                                    @php
                                        $firstProd = $firstItem?->product;
                                        $firstImg = $firstProd?->image_path ? asset('storage/' . $firstProd->image_path) : null;
                                    @endphp
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg border border-gray-100 shrink-0 overflow-hidden bg-sky-50 flex items-center justify-center">
                                            @if($firstImg)
                                                <img src="{{ $firstImg }}" alt="{{ $firstProd?->name ?? $productName }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="fa-solid fa-print text-sky-400 text-sm"></i>
                                            @endif
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-semibold text-gray-800 text-xs sm:text-sm break-words">{{ $productName }}</span>
                                            @if($order->is_custom_order)
                                                <span class="text-[10px] text-orange-500 font-bold">Custom Order</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.85rem; white-space: nowrap;">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>
                            <td style="font-weight: 800; color: var(--text-main);">
                                ₱{{ number_format($order->total_price ?: $order->total_amount, 2) }}
                            </td>
                            <td>
                                @if(in_array($st, ['printing', 'approved', 'design_review', 'in_production']))
                                    <span class="status-pill status-in-production">
                                        <i class="fa-solid fa-gears" style="font-size: 0.72rem;"></i> In Production
                                    </span>
                                @elseif(in_array($st, ['ready', 'shipped', 'ready_for_pickup']))
                                    <span class="status-pill status-ready">
                                        <i class="fa-solid fa-box-archive" style="font-size: 0.72rem;"></i> Ready for Pickup
                                    </span>
                                @elseif($st === 'completed')
                                    <span class="status-pill status-completed">
                                        <i class="fa-solid fa-circle-check" style="font-size: 0.72rem;"></i> Completed
                                    </span>
                                @elseif($st === 'cancelled')
                                    <span class="status-pill status-cancelled">
                                        <i class="fa-solid fa-circle-xmark" style="font-size: 0.72rem;"></i> Cancelled
                                    </span>
                                @else
                                    <span class="status-pill status-pending">
                                        <i class="fa-solid fa-clock" style="font-size: 0.72rem;"></i> {{ ucwords(str_replace('_', ' ', $st)) }}
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;" onclick="event.stopPropagation();">
                                @php
                                    $unreviewedItems = $st === 'completed'
                                        ? $order->items->filter(fn($i) => $i->product_id && !$order->reviews->contains('product_id', $i->product_id))
                                        : collect();
                                @endphp
                                <div style="display:flex; align-items:center; justify-content:center; gap:0.5rem; flex-wrap:wrap;">
                                    <a href="{{ route('customer.orders.show', $order->id) }}" class="btn-action-details">
                                        Details
                                    </a>
                                    @if($unreviewedItems->isNotEmpty())
                                        @php
                                            $quickRateItem = $unreviewedItems->first();
                                            $quickRateImg = $quickRateItem->product?->image_path ? asset('storage/' . $quickRateItem->product->image_path) : null;
                                        @endphp
                                        <button type="button" onclick="openReviewModal({{ $quickRateItem->id }}, @js($quickRateItem->product->name), @js($quickRateImg), @js($order->order_number))"
                                            class="px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors flex items-center gap-1" style="cursor:pointer;" title="Rate {{ $quickRateItem->product->name }}">
                                            <i class="fa-solid fa-star text-amber-500"></i> Rate
                                        </button>
                                    @elseif($st === 'completed' && $order->items->where('product_id', '!=', null)->isNotEmpty())
                                        <span class="text-[11px] font-bold text-emerald-600 whitespace-nowrap"><i class="fa-solid fa-circle-check"></i> Reviewed</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                <i class="fa-solid fa-box-open" style="font-size: 2.5rem; color: var(--text-disabled); margin-bottom: 0.5rem;"></i>
                                <div style="font-weight: 600;">You have not placed any print shop orders yet.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            {{-- Clean Custom Pagination Controls (5 orders per page) --}}
            @if($recentOrders->hasPages())
                <div class="orders-pagination-container">
                    <div class="pagination-info">
                        Showing <span class="fw-bold">{{ $recentOrders->firstItem() ?? 0 }}</span> to <span class="fw-bold">{{ $recentOrders->lastItem() ?? 0 }}</span> of <span class="fw-bold" style="color: var(--text-accent);">{{ $recentOrders->total() }}</span> results
                    </div>

                    <div class="pagination-buttons">
                        {{-- Previous Page Link --}}
                        @if ($recentOrders->onFirstPage())
                            <span class="pagination-btn disabled" aria-disabled="true">
                                <i class="fa-solid fa-chevron-left" style="font-size: 0.75rem;"></i> Previous
                            </span>
                        @else
                            <a href="{{ $recentOrders->previousPageUrl() }}" class="pagination-btn" rel="prev">
                                <i class="fa-solid fa-chevron-left" style="font-size: 0.75rem;"></i> Previous
                            </a>
                        @endif

                        {{-- Pagination Numbers --}}
                        <div class="pagination-numbers">
                            @foreach ($recentOrders->getUrlRange(max(1, $recentOrders->currentPage() - 2), min($recentOrders->lastPage(), $recentOrders->currentPage() + 2)) as $page => $url)
                                @if ($page == $recentOrders->currentPage())
                                    <span class="pagination-num active" aria-current="page">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="pagination-num">{{ $page }}</a>
                                @endif
                            @endforeach
                        </div>

                        {{-- Next Page Link --}}
                        @if ($recentOrders->hasMorePages())
                            <a href="{{ $recentOrders->nextPageUrl() }}" class="pagination-btn" rel="next">
                                Next <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
                            </a>
                        @else
                            <span class="pagination-btn disabled" aria-disabled="true">
                                Next <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        @endif

        {{-- Review flash feedback + shared Rate & Review modal (available on both tabs) --}}
        @include('customer.partials.review-modal')
    </main>
</div>

<button type="button" class="gs-back-to-top" id="gsBackToTop" aria-label="Back to top" title="Back to top">
    <i class="fa-solid fa-arrow-up"></i>
</button>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const menuLinks = document.querySelectorAll('.customer-sidebar .sidebar-menu-item');

        menuLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href && href.startsWith('#')) {
                    e.preventDefault();
                    const targetEl = document.querySelector(href);
                    if (targetEl) {
                        const yOffset = -90;
                        const y = targetEl.getBoundingClientRect().top + window.pageYOffset + yOffset;
                        window.scrollTo({ top: y, behavior: 'smooth' });

                        menuLinks.forEach(item => item.classList.remove('active'));
                        this.classList.add('active');
                    }
                }
            });
        });
    });

    /* Back to Top: fade in after scrolling, smooth scroll to top on click */
    const backToTop = document.getElementById('gsBackToTop');
    if (backToTop) {
        const onScroll = function () {
            backToTop.classList.toggle('is-visible', window.pageYOffset > 300);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
</script>
@endpush
@endsection
