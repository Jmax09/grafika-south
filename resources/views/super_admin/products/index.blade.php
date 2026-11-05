@extends('layouts.admin')

@section('title', 'Full Products & Pricing Management - Super Admin')

@push('styles')
<style>
    /* Product cards: catalog-style dense grid of compact flat cards */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
        gap: 0.75rem;
        margin-bottom: 2rem;
    }
    @media (max-width: 640px) { .product-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 0.5rem; } }
</style>
@endpush

@section('content')
<!-- CREATE NEW PRODUCT MODAL -->
<div id="add-product-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:9999; backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:1.5rem 1rem; box-sizing:border-box; overflow-y:auto;">
    <div style="background:var(--bg-surface); border-radius:16px; width:100%; max-width:560px; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border:1px solid var(--border-color); margin:auto;">

        <!-- Header (sticky) -->
        <div style="display:flex; justify-content:space-between; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid var(--border-color); flex-shrink:0;">
            <h4 style="margin:0; font-size:1.1rem; font-weight:800; color:var(--text-main); display:flex; align-items:center; gap:0.5rem;">
                <i class="fa-solid fa-plus-circle" style="color:var(--text-accent);"></i> Create New Print Product
            </h4>
            <button type="button" onclick="document.getElementById('add-product-modal').style.display='none'; resetAddProductModal();" style="background:var(--bg-subtle); border:none; width:32px; height:32px; border-radius:50%; font-size:1rem; cursor:pointer; color:var(--text-muted); display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>

        <!-- Scrollable Body + Footer wrapped in form -->
        <form action="{{ route('super-admin.products.store') }}" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; flex:1; min-height:0; overflow:hidden;">
            @csrf

            <!-- Scrollable content area -->
            <div style="flex:1; overflow-y:auto; padding:1.5rem; display:flex; flex-direction:column; gap:1rem;">

                <!-- Product Name -->
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.35rem;">Product Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Die-Cut Vinyl Stickers"
                        style="width:100%; padding:0.65rem 0.9rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.9rem; outline:none; color:var(--text-main); box-sizing:border-box;"
                        onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor=''">
                </div>

                <!-- Category + Price -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div>
                        <label style="font-size:0.82rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.35rem;">Category *</label>

                        {{-- Hidden input carries the actual submitted value --}}
                        <input type="hidden" name="category" id="category-value-super">

                        {{-- Visible dropdown (not submitted directly) --}}
                        <select id="category-select-super"
                            style="width:100%; padding:0.65rem 0.9rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.9rem; outline:none; background:var(--bg-surface); color:var(--text-main); box-sizing:border-box; cursor:pointer;"
                            onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor=''"
                            onchange="handleCategorySelect(this)">
                            <option value="" disabled selected style="color:var(--text-subtle);">— Select a category —</option>
                            <option value="Apparel">Apparel (T-Shirts)</option>
                            <option value="Identification">Identification (ID Cards)</option>
                            <option value="Marketing">Marketing (Business Cards)</option>
                            <option value="Services">Services (Lamination)</option>
                            <option value="Other">✏️ Other / Custom…</option>
                        </select>

                        {{-- Custom category text input, hidden by default --}}
                        <div id="custom-category-wrapper-super" style="overflow:hidden; max-height:0; opacity:0; transition:max-height 0.3s ease, opacity 0.3s ease, margin-top 0.3s ease; margin-top:0;">
                            <div style="display:flex; align-items:center; gap:0.5rem; margin-top:0.6rem; background:var(--accent-soft); border:1px solid var(--info-border); border-radius:10px; padding:0.5rem 0.75rem;">
                                <i class="fa-solid fa-tag" style="color:var(--text-accent); font-size:0.85rem; flex-shrink:0;"></i>
                                <input type="text" id="custom-category-input-super"
                                    placeholder="e.g. Packaging, Signage…"
                                    maxlength="100"
                                    style="flex:1; border:none; background:transparent; font-size:0.9rem; outline:none; color:var(--text-main);"
                                    oninput="document.getElementById('category-value-super').value = this.value.trim();"
                                    onfocus="this.parentElement.style.borderColor='#0099FF';"
                                    onblur="this.parentElement.style.borderColor='#BAE6FD';">
                            </div>
                            <div style="font-size:0.72rem; color:var(--text-muted); margin-top:0.3rem; padding-left:0.2rem;">
                                This will be saved as the product's category name.
                            </div>
                        </div>
                    </div>
                    <div>
                        <label style="font-size:0.82rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.35rem;">Base Price (₱) *</label>
                        <input type="number" step="0.01" name="price" required placeholder="e.g. 250.00"
                            style="width:100%; padding:0.65rem 0.9rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.9rem; outline:none; color:var(--text-main); box-sizing:border-box;"
                            onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor=''">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.35rem;">Description</label>
                    <textarea name="description" rows="3" placeholder="Brief details about material options, specs, or turnaround time..."
                        style="width:100%; padding:0.65rem 0.9rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.9rem; outline:none; color:var(--text-main); resize:vertical; font-family:inherit; box-sizing:border-box;"
                        onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor=''"></textarea>
                </div>

                <!-- ═══ CUSTOM ITEM MODE ═══ -->
                <div id="add-custom-item-mode-card"
                    style="background:var(--bg-page); border:1.5px solid var(--border-color); border-radius:12px; padding:0.95rem 1.1rem; transition:all 0.25s ease;">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <div id="add-custom-item-icon"
                                style="width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:0.95rem; background:var(--bg-subtle); color:var(--text-subtle); transition:all 0.2s;">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                            </div>
                            <div>
                                <div style="display:flex; align-items:center; gap:0.5rem;">
                                    <span style="font-size:0.88rem; font-weight:800; color:var(--text-main);">Custom Item Mode</span>
                                    <span id="add-prod-time-state" style="font-size:0.65rem; font-weight:800; padding:0.15rem 0.5rem; border-radius:9999px; background:var(--bg-subtle); color:var(--text-subtle); border:1px solid var(--border-color);">OFF</span>
                                </div>
                                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.15rem;">
                                    Enables customer design upload, terms agreement, & production time
                                </div>
                            </div>
                        </div>

                        {{-- Toggle Switch --}}
                        <div style="position:relative; width:48px; height:26px; cursor:pointer; flex-shrink:0;" onclick="toggleAddCustomItemSwitch(this)">
                            <input type="hidden" name="enable_production_time" value="0">
                            <input type="checkbox" name="enable_production_time" value="1" style="position:absolute; opacity:0; width:0; height:0;">
                            <input type="hidden" name="requires_design_file" id="add-requires-design-input" value="0">
                            <input type="hidden" name="requires_custom_terms" id="add-requires-terms-input" value="0">
                            <div class="sw-track" style="position:absolute; inset:0; background:var(--text-disabled); border-radius:9999px; transition:background 0.2s;"></div>
                            <div class="sw-knob" style="position:absolute; top:3px; left:3px; width:20px; height:20px; background:var(--bg-surface); border-radius:50%; box-shadow:0 1px 3px rgba(0,0,0,0.2); transition:transform 0.2s;"></div>
                        </div>
                    </div>

                    {{-- Turnaround Time Input (visible only when ON) --}}
                    <div id="add-custom-item-details" style="display:none; margin-top:0.85rem; padding-top:0.85rem; border-top:1px dashed var(--border-color);">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                            <div>
                                <label for="custom_production_time_minutes_add" style="font-size:0.82rem; font-weight:700; color:var(--text-accent); display:flex; align-items:center; gap:0.35rem;">
                                    <i class="fa-solid fa-stopwatch" style="color:var(--text-accent);"></i> Estimated Production Time
                                </label>
                                <div style="font-size:0.72rem; color:var(--text-muted);">Minimum turnaround time for this custom item</div>
                            </div>
                            <div style="position:relative; width:140px;">
                                <input type="number" id="custom_production_time_minutes_add" name="custom_production_time_minutes" min="0" step="1"
                                    placeholder="e.g. 30"
                                    style="width:100%; padding:0.5rem 2.6rem 0.5rem 0.8rem; border:1.5px solid var(--accent-color); border-radius:8px; font-size:0.88rem; font-weight:700; outline:none; color:var(--text-main); background:var(--bg-surface); box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='#7DD3FC';">
                                <span style="position:absolute; right:9px; top:50%; transform:translateY(-50%); font-size:0.75rem; font-weight:700; color:var(--text-muted);">mins</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Image -->
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.35rem;">Product Image *</label>
                    <div id="product-image-dropzone-super"
                        style="border:2px dashed var(--border-color); border-radius:12px; padding:1.5rem 1rem; text-align:center; background:var(--bg-page); cursor:pointer; transition:all 0.2s;"
                        onclick="document.getElementById('product-image-file-input-super').click();"
                        ondragover="event.preventDefault(); this.style.borderColor='#0099FF'; this.style.background='#E6F4FF';"
                        ondragleave="this.style.borderColor=''; this.style.background='#F8FAFC';"
                        ondrop="event.preventDefault(); this.style.borderColor=''; this.style.background='#F8FAFC'; handleFileDropSuper(event);">

                        <input type="file" name="image" id="product-image-file-input-super" accept="image/*" required style="display:none;" onchange="previewProductImageSuper(this)">

                        <div id="dropzone-prompt-super">
                            <div style="width:48px; height:48px; background:var(--accent-soft); color:var(--text-accent); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 0.6rem; font-size:1.3rem;">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <div style="font-size:0.88rem; font-weight:700; color:var(--text-main);">Click to browse or drag &amp; drop product image *</div>
                            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.2rem;">PNG, JPG, WEBP up to 5MB (Required)</div>
                        </div>

                        <div id="dropzone-preview-super" style="display:none; align-items:center; justify-content:center; gap:1rem;">
                            <img id="preview-img-element-super" src="#" alt="Preview" style="max-height:70px; max-width:100px; border-radius:8px; object-fit:cover; border:1px solid var(--border-color);">
                            <div style="text-align:left;">
                                <div id="preview-filename-super" style="font-size:0.85rem; font-weight:700; color:var(--text-main); max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></div>
                                <div id="preview-filesize-super" style="font-size:0.75rem; color:var(--text-muted); margin-top:0.15rem;"></div>
                                <span style="font-size:0.75rem; color:var(--text-success); font-weight:700; display:inline-flex; align-items:center; gap:0.25rem; margin-top:0.25rem;"><i class="fa-solid fa-circle-check"></i> Image attached</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer (always visible at bottom) -->
            <div style="display:flex; justify-content:flex-end; gap:0.75rem; padding:1rem 1.5rem; border-top:1px solid var(--border-color); flex-shrink:0; background:var(--bg-surface); border-radius:0 0 16px 16px;">
                <button type="button" onclick="document.getElementById('add-product-modal').style.display='none'; resetAddProductModal();" style="background:var(--bg-surface); color:var(--text-muted); border:1px solid var(--border-color); padding:0.65rem 1.4rem; border-radius:10px; font-weight:700; font-size:0.88rem; cursor:pointer;">Cancel</button>
                <button type="submit" style="background:var(--accent-color); color:var(--text-inverse); border:none; padding:0.65rem 1.4rem; border-radius:10px; font-weight:700; font-size:0.88rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.4rem;"
                    onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                    <i class="fa-solid fa-plus-circle"></i> Create &amp; Configure BOM
                </button>
            </div>
        </form>

    </div>
