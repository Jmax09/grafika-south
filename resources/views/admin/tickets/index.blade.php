@extends('layouts.admin')

@section('title', 'Support Tickets Inbox - Admin Panel')

@section('content')
<style>
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-pending,
    .badge-open { background: var(--warning-soft); color: var(--text-warning); border: 1px solid var(--warning-border); }
    .badge-in-progress { background: var(--accent-soft); color: var(--text-accent); border: 1px solid var(--info-border); }
    .badge-resolved { background: var(--success-soft); color: var(--text-success); border: 1px solid var(--success-border); }
    .badge-closed { background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-color); }

    .badge-priority-urgent { background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); }
    .badge-priority-high { background: var(--warning-soft); color: var(--text-warning); border: 1px solid var(--warning-border); }
    .badge-priority-medium { background: var(--warning-soft); color: var(--text-warning); border: 1px solid var(--warning-border); }
    .badge-priority-low { background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-color); }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .kpi-box {
        background: var(--bg-surface);
        border-radius: 12px;
        padding: 1.25rem;
        border: 1px solid var(--border-color);
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    /* Keep every ticket row on a single clean line (no word wrapping, no horizontal slider) */
    .ticket-table {
        table-layout: fixed;
        width: 100%;
    }
    .ticket-table th,
    .ticket-table td { white-space: nowrap; }
    .ticket-cell-ellipsis {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ticket-table .badge-status { white-space: nowrap; }
</style>

<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
            Support Ticket Inbox
        </h1>
        <p style="color: var(--text-muted); font-size: 0.92rem;">
            Manage, respond to, and resolve customer inquiries, complaints, and service tickets.
        </p>
    </div>
</div>

{{-- KPI Summary Boxes --}}
<div class="kpi-grid">
    <div class="kpi-box" style="border-left: 4px solid var(--warning-color);">
        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Pending Tickets</span>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--text-warning); margin-top: 4px;">{{ $counts['pending'] ?? $counts['open'] }}</div>
    </div>
    <div class="kpi-box" style="border-left: 4px solid var(--accent-color);">
        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">In Progress</span>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--text-accent); margin-top: 4px;">{{ $counts['in_progress'] }}</div>
    </div>
    <div class="kpi-box" style="border-left: 4px solid var(--success-color);">
        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Resolved</span>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--text-success); margin-top: 4px;">{{ $counts['resolved'] }}</div>
    </div>
    <div class="kpi-box" style="border-left: 4px solid var(--accent-color);">
        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Total Tickets</span>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--text-accent); margin-top: 4px;">{{ $counts['total'] }}</div>
    </div>
</div>

