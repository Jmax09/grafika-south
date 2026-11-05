@extends('layouts.admin')

@section('title', 'Configure Product & BOM Builder - ' . $product->name)

@section('hide_alerts', true)

@push('styles')
<style>
    #media-grid {
        display: none;
        grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
        gap: 0.65rem;
        padding: 0.85rem 0.85rem 0.5rem;
        counter-reset: media-num;
    }
    #media-grid.has-media { display: grid; }

    /* Auto-incrementing order number on every media thumbnail */
    #media-grid .media-num {
        counter-increment: media-num;
        position: absolute;
        bottom: 4px;
        left: 4px;
        background: rgba(15,23,42,0.72);
        color: var(--text-inverse);
        font-size: 0.6rem;
        font-weight: 800;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 4;
        pointer-events: none;
    }
    #media-grid .media-num::before { content: counter(media-num); }

    /* Always-visible thumbnail action buttons (shared by image + video thumbs) */
    .gallery-thumb, .video-thumb { position: relative; }
    .gallery-thumb .thumb-actions,
    .video-thumb .thumb-actions {
        position: absolute;
        top: 4px;
        right: 4px;
        display: flex;
        flex-direction: column;
        gap: 3px;
        z-index: 5;
    }
    .gallery-thumb .thumb-btn,
    .video-thumb .thumb-btn {
        width: 26px;
        height: 26px;
        border: none;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.7rem;
        transition: transform 0.1s, opacity 0.15s;
        box-shadow: 0 1px 4px rgba(0,0,0,0.2);
        opacity: 0.88;
    }
    .gallery-thumb .thumb-btn:hover,
    .video-thumb .thumb-btn:hover { opacity: 1; transform: scale(1.12); }
    .gallery-thumb .thumb-btn-delete, .video-thumb .thumb-btn-delete { background: var(--danger-color); color: var(--text-inverse); }

    /* Drag-to-reorder affordances */
    #media-grid .gallery-thumb, #media-grid .video-thumb { cursor: grab; }
    #media-grid .gallery-thumb:active, #media-grid .video-thumb:active { cursor: grabbing; }
    .media-thumb-dragging { opacity: 0.4 !important; }
    .media-thumb-drop-target { box-shadow: 0 0 0 3px var(--accent-color) inset !important; }

    /* Production time number input: hide spinners, soften placeholder */
    #prod-time-value { -moz-appearance: textfield; appearance: textfield; }
    #prod-time-value::-webkit-outer-spin-button,
    #prod-time-value::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    #prod-time-value::placeholder { color: var(--text-subtle); font-weight: 600; }

    /* Production time unit segmented toggle */
    #prod-time-unit { display:inline-flex; background:var(--bg-subtle); border:1px solid var(--border-color); border-radius:10px; padding:2px; gap:2px; }
    #prod-time-unit .ptu-btn { border:none; background:transparent; color:var(--text-muted); font-size:0.76rem; font-weight:700; padding:0.4rem 0.6rem; border-radius:8px; cursor:pointer; transition:all 0.15s; white-space:nowrap; line-height:1; }
    #prod-time-unit .ptu-btn:hover:not(.active) { color:var(--text-accent); }
    #prod-time-unit .ptu-btn.active { background:var(--accent-color); color:var(--text-inverse); box-shadow:0 1px 2px rgba(0,153,255,0.35); }
    #prod-time-unit.disabled { opacity:0.5; pointer-events:none; }

    /* Delete confirmation modal pop-in */
    @@keyframes delModalPop { from { transform: scale(0.95); opacity:0; } to { transform: scale(1); opacity:1; } }
</style>
@endpush

@section('content')