</div>

<script>
/* ── Category select handler ─────────────────────────────────────── */
function handleCategorySelect(select) {
    const hiddenInput  = document.getElementById('category-value-super');
    const wrapper      = document.getElementById('custom-category-wrapper-super');
    const customInput  = document.getElementById('custom-category-input-super');

    if (select.value === 'Other') {
        // Reveal the custom text box
        wrapper.style.maxHeight = '120px';
        wrapper.style.opacity   = '1';
        wrapper.style.marginTop = '0';
        customInput.required    = true;
        hiddenInput.value       = ''; // wait for the user to type
        setTimeout(() => customInput.focus(), 320); // focus after transition
    } else {
        // Hide the custom text box and use the dropdown value directly
        wrapper.style.maxHeight = '0';
        wrapper.style.opacity   = '0';
        wrapper.style.marginTop = '0';
        customInput.required    = false;
        customInput.value       = '';
        hiddenInput.value       = select.value;
    }
}

/* ── Reset category fields when modal is closed / re-opened ─────── */
// ── Custom Item Mode: ONE master switch drives all three flags ──
// (enable_production_time, requires_design_file, requires_custom_terms)
function toggleAddCustomItemSwitch(wrapper) {
    const cb = wrapper.querySelector('input[type=checkbox]');
    applyAddCustomItemMode(!cb.checked);
}

