@extends('layouts.admin')

@section('title', 'Product Catalog Management - Staff Panel')

@section('content')
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
            Products Offered Management
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Operational staff can add or edit product info and status. (Core base pricing & product deletions are reserved for Super Admin).</p>
    </div>
</div>

<!-- STAFF ADD PRODUCT MODAL -->
<div id="add-product-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: flex-start; justify-content: center; backdrop-filter: blur(4px); padding: 2rem 1rem; overflow-y: auto;">
    <div style="background: var(--bg-surface); padding: 0; border-radius: 16px; max-width: 640px; width: 100%; text-align: left; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); margin: auto 0;">
        <!-- Modal Header (Sticky) -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 1.75rem; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; background: var(--bg-surface); border-radius: 16px 16px 0 0; z-index: 10;">
            <h4 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-plus-circle" style="color: var(--text-accent); font-size: 1.35rem;"></i> Add New Print Product
            </h4>
            <button type="button" onclick="document.getElementById('add-product-modal').style.display='none';" style="background: var(--bg-subtle); border: none; width: 36px; height: 36px; border-radius: 50%; font-size: 1.25rem; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; transition: all 0.15s ease;" onmouseover="this.style.background='#E2E8F0'; this.style.color='#0F172A';" onmouseout="this.style.background='#F1F5F9'; this.style.color='#64748B';">&times;</button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.25rem; padding: 1.75rem;">
            @csrf
            <div>
                <label style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.45rem;">Product Name *</label>
                <input type="text" name="name" required placeholder="e.g. Mug Printing" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.95rem; outline: none; background: var(--bg-surface); color: var(--text-main); transition: border-color 0.15s ease;" onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='#CBD5E1';">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div>
                    <label style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.45rem;">Category *</label>
                    <select name="category" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.95rem; background: var(--bg-surface); color: var(--text-main); cursor: pointer; outline: none; transition: border-color 0.15s ease;" onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='#CBD5E1';">
                        <option value="Apparel">Apparel (T-Shirts)</option>
                        <option value="Identification">Identification (ID Cards)</option>
                        <option value="Marketing">Marketing (Business Cards)</option>
                        <option value="Services">Services (Lamination)</option>
                        <option value="Other">Other Print Products</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.45rem;">Initial Base Price (₱) *</label>
                    <input type="number" step="0.01" name="price" required placeholder="e.g. 150.00" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.95rem; outline: none; background: var(--bg-surface); color: var(--text-main); font-weight: 600; transition: border-color 0.15s ease;" onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='#CBD5E1';">
                </div>
            </div>

            <div>
                <label style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.45rem;">Description</label>
                <textarea name="description" rows="3" placeholder="Description of the product..." style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.95rem; outline: none; background: var(--bg-surface); color: var(--text-main); resize: vertical; transition: border-color 0.15s ease; font-family: inherit;" onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='#CBD5E1';"></textarea>
            </div>

            <div>
                <label style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.55rem;">
                    Product Image *
                </label>
                <div id="product-image-dropzone-admin" style="border: 2px dashed var(--border-color); border-radius: 12px; padding: 1.75rem 1rem; text-align: center; background: var(--bg-page); cursor: pointer; transition: all 0.2s ease; position: relative;" onclick="document.getElementById('product-image-file-input-admin').click();" ondragover="event.preventDefault(); this.style.borderColor='#0099FF'; this.style.background='#E6F4FF';" ondragleave="this.style.borderColor='#CBD5E1'; this.style.background='#F8FAFC';" ondrop="event.preventDefault(); this.style.borderColor='#CBD5E1'; this.style.background='#F8FAFC'; handleFileDropAdmin(event);">
                    
                    <input type="file" name="image" id="product-image-file-input-admin" accept="image/*" required style="display: none;" onchange="previewProductImageAdmin(this)">

                    <div id="dropzone-prompt-admin">
                        <div style="width: 56px; height: 56px; background: var(--accent-soft); color: var(--text-accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto; font-size: 1.5rem;">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <div style="font-size: 0.95rem; font-weight: 700; color: var(--text-main);">
                            Click to browse or drag & drop product image *
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.35rem;">
                            PNG, JPG, WEBP up to 5MB (Required)
                        </div>
                    </div>

                    <div id="dropzone-preview-admin" style="display: none; align-items: center; justify-content: center; gap: 1.25rem;">
                        <img id="preview-img-element-admin" src="#" alt="Preview" style="max-height: 80px; max-width: 120px; border-radius: 10px; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--border-color);">
                        <div style="text-align: left;">
                            <div id="preview-filename-admin" style="font-size: 0.9rem; font-weight: 700; color: var(--text-main); max-width: 220px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"></div>
                            <div id="preview-filesize-admin" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;"></div>
                            <span style="font-size: 0.78rem; color: var(--text-success); font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem; margin-top: 0.35rem;">
                                <i class="fa-solid fa-circle-check"></i> Image attached
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            function previewProductImageAdmin(input) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('preview-img-element-admin').src = e.target.result;
                        document.getElementById('preview-filename-admin').textContent = file.name;
                        document.getElementById('preview-filesize-admin').textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                        document.getElementById('dropzone-prompt-admin').style.display = 'none';
                        document.getElementById('dropzone-preview-admin').style.display = 'flex';
                    };
                    reader.readAsDataURL(file);
                }
            }
            function handleFileDropAdmin(e) {
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    const fileInput = document.getElementById('product-image-file-input-admin');
                    fileInput.files = e.dataTransfer.files;
                    previewProductImageAdmin(fileInput);
                }
            }
            </script>

            <!-- Requires Design File Toggle -->
            <div style="background:var(--bg-page); border:1px solid var(--border-color); border-radius:12px; padding:1rem 1.15rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">
                <div style="flex:1;">
                    <div style="font-size:0.88rem; font-weight:700; color:var(--text-main); display:flex; align-items:center; gap:0.45rem;">
                        <i class="fa-solid fa-file-image" style="color:var(--text-accent);"></i> Requires Customer Design File
                    </div>
                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.25rem; line-height:1.4;">
                        Customers must upload a design/artwork file when ordering.
                    </div>
                </div>
                <div style="position:relative; width:48px; height:26px; flex-shrink:0; cursor:pointer;" onclick="toggleAdminSwitch(this)">
                    <input type="hidden" name="requires_design_file" value="0">
                    <input type="checkbox" name="requires_design_file" value="1" style="position:absolute; opacity:0; width:0; height:0;">
                    <div class="sw-track" style="position:absolute; inset:0; background:var(--text-disabled); border-radius:9999px; transition:background 0.2s;"></div>
                    <div class="sw-knob" style="position:absolute; top:3px; left:3px; width:20px; height:20px; background:var(--bg-surface); border-radius:50%; box-shadow:0 1px 3px rgba(0,0,0,0.2); transition:transform 0.2s;"></div>
                </div>
            </div>

            <!-- Requires Custom Terms & Conditions Toggle -->
            <div style="background:var(--bg-page); border:1px solid var(--border-color); border-radius:12px; padding:1rem 1.15rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">
                <div style="flex:1;">
                    <div style="font-size:0.88rem; font-weight:700; color:var(--text-main); display:flex; align-items:center; gap:0.45rem;">
                        <i class="fa-solid fa-file-contract" style="color:var(--text-accent);"></i> Mandatory Custom Terms Agreement
                    </div>
                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.25rem; line-height:1.4;">
                        Require customer to agree to custom terms &amp; conditions at checkout.
                    </div>
                </div>
                <div style="position:relative; width:48px; height:26px; flex-shrink:0; cursor:pointer;" onclick="toggleAdminSwitch(this)">
                    <input type="hidden" name="requires_custom_terms" value="0">
                    <input type="checkbox" name="requires_custom_terms" value="1" style="position:absolute; opacity:0; width:0; height:0;">
                    <div class="sw-track" style="position:absolute; inset:0; background:var(--text-disabled); border-radius:9999px; transition:background 0.2s;"></div>
                    <div class="sw-knob" style="position:absolute; top:3px; left:3px; width:20px; height:20px; background:var(--bg-surface); border-radius:50%; box-shadow:0 1px 3px rgba(0,0,0,0.2); transition:transform 0.2s;"></div>
                </div>
            </div>

            <script>
            function toggleAdminSwitch(wrapper) {
                const cb = wrapper.querySelector('input[type=checkbox]');
                const hidden = wrapper.querySelector('input[type=hidden]');
                const track = wrapper.querySelector('.sw-track');
                const knob = wrapper.querySelector('.sw-knob');
                cb.checked = !cb.checked;
                hidden.value = cb.checked ? '1' : '0';
                track.style.background = cb.checked ? '#0099FF' : '#CBD5E1';
                knob.style.transform = cb.checked ? 'translateX(22px)' : 'translateX(0)';
            }
            </script>

            <!-- Modal Footer (Sticky Buttons) -->
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--border-color); margin-top: 0.25rem; padding-top: 1.25rem;">
                <button type="button" onclick="document.getElementById('add-product-modal').style.display='none';" class="btn btn-outline" style="padding: 0.7rem 1.5rem; font-weight: 700; font-size: 0.9rem; border-radius: 10px;">Cancel</button>
                <button type="submit" class="btn" style="background: var(--accent-color); color: var(--text-inverse); padding: 0.7rem 1.6rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; transition: background 0.15s ease;" onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                    <i class="fa-solid fa-plus-circle"></i> Create Product
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .product-table-row {
        transition: all 0.2s ease !important;
    }
    .product-table-row:hover {
        background-color: var(--accent-soft) !important;
        box-shadow: inset 3px 0 0 0 var(--accent-color) !important;
    }