{{-- Add padding bottom so the content is not hidden behind the fixed bottom bar --}}
<div style="padding-bottom: 5.5rem;">

    {{-- BREADCRUMB & HEADER --}}
    <div style="margin-bottom: 1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
            <div>
                <h1 style="font-size:1.6rem; font-weight:800; color:var(--text-main); margin:0; display:flex; align-items:center; gap:0.6rem;">
                    <i class="fa-solid fa-sliders" style="color:var(--text-accent);"></i>
                    <span style="color:var(--text-main);">{{ $product->name }}</span>
                </h1>
            </div>
            <span style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.3rem 0.85rem; background:{{ $product->is_active ? 'var(--success-soft)' : 'var(--warning-soft)' }}; color:{{ $product->is_active ? 'var(--text-success)' : 'var(--text-warning)' }}; border-radius:9999px; font-size:0.75rem; font-weight:700; text-transform:uppercase; {{ !$product->is_active ? 'border:1px solid var(--warning-border);' : '' }}">
                <i class="fa-solid fa-circle" style="font-size:0.45rem;"></i> {{ $product->is_active ? 'Active' : 'Draft' }}
            </span>
        </div>
    </div>

    {{-- MASTER SAVING FORM --}}
    <form id="master-product-form" action="{{ route('super-admin.products.master-save', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf


        {{-- ═══════════════════════════════════════
             SECTION 1 — CORE DETAILS & BASE PRICE
        ════════════════════════════════════════ --}}
        <div class="card" style="padding:1.5rem; margin-bottom:1.25rem;">
            <h3 style="font-size:1rem; font-weight:800; color:var(--text-main); margin:0 0 1.1rem; display:flex; align-items:center; gap:0.5rem; padding-bottom:0.75rem; border-bottom:1px solid var(--border-color);">
                <i class="fa-solid fa-circle-info" style="color:var(--text-accent);"></i> Details
            </h3>

            {{-- ═══════════════════════════════════════
                 CUSTOM ITEM MODE + PRODUCTION TIME — one merged card
            ═══════════════════════════════════════ --}}
            @php $customItemOn = ($product->enable_production_time ?? true); @endphp
            <div id="custom-item-mode-card"
                style="margin-bottom:1.25rem; background:{{ $customItemOn ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; border:1.5px solid {{ $customItemOn ? 'var(--accent-color)' : 'var(--border-color)' }}; border-radius:12px; padding:0.7rem 1.15rem; display:flex; align-items:center; justify-content:space-between; gap:1.25rem; flex-wrap:wrap; transition:background 0.2s, border-color 0.2s;">
                {{-- Left: Custom Item Mode toggle --}}
                <div style="display:flex; align-items:center; gap:0.9rem; flex-shrink:0;">
                    <div id="custom-item-icon"
                        style="width:40px; height:40px; border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:1rem; background:{{ $customItemOn ? 'var(--accent-color)' : 'var(--bg-subtle)' }}; color:{{ $customItemOn ? 'var(--text-inverse)' : 'var(--text-subtle)' }}; transition:background 0.2s, color 0.2s;">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <span style="font-size:0.95rem; font-weight:800; color:var(--text-main);">Custom Item Mode</span>
                    <span style="font-size:0.72rem; font-weight:800; color:var(--text-accent); letter-spacing:0.06em; text-transform:uppercase;" id="prod-time-state">
                        {{ $customItemOn ? 'ON' : 'OFF' }}
                    </span>
                    <div style="position:relative; width:48px; height:26px; cursor:pointer;" onclick="toggleCustomItemSwitch(this)" title="Custom Item Mode — turns on production time, design file upload & custom terms together">
                        <input type="hidden" name="enable_production_time" value="0">
                        <input type="checkbox" name="enable_production_time" value="1" {{ $customItemOn ? 'checked' : '' }} style="position:absolute; opacity:0; width:0; height:0;">
                        <div class="sw-track" style="position:absolute; inset:0; background:{{ $customItemOn ? 'var(--accent-color)' : 'var(--text-disabled)' }}; border-radius:9999px; transition:background 0.2s;"></div>
                        <div class="sw-knob" style="position:absolute; top:3px; left:3px; width:20px; height:20px; background:var(--bg-surface); border-radius:50%; box-shadow:0 1px 3px rgba(0,0,0,0.2); transform:{{ $customItemOn ? 'translateX(22px)' : 'translateX(0)' }}; transition:transform 0.2s;"></div>
                    </div>
                </div>
                {{-- Right: Estimated Production Time --}}
                <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap; margin-left:auto;">
                    <div>
                        <label for="prod-time-value" style="font-size:0.8rem; font-weight:800; color:var(--text-main); display:flex; align-items:center; gap:0.4rem;">
                            <i class="fa-solid fa-stopwatch" style="color:var(--text-accent); font-size:0.95rem;"></i>
                            Est. Production Time
                        </label>
                        <div id="prod-time-lock" style="display:{{ $customItemOn ? 'none' : 'inline-flex' }}; align-items:center; gap:0.4rem; margin-top:0.3rem; font-size:0.68rem; font-weight:700; color:var(--text-main); background:var(--bg-subtle); border:1px solid var(--border-color); padding:0.15rem 0.55rem; border-radius:9999px;">
                            <i class="fa-solid fa-lock" style="font-size:0.6rem;"></i> Enable Custom Item Mode to set
                        </div>
                    </div>
                    @php
                        $ptMins = (int) old('custom_production_time_minutes', $product->custom_production_time_minutes);
                        if ($ptMins > 0 && $ptMins % 1440 === 0) { $ptUnit = 'day'; $ptDisp = (int)($ptMins / 1440); }
                        elseif ($ptMins > 0 && $ptMins % 60 === 0) { $ptUnit = 'hr'; $ptDisp = (int)($ptMins / 60); }
                        else { $ptUnit = 'min'; $ptDisp = $ptMins; }
                        $ptEditable = ($product->enable_production_time ?? true);
                    @endphp
                    <div id="prod-time-field" style="flex-shrink:0; transition:opacity 0.2s;{{ $ptEditable ? '' : ' opacity:0.5;' }}">
                        <div style="display:flex; align-items:center; gap:0.4rem;">
                            <input type="number" id="prod-time-value" min="0" step="1"
                                value="{{ $ptDisp ?: '' }}"
                                placeholder="e.g. 30" {{ $ptEditable ? '' : 'readonly' }}
                                style="width:72px; padding:0.55rem 0.6rem; border:1.5px solid var(--accent-color); border-radius:10px; font-size:0.92rem; font-weight:700; outline:none; color:var(--text-main); background:{{ $ptEditable ? 'var(--bg-surface)' : 'var(--bg-subtle)' }}; box-sizing:border-box; transition:all 0.2s; text-align:center; cursor:{{ $ptEditable ? 'text' : 'not-allowed' }};"
                                onfocus="this.style.borderColor='#0099FF'; this.style.boxShadow='0 0 0 3px rgba(0,153,255,0.15)';"
                                onblur="this.style.borderColor='#7DD3FC'; this.style.boxShadow='none';"
                                oninput="prodTimeSyncFromInput()">
                            <div id="prod-time-unit" role="group" aria-label="Production time unit" class="{{ $ptEditable ? '' : 'disabled' }}">
                                <button type="button" data-unit="min" onclick="setProdTimeUnit('min')" class="ptu-btn{{ $ptUnit === 'min' ? ' active' : '' }}">Mins</button>
                                <button type="button" data-unit="hr" onclick="setProdTimeUnit('hr')" class="ptu-btn{{ $ptUnit === 'hr' ? ' active' : '' }}">Hours</button>
                                <button type="button" data-unit="day" onclick="setProdTimeUnit('day')" class="ptu-btn{{ $ptUnit === 'day' ? ' active' : '' }}">Days</button>
                            </div>
                        </div>
                        <input type="hidden" id="custom_production_time_minutes" name="custom_production_time_minutes" value="{{ $ptMins }}">
                        <div id="production-time-preview" style="font-size:0.72rem; font-weight:700; color:var(--text-accent); margin-top:0.3rem; text-align:right;">
                            @if($ptMins > 0)
                                <i class="fa-regular fa-clock"></i> {{ $product->formatted_production_time }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 1: Name --}}
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Product Name *</label>
                <input type="text" name="name" value="{{ $product->name }}" required
                    style="width:100%; padding:0.65rem 0.95rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.92rem; outline:none; color:var(--text-main); background:var(--bg-surface); box-sizing:border-box;"
                    onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor=''">
            </div>

            {{-- Row 2: Category + Price --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Category *</label>
                    @php
                        $knownCategories = $categories ?? collect();
                        $isOther = $product->category && !$knownCategories->contains($product->category);
                    @endphp
                    <select id="category-select" onchange="handleCategoryEditSelect(this)"
                        style="width:100%; padding:0.65rem 0.95rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.92rem; outline:none; background:var(--bg-surface); color:var(--text-main); cursor:pointer;"
                        onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor=''">
                        @foreach($knownCategories as $cat)
                            @if($cat !== 'Uncategorized')
                                <option value="{{ $cat }}" {{ (!$isOther && $product->category === $cat) ? 'selected' : '' }}>{{ $cat }}</option>
                            @endif
                        @endforeach
                        <option value="__others__" {{ $isOther ? 'selected' : '' }}>Others...</option>
                    </select>
                    {{-- Hidden real input that gets submitted --}}
                    <input type="text" id="category-input" name="category" required
                        value="{{ $product->category }}"
                        placeholder="Type category name..."
                        style="width:100%; padding:0.65rem 0.95rem; border:1px solid var(--accent-color); border-radius:10px; font-size:0.92rem; outline:none; color:var(--text-main); background:var(--bg-surface); margin-top:0.4rem; box-sizing:border-box; display:{{ $isOther ? 'block' : 'none' }};"
                        onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor=''">
                </div>
                <div>
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Base Price (₱) *</label>
                    <input type="number" step="0.01" name="price" value="{{ (float)($product->price ?: $product->base_price) }}" required
                        style="width:100%; padding:0.65rem 0.95rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.92rem; outline:none; color:var(--text-main); background:var(--bg-surface); font-weight:700;"
                        onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor=''">
                </div>
                {{-- Status is managed via the Publish toggle / readiness banner; preserved here so the form still validates --}}
                <input type="hidden" name="is_active" value="{{ $product->is_active ? 1 : 0 }}">
            </div>

            {{-- Row 3: Description --}}
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Description</label>
                <textarea name="description" rows="3"
                    style="width:100%; padding:0.65rem 0.95rem; border:1px solid var(--border-color); border-radius:10px; font-size:0.92rem; outline:none; color:var(--text-main); background:var(--bg-surface); resize:vertical; font-family:inherit;"
                    onfocus="this.style.borderColor='#0099FF'" onblur="this.style.borderColor=''">{{ $product->description }}</textarea>
            </div>

            {{-- Row 4: Product Images (full width) --}}
            {{-- requires_design_file & requires_custom_terms are submitted as hidden fields driven by the master switch --}}
            <input type="hidden" id="hidden_requires_design_file" name="requires_design_file" value="{{ $customItemOn ? '1' : '0' }}">
            <input type="hidden" id="hidden_requires_custom_terms" name="requires_custom_terms" value="{{ $customItemOn ? '1' : '0' }}">

            <div style="margin-bottom:0.5rem;">
                <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.3rem;">
                    <i class="fa-solid fa-photo-film" style="color:var(--text-accent); margin-right:0.35rem;"></i>Product Media
                    <span id="gallery-count-span" style="font-weight:500; color:var(--text-subtle); font-size:0.72rem; margin-left:0.3rem;">({{ $product->images->count() + $product->videos->count() }} saved)</span>
                </label>

                {{-- Unified media container — images + showcase videos in one box --}}
                <div id="gallery-wrapper" style="border:2px dashed var(--border-color); border-radius:12px; background:var(--bg-page); overflow:hidden;">

                    {{-- Combined media grid — images + videos in ONE unified drag-order, first = Main --}}
                    @php
                        $combinedMedia = collect(array_merge(
                            $product->videos->map(fn($v) => ['type' => 'video', 'm' => $v, 'pos' => (int) ($v->position ?: 0), 'primary' => (bool) $v->is_primary])->all(),
                            $product->images->map(fn($i) => ['type' => 'image', 'm' => $i, 'pos' => (int) ($i->position ?: 0), 'primary' => (bool) $i->is_primary])->all()
                        ))
                            ->sortBy([['primary', 'desc'], ['pos', 'asc']])
                            ->values();
                    @endphp
                    <div id="media-grid" class="{{ $combinedMedia->isNotEmpty() ? 'has-media' : '' }}"
                        ondragover="mediaDragOver(event)" ondrop="mediaDrop(event)">
                        @foreach($combinedMedia as $mi)
                            @php $isVid = $mi['type'] === 'video'; $m = $mi['m']; @endphp
                            <div class="{{ $isVid ? 'video-thumb' : 'gallery-thumb' }}" data-id="{{ $m->id }}" @if($isVid) data-ext="{{ strtolower($m->extension) }}" @endif draggable="true"
                                ondragstart="mediaDragStart(event, this)" ondragend="mediaDragEnd(event, this)"
                                style="position:relative; border-radius:10px; overflow:hidden; border:2px solid {{ $m->is_primary ? 'var(--accent-color)' : 'var(--border-color)' }}; aspect-ratio:1; background:{{ $isVid ? 'var(--text-main)' : 'var(--bg-page)' }};">
                                @if($isVid)
                                    <video src="{{ $m->url }}#t=0.5" preload="metadata" muted playsinline
                                        style="width:100%; height:100%; object-fit:cover; display:block; pointer-events:none;"></video>
                                    @if($m->is_primary)
                                        <span class="primary-badge" style="position:absolute; top:4px; left:4px; background:var(--accent-color); color:var(--text-inverse); font-size:0.58rem; font-weight:800; padding:1px 5px; border-radius:4px; text-transform:uppercase; pointer-events:none; z-index:4;">Main</span>
                                    @else
                                        <span style="position:absolute; top:4px; left:4px; background:rgba(0,153,255,0.92); color:var(--text-inverse); font-size:0.55rem; font-weight:800; padding:1px 5px; border-radius:4px; pointer-events:none; z-index:4;"><i class="fa-solid fa-play" style="font-size:0.5rem; margin-right:2px;"></i>{{ strtoupper($m->extension) }}</span>
                                    @endif
                                @else
                                    <img src="{{ $m->url }}" alt="" style="width:100%; height:100%; object-fit:cover; display:block;">
                                    @if($m->is_primary)
                                        <span class="primary-badge" style="position:absolute; top:4px; left:4px; background:var(--accent-color); color:var(--text-inverse); font-size:0.58rem; font-weight:800; padding:1px 5px; border-radius:4px; text-transform:uppercase; pointer-events:none; z-index:4;">Main</span>
                                    @endif
                                @endif
                                <span class="media-num"></span>
                                <div class="thumb-actions">
                                    <button type="button" class="thumb-btn thumb-btn-delete"
                                        onclick="{{ $isVid ? 'deleteProductVideo' : 'deleteGalleryImage' }}({{ $m->id }}, this)"
                                        title="Delete {{ $isVid ? 'video' : 'image' }}">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Divider shown only when there is media --}}
                    @if($product->images->isNotEmpty() || $product->videos->isNotEmpty())
                    <div id="gallery-divider" style="border-top:1px dashed var(--border-color); margin:0 0.85rem;"></div>
                    @endif

                    {{-- Unified dropzone: auto-routes images vs videos --}}
                    <div id="edit-img-dropzone"
                        style="padding:1.1rem 1rem; text-align:center; cursor:pointer; transition:background 0.2s;"
                        onclick="document.getElementById('media-file-input').click();"
                        ondragover="event.preventDefault(); document.getElementById('edit-img-dropzone').style.background='#EFF6FF'; document.getElementById('gallery-wrapper').style.borderColor='#0099FF';"
                        ondragleave="document.getElementById('edit-img-dropzone').style.background=''; document.getElementById('gallery-wrapper').style.borderColor='';"
                        ondrop="event.preventDefault(); document.getElementById('edit-img-dropzone').style.background=''; document.getElementById('gallery-wrapper').style.borderColor=''; handleMediaDrop(event);">
                        <input type="file" id="media-file-input" accept="image/*,video/mp4,video/webm,video/quicktime,video/x-msvideo,.mp4,.webm,.mov,.avi,.m4v" multiple
                            style="display:none;" onchange="handleMediaFiles(this.files); this.value='';">
                        <div id="media-upload-prompt">
                            <div style="width:36px; height:36px; background:var(--accent-soft); color:var(--text-accent); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 0.4rem; font-size:1rem;">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <div style="font-size:0.8rem; font-weight:700; color:var(--text-main); margin-bottom:0.15rem;">Click or drag &amp; drop to add images or videos</div>
                            <div style="font-size:0.7rem; color:var(--text-subtle);">Images: PNG, JPG, WEBP ≤5MB · Videos: MP4, WEBM, MOV, AVI ≤50MB</div>
                        </div>
                        <div id="media-uploading" style="display:none; align-items:center; justify-content:center; gap:0.5rem; color:var(--text-accent); font-size:0.82rem; font-weight:700;">
                            <i class="fa-solid fa-spinner fa-spin"></i> Uploading...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
             SECTION 2 — BILL OF MATERIALS (BOM)
        ════════════════════════════════════════ --}}
        <div class="card" id="materials-card" style="padding:1.5rem; margin-bottom:1.25rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; padding-bottom:0.75rem; border-bottom:1px solid var(--border-color); flex-wrap:wrap; gap:0.75rem;">
                <h3 id="materials-heading" style="font-size:1rem; font-weight:800; color:var(--text-main); margin:0; display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                    <i class="fa-solid fa-boxes-packing" style="color:var(--text-accent);"></i> Materials
                    <span id="materials-hint" style="display:none; font-size:0.78rem; font-weight:700; color:var(--text-danger);"><i class="fa-solid fa-triangle-exclamation"></i> Add at least one material to save</span>
                </h3>
                <button type="button" onclick="addBomRow()"
                    style="background:var(--accent-color); color:var(--text-inverse); padding:0.5rem 1rem; border-radius:8px; font-size:0.85rem; font-weight:700; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:0.4rem; transition:background 0.15s;"
                    onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                    <i class="fa-solid fa-plus"></i> Add Material Row
                </button>
            </div>

            <div id="bom-rows-container" style="display:flex; flex-direction:column; gap:0.65rem;">
                @forelse($product->productMaterials as $idx => $pm)
                <div class="bom-row" style="display:grid; grid-template-columns:2fr 1fr 40px; gap:0.75rem; align-items:end; background:var(--bg-page); border:1px solid var(--border-color); padding:0.85rem 1rem; border-radius:10px;">
                    <div>
                        <label style="font-size:0.75rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.25rem;">Raw Material *</label>
                        <select name="materials[{{ $idx }}][material_id]" required style="width:100%; padding:0.6rem 0.85rem; border:1px solid var(--border-color); border-radius:8px; font-size:0.88rem; background:var(--bg-surface); color:var(--text-main); outline:none;">
                            @foreach($allMaterials as $m)
                                <option value="{{ $m->id }}" {{ $pm->material_id == $m->id ? 'selected' : '' }}>{{ $m->name }} ({{ $m->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.75rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.25rem;">Qty / Unit *</label>
                        <input type="number" step="0.0001" name="materials[{{ $idx }}][quantity_used]" value="{{ (float)$pm->quantity_used }}" required
                            style="width:100%; padding:0.6rem 0.85rem; border:1px solid var(--border-color); border-radius:8px; font-size:0.88rem; background:var(--bg-surface); color:var(--text-main); font-weight:700; outline:none;">
                    </div>
                    <button type="button" onclick="removeBomRow(this)"
                        style="background:var(--danger-soft); border:1px solid var(--danger-border); color:var(--text-danger); border-radius:8px; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                        <i class="fa-solid fa-trash-can" style="font-size:0.82rem;"></i>
                    </button>
                </div>
                @empty
                <div class="bom-row" style="display:grid; grid-template-columns:2fr 1fr 40px; gap:0.75rem; align-items:end; background:var(--bg-page); border:1px solid var(--border-color); padding:0.85rem 1rem; border-radius:10px;">
                    <div>
                        <label style="font-size:0.75rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.25rem;">Raw Material *</label>
                        <select name="materials[0][material_id]" required style="width:100%; padding:0.6rem 0.85rem; border:1px solid var(--border-color); border-radius:8px; font-size:0.88rem; background:var(--bg-surface); color:var(--text-main); outline:none;">
                            @foreach($allMaterials as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.75rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.25rem;">Qty / Unit *</label>
                        <input type="number" step="0.0001" name="materials[0][quantity_used]" value="1.0000" required
                            style="width:100%; padding:0.6rem 0.85rem; border:1px solid var(--border-color); border-radius:8px; font-size:0.88rem; background:var(--bg-surface); color:var(--text-main); font-weight:700; outline:none;">
                    </div>
                    <button type="button" onclick="removeBomRow(this)"
                        style="background:var(--danger-soft); border:1px solid var(--danger-border); color:var(--text-danger); border-radius:8px; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                        <i class="fa-solid fa-trash-can" style="font-size:0.82rem;"></i>
                    </button>
                </div>
                @endforelse
            </div>
        </div>

        {{-- ═══════════════════════════════════════
             SECTION 3 — DYNAMIC OPTIONS
        ════════════════════════════════════════ --}}
        <div class="card" style="padding:1.5rem; margin-bottom:1.25rem;">
            <div id="options-hidden-inputs-container">
                <input type="hidden" name="options_present" value="1">
            </div>
            <h3 style="font-size:1rem; font-weight:800; color:var(--text-main); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem; padding-bottom:0.75rem; border-bottom:1px solid var(--border-color);">
                <i class="fa-solid fa-sliders" style="color:var(--text-accent);"></i> Options <span style="font-size:0.82rem; color:var(--text-muted); font-weight:600;" id="options-count-badge">(0)</span>
            </h3>

            {{-- Dynamic list of options managed by JS --}}
            <div id="options-list-container" style="display:flex; flex-direction:column; gap:0.5rem; margin-bottom:1rem;">
                {{-- Populated dynamically via JS --}}
            </div>

            {{-- Shared picker behind every row thumbnail: no name attribute, so the
                 master form never serializes it (the file is appended on submit instead) --}}
            <input type="file" id="option-row-image-input" accept="image/png,image/jpeg,image/webp" style="display:none;"
                onchange="handleOptionRowImageSelect(this.files[0]); this.value='';">

            {{-- Form container to add new option row to JS array --}}
            <div style="background:var(--bg-page); border:1px solid var(--border-color); border-radius:10px; padding:1rem;">
                <div style="font-size:0.82rem; font-weight:700; color:var(--text-main); margin-bottom:0.75rem; display:flex; align-items:center; gap:0.4rem;">
                    <i class="fa-solid fa-plus-circle" style="color:var(--text-accent);"></i> Add New Option Value
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                    <div>
                        <label style="font-size:0.75rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.25rem;">Option Name *</label>
                        {{-- Custom combobox: type a new name or click the caret to pick an existing one --}}
                        <div id="option-name-combo" style="position:relative;">
                            <input type="text" id="new-option-name" placeholder="e.g. Size" autocomplete="off"
                                oninput="renderOptionNameDropdown()" onfocus="openOptionNameDropdown()"
                                style="width:100%; padding:0.55rem 0.75rem; border:1px solid var(--border-color); border-radius:8px; font-size:0.88rem; outline:none; background:var(--bg-surface); color:var(--text-main);">
                            <div id="option-name-dropdown"
                                style="display:none; position:absolute; top:calc(100% + 4px); left:0; right:0; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:8px; box-shadow:0 8px 20px rgba(15,23,42,0.12); max-height:200px; overflow-y:auto; z-index:60;"></div>
                        </div>
                    </div>
                    <div>
                        <label style="font-size:0.75rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.25rem;">Option Value *</label>
                        <input type="text" id="new-option-value" placeholder="e.g. Large"
                            style="width:100%; padding:0.55rem 0.75rem; border:1px solid var(--border-color); border-radius:8px; font-size:0.88rem; outline:none; background:var(--bg-surface);">
                    </div>
                    <div>
                        <label style="font-size:0.75rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.25rem;">Price Add-on (+₱) *</label>
                        <input type="number" step="0.01" id="new-option-price" value="0.00"
                            style="width:100%; padding:0.55rem 0.75rem; border:1px solid var(--border-color); border-radius:8px; font-size:0.88rem; outline:none; background:var(--bg-surface); font-weight:700;">
                    </div>
                </div>
                <div id="new-option-error" style="color:var(--text-danger); font-size:0.78rem; font-weight:600; margin-bottom:0.5rem; display:none; align-items:center; gap:0.25rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <span>Please enter both Option Name and Option Value.</span>
                </div>

                {{-- Optional reference image for the value being added --}}
                <div style="margin-bottom:0.75rem;">
                    <label style="font-size:0.75rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.25rem;">
                        Option Image <span style="font-weight:600; color:var(--text-subtle);">(optional)</span>
                    </label>
                    <div id="new-option-image-dropzone"
                        onclick="document.getElementById('new-option-image-input').click();"
                        ondragover="event.preventDefault(); this.style.borderColor='#0099FF'; this.style.background='var(--accent-soft)';"
                        ondragleave="event.preventDefault(); this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-surface)';"
                        ondrop="event.preventDefault(); this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-surface)'; handleNewOptionImageDrop(event);"
                        style="display:flex; align-items:center; gap:0.75rem; border:1.5px dashed var(--border-color); border-radius:10px; padding:0.6rem 0.75rem; background:var(--bg-surface); cursor:pointer; transition:border-color 0.15s, background 0.15s;">
                        <input type="file" id="new-option-image-input" accept="image/png,image/jpeg,image/webp" style="display:none;"
                            onchange="handleNewOptionImageSelect(this.files[0]); this.value='';">
                        <div id="new-option-image-thumb" style="display:none; position:relative; flex-shrink:0;">
                            <img src="" alt="Option image preview" style="width:44px; height:44px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color);">
                            <button type="button" onclick="event.stopPropagation(); clearNewOptionImage();"
                                title="Remove image"
                                style="position:absolute; top:-7px; right:-7px; width:20px; height:20px; border-radius:50%; background:var(--danger-color); color:var(--text-inverse); border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; padding:0; font-size:0.6rem;">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div id="new-option-image-prompt" style="min-width:0;">
                            <div style="font-size:0.78rem; font-weight:700; color:var(--text-main);">
                                <i class="fa-solid fa-cloud-arrow-up" style="color:var(--text-accent); margin-right:0.3rem;"></i> Click to upload or drag &amp; drop
                            </div>
                            <div style="font-size:0.68rem; color:var(--text-subtle);">Shown on the product page, cart and checkout · PNG, JPG, WEBP ≤2MB</div>
                        </div>
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end;">
                    <button type="button" onclick="addNewOptionRow()"
                        style="background:var(--accent-color); color:var(--text-inverse); padding:0.55rem 1.2rem; border-radius:8px; font-size:0.85rem; font-weight:700; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:0.4rem; transition:background 0.15s;"
                        onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                        <i class="fa-solid fa-plus"></i> Add Option to Product
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

{{-- MASTER SAVE FIXED BOTTOM BAR --}}
<style>
    .fixed-bottom-bar {
        position:fixed; bottom:0; left:180px; right:0; background:var(--bg-surface); border-top:1px solid var(--border-color); padding:1rem 2rem; display:flex; justify-content:flex-end; align-items:center; z-index:900; box-shadow:0 -4px 6px -1px rgba(0,0,0,0.05);
    }
    @media (max-width: 992px) {
        .fixed-bottom-bar {
            left:0;
            padding:1rem;
        }
    }
</style>
<div class="fixed-bottom-bar">
    <div style="display:flex; align-items:center; justify-content:space-between; width:100%;">
        {{-- Left: Back button (blue) --}}
        <a href="{{ route('super-admin.products.index') }}"
            style="display:inline-flex; align-items:center; gap:0.45rem; color:var(--text-inverse); text-decoration:none; font-size:0.88rem; font-weight:700; background:var(--accent-color); padding:0.7rem 1.25rem; border-radius:10px; border:none; transition:all 0.15s; box-shadow:0 2px 6px rgba(0,153,255,0.25);"
            onmouseover="this.style.background='#0080FF';" onmouseout="this.style.background='#0099FF';">
            <i class="fa-solid fa-arrow-left"></i> Back to Products
        </a>

        {{-- Right: Status messages + Delete + Save --}}
        <div style="display:flex; align-items:center; gap:1rem;">
            <span id="save-status-msg" style="font-size:0.88rem; font-weight:600; color:var(--text-success); display:none; align-items:center; gap:0.25rem;">
                <i class="fa-solid fa-circle-check"></i> Saved successfully!
            </span>
            <span id="save-error-msg" style="font-size:0.88rem; font-weight:600; color:var(--text-danger); display:none; align-items:center; gap:0.25rem;">
                <i class="fa-solid fa-circle-exclamation"></i> Error saving changes.
            </span>
            <form id="delete-product-form" action="{{ route('super-admin.products.destroy', $product->id) }}" method="POST">
                @csrf
                @method('DELETE')
            </form>
            <button type="button" onclick="openDeleteModal()"
                style="background:var(--danger-color); color:var(--text-inverse); padding:0.75rem 1.5rem; border-radius:10px; font-weight:700; font-size:0.95rem; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem; transition:background 0.15s;"
                onmouseover="this.style.background='#B91C1C'" onmouseout="this.style.background='#DC2626'">
                <i class="fa-solid fa-trash-can"></i> Delete Product
            </button>
            <button type="submit" form="master-product-form" id="master-save-btn"
                style="background:var(--accent-color); color:var(--text-inverse); padding:0.75rem 2rem; border-radius:10px; font-weight:700; font-size:0.95rem; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem; transition:background 0.15s;"
                onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
    </div>
</div>

{{-- Option BOM Modal (updates JS array state, not DB) --}}
<div id="option-bom-modal" style="display:none; position:fixed; inset:0; background:rgba(15, 23, 42, 0.6); z-index:9999; align-items:center; justify-content:center; padding:1rem; backdrop-filter:blur(4px);">
    <div style="background:var(--bg-surface); width:100%; max-width:550px; border-radius:12px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); display:flex; flex-direction:column; overflow:hidden;">
        {{-- Header --}}
        <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; background:var(--bg-page);">
            <div>
                <h3 style="font-size:1.1rem; font-weight:800; color:var(--text-main); margin:0;" id="modal-option-title">Add-on Recipe</h3>
                <p style="color:var(--text-muted); font-size:0.8rem; margin-top:0.15rem; margin-bottom:0;" id="modal-option-subtitle">Extra materials added on top of the base product.</p>
            </div>
            <button type="button" onclick="closeOptionBomModal()" style="background:none; border:none; color:var(--text-subtle); font-size:1.2rem; cursor:pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Form elements inside Modal --}}
        <div style="padding:1.25rem 1.5rem; max-height:400px; overflow-y:auto;">
            {{-- Column header --}}
            <div style="display:grid; grid-template-columns:1fr 110px 36px; gap:0.5rem; font-size:0.7rem; font-weight:700; color:var(--text-subtle); text-transform:uppercase; letter-spacing:0.04em; padding:0 0.1rem 0.35rem;">
                <span>Extra Material</span><span>Qty / Unit</span><span></span>
            </div>
            <div id="option-bom-rows" style="display:flex; flex-direction:column;"></div>
            <button type="button" onclick="addOptionBomRow()"
                style="margin-top:0.4rem; background:var(--bg-surface); border:1px dashed var(--info-border); color:var(--text-accent); padding:0.5rem 0.9rem; border-radius:8px; font-size:0.85rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:0.4rem; transition:all 0.15s;"
                onmouseover="this.style.background='var(--accent-soft)'; this.style.borderColor='var(--accent-color)'" onmouseout="this.style.background='var(--bg-elevated)'; this.style.borderColor='var(--border-color)'">
                <i class="fa-solid fa-plus"></i> Add material
            </button>
        </div>

        {{-- Footer --}}
        <div style="padding:0.9rem 1.5rem; border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; gap:0.75rem; background:var(--bg-page);">
            <button type="button" onclick="clearOptionBomRows()" style="background:var(--bg-surface); border:1px solid var(--border-color); color:var(--text-muted); padding:0.55rem 1rem; border-radius:8px; font-size:0.85rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:0.4rem; transition:all 0.15s;" onmouseover="this.style.borderColor='#FCA5A5'; this.style.color='#DC2626'" onmouseout="this.style.borderColor=''; this.style.color='#64748B'" title="Remove all extra materials">
                <i class="fa-solid fa-eraser"></i> Remove all
            </button>
            <div style="display:flex; gap:0.6rem;">
                <button type="button" onclick="closeOptionBomModal()" style="background:var(--bg-surface); border:1px solid var(--border-color); color:var(--text-muted); padding:0.55rem 1.1rem; border-radius:8px; font-size:0.85rem; font-weight:700; cursor:pointer;">
                    Cancel
                </button>
                <button type="button" onclick="saveOptionBomFromModal()" style="background:var(--accent-color); color:var(--text-inverse); border:none; padding:0.55rem 1.4rem; border-radius:8px; font-size:0.85rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:0.4rem; transition:background 0.15s;"
                    onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                    <i class="fa-solid fa-check"></i> Apply
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Product Confirmation Modal --}}
<div id="delete-product-modal" onclick="if(event.target===this) closeDeleteModal()" style="display:none; position:fixed; inset:0; background:rgba(15, 23, 42, 0.6); z-index:9999; align-items:center; justify-content:center; padding:1rem; backdrop-filter:blur(4px);">
    <div style="background:var(--bg-surface); width:100%; max-width:440px; border-radius:14px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.15), 0 10px 10px -5px rgba(0,0,0,0.05); overflow:hidden; animation:delModalPop 0.15s ease-out;">
        <div style="padding:1.75rem 1.75rem 1.25rem; text-align:center;">
            <div style="width:56px; height:56px; margin:0 auto 1rem; border-radius:50%; background:var(--danger-soft); display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:var(--text-danger);">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3 style="font-size:1.15rem; font-weight:800; color:var(--text-main); margin:0 0 0.4rem;">Delete this product?</h3>
            <p style="color:var(--text-muted); font-size:0.86rem; margin:0; line-height:1.5;">
                You are about to permanently delete <strong style="color:var(--text-main);">{{ $product->name }}</strong> and all of its BOM settings. This action <strong style="color:var(--text-danger);">cannot be undone</strong>.
            </p>
        </div>
        <div style="padding:1rem 1.75rem 1.5rem; display:flex; gap:0.75rem; border-top:1px solid var(--border-color); background:var(--bg-page);">
            <button type="button" onclick="closeDeleteModal()" style="flex:1; background:var(--bg-surface); border:1px solid var(--border-color); color:var(--text-muted); padding:0.65rem 1rem; border-radius:10px; font-size:0.88rem; font-weight:700; cursor:pointer; transition:background 0.15s;" onmouseover="this.style.background='var(--bg-subtle)'" onmouseout="this.style.background='var(--bg-surface)'">
                Cancel
            </button>
            <button type="button" onclick="confirmDeleteProduct()" style="flex:1; background:var(--danger-color); color:var(--text-inverse); border:none; padding:0.65rem 1rem; border-radius:10px; font-size:0.88rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; transition:background 0.15s;" onmouseover="this.style.background='#B91C1C'" onmouseout="this.style.background='#DC2626'">
                <i class="fa-solid fa-trash-can"></i> Yes, Delete
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Delete product confirmation modal ──
function openDeleteModal() {
    const m = document.getElementById('delete-product-modal');
    if (m) m.style.display = 'flex';
}
function closeDeleteModal() {
    const m = document.getElementById('delete-product-modal');
    if (m) m.style.display = 'none';
}
function confirmDeleteProduct() {
    const f = document.getElementById('delete-product-form');
    if (f) f.submit();
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
});

// ── Category select: dropdown + Others free-text ──────────────────────────
function handleCategoryEditSelect(sel) {
    const input = document.getElementById('category-input');
    if (sel.value === '__others__') {
        input.style.display = 'block';
        input.value = '';
        input.focus();
        input.name = 'category';
        sel.name = ''; // disable the select from submitting
    } else {
        input.style.display = 'none';
        input.name = '';      // disable free-text from submitting
        sel.name = 'category'; // select submits the value
    }
}

// On page load: ensure the correct field is the one with name="category"
(function() {
    const sel   = document.getElementById('category-select');
    const input = document.getElementById('category-input');
    if (!sel || !input) return;
    if (sel.value === '__others__') {
        sel.name   = '';
        input.name = 'category';
    } else {
        sel.name   = 'category';
        input.name = '';
    }
})();
// Material production time lookup table: { material_id: production_time_minutes }
const allMaterialProdTimes = @json($allMaterials->pluck('production_time_minutes', 'id'));

function updateProductionTimePreview(val) {
    const preview = document.getElementById('production-time-preview');
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

// Production-time unit selector: visible value + unit -> hidden minutes
const PROD_TIME_UNITS = { min: 1, hr: 60, day: 1440 };

function getProdTimeUnit() {
    const group = document.getElementById('prod-time-unit');
    if (!group) return 'min';
    const active = group.querySelector('.ptu-btn.active');
    return active ? active.dataset.unit : 'min';
}

function setProdTimeUnit(unit) {
    const group = document.getElementById('prod-time-unit');
    if (!group) return;
    group.querySelectorAll('.ptu-btn').forEach(b => b.classList.toggle('active', b.dataset.unit === unit));
    prodTimeSyncFromInput();
}

function prodTimeSyncFromInput() {
    const valEl = document.getElementById('prod-time-value');
    const hidden = document.getElementById('custom_production_time_minutes');
    if (!valEl || !hidden) return;
    const n = parseInt(valEl.value, 10);
    const mult = PROD_TIME_UNITS[getProdTimeUnit()] || 1;
    const mins = (!isNaN(n) && n > 0) ? Math.round(n * mult) : 0;
    hidden.value = mins;
    updateProductionTimePreview(mins);
}

function setProdTimeFromMinutes(mins) {
    const valEl = document.getElementById('prod-time-value');
    const hidden = document.getElementById('custom_production_time_minutes');
    const group = document.getElementById('prod-time-unit');
    if (!valEl || !hidden) return;
    mins = parseInt(mins, 10) || 0;
    let unit = 'min', disp = mins;
    if (mins > 0 && mins % 1440 === 0) { unit = 'day'; disp = mins / 1440; }
    else if (mins > 0 && mins % 60 === 0) { unit = 'hr'; disp = mins / 60; }
    valEl.value = disp || '';
    if (group) group.querySelectorAll('.ptu-btn').forEach(b => b.classList.toggle('active', b.dataset.unit === unit));
    hidden.value = mins;
    updateProductionTimePreview(mins);
}

/**
 * Auto-calculate the estimated production time from the Main BOM materials.
 * Reads each material select in #bom-rows-container, sums up each material's
 * production_time_minutes, then sets the result in #custom_production_time_minutes.
 *
 * @param {boolean} silent - If true, no alert shown when total is 0.
 */
function autoCalculateFromBom(silent) {
    const container = document.getElementById('bom-rows-container');
    if (!container) return;

    const selects = container.querySelectorAll('select[name^="materials["]');
    let totalMins = 0;
    let rowCount = 0;

    selects.forEach(function(sel) {
        const matId = parseInt(sel.value, 10);
        if (matId && allMaterialProdTimes[matId]) {
            totalMins += parseInt(allMaterialProdTimes[matId], 10) || 0;
        }
        rowCount++;
    });

    const visible = document.getElementById('prod-time-value');
    if (!visible) return;

    if (totalMins <= 0) {
        if (!silent) {
            alert('None of the current BOM materials have a production time set.\n\nPlease set "Production Time (mins)" on each raw material first, then try again.');
        }
        return;
    }

    setProdTimeFromMinutes(totalMins);

    // Flash the input to give visual feedback
    visible.style.borderColor = '#22C55E';
    visible.style.boxShadow = '0 0 0 3px rgba(34,197,94,0.2)';
    setTimeout(function() {
        visible.style.borderColor = '#7DD3FC';
        visible.style.boxShadow = 'none';
    }, 1500);
}

function toggleDesignSwitch(wrapper) {
    const cb = wrapper.querySelector('input[type=checkbox]');
    const hidden = wrapper.querySelector('input[type=hidden]');
    const track = wrapper.querySelector('.sw-track');
    const knob = wrapper.querySelector('.sw-knob');
    cb.checked = !cb.checked;
    hidden.value = cb.checked ? '1' : '0';
    track.style.background = cb.checked ? 'var(--accent-color)' : 'var(--border-strong)';
    knob.style.transform = cb.checked ? 'translateX(22px)' : 'translateX(0)';
}

// ── Custom Item Mode: ONE master switch drives all three flags ──
// (enable_production_time, requires_design_file, requires_custom_terms)
function toggleCustomItemSwitch(wrapper) {
    const cb = wrapper.querySelector('input[type=checkbox]');
    applyCustomItemMode(!cb.checked);
}

function setCustomItemFlag(name, on) {
    const cb = document.querySelector('input[name="' + name + '"][type=checkbox]');
    if (cb) cb.checked = on;
    const chip = document.querySelector('.custom-item-chip[data-flag="' + name + '"]');
    if (chip) {
        chip.innerHTML = on
            ? '<i class="fa-solid fa-circle-check" style="font-size:0.7rem;"></i> ON'
            : '<i class="fa-regular fa-circle" style="font-size:0.7rem;"></i> OFF';
        chip.style.background = on ? '#DCFCE7' : '#F1F5F9';
        chip.style.color = on ? '#15803D' : '#94A3B8';
        chip.style.borderColor = on ? '#BBF7D0' : '#E2E8F0';
    }
}

function applyCustomItemMode(on) {
    const cb = document.querySelector('input[name="enable_production_time"][type=checkbox]');
    if (cb) cb.checked = on;
    const hidden = document.querySelector('input[name="enable_production_time"][type=hidden]');
    if (hidden) hidden.value = on ? '1' : '0';
    const wrap = cb ? cb.closest('div[style*="position:relative"]') : null;
    if (wrap) {
        const track = wrap.querySelector('.sw-track');
        const knob = wrap.querySelector('.sw-knob');
        if (track) track.style.background = on ? 'var(--accent-color)' : 'var(--border-strong)';
        if (knob) knob.style.transform = on ? 'translateX(22px)' : 'translateX(0)';
    }

    const state = document.getElementById('prod-time-state');
    const field = document.getElementById('prod-time-field');
    const input = document.getElementById('prod-time-value');
    const unitGrp = document.getElementById('prod-time-unit');
    if (state) state.textContent = on ? 'ON' : 'OFF';
    if (field) field.style.opacity = on ? '1' : '0.5';
    if (input) {
        input.readOnly = !on;
        input.style.background = on ? 'var(--bg-surface)' : 'var(--bg-subtle)';
        input.style.cursor = on ? 'text' : 'not-allowed';
    }
    if (unitGrp) unitGrp.classList.toggle('disabled', !on);

    setCustomItemFlag('requires_design_file', on);
    setCustomItemFlag('requires_custom_terms', on);

    // Also sync the standalone hidden inputs (the visible cards are removed; these carry the actual form values)
    const hiddenDesign = document.getElementById('hidden_requires_design_file');
    if (hiddenDesign) hiddenDesign.value = on ? '1' : '0';
    const hiddenTerms = document.getElementById('hidden_requires_custom_terms');
    if (hiddenTerms) hiddenTerms.value = on ? '1' : '0';

    // Card tint, icon & lock badge follow the mode
    const modeCard = document.getElementById('custom-item-mode-card');
    if (modeCard) { modeCard.style.background = on ? 'var(--accent-soft)' : 'var(--bg-surface)'; modeCard.style.borderColor = on ? 'var(--accent-color)' : 'var(--border-color)'; }
    const modeIcon = document.getElementById('custom-item-icon');
    if (modeIcon) { modeIcon.style.background = on ? 'var(--accent-color)' : 'var(--bg-subtle)'; modeIcon.style.color = on ? 'var(--accent-contrast)' : 'var(--text-subtle)'; }
    const lock = document.getElementById('prod-time-lock');
    if (lock) lock.style.display = on ? 'none' : 'inline-flex';
}

let bomRowIndex = {{ max(1, $product->productMaterials->count()) }};

function addBomRow() {
    const container = document.getElementById('bom-rows-container');
    const row = document.createElement('div');
    row.className = 'bom-row';
    row.style.cssText = 'display:grid; grid-template-columns:2fr 1fr 40px; gap:0.75rem; align-items:end; background:var(--bg-subtle); border:1px solid var(--border-color); padding:0.85rem 1rem; border-radius:10px;';

    let opts = '';
    @foreach($allMaterials as $m)
        opts += `<option value="{{ $m->id }}">{{ addslashes($m->name) }} ({{ $m->unit }})</option>`;
    @endforeach

    row.innerHTML = `
        <div>
            <label style="font-size:0.75rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.25rem;">Raw Material *</label>
            <select name="materials[${bomRowIndex}][material_id]" required style="width:100%; padding:0.6rem 0.85rem; border:1px solid var(--border-color); border-radius:8px; font-size:0.88rem; background:var(--bg-surface); color:var(--text-main); outline:none;">
                ${opts}
            </select>
        </div>
        <div>
            <label style="font-size:0.75rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:0.25rem;">Qty / Unit *</label>
            <input type="number" step="0.0001" name="materials[${bomRowIndex}][quantity_used]" value="1.0000" required style="width:100%; padding:0.6rem 0.85rem; border:1px solid var(--border-color); border-radius:8px; font-size:0.88rem; background:var(--bg-surface); color:var(--text-main); font-weight:700; outline:none;">
        </div>
        <button type="button" onclick="removeBomRow(this)" style="background:var(--danger-soft); border:1px solid var(--danger-border); color:var(--text-danger); border-radius:8px; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer;">
            <i class="fa-solid fa-trash-can" style="font-size:0.82rem;"></i>
        </button>
    `;
    container.appendChild(row);
    enhanceBomRowComboboxes();
    bomRowIndex++;
    autoCalculateFromBom(true);
}

function removeBomRow(btn) {
    const row = btn.closest('.bom-row');
    if (row) {
        row.remove();
        autoCalculateFromBom(true);
    }
}

// Wrap each main-BOM material <select> with a searchable combobox that mirrors into the hidden select,
// so form submission and all BOM readers (autoCalculate, hasValidMaterials, readMainBomMaterials) keep working.
function enhanceBomRowComboboxes() {
    document.querySelectorAll('#bom-rows-container .bom-row').forEach(row => {
        const select = row.querySelector('select[name^="materials["]');
        if (!select || select.dataset.enhanced) return;
        select.dataset.enhanced = '1';
        select.style.display = 'none';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'mat-combo-input';
        input.placeholder = 'Select material...';
        input.autocomplete = 'off';
        const idx = select.selectedIndex;
        const opt = (idx >= 0 && select.options[idx]) ? select.options[idx] : null;
        input.value = (select.value && opt) ? opt.textContent.trim() : '';
        input.dataset.materialId = select.value || '';
        input.style.cssText = 'width:100%; padding:0.6rem 0.85rem; border:1px solid var(--border-strong); border-radius:8px; font-size:0.88rem; background:var(--bg-surface); color:var(--text-main); outline:none;';
        input.addEventListener('input', () => openMatComboPopup(input));
        input.addEventListener('focus', () => openMatComboPopup(input));
        select.parentNode.insertBefore(input, select);
    });
}

// Auto-trigger calculation when BOM materials selection changes
document.addEventListener('DOMContentLoaded', function() {
    const bomContainer = document.getElementById('bom-rows-container');
    if (bomContainer) {
        bomContainer.addEventListener('change', function(e) {
            if (e.target && e.target.tagName === 'SELECT' && e.target.name && e.target.name.includes('material_id')) {
                autoCalculateFromBom(true);
                if (hasValidMaterials()) markMaterialsError(false);
            }
        });
    }
    // Convert existing main-BOM material selects into comboboxes on load
    enhanceBomRowComboboxes();
});

// ──────────────────────────────────────────────
//  JS OPTIONS STATE MANAGEMENT
// ──────────────────────────────────────────────

@php
    $mainBomList = $product->productMaterials->map(fn($pm) => [
        'material_id'   => $pm->material_id,
        'material_name' => $pm->material->name ?? '',
        'unit'          => $pm->material->unit ?? '',
        'quantity_used' => (float) $pm->quantity_used,
    ])->values()->toArray();
@endphp

let productOptions = {!! json_encode($product->options->map(function($o) {
    $hasCustom = $o->materials->isNotEmpty();
    return [
        'id' => $o->id,
        'option_name' => $o->option_name,
        'option_value' => $o->option_value,
        'price_modifier' => (float) $o->price_modifier,
        'image_url' => $o->image_url,
        'has_custom_bom' => $hasCustom,
        'materials' => $hasCustom ? $o->materials->map(fn($m) => [
            'material_id' => $m->id,
            'material_name' => $m->name,
            'unit' => $m->unit,
            'quantity_used' => (float) $m->pivot->quantity_used,
        ])->values()->toArray() : [],
    ];
})->values()->toArray()) !!};

let optionBomRowIndex = 0;
const allMaterials = @json($allMaterials);
const defaultOptionNames = @json($existingOptionNames ?? []);
let currentEditingOptionIdx = null;

/**
 * Reads the current main BOM rows from the DOM and returns them as a
 * materials array compatible with the productOptions.materials format.
 */
function readMainBomMaterials() {
    const rows = document.querySelectorAll('#bom-rows-container .bom-row');
    const inherited = [];

    rows.forEach(row => {
        const select = row.querySelector('select');
        const qtyInput = row.querySelector('input[type=number]');
        if (!select || !qtyInput) return;

        const materialId = parseInt(select.value);
        const qty = parseFloat(qtyInput.value);
        if (!materialId || isNaN(qty) || qty <= 0) return;

        const mat = allMaterials.find(m => m.id == materialId);
        const selectedOpt = (select.options && select.selectedIndex >= 0) ? select.options[select.selectedIndex] : null;
        const optText = selectedOpt ? selectedOpt.text : '';
        const nameMatch = optText.replace(/\s*\([^)]*\)$/, '').trim();
        const unitMatch = (optText.match(/\(([^)]+)\)$/) || [])[1] || '';

        inherited.push({
            material_id: materialId,
            material_name: (mat && mat.name) ? mat.name : (nameMatch || ('Material #' + materialId)),
            unit: (mat && mat.unit) ? mat.unit : unitMatch,
            quantity_used: qty
        });
    });

    return inherited;
}