function applyAddCustomItemMode(on) {
    const cb = document.querySelector('#add-product-modal input[name="enable_production_time"][type=checkbox]');
    if (cb) cb.checked = on;
    const hidden = document.querySelector('#add-product-modal input[name="enable_production_time"][type=hidden]');
    if (hidden) hidden.value = on ? '1' : '0';

    const designInput = document.getElementById('add-requires-design-input');
    if (designInput) designInput.value = on ? '1' : '0';
    const termsInput = document.getElementById('add-requires-terms-input');
    if (termsInput) termsInput.value = on ? '1' : '0';

    const wrap = cb ? cb.closest('div[style*="position:relative"]') : null;
    if (wrap) {
        const track = wrap.querySelector('.sw-track');
        const knob = wrap.querySelector('.sw-knob');
        if (track) track.style.background = on ? '#0099FF' : '#CBD5E1';
        if (knob) knob.style.transform = on ? 'translateX(22px)' : 'translateX(0)';
    }

    const state = document.getElementById('add-prod-time-state');
    if (state) {
        state.textContent = on ? 'ON' : 'OFF';
        state.style.background = on ? '#DCFCE7' : '#F1F5F9';
        state.style.color = on ? '#15803D' : '#94A3B8';
        state.style.borderColor = on ? '#BBF7D0' : '#E2E8F0';
    }

    const modeCard = document.getElementById('add-custom-item-mode-card');
    if (modeCard) {
        modeCard.style.background = on ? '#F0F9FF' : '#F8FAFC';
        modeCard.style.borderColor = on ? '#0099FF' : '#E2E8F0';
    }
    const modeIcon = document.getElementById('add-custom-item-icon');
    if (modeIcon) {
        modeIcon.style.background = on ? '#0099FF' : '#F1F5F9';
        modeIcon.style.color = on ? '#FFF' : '#94A3B8';
    }

    const details = document.getElementById('add-custom-item-details');
    if (details) {
        details.style.display = on ? 'block' : 'none';
    }
}

