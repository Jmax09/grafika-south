@extends('layouts.admin')

@section('title', 'Hero Slider / Banner Carousel Management - Super Admin')

@push('styles')
<style>
    .banner-card {
        display: flex;
        gap: 1.25rem;
        align-items: center;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s ease;
    }
    .banner-card:hover {
        border-color: rgba(0, 153, 255, 0.4);
        box-shadow: 0 4px 12px -4px rgba(0, 0, 0, 0.06);
    }
    .banner-card.inactive {
        opacity: 0.55;
        background: var(--bg-page);
    }
    .banner-thumb {
        width: 220px;
        height: 120px;
        flex-shrink: 0;
        border-radius: 8px;
        overflow: hidden;
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
    }
    .banner-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .banner-info {
        flex: 1;
        min-width: 0;
    }
    .banner-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-active { background: var(--success-soft); color: var(--text-success); }
    .status-inactive { background: var(--bg-subtle); color: var(--text-muted); }
    .banner-actions {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
    }
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal-backdrop.active { display: flex; }
    .modal-panel {
        background: var(--bg-surface);
        border-radius: 16px;
        max-width: 560px;
        width: 100%;
        padding: 1.75rem;
        max-height: 90vh;
        overflow-y: auto;
    }
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        color: var(--text-muted);
    }
    .banner-card.sortable-ghost {
        opacity: 0.35;
        background: var(--accent-soft) !important;
        border: 2px dashed var(--accent-color) !important;
        transform: scale(0.99);
    }
    .banner-card.sortable-chosen {
        background: var(--bg-page) !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 0 0 2px var(--accent-color) !important;
    }
    .banner-card.sortable-drag {
        opacity: 0.95;
        cursor: grabbing !important;
    }
    .drag-col {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-right: 0.5rem;
        flex-shrink: 0;
    }
    .drag-handle {
        cursor: grab;
        color: var(--text-subtle);
        padding: 0.6rem 0.5rem;
        font-size: 1.35rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .drag-handle:hover {
        color: var(--text-accent);
        background: var(--accent-soft);
    }
    .drag-handle:active {
        cursor: grabbing;
    }
    .save-status-toast {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.85rem;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease, transform 0.25s ease;
        transform: translateY(-4px);
    }
    .save-status-toast.show {
        opacity: 1;
        transform: translateY(0);
    }
    .save-status-toast.success {
        background: var(--success-soft);
        color: var(--text-success);
        border: 1px solid var(--success-border);
    }
    .save-status-toast.loading {
        background: var(--accent-soft);
        color: var(--text-accent);
        border: 1px solid var(--info-border);
    }
    .save-status-toast.error {
        background: var(--danger-soft);
        color: var(--text-danger);
        border: 1px solid var(--danger-border);
    }

        /* Banner image upload dropzone */
        .banner-drop{display:flex;align-items:center;justify-content:center;min-height:150px;padding:1.25rem;border:2px dashed var(--border-color);border-radius:12px;background:var(--bg-page);cursor:pointer;text-align:center;transition:border-color .15s ease,background .15s ease;}
        .banner-drop:hover,.banner-drop:focus{border-color:var(--cmyk-cyan);background:var(--accent-soft);outline:none;}
        .banner-drop.is-drag{border-color:var(--cmyk-cyan);background:var(--accent-soft);}
        .banner-drop.is-error{border-color:var(--danger-color);background:var(--danger-soft);}
        .banner-drop__idle{display:flex;flex-direction:column;align-items:center;gap:.4rem;}
        .banner-drop__idle i{font-size:1.9rem;color:var(--brand-cyan-fg);}
        .banner-drop__title{font-size:.92rem;font-weight:700;color:var(--text-main);}
        .banner-drop__title span{font-weight:500;color:var(--text-muted);}
        .banner-drop__hint{font-size:.76rem;color:var(--text-muted);}
        .banner-drop__preview{display:flex;flex-direction:column;align-items:center;gap:.5rem;width:100%;}
        .banner-drop__preview img{max-width:100%;max-height:180px;border-radius:8px;object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,.12);}
        .banner-drop__change{font-size:.8rem;font-weight:600;color:var(--brand-cyan-fg);}
        /* Styled "choose file" button inside raw file inputs (Delete/legacy spots) */
        input[type="file"]::file-selector-button{padding:.45rem .9rem;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-page);color:var(--text-muted);font-weight:600;font-size:.8rem;cursor:pointer;margin-right:.75rem;transition:all .15s ease;}
        input[type="file"]::file-selector-button:hover{background:var(--accent-soft);border-color:var(--accent-color);color:var(--text-accent);}