/**
 * INTENTIONALLY DISABLED: Syncing inherited BOM to options on every BOM input/change
 * caused the options array to be wiped or re-rendered unexpectedly.
 * Inherited BOM preview data is instead resolved at two safe points only:
 *   1. When a NEW option is added (addNewOptionRow) — snapshot of BOM taken at that instant.
 *   2. When the form is SAVED — backend returns fresh option+BOM data in JSON response.
 *
 * DO NOT re-enable live BOM-to-options sync listeners.
 */
function syncInheritedBomToOptions() {
    // No-op: deliberately disabled to prevent options wipe on BOM edits.
}

function syncOptionsHiddenInputs() {
    const container = document.getElementById('options-hidden-inputs-container');
    if (!container) return;

    let html = '<input type="hidden" name="options_present" value="1">';
    if (productOptions && productOptions.length > 0) {
        productOptions.forEach((opt, idx) => {
            const priceMod = isNaN(parseFloat(opt.price_modifier)) ? 0.00 : parseFloat(opt.price_modifier);
            html += `
                <input type="hidden" name="options[${idx}][id]" value="${opt.id || ''}">
                <input type="hidden" name="options[${idx}][option_name]" value="${opt.option_name}">
                <input type="hidden" name="options[${idx}][option_value]" value="${opt.option_value}">
                <input type="hidden" name="options[${idx}][price_modifier]" value="${priceMod}">
                <input type="hidden" name="options[${idx}][has_custom_bom]" value="${opt.has_custom_bom ? '1' : '0'}">
                <input type="hidden" name="options[${idx}][remove_image]" value="${opt.removeImage ? '1' : '0'}">
            `;
            if (opt.has_custom_bom && opt.materials && Array.isArray(opt.materials)) {
                opt.materials.forEach((m, matIdx) => {
                    html += `
                        <input type="hidden" name="options[${idx}][materials][${matIdx}][material_id]" value="${m.material_id}">
                        <input type="hidden" name="options[${idx}][materials][${matIdx}][quantity_used]" value="${m.quantity_used}">
                    `;
                });
            }
        });
    }
    container.innerHTML = html;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function quickAddForCategory(groupName) {
    const nameInput = document.getElementById('new-option-name');
    const valInput = document.getElementById('new-option-value');
    if (!nameInput || !valInput) return;

    nameInput.value = groupName;
    nameInput.style.borderColor = '#CBD5E1';

    valInput.focus();
    valInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function renderOptions() {
    const container = document.getElementById('options-list-container');
    const badge = document.getElementById('options-count-badge');
    if (!container) return;
    container.innerHTML = '';

    if (!productOptions || productOptions.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; padding:2rem 1.25rem; background:var(--bg-page); border:1.5px dashed var(--border-color); border-radius:12px; color:var(--text-subtle); font-size:0.85rem; margin-bottom:1rem; display:flex; flex-direction:column; align-items:center; gap:0.5rem;">
                <i class="fa-solid fa-sliders" style="font-size:1.4rem; opacity:0.35;"></i>
                <span>No options yet. Use the form below to add variant options (e.g. Size, Color).</span>
            </div>
        `;
        if (badge) badge.innerText = '(0)';
        syncOptionNameSelect();
        syncOptionsHiddenInputs();
        return;
    }

    // Group options by option_name (e.g. "Color", "Size")
    const groups = {};
    productOptions.forEach((opt, idx) => {
        const rawName = (opt.option_name || 'General').trim();
        const groupKey = rawName.toLowerCase();
        if (!groups[groupKey]) {
            groups[groupKey] = {
                displayName: rawName,
                items: []
            };
        }
        groups[groupKey].items.push({ opt, originalIndex: idx });
    });

    const categoryKeys = Object.keys(groups);
    if (badge) {
        badge.innerText = `(${productOptions.length})`;
    }

    categoryKeys.forEach(key => {
        const group = groups[key];
        const groupCard = document.createElement('div');
        groupCard.style.cssText = `
            background:var(--bg-surface);
            border:1.5px solid var(--border-color);
            border-radius:12px;
            margin-bottom:1.15rem;
            overflow:hidden;
            box-shadow:0 1px 3px rgba(15,23,42,0.03);
        `;

        const headerTitle = group.displayName.toLowerCase().endsWith('options') 
            ? group.displayName 
            : `${group.displayName} Options`;

        let itemsHtml = '';
        group.items.forEach(({ opt, originalIndex }) => {
            const priceMod = isNaN(parseFloat(opt.price_modifier)) ? 0.00 : parseFloat(opt.price_modifier);
            const priceColor = priceMod > 0 ? '#16A34A' : (priceMod < 0 ? '#DC2626' : '#64748B');
            const priceBg = priceMod > 0 ? 'var(--success-soft)' : (priceMod < 0 ? 'var(--danger-soft)' : 'var(--bg-subtle)');
            const priceBorder = priceMod > 0 ? '#BBF7D0' : (priceMod < 0 ? '#FCA5A5' : '#E2E8F0');
            const priceSign = priceMod >= 0 ? '+' : '';

            // BOM Tag Badges
            let bomTagHtml = '';
            const hasCustomMaterials = opt.has_custom_bom && Array.isArray(opt.materials) && opt.materials.length > 0;

            if (!hasCustomMaterials) {
                bomTagHtml = `
                    <span style="display:inline-flex; align-items:center; gap:0.35rem;
                        background:var(--bg-subtle); color:var(--text-muted); border:1px solid var(--border-color);
                        padding:0.22rem 0.65rem; border-radius:20px;
                        font-size:0.72rem; font-weight:700; white-space:nowrap;">
                        <i class="fa-solid fa-cube" style="font-size:0.65rem; color:var(--text-subtle);"></i> Inherits base materials
                    </span>
                `;
            } else {
                const chipsHtml = opt.materials.map(m => {
                    const qtyStr = String(parseFloat((Number(m.quantity_used) || 0).toFixed(4)));
                    const matName = m.material_name || ('Material #' + (m.material_id || ''));
                    const matUnit = m.unit || '';
                    return `
                        <span style="display:inline-flex; align-items:center; gap:0.25rem;
                            background:var(--bg-surface); color:var(--text-main); border:1px solid var(--brand-violet-soft);
                            padding:0.2rem 0.6rem; border-radius:20px;
                            font-size:0.72rem; font-weight:700; white-space:nowrap;
                            box-shadow:0 1px 2px rgba(124,58,237,0.06);">
                            <i class="fa-solid fa-cubes-stacked" style="font-size:0.6rem; color:var(--text-violet);"></i>
                            +${qtyStr} ${escapeHtml(matUnit)} ${escapeHtml(matName)}
                        </span>
                    `;
                }).join('');

                bomTagHtml = `
                    <span style="display:inline-flex; align-items:center; gap:0.35rem;
                        background:var(--brand-violet-soft); color:var(--text-violet); border:1px solid var(--brand-violet-soft);
                        padding:0.22rem 0.65rem; border-radius:20px;
                        font-size:0.72rem; font-weight:700; white-space:nowrap;">
                        <i class="fa-solid fa-plus-circle" style="font-size:0.65rem; color:var(--text-violet);"></i> Extra Material Add-on (+Qty)
                    </span>
                    ${chipsHtml}
                `;
            }

            itemsHtml += `
                <div style="background:var(--bg-page); border:1px solid var(--border-color); border-radius:10px; padding:0.8rem 1rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; transition:border-color 0.15s, box-shadow 0.15s;"
                    onmouseenter="this.style.borderColor='#BFDBFE'; this.style.boxShadow='0 2px 6px rgba(0,153,255,0.06)';"
                    onmouseleave="this.style.borderColor='#E2E8F0'; this.style.boxShadow='none';">
                    <div style="display:flex; align-items:center; gap:0.75rem; flex:1; min-width:0;">
                        ${buildOptionImageHtml(opt, originalIndex)}
                        <div style="display:flex; flex-direction:column; gap:0.45rem; flex:1; min-width:0;">
                            <div style="display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap;">
                                <span style="font-weight:800; color:var(--text-main); font-size:0.95rem;">
                                    ${escapeHtml(opt.option_value)}
                                </span>
                                <span style="font-size:0.8rem; font-weight:800; color:${priceColor};
                                    background:${priceBg}; border:1px solid ${priceBorder};
                                    padding:0.15rem 0.55rem; border-radius:20px; white-space:nowrap;">
                                    ${priceSign}₱${priceMod.toFixed(2)}
                                </span>
                            </div>
                            <div style="display:flex; align-items:center; gap:0.45rem; flex-wrap:wrap;">
                                ${bomTagHtml}
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.5rem; flex-shrink:0;">
                        <button type="button" onclick="openOptionBomModal(${originalIndex})"
                            style="display:inline-flex; align-items:center; gap:0.35rem;
                                background:var(--accent-soft); border:1.5px solid var(--info-border); color:var(--text-accent);
                                border-radius:8px; padding:0.45rem 0.85rem;
                                font-size:0.78rem; font-weight:700; cursor:pointer; white-space:nowrap;
                                transition:all 0.15s;"
                            onmouseenter="this.style.background='#DBEAFE'; this.style.borderColor='#93C5FD';"
                            onmouseleave="this.style.background='#EFF6FF'; this.style.borderColor='#BFDBFE';"
                            title="Configure extra raw materials for this option">
                            <i class="fa-solid fa-boxes-packing" style="font-size:0.75rem;"></i> Manage Materials
                        </button>
                        <button type="button" onclick="deleteOptionRow(${originalIndex})"
                            style="display:inline-flex; align-items:center; justify-content:center;
                                background:var(--danger-soft); border:1.5px solid var(--danger-border); color:var(--text-danger);
                                border-radius:8px; width:34px; height:34px;
                                font-size:0.8rem; cursor:pointer; transition:all 0.15s;"
                            onmouseenter="this.style.background='#FEE2E2'; this.style.borderColor='#FCA5A5';"
                            onmouseleave="this.style.background='#FEF2F2'; this.style.borderColor='#FECACA';"
                            title="Delete Option">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        groupCard.innerHTML = `
            <div style="padding:0.75rem 1.1rem; background:linear-gradient(135deg,var(--bg-page) 0%,var(--bg-subtle) 100%); border-bottom:1px solid var(--border-color); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                <div style="display:flex; align-items:center; gap:0.55rem;">
                    <span style="width:26px; height:26px; border-radius:7px; background:var(--accent-color); color:var(--text-inverse); display:inline-flex; align-items:center; justify-content:center; font-size:0.72rem; font-weight:800; box-shadow:0 1px 3px rgba(0,153,255,0.25);">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>
                    <span style="font-size:0.95rem; font-weight:800; color:var(--text-main);">
                        ${escapeHtml(headerTitle)}
                    </span>
                    <span style="background:var(--text-disabled); color:var(--text-muted); font-size:0.72rem; font-weight:700; padding:0.15rem 0.55rem; border-radius:20px;">
                        ${group.items.length} value${group.items.length === 1 ? '' : 's'}
                    </span>
                </div>
                <button type="button" onclick="quickAddForCategory('${escapeHtml(group.displayName)}')"
                    style="background:var(--accent-color); color:var(--text-inverse); padding:0.35rem 0.75rem; border-radius:7px; font-size:0.75rem; font-weight:700; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:0.3rem; transition:background 0.15s;"
                    onmouseenter="this.style.background='#0080FF'"
                    onmouseleave="this.style.background='#0099FF'">
                    <i class="fa-solid fa-plus"></i> Add ${escapeHtml(group.displayName)} Value
                </button>
            </div>
            <div style="padding:0.85rem 1rem; display:flex; flex-direction:column; gap:0.6rem;">
                ${itemsHtml}
            </div>
        `;
        container.appendChild(groupCard);
    });

    syncOptionNameSelect();
    syncOptionsHiddenInputs();
}

// Rebuild the Option Name datalist to retain default + active option names
function syncOptionNameSelect() {
    const dd = document.getElementById('option-name-dropdown');
    if (dd && dd.style.display === 'block') renderOptionNameDropdown();
}

// Returns the resolved option name from the option-name input
function getOptionName() {
    const input = document.getElementById('new-option-name');
    return input ? input.value.trim() : '';
}

// ---- Custom Option Name combobox ----
function getOptionNameOptions() {
    const base = Array.isArray(defaultOptionNames) ? defaultOptionNames : [];
    const active = productOptions.map(o => o.option_name);
    // Case-insensitive dedupe: "Size" and "size" are the same name (keep first-seen casing)
    const seen = new Set();
    const result = [];
    [...base, ...active].map(n => (n || '').trim()).filter(Boolean).forEach(n => {
        const key = n.toLowerCase();
        if (!seen.has(key)) { seen.add(key); result.push(n); }
    });
    return result.sort();
}

function renderOptionNameDropdown() {
    const dd = document.getElementById('option-name-dropdown');
    const input = document.getElementById('new-option-name');
    if (!dd || !input) return;

    const term = input.value.trim().toLowerCase();
    const all = getOptionNameOptions();
    const matches = term ? all.filter(n => n.toLowerCase().includes(term)) : all;

    if (matches.length === 0) {
        dd.innerHTML = `<div style="padding:0.55rem 0.75rem; font-size:0.82rem; color:var(--text-subtle);">${term ? 'No existing match &mdash; your typed name will be used' : 'No names yet &mdash; type to add one'}</div>`;
    } else {
        const current = input.value.trim().toLowerCase();
        dd.innerHTML = matches.map(n => {
            const safe = escapeHtml(n);
            const selected = n.toLowerCase() === current;
            const style = selected
                ? 'padding:0.55rem 0.75rem; font-size:0.85rem; color:var(--accent-contrast); background:var(--accent-color); font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:0.5rem;'
                : 'padding:0.55rem 0.75rem; font-size:0.85rem; color:var(--text-main); cursor:pointer; transition:background 0.1s;';
            const hover = selected ? '' : ` onmouseover="this.style.background='var(--accent-soft)'" onmouseout="this.style.background='var(--bg-elevated)'"`;
            const check = selected ? '<i class="fa-solid fa-check" style="font-size:0.72rem;"></i>' : '';
            return `<div data-val="${safe}" onclick="selectOptionName(this)" style="${style}"${hover}>${safe}${check}</div>`;
        }).join('');
    }
    dd.style.display = 'block';
}

function openOptionNameDropdown() { renderOptionNameDropdown(); }

function closeOptionNameDropdown() {
    const dd = document.getElementById('option-name-dropdown');
    if (dd) dd.style.display = 'none';
}

function selectOptionName(el) {
    const input = document.getElementById('new-option-name');
    if (input && el) input.value = el.getAttribute('data-val');
    closeOptionNameDropdown();
}

// ──────────────────────────────────────────────
//  OPTIONAL OPTION IMAGE
//  Files cannot live in hidden inputs, so the picked File is kept on the
//  option object and appended to the FormData when the master form is saved.
// ──────────────────────────────────────────────
const OPTION_IMAGE_MAX_BYTES = 2 * 1024 * 1024;
let pendingNewOptionImage = null;  // file chosen in the "Add New Option Value" form
let pendingImageTargetIdx = null;  // option row targeted by the shared row picker

function isValidOptionImage(file) {
    if (!file) return false;
    if (!/^image\/(png|jpe?g|webp)$/i.test(file.type)) {
        alert('Please choose a PNG, JPG or WEBP image.');
        return false;
    }
    if (file.size > OPTION_IMAGE_MAX_BYTES) {
        alert('That image is larger than 2MB. Please use a smaller file.');
        return false;
    }
    return true;
}

// Local preview wins over the stored image so a swap is visible before saving
function buildOptionImageHtml(opt, idx) {
    const src = opt.previewUrl || opt.image_url || null;
    const label = escapeHtml(opt.option_value);

    if (src) {
        return `
            <div style="position:relative; width:44px; height:44px; flex-shrink:0;">
                <img src="${src}" alt="${label}" title="Click to replace image" onclick="pickOptionRowImage(${idx})"
                    style="width:44px; height:44px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color); background:var(--bg-surface); cursor:pointer; display:block;">
                <button type="button" onclick="event.stopPropagation(); removeOptionImage(${idx})" title="Remove image"
                    style="position:absolute; top:-7px; right:-7px; width:20px; height:20px; border-radius:50%; background:var(--danger-color); color:var(--text-inverse); border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; padding:0; font-size:0.6rem;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        `;
    }

    return `
        <button type="button" onclick="pickOptionRowImage(${idx})" title="Add an optional image"
            style="width:44px; height:44px; flex-shrink:0; border:1.5px dashed var(--border-color); border-radius:8px; background:var(--bg-surface); color:var(--text-subtle); cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:0.85rem; transition:all 0.15s;"
            onmouseenter="this.style.borderColor='#0099FF'; this.style.color='var(--text-accent)';"
            onmouseleave="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-subtle)';">
            <i class="fa-solid fa-image"></i>
        </button>
    `;
}

function renderNewOptionImagePreview(src) {
    const thumb = document.getElementById('new-option-image-thumb');
    const prompt = document.getElementById('new-option-image-prompt');
    if (!thumb || !prompt) return;

    if (src) {
        thumb.querySelector('img').src = src;
        thumb.style.display = 'block';
        prompt.style.display = 'none';
    } else {
        thumb.querySelector('img').removeAttribute('src');
        thumb.style.display = 'none';
        prompt.style.display = '';
    }
}

function handleNewOptionImageSelect(file) {
    if (!isValidOptionImage(file)) return;
    pendingNewOptionImage = file;
    renderNewOptionImagePreview(URL.createObjectURL(file));
}

function handleNewOptionImageDrop(e) {
    const file = e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files[0] : null;
    handleNewOptionImageSelect(file);
}

function clearNewOptionImage() {
    pendingNewOptionImage = null;
    renderNewOptionImagePreview(null);
}

function pickOptionRowImage(idx) {
    pendingImageTargetIdx = idx;
    const input = document.getElementById('option-row-image-input');
    if (input) input.click();
}

function handleOptionRowImageSelect(file) {
    const idx = pendingImageTargetIdx;
    pendingImageTargetIdx = null;
    if (idx === null || !isValidOptionImage(file)) return;

    const opt = productOptions[idx];
    if (!opt) return;

    if (opt.previewUrl) URL.revokeObjectURL(opt.previewUrl);
    opt.imageFile = file;
    opt.previewUrl = URL.createObjectURL(file);
    opt.removeImage = false;
    renderOptions();
}

function removeOptionImage(idx) {
    const opt = productOptions[idx];
    if (!opt) return;

    if (opt.previewUrl) {
        URL.revokeObjectURL(opt.previewUrl);
        opt.previewUrl = null;
    }
    opt.imageFile = null;

    // A stored image still has to be cleared server-side on save
    if (opt.image_url) {
        opt.image_url = null;
        opt.removeImage = true;
    }
    renderOptions();
}

function addNewOptionRow() {
    const nameInput = document.getElementById('new-option-name');
    const valInput  = document.getElementById('new-option-value');
    const priceInput = document.getElementById('new-option-price');
    const errorDiv  = document.getElementById('new-option-error');

    const name = getOptionName();
    const val  = valInput.value.trim();
    const price = parseFloat(priceInput.value);

    // Reset validation styles
    errorDiv.style.display = 'none';
    nameInput.style.borderColor = '#CBD5E1';
    valInput.style.borderColor = '#CBD5E1';

    if (!name || !val) {
        errorDiv.style.display = 'inline-flex';
        if (!name) nameInput.style.borderColor = '#DC2626';
        if (!val) valInput.style.borderColor = '#DC2626';
        return;
    }

    // Case-insensitive: reuse an existing option name so "Size"/"size" don't split into separate groups
    const existing = productOptions.find(o => (o.option_name || '').trim().toLowerCase() === name.toLowerCase());
    const finalName = existing ? existing.option_name : name;

    // Newly added options inherit the main BOM by default with no extra material deductions
    productOptions.push({
        id: null,
        option_name: finalName,
        option_value: val,
        price_modifier: isNaN(price) ? 0.00 : price,
        has_custom_bom: false,
        materials: [],
        image_url: null,
        imageFile: pendingNewOptionImage,
        previewUrl: pendingNewOptionImage ? URL.createObjectURL(pendingNewOptionImage) : null,
        removeImage: false
    });

    // Reset controls (keep the option name so several values can be added in a row)
    valInput.value = '';
    priceInput.value = '0.00';
    pendingNewOptionImage = null;
    clearNewOptionImage();
    closeOptionNameDropdown();
    valInput.focus();

    renderOptions();
}

function deleteOptionRow(idx) {
    const opt = productOptions[idx];
    if (!opt) return;

    // Brand-new option (not yet saved to DB) — remove from UI only
    if (!opt.id) {
        productOptions.splice(idx, 1);
        renderOptions();
        return;
    }

    // Disable the delete button to prevent double-clicks while the request is in flight
    const btn = document.querySelector(`button[onclick="deleteOptionRow(${idx})"]`);
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    }

    const deleteUrl = `/super-admin/products/options/${opt.id}`;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                   || document.querySelector('input[name="_token"]')?.value
                   || '{{ csrf_token() }}';

    fetch(deleteUrl, {
        method: 'POST',           // Laravel tunnel: POST + _method=DELETE
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
        },
        body: '_method=DELETE',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            productOptions.splice(idx, 1);
            renderOptions();
        } else {
            console.error('Delete failed:', data);
            alert('Failed to delete option. Please try again.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
            }
        }
    })
    .catch(err => {
        console.error('Delete error:', err);
        alert('Network error — could not delete option. Please try again.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
        }
    });
}