</style>

<!-- UNIFIED STICKY PRODUCTS BAR (Header + Filters on Scroll, styled like Orders) -->
<div class="card filter-card-sticky" style="padding: 1.25rem; margin-bottom: 1.5rem; position: sticky; top: 60px; z-index: 40; background: var(--bg-surface); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border-bottom: 2px solid var(--cmyk-cyan);">

        <!-- Top Row: Title + Add New Product Button -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.9rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.85rem; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: var(--accent-soft); color: var(--text-accent); font-size: 1rem;">
                    <i class="fa-solid fa-layer-group"></i>
                </span>
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--text-main); margin: 0; font-family: 'Outfit', sans-serif; line-height: 1.2;">
                        Products
                    </h2>
                    <span style="background: var(--bg-subtle); color: var(--text-muted); font-size: 0.78rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 9999px; border: 1px solid var(--border-color);">
                        {{ $products->count() }}
                    </span>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('add-product-modal').style.display='flex';" class="btn" style="background: var(--accent-color); color: var(--text-inverse); padding: 0.65rem 1.25rem; font-size: 0.92rem; border-radius: 12px; font-weight: 700; border: none; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: all 0.15s ease; box-shadow: 0 4px 12px rgba(0, 153, 255, 0.25);" onmouseover="this.style.background='#0080FF'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#0099FF'; this.style.transform='translateY(0)';">
                <i class="fa-solid fa-plus-circle"></i> Add New Product
            </button>
        </div>

        <!-- Filter Controls Form -->
        <form action="{{ route('admin.products.index') }}" method="GET" style="display: flex; gap: 0.85rem; align-items: center; flex-wrap: wrap;" id="product-filter-form">
            <!-- Search Input -->
            <div style="flex: 2; min-width: 220px;">
                <input type="text" name="search" id="product-search-input" value="{{ request('search') }}" placeholder="Search by product name..." style="width: 100%; padding: 0.65rem 1rem; border: 1px solid {{ request('search') ? 'var(--accent-color)' : 'var(--border-color)' }}; border-left: {{ request('search') ? '3px solid var(--accent-color)' : '1px solid var(--border-color)' }}; border-radius: 10px; font-size: 0.92rem; outline: none; background: {{ request('search') ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; color: var(--text-main); transition: all 0.2s ease;" onfocus="this.style.borderColor='#0099FF'; this.style.borderLeft='3px solid #0099FF'; this.style.boxShadow='0 0 0 3px rgba(0, 153, 255, 0.15)';" onblur="this.style.borderColor='{{ request('search') ? '#0099FF' : '#CBD5E1' }}'; this.style.borderLeft='{{ request('search') ? '3px solid #0099FF' : '1px solid #CBD5E1' }}'; this.style.boxShadow='none';">
            </div>

            <!-- Category Dropdown Filter -->
            <div style="flex: 1; min-width: 160px;">
                <select name="category" id="product-category-select" style="width: 100%; padding: 0.65rem 1rem; border: 1px solid {{ request('category') ? 'var(--accent-color)' : 'var(--border-color)' }}; border-left: {{ request('category') ? '3px solid var(--accent-color)' : '1px solid var(--border-color)' }}; border-radius: 10px; font-size: 0.92rem; outline: none; background: {{ request('category') ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; color: {{ request('category') ? 'var(--text-accent)' : 'var(--text-main)' }}; {{ request('category') ? 'font-weight: 700;' : '' }} cursor: pointer; transition: all 0.2s ease;" onchange="this.form.submit()" onfocus="this.style.borderColor='#0099FF'; this.style.borderLeft='3px solid #0099FF'; this.style.boxShadow='0 0 0 3px rgba(0, 153, 255, 0.15)';" onblur="this.style.borderColor='{{ request('category') ? '#0099FF' : '#CBD5E1' }}'; this.style.borderLeft='{{ request('category') ? '3px solid #0099FF' : '1px solid #CBD5E1' }}'; this.style.boxShadow='none';">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Product Status Dropdown Filter -->
            <div style="flex: 1; min-width: 170px;">
                <select name="status" id="product-status-select" style="width: 100%; padding: 0.65rem 1rem; border: 1px solid {{ request('status') ? 'var(--accent-color)' : 'var(--border-color)' }}; border-left: {{ request('status') ? '3px solid var(--accent-color)' : '1px solid var(--border-color)' }}; border-radius: 10px; font-size: 0.92rem; outline: none; background: {{ request('status') ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; color: {{ request('status') ? 'var(--text-accent)' : 'var(--text-main)' }}; {{ request('status') ? 'font-weight: 700;' : '' }} cursor: pointer; transition: all 0.2s ease;" onchange="this.form.submit()" onfocus="this.style.borderColor='#0099FF'; this.style.borderLeft='3px solid #0099FF'; this.style.boxShadow='0 0 0 3px rgba(0, 153, 255, 0.15)';" onblur="this.style.borderColor='{{ request('status') ? '#0099FF' : '#CBD5E1' }}'; this.style.borderLeft='{{ request('status') ? '3px solid #0099FF' : '1px solid #CBD5E1' }}'; this.style.boxShadow='none';">
                    <option value="">All Product Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>✅ Active Products Only</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>⛔ Inactive Products Only</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <button type="submit" class="btn" style="background: var(--accent-color); color: var(--text-inverse); padding: 0.65rem 1.4rem; font-size: 0.92rem; border-radius: 12px; font-weight: 700; border: none; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'category', 'status']))
                    <a href="{{ route('admin.products.index') }}" class="btn" style="background: var(--bg-surface); padding: 0.65rem 1.2rem; font-size: 0.92rem; border-radius: 12px; color: var(--text-muted); border: 1px solid var(--border-color); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; transition: all 0.15s ease;" onmouseover="this.style.borderColor='#94A3B8'; this.style.color='#0F172A'" onmouseout="this.style.borderColor='#CBD5E1'; this.style.color='#475569'" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left" style="color: var(--text-muted);"></i> Reset
                    </a>
                @endif
            </div>
        </form>