</style>
@endpush

@section('content')
<!-- Quick Stats -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card" style="padding: 1.25rem; margin: 0;">
        <div style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600; margin-bottom: 0.35rem;">Total Banners</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--text-main); font-family: 'Outfit', sans-serif;">{{ $banners->count() }}</div>
    </div>
    <div class="card" style="padding: 1.25rem; margin: 0;">
        <div style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600; margin-bottom: 0.35rem;">Active (Visible)</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--text-success); font-family: 'Outfit', sans-serif;">{{ $banners->where('is_active', true)->count() }}</div>
    </div>
    <div class="card" style="padding: 1.25rem; margin: 0;">
        <div style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600; margin-bottom: 0.35rem;">Inactive</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--text-muted); font-family: 'Outfit', sans-serif;">{{ $banners->where('is_active', false)->count() }}</div>
    </div>
</div>

<!-- Banners List -->
<div class="card" style="padding: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin: 0;">
                <i class="fa-solid fa-images" style="color: var(--brand-cyan-fg); margin-right: 0.4rem;"></i> Manage Banners
            </h3>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0.25rem 0 0;">
                Drag the handle <i class="fa-solid fa-grip-vertical" style="color: var(--text-subtle);"></i> to reorder. Changes save automatically.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div id="saveStatusToast" class="save-status-toast"></div>
            <button onclick="openAddModal()" class="btn btn-primary" style="font-size: 0.9rem;">
                <i class="fa-solid fa-plus"></i> Upload New Banner
            </button>
        </div>
    </div>

    @if($banners->count() === 0)
        <div class="empty-state">
            <i class="fa-solid fa-image" style="font-size: 3rem; color: var(--text-disabled); margin-bottom: 1rem;"></i>
            <p style="font-size: 1rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">No banners yet</p>
            <p style="font-size: 0.88rem;">Click "Upload New Banner" above to add promotional slider images for the homepage hero section.</p>
        </div>
    @else
        <div id="bannersList">
            @foreach($banners as $index => $banner)
                <div class="banner-card {{ $banner->is_active ? '' : 'inactive' }}" data-id="{{ $banner->id }}" id="banner-card-{{ $banner->id }}">
                    <div class="drag-col">
                        <span class="drag-handle" title="Drag to reorder"><i class="fa-solid fa-grip-vertical"></i></span>
                    </div>

                    <div class="banner-thumb">
                        <img src="{{ asset('storage/' . $banner->image_path) }}" alt="{{ $banner->title ?: 'Banner ' . $banner->id }}">
                    </div>

                    <div class="banner-info">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.4rem; flex-wrap: wrap;">
                            <span class="banner-status-pill {{ $banner->is_active ? 'status-active' : 'status-inactive' }}">
                                <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i>
                                {{ $banner->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <strong style="font-size: 1rem; color: var(--text-main);">
                                {{ $banner->title ?: '(No title)' }}
                            </strong>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                            <span style="font-size: 0.78rem; color: var(--text-muted);">
                                <i class="fa-regular fa-clock"></i> Updated: {{ $banner->updated_at->format('M d, Y g:i A') }}
                            </span>
                        </div>
                    </div>

                    <div class="banner-actions">
                        <form action="{{ route('super-admin.hero.toggle', $banner->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn {{ $banner->is_active ? 'btn-outline' : 'btn-primary' }}" style="padding: 0.5rem 0.75rem; font-size: 0.82rem;"
                                title="{{ $banner->is_active ? 'Deactivate banner' : 'Activate banner' }}">
                                <i class="fa-solid {{ $banner->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                            </button>
                        </form>
                        <button onclick="openEditModal({{ $banner->id }}, '{{ addslashes($banner->title) }}', '{{ addslashes($banner->link_url) }}', {{ $banner->sort_order }}, {{ $banner->is_active ? 'true' : 'false' }})"
                            class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.82rem;" title="Edit banner">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <form action="{{ route('super-admin.hero.destroy', $banner->id) }}" method="POST" id="delete-form-{{ $banner->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                onclick="openDeleteModal({{ $banner->id }}, '{{ addslashes($banner->title ?: 'Banner #' . $banner->id) }}')"
                                class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.82rem; background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border);" title="Delete banner">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Add Banner Modal -->
<div class="modal-backdrop" id="addModal" onclick="if(event.target===this)closeAddModal()">
    <div class="modal-panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.85rem;">
            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin: 0;">
                <i class="fa-solid fa-cloud-arrow-up" style="color: var(--brand-cyan-fg);"></i> Upload New Banner
            </h3>
            <button onclick="closeAddModal()" style="background: none; border: none; font-size: 1.3rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form id="addBannerForm" action="{{ route('super-admin.hero.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.88rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">
                    Banner Image <span style="color: var(--text-danger);">*</span>
                </label>
                <div class="banner-drop" id="addBannerDrop" tabindex="0" role="button" aria-label="Upload banner image"
                     onclick="document.getElementById('addBannerFile').click()"
                     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();document.getElementById('addBannerFile').click();}">
                    <input type="file" name="image" id="addBannerFile" accept="image/*" style="display:none;">
                    <div class="banner-drop__idle" id="addBannerIdle">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span class="banner-drop__title">Click to upload <span>or drag &amp; drop</span></span>
                        <span class="banner-drop__hint">PNG, JPG or WEBP &middot; up to 5MB</span>
                    </div>
                    <div class="banner-drop__preview" id="addBannerPreview" style="display:none;">
                        <img id="addBannerImg" alt="Banner preview">
                        <span class="banner-drop__change"><i class="fa-solid fa-camera"></i> Click to change image</span>
                    </div>
                </div>
                <small style="color: var(--text-muted); font-size: 0.78rem;">Recommended: Wide landscape image (1920x600px or wider, max 5MB).</small>
                <span id="addBannerErr" style="display:none; color:var(--text-danger); font-size:0.78rem; font-weight:600; margin-top:0.35rem;"><i class="fa-solid fa-circle-exclamation"></i> Please choose an image to upload.</span>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.88rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">Banner Title</label>
                <input type="text" name="title" value="{{ old('title') }}" maxlength="255"
                    style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;"
                    placeholder="e.g. Summer T-Shirt Promo - 20% Off">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.88rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">Click Link URL</label>
                <input type="text" name="link_url" value="{{ old('link_url') }}" maxlength="255"
                    style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;"
                    placeholder="e.g. /products or https://example.com">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.88rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">Status</label>
                <select name="is_active" style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; background: var(--bg-surface);">
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active (Visible)</option>
                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive (Hidden)</option>
                </select>
                <input type="hidden" name="sort_order" value="{{ old('sort_order', $nextOrder ?? 1) }}">
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeAddModal()" class="btn btn-outline" style="padding: 0.7rem 1.5rem;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.7rem 1.75rem;">
                    <i class="fa-solid fa-upload"></i> Upload Banner
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Banner Modal -->
<div class="modal-backdrop" id="editModal" onclick="if(event.target===this)closeEditModal()">
    <div class="modal-panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.85rem;">
            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin: 0;">
                <i class="fa-solid fa-pen-to-square" style="color: var(--text-accent);"></i> Edit Banner
            </h3>
            <button onclick="closeEditModal()" style="background: none; border: none; font-size: 1.3rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.88rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">
                    Replace Image
                </label>
                <div class="banner-drop" id="editBannerDrop" tabindex="0" role="button" aria-label="Replace banner image"
                     onclick="document.getElementById('editBannerFile').click()"
                     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();document.getElementById('editBannerFile').click();}">
                    <input type="file" name="image" id="editBannerFile" accept="image/*" style="display:none;">
                    <div class="banner-drop__idle" id="editBannerIdle">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span class="banner-drop__title">Click to upload <span>or drag &amp; drop</span></span>
                        <span class="banner-drop__hint">Leave empty to keep current image &middot; max 5MB</span>
                    </div>
                    <div class="banner-drop__preview" id="editBannerPreview" style="display:none;">
                        <img id="editBannerImg" alt="New banner preview">
                        <span class="banner-drop__change"><i class="fa-solid fa-camera"></i> Click to change image</span>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.88rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">Banner Title</label>
                <input type="text" name="title" id="edit_title" maxlength="255"
                    style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.88rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">Click Link URL</label>
                <input type="text" name="link_url" id="edit_link_url" maxlength="255"
                    style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.88rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">Status</label>
                <select name="is_active" id="edit_is_active" style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; background: var(--bg-surface);">
                    <option value="1">Active (Visible)</option>
                    <option value="0">Inactive (Hidden)</option>
                </select>
                <input type="hidden" name="sort_order" id="edit_sort_order">
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeEditModal()" class="btn btn-outline" style="padding: 0.7rem 1.5rem;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.7rem 1.75rem;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-backdrop" id="deleteModal" onclick="if(event.target===this)closeDeleteModal()">
    <div class="modal-panel" style="max-width: 380px; text-align: center; padding: 1.85rem 1.75rem; border-radius: 16px;">

        <!-- Icon -->
        <div style="width: 56px; height: 56px; background: var(--danger-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem; border: 2px solid var(--danger-border);">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.5rem; color: var(--text-danger);"></i>
        </div>

        <!-- Heading -->
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.4rem;">Delete Banner?</h3>
        <p style="font-size: 0.92rem; color: var(--text-muted); margin: 0 0 1.5rem; line-height: 1.45;">
            Are you sure you want to delete this banner?
        </p>

        <!-- Buttons -->
        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button type="button" onclick="closeDeleteModal()"
                style="flex: 1; padding: 0.68rem 1.25rem; background: var(--bg-surface); color: var(--text-muted); border: 1.5px solid var(--border-color); border-radius: 10px; font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: all 0.15s;"
                onmouseover="this.style.borderColor='#94A3B8'; this.style.color='#0F172A';"
                onmouseout="this.style.borderColor='#CBD5E1'; this.style.color='#475569';">
                <i class="fa-solid fa-xmark"></i> Cancel
            </button>
            <button type="button" id="deleteModalConfirmBtn" onclick="confirmDelete()"
                style="flex: 1; padding: 0.68rem 1.25rem; background: var(--danger-color); color: var(--text-inverse); border: 1.5px solid var(--danger-color); border-radius: 10px; font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);"
                onmouseover="this.style.background='#B91C1C'; this.style.borderColor='#B91C1C';"
                onmouseout="this.style.background='#DC2626'; this.style.borderColor='#DC2626';">
                <i class="fa-solid fa-trash-can"></i> Yes, Delete
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    const REORDER_URL = "{{ route('super-admin.hero.reorder') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
    let toastTimeout = null;

    function showToast(type, html) {
        const toast = document.getElementById('saveStatusToast');
        if (!toast) return;
        clearTimeout(toastTimeout);
        toast.className = `save-status-toast ${type} show`;
        toast.innerHTML = html;
        if (type === 'success' || type === 'error') {
            toastTimeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 2500);
        }
    }

    function saveOrderAjax() {
        const listEl = document.getElementById('bannersList');
        if (!listEl) return;
        const cards = Array.from(listEl.querySelectorAll('.banner-card'));
        const ids = cards.map(c => parseInt(c.dataset.id, 10));
        if (ids.length === 0) return;

        showToast('loading', '<i class="fa-solid fa-spinner fa-spin"></i> Saving order...');

        fetch(REORDER_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('success', '<i class="fa-solid fa-check"></i> Display order saved!');
            } else {
                showToast('error', '<i class="fa-solid fa-triangle-exclamation"></i> Error saving order');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('error', '<i class="fa-solid fa-triangle-exclamation"></i> Failed to save');
        });
    }

    // Modal helpers
    function openAddModal() {
        document.getElementById('addModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.remove('active');
        document.body.style.overflow = '';
    }
    function openEditModal(id, title, linkUrl, sortOrder, isActive) {
        const form = document.getElementById('editForm');
        form.action = `/super-admin/hero/${id}`;
        document.getElementById('edit_title').value = title || '';
        document.getElementById('edit_link_url').value = linkUrl || '';
        const sortOrderInput = document.getElementById('edit_sort_order');
        if (sortOrderInput) sortOrderInput.value = sortOrder || 0;
        document.getElementById('edit_is_active').value = isActive ? '1' : '0';
        resetEditDrop();
        document.getElementById('editModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
        document.body.style.overflow = '';
    }
    function resetEditDrop() {
        var file = document.getElementById('editBannerFile');
        if (file) file.value = '';
        var drop = document.getElementById('editBannerDrop');
        if (drop) drop.classList.remove('is-error');
        var idle = document.getElementById('editBannerIdle');
        var prev = document.getElementById('editBannerPreview');
        if (idle) idle.style.display = 'flex';
        if (prev) { prev.style.display = 'none'; document.getElementById('editBannerImg').removeAttribute('src'); }
    }

    // Delete confirmation modal
    let _deleteTargetFormId = null;

    function openDeleteModal(bannerId, bannerName) {
        _deleteTargetFormId = 'delete-form-' + bannerId;
        const bannerNameEl = document.getElementById('deleteModalBannerName');
        if (bannerNameEl) bannerNameEl.textContent = bannerName;
        document.getElementById('deleteModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        _deleteTargetFormId = null;
        document.getElementById('deleteModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    function confirmDelete() {
        if (_deleteTargetFormId) {
            const btn = document.getElementById('deleteModalConfirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';
            document.getElementById(_deleteTargetFormId).submit();
        }
    }

    // Init Sortable & Drag Drop
    document.addEventListener('DOMContentLoaded', function() {
        const listEl = document.getElementById('bannersList');
        if (!listEl) return;

        // Initialize SortableJS
        if (typeof Sortable !== 'undefined') {
            new Sortable(listEl, {
                handle: '.drag-handle',
                animation: 200,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function() {
                    saveOrderAjax();
                }
            });
        } else {
            // HTML5 Drag and Drop Fallback
            let draggedCard = null;
            const cards = listEl.querySelectorAll('.banner-card');
            cards.forEach(card => {
                const handle = card.querySelector('.drag-handle');
                if (handle) {
                    handle.setAttribute('draggable', 'true');
                    handle.addEventListener('dragstart', (e) => {
                        draggedCard = card;
                        card.classList.add('sortable-chosen');
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', card.dataset.id);
                    });
                    handle.addEventListener('dragend', () => {
                        if (draggedCard) draggedCard.classList.remove('sortable-chosen');
                        draggedCard = null;
                        listEl.querySelectorAll('.banner-card').forEach(c => c.classList.remove('sortable-ghost'));
                        saveOrderAjax();
                    });
                }

                card.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    if (!draggedCard || draggedCard === card) return;
                    const rect = card.getBoundingClientRect();
                    const midY = rect.top + rect.height / 2;
                    if (e.clientY < midY) {
                        listEl.insertBefore(draggedCard, card);
                    } else {
                        listEl.insertBefore(draggedCard, card.nextSibling);
                    }
                });
            });
        }
    });
    // Banner image upload dropzone (Add modal)
    (function () {
        var drop = document.getElementById('addBannerDrop');
        var input = document.getElementById('addBannerFile');
        var idle = document.getElementById('addBannerIdle');
        var prev = document.getElementById('addBannerPreview');
        var img = document.getElementById('addBannerImg');
        if (!drop || !input) return;
        function showPreview(file) {
            if (!file) return;
            img.src = URL.createObjectURL(file);
            idle.style.display = 'none';
            prev.style.display = 'flex';
        }
        input.addEventListener('change', function () { showPreview(input.files[0]); });
        drop.addEventListener('dragover', function (e) { e.preventDefault(); drop.classList.add('is-drag'); });
        drop.addEventListener('dragleave', function (e) { e.preventDefault(); drop.classList.remove('is-drag'); });
        drop.addEventListener('drop', function (e) {
            e.preventDefault();
            drop.classList.remove('is-drag');
            if (e.dataTransfer.files && e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                showPreview(input.files[0]);
            }
        });
        var form = document.getElementById('addBannerForm');
        var err = document.getElementById('addBannerErr');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!input.files || !input.files.length) {
                    e.preventDefault();
                    drop.classList.add('is-error');
                    if (err) err.style.display = 'block';
                }
            });
        }
        input.addEventListener('change', function () {
            drop.classList.remove('is-error');
            if (err) err.style.display = 'none';
        });
    })();

    // Banner image upload dropzone (Edit modal — optional replace)
    (function () {
        var drop = document.getElementById('editBannerDrop');
        var input = document.getElementById('editBannerFile');
        var idle = document.getElementById('editBannerIdle');
        var prev = document.getElementById('editBannerPreview');
        var img = document.getElementById('editBannerImg');
        if (!drop || !input) return;
        function showPreview(file) {
            if (!file) { resetEditDrop(); return; }
            img.src = URL.createObjectURL(file);
            idle.style.display = 'none';
            prev.style.display = 'flex';
        }
        input.addEventListener('change', function () { showPreview(input.files[0]); });
        drop.addEventListener('dragover', function (e) { e.preventDefault(); drop.classList.add('is-drag'); });
        drop.addEventListener('dragleave', function (e) { e.preventDefault(); drop.classList.remove('is-drag'); });
        drop.addEventListener('drop', function (e) {
            e.preventDefault();
            drop.classList.remove('is-drag');
            if (e.dataTransfer.files && e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                showPreview(input.files[0]);
            }
        });
    })();
</script>
@endpush