function openOptionBomModal(optionIdx) {
    currentEditingOptionIdx = optionIdx;
    const option = productOptions[optionIdx];
    
    document.getElementById('modal-option-title').innerText = `Add-on for ${option.option_name}: ${option.option_value}`;
    document.getElementById('modal-option-subtitle').innerText = `Extra materials added on top of the base product.`;
    
    const container = document.getElementById('option-bom-rows');
    container.innerHTML = '';
    optionBomRowIndex = 0;

    const materialsToLoad = (option.has_custom_bom && option.materials && option.materials.length > 0)
        ? option.materials
        : [];

    if (materialsToLoad.length > 0) {
        materialsToLoad.forEach(m => {
            addOptionBomRow(m.material_id, m.quantity_used);
        });
    }

    document.getElementById('option-bom-modal').style.display = 'flex';
}

function closeOptionBomModal() {
    document.getElementById('option-bom-modal').style.display = 'none';
    closeMatComboPopup();
    currentEditingOptionIdx = null;
}

function resetOptionBomToMain() {
    if (currentEditingOptionIdx === null) return;
    productOptions[currentEditingOptionIdx].has_custom_bom = false;
    productOptions[currentEditingOptionIdx].materials = [];
    closeOptionBomModal();
    renderOptions();
}

// Clear all extra-material rows in the modal (user then clicks Apply to persist)
function clearOptionBomRows() {
    const container = document.getElementById('option-bom-rows');
    if (container) container.innerHTML = '';
}

