@extends('layouts.admin')

@section('title', 'Add New Print Product - Super Admin')

@section('content')
<div style="padding-bottom: 5.5rem;">

    {{-- HEADER --}}
    <div style="margin-bottom: 1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
            <div>
                <h1 style="font-size:1.6rem; font-weight:800; color:var(--text-main); margin:0; display:flex; align-items:center; gap:0.6rem;">
                    <i class="fa-solid fa-plus-circle" style="color:var(--text-accent);"></i>
                    <span style="color:var(--text-accent);">Add New Print Product</span>
                </h1>
                <p style="color:var(--text-muted); font-size:0.88rem; margin-top:0.2rem;">Fill in the details below. After saving, you can configure BOM, options, and pricing.</p>
            </div>
            <a href="{{ route('super-admin.products.index') }}"
               style="display:inline-flex; align-items:center; gap:0.45rem; background:var(--bg-subtle); border:1px solid var(--border-color); color:var(--text-muted); padding:0.55rem 1.1rem; border-radius:9px; font-size:0.85rem; font-weight:700; text-decoration:none;">
                <i class="fa-solid fa-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    {{-- FORM --}}
    <form action="{{ route('super-admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Validation errors --}}
        @if ($errors->any())
        <div style="background:var(--danger-soft); border:1.5px solid var(--danger-border); border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.25rem;">
            <div style="font-weight:700; color:var(--text-danger); margin-bottom:0.4rem;"><i class="fa-solid fa-triangle-exclamation"></i> Please fix the following:</div>
            <ul style="margin:0; padding-left:1.2rem; color:var(--text-danger); font-size:0.88rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; align-items:start;">

            {{-- LEFT COLUMN --}}
            <div style="display:flex; flex-direction:column; gap:1.25rem;">

                {{-- Core Details Card --}}
                <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:14px; padding:1.35rem 1.5rem;">
                    <div style="font-size:0.82rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:1rem;">
                        <i class="fa-solid fa-tag" style="color:var(--text-accent);"></i> Core Details
                    </div>

                    {{-- Product Name --}}
                    <div style="margin-bottom:1rem;">
                        <label style="font-size:0.82rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.35rem;">Product Name *</label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                               placeholder="e.g. Die-Cut Vinyl Stickers"
                               style="width:100%; padding:0.65rem 0.9rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.9rem; outline:none; color:var(--text-main); box-sizing:border-box;"
                               onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor='#CBD5E1'">
                    </div>

                    {{-- Category + Price --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                        <div>
                            <label style="font-size:0.82rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.35rem;">Category *</label>
                            <input type="text" name="category" required value="{{ old('category') }}"
                                   placeholder="e.g. Stickers"
                                   list="category-suggestions"
                                   style="width:100%; padding:0.65rem 0.9rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.9rem; outline:none; color:var(--text-main); box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor='#CBD5E1'">
                            <datalist id="category-suggestions">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label style="font-size:0.82rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.35rem;">Base Price (₱) *</label>
                            <input type="number" name="price" required value="{{ old('price') }}"
                                   step="0.01" min="0" placeholder="e.g. 250.00"
                                   style="width:100%; padding:0.65rem 0.9rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.9rem; outline:none; color:var(--text-main); box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor='#CBD5E1'">
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label style="font-size:0.82rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.35rem;">Description</label>
                        <textarea name="description" rows="4" placeholder="Brief details about material options, specs, or turnaround time..."
                                  style="width:100%; padding:0.65rem 0.9rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.9rem; outline:none; color:var(--text-main); box-sizing:border-box; resize:vertical;"
                                  onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor='#CBD5E1'">{{ old('description') }}</textarea>
                    </div>
                </div>

                {{-- Custom Item Mode Card --}}
                <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:14px; padding:1.35rem 1.5rem;">
                    <div style="font-size:0.82rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:1rem;">
                        <i class="fa-solid fa-wand-magic-sparkles" style="color:var(--text-accent);"></i> Custom Item Mode
                    </div>

                    <div id="custom-mode-card" style="border:1.5px solid var(--border-color); border-radius:12px; padding:1rem; transition:all 0.2s;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <div style="width:36px; height:36px; background:var(--accent-soft); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1rem; color:var(--text-accent); flex-shrink:0;">
                                    <i class="fa-solid fa-sliders"></i>
                                </div>
                                <div>
                                    <div style="font-weight:800; font-size:0.9rem; color:var(--text-main);">Custom Item Mode
                                        <span id="custom-badge" style="font-size:0.7rem; padding:0.15rem 0.55rem; border-radius:9999px; font-weight:800; margin-left:0.4rem; background:var(--bg-subtle); color:var(--text-muted);">MASTER SWITCH</span>
                                    </div>
                                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.1rem;" id="custom-desc">One switch for customized products — turning it ON enables everything a custom item needs:</div>
                                    <div id="custom-features" style="margin-top:0.5rem; display:flex; flex-direction:column; gap:0.2rem;">
                                        <div style="font-size:0.75rem; color:var(--text-subtle);"><i class="fa-regular fa-circle" style="font-size:0.55rem;"></i> Estimated Production Time</div>
                                        <div style="font-size:0.75rem; color:var(--text-subtle);"><i class="fa-regular fa-circle" style="font-size:0.55rem;"></i> Customer Design File Upload</div>
                                        <div style="font-size:0.75rem; color:var(--text-subtle);"><i class="fa-regular fa-circle" style="font-size:0.55rem;"></i> Mandatory Terms Agreement</div>
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex; flex-direction:column; align-items:center; gap:0.25rem; flex-shrink:0;">
                                <span id="custom-state-label" style="font-size:0.7rem; font-weight:800; color:var(--text-subtle);">OFF</span>
                                <label style="position:relative; display:inline-block; width:44px; height:24px; cursor:pointer;">
                                    <input type="checkbox" id="enable_production_time" name="enable_production_time" value="1"
                                           onchange="toggleCustomMode(this.checked)"
                                           {{ old('enable_production_time') ? 'checked' : '' }}
                                           style="opacity:0; width:0; height:0;">
                                    <span id="toggle-track" style="position:absolute; inset:0; border-radius:9999px; background:var(--text-disabled); transition:background 0.2s;">
                                        <span id="toggle-knob" style="position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%; background:var(--bg-surface); box-shadow:0 1px 3px rgba(0,0,0,0.2); transition:transform 0.2s;"></span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        {{-- Expanded details (ON state) --}}
                        <div id="add-custom-item-details" style="display:none; border-top:1px dashed var(--border-color); margin-top:1rem; padding-top:1rem;">
                            {{-- Turnaround Time Input --}}
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
                                <div style="flex:1;">
                                    <label for="custom_production_time_minutes_create" style="font-size:0.82rem; font-weight:700; color:var(--text-main);">
                                        <i class="fa-solid fa-stopwatch" style="color:var(--text-accent);"></i> Estimated Production Time
                                    </label>
                                    <div style="font-size:0.72rem; color:var(--text-muted);">Minimum turnaround time for this custom item</div>
                                </div>
                                <div style="display:flex; align-items:center; gap:0.4rem; flex-shrink:0;">
                                    <input type="number" id="custom_production_time_minutes_create"
                                           name="custom_production_time_minutes"
                                           min="0" placeholder="e.g. 30"
                                           value="{{ old('custom_production_time_minutes') }}"
                                           style="width:80px; padding:0.55rem 0.7rem; border:1px solid var(--border-color); border-radius:8px; font-size:0.9rem; outline:none; text-align:center;"
                                           onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor='#CBD5E1'">
                                    <span style="font-size:0.8rem; color:var(--text-muted); font-weight:600;">mins</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden fields for requires_design_file and requires_custom_terms (driven by master switch) --}}
                    <input type="hidden" name="requires_design_file" id="req_design_file" value="{{ old('enable_production_time') ? '1' : '0' }}">
                    <input type="hidden" name="requires_custom_terms" id="req_custom_terms" value="{{ old('enable_production_time') ? '1' : '0' }}">
                </div>

            </div>

            {{-- RIGHT COLUMN --}}
            <div style="display:flex; flex-direction:column; gap:1.25rem;">

                {{-- Product Image Card --}}
                <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:14px; padding:1.35rem 1.5rem;">
                    <div style="font-size:0.82rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:1rem;">
                        <i class="fa-solid fa-image" style="color:var(--text-accent);"></i> Product Image *
                    </div>

                    <label for="product-image-input" id="image-drop-zone"
                           style="display:block; border:2px dashed var(--border-color); border-radius:12px; padding:2rem 1rem; text-align:center; cursor:pointer; transition:all 0.2s; background:var(--bg-page);"
                           ondragover="event.preventDefault(); this.style.borderColor='#0099FF'; this.style.background='#EFF6FF';"
                           ondragleave="this.style.borderColor='#CBD5E1'; this.style.background='#F8FAFC';"
                           ondrop="handleImageDrop(event)">
                        <div id="image-preview-wrap" style="display:none; margin-bottom:0.75rem;">
                            <img id="image-preview" src="" alt="Preview"
                                 style="max-height:160px; max-width:100%; border-radius:8px; object-fit:contain; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                        </div>
                        <div id="image-placeholder">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size:2rem; color:var(--text-subtle); margin-bottom:0.5rem;"></i>
                            <div style="font-size:0.88rem; font-weight:700; color:var(--text-muted);">Click to upload or drag & drop</div>
                            <div style="font-size:0.75rem; color:var(--text-subtle); margin-top:0.25rem;">JPG, PNG, WEBP — max 5MB</div>
                        </div>
                        <div id="image-file-name" style="font-size:0.78rem; color:var(--text-accent); font-weight:700; margin-top:0.5rem; display:none;"></div>
                    </label>
                    <input type="file" id="product-image-input" name="image" accept="image/*" required
                           style="display:none;" onchange="previewImage(this)">
                </div>

                {{-- Tips Card --}}
                <div style="background:var(--accent-soft); border:1.5px solid var(--info-border); border-radius:14px; padding:1.25rem 1.35rem;">
                    <div style="font-size:0.82rem; font-weight:800; color:var(--text-accent); margin-bottom:0.65rem;">
                        <i class="fa-solid fa-lightbulb"></i> What happens next?
                    </div>
                    <ul style="margin:0; padding-left:1.1rem; font-size:0.8rem; color:var(--text-accent); line-height:1.7;">
                        <li>Product is saved as <strong>Draft</strong> (hidden from catalog)</li>
                        <li>You'll be taken to the BOM & Options editor</li>
                        <li>Set Status → <strong>Active</strong> to publish</li>
                    </ul>
                </div>

            </div>
        </div>

        {{-- BOTTOM ACTION BAR --}}
        <div style="position:fixed; bottom:0; left:0; right:0; background:var(--bg-surface); border-top:1px solid var(--border-color); padding:1rem 2rem; display:flex; justify-content:flex-end; align-items:center; gap:0.75rem; z-index:100; box-shadow:0 -4px 12px rgba(0,0,0,0.06);">
            <a href="{{ route('super-admin.products.index') }}"
               style="background:var(--bg-surface); border:1px solid var(--border-color); color:var(--text-muted); padding:0.65rem 1.5rem; border-radius:10px; font-size:0.9rem; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem;">
                Cancel
            </a>
            <button type="submit"
                    style="background:linear-gradient(135deg,var(--accent-color),var(--accent-color)); color:var(--text-inverse); border:none; padding:0.65rem 1.75rem; border-radius:10px; font-size:0.9rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem; box-shadow:0 4px 12px rgba(0,153,255,0.3);">
                <i class="fa-solid fa-plus-circle"></i> Create & Configure BOM
            </button>
        </div>

    </form>
