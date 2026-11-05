@extends('layouts.app')

@section('title', 'Support Tickets - Grafika South')

@section('content')
<style>
    body {
        background-color: var(--bg-subtle) !important;
    }

    .customer-dashboard-layout {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
        margin-top: -0.5rem;
    }

    /* Main Panel Content Area - Full Width Desktop */
    .customer-main-content {
        width: 100% !important;
        max-width: 100% !important;
        flex: 1 1 100% !important;
        min-width: 0;
    }

    .panel-header-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 0.25rem;
    }

    .panel-header-subtitle {
        color: var(--text-muted);
        font-size: 0.92rem;
    }

    .btn-create-ticket {
        background: var(--accent-color);
        color: var(--text-inverse);
        padding: 0.65rem 1.35rem;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 10px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        box-shadow: none;
        border: none;
    }

    .btn-create-ticket:hover {
        background: var(--accent-color);
        color: var(--text-inverse);
        box-shadow: none;
    }

    /* Filter Tabs Bar (Horizontal Scrollable) */
    .ticket-filter-tabs {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--bg-surface);
        padding: 0.4rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02);
        margin-bottom: 1.25rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        white-space: nowrap;
    }

    .scrollbar-none::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-none {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .filter-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 1rem;
        border-radius: 8px;
        font-size: 0.86rem;
        font-weight: 700;
        color: var(--text-muted);
        text-decoration: none;
        transition: all 0.2s ease;
        flex-shrink: 0;
        white-space: nowrap;
    }

    /* Mobile Ticket Stacked Cards */
    .ticket-mobile-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        transition: all 0.2s ease;
    }

    .ticket-mobile-card:hover {
        border-color: var(--info-border);
        box-shadow: 0 4px 12px rgba(0, 153, 255, 0.08);
    }

    .filter-tab-btn:hover {
        color: var(--text-main);
        background: var(--bg-page);
    }

    .filter-tab-btn.active {
        background: var(--accent-color);
        color: var(--text-inverse);
        box-shadow: 0 2px 6px rgba(0, 153, 255, 0.25);
    }

    .filter-tab-btn.active .tab-count-badge {
        background: rgba(255, 255, 255, 0.25);
        color: var(--text-inverse);
    }

    .tab-count-badge {
        background: var(--bg-subtle);
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.15rem 0.55rem;
        border-radius: 9999px;
        line-height: 1.2;
    }

    .tab-count-pending,
    .tab-count-open {
        background: var(--warning-soft);
        color: var(--text-warning);
    }

    /* Tickets Table Card */
    .tickets-card {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .tickets-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.9rem;
    }

    .tickets-table thead tr {
        background: var(--bg-page);
        border-bottom: 1.5px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 700;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .tickets-table th {
        padding: 0.85rem 0.7rem;
        font-size: 0.74rem;
    }

    /* Pull the last column's content off the card's rounded edge so the button isn't clipped */
    .tickets-table th:last-child,
    .ticket-row td:last-child {
        padding-right: 1.4rem;
    }

    .ticket-row {
        border-bottom: 1px solid var(--border-color);
        transition: background-color 0.15s ease;
        cursor: pointer;
    }

    .ticket-row:hover {
        background-color: var(--bg-page);
    }

    .ticket-row:last-child {
        border-bottom: none;
    }

    .ticket-row td {
        padding: 0.9rem 0.7rem;
        vertical-align: middle;
        overflow: hidden;
    }

    .ticket-number-badge {
        font-weight: 800;
        color: var(--text-main);
        font-size: 0.78rem;
        letter-spacing: -0.03em;
        white-space: nowrap;
    }

    .ticket-subject-title {
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.35rem;
        line-height: 1.35;
        font-size: 0.9rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ticket-category-tag {
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--text-muted);
        background: var(--bg-subtle);
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border: 1px solid var(--border-color);
    }

    .order-link-pill {
        color: var(--text-accent);
        background: var(--accent-soft);
        font-weight: 700;
        font-size: 0.82rem;
        text-decoration: none;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border: 1px solid var(--info-border);
        transition: all 0.2s ease;
    }

    .order-link-pill:hover {
        background: var(--accent-color);
        color: var(--text-inverse);
        border-color: var(--accent-color);
    }

    /* Enhanced Status Badges */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.32rem 0.8rem;
        border-radius: 9999px;
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        line-height: 1;
        white-space: nowrap;
    }

    .badge-pending,
    .badge-open {
        background: var(--warning-soft);
        color: var(--text-warning);
        border: 1.5px solid var(--warning-border);
    }

    .badge-in-progress {
        background: var(--accent-soft);
        color: var(--text-accent);
        border: 1.5px solid var(--info-border);
    }

    .badge-resolved {
        background: var(--success-soft);
        color: var(--text-success);
        border: 1.5px solid var(--success-border);
    }

    .badge-closed {
        background: var(--bg-subtle);
        color: var(--text-muted);
        border: 1.5px solid var(--border-color);
    }

    .btn-view-thread {
        background: var(--bg-surface);
        border: 1.5px solid var(--border-color);
        color: var(--text-muted);
        padding: 0.4rem 0.7rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
    }

    .ticket-row:hover .btn-view-thread,
    .btn-view-thread:hover {
        background: var(--accent-color);
        border-color: var(--accent-color);
        color: var(--text-inverse);
        box-shadow: 0 2px 8px rgba(0, 153, 255, 0.25);
    }

    /* Custom Pagination Styling */
    .tickets-pagination-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-top: 1.5px solid var(--border-color);
        background: var(--bg-surface);
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
</style>