function addOptionBomRow(selectedMaterialId = null, quantity = 1.0000) {
    const container = document.getElementById('option-bom-rows');
    const row = document.createElement('div');
    row.className = 'option-bom-row';
    row.style.cssText = 'display:grid; grid-template-columns:1fr 110px 36px; gap:0.5rem; align-items:center; padding:0.45rem 0.1rem; border-bottom:1px solid #F1F5F9;';

    // Pre-fill the combobox label when editing an existing material row
    let selectedLabel = '';
    if (selectedMaterialId) {
        const sm = allMaterials.find(m => m.id == selectedMaterialId);
        if (sm) selectedLabel = sm.name + ' (' + sm.unit + ')';
    }

    row.innerHTML = `
        <div class="mat-combo" style="position:relative;">
            <input type="text" class="mat-combo-input" placeholder="Select material..." autocomplete="off"
                value="${escapeHtml(selectedLabel)}" data-material-id="${selectedMaterialId || ''}"
                oninput="openMatComboPopup(this)" onfocus="openMatComboPopup(this)"
                style="width:100%; padding:0.55rem 0.7rem; border:1px solid var(--border-color); border-radius:8px; font-size:0.88rem; background:var(--bg-surface); color:var(--text-main); outline:none;">
        </div>
        <input type="number" step="0.0001" name="modal_materials[${optionBomRowIndex}][quantity_used]" value="${parseFloat(Number(quantity).toFixed(4))}" required style="width:100%; padding:0.55rem 0.7rem; border:1px solid var(--border-color); border-radius:8px; font-size:0.88rem; background:var(--bg-surface); color:var(--text-main); font-weight:700; outline:none; text-align:center;">
        <button type="button" onclick="this.closest('.option-bom-row').remove()" style="background:var(--bg-surface); border:1px solid var(--border-color); color:var(--text-subtle); width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.15s;" onmouseover="this.style.color='#DC2626'; this.style.borderColor='#FCA5A5'" onmouseout="this.style.color='#94A3B8'; this.style.borderColor='#E2E8F0'">
            <i class="fa-solid fa-trash-can" style="font-size:0.85rem;"></i>
        </button>
    `;
    container.appendChild(row);
    optionBomRowIndex++;
}

