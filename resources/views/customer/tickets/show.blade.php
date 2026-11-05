@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number . ' - Grafika South Support')

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
    .customer-main-content {
        width: 100% !important;
        max-width: 100% !important;
        flex: 1 1 100% !important;
        min-width: 0;
    }

    .ticket-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .ticket-grid { grid-template-columns: 1fr; }
    }

    .ticket-sidebar { position: sticky; top: 90px; display: flex; flex-direction: column; gap: 1.25rem; }

    /* Back link button */
    .back-link {
        display: inline-flex; align-items: center; gap: 0.45rem;
        color: var(--text-inverse); text-decoration: none; font-size: 0.85rem; font-weight: 700;
        padding: 0.5rem 1rem; border-radius: 10px; border: none; background: var(--accent-color);
        transition: all 0.15s; margin-bottom: 0.75rem;
        box-shadow: 0 2px 8px rgba(0, 153, 255, 0.25);
    }
    .back-link:hover { background: var(--accent-color); transform: translateX(-2px); box-shadow: 0 4px 12px rgba(0, 153, 255, 0.3); }

    /* Badges */
    .badge-status {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.25rem 0.65rem; border-radius: 9999px;
        font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
    }
    .badge-pending, .badge-open { background: var(--warning-soft); color: var(--text-warning); border: 1px solid var(--warning-border); }
    .badge-in-progress { background: var(--accent-soft); color: var(--text-accent); border: 1px solid var(--info-border); }
    .badge-resolved { background: var(--success-soft); color: var(--text-success); border: 1px solid var(--success-border); }
    .badge-closed { background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-color); }

    /* Chat bubbles — messenger style */
    .msg-row { display: flex; margin-bottom: 0.65rem; }
    .msg-row-left { justify-content: flex-start; }
    .msg-row-right { justify-content: flex-end; }
    .msg-bubble {
        max-width: 72%; padding: 0.75rem 1rem; border-radius: 14px; position: relative;
    }
    .msg-customer {
        background: var(--bg-page); border: 1px solid var(--border-color);
        border-top-left-radius: 4px;
    }
    .msg-admin {
        background: var(--accent-soft); border: 1px solid var(--info-border);
        border-top-right-radius: 4px;
    }
    .msg-meta {
        font-size: 0.75rem; font-weight: 600; margin-bottom: 0.3rem;
    }
    .msg-meta-customer { color: var(--text-muted); }
    .msg-meta-admin { color: var(--text-accent); }
    .msg-body { color: var(--text-main); line-height: 1.55; font-size: 0.9rem; white-space: pre-line; }

    /* Attachments */
    .att-grid { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.6rem; padding-top: 0.5rem; border-top: 1px dashed rgba(0,0,0,0.06); }
    .att-img {
        width: 100px; height: 75px; border-radius: 8px; overflow: hidden; border: 1px solid var(--border-color);
        display: block; transition: border-color 0.15s;
    }
    .att-img:hover { border-color: var(--accent-color); }
    .att-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .att-doc {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.3rem 0.6rem; border-radius: 6px; background: var(--bg-surface); border: 1px solid var(--border-color);
        color: var(--text-main); text-decoration: none; font-size: 0.78rem; font-weight: 600; transition: all 0.15s;
    }
    .att-doc:hover { border-color: var(--accent-color); color: var(--text-accent); }

    /* Inline reply bar */
    .reply-bar {
        display: flex; align-items: center; gap: 0.6rem;
        padding: 0.75rem 1rem; border-top: 1px solid var(--border-color); background: var(--bg-surface);
    }
    .reply-attach-btn {
        width: 38px; height: 38px; border-radius: 50%; border: 1.5px solid var(--info-border); background: var(--accent-soft);
        display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-accent);
        transition: all 0.15s; flex-shrink: 0; position: relative;
    }
    .reply-attach-btn:hover { border-color: var(--accent-color); color: var(--text-inverse); background: var(--accent-color); }
    .reply-attach-btn input {
        position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;
    }
    .reply-input {
        flex: 1; padding: 0.6rem 0.85rem; border: 1px solid var(--border-color); border-radius: 9999px;
        font-size: 0.9rem; color: var(--text-main); background: var(--bg-page); outline: none; font-family: inherit;
        transition: border-color 0.15s;
    }
    .reply-input:focus { border-color: var(--accent-color); background: var(--bg-surface); }
    .reply-send-btn {
        padding: 0.55rem 1.25rem; background: var(--accent-color); color: var(--text-inverse); border: none; border-radius: 10px;
        font-size: 0.88rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center;
        gap: 0.35rem; white-space: nowrap; transition: background 0.15s; flex-shrink: 0;
    }
    .reply-send-btn:hover { background: var(--accent-color); }

    /* Preview Pills */
    .preview-grid { display: flex; flex-wrap: wrap; gap: 0.4rem; padding: 0.4rem 1rem 0.5rem; }
    .preview-pill {
        display: inline-flex; align-items: center; gap: 0.4rem; background: var(--bg-surface); border: 1px solid var(--info-border);
        border-radius: 6px; padding: 0.25rem 0.5rem; font-size: 0.78rem; font-weight: 600; color: var(--text-main);
    }
    .pill-remove {
        border: none; background: var(--bg-subtle); color: var(--text-muted);
        border-radius: 4px; width: 18px; height: 18px; display: inline-flex; align-items: center;
        justify-content: center; cursor: pointer; font-size: 0.7rem; transition: all 0.15s;
    }
    .pill-remove:hover { background: var(--danger-soft); color: var(--text-danger); }

    /* Sidebar labels */
    .sidebar-label {
        display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 0.2rem;
    }

    /* Chat scroll */
    .chat-scroll { max-height: 520px; overflow-y: auto; padding-right: 0.3rem; scroll-behavior: smooth; }
    .chat-scroll::-webkit-scrollbar { width: 5px; }
    .chat-scroll::-webkit-scrollbar-track { background: var(--bg-subtle); border-radius: 9999px; }
    .chat-scroll::-webkit-scrollbar-thumb { background: var(--text-disabled); border-radius: 9999px; }
    .chat-scroll::-webkit-scrollbar-thumb:hover { background: var(--accent-color); }