</div>

<script>
function toggleCustomMode(on) {
    const card       = document.getElementById('custom-mode-card');
    const details    = document.getElementById('add-custom-item-details');
    const badge      = document.getElementById('custom-badge');
    const desc       = document.getElementById('custom-desc');
    const features   = document.getElementById('custom-features');
    const stateLabel = document.getElementById('custom-state-label');
    const track      = document.getElementById('toggle-track');
    const knob       = document.getElementById('toggle-knob');
    const reqDesign  = document.getElementById('req_design_file');
    const reqTerms   = document.getElementById('req_custom_terms');

    if (on) {
        card.style.borderColor   = '#0099FF';
        card.style.background    = '#EFF6FF';
        badge.style.background   = '#D1FAE5';
        badge.style.color        = '#065F46';
        badge.textContent        = 'ON';
        desc.textContent         = 'Enables customer design upload, terms agreement, & production time';
        features.innerHTML       = `
            <div style="font-size:0.75rem; color:var(--text-success); font-weight:600;"><i class="fa-solid fa-check-circle" style="font-size:0.6rem;"></i> Estimated Production Time</div>
            <div style="font-size:0.75rem; color:var(--text-success); font-weight:600;"><i class="fa-solid fa-check-circle" style="font-size:0.6rem;"></i> Customer Design File Upload</div>
            <div style="font-size:0.75rem; color:var(--text-success); font-weight:600;"><i class="fa-solid fa-check-circle" style="font-size:0.6rem;"></i> Mandatory Terms Agreement</div>`;
        stateLabel.textContent   = 'ON';
        stateLabel.style.color   = '#0099FF';
        track.style.background   = '#0099FF';
        knob.style.transform     = 'translateX(20px)';
        details.style.display    = 'block';
        reqDesign.value          = '1';
        reqTerms.value           = '1';
    } else {
        card.style.borderColor   = '#E2E8F0';
        card.style.background    = '';
        badge.style.background   = '#F1F5F9';
        badge.style.color        = '#64748B';
        badge.textContent        = 'MASTER SWITCH';
        desc.textContent         = 'One switch for customized products — turning it ON enables everything a custom item needs:';
        features.innerHTML       = `
            <div style="font-size:0.75rem; color:var(--text-subtle);"><i class="fa-regular fa-circle" style="font-size:0.55rem;"></i> Estimated Production Time</div>
            <div style="font-size:0.75rem; color:var(--text-subtle);"><i class="fa-regular fa-circle" style="font-size:0.55rem;"></i> Customer Design File Upload</div>
            <div style="font-size:0.75rem; color:var(--text-subtle);"><i class="fa-regular fa-circle" style="font-size:0.55rem;"></i> Mandatory Terms Agreement</div>`;
        stateLabel.textContent   = 'OFF';
        stateLabel.style.color   = '#94A3B8';
        track.style.background   = '#CBD5E1';
        knob.style.transform     = 'translateX(0)';
        details.style.display    = 'none';
        reqDesign.value          = '0';
        reqTerms.value           = '0';
    }
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image-preview').src = e.target.result;
            document.getElementById('image-preview-wrap').style.display = 'block';
            document.getElementById('image-placeholder').style.display = 'none';
            document.getElementById('image-file-name').style.display = 'block';
            document.getElementById('image-file-name').textContent = input.files[0].name + ' (' + (input.files[0].size / 1048576).toFixed(2) + ' MB)';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleImageDrop(event) {
    event.preventDefault();
    const input = document.getElementById('product-image-input');
    const dt    = new DataTransfer();
    dt.items.add(event.dataTransfer.files[0]);
    input.files = dt.files;
    previewImage(input);
    event.currentTarget.style.borderColor = '#CBD5E1';
    event.currentTarget.style.background  = '#F8FAFC';
}

// Apply initial state if old() value was true
@if(old('enable_production_time'))
toggleCustomMode(true);
@endif
</script>
@endsection