// ---- Material combobox (Add-on modal rows): shared fixed popup to escape modal scroll clipping ----
let activeMatInput = null;

function getMatComboPopup() {
    let p = document.getElementById('mat-combo-popup');
    if (!p) {
        p = document.createElement('div');
        p.id = 'mat-combo-popup';
        p.style.cssText = 'display:none; position:fixed; background:var(--bg-elevated); border:1px solid var(--border-color); border-radius:8px; box-shadow:0 8px 20px rgba(15,23,42,0.15); max-height:240px; overflow-y:auto; z-index:10000;';
        document.body.appendChild(p);
    }
    return p;
}

function openMatComboPopup(input) {
    activeMatInput = input;
    const p = getMatComboPopup();
    const rect = input.getBoundingClientRect();
    p.style.left = rect.left + 'px';
    p.style.width = rect.width + 'px';
    // If the field already holds a committed full selection, show ALL materials (browsing);
    // only filter while the user is typing a partial term.
    const val = input.value.trim();
    const isCommitted = allMaterials.some(m => (m.name + ' (' + m.unit + ')') === val);
    renderMatComboPopup(isCommitted ? '' : val.toLowerCase());
    p.style.display = 'block';

    // Flip upward when there is not enough room below the field (avoid off-screen overlap)
    const h = p.offsetHeight;
    const spaceBelow = window.innerHeight - rect.bottom;
    if (spaceBelow < h + 8 && rect.top > h + 8) {
        p.style.top = (rect.top - h - 4) + 'px';
    } else {
        p.style.top = (rect.bottom + 4) + 'px';
    }
}