{{-- SEARCH & FILTER BAR CARD --}}
<div class="card" style="padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.06), 0 2px 4px -1px rgba(0, 0, 0, 0.04); border: 1px solid var(--border-color); border-bottom: 2px solid var(--cmyk-cyan); background: var(--bg-surface);">
    <form action="{{ route('admin.tickets.index') }}" method="GET" style="display: flex; gap: 0.85rem; align-items: center; flex-wrap: wrap;" id="tickets-filter-form">

        {{-- Search Input + Button (fused pair) --}}
        <div style="flex: 2; min-width: 260px; display: flex; align-items: center;">
            <input type="text" name="search" id="ticket-search-input" value="{{ request('search') }}"
                placeholder="Search by Ticket #, Subject, Customer Name, Email, or Order #"
                style="flex: 1; padding: 0.65rem 1rem; border: 1px solid var(--border-color); border-right: none; border-radius: 10px 0 0 10px; font-size: 0.92rem; outline: none; background: var(--bg-surface); color: var(--text-main); transition: border-color 0.2s;"
                onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='#CBD5E1';">
            <button type="submit"
                style="padding: 0.65rem 1.1rem; background: var(--accent-color); color: var(--text-inverse); border: 1px solid var(--accent-color); border-radius: 0 10px 10px 0; font-size: 0.88rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; transition: background 0.15s;"
                onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
        </div>

        {{-- Status Dropdown Filter --}}
        <div style="flex: 1; min-width: 160px;">
            <select name="status" id="ticket-status-select" class="custom-combo" data-icon="fa-filter"
                onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="pending" {{ in_array(request('status'), ['pending', 'open']) ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </div>

        {{-- Priority Dropdown Filter --}}
        <div style="flex: 1; min-width: 160px;">
            <select name="priority" id="ticket-priority-select" class="custom-combo" data-icon="fa-flag"
                onchange="this.form.submit()">
                <option value="">All Priorities</option>
                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
            </select>
        </div>

        @if(request()->hasAny(['search', 'status', 'priority', 'category']))
            <a href="{{ route('admin.tickets.index') }}"
                style="padding: 0.65rem 1rem; background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.88rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; transition: background 0.15s;"
                onmouseover="this.style.background='#E2E8F0'" onmouseout="this.style.background='#F1F5F9'">
                <i class="fa-solid fa-xmark"></i> Clear Filters
            </a>
        @endif
    </form>
</div>

{{-- Tickets Inbox Table --}}
<div class="card" style="padding: 0; overflow: hidden; border-radius: 12px;">
    @if($tickets->isEmpty())
        <div style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
            <i class="fa-solid fa-inbox" style="font-size: 2.5rem; color: var(--text-disabled); margin-bottom: 0.75rem; display: block;"></i>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">No Support Tickets Found</h3>
            <p style="font-size: 0.88rem;">There are no tickets matching your current filter criteria.</p>
        </div>
    @else
        <div style="overflow-x: auto;">
            <table class="ticket-table" style="border-collapse: collapse; font-size: 0.88rem; text-align: left;">
                <thead>
                    <tr style="background: var(--bg-page); border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-weight: 700;">
                        <th style="padding: 0.85rem 1.25rem; width: 12%;">Ticket #</th>
                        <th style="padding: 0.85rem 1.25rem; width: 15%;">Customer</th>
                        <th style="padding: 0.85rem 1.25rem; width: 18%;">Subject &amp; Category</th>
                        <th style="padding: 0.85rem 1.25rem; width: 11%;">Linked Order</th>
                        <th style="padding: 0.85rem 1.25rem; width: 8%;">Priority</th>
                        <th style="padding: 0.85rem 1.25rem; width: 11%;">Status</th>
                        <th style="padding: 0.85rem 1.25rem; width: 11%;">Last Activity</th>
                        <th style="padding: 0.85rem 1.25rem; width: 14%; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                        <tr style="border-bottom: 1px solid var(--border-color); transition: all 0.15s ease; cursor: pointer;"
                            onmouseover="this.style.background='var(--accent-soft)'; this.style.boxShadow='inset 0 0 0 2px #0099FF';"
                            onmouseout="this.style.background='transparent'; this.style.boxShadow='none';"
                            onclick="window.location='{{ route('admin.tickets.show', $ticket->id) }}'">
                            <td style="padding: 1rem 1.25rem; font-weight: 700; color: var(--text-main);" class="ticket-cell-ellipsis" title="{{ $ticket->ticket_number }}">
                                {{ $ticket->ticket_number }}
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <div style="font-weight: 700; color: var(--text-main);" class="ticket-cell-ellipsis" title="{{ $ticket->user->name }}">{{ $ticket->user->name }}</div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);" class="ticket-cell-ellipsis" title="{{ $ticket->user->email }}">{{ $ticket->user->email }}</div>
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <div style="font-weight: 700; color: var(--text-main); margin-bottom: 2px;" class="ticket-cell-ellipsis" title="{{ $ticket->subject }}">
                                    {{ $ticket->subject }}
                                </div>
                                <span style="font-size: 0.75rem; color: var(--text-muted); background: var(--bg-subtle); padding: 2px 6px; border-radius: 4px; display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $ticket->category }}">
                                    {{ $ticket->category }}
                                </span>
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                @if($ticket->order)
                                    <a href="{{ route('admin.orders.show', $ticket->order->id) }}" onclick="event.stopPropagation();" style="color: var(--text-accent); font-weight: 600; text-decoration: none; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="#{{ $ticket->order->order_number }}">
                                        <i class="fa-solid fa-box"></i> #{{ $ticket->order->order_number }}
                                    </a>
                                @else
                                    <span style="color: var(--text-subtle); font-style: italic;">General Inquiry</span>
                                @endif
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <span class="badge-status {{ $ticket->priority_badge_class }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <span class="badge-status {{ $ticket->status_badge_class }}">
                                    {{ $ticket->status_label }}
                                </span>
                            </td>
                            <td style="padding: 1rem 1.25rem; color: var(--text-muted); font-size: 0.82rem;" class="ticket-cell-ellipsis" title="{{ $ticket->last_replied_at ? $ticket->last_replied_at->diffForHumans() : $ticket->created_at->diffForHumans() }}">
                                {{ $ticket->last_replied_at ? $ticket->last_replied_at->diffForHumans() : $ticket->created_at->diffForHumans() }}
                            </td>
                            <td style="padding: 1rem 1.25rem; text-align: right;">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" onclick="event.stopPropagation();" class="btn btn-primary" style="padding: 0.35rem 0.85rem; font-size: 0.82rem; border-radius: 6px;">
                                    <i class="fa-solid fa-reply"></i> View Ticket
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-color);">
            {{ $tickets->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection

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
