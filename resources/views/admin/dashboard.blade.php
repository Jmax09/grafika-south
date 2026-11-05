@extends('layouts.admin')

@section('title', 'Admin Operational Dashboard - Grafika South')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
        Operational Staff Dashboard
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Manage customer orders, conduct artwork design reviews, check raw material stock levels, and reply to inquiries.</p>
</div>

<!-- METRICS CARDS -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
    <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
        <div style="width: 50px; height: 50px; background: var(--danger-soft); color: var(--accent-color); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-clipboard-list"></i>
        </div>
        <div>
            <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Total Orders Today</span>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-main);">{{ $ordersToday }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
        <div style="width: 50px; height: 50px; background: var(--accent-soft); color: var(--text-accent); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-spinner"></i>
        </div>
        <div>
            <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">In Production / Review</span>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-accent);">{{ $inProduction }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
        <div style="width: 50px; height: 50px; background: {{ $lowStock > 0 ? 'var(--danger-soft)' : 'var(--success-soft)' }}; color: {{ $lowStock > 0 ? 'var(--text-danger)' : 'var(--text-success)' }}; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-boxes-packing"></i>
        </div>
        <div>
            <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Low Stock Materials</span>
            <div style="font-size: 1.6rem; font-weight: 800; color: {{ $lowStock > 0 ? 'var(--text-danger)' : 'var(--text-success)' }};">{{ $lowStock }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
        <div style="width: 50px; height: 50px; background: var(--success-soft); color: var(--text-success); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-peso-sign"></i>
        </div>
        <div>
            <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Revenue This Month</span>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-success);">₱{{ number_format($revenueThisMonth, 2) }}</div>
        </div>
    </div>
</div>

<!-- RECENT ORDERS TABLE -->
<div class="card" style="padding: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
        <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main);">Recent Orders Pipeline</h3>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline" style="font-size: 0.82rem; padding: 0.4rem 0.85rem;">View All Orders</a>
    </div>

    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
        <thead>
            <tr style="background: var(--bg-page); text-align: left; color: var(--text-muted);">
                <th style="padding: 0.75rem 1rem;">Order #</th>
                <th style="padding: 0.75rem 1rem;">Customer</th>
                <th style="padding: 0.75rem 1rem;">Total Price</th>
                <th style="padding: 0.75rem 1rem;">Status</th>
                <th style="padding: 0.75rem 1rem;">Date</th>
                <th style="padding: 0.75rem 1rem; text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentOrders as $ord)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 0.85rem 1rem; font-weight: 700; color: var(--text-main);">#{{ $ord->order_number }}</td>
                    <td style="padding: 0.85rem 1rem;">{{ $ord->user ? $ord->user->name : 'N/A' }}</td>
                    <td style="padding: 0.85rem 1rem; font-weight: 700; color: var(--accent-color);">₱{{ number_format($ord->total_price ?: $ord->total_amount, 2) }}</td>
                    <td style="padding: 0.85rem 1rem;">
                        <span class="role-badge" style="background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-color);">
                            {{ str_replace('_', ' ', $ord->status) }}
                        </span>
                    </td>
                    <td style="padding: 0.85rem 1rem; color: var(--text-muted);">{{ $ord->created_at->format('M d, Y') }}</td>
                    <td style="padding: 0.85rem 1rem; text-align: right;">
                        <a href="{{ route('admin.orders.show', $ord->id) }}" class="btn btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                            <i class="fa-solid fa-pen-to-square"></i> Review & Process
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">No orders found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- LOW STOCK POPUP ALERT (auto-open) --}}
@if($lowStock > 0)
<div id="lowstock-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.55); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: var(--bg-surface); width: 100%; max-width: 520px; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); overflow: hidden; border-top: 6px solid var(--danger-color);">
        <div style="padding: 1.5rem 1.5rem 1rem; display: flex; align-items: flex-start; gap: 1rem;">
            <div style="width: 48px; height: 48px; background: var(--danger-soft); color: var(--text-danger); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-danger); margin: 0 0 0.25rem;">Low Stock Alert</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">
                    {{ $lowStock }} material{{ $lowStock > 1 ? 's are' : ' is' }} at or below threshold. Restock soon to avoid production delays.
                </p>
            </div>
        </div>

        <div style="max-height: 300px; overflow-y: auto; padding: 0 1.5rem;">
            @foreach($lowStockMaterials as $m)
                @php $isOut = (float) $m->current_stock <= 0; @endphp
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                    <div>
                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                            {{ $m->name }} <span style="color: var(--text-subtle); font-weight: 500;">&middot; {{ $m->category }}</span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Threshold: {{ number_format($m->low_stock_threshold, 2) }} {{ $m->unit }}</div>
                    </div>
                    <span class="role-badge" style="background: {{ $isOut ? 'var(--danger-soft)' : 'var(--warning-soft)' }}; color: {{ $isOut ? 'var(--text-danger)' : 'var(--text-warning)' }}; border: 1px solid {{ $isOut ? 'var(--danger-border)' : 'var(--warning-border)' }};">
                        {{ $isOut ? 'OUT OF STOCK' : number_format($m->current_stock, 2) . ' ' . $m->unit }}
                    </span>
                </div>
            @endforeach
        </div>

        <div style="padding: 1.25rem 1.5rem; display: flex; gap: 0.75rem; justify-content: flex-end; background: var(--bg-page);">
            <button type="button" onclick="document.getElementById('lowstock-overlay').style.display='none';" class="btn" style="background: var(--bg-surface); color: var(--text-muted); padding: 0.6rem 1.2rem; border-radius: 10px; font-weight: 700; border: 1.5px solid var(--border-color); cursor: pointer;">Close</button>
            <a href="{{ route('admin.materials.index', ['stock_status' => 'low_stock']) }}" class="btn" style="background: var(--danger-color); color: var(--text-inverse); padding: 0.6rem 1.2rem; border-radius: 10px; font-weight: 700; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <i class="fa-solid fa-boxes-packing"></i> Go to Inventory
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var overlay = document.getElementById('lowstock-overlay');
        if (overlay) { overlay.style.display = 'flex'; }
    });
</script>
@endif
@endsection
