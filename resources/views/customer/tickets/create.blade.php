@extends('layouts.app')

@section('title', 'Submit Support Ticket - Grafika South')

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

    /* Main Panel Content Area - Full Width Desktop */
    .customer-main-content {
        width: 100% !important;
        max-width: 100% !important;
        flex: 1 1 100% !important;
        min-width: 0;
    }

    .ticket-create-card {
        background: var(--bg-surface);
        border-radius: 14px;
        border: 1px solid var(--border-color);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        padding: 1.75rem 2rem;
    }

    .panel-header-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-main);
        margin-top: 0.35rem;
        margin-bottom: 0.2rem;
    }

    .panel-header-subtitle {
        color: var(--text-muted);
        font-size: 0.88rem;
    }

    .btn-back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: var(--text-inverse);
        background: var(--accent-color);
        text-decoration: none;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.45rem 0.9rem;
        border-radius: 8px;
        transition: all 0.15s ease;
        box-shadow: 0 2px 6px rgba(0, 153, 255, 0.2);
    }

    .btn-back-link:hover {
        background: var(--accent-color);
        transform: translateX(-2px);
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        display: block;
        font-weight: 700;
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-bottom: 0.35rem;
    }

    .required-asterisk {
        color: var(--text-danger);
        font-weight: 700;
        margin-left: 1px;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 0.65rem 0.85rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.88rem;
        color: var(--text-main);
        background: var(--bg-surface);
        outline: none;
        transition: border-color 0.15s ease;
        font-family: inherit;
    }

    .form-control::placeholder {
        color: var(--text-subtle);
        font-size: 0.85rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(0, 153, 255, 0.12);
        background: var(--bg-surface);
    }

    .form-hint {
        color: var(--text-muted);
        font-size: 0.78rem;
        margin-top: 0.3rem;
        display: block;
    }

    .form-error-msg {
        color: var(--text-danger);
        font-size: 0.78rem;
        font-weight: 600;
        margin-top: 0.35rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    /* Clean, Simple Drag & Drop File Upload Zone */
    .file-dropzone {
        position: relative;
        border: 1.5px dashed var(--border-color);
        background: var(--bg-page);
        border-radius: 10px;
        padding: 1.25rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .file-dropzone:hover {
        border-color: var(--accent-color);
        background: var(--accent-soft);
    }

    .file-dropzone.dragover {
        border-color: var(--accent-color);
        background: var(--accent-soft);
    }

    .file-input-hidden {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 5;
    }

    .dropzone-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        pointer-events: none;
    }

    .dropzone-prompt {
        font-size: 0.88rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    .dropzone-sub {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    /* File Preview List */
    .dropzone-preview-wrapper {
        position: relative;
        z-index: 6;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        text-align: left;
        width: 100%;
    }

    .dropzone-preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 0.4rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.8rem;
    }

    .file-count-badge {
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .btn-add-more-hint {
        color: var(--text-accent);
        font-weight: 600;
        font-size: 0.78rem;
    }

    .file-preview-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .file-preview-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 0.3rem 0.55rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-main);
    }

    .btn-remove-file {
        position: relative;
        z-index: 10;
        border: none;
        background: var(--bg-subtle);
        color: var(--text-muted);
        border-radius: 4px;
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.7rem;
        transition: all 0.15s ease;
    }

    .btn-remove-file:hover {
        background: var(--danger-soft);
        color: var(--text-danger);
    }

    /* Form Actions */
    .form-actions-bar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1.5rem;
        border-top: 1px solid var(--border-color);
        padding-top: 1.25rem;
    }

    .btn-cancel {
        padding: 0.55rem 1.2rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .btn-cancel:hover {
        background: var(--bg-page);
        color: var(--text-main);
        border-color: var(--border-strong);
    }

    .btn-submit-ticket {
        padding: 0.55rem 1.35rem;
        border-radius: 8px;
        background: var(--accent-color);
        color: var(--text-inverse);
        font-weight: 700;
        font-size: 0.85rem;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: background 0.15s ease;
        box-shadow: 0 2px 6px rgba(0, 153, 255, 0.25);
    }

    .btn-submit-ticket:hover {
        background: var(--accent-color);
    }
</style>

<div class="customer-dashboard-layout flex flex-col md:flex-row gap-6 items-start">
    {{-- Customer Sidebar --}}
    <x-customer-sidebar />

    {{-- Main Content --}}
    <main class="customer-main-content flex-1 w-full min-w-0">
        <!-- HEADER -->
        <div style="margin-bottom: 1.25rem;">
            <a href="{{ route('customer.tickets.index') }}" class="btn-back-link">
                <i class="fa-solid fa-arrow-left"></i> Back to Support Tickets
            </a>
            <h1 class="panel-header-title">Submit a Support Ticket</h1>
            <p class="panel-header-subtitle">
                Fill out the inquiry details below. Our team will review and respond promptly.
            </p>
        </div>

        <!-- FORM CARD -->
        <div class="ticket-create-card">
            <form action="{{ route('customer.tickets.store') }}" method="POST" enctype="multipart/form-data" id="ticket-form">
                @csrf

                {{-- Ticket Category --}}
                <div class="form-group">
                    <label class="form-label" for="category">
                        Inquiry Category <span class="required-asterisk">*</span>
                    </label>
                    <select name="category" id="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <option value="General Inquiry" {{ old('category') === 'General Inquiry' ? 'selected' : '' }}>General Inquiry / Question</option>
                        <option value="Order Status & Delivery" {{ old('category') === 'Order Status & Delivery' ? 'selected' : '' }}>Order Status &amp; Delivery</option>
                        <option value="Print Quality & Misprint Complaint" {{ old('category') === 'Print Quality & Misprint Complaint' ? 'selected' : '' }}>Print Quality &amp; Misprint Complaint</option>
                        <option value="Payment & Billing Issue" {{ old('category') === 'Payment & Billing Issue' ? 'selected' : '' }}>Payment &amp; Billing Issue</option>
                        <option value="Custom Quotation Request" {{ old('category') === 'Custom Quotation Request' ? 'selected' : '' }}>Custom Quote Request</option>
                    </select>
                    @error('category')
                        <span class="form-error-msg"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Related Order (Optional) --}}
                <div class="form-group">
                    <label class="form-label" for="order_id">
                        Link to Existing Order (Optional)
                    </label>
                    <select name="order_id" id="order_id" class="form-select">
                        <option value="">No Specific Order (General Inquiry)</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}" {{ (string)old('order_id', $selectedOrderId) === (string)$order->id ? 'selected' : '' }}>
                                Order #{{ $order->order_number }} - ₱{{ number_format($order->total_price, 2) }} ({{ ucwords(str_replace('_', ' ', $order->status)) }}) - {{ $order->created_at->format('M d, Y') }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-hint">
                        Select an existing order if your inquiry is regarding a specific purchase, proof file, or delivery update.
                    </small>
                    @error('order_id')
                        <span class="form-error-msg"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Subject --}}
                <div class="form-group">
                    <label class="form-label" for="subject">
                        Subject / Title <span class="required-asterisk">*</span>
                    </label>
                    <input type="text" name="subject" id="subject" class="form-control" value="{{ old('subject') }}" placeholder="Brief summary of your inquiry" required>
                    @error('subject')
                        <span class="form-error-msg"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Description Message --}}
                <div class="form-group">
                    <label class="form-label" for="message">
                        Description / Details <span class="required-asterisk">*</span>
                    </label>
                    <textarea name="message" id="message" rows="5" class="form-control" placeholder="Please provide specific details regarding your inquiry..." required>{{ old('message') }}</textarea>
                    @error('message')
                        <span class="form-error-msg"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Clean Drag & Drop File Upload Zone --}}
                <div class="form-group">
                    <label class="form-label">
                        Attachments / Reference Files (Optional)
                    </label>

                    <div class="file-dropzone" id="file-dropzone">
                        <input type="file" name="attachments[]" id="attachments" class="file-input-hidden" accept="image/*,video/*,.pdf,.doc,.docx,.zip" multiple>
                        
                        <!-- Idle State View -->
                        <div class="dropzone-content" id="dropzone-idle">
                            <span class="dropzone-prompt">
                                <i class="fa-solid fa-cloud-arrow-up" style="color: var(--text-accent); margin-right: 4px;"></i>
                                <strong style="color: var(--text-accent);">Click to browse</strong> or drag &amp; drop files here
                            </span>
                            <span class="dropzone-sub">
                                Upload multiple photos, videos (MP4, WEBM, MOV) or documents &bull; Max 50MB per file
                            </span>
                        </div>

                        <!-- Files Selected Preview List State View -->
                        <div class="dropzone-preview-wrapper" id="dropzone-preview-wrapper" style="display: none;">
                            <div class="dropzone-preview-header">
                                <span class="file-count-badge" id="file-count-badge">
                                    <i class="fa-solid fa-paperclip" style="color: var(--text-accent);"></i>
                                    <span id="file-count-text">0 files selected</span>
                                </span>
                                <span class="btn-add-more-hint">
                                    <i class="fa-solid fa-plus"></i> Add more files
                                </span>
                            </div>
                            <div class="file-preview-grid" id="file-preview-grid">
                                <!-- Dynamic pills via JS -->
                            </div>
                        </div>
                    </div>
                    @error('attachments')
                        <span class="form-error-msg"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                    @error('attachments.*')
                        <span class="form-error-msg"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Form Actions Bar --}}
                <div class="form-actions-bar">
                    <a href="{{ route('customer.tickets.index') }}" class="btn-cancel">
                        Cancel
                    </a>
                    <button type="submit" class="btn-submit-ticket">
                        <i class="fa-solid fa-paper-plane"></i> Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropzone = document.getElementById('file-dropzone');
    const fileInput = document.getElementById('attachments');
    const idleView = document.getElementById('dropzone-idle');
    const previewWrapper = document.getElementById('dropzone-preview-wrapper');
    const previewGrid = document.getElementById('file-preview-grid');
    const fileCountText = document.getElementById('file-count-text');

    let dt = new DataTransfer();

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function renderPreviews() {
        const files = dt.files;
        previewGrid.innerHTML = '';

        if (files.length === 0) {
            idleView.style.display = 'flex';
            previewWrapper.style.display = 'none';
            fileCountText.textContent = '0 files selected';
            return;
        }

        idleView.style.display = 'none';
        previewWrapper.style.display = 'flex';
        fileCountText.textContent = `${files.length} file${files.length > 1 ? 's' : ''} selected`;

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const ext = file.name.split('.').pop().toLowerCase();
            let iconHtml = '<i class="fa-solid fa-file" style="color: var(--text-muted); font-size: 0.8rem;"></i>';

            if (file.type.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) {
                iconHtml = '<i class="fa-solid fa-image" style="color: var(--text-accent); font-size: 0.8rem;"></i>';
            } else if (file.type.startsWith('video/') || ['mp4', 'mov', 'avi', 'webm', 'mkv', 'wmv'].includes(ext)) {
                iconHtml = '<i class="fa-solid fa-video" style="color: var(--text-violet); font-size: 0.8rem;"></i>';
            } else if (ext === 'pdf') {
                iconHtml = '<i class="fa-solid fa-file-pdf" style="color: var(--text-danger); font-size: 0.8rem;"></i>';
            } else if (['doc', 'docx'].includes(ext)) {
                iconHtml = '<i class="fa-solid fa-file-word" style="color: var(--text-accent); font-size: 0.8rem;"></i>';
            } else if (['zip', 'rar'].includes(ext)) {
                iconHtml = '<i class="fa-solid fa-file-zipper" style="color: var(--text-warning); font-size: 0.8rem;"></i>';
            }

            const pill = document.createElement('div');
            pill.className = 'file-preview-pill';
            pill.innerHTML = `
                ${iconHtml}
                <span>${escapeHtml(file.name)} (${formatBytes(file.size)})</span>
                <button type="button" class="btn-remove-file" data-index="${i}" title="Remove file">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            previewGrid.appendChild(pill);
        }

        previewGrid.querySelectorAll('.btn-remove-file').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const idx = parseInt(this.getAttribute('data-index'), 10);
                removeFile(idx);
            });
        });
    }

    function addFiles(newFiles) {
        for (let i = 0; i < newFiles.length; i++) {
            const f = newFiles[i];
            if (f.size > 50 * 1024 * 1024) {
                alert(`File "${f.name}" exceeds 50MB limit and was skipped.`);
                continue;
            }
            dt.items.add(f);
        }
        fileInput.files = dt.files;
        renderPreviews();
    }

    function removeFile(index) {
        const newDt = new DataTransfer();
        for (let i = 0; i < dt.files.length; i++) {
            if (i !== index) newDt.items.add(dt.files[i]);
        }
        dt = newDt;
        fileInput.files = dt.files;
        renderPreviews();
    }

    fileInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            addFiles(Array.from(this.files));
        }
    });

    ['dragenter', 'dragover'].forEach(name => {
        dropzone.addEventListener(name, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(name => {
        dropzone.addEventListener(name, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        });
    });

    dropzone.addEventListener('drop', (e) => {
        const files = e.dataTransfer ? e.dataTransfer.files : null;
        if (files && files.length > 0) {
            addFiles(Array.from(files));
        }
    });
});
</script>
@endpush
@endsection