function resetAddProductModal() {
    const select      = document.getElementById('category-select-super');
    const hiddenInput = document.getElementById('category-value-super');
    const wrapper     = document.getElementById('custom-category-wrapper-super');
    const customInput = document.getElementById('custom-category-input-super');
    if (select)      { select.selectedIndex = 0; }
    if (hiddenInput) { hiddenInput.value    = ''; }
    if (wrapper)     { wrapper.style.maxHeight = '0'; wrapper.style.opacity = '0'; }
    if (customInput) { customInput.value = ''; customInput.required = false; }

    applyAddCustomItemMode(false);

    const prodTimeInput = document.getElementById('custom_production_time_minutes_add');
    if (prodTimeInput) { prodTimeInput.value = ''; }
}

function updateAddProductionTimePreview(val) {
    const preview = document.getElementById('add-production-time-preview');
    if (!preview) return;
    const mins = parseInt(val, 10);
    if (!mins || mins <= 0) {
        preview.innerHTML = '';
        return;
    }
    const days = Math.floor(mins / 1440);
    const remainingAfterDays = mins % 1440;
    const hours = Math.floor(remainingAfterDays / 60);
    const remainingMins = remainingAfterDays % 60;

    let parts = [];
    if (days > 0) parts.push(days + (days === 1 ? ' day' : ' days'));
    if (hours > 0) parts.push(hours + (hours === 1 ? ' hr' : ' hrs'));
    if (remainingMins > 0) parts.push(remainingMins + (remainingMins === 1 ? ' min' : ' mins'));

    preview.innerHTML = '<i class="fa-regular fa-clock"></i> ' + (parts.join(' ') || (mins + ' mins'));
}


function toggleDesignSwitch(wrapper) {
    const cb     = wrapper.querySelector('input[type=checkbox]');
    const hidden = wrapper.querySelector('input[type=hidden]');
    const track  = wrapper.querySelector('.sw-track');
    const knob   = wrapper.querySelector('.sw-knob');
    const panel  = document.getElementById('design-file-panel-super');
    const card   = wrapper.closest('div[style*="background:#F8FAFC"]'); // the toggle card

    cb.checked   = !cb.checked;
    hidden.value = cb.checked ? '1' : '0';

    // Animate the switch
    track.style.background    = cb.checked ? '#0099FF' : '#CBD5E1';
    knob.style.transform      = cb.checked ? 'translateX(22px)' : 'translateX(0)';

    // Highlight the toggle card border when active
    if (card) {
        card.style.borderColor  = cb.checked ? '#93C5FD' : '#E2E8F0';
        card.style.background   = cb.checked ? '#F0F9FF' : '#F8FAFC';
    }

    // Reveal / collapse the design file preview panel
    if (panel) {
        if (cb.checked) {
            panel.style.maxHeight    = '260px';
            panel.style.opacity      = '1';
            panel.style.pointerEvents = 'auto';
        } else {
            panel.style.maxHeight    = '0';
            panel.style.opacity      = '0';
            panel.style.pointerEvents = 'none';
        }
    }
}
function previewProductImageSuper(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img-element-super').src = e.target.result;
            document.getElementById('preview-filename-super').textContent = file.name;
            document.getElementById('preview-filesize-super').textContent = (file.size / (1024*1024)).toFixed(2) + ' MB';
            document.getElementById('dropzone-prompt-super').style.display = 'none';
            document.getElementById('dropzone-preview-super').style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }
}
function handleFileDropSuper(e) {
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        const fi = document.getElementById('product-image-file-input-super');
        fi.files = e.dataTransfer.files;
        previewProductImageSuper(fi);
    }
}

let formToDelete = null;