</div>

<!-- CATALOG PRODUCTS TABLE CARD -->
<div class="card" style="padding: 1.5rem; margin-bottom: 2rem; border-radius: 16px; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04); border: 1px solid var(--border-color);">

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.9rem;">
            <thead>
                <tr style="background: var(--bg-page); text-align: left; color: var(--text-muted);">
                    <th style="padding: 0.85rem 1.25rem; border-bottom: 2px solid var(--border-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; border-top-left-radius: 10px;">Product Name</th>
                    <th style="padding: 0.85rem 1.25rem; border-bottom: 2px solid var(--border-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Category</th>
                    <th style="padding: 0.85rem 1.25rem; border-bottom: 2px solid var(--border-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Base Price</th>
                    <th style="padding: 0.85rem 1.25rem; border-bottom: 2px solid var(--border-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Status</th>
                    <th style="padding: 0.85rem 1.25rem; border-bottom: 2px solid var(--border-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Readiness</th>
                    <th style="padding: 0.85rem 1.25rem; border-bottom: 2px solid var(--border-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; text-align: right; border-top-right-radius: 10px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $prod)
                    @php
                        $effectivePrice = (float)($prod->price ?: $prod->base_price);
                        $hasOptions     = $prod->options->isNotEmpty();
                        $hasBom         = $prod->materials->isNotEmpty();
                        $hasPrice       = $effectivePrice > 0;
                        $isReady        = $hasOptions && $hasBom && $hasPrice;
                    @endphp
                    <tr class="product-table-row">

                        {{-- Product Name --}}
                        <td style="padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid var(--border-color);">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">{{ $prod->name }}</div>
                            @if(!$prod->is_active)
                                <div style="font-size: 0.72rem; color: var(--text-warning); margin-top: 0.2rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <i class="fa-solid fa-eye-slash" style="font-size: 0.65rem;"></i> Hidden from public catalog
                                </div>
                            @endif
                        </td>

                        {{-- Category --}}
                        <td style="padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid var(--border-color);">
                            <span style="display: inline-block; padding: 0.25rem 0.65rem; background: var(--bg-subtle); color: var(--text-muted); border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                                {{ $prod->category }}
                            </span>
                        </td>

                        {{-- Base Price --}}
                        <td style="padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid var(--border-color);">
                            <div style="font-weight: 800; font-size: 1.05rem; color: {{ $hasPrice ? 'var(--text-accent)' : 'var(--text-danger)' }}; font-family: 'Outfit', sans-serif;">
                                ₱{{ number_format($effectivePrice, 2) }}
                            </div>
                        </td>

                        {{-- Status Badge --}}
                        <td style="padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid var(--border-color);">
                            @if($prod->is_active)
                                <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.7rem; background: var(--success-soft); color: var(--text-success); border-radius: 9999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                    <i class="fa-solid fa-circle" style="font-size: 0.4rem;"></i> Active
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.7rem; background: var(--warning-soft); color: var(--text-warning); border-radius: 9999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; border: 1px solid var(--warning-border);">
                                    <i class="fa-solid fa-pen-to-square" style="font-size: 0.6rem;"></i> Draft
                                </span>
                            @endif
                        </td>

                        {{-- Readiness Chips --}}
                        <td style="padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid var(--border-color);">
                            @if($isReady)
                                <span style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.72rem; font-weight: 700; color: var(--text-success);">
                                    <i class="fa-solid fa-circle-check"></i> Ready
                                </span>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                    @if(!$hasPrice)
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.7rem; font-weight: 700; color: var(--text-danger); background: var(--danger-soft); border: 1px solid var(--danger-border); border-radius: 5px; padding: 0.15rem 0.45rem; white-space: nowrap;">
                                            <i class="fa-solid fa-triangle-exclamation" style="font-size: 0.6rem;"></i> Price ₱0.00
                                        </span>
                                    @endif
                                    @if(!$hasOptions)
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.7rem; font-weight: 700; color: var(--text-warning); background: var(--warning-soft); border: 1px solid var(--warning-border); border-radius: 5px; padding: 0.15rem 0.45rem; white-space: nowrap;">
                                            <i class="fa-solid fa-triangle-exclamation" style="font-size: 0.6rem;"></i> No Options
                                        </span>
                                    @endif
                                    @if(!$hasBom)
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.7rem; font-weight: 700; color: var(--text-warning); background: var(--warning-soft); border: 1px solid var(--warning-border); border-radius: 5px; padding: 0.15rem 0.45rem; white-space: nowrap;">
                                            <i class="fa-solid fa-triangle-exclamation" style="font-size: 0.6rem;"></i> No BOM
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td style="padding: 1rem 1.25rem; text-align: right; vertical-align: middle; border-bottom: 1px solid var(--border-color);">
                            <div style="display: inline-flex; align-items: center; gap: 0.5rem; justify-content: flex-end; flex-wrap: wrap;">
                                @if(Auth::user()->isSuperAdmin())
                                    {{-- Super Admin: Edit & BOM Builder --}}
                                    <a href="{{ route('super-admin.products.edit', $prod->id) }}"
                                        style="background: var(--accent-color); color: var(--text-inverse); padding: 0.45rem 0.85rem; font-size: 0.82rem; border-radius: 8px; font-weight: 700; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap;"
                                        onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                                        <i class="fa-solid fa-sliders"></i> Edit & BOM
                                    </a>
                                    {{-- Quick Publish Toggle (Super Admin) --}}
                                    <form action="{{ route('super-admin.products.toggle-publish', $prod->id) }}" method="POST" style="margin:0; display:inline;">
                                        @csrf
                                        <button type="submit"
                                            style="background: {{ $prod->is_active ? 'var(--danger-soft)' : 'var(--success-soft)' }}; color: {{ $prod->is_active ? 'var(--text-danger)' : 'var(--text-success)' }}; border: 1px solid {{ $prod->is_active ? 'var(--danger-border)' : 'var(--success-border)' }}; padding: 0.45rem 0.75rem; font-size: 0.8rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; white-space: nowrap;"
                                            onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                            <i class="fa-solid {{ $prod->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                            {{ $prod->is_active ? 'Unpublish' : 'Publish' }}
                                        </button>
                                    </form>
                                @else
                                    {{-- Regular Admin: Quick Publish / Unpublish toggle via update route --}}
                                    <form action="{{ route('admin.products.update', $prod->id) }}" method="POST" style="margin:0; display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="name"        value="{{ $prod->name }}">
                                        <input type="hidden" name="category"    value="{{ $prod->category }}">
                                        <input type="hidden" name="description" value="{{ $prod->description }}">
                                        <input type="hidden" name="is_active"   value="{{ $prod->is_active ? '0' : '1' }}">
                                        <button type="submit"
                                            style="background: {{ $prod->is_active ? 'var(--danger-soft)' : 'var(--success-soft)' }}; color: {{ $prod->is_active ? 'var(--text-danger)' : 'var(--text-success)' }}; border: 1px solid {{ $prod->is_active ? 'var(--danger-border)' : 'var(--success-border)' }}; padding: 0.45rem 0.75rem; font-size: 0.8rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; white-space: nowrap;"
                                            onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                            <i class="fa-solid {{ $prod->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                            {{ $prod->is_active ? 'Unpublish' : 'Publish' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem 1.5rem; color: var(--text-subtle); font-size: 0.95rem;">
                            <i class="fa-solid fa-box-open" style="font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: var(--text-disabled);"></i>
                            No products found matching the search criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