<div class="customer-dashboard-layout flex flex-col md:flex-row gap-6 items-start">
    {{-- Customer Sidebar --}}
    <x-customer-sidebar />

    {{-- Main Content --}}
    <main class="customer-main-content flex-1 w-full min-w-0">
        <!-- HEADER WITH ACTIONS (Compact Hero Spacing on Mobile) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 py-3 sm:py-6 mb-2 sm:mb-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-main)] tracking-tight mb-1">
                    Customer Support &amp; Inquiries
                </h1>
                <p class="text-xs sm:text-sm text-[var(--text-muted)]">
                    Submit complaints, order questions, or technical inquiries directly to our team.
                </p>
            </div>
            <a href="{{ route('customer.tickets.create') }}" class="btn-create-ticket w-full sm:w-auto justify-center">
                <i class="fa-solid fa-plus"></i> New Support Ticket
            </a>
        </div>

        <!-- STATUS FILTER TABS (Horizontal Scrollable Filter Pills) -->
        <div class="ticket-filter-tabs flex items-center gap-2 overflow-x-auto px-1 py-1 scrollbar-none w-full">
            <a href="{{ route('customer.tickets.index') }}" class="filter-tab-btn shrink-0 whitespace-nowrap {{ !request('status') ? 'active' : '' }}">
                <i class="fa-solid fa-layer-group"></i> All Tickets
                <span class="tab-count-badge">{{ $counts['all'] }}</span>
            </a>
            <a href="{{ route('customer.tickets.index', ['status' => 'pending']) }}" class="filter-tab-btn shrink-0 whitespace-nowrap {{ in_array(request('status'), ['pending', 'open']) ? 'active' : '' }}">
                <i class="fa-solid fa-clock"></i> Pending
                <span class="tab-count-badge {{ ($counts['pending'] ?? 0) > 0 ? 'tab-count-pending' : '' }}">{{ $counts['pending'] ?? 0 }}</span>
            </a>
            <a href="{{ route('customer.tickets.index', ['status' => 'in_progress']) }}" class="filter-tab-btn shrink-0 whitespace-nowrap {{ request('status') === 'in_progress' ? 'active' : '' }}">
                <i class="fa-solid fa-gears"></i> In Progress
                <span class="tab-count-badge">{{ $counts['in_progress'] }}</span>
            </a>
            <a href="{{ route('customer.tickets.index', ['status' => 'resolved']) }}" class="filter-tab-btn shrink-0 whitespace-nowrap {{ request('status') === 'resolved' ? 'active' : '' }}">
                <i class="fa-solid fa-circle-check"></i> Resolved
                <span class="tab-count-badge">{{ $counts['resolved'] }}</span>
            </a>
            <a href="{{ route('customer.tickets.index', ['status' => 'closed']) }}" class="filter-tab-btn shrink-0 whitespace-nowrap {{ request('status') === 'closed' ? 'active' : '' }}">
                <i class="fa-solid fa-lock"></i> Closed
                <span class="tab-count-badge">{{ $counts['closed'] }}</span>
            </a>
        </div>

        <!-- TICKETS CARD -->
        <div class="tickets-card">
            @if($tickets->isEmpty())
                <div style="text-align: center; padding: 4rem 1.5rem;">
                    <div style="width: 72px; height: 72px; background: var(--accent-soft); color: var(--text-accent); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 1.25rem;">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    @if(request('status'))
                        @php
                            $filterLabel = in_array(request('status'), ['pending', 'open']) ? 'Pending' : ucfirst(str_replace('_', ' ', request('status')));
                        @endphp
                        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.35rem;">No {{ $filterLabel }} Tickets Found</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; max-width: 420px; margin: 0 auto 1.5rem;">
                            There are currently no tickets matching this status filter.
                        </p>
                        <a href="{{ route('customer.tickets.index') }}" class="btn-create-ticket" style="background: var(--bg-surface); color: var(--text-accent); border: 1.5px solid var(--accent-color); box-shadow: none;">
                            <i class="fa-solid fa-layer-group"></i> View All Tickets
                        </a>
                    @else
                        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.35rem;">No Support Tickets Yet</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; max-width: 420px; margin: 0 auto 1.5rem;">
                            You have not submitted any customer support tickets yet. Need help with an order or product inquiry?
                        </p>
                        <a href="{{ route('customer.tickets.create') }}" class="btn-create-ticket">
                            <i class="fa-solid fa-plus"></i> Open a Ticket
                        </a>
                    @endif
                </div>
            @else
                <!-- 1. MOBILE CARD LAYOUT (md:hidden) -->
                <div class="block md:hidden p-3 space-y-3">
                    @foreach($tickets as $ticket)
                        <div class="ticket-mobile-card cursor-pointer" onclick="window.location.href='{{ route('customer.tickets.show', $ticket->id) }}'">
                            <!-- Top row: Ticket ID and Status Badge -->
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="text-xs sm:text-sm font-bold text-[var(--text-main)] break-all">
                                    {{ str_starts_with($ticket->ticket_number, '#') ? '' : '#' }}{{ $ticket->ticket_number }}
                                </span>
                                <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                                    <span class="badge-status {{ $ticket->status_badge_class }}">
                                        @if(in_array($ticket->status, ['pending', 'open']))
                                            <i class="fa-solid fa-clock"></i> Pending
                                        @elseif($ticket->status === 'in_progress')
                                            <i class="fa-solid fa-gears"></i> In Progress
                                        @elseif($ticket->status === 'resolved')
                                            <i class="fa-solid fa-circle-check"></i> Resolved
                                        @else
                                            <i class="fa-solid fa-lock"></i> Closed
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Middle row: Subject & Category tag -->
                            <div class="mb-3">
                                <div class="font-bold text-[var(--text-main)] text-sm leading-snug mb-1.5">
                                    {{ $ticket->subject }}
                                </div>
                                <div class="flex items-center flex-wrap gap-1.5">
                                    <span class="ticket-category-tag text-[0.72rem]">
                                        <i class="fa-solid fa-tag text-[0.65rem] text-[var(--text-subtle)]"></i>
                                        {{ $ticket->category }}
                                    </span>
                                    @if($ticket->order)
                                        <a href="{{ route('customer.orders.show', $ticket->order_id) }}" class="order-link-pill text-[0.72rem] py-0.5 px-2" onclick="event.stopPropagation();">
                                            <i class="fa-solid fa-box text-[0.65rem]"></i> #{{ $ticket->order->order_number }}
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Bottom row: Date and 'View Ticket' action button -->
                            <div class="flex items-center justify-between pt-2.5 border-t border-[var(--border-color)] text-xs">
                                <div class="text-[var(--text-muted)] font-medium">
                                    <i class="fa-regular fa-clock text-[var(--text-subtle)] mr-1"></i>
                                    {{ $ticket->last_replied_at ? $ticket->last_replied_at->diffForHumans() : $ticket->created_at->diffForHumans() }}
                                </div>
                                <a href="{{ route('customer.tickets.show', $ticket->id) }}" class="btn-view-thread text-xs py-1.5 px-3" onclick="event.stopPropagation();">
                                    <span>View Ticket</span>
                                    <i class="fa-solid fa-arrow-right text-[0.65rem]"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- 2. DESKTOP TABLE LAYOUT (hidden md:table) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="tickets-table hidden md:table w-full">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Ticket #</th>
                                <th style="width: 27%;">Subject &amp; Category</th>
                                <th style="width: 10%;">Related Order</th>
                                <th style="width: 12%;">Status</th>
                                <th style="width: 15%;">Last Activity</th>
                                <th style="width: 16%; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                <tr class="ticket-row" data-href="{{ route('customer.tickets.show', $ticket->id) }}">
                                    <td>
                                        <div class="ticket-number-badge" title="#{{ $ticket->ticket_number }}">
                                            {{ str_starts_with($ticket->ticket_number, '#') ? '' : '#' }}{{ $ticket->ticket_number }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ticket-subject-title" title="{{ $ticket->subject }}">
                                            {{ $ticket->subject }}
                                        </div>
                                        <span class="ticket-category-tag">
                                            <i class="fa-solid fa-tag" style="font-size: 0.68rem; color: var(--text-subtle);"></i>
                                            {{ $ticket->category }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ticket->order)
                                            <a href="{{ route('customer.orders.show', $ticket->order_id) }}" class="order-link-pill" onclick="event.stopPropagation();">
                                                <i class="fa-solid fa-box"></i> #{{ $ticket->order->order_number }}
                                            </a>
                                        @else
                                            <span style="color: var(--text-disabled); font-size: 0.95rem;" title="No linked order">&mdash;</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.35rem;">
                                            <span class="badge-status {{ $ticket->status_badge_class }}">
                                                @if(in_array($ticket->status, ['pending', 'open']))
                                                    <i class="fa-solid fa-clock"></i> Pending
                                                @elseif($ticket->status === 'in_progress')
                                                    <i class="fa-solid fa-gears"></i> In Progress
                                                @elseif($ticket->status === 'resolved')
                                                    <i class="fa-solid fa-circle-check"></i> Resolved
                                                @else
                                                    <i class="fa-solid fa-lock"></i> Closed
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td style="color: var(--text-muted); font-size: 0.84rem;">
                                        <div style="font-weight: 600; color: var(--text-muted);">
                                            {{ $ticket->last_replied_at ? $ticket->last_replied_at->diffForHumans() : $ticket->created_at->diffForHumans() }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-subtle);">
                                            {{ ($ticket->last_replied_at ?: $ticket->created_at)->timezone('Asia/Manila')->format('M d, Y') }}
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('customer.tickets.show', $ticket->id) }}" class="btn-view-thread" onclick="event.stopPropagation();">
                                            <span>View</span>
                                            <i class="fa-solid fa-arrow-right" style="font-size: 0.75rem;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Clean Custom Pagination Controls --}}
                @if($tickets->hasPages())
                    <div class="tickets-pagination-container flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 sm:p-5 border-t border-[var(--border-color)] bg-[var(--bg-surface)]">
                        <div class="pagination-info text-xs sm:text-sm text-[var(--text-muted)] text-center sm:text-left">
                            Showing <span class="fw-bold">{{ $tickets->firstItem() ?? 0 }}</span> to <span class="fw-bold">{{ $tickets->lastItem() ?? 0 }}</span> of <span class="fw-bold">{{ $tickets->total() }}</span> tickets
                        </div>

                        <div class="pagination-buttons">
                            {{-- Previous Page Link --}}
                            @if ($tickets->onFirstPage())
                                <span class="pagination-btn disabled">
                                    <i class="fa-solid fa-chevron-left" style="font-size: 0.75rem;"></i> Prev
                                </span>
                            @else
                                <a href="{{ $tickets->previousPageUrl() }}" class="pagination-btn">
                                    <i class="fa-solid fa-chevron-left" style="font-size: 0.75rem;"></i> Prev
                                </a>
                            @endif

                            {{-- Pagination Numbers --}}
                            <div class="pagination-numbers">
                                @foreach ($tickets->getUrlRange(max(1, $tickets->currentPage() - 2), min($tickets->lastPage(), $tickets->currentPage() + 2)) as $page => $url)
                                    @if ($page == $tickets->currentPage())
                                        <span class="pagination-num active">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="pagination-num">{{ $page }}</a>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Next Page Link --}}
                            @if ($tickets->hasMorePages())
                                <a href="{{ $tickets->nextPageUrl() }}" class="pagination-btn">
                                    Next <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
                                </a>
                            @else
                                <span class="pagination-btn disabled">
                                    Next <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </main>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Clickable table rows redirecting straight to ticket details
    document.querySelectorAll('.ticket-row').forEach(row => {
        row.addEventListener('click', function (e) {
            if (e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            const href = this.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });
});
</script>
@endpush
@endsection