function showDeleteModal(e, form, productName) {
    e.preventDefault();
    formToDelete = form;
    document.getElementById('delete-confirm-text').innerHTML = `Are you sure you want to delete <strong>${productName}</strong> and its BOM settings? This action cannot be undone.`;
    document.getElementById('delete-confirm-modal').style.display = 'flex';
    return false;
}

function closeDeleteModal() {
    document.getElementById('delete-confirm-modal').style.display = 'none';
    formToDelete = null;
}

document.addEventListener('DOMContentLoaded', () => {
    const confirmBtn = document.getElementById('confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (formToDelete) {
                formToDelete.submit();
            }
        });
    }
});
</script>

<style>
    .product-grid-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        position: relative;
        cursor: pointer;
    }
    .product-grid-card:hover, .product-grid-card:focus-within {
        transform: translateY(-2px);
        border-color: var(--border-color);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.10);
    }
    /* Catalog behaviour: image zooms and the title takes the link blue on hover */
    .product-grid-card .pg-img { transition: transform 0.3s ease; }
    .product-grid-card:hover .pg-img { transform: scale(1.05); }
    .product-grid-card:hover .pg-title, .product-grid-card:focus-within .pg-title { color: var(--text-accent); }
</style>

<!-- UNIFIED STICKY PRODUCTS BAR (Header + Filters on Scroll, styled like Orders) -->
<div class="card filter-card-sticky" style="padding: 0.8rem 1rem; margin-bottom: 1rem; position: sticky; top: 60px; z-index: 40; background: var(--bg-surface); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border-bottom: 2px solid var(--cmyk-cyan);">

        <!-- Top Row: Title + Add New Product Button -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; flex-wrap: wrap; gap: 0.6rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; background: var(--accent-soft); color: var(--text-accent); font-size: 0.8rem;">
                    <i class="fa-solid fa-layer-group"></i>
                </span>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <h2 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: 'Outfit', sans-serif; line-height: 1.2;">
                        Products
                    </h2>
                    <span style="background: var(--bg-subtle); color: var(--text-muted); font-size: 0.72rem; font-weight: 800; padding: 0.12rem 0.45rem; border-radius: 9999px; border: 1px solid var(--border-color);">
                        {{ $products->count() }}
                    </span>
                </div>
            </div>
            <form action="{{ route('super-admin.products.quick-create') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn" style="background: var(--accent-color); color: var(--text-inverse); padding: 0.42rem 0.9rem; font-size: 0.82rem; border-radius: 9px; font-weight: 700; border: none; display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; transition: all 0.15s ease;" onmouseover="this.style.background='#0080FF';" onmouseout="this.style.background='#0099FF';">
                    <i class="fa-solid fa-plus-circle"></i> Add New Product
                </button>
            </form>
        </div>

        <!-- Filter Controls Form -->
        <form action="{{ route('super-admin.products.index') }}" method="GET" style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;" id="product-filter-form">

            <!-- Search Input + Button -->
            <div style="flex: 2; min-width: 200px; display: flex; align-items: center;">
                <input type="text" name="search" id="product-search-input" value="{{ request('search') }}"
                    placeholder="Search by product name..."
                    style="flex: 1; padding: 0.45rem 0.75rem; border: 1px solid {{ request('search') ? 'var(--accent-color)' : 'var(--border-color)' }}; border-left: {{ request('search') ? '3px solid var(--accent-color)' : '1px solid var(--border-color)' }}; border-right: none; border-radius: 8px 0 0 8px; font-size: 0.85rem; outline: none; background: {{ request('search') ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; color: var(--text-main); transition: all 0.2s;"
                    onfocus="this.style.borderColor='#0099FF'; this.style.borderLeft='3px solid #0099FF'; this.style.boxShadow='0 0 0 3px rgba(0, 153, 255, 0.18)';"
                    onblur="this.style.borderColor='{{ request('search') ? '#0099FF' : '#CBD5E1' }}'; this.style.borderLeft='{{ request('search') ? '3px solid #0099FF' : '1px solid #CBD5E1' }}'; this.style.boxShadow='none';">
                <button type="submit"
                    style="padding: 0.45rem 0.85rem; background: var(--accent-color); color: var(--text-inverse); border: 1px solid var(--accent-color); border-radius: 0 8px 8px 0; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; white-space: nowrap; transition: background 0.15s;"
                    onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
            </div>

            <!-- Category Dropdown -->
            <div style="flex: 1; min-width: 150px;">
                <select name="category" id="product-category-select" class="custom-combo"
                    style="width: 100%; padding: 0.45rem 0.75rem; border: 1px solid {{ request('category') ? 'var(--accent-color)' : 'var(--border-color)' }}; border-left: {{ request('category') ? '3px solid var(--accent-color)' : '1px solid var(--border-color)' }}; border-radius: 8px; font-size: 0.85rem; outline: none; background: {{ request('category') ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; color: {{ request('category') ? 'var(--text-accent)' : 'var(--text-main)' }}; {{ request('category') ? 'font-weight: 700;' : '' }} cursor: pointer; transition: all 0.2s;"
                    onchange="this.form.submit()"
                    onfocus="this.style.borderColor='#0099FF'; this.style.borderLeft='3px solid #0099FF'; this.style.boxShadow='0 0 0 3px rgba(0, 153, 255, 0.18)';"
                    onblur="this.style.borderColor='{{ request('category') ? '#0099FF' : '#CBD5E1' }}'; this.style.borderLeft='{{ request('category') ? '3px solid #0099FF' : '1px solid #CBD5E1' }}'; this.style.boxShadow='none';">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Dropdown -->
            <div style="flex: 1; min-width: 155px;">
                <select name="status" id="product-status-select" class="custom-combo"
                    style="width: 100%; padding: 0.45rem 0.75rem; border: 1px solid {{ request('status') ? 'var(--accent-color)' : 'var(--border-color)' }}; border-left: {{ request('status') ? '3px solid var(--accent-color)' : '1px solid var(--border-color)' }}; border-radius: 8px; font-size: 0.85rem; outline: none; background: {{ request('status') ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; color: {{ request('status') ? 'var(--text-accent)' : 'var(--text-main)' }}; {{ request('status') ? 'font-weight: 700;' : '' }} cursor: pointer; transition: all 0.2s;"
                    onchange="this.form.submit()"
                    onfocus="this.style.borderColor='#0099FF'; this.style.borderLeft='3px solid #0099FF'; this.style.boxShadow='0 0 0 3px rgba(0, 153, 255, 0.18)';"
                    onblur="this.style.borderColor='{{ request('status') ? '#0099FF' : '#CBD5E1' }}'; this.style.borderLeft='{{ request('status') ? '3px solid #0099FF' : '1px solid #CBD5E1' }}'; this.style.boxShadow='none';">
                    <option value="">All Product Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Products Only</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Products Only</option>
                </select>
            </div>

            <!-- Reset button (only when filters active) -->
            @if(request()->hasAny(['search', 'category', 'status']))
                <a href="{{ route('super-admin.products.index') }}"
                    style="padding: 0.45rem 0.85rem; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.82rem; font-weight: 700; color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; white-space: nowrap; transition: all 0.15s;"
                    onmouseover="this.style.borderColor='var(--border-strong)'; this.style.color='var(--text-main)';" onmouseout="this.style.borderColor=''; this.style.color='var(--text-muted)';">
                    <i class="fa-solid fa-rotate-left" style="color: var(--text-muted);"></i> Reset
                </a>
            @endif

        </form>