</style>

<div class="customer-dashboard-layout flex flex-col md:flex-row gap-6 items-start">
    {{-- Customer Sidebar --}}
    <x-customer-sidebar />

    {{-- Main Content --}}
    <main class="customer-main-content flex-1 w-full min-w-0">
        {{-- Header --}}
        <div style="margin-bottom: 1.25rem;">
            <a href="{{ route('customer.tickets.index') }}" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Back to Support Tickets
            </a>
            <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap; margin-bottom: 0.2rem;">
                <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0;">Ticket #{{ $ticket->ticket_number }}</h1>
                <span class="badge-status {{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
            </div>
            <p style="color: var(--text-muted); font-weight: 600; font-size: 0.95rem; margin: 0;">{{ $ticket->subject }}</p>
        </div>

        <div class="ticket-grid">
            {{-- LEFT: Chat Card --}}
            <div>
                <div class="card" style="padding: 0; border-radius: 14px; margin-bottom: 1.25rem; overflow: hidden; background: var(--bg-surface); border: 1px solid var(--border-color);">
                    {{-- Chat Header --}}
                    <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color);">
                        <span style="font-weight: 800; color: var(--text-main); font-size: 1rem; display: flex; align-items: center; gap: 0.45rem;">
                            <i class="fa-solid fa-comments" style="color: var(--text-accent);"></i> Customer Support Chat
                        </span>
                    </div>

                    {{-- Messages --}}
                    <div class="chat-scroll" id="chat-scroll" style="padding: 1.25rem;">
                        @foreach($ticket->messages as $msg)
                            <div class="msg-row {{ $msg->is_admin_reply ? 'msg-row-right' : 'msg-row-left' }}">
                                <div class="msg-bubble {{ $msg->is_admin_reply ? 'msg-admin' : 'msg-customer' }}">
                                    <div class="msg-meta {{ $msg->is_admin_reply ? 'msg-meta-admin' : 'msg-meta-customer' }}">
                                        {{ $msg->is_admin_reply ? 'Grafika South Support Team' : $msg->user->name }} &middot; {{ $msg->created_at->format('M d, h:i A') }}
                                    </div>
                                    <div class="msg-body">{{ $msg->message }}</div>

                                    @if(count($msg->attachments) > 0)
                                        @php
                                            $images = array_filter($msg->attachments, fn($p) => $msg->isImageAttachment($p));
                                            $videos = array_filter($msg->attachments, fn($p) => $msg->isVideoAttachment($p));
                                            $docs   = array_filter($msg->attachments, fn($p) => !$msg->isImageAttachment($p) && !$msg->isVideoAttachment($p));
                                        @endphp

                                        @if(count($images) > 0)
                                            <div class="att-grid">
                                                @foreach($images as $img)
                                                    <a href="{{ asset('storage/' . $img) }}" target="_blank" class="att-img" title="{{ basename($img) }}">
                                                        <img src="{{ asset('storage/' . $img) }}" alt="{{ basename($img) }}" loading="lazy">
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(count($videos) > 0)
                                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.5rem;">
                                                @foreach($videos as $vid)
                                                    <div style="max-width: 340px;">
                                                        <video controls style="width: 100%; border-radius: 8px; border: 1px solid var(--border-color); background: var(--text-main);" preload="metadata">
                                                            <source src="{{ asset('storage/' . $vid) }}">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                                            <i class="fa-solid fa-video" style="color: var(--text-violet);"></i> {{ basename($vid) }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(count($docs) > 0)
                                            <div class="att-grid">
                                                @foreach($docs as $doc)
                                                    @php
                                                        $ext = strtolower(pathinfo($doc, PATHINFO_EXTENSION));
                                                        $icon = 'fa-file-lines'; $iconColor = '#0099FF';
                                                        if ($ext === 'pdf') { $icon = 'fa-file-pdf'; $iconColor = '#DC2626'; }
                                                        elseif (in_array($ext, ['doc', 'docx'])) { $icon = 'fa-file-word'; $iconColor = '#2563EB'; }
                                                        elseif (in_array($ext, ['zip', 'rar'])) { $icon = 'fa-file-zipper'; $iconColor = '#D97706'; }
                                                    @endphp
                                                    <a href="{{ asset('storage/' . $doc) }}" target="_blank" class="att-doc">
                                                        <i class="fa-solid {{ $icon }}" style="color: {{ $iconColor }};"></i> {{ basename($doc) }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- File preview area --}}
                    <div class="preview-grid" id="preview-grid" style="display: none;"></div>

                    {{-- Reply Form --}}
                    @if($ticket->status === 'closed')
                        <div style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.88rem; border-top: 1px solid var(--border-color); background: var(--bg-page);">
                            <i class="fa-solid fa-lock" style="color: var(--text-subtle); margin-right: 4px;"></i>
                            This ticket has been marked closed. If you have any further questions, please submit a new ticket.
                        </div>
                    @else
                        <form action="{{ route('customer.tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="reply-bar">
                                <div class="reply-attach-btn" title="Attach photos, videos, files">
                                    <i class="fa-solid fa-paperclip"></i>
                                    <input type="file" name="attachments[]" id="reply-files" accept="image/*,video/*,.pdf,.doc,.docx,.zip" multiple>
                                </div>
                                <input type="text" name="message" class="reply-input" placeholder="Reply to support..." required>
                                <button type="submit" class="reply-send-btn">
                                    <i class="fa-solid fa-paper-plane"></i> Reply
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- RIGHT: Sidebar --}}
            <aside class="ticket-sidebar">
                {{-- Ticket Details --}}
                <div class="card" style="border-radius: 12px; padding: 1.15rem; background: var(--bg-surface); border: 1px solid var(--border-color);">
                    <h3 style="font-size: 0.92rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.85rem; padding-bottom: 0.45rem; border-bottom: 1px solid var(--border-color);">
                        Ticket Details
                    </h3>
                    <div style="font-size: 0.85rem; display: flex; flex-direction: column; gap: 0.65rem;">
                        <div>
                            <span class="sidebar-label">Category</span>
                            <span style="font-weight: 700; color: var(--text-main);">{{ $ticket->category }}</span>
                        </div>
                        <div>
                            <span class="sidebar-label">Submitted Date</span>
                            <span style="color: var(--text-muted);">{{ $ticket->created_at->format('M d, Y - h:i A') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Linked Order (if any) --}}
                @if($ticket->order)
                    <div class="card" style="border-radius: 12px; padding: 1.15rem; background: var(--bg-surface); border: 1px solid var(--border-color);">
                        <h3 style="font-size: 0.92rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.75rem; padding-bottom: 0.45rem; border-bottom: 1px solid var(--border-color);">
                            Linked Order
                        </h3>
                        <div style="font-size: 0.85rem;">
                            <div style="font-weight: 700; color: var(--text-accent); margin-bottom: 0.25rem;">Order #{{ $ticket->order->order_number }}</div>
                            <div style="color: var(--text-muted); margin-bottom: 0.15rem;">Total: <strong>₱{{ number_format($ticket->order->total_price, 2) }}</strong></div>
                            <div style="color: var(--text-muted); margin-bottom: 0.65rem;">
                                Status: <span style="background: var(--bg-subtle); padding: 1px 5px; border-radius: 4px; font-weight: 600; font-size: 0.82rem;">{{ ucwords(str_replace('_', ' ', $ticket->order->status)) }}</span>
                            </div>
                            <a href="{{ route('customer.orders.show', $ticket->order->id) }}" class="btn btn-outline" style="width: 100%; justify-content: center; font-size: 0.82rem; padding: 0.45rem;">
                                <i class="fa-solid fa-eye"></i> View Order
                            </a>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </main>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatScroll = document.getElementById('chat-scroll');
    if (chatScroll) setTimeout(() => chatScroll.scrollTop = chatScroll.scrollHeight, 100);

    const fileInput = document.getElementById('reply-files');
    const previewGrid = document.getElementById('preview-grid');
    if (!fileInput || !previewGrid) return;

    let dt = new DataTransfer();

    function fmtSize(b) {
        if (!b) return '0B';
        const s = ['B','KB','MB']; const i = Math.floor(Math.log(b)/Math.log(1024));
        return parseFloat((b/Math.pow(1024,i)).toFixed(1))+' '+s[i];
    }
    function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function render() {
        previewGrid.innerHTML = '';
        if (!dt.files.length) { previewGrid.style.display='none'; return; }
        previewGrid.style.display = 'flex';
        for (let i=0; i<dt.files.length; i++) {
            const f = dt.files[i];
            const ext = f.name.split('.').pop().toLowerCase();
            let iconHtml = '<i class="fa-solid fa-file" style="color:var(--text-muted);"></i>';
            if (f.type.startsWith('image/') || ['jpg','jpeg','png','gif','webp'].includes(ext)) {
                iconHtml = '<i class="fa-solid fa-image" style="color:var(--text-accent);"></i>';
            } else if (f.type.startsWith('video/') || ['mp4','mov','avi','webm'].includes(ext)) {
                iconHtml = '<i class="fa-solid fa-video" style="color:var(--text-violet);"></i>';
            }
            const p = document.createElement('div'); p.className='preview-pill';
            p.innerHTML=`${iconHtml}<span>${esc(f.name)} (${fmtSize(f.size)})</span><button type="button" class="pill-remove" data-i="${i}"><i class="fa-solid fa-xmark"></i></button>`;
            previewGrid.appendChild(p);
        }
        previewGrid.querySelectorAll('.pill-remove').forEach(b => b.addEventListener('click', e => {
            e.preventDefault(); e.stopPropagation();
            const idx = parseInt(b.getAttribute('data-i'),10);
            const nd = new DataTransfer();
            for(let j=0;j<dt.files.length;j++) if(j!==idx) nd.items.add(dt.files[j]);
            dt=nd; fileInput.files=dt.files; render();
        }));
    }

    function addFiles(files) {
        for(const f of files) {
            if(f.size > 50*1024*1024){ alert(`"${f.name}" exceeds 50MB.`); continue; }
            dt.items.add(f);
        }
        fileInput.files=dt.files; render();
    }

    fileInput.addEventListener('change', function() { if(this.files.length) addFiles(Array.from(this.files)); });
});
</script>
@endpush
@endsection