function renderMatComboPopup(term) {
    const p = getMatComboPopup();
    const currentId = activeMatInput ? String(activeMatInput.dataset.materialId || '') : '';
    const matches = allMaterials.filter(m => {
        const label = (m.name + ' (' + m.unit + ')').toLowerCase();
        return !term || label.includes(term);
    });

    if (matches.length === 0) {
        p.innerHTML = `<div style="padding:0.55rem 0.75rem; font-size:0.82rem; color:var(--text-subtle);">No matching material</div>`;
        return;
    }

    p.innerHTML = matches.map(m => {
        const label = escapeHtml(m.name + ' (' + m.unit + ')');
        const selected = String(m.id) === currentId;
        const style = selected
            ? 'padding:0.55rem 0.75rem; font-size:0.85rem; color:var(--accent-contrast); background:var(--accent-color); font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:0.5rem;'
            : 'padding:0.55rem 0.75rem; font-size:0.85rem; color:var(--text-main); cursor:pointer; transition:background 0.1s;';
        const hover = selected ? '' : ` onmouseover="this.style.background='var(--accent-soft)'" onmouseout="this.style.background='var(--bg-elevated)'"`;
        const check = selected ? '<i class="fa-solid fa-check" style="font-size:0.72rem;"></i>' : '';
        return `<div data-id="${m.id}" data-label="${label}" onclick="selectMatCombo(this)" style="${style}"${hover}>${label}${check}</div>`;
    }).join('');
}

function selectMatCombo(el) {
    if (activeMatInput && el) {
        const id = el.getAttribute('data-id');
        activeMatInput.value = el.getAttribute('data-label');
        activeMatInput.dataset.materialId = id;
        activeMatInput.style.borderColor = '#CBD5E1';
        // Mirror into an adjacent hidden native select (main BOM rows) so form submit + readers keep working
        const hidden = activeMatInput.parentElement ? activeMatInput.parentElement.querySelector('select') : null;
        if (hidden) {
            hidden.value = id;
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
    closeMatComboPopup();
}

function closeMatComboPopup() {
    const p = document.getElementById('mat-combo-popup');
    if (p) p.style.display = 'none';
    activeMatInput = null;
}

function saveOptionBomFromModal() {
    if (currentEditingOptionIdx === null) return;
    const container = document.getElementById('option-bom-rows');
    const rows = container.querySelectorAll('.option-bom-row');
    // Aggregate by material: if the same material appears in multiple rows,
    // automatically combine (sum) their quantities into a single entry.
    const merged = {};

    rows.forEach(row => {
        const materialInput = row.querySelector('.mat-combo-input');
        const qtyInput = row.querySelector('input[type=number]');
        if (!materialInput || !qtyInput) return;
        const materialId = parseInt(materialInput.dataset.materialId || '0');
        const qty = parseFloat(qtyInput.value);

        if (materialId && qty > 0) {
            if (merged[materialId]) {
                merged[materialId].quantity_used += qty;
            } else {
                const mat = allMaterials.find(m => m.id === materialId);
                merged[materialId] = {
                    material_id: materialId,
                    material_name: mat ? mat.name : '',
                    unit: mat ? mat.unit : '',
                    quantity_used: qty
                };
            }
        }
    });

    const newMaterials = Object.values(merged);

    if (newMaterials.length > 0) {
        productOptions[currentEditingOptionIdx].materials = newMaterials;
        productOptions[currentEditingOptionIdx].has_custom_bom = true;
    } else {
        productOptions[currentEditingOptionIdx].materials = [];
        productOptions[currentEditingOptionIdx].has_custom_bom = false;
    }

    closeOptionBomModal();
    renderOptions();
}

// ──────────────────────────────────────────────
//  MASTER AJAX SAVE & SCROLL POSITION PRESERVATION
// ──────────────────────────────────────────────

window.addEventListener('DOMContentLoaded', () => {
    const scrollPos = sessionStorage.getItem('admin_product_edit_scroll');
    if (scrollPos) {
        window.scrollTo({
            top: parseInt(scrollPos),
            behavior: 'auto'
        });
        sessionStorage.removeItem('admin_product_edit_scroll');
    }
    
    renderOptions();

    // Prevent Enter key in option inputs from submitting the master form
    ['new-option-value', 'new-option-price', 'new-option-name'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addNewOptionRow();
                }
            });
        }
    });

    // Close the Option Name combobox when clicking outside of it
    document.addEventListener('click', (e) => {
        const combo = document.getElementById('option-name-combo');
        if (combo && !combo.contains(e.target)) closeOptionNameDropdown();

        // Close the material combobox popup when clicking outside it or its input
        const matPopup = document.getElementById('mat-combo-popup');
        if (matPopup && matPopup.style.display === 'block'
            && !matPopup.contains(e.target) && !(e.target.closest && e.target.closest('.mat-combo-input'))) {
            closeMatComboPopup();
        }
    });

    // Close the material combobox popup when the page/modal scrolls (fixed popup would misalign),
    // but NOT when scrolling inside the popup's own list.
    window.addEventListener('scroll', (e) => {
        const p = document.getElementById('mat-combo-popup');
        if (p && e.target === p) return;
        closeMatComboPopup();
    }, true);
});

// Require at least one selected raw material before the product can be saved
function hasValidMaterials() {
    const selects = document.querySelectorAll('#bom-rows-container select[name^="materials["]');
    for (const s of selects) {
        if (s.value && s.value.trim() !== '') return true;
    }
    return false;
}

function markMaterialsError(show) {
    const heading = document.getElementById('materials-heading');
    const icon = heading ? heading.querySelector('i') : null;
    const hint = document.getElementById('materials-hint');
    const card = document.getElementById('materials-card');
    if (heading) heading.style.color = show ? '#DC2626' : 'var(--text-main)';
    if (icon) icon.style.color = show ? '#DC2626' : '#0099FF';
    if (hint) hint.style.display = show ? 'inline-flex' : 'none';
    if (card) { card.style.borderColor = show ? '#DC2626' : ''; card.style.boxShadow = show ? '0 0 0 3px rgba(220,38,38,0.12)' : ''; }
}

document.getElementById('master-product-form').addEventListener('submit', function(e) {
    e.preventDefault();

    // Ensure all options and custom/inherited BOMs are cleanly serialized into hidden inputs
    syncOptionsHiddenInputs();

    // Block save when no raw material is set: highlight Materials in red and scroll to it
    if (!hasValidMaterials()) {
        markMaterialsError(true);
        const matErr = document.getElementById('save-error-msg');
        if (matErr) { matErr.style.display = 'inline-flex'; matErr.style.opacity = '1'; }
        const matCard = document.getElementById('materials-card');
        if (matCard) matCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    markMaterialsError(false);

    const form = e.target;
    const formData = new FormData(form);

    // Newly picked option images travel as real files, keyed by the same index
    // the backend reads back with options.{index}.image
    productOptions.forEach((opt, idx) => {
        if (opt.imageFile) formData.append(`options[${idx}][image]`, opt.imageFile);
    });

    const saveBtn = document.getElementById('master-save-btn');
    const statusMsg = document.getElementById('save-status-msg');
    const errorMsg = document.getElementById('save-error-msg');

    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    statusMsg.style.display = 'none';
    statusMsg.style.opacity = '1';
    errorMsg.style.display = 'none';

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';

        if (data.success) {
            statusMsg.style.display = 'inline-flex';
            statusMsg.style.opacity = '1';

            // 1. Update options JS state and render
            if (data.options) {
                productOptions.forEach(o => { if (o.previewUrl) URL.revokeObjectURL(o.previewUrl); });
                productOptions = data.options;
                renderOptions();
            }

            // 2. Update product image preview if new image uploaded
            if (data.image_url) {
                const previewContainer = document.getElementById('product-image-preview-container');
                if (previewContainer) {
                    previewContainer.innerHTML = `
                        <img src="${data.image_url}" alt="Product Image" style="width:56px; height:56px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color);">
                    `;
                }
            }

            // 3. Reset image file input field
            const fileInput = form.querySelector('input[type=file][name=image]');
            if (fileInput) {
                fileInput.value = '';
            }

            // 4. Fade out status message after 3 seconds
            setTimeout(() => {
                statusMsg.style.transition = 'opacity 0.5s';
                statusMsg.style.opacity = '0';
                setTimeout(() => {
                    statusMsg.style.display = 'none';
                    statusMsg.style.opacity = '1';
                }, 500);
            }, 3000);
        } else {
            errorMsg.style.display = 'inline-flex';
        }
    })
    .catch(error => {
        console.error('Error saving product:', error);
        errorMsg.style.display = 'inline-flex';
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
    });
}); // ← closes: document.getElementById('master-product-form').addEventListener('submit', ...)