</div>

<!-- MANAGED PRODUCTS GRID -->
@if($products->isEmpty())
    <div class="card" style="padding: 3.5rem 1.5rem; margin-bottom: 2rem; border-radius: 16px; box-shadow: 0 4px 14px rgba(0,0,0,0.04); border: 1px solid var(--border-color); text-align: center; background: var(--bg-surface);">
        <i class="fa-solid fa-box-open" style="font-size: 2.75rem; margin-bottom: 0.85rem; display: block; color: var(--text-disabled);"></i>
        <div style="font-size: 1.05rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem;">No products found</div>
        <p style="font-size: 0.86rem; color: var(--text-subtle); margin: 0;">Try adjusting your search terms or filter criteria.</p>
    </div>
@else
    <div class="product-grid">
        @foreach($products as $prod)
        @php
            $effectivePrice = (float)($prod->price ?: $prod->base_price);
            $hasOptions     = $prod->options->isNotEmpty();
            $hasBom         = $prod->materials->isNotEmpty();
            $hasPrice       = $effectivePrice > 0;
            $isReady        = $hasOptions && $hasBom && $hasPrice;
        @endphp
        <div class="product-grid-card {{ !$prod->is_active ? 'is-draft' : '' }}" onclick="window.location='{{ route('super-admin.products.edit', $prod->id) }}'" title="Open product">

            {{-- Product Image (square, catalog-style) --}}
            <div style="width: 100%; aspect-ratio: 1 / 1; background: var(--bg-page); position: relative; overflow: hidden; flex-shrink: 0;">
                @if($prod->image_path)
                    <img src="{{ asset('storage/' . $prod->image_path) }}" alt="{{ $prod->name }}" class="pg-img"
                        style="width: 100%; height: 100%; object-fit: cover; display: block;">
                @else
                    <div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem; background: var(--accent-soft);">
                        <i class="fa-solid fa-print" style="font-size: 1.6rem; color: var(--text-accent);"></i>
                        <span style="font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-subtle);">Grafika South Print</span>
                    </div>
                @endif

                {{-- Category chip top-left (matches the catalog: white chip, blue text) --}}
                @if($prod->category)
                    <span style="position: absolute; top: 0.4rem; left: 0.4rem; padding: 0.12rem 0.4rem; background: var(--bg-surface); color: var(--text-accent); border: 1px solid rgba(0, 153, 255, 0.2); border-radius: 4px; font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; max-width: calc(100% - 5rem); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $prod->category }}
                    </span>
                @endif

                {{-- Status chip top-right --}}
                <span style="position: absolute; top: 0.4rem; right: 0.4rem; padding: 0.12rem 0.4rem; background: {{ $prod->is_active ? 'var(--success-color)' : 'var(--text-muted)' }}; color: var(--text-inverse); border-radius: 4px; font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em;">
                    {{ $prod->is_active ? 'Active' : 'Draft' }}
                </span>

                {{-- Featured marker (same slot as the catalog's out-of-stock chip) --}}
                @if($prod->is_featured)
                    <span style="position: absolute; bottom: 0.4rem; left: 0.4rem; padding: 0.12rem 0.4rem; background: var(--warning-color); color: var(--text-inverse); border-radius: 4px; font-size: 0.62rem; font-weight: 700;">
                        <i class="fa-solid fa-star" style="font-size: 0.56rem;"></i> Featured
                    </span>
                @endif
            </div>

            {{-- Card body --}}
            <div style="padding: 0.6rem 0.7rem 0.7rem; display: flex; flex-direction: column; flex: 1; gap: 0.35rem;">
                {{-- Product Title (2-line clamp like the catalog) --}}
                <h4 class="pg-title" title="{{ $prod->name }}" style="font-size: 0.82rem; font-weight: 500; color: var(--text-main); margin: 0; line-height: 1.35; min-height: 2.2em; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; transition: color 0.2s;">
                    {{ $prod->name }}
                </h4>

                {{-- Price --}}
                <div style="font-size: 0.98rem; font-weight: 800; color: {{ $hasPrice ? 'var(--cmyk-cyan-hover)' : 'var(--text-danger)' }}; line-height: 1.2;">
                    ₱{{ number_format($effectivePrice, 2) }}
                </div>

                {{-- Info line (sits where the catalog shows ratings) --}}
                <div style="font-size: 0.68rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.3rem;">
                    <i class="fa-solid fa-boxes-stacked" style="font-size: 0.62rem; color: var(--text-accent);"></i>
                    {{ $prod->materials->count() }} material(s)
                </div>

                {{-- Action Footer: Edit + compact publish icon in ONE row (delete moved inside Edit page) --}}
                <div style="margin-top: auto; padding-top: 0.55rem; display: flex; align-items: center; gap: 0.4rem;">
                    <a href="{{ route('super-admin.products.edit', $prod->id) }}"
                        style="flex: 1; min-width: 0; box-sizing: border-box; background: var(--accent-color); color: var(--text-inverse); padding: 0.4rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.35rem; transition: background 0.15s ease;"
                        onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                        <i class="fa-solid fa-pen-to-square" style="font-size: 0.68rem;"></i> Edit
                    </a>

                    <form action="{{ route('super-admin.products.toggle-publish', $prod->id) }}" method="POST" style="margin: 0; display: flex;" onclick="event.stopPropagation()">
                        @csrf
                        <button type="submit"
                            style="box-sizing: border-box; width: 30px; height: 30px; background: var(--bg-surface); color: {{ $prod->is_active ? 'var(--text-danger)' : 'var(--text-success)' }}; border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s;"
                            title="{{ $prod->is_active ? 'Unpublish (set to Draft)' : 'Publish to catalog' }}"
                            onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='#FFF'">
                            <i class="fa-solid {{ $prod->is_active ? 'fa-eye-slash' : 'fa-eye' }}" style="font-size: 0.72rem;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif


<!-- CUSTOM DELETE CONFIRMATION MODAL -->
<div id="delete-confirm-modal" style="display:none; position:fixed; inset:0; background:rgba(15, 23, 42, 0.6); z-index:9999; align-items:center; justify-content:center; padding:1rem; backdrop-filter:blur(4px);">
    <div style="background:var(--bg-surface); width:100%; max-width:400px; border-radius:12px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); display:flex; flex-direction:column; overflow:hidden; animation: modalFadeIn 0.2s ease-out;">
        <div style="padding:1.5rem; text-align:center;">
            <div style="width:48px; height:48px; background:var(--danger-soft); color:var(--text-danger); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem auto; font-size:1.5rem;">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3 style="font-size:1.15rem; font-weight:800; color:var(--text-main); margin:0 0 0.5rem 0; font-family:'Outfit', sans-serif;">Delete Product?</h3>
            <p style="color:var(--text-muted); font-size:0.88rem; margin:0; line-height:1.45; font-family:'Inter', sans-serif;" id="delete-confirm-text">
                Are you sure you want to delete this product and all of its BOM settings? This action cannot be undone.
            </p>
        </div>
        <div style="padding:1rem 1.25rem; background:var(--bg-page); border-top:1px solid var(--border-color); display:flex; justify-content:flex-end; gap:0.75rem;">
            <button type="button" onclick="closeDeleteModal()" style="background:var(--bg-surface); border:1px solid var(--border-color); color:var(--text-muted); padding:0.55rem 1.2rem; border-radius:8px; font-size:0.85rem; font-weight:700; cursor:pointer; transition:all 0.15s;" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='#FFF'">
                Cancel
            </button>
            <button type="button" id="confirm-delete-btn" style="background:var(--danger-color); color:var(--text-inverse); border:none; padding:0.55rem 1.5rem; border-radius:8px; font-size:0.85rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:0.4rem; transition:background 0.15s;" onmouseover="this.style.background='#B91C1C'" onmouseout="this.style.background='#DC2626'">
                Delete
            </button>
        </div>
    </div>
</div>

<style>
    @@keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

{{-- ─── CUSTOM COMBOBOX FOR FILTER SELECTS (keeps hidden native select; auto-submits GET form on pick) ─── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    function enhanceFilterCombo(select) {
        if (select.dataset.comboDone) return;
        select.dataset.comboDone = '1';

        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        select.style.display = 'none';

        const btn = document.createElement('button');
        btn.type = 'button';
        const label = document.createElement('span');
        label.style.cssText = 'flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-align:left;';
        const caret = document.createElement('i');
        caret.className = 'fa-solid fa-chevron-down';
        caret.style.cssText = 'font-size:0.72rem; transition:transform 0.15s;';
        btn.appendChild(label); btn.appendChild(caret);
        wrapper.appendChild(btn);

        const panel = document.createElement('div');
        panel.className = 'combo-panel';
        panel.style.cssText = 'display:none; position:absolute; top:calc(100% + 4px); left:0; right:0; background:var(--bg-elevated); border:1px solid var(--border-color); border-radius:10px; box-shadow:0 8px 20px rgba(15,23,42,0.12); max-height:260px; overflow-y:auto; z-index:80; padding:0.25rem;';
        wrapper.appendChild(panel);

        function styleBtn() {
            const active = select.value !== '';
            btn.style.cssText = 'width:100%; display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.45rem 0.75rem; border:1px solid ' + (active ? 'var(--accent-color)' : 'var(--border-strong)') + '; border-left:3px solid ' + (active ? 'var(--accent-color)' : 'var(--border-strong)') + '; border-radius:8px; font-size:0.85rem; font-weight:' + (active ? '700' : '500') + '; background:' + (active ? 'var(--accent-soft)' : 'var(--bg-surface)') + '; color:' + (active ? 'var(--accent-hover)' : 'var(--text-main)') + '; cursor:pointer; transition:all 0.2s;';
            caret.style.color = active ? 'var(--accent-hover)' : 'var(--text-muted)';
        }
        function render() {
            const o = select.options[select.selectedIndex];
            label.textContent = o ? o.textContent.trim() : '';
            styleBtn();
            const cur = select.value;
            panel.innerHTML = '';
            Array.from(select.options).forEach(function (opt) {
                const item = document.createElement('div');
                const selected = opt.value === cur;
                item.textContent = opt.textContent.trim();
                item.style.cssText = 'display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.5rem 0.65rem; border-radius:7px; font-size:0.86rem; font-weight:' + (selected ? '700' : '500') + '; cursor:pointer; color:' + (selected ? 'var(--accent-contrast)' : 'var(--text-main)') + '; background:' + (selected ? 'var(--accent-color)' : 'var(--bg-elevated)') + ';';
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
                    close();
                });
                panel.appendChild(item);
            });
        }
        function open() { panel.style.display = 'block'; caret.style.transform = 'rotate(180deg)'; }
        function close() { panel.style.display = 'none'; caret.style.transform = 'rotate(0deg)'; }
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (panel.style.display === 'block') { close(); } else { open(); }
        });
        render();
    }

    document.querySelectorAll('select.custom-combo').forEach(enhanceFilterCombo);

    document.addEventListener('click', function (e) {
        document.querySelectorAll('select.custom-combo').forEach(function (sel) {
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
@endsection
