@extends('layouts.app')

@section('title', 'Notifications - Grafika South')

@section('content')
<style>
    body {
        background-color: var(--bg-page) !important;
    }

    .customer-dashboard-layout {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
        margin-top: -0.5rem;
    }

    /* Customer Dashboard Layout - Full Width for Content */
    .customer-sidebar, aside.customer-sidebar {
        display: none !important;
    }

    .customer-main-content {
        width: 100% !important;
        max-width: 100% !important;
        flex: 1 1 100% !important;
    }

    /* Main Panel Content Area */
    .customer-main-content {
        flex: 1;
        min-width: 0;
    }

    .panel-header-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 0.2rem;
    }

    @media (min-width: 480px) {
        .panel-header-title { font-size: 1.75rem; }
    }

    .panel-header-subtitle {
        color: var(--text-muted);
        font-size: 0.88rem;
    }

    /* Top Action Buttons */
    .header-actions-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-action-outline {
        background: var(--bg-surface);
        border: 1.5px solid var(--border-color);
        color: var(--text-muted);
        padding: 0.5rem 1.1rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        text-decoration: none;
    }

    .btn-action-outline:hover {
        border-color: var(--accent-color);
        color: var(--text-accent);
        background: var(--accent-soft);
        box-shadow: 0 2px 8px rgba(0, 153, 255, 0.12);
    }

    .btn-action-danger {
        border-color: var(--danger-border);
        color: var(--text-muted);
    }

    .btn-action-danger:hover {
        border-color: var(--danger-color);
        color: var(--text-danger);
        background: var(--danger-soft);
        box-shadow: 0 2px 8px rgba(225, 25, 48, 0.12);
    }

    /* Filter Tabs Bar */
    .notif-filter-tabs {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--bg-surface);
        padding: 0.4rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02);
        margin-bottom: 1.5rem;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .notif-filter-tabs::-webkit-scrollbar { display: none; }

    /* Center the filter tabs on >= sm screens, keep left-aligned on mobile */
    @media (min-width: 640px) {
        .notif-filter-tabs { justify-content: center; }
    }

    .filter-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.375rem 0.75rem; /* py-1.5 px-3 */
        border-radius: 8px;
        font-size: 0.75rem; /* text-xs */
        font-weight: 700;
        color: var(--text-muted);
        text-decoration: none;
        transition: all 0.2s ease;
        white-space: nowrap; /* never truncate the label on small screens */
        flex-shrink: 0;
    }

    @media (min-width: 640px) {
        .filter-tab-btn {
            padding: 0.375rem 1rem;
            font-size: 0.875rem; /* sm:text-sm */
            gap: 0.5rem;
        }
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
        line-height: 1;
        min-width: 22px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .tab-count-unread {
        background: var(--brand-magenta-soft);
        color: var(--text-magenta);
    }

    /* === Custom Confirmation Modal === */
    .confirm-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(4px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        animation: fadeInOverlay 0.2s ease forwards;
    }

    .confirm-modal-overlay.visible {
        display: flex;
    }

    @keyframes fadeInOverlay {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .confirm-modal-dialog {
        background: var(--bg-surface);
        border-radius: 16px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
        padding: 2rem 2.25rem;
        max-width: 420px;
        width: 90%;
        text-align: center;
        animation: scaleUpModal 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes scaleUpModal {
        from { transform: scale(0.92) translateY(10px); opacity: 0; }
        to { transform: scale(1) translateY(0); opacity: 1; }
    }

    .confirm-modal-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--danger-soft);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.1rem;
    }

    .confirm-modal-icon i {
        font-size: 1.5rem;
        color: var(--text-danger);
    }

    .confirm-modal-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 0.5rem;
    }

    .confirm-modal-message {
        font-size: 0.9rem;
        color: var(--text-muted);
        line-height: 1.55;
        margin-bottom: 1.5rem;
    }

    .confirm-modal-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }

    .btn-modal-cancel {
        background: var(--bg-surface);
        border: 1.5px solid var(--border-color);
        color: var(--text-muted);
        padding: 0.6rem 1.4rem;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-modal-cancel:hover {
        border-color: var(--border-strong);
        background: var(--bg-page);
        color: var(--text-main);
    }

    .btn-modal-confirm-danger {
        background: var(--danger-color);
        border: 1.5px solid var(--danger-color);
        color: var(--text-inverse);
        padding: 0.6rem 1.4rem;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-modal-confirm-danger:hover {
        background: var(--danger-color);
        border-color: var(--danger-color);
        box-shadow: 0 4px 12px rgba(225, 25, 48, 0.25);
    }

    /* Notifications List Container */
    .notifications-card {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    /* Notification Interactive Row Card */
    .notif-row {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1.1rem 1.25rem;
        border-radius: 12px;
        background: var(--bg-surface);
        border: 1.5px solid var(--border-color);
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        color: inherit;
    }

    /* Row layout on >= sm screens: text block left, actions far right */
    @media (min-width: 640px) {
        .notif-row {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 1.15rem;
        }
    }

    .notif-row.clickable {
        cursor: pointer;
    }

    .notif-row:hover {
        background: var(--bg-page);
        border-color: var(--border-color);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }

    .notif-row.unread {
        background: var(--accent-soft);
        border-color: var(--info-border);
        border-left: 4px solid var(--accent-color);
    }

    .notif-row.unread:hover {
        background: var(--accent-soft);
        border-color: var(--accent-color);
        border-left: 4px solid var(--accent-color);
    }

    .notif-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    /* Text & icon flex block (icon + text + timestamp) */
    .notif-content {
        display: flex;
        align-items: flex-start;
        gap: 1.15rem;
        width: 100%;
        min-width: 0;
        flex: 1;
    }

    .notif-body {
        flex: 1;
        min-width: 0;
    }

    .notif-message-text {
        font-size: 0.94rem;
        color: var(--text-main);
        font-weight: 600;
        line-height: 1.45;
        margin-bottom: 0.3rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        overflow-wrap: break-word;
        word-break: break-word;
    }

    .unread-indicator-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--accent-color);
        box-shadow: 0 0 0 2px rgba(0, 153, 255, 0.2);
        flex-shrink: 0;
    }

    .notif-meta-time {
        font-size: 0.78rem;
        color: var(--text-subtle);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-weight: 500;
        white-space: nowrap;
        max-width: 100%;
        overflow-x: auto;
        scrollbar-width: none; /* Firefox */
    }

    .notif-meta-time::-webkit-scrollbar { display: none; }

    .notif-meta-time > span {
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* Row Actions */
    .notif-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
        width: 100%;
        justify-content: flex-end;
    }

    /* On >= sm screens, actions sit at the far right of the row instead of below */
    @media (min-width: 640px) {
        .notif-actions {
            width: auto;
            justify-content: flex-start;
        }
    }

    .btn-notif-link {
        background: var(--bg-surface);
        border: 1.5px solid var(--accent-color);
        color: var(--text-accent);
        padding: 0.35rem 0.85rem;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .notif-row:hover .btn-notif-link {
        background: var(--accent-color);
        color: var(--text-inverse);
    }

    .btn-notif-action-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.88rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-notif-action-icon:hover {
        border-color: var(--accent-color);
        color: var(--text-accent);
        background: var(--accent-soft);
        transform: scale(1.05);
    }

    .btn-notif-delete:hover {
        border-color: var(--danger-color);
        color: var(--text-danger);
        background: var(--danger-soft);
    }

    /* Toast Alert Notification */
    .profile-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: var(--text-main);
        color: var(--text-inverse);
        padding: 0.85rem 1.4rem;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.18);
        font-size: 0.9rem;
        font-weight: 600;
        display: none;
        align-items: center;
        gap: 0.75rem;
        z-index: 9999;
        animation: slideUpToast 0.3s ease forwards;
    }

    .profile-toast.toast-success {
        border-left: 4px solid var(--success-color);
    }

    .profile-toast.toast-error {
        border-left: 4px solid var(--danger-color);
    }

    @keyframes slideUpToast {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Deletion Animation */
    .notif-row.removing {
        opacity: 0;
        transform: translateX(40px) scale(0.95);
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
        margin-bottom: 0;
        border-width: 0;
        overflow: hidden;
        transition: all 0.35s ease;
    }

    /* === Custom Modern Pagination Styling === */
    .notif-pagination-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1.25rem;
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
    <!-- LEFT SIDEBAR NAVIGATION -->
    <x-customer-sidebar class="hidden md:block w-64 shrink-0 sticky top-20 z-30" />

    <!-- DEDICATED NOTIFICATIONS CENTER PANEL -->
    <main class="customer-main-content flex-1 w-full min-w-0">
        <!-- HEADER WITH ACTIONS -->
        <div class="pt-2 pb-3" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div class="panel-header-title">Notifications</div>
                <div class="panel-header-subtitle">
                    Track updates regarding your print shop orders, design reviews, and account activity.
                </div>
            </div>

            <div class="header-actions-group">
                @if($unreadNotificationsCount > 0)
                    <button type="button" class="btn-action-outline" id="btn-mark-all-read">
                        <i class="fa-solid fa-check-double" style="color: var(--text-accent);"></i> Mark All as Read
                    </button>
                @endif

                @if($totalNotificationsCount > 0)
                    <button type="button" class="btn-action-outline btn-action-danger" id="btn-clear-all">
                        <i class="fa-regular fa-trash-can" style="color: var(--text-danger);"></i> Clear All
                    </button>
                @endif
            </div>
        </div>

        <!-- FILTER TABS -->
        <div class="notif-filter-tabs flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none w-full justify-start sm:justify-center">
            <a href="{{ route('customer.notifications', ['filter' => 'all']) }}" class="filter-tab-btn px-3 py-1.5 text-xs sm:text-sm {{ ($filter ?? 'all') === 'all' ? 'active' : '' }}">
                <i class="fa-solid fa-inbox"></i> All
                <span class="tab-count-badge">{{ $totalNotificationsCount }}</span>
            </a>
            <a href="{{ route('customer.notifications', ['filter' => 'unread']) }}" class="filter-tab-btn px-3 py-1.5 text-xs sm:text-sm {{ ($filter ?? 'all') === 'unread' ? 'active' : '' }}">
                <i class="fa-regular fa-envelope"></i> Unread
                <span class="tab-count-badge {{ $unreadNotificationsCount > 0 ? 'tab-count-unread' : '' }}" id="unread-tab-badge">
                    {{ $unreadNotificationsCount }}
                </span>
            </a>
            <a href="{{ route('customer.notifications', ['filter' => 'read']) }}" class="filter-tab-btn px-3 py-1.5 text-xs sm:text-sm {{ ($filter ?? 'all') === 'read' ? 'active' : '' }}">
                <i class="fa-regular fa-envelope-open"></i> Read
                <span class="tab-count-badge">{{ $readNotificationsCount }}</span>
            </a>
        </div>

        <!-- NOTIFICATIONS LIST CARD -->
        <div class="notifications-card" id="notifications-list-container">
            @forelse($notifications as $notif)
                @php
                    $msgLower = strtolower($notif->message);
                    $iconClass = 'fa-solid fa-bell';
                    $iconBg = '#EFF6FF';
                    $iconColor = '#2563EB';

                    if (str_contains($msgLower, 'ready') || str_contains($msgLower, 'shipped') || str_contains($msgLower, 'delivery')) {
                        $iconClass = 'fa-solid fa-truck-fast';
                        $iconBg = '#E6F4FF';
                        $iconColor = '#0099FF';
                    } elseif (str_contains($msgLower, 'completed') || str_contains($msgLower, 'approved') || str_contains($msgLower, 'success')) {
                        $iconClass = 'fa-solid fa-circle-check';
                        $iconBg = '#FDE8F5';
                        $iconColor = '#EC008C';
                    } elseif (str_contains($msgLower, 'chat') || str_contains($msgLower, 'message') || str_contains($msgLower, 'revision') || str_contains($msgLower, 'review')) {
                        $iconClass = 'fa-solid fa-comments';
                        $iconBg = '#FEF3C7';
                        $iconColor = '#D97706';
                    } elseif (str_contains($msgLower, 'ticket') || str_contains($msgLower, 'support')) {
                        $iconClass = 'fa-solid fa-headset';
                        $iconBg = '#EEF2FF';
                        $iconColor = '#6366F1';
                    } elseif (str_contains($msgLower, 'printing') || str_contains($msgLower, 'production') || str_contains($msgLower, 'order')) {
                        $iconClass = 'fa-solid fa-box-open';
                        $iconBg = '#E0F2FE';
                        $iconColor = '#0284C7';
                    }
                @endphp

                <div class="notif-row flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 {{ $notif->is_read ? '' : 'unread' }} {{ $notif->link ? 'clickable' : '' }}"
                     id="notif-item-{{ $notif->id }}"
                     data-id="{{ $notif->id }}"
                     data-link="{{ $notif->link ?? '' }}"
                     data-is-read="{{ $notif->is_read ? '1' : '0' }}">

                    <!-- Icon + Text + Timestamp in one flex block -->
                    <div class="notif-content flex items-start gap-3 min-w-0 flex-1">
                        <!-- Color Badged Icon Circle -->
                        <div class="notif-icon-circle" style="background: {{ $iconBg }}; color: {{ $iconColor }};">
                            <i class="{{ $iconClass }}"></i>
                        </div>

                        <!-- Message Body -->
                        <div class="notif-body">
                            <div class="notif-message-text break-words text-sm font-medium text-gray-900 w-full">
                                @if(!$notif->is_read)
                                    <span class="unread-indicator-dot" title="Unread alert"></span>
                                @endif
                                <span>{{ $notif->message }}</span>
                            </div>
                            <div class="notif-meta-time text-xs text-gray-500 whitespace-nowrap mt-1">
                                <i class="fa-regular fa-clock"></i>
                                <span>{{ $notif->created_at->diffForHumans() }}</span>
                                <span style="color: var(--text-disabled);">•</span>
                                <span>{{ $notif->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Group -->
                    <div class="notif-actions flex items-center gap-2 shrink-0 self-end sm:self-center mt-2 sm:mt-0">
                        @if($notif->link)
                            <a href="{{ $notif->link }}" class="btn-notif-link" title="Open details">
                                <span>View</span>
                                <i class="fa-solid fa-arrow-right" style="font-size: 0.75rem;"></i>
                            </a>
                        @endif

                        @if(!$notif->is_read)
                            <button type="button" class="btn-notif-action-icon btn-notif-mark-single" title="Mark as read" data-id="{{ $notif->id }}">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        @endif

                        <button type="button" class="btn-notif-action-icon btn-notif-delete" title="Delete notification" data-id="{{ $notif->id }}">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-6 flex flex-col items-center justify-center" style="text-align: center; color: var(--text-muted);">
                    <div style="width: 72px; height: 72px; border-radius: 50%; background: var(--bg-page); border: 1.5px solid var(--border-color); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.25rem;">
                        <i class="fa-regular fa-bell-slash" style="font-size: 2.2rem; color: var(--text-subtle);"></i>
                    </div>
                    
                    @if(($filter ?? 'all') === 'unread')
                        <h3 style="font-weight: 800; font-size: 1.2rem; color: var(--text-main); margin-bottom: 0.35rem;">No Unread Notifications</h3>
                        <p style="font-size: 0.9rem; color: var(--text-muted); max-width: 380px; margin: 0 auto 1.25rem;">
                            Great job! You are completely caught up on all order updates and shop messages.
                        </p>
                        <a href="{{ route('customer.notifications', ['filter' => 'all']) }}" class="btn-action-outline">
                            <i class="fa-solid fa-inbox"></i> View All Notifications
                        </a>
                    @elseif(($filter ?? 'all') === 'read')
                        <h3 style="font-weight: 800; font-size: 1.2rem; color: var(--text-main); margin-bottom: 0.35rem;">No Read Notifications</h3>
                        <p style="font-size: 0.9rem; color: var(--text-muted); max-width: 380px; margin: 0 auto;">
                            You don't have any archived read alerts yet.
                        </p>
                    @else
                        <h3 style="font-weight: 800; font-size: 1.2rem; color: var(--text-main); margin-bottom: 0.35rem;">No Notifications Yet</h3>
                        <p style="font-size: 0.9rem; color: var(--text-muted); max-width: 420px; margin: 0 auto;">
                            Updates about your print orders, design reviews, and support tickets will appear here.
                        </p>
                    @endif
                </div>
            @endforelse

            @if($notifications->hasPages())
                <div class="notif-pagination-container">
                    <div class="pagination-info">
                        Showing <span class="fw-bold">{{ $notifications->firstItem() ?? 0 }}</span> to <span class="fw-bold">{{ $notifications->lastItem() ?? 0 }}</span> of <span class="fw-bold">{{ $notifications->total() }}</span> alerts
                    </div>

                    <div class="pagination-buttons">
                        {{-- Previous Page Link --}}
                        @if ($notifications->onFirstPage())
                            <span class="pagination-btn disabled">
                                <i class="fa-solid fa-chevron-left" style="font-size: 0.75rem;"></i> Previous
                            </span>
                        @else
                            <a href="{{ $notifications->previousPageUrl() }}" class="pagination-btn">
                                <i class="fa-solid fa-chevron-left" style="font-size: 0.75rem;"></i> Previous
                            </a>
                        @endif

                        {{-- Pagination Numbers --}}
                        <div class="pagination-numbers">
                            @foreach ($notifications->getUrlRange(max(1, $notifications->currentPage() - 2), min($notifications->lastPage(), $notifications->currentPage() + 2)) as $page => $url)
                                @if ($page == $notifications->currentPage())
                                    <span class="pagination-num active">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="pagination-num">{{ $page }}</a>
                                @endif
                            @endforeach
                        </div>

                        {{-- Next Page Link --}}
                        @if ($notifications->hasMorePages())
                            <a href="{{ $notifications->nextPageUrl() }}" class="pagination-btn">
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
        </div>
    </main>
</div>

<!-- TOAST ALERT -->
<div id="profile-toast-box" class="profile-toast">
    <i class="fa-solid fa-circle-check" id="toast-icon" style="color: var(--text-success); font-size: 1.15rem;"></i>
    <span id="toast-text">Notification update</span>
</div>

<!-- CUSTOM CONFIRMATION MODAL: Clear All -->
<div class="confirm-modal-overlay" id="clear-all-modal">
    <div class="confirm-modal-dialog">
        <div class="confirm-modal-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="confirm-modal-title">Clear All Notifications?</div>
        <div class="confirm-modal-message">
            Are you sure you want to clear all your notifications? This action cannot be undone.
        </div>
        <div class="confirm-modal-actions">
            <button type="button" class="btn-modal-cancel" id="modal-cancel-btn">Cancel</button>
            <button type="button" class="btn-modal-confirm-danger" id="modal-confirm-btn">
                <i class="fa-regular fa-trash-can"></i> Yes, Clear All
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const csrfToken = '{{ csrf_token() }}';
    const toastBox = document.getElementById('profile-toast-box');
    const toastText = document.getElementById('toast-text');
    const toastIcon = document.getElementById('toast-icon');
    let toastTimeout = null;

    function showToast(message, isError = false) {
        if (!toastBox || !toastText) return;
        if (toastTimeout) clearTimeout(toastTimeout);
        toastText.textContent = message;
        if (isError) {
            toastBox.className = 'profile-toast toast-error';
            if (toastIcon) {
                toastIcon.className = 'fa-solid fa-triangle-exclamation';
                toastIcon.style.color = '#E11930';
            }
        } else {
            toastBox.className = 'profile-toast toast-success';
            if (toastIcon) {
                toastIcon.className = 'fa-solid fa-circle-check';
                toastIcon.style.color = '#10B981';
            }
        }
        toastBox.style.display = 'flex';
        toastTimeout = setTimeout(() => {
            toastBox.style.display = 'none';
        }, 3500);
    }

    function updateUnreadBadges(count) {
        const unreadTabBadge = document.getElementById('unread-tab-badge');
        if (unreadTabBadge) {
            unreadTabBadge.textContent = count;
            if (count > 0) {
                unreadTabBadge.classList.add('tab-count-unread');
            } else {
                unreadTabBadge.classList.remove('tab-count-unread');
            }
        }
        const sidebarPill = document.querySelector('.customer-sidebar .notif-badge-pill');
        if (sidebarPill) {
            if (count > 0) {
                sidebarPill.textContent = count;
                sidebarPill.style.display = 'flex';
            } else {
                sidebarPill.style.display = 'none';
            }
        }
    }

    // 1. Clickable notification rows redirect straight to details
    document.querySelectorAll('.notif-row.clickable').forEach(row => {
        row.addEventListener('click', function (e) {
            // Prevent if clicked an action button inside
            if (e.target.closest('.notif-actions') || e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            const link = this.getAttribute('data-link');
            const id = this.getAttribute('data-id');
            const isRead = this.getAttribute('data-is-read') === '1';

            if (link) {
                // If unread, mark as read before navigating
                if (!isRead && id) {
                    fetch(`{{ url('/customer/notifications') }}/${id}/mark-read`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    }).finally(() => {
                        window.location.href = link;
                    });
                } else {
                    window.location.href = link;
                }
            }
        });
    });

    // 2. Mark Single Notification as Read
    document.querySelectorAll('.btn-notif-mark-single').forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.stopPropagation();
            const id = this.getAttribute('data-id');
            const row = document.getElementById(`notif-item-${id}`);

            try {
                const res = await fetch(`{{ url('/customer/notifications') }}/${id}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    if (row) {
                        row.classList.remove('unread');
                        row.setAttribute('data-is-read', '1');
                        const dot = row.querySelector('.unread-indicator-dot');
                        if (dot) dot.remove();
                    }
                    this.remove();
                    updateUnreadBadges(data.unread_count ?? 0);
                    showToast('Notification marked as read.');
                }
            } catch (err) {
                console.error(err);
                showToast('Failed to mark as read.', true);
            }
        });
    });

    // 3. Delete Individual Notification
    document.querySelectorAll('.btn-notif-delete').forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.stopPropagation();
            const id = this.getAttribute('data-id');
            const row = document.getElementById(`notif-item-${id}`);

            try {
                const res = await fetch(`{{ url('/customer/notifications') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    if (row) {
                        row.classList.add('removing');
                        setTimeout(() => {
                            row.remove();
                            // Check if empty
                            const remaining = document.querySelectorAll('.notif-row');
                            if (remaining.length === 0) {
                                window.location.reload();
                            }
                        }, 350);
                    }
                    updateUnreadBadges(data.unread_count ?? 0);
                    showToast('Notification deleted.');
                } else {
                    showToast('Failed to delete notification.', true);
                }
            } catch (err) {
                console.error(err);
                showToast('Error deleting notification.', true);
            }
        });
    });

    // 4. Mark All As Read
    const markAllBtn = document.getElementById('btn-mark-all-read');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', async function (e) {
            e.preventDefault();
            try {
                const res = await fetch('{{ route('customer.notifications.markRead') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    document.querySelectorAll('.notif-row.unread').forEach(row => {
                        row.classList.remove('unread');
                        row.setAttribute('data-is-read', '1');
                        const dot = row.querySelector('.unread-indicator-dot');
                        if (dot) dot.remove();
                        const markBtn = row.querySelector('.btn-notif-mark-single');
                        if (markBtn) markBtn.remove();
                    });
                    markAllBtn.remove();
                    updateUnreadBadges(0);
                    showToast('All notifications marked as read.');
                }
            } catch (err) {
                console.error(err);
                showToast('Failed to mark all as read.', true);
            }
        });
    }

    // 5. Clear All Notifications (with Custom Modal)
    const clearAllBtn = document.getElementById('btn-clear-all');
    const clearModal = document.getElementById('clear-all-modal');
    const modalCancelBtn = document.getElementById('modal-cancel-btn');
    const modalConfirmBtn = document.getElementById('modal-confirm-btn');

    function openClearModal() {
        if (clearModal) {
            clearModal.classList.add('visible');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeClearModal() {
        if (clearModal) {
            clearModal.classList.remove('visible');
            document.body.style.overflow = '';
        }
    }

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openClearModal();
        });
    }

    if (modalCancelBtn) {
        modalCancelBtn.addEventListener('click', closeClearModal);
    }

    // Close modal on overlay click (outside dialog)
    if (clearModal) {
        clearModal.addEventListener('click', function (e) {
            if (e.target === clearModal) {
                closeClearModal();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && clearModal && clearModal.classList.contains('visible')) {
            closeClearModal();
        }
    });

    if (modalConfirmBtn) {
        modalConfirmBtn.addEventListener('click', async function () {
            closeClearModal();
            try {
                const res = await fetch('{{ route('customer.notifications.clearAll') }}', {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    showToast('All notifications cleared.');
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                }
            } catch (err) {
                console.error(err);
                showToast('Failed to clear notifications.', true);
            }
        });
    }
})();
</script>
@endpush
@endsection