// ── Product media: images + showcase videos (global scope) ──
const galleryUploadUrl = '{{ route("super-admin.products.gallery.upload", $product->id) }}';
const videoUploadUrl = '{{ route("super-admin.products.videos.upload", $product->id) }}';
const mediaReorderUrl = '{{ route("super-admin.products.media-reorder", $product->id) }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function uploadGalleryImages(files) {
    const list = files ? Array.from(files).filter(f => f.type.startsWith('image/')) : [];
    if (!list.length) return;
    const prompt = document.getElementById('media-upload-prompt');
    const uploading = document.getElementById('media-uploading');
    prompt.style.display = 'none';
    uploading.style.display = 'flex';

    const fd = new FormData();
    list.forEach(f => fd.append('gallery_images[]', f));

    fetch(galleryUploadUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: fd,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            data.images.forEach(img => addThumbToGrid(img));
            updateImageCountLabel();
            showGalleryDivider();
            refreshMediaGrid();
        }
    })
    .catch(() => alert('Upload failed. Please try again.'))
    .finally(() => {
        prompt.style.display = '';
        uploading.style.display = 'none';
        document.getElementById('edit-img-dropzone').style.borderColor = '#CBD5E1';
        document.getElementById('edit-img-dropzone').style.background = 'var(--bg-subtle)';
    });
}

function addThumbToGrid(img) {
    const grid = document.getElementById('media-grid');
    grid.classList.add('has-media');

    const div = document.createElement('div');
    div.className = 'gallery-thumb';
    div.dataset.id = img.id;
    div.style.cssText = `position:relative; border-radius:10px; overflow:hidden; border:2px solid ${img.is_primary ? 'var(--accent-color)' : 'var(--border-color)'}; aspect-ratio:1; background:var(--bg-subtle);`;
    div.innerHTML = `
        <img src="${img.url}" alt="" style="width:100%; height:100%; object-fit:cover; display:block;">
        <span class="media-num"></span>
        <div class="thumb-actions">
            <button type="button" class="thumb-btn thumb-btn-delete" onclick="deleteGalleryImage(${img.id}, this)" title="Delete image"><i class="fa-solid fa-trash-can"></i></button>
        </div>`;
    div.setAttribute('draggable', 'true');
    div.addEventListener('dragstart', function (e) { mediaDragStart(e, this); });
    div.addEventListener('dragend', function (e) { mediaDragEnd(e, this); });
    grid.appendChild(div);
}


function deleteGalleryImage(imgId, btn) {
    if (!confirm('Delete this image?')) return;
    btn.disabled = true;
    fetch(`/super-admin/products/gallery/${imgId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const thumb = document.querySelector(`.gallery-thumb[data-id="${imgId}"]`);
            if (thumb) thumb.remove();
            const grid = document.getElementById('media-grid');
            if (!grid.querySelector('.gallery-thumb, .video-thumb')) hideGalleryDivider();
            updateImageCountLabel();
            refreshMediaGrid();
            persistMediaOrder();
        } else {
            btn.disabled = false;
        }
    })
    .catch(() => { btn.disabled = false; });
}

// ── Main badge helpers (Main = first item; set automatically) ──
function ensureMainBadge(t) {
    if (t.querySelector('.primary-badge')) return;
    const badge = document.createElement('span');
    badge.className = 'primary-badge';
    badge.style.cssText = 'position:absolute; top:4px; left:4px; background:#0099FF; color:#FFF; font-size:0.58rem; font-weight:800; padding:1px 5px; border-radius:4px; text-transform:uppercase; pointer-events:none; z-index:4;';
    badge.textContent = 'Main';
    t.insertBefore(badge, t.firstChild);
}

function videoFormatBadgeHtml(t) {
    const ext = (t.dataset.ext || 'VID').toUpperCase();
    return '<span class="video-fmt-badge" style="position:absolute; top:4px; left:4px; background:rgba(0,153,255,0.92); color:var(--text-inverse); font-size:0.55rem; font-weight:800; padding:1px 5px; border-radius:4px; pointer-events:none; z-index:4;"><i class="fa-solid fa-play" style="font-size:0.5rem; margin-right:2px;"></i>' + ext + '</span>';
}

// Re-derive Main (first), format badges, and blue border for the whole grid.
function refreshMediaGrid() {
    const grid = document.getElementById('media-grid');
    if (!grid) return;
    const thumbs = grid.querySelectorAll('.gallery-thumb, .video-thumb');
    thumbs.forEach((t, idx) => {
        const isFirst = idx === 0;
        const isVideo = t.classList.contains('video-thumb');
        t.style.borderColor = isFirst ? '#0099FF' : '#E2E8F0';
        const existing = t.querySelector('.primary-badge');
        if (existing) existing.remove();
        const fmt = t.querySelector('.video-fmt-badge');
        if (fmt) fmt.remove();
        if (isFirst) {
            ensureMainBadge(t);
        } else if (isVideo) {
            t.insertAdjacentHTML('afterbegin', videoFormatBadgeHtml(t));
        }
    });
}

// ── Drag to reorder; the FIRST item automatically becomes Main ──
let _dragEl = null;
function mediaDragStart(e, el) {
    _dragEl = el;
    el.classList.add('media-thumb-dragging');
    e.dataTransfer.effectAllowed = 'move';
    try { e.dataTransfer.setData('text/plain', el.dataset.id); } catch (err) {}
}
function mediaDragEnd(e, el) {
    el.classList.remove('media-thumb-dragging');
    document.querySelectorAll('.media-thumb-drop-target').forEach(x => x.classList.remove('media-thumb-drop-target'));
    _dragEl = null;
    persistMediaOrder();
}
function mediaDragOver(e) {
    e.preventDefault();
    const grid = document.getElementById('media-grid');
    if (!grid || !grid.classList.contains('has-media')) return;
    const after = getDragAfterElement(grid, e.clientX);
    if (!after && _dragEl) { grid.appendChild(_dragEl); return; }
    if (after && _dragEl && after !== _dragEl) { grid.insertBefore(_dragEl, after); }
}
function getDragAfterElement(container, x) {
    const els = [...container.querySelectorAll('.gallery-thumb:not(.media-thumb-dragging), .video-thumb:not(.media-thumb-dragging)')];
    return els.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = x - box.left - box.width / 2;
        if (offset < 0 && offset > closest.offset) { return { offset, element: child }; }
        return closest;
    }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
}
function mediaDrop(e) {
    e.preventDefault();
    refreshMediaGrid();
}

// Persist current DOM order to the server (first item becomes Main there too).
let _lastSavedOrder = null;
function persistMediaOrder() {
    const grid = document.getElementById('media-grid');
    if (!grid) return;
    const order = [...grid.querySelectorAll('.gallery-thumb, .video-thumb')].map(el => ({
        type: el.classList.contains('video-thumb') ? 'video' : 'image',
        id: parseInt(el.dataset.id, 10),
    }));
    const signature = order.map(o => o.type + ':' + o.id).join(',');
    if (signature === _lastSavedOrder) return; // no change since last save
    fetch(mediaReorderUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ order }),
    })
    .then(r => {
        if (!r.ok) { throw new Error('Reorder save failed (' + r.status + ')'); }
        _lastSavedOrder = signature;
        return r.json();
    })
    .then(() => refreshMediaGrid())
    .catch(err => console.error(err));
}

// ── Helper: update "X saved" label (images + videos) ──
function updateImageCountLabel() {
    const count = document.querySelectorAll('.gallery-thumb, .video-thumb').length;
    const countSpan = document.querySelector('#gallery-count-span');
    if (countSpan) countSpan.textContent = `(${count} saved)`;
}

// ── Helper: show/hide divider between grid and dropzone ──
function showGalleryDivider() {
    let divider = document.getElementById('gallery-divider');
    if (!divider) {
        divider = document.createElement('div');
        divider.id = 'gallery-divider';
        divider.style.cssText = 'border-top:1px dashed #E2E8F0; margin:0 0.85rem;';
        const dropzone = document.getElementById('edit-img-dropzone');
        dropzone.parentNode.insertBefore(divider, dropzone);
    }
    divider.style.display = '';
}

function hideGalleryDivider() {
    const divider = document.getElementById('gallery-divider');
    if (divider) divider.style.display = 'none';
}

// ── Unified media dropzone: routes files by type to the image or video uploader ──
function handleMediaFiles(files) {
    const list = Array.from(files);
    if (!list.length) return;
    const images = list.filter(f => f.type.startsWith('image/'));
    const videos = list.filter(f => f.type.startsWith('video/') || /\.(mp4|webm|mov|avi|m4v)$/i.test(f.name));
    if (images.length) uploadGalleryImages(images);
    if (videos.length) uploadProductVideos(videos);
    if (!images.length && !videos.length) alert('No supported files selected. Use PNG/JPG/WEBP images or MP4/WEBM/MOV/AVI videos.');
}

function handleMediaDrop(e) {
    handleMediaFiles(e.dataTransfer.files);
}

function uploadProductVideos(files) {
    const list = files ? Array.from(files).filter(f => f.type.startsWith('video/') || /\.(mp4|webm|mov|avi|m4v)$/i.test(f.name)) : [];
    if (!list.length) return;
    const prompt = document.getElementById('media-upload-prompt');
    const uploading = document.getElementById('media-uploading');
    prompt.style.display = 'none';
    uploading.style.display = 'flex';

    const fd = new FormData();
    list.forEach(f => fd.append('product_videos[]', f));

    fetch(videoUploadUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: fd,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            data.videos.forEach(v => addVideoThumbToGrid(v));
            updateImageCountLabel();
            showGalleryDivider();
            refreshMediaGrid();
        } else if (data.message) {
            alert(data.message);
        }
    })
    .catch(() => alert('Video upload failed. Check the file size/type and try again.'))
    .finally(() => {
        prompt.style.display = '';
        uploading.style.display = 'none';
    });
}

function addVideoThumbToGrid(v) {
    const grid = document.getElementById('media-grid');
    grid.classList.add('has-media');

    const div = document.createElement('div');
    div.className = 'video-thumb';
    div.dataset.id = v.id;
    div.dataset.ext = (v.ext || 'vid').toLowerCase();
    div.style.cssText = `position:relative; border-radius:10px; overflow:hidden; border:2px solid var(--border-color); aspect-ratio:1; background:#0F172A;`;
    div.innerHTML = `
        <video src="${v.url}#t=0.5" preload="metadata" muted playsinline style="width:100%; height:100%; object-fit:cover; display:block; pointer-events:none;"></video>
        <span class="media-num"></span>
        <div class="thumb-actions">
            <button type="button" class="thumb-btn thumb-btn-delete" onclick="deleteProductVideo(${v.id}, this)" title="Delete video"><i class="fa-solid fa-trash-can"></i></button>
        </div>`;
    div.setAttribute('draggable', 'true');
    div.addEventListener('dragstart', function (e) { mediaDragStart(e, this); });
    div.addEventListener('dragend', function (e) { mediaDragEnd(e, this); });
    grid.appendChild(div);
}
function deleteProductVideo(vidId, btn) {
    if (!confirm('Delete this video?')) return;
    btn.disabled = true;
    fetch(`/super-admin/products/videos/${vidId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const thumb = document.querySelector(`.video-thumb[data-id="${vidId}"]`);
            if (thumb) thumb.remove();
            const grid = document.getElementById('media-grid');
            if (!grid.querySelector('.gallery-thumb, .video-thumb')) hideGalleryDivider();
            updateImageCountLabel();
            refreshMediaGrid();
            persistMediaOrder();
        } else {
            btn.disabled = false;
        }
    })
    .catch(() => { btn.disabled = false; });
}
</script>
@endpush
