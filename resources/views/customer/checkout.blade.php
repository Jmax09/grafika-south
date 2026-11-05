@extends('layouts.app')

@section('title', 'Checkout - Grafika South Print Shop')

@push('styles')
<style>
    .checkout-layout-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) 390px;
        gap: 2rem;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .checkout-layout-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
    }
    .checkout-summary-sidebar {
        position: -webkit-sticky;
        position: sticky;
        top: 85px;
        z-index: 10;
    }
    @media (max-width: 1024px) {
        .checkout-summary-sidebar {
            position: static;
            top: auto;
        }
    }

    /* Form Validation Highlight & Shake Animation */
    @keyframes inputShake {
        0%, 100% { transform: translateX(0); }
        15%, 45%, 75% { transform: translateX(-6px); }
        30%, 60%, 90% { transform: translateX(6px); }
    }
    .input-error-shake {
        animation: inputShake 0.42s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
    }
    .field-error-border {
        border-color: var(--danger-color) !important;
        background-color: var(--danger-soft) !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.14) !important;
    }
    .field-error-msg {
        color: var(--text-danger);
        font-size: 0.78rem;
        font-weight: 600;
        margin-top: 0.35rem;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        animation: fadeInError 0.2s ease-in;
    }
    @keyframes fadeInError {
        from { opacity: 0; transform: translateY(-3px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Mobile: stack every two-column inline grid in the checkout form
       (fulfillment cards, customer info, payment terms/methods) into one
       column so options and labels stop overflowing the viewport. */
    @media (max-width: 639.98px) {
        #checkoutForm [style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
        #checkoutForm [style*="grid-template-columns"] {
            min-width: 0;
        }
        #checkoutForm [style*="grid-template-columns"] > * {
            min-width: 0;
            max-width: 100%;
        }
    }

    /* Long emails/addresses must wrap instead of pushing past the card edge */
    .checkout-info-value {
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.45;
    }

    /* Payment QR panel: only visible while its own payment-method card is selected */
    .pm-qr-panel {
        display: none;
    }
    .payment-method-item:has(input[type="radio"]:checked) .pm-qr-panel {
        display: flex;
    }

    /* Staff POS walk-in Payment Type — segmented category control + e-wallet chips */
    .walkin-seg-track {
        display: inline-flex;
        background: var(--bg-subtle);
        border-radius: 9999px;
        padding: 3px;
        gap: 2px;
    }
    .walkin-pay-cat {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.42rem 1.05rem;
        border-radius: 9999px;
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-muted);
        transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
        user-select: none;
    }
    .walkin-pay-cat:hover {
        color: var(--text-muted);
        background: color-mix(in srgb, var(--bg-surface) 65%, transparent);
    }
    .walkin-pay-cat.cat-active-online {
        background: var(--accent-color);
        color: var(--text-inverse);
        box-shadow: none;
    }
    .walkin-pay-cat.cat-active-cash {
        background: var(--accent-color);
        color: var(--text-inverse);
        box-shadow: none;
    }
    .walkin-sub-label {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.7rem;
        font-weight: 800;
        color: var(--text-accent);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .walkin-pay-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.85rem;
        border-radius: 9999px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-surface);
        cursor: pointer;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-muted);
        transition: all 0.15s ease;
        user-select: none;
    }
    .walkin-pay-toggle:hover {
        border-color: var(--accent-color);
        color: var(--text-accent);
        background: var(--accent-soft);
    }
    .walkin-pay-toggle.chip-active {
        background: var(--accent-color);
        border-color: var(--accent-color);
        color: var(--text-inverse);
        box-shadow: none;
    }
    .walkin-pay-toggle.chip-active:hover {
        color: var(--text-inverse);
        background: var(--accent-color);
    }
</style>
@endpush

@section('content')
@if($errors->any())
    <div style="background: var(--danger-soft); border: 1px solid var(--danger-border); color: var(--text-danger); padding: 1rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
        <strong style="display: block; margin-bottom: 0.5rem;"><i class="fa-solid fa-circle-exclamation"></i> Please fix the following errors before submitting:</strong>
        <ul style="margin-left: 1.25rem; font-size: 0.9rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $isAdminOrSuper = in_array(auth()->user()->role, ['admin', 'super_admin']);
    $isMixed = $isMixedCart ?? false;

    // Initial state of the ready-made payment method so the split summary can
    // display each group's amount separately right from the server render.
    $firstRegPm = isset($regularPaymentMethods) ? $regularPaymentMethods->first() : null;
    $regMethodOnlineInitial = $firstRegPm ? (bool) $firstRegPm->is_online_payment : false;
    $regMethodNameInitial = $firstRegPm ? $firstRegPm->name : 'Cash';

    // Combined split-cart totals: everything payable online now vs. cash later.
    $initialTotalDueNow = $downpaymentAmount + ($regMethodOnlineInitial ? $subtotalRegular : 0);
    $initialTotalDueLater = $remainingBalance + ($regMethodOnlineInitial ? 0 : $subtotalRegular);
@endphp

@if($isMixed && !$isAdminOrSuper)
    {{-- Cart Splitting Active banner hidden by design: the numbered Group 1/2
         cards and the sidebar split totals already convey the mixed-order flow. --}}
@endif

<form action="{{ route('checkout.store') }}" method="POST" enctype="multipart/form-data" id="checkoutForm">
    @csrf
    @if($isAdminOrSuper)
        <input type="hidden" name="is_walkin" value="1">
    @endif

    <div class="checkout-layout-grid">
        <!-- LEFT COLUMN: CUSTOMER DETAILS, FULFILLMENT & ARTWORK -->
        <div>
            <!-- CUSTOMER DETAILS / WALK-IN PANEL -->
            <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
                    <span>
                        <i class="fa-solid {{ $isAdminOrSuper ? 'fa-person-walking' : 'fa-user' }}" style="color: {{ $isAdminOrSuper ? 'var(--text-success)' : 'var(--accent-color)' }};"></i> 
                        {{ $isAdminOrSuper ? 'Walk-in Customer Details' : 'Customer Details' }}
                    </span>
                </h3>

                @if(!$isMixed)
                    <!-- SINGLE CART FULFILLMENT / DELIVERY OPTION CHOICE -->
                    @php
                        $allowedFulfillment = $allowedFulfillmentTypes ?? ['pickup', 'delivery'];
                        $allowPickup   = in_array('pickup',   $allowedFulfillment);
                        $allowDelivery = in_array('delivery', $allowedFulfillment);
                        $defaultFulfillment = $hasCustomItems
                            ? old('fulfillment_type', 'delivery')
                            : ($allowPickup ? old('fulfillment_type', 'pickup') : 'delivery');
                    @endphp

                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-size: 0.88rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 0.5rem;">
                            Select Fulfillment Method <span style="color: var(--text-danger);">*</span>
                        </label>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;" id="fulfillment_options_container">
                            <!-- STORE PICK-UP OPTION -->
                            <label id="option_pickup_card" style="border: {{ $defaultFulfillment === 'pickup' ? '2px solid var(--success-color)' : '1.5px solid var(--border-color)' }}; background: {{ $defaultFulfillment === 'pickup' ? 'var(--success-soft)' : 'var(--bg-surface)' }}; border-radius: 10px; padding: 0.9rem 1rem; display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; transition: all 0.2s ease;">
                                <input type="radio" name="fulfillment_type" value="pickup" id="fulfillment_pickup" {{ $defaultFulfillment === 'pickup' ? 'checked' : '' }} onchange="toggleFulfillmentType('pickup')" style="accent-color: var(--success-color); width: 18px; height: 18px; margin-top: 2px;">
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-success); display: flex; align-items: center; justify-content: space-between;">
                                        <span><i class="fa-solid fa-store"></i> Store Pick-up</span>
                                        <span style="font-size: 0.7rem; font-weight: 800; background: var(--success-soft); color: var(--text-success); padding: 0.1rem 0.45rem; border-radius: 4px;">FREE</span>
                                    </div>
                                    <div style="font-size: 0.76rem; color: var(--text-success); margin-top: 0.25rem; line-height: 1.35;">
                                        San Pedro Branch Counter Pick-up
                                    </div>
                                </div>
                            </label>

                            <!-- COURIER DELIVERY / DISPATCH OPTION -->
                            <label id="option_delivery_card" style="border: {{ $defaultFulfillment === 'delivery' ? '2px solid var(--accent-color)' : '1.5px solid var(--border-color)' }}; background: {{ $defaultFulfillment === 'delivery' ? 'var(--accent-soft)' : 'var(--bg-surface)' }}; border-radius: 10px; padding: 0.9rem 1rem; display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; transition: all 0.2s ease;">
                                <input type="radio" name="fulfillment_type" value="delivery" id="fulfillment_delivery" {{ $defaultFulfillment === 'delivery' ? 'checked' : '' }} onchange="toggleFulfillmentType('delivery')" style="accent-color: var(--accent-color); width: 18px; height: 18px; margin-top: 2px;">
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-accent); display: flex; align-items: center; justify-content: space-between;">
                                        <span><i class="fa-solid fa-truck-fast"></i> Courier Delivery</span>
                                        <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-accent); background: var(--accent-soft); padding: 0.1rem 0.45rem; border-radius: 4px;">Direct Dispatch</span>
                                    </div>
                                    <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 0.25rem; line-height: 1.35;">
                                        Direct Courier Delivery to Customer Address
                                    </div>
                                </div>
                            </label>
                        </div>

                        {{-- Notices are customer-side only; hidden in Staff POS Walk-in mode --}}
                        @if(!$isAdminOrSuper)
                        <!-- STORE PICK-UP NOTICE -->
                        <div id="store_pickup_notice" style="display: {{ $defaultFulfillment === 'pickup' ? 'flex' : 'none' }}; margin-top: 0.85rem; background: var(--success-soft); border: 1px solid var(--success-border); border-left: 4px solid var(--success-color); border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.83rem; color: var(--text-success); align-items: flex-start; gap: 0.65rem; line-height: 1.45; transition: all 0.2s ease;">
                            <i class="fa-solid fa-store" style="color: var(--text-success); font-size: 1.15rem; margin-top: 2px; flex-shrink: 0;"></i>
                            <div>
                                <strong style="color: var(--text-success); font-size: 0.85rem; display: block; margin-bottom: 0.15rem;">
                                    <i class="fa-solid fa-circle-info"></i> Store Pick-up Collection Rules
                                </strong>
                                Collect your order in person at our <strong>San Pedro, Laguna branch</strong>. You will be notified via email when your order is ready for pick-up.


                                <!-- Store Location Card (Clickable Google Maps Link) -->
                                <div class="mt-3 p-3 bg-[var(--bg-surface)] border border-emerald-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                                    <!-- Address Info -->
                                    <div class="flex items-start gap-2.5">
                                        <span class="text-emerald-600 text-base mt-0.5"><i class="fa-solid fa-location-dot"></i></span>
                                        <div>
                                            <div class="text-xs font-bold text-[var(--text-main)]">Grafika South - San Pedro Branch</div>
                                            <a href="https://www.google.com/maps/search/?api=1&amp;query=14.351610247551,121.06767565012"
                                               target="_blank"
                                               rel="noopener"
                                               class="text-xs text-sky-600 font-medium no-underline hover:underline flex items-center gap-1 mt-0.5">
                                                #1 San Luis St. Purok 5 Barangay Landayan, San Pedro, Philippines, 4023
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Open in Google Maps Action Button -->
                                    <a href="https://www.google.com/maps/search/?api=1&amp;query=14.351610247551,121.06767565012"
                                       target="_blank"
                                       rel="noopener"
                                       class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 text-xs font-bold rounded-lg transition-colors shrink-0 no-underline">
                                        <i class="fa-solid fa-map-location-dot"></i> Open in Google Maps
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- DYNAMIC SHIPPING FEE NOTICE -->
                        <div id="courier_shipping_notice" style="display: {{ $defaultFulfillment === 'delivery' ? 'flex' : 'none' }}; margin-top: 0.85rem; background: var(--warning-soft); border: 1px solid var(--warning-border); border-left: 4px solid var(--warning-color); border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.83rem; color: var(--text-warning); align-items: flex-start; gap: 0.65rem; line-height: 1.45; transition: all 0.2s ease;">
                            <i class="fa-solid fa-truck-ramp-box" style="color: var(--text-warning); font-size: 1.15rem; margin-top: 2px; flex-shrink: 0;"></i>
                            <div>
                                <strong style="color: var(--text-warning); font-size: 0.85rem; display: block; margin-bottom: 0.15rem;">
                                    <i class="fa-solid fa-circle-info"></i> Third-Party Courier Shipping Notice
                                </strong>
                                Shipping fee is calculated based on your delivery location and will be fulfilled via third-party courier app (e.g., Lalamove/Grab/J&T).
                            </div>
                        </div>
                        @endif
                    </div>
                @endif

                @if($isAdminOrSuper)
                    {{-- WALK-IN CUSTOMER INPUT FIELDS (Admin/Super Admin POS Mode) --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;" id="walkin_customer_fields">
                        <div>
                            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 0.35rem;">
                                First Name <span style="color: var(--text-danger);">*</span>
                            </label>
                            <input type="text" name="walkin_first_name" id="walkin_first_name" value="{{ old('walkin_first_name') }}" placeholder="e.g. Juan"
                                class="@error('walkin_first_name') field-error-border @enderror"
                                style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid var(--border-strong); border-radius: var(--radius-md); font-size: 0.9rem; color: var(--text-main); background: var(--bg-surface); outline: none; transition: border-color 0.15s, box-shadow 0.15s;"
                                onfocus="if(!this.classList.contains('field-error-border')) this.style.borderColor='#0099FF'" onblur="if(!this.classList.contains('field-error-border')) this.style.borderColor=''">
                            @error('walkin_first_name')
                                <span class="field-error-msg">
                                    <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div>
                            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 0.35rem;" id="walkin_last_name_label">
                                Last Name <span id="walkin_last_name_req" style="color: var(--text-muted); font-weight: 600;">(optional for pickup)</span>
                            </label>
                            <input type="text" name="walkin_last_name" id="walkin_last_name" value="{{ old('walkin_last_name') }}" placeholder="e.g. Dela Cruz"
                                class="@error('walkin_last_name') field-error-border @enderror"
                                style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid var(--border-strong); border-radius: var(--radius-md); font-size: 0.9rem; color: var(--text-main); background: var(--bg-surface); outline: none; transition: border-color 0.15s, box-shadow 0.15s;"
                                onfocus="if(!this.classList.contains('field-error-border')) this.style.borderColor='#0099FF'" onblur="if(!this.classList.contains('field-error-border')) this.style.borderColor=''">
                            @error('walkin_last_name')
                                <span class="field-error-msg">
                                    <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div>
                            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 0.35rem;" id="walkin_phone_label">
                                Contact Number <span id="walkin_phone_req" style="color: var(--text-muted); font-weight: 600;">(optional for pickup)</span>
                            </label>
                            <input type="text" name="walkin_contact_number" id="walkin_contact_number" value="{{ old('walkin_contact_number') }}" placeholder="e.g. 09123456789"
                                inputmode="numeric" maxlength="11" title="Enter a valid 11-digit mobile number starting with 09"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)"
                                class="@error('walkin_contact_number') field-error-border @enderror"
                                style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid var(--border-strong); border-radius: var(--radius-md); font-size: 0.9rem; color: var(--text-main); background: var(--bg-surface); outline: none; transition: border-color 0.15s, box-shadow 0.15s;"
                                onfocus="if(!this.classList.contains('field-error-border')) this.style.borderColor='#0099FF'" onblur="if(!this.classList.contains('field-error-border')) this.style.borderColor=''">
                            @error('walkin_contact_number')
                                <span class="field-error-msg">
                                    <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div>
                            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 0.35rem;" id="walkin_email_label">
                                Email Address <span id="walkin_email_req" style="color: var(--text-muted); font-weight: 600;">(optional for pickup)</span>
                            </label>
                            <input type="email" name="walkin_email" id="walkin_email" value="{{ old('walkin_email') }}" placeholder="customer@email.com"
                                class="@error('walkin_email') field-error-border @enderror"
                                style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid var(--border-strong); border-radius: var(--radius-md); font-size: 0.9rem; color: var(--text-main); background: var(--bg-surface); outline: none; transition: border-color 0.15s, box-shadow 0.15s;"
                                onfocus="if(!this.classList.contains('field-error-border')) this.style.borderColor='#0099FF'" onblur="if(!this.classList.contains('field-error-border')) this.style.borderColor=''">
                            @error('walkin_email')
                                <span class="field-error-msg">
                                    <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 0.35rem;" id="walkin_address_label">
                                Delivery Address <span id="walkin_address_req" style="color: var(--text-muted); font-weight: 600;">(required for delivery)</span>
                            </label>
                            <input type="text" name="walkin_address" id="walkin_address" value="{{ old('walkin_address') }}" placeholder="Street, Barangay, City, Province"
                                class="@error('walkin_address') field-error-border @enderror"
                                style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid var(--border-strong); border-radius: var(--radius-md); font-size: 0.9rem; color: var(--text-main); background: var(--bg-surface); outline: none; transition: border-color 0.15s, box-shadow 0.15s;"
                                onfocus="if(!this.classList.contains('field-error-border')) this.style.borderColor='#0099FF'" onblur="if(!this.classList.contains('field-error-border')) this.style.borderColor=''">
                            @error('walkin_address')
                                <span class="field-error-msg">
                                    <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>
                @else
                    {{-- CUSTOMER PROFILE READ-ONLY DISPLAY --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.25rem; padding-top: 1rem; border-top: 1px solid var(--border-strong);">
                        <div>
                            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.2rem;">Full Name</label>
                            <div class="checkout-info-value" style="font-weight: 600; font-size: 0.95rem; color: var(--text-main);">{{ $user->name }}</div>
                        </div>
                        <div>
                            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.2rem;">Email Address</label>
                            <div class="checkout-info-value" style="font-weight: 600; font-size: 0.95rem; color: var(--text-main);">{{ $user->email }}</div>
                        </div>
                        <div>
                            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.2rem;">Contact Number</label>
                            <div class="checkout-info-value" style="font-weight: 600; font-size: 0.95rem; color: var(--text-main);">{{ $user->contact ?: ($user->phone ?: 'Not provided') }}</div>
                        </div>
                        <div>
                            <label id="customer_address_label" style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.2rem;">
                                Registered Delivery Address
                            </label>
                            <div id="customer_address_display" class="checkout-info-value" style="font-weight: 600; font-size: 0.95rem; color: var(--text-main);">
                                {{ $user->address ?: 'Your registered delivery address' }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if($isMixed)
                <!-- ══════════════════════════════════════════════════════════════ -->
                <!-- MIXED CART GROUP 1: READY-MADE / REGULAR PRODUCTS -->
                <!-- ══════════════════════════════════════════════════════════════ -->
                <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem; border: 1.5px solid var(--success-border); background: var(--bg-page);">
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.4rem 0.75rem; border-bottom: 1.5px solid var(--success-border); padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="background: var(--success-color); color: var(--text-inverse); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">1</span>
                            <div>
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-success); margin: 0;">
                                    Ready-Made / Regular Products
                                </h3>
                                <div style="font-size: 0.78rem; color: var(--text-success);">Standard Catalog Items • Cash/Offline or Digital Payment</div>
                            </div>
                        </div>
                        <span style="font-size: 1.15rem; font-weight: 800; color: var(--text-success);">₱{{ number_format($subtotalRegular, 2) }}</span>
                    </div>

                    <!-- Regular Items List -->
                    <div style="margin-bottom: 1.25rem;">
                        @foreach($regularCart as $key => $item)
                            <div style="background: var(--bg-page); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <div style="display: flex; align-items: center; gap: 0.65rem; min-width: 0;">
                                        @if(!empty($item['image_path']))
                                            <img src="{{ asset('storage/' . $item['image_path']) }}" alt="{{ $item['name'] }}" style="width: 46px; height: 46px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-surface); flex-shrink: 0;">
                                        @endif
                                        <strong style="font-size: 0.98rem; color: var(--text-main);">{{ $item['name'] }}</strong>
                                    </div>
                                    <span style="font-weight: 800; color: var(--text-success);">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                </div>
                                <div style="font-size: 0.82rem; color: var(--text-muted);">
                                    Quantity: <strong>{{ $item['quantity'] }}</strong> | Unit Price: ₱{{ number_format($item['price'], 2) }}
                                </div>
                                @if(!empty($item['options']))
                                    @php
                                        $optionImages = !empty($item['product_id'])
                                            ? \App\Models\ProductOption::imageMapFor((int) $item['product_id'])
                                            : [];
                                    @endphp
                                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.5rem;">
                                        @foreach($item['options'] as $optKey => $optVal)
                                            @php $optImg = $optionImages[\App\Models\ProductOption::imageMapKey($optKey, $optVal)] ?? null; @endphp
                                            <span style="display:inline-flex; align-items:center; gap:0.3rem; background: var(--text-disabled); color: var(--text-muted); font-size: 0.72rem; font-weight: 600; padding: 0.1rem 0.4rem; border-radius: 4px;">
                                                @if($optImg)
                                                    <img src="{{ $optImg }}" alt="" loading="lazy" style="width:16px; height:16px; border-radius:4px; object-fit:cover; border:1px solid var(--border-color); background:var(--bg-surface); flex-shrink:0;">
                                                @endif
                                                {{ $optKey }}: {{ $optVal }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Regular Fulfillment Choice -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-size: 0.85rem; font-weight: 700; color: var(--text-success); display: block; margin-bottom: 0.4rem;">
                            <i class="fa-solid fa-truck"></i> Regular Items Fulfillment Method:
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                            <label style="border: 1.5px solid var(--success-border); background: var(--success-soft); border-radius: 8px; padding: 0.75rem; display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
                                <input type="radio" name="regular_fulfillment_type" value="pickup" checked style="accent-color: var(--success-color);">
                                <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-success);">
                                    <i class="fa-solid fa-store"></i> Store Pick-up (FREE)
                                </div>
                            </label>
                            <label style="border: 1.5px solid var(--border-color); background: var(--bg-surface); border-radius: 8px; padding: 0.75rem; display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
                                <input type="radio" name="regular_fulfillment_type" value="delivery" style="accent-color: var(--accent-color);">
                                <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-accent);">
                                    <i class="fa-solid fa-truck-fast"></i> Courier Delivery
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Regular Payment Methods -->
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: var(--text-success); display: block; margin-bottom: 0.4rem;">
                            <i class="fa-solid fa-credit-card"></i> Regular Items Payment Method:
                        </label>
                        <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                            @foreach($regularPaymentMethods as $index => $pm)
                                <label class="reg-payment-method-item" data-is-online="{{ $pm->is_online_payment ? '1' : '0' }}" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.75rem 1rem; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s ease;">
                                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                                        <input type="radio" name="regular_payment_method" value="{{ $pm->name }}" {{ $index === 0 ? 'checked' : '' }} style="accent-color: var(--success-color);">
                                        <span style="font-weight: 700; font-size: 0.9rem; color: var(--text-main);">{{ $pm->name }}</span>
                                    </div>
                                    <span style="font-size: 0.72rem; font-weight: 700; padding: 0.1rem 0.45rem; border-radius: 4px; background: {{ $pm->is_online_payment ? 'var(--accent-soft)' : 'var(--warning-soft)' }}; color: {{ $pm->is_online_payment ? 'var(--text-accent)' : 'var(--text-warning)' }};">
                                        {{ $pm->is_online_payment ? 'ONLINE / DIGITAL' : 'CASH / PHYSICAL' }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Shared-screenshot hint removed by design: the single upload
                             box in the Custom Items section speaks for itself. --}}
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════════ -->
                <!-- MIXED CART GROUP 2: CUSTOMIZED / PERSONALIZED PRINT PRODUCTS -->
                <!-- ══════════════════════════════════════════════════════════════ -->
                <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem; border: 1.5px solid var(--info-border); background: var(--bg-page);">
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.4rem 0.75rem; border-bottom: 1.5px solid var(--info-border); padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="background: var(--accent-color); color: var(--text-inverse); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">2</span>
                            <div>
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-accent); margin: 0;">
                                    Customized / Personalized Items
                                </h3>
                                <div style="font-size: 0.78rem; color: var(--text-accent);">
                                    Custom Print Jobs • Digital Advance Payment Required
                                    @if(!empty($formattedCustomProdTime))
                                        <span style="display: inline-flex; align-items: center; gap: 0.3rem; margin-left: 0.5rem; background: var(--warning-soft); color: var(--text-warning); font-weight: 700; padding: 0.1rem 0.45rem; border-radius: 4px; border: 1px solid var(--warning-border);">
                                            <i class="fa-solid fa-stopwatch" style="font-size: 0.65rem;"></i> Est. Turnaround: {{ $formattedCustomProdTime }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <span style="font-size: 1.15rem; font-weight: 800; color: var(--text-accent);">₱{{ number_format($subtotalCustom, 2) }}</span>
                    </div>

                    <!-- Custom Items List & Artwork -->
                    <div style="margin-bottom: 1.25rem;">
                        @foreach($customCart as $key => $item)
                            <div style="background: var(--bg-page); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.15rem; margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <div style="display: flex; align-items: center; gap: 0.65rem; min-width: 0;">
                                        @if(!empty($item['image_path']))
                                            <img src="{{ asset('storage/' . $item['image_path']) }}" alt="{{ $item['name'] }}" style="width: 46px; height: 46px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-surface); flex-shrink: 0;">
                                        @endif
                                        <strong style="font-size: 0.98rem; color: var(--text-main);">{{ $item['name'] }}</strong>
                                    </div>
                                    <span style="font-weight: 800; color: var(--text-main);">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                </div>
                                <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.4rem;">
                                    Quantity: <strong>{{ $item['quantity'] }}</strong> | Unit Price: ₱{{ number_format($item['price'], 2) }}
                                </div>

                                @if(!empty($item['formatted_production_time']))
                                    <div style="font-size: 0.74rem; color: var(--text-magenta); font-weight: 700; margin-bottom: 0.5rem; display: inline-flex; align-items: center; gap: 0.4rem; background: var(--brand-magenta-soft); border: 1px solid var(--brand-magenta-soft); padding: 0.25rem 0.65rem; border-radius: 9999px;">
                                        <i class="fa-solid fa-stopwatch" style="color: var(--text-magenta); font-size: 0.7rem;"></i> Est. Production: {{ $item['formatted_production_time'] }}
                                    </div>
                                @endif

                                @if(!empty($item['options']))
                                    @php
                                        $optionImages = !empty($item['product_id'])
                                            ? \App\Models\ProductOption::imageMapFor((int) $item['product_id'])
                                            : [];
                                    @endphp
                                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-bottom: 0.6rem;">
                                        @foreach($item['options'] as $optKey => $optVal)
                                            @php $optImg = $optionImages[\App\Models\ProductOption::imageMapKey($optKey, $optVal)] ?? null; @endphp
                                            <span style="display:inline-flex; align-items:center; gap:0.3rem; background: var(--text-disabled); color: var(--text-muted); font-size: 0.72rem; font-weight: 600; padding: 0.1rem 0.4rem; border-radius: 4px;">
                                                @if($optImg)
                                                    <img src="{{ $optImg }}" alt="" loading="lazy" style="width:16px; height:16px; border-radius:4px; object-fit:cover; border:1px solid var(--border-color); background:var(--bg-surface); flex-shrink:0;">
                                                @endif
                                                {{ $optKey }}: {{ $optVal }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @php
                                    $checkoutFiles = $item['design_file_paths'] ?? [];
                                    if (!is_array($checkoutFiles)) { $checkoutFiles = []; }
                                    if (empty($checkoutFiles) && !empty($item['design_file_path'])) {
                                        $checkoutFiles = [$item['design_file_path']];
                                    }
                                @endphp
                                @if(!empty($checkoutFiles))
                                    <div style="background: var(--success-soft); border: 1px solid var(--success-border); border-radius: 6px; padding: 0.5rem 0.75rem; margin-bottom: 0.6rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.8rem; color: var(--text-success);">
                                        <span><i class="fa-solid fa-circle-check"></i> {{ count($checkoutFiles) > 1 ? count($checkoutFiles) . ' artwork files attached' : 'Artwork file attached' }}</span>
                                        <span style="display:inline-flex; gap:0.5rem; flex-wrap:wrap; justify-content:flex-end;">
                                            @foreach($checkoutFiles as $checkoutFile)
                                                <a href="{{ asset('storage/' . $checkoutFile) }}" target="_blank" style="color: var(--text-success); font-weight: 700; text-decoration: none;">View {{ basename($checkoutFile) }}</a>
                                            @endforeach
                                        </span>
                                    </div>
                                @endif

                                {{-- Scenario A/B: hide the uploader entirely when the catalog already
                                     attached a design file for this item (matches the server-side rule) --}}
                                @if(!$isAdminOrSuper && !empty($item['requires_design_file']) && empty($item['design_file_path']))
                                    <div>
                                        <label style="font-size: 0.84rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 0.35rem;">
                                            <i class="fa-solid fa-cloud-arrow-up" style="color: var(--brand-cyan-fg);"></i> Upload Artwork File(s) for {{ $item['name'] }} <span style="color: var(--text-danger);">*</span>
                                            <span style="font-size: 0.68rem; font-weight: 700; color: var(--text-danger); background: var(--danger-soft); padding: 0.1rem 0.4rem; border-radius: 4px; margin-left: 0.25rem;">REQUIRED</span>
                                        </label>
                                        <div class="artwork-dropzone" data-item-key="{{ $key }}" data-item-name="{{ $item['name'] }}" style="border: 2px dashed var(--border-strong); background: var(--bg-surface); border-radius: 8px; padding: 1rem; text-align: center; cursor: pointer; transition: all 0.2s ease;">
                                            <input type="file" class="artwork-file-input" id="item_files_input_{{ $key }}" name="item_files[{{ $key }}][]" multiple accept=".png,.jpg,.jpeg,.pdf,.ai,.psd,.svg,.zip" style="display: none;">
                                            <div style="font-size: 0.84rem; font-weight: 700; color: var(--text-main);">Drag &amp; drop artwork here or browse</div>
                                            <div style="font-size: 0.72rem; color: var(--text-subtle);">PNG, JPG, PDF, AI, PSD, SVG, ZIP (Up to 20MB)</div>
                                        </div>
                                        <div class="artwork-preview-grid" id="preview_grid_{{ $key }}" style="display: none; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 0.5rem; margin-top: 0.5rem;"></div>
                                        <div class="artwork-missing-warning" data-warn-key="{{ $key }}" style="display: none; margin-top: 0.45rem; padding: 0.4rem 0.6rem; background: var(--danger-soft); border: 1px solid var(--danger-border); border-radius: 6px; font-size: 0.78rem; color: var(--text-danger); font-weight: 600; align-items: center; gap: 0.4rem;">
                                            <i class="fa-solid fa-paperclip"></i> Artwork file is required for this custom item — please upload before ordering.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Custom Fulfillment Choice -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-size: 0.85rem; font-weight: 700; color: var(--text-accent); display: block; margin-bottom: 0.4rem;">
                            <i class="fa-solid fa-truck"></i> Custom Items Fulfillment Method:
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                            <label style="border: 1.5px solid var(--border-color); background: var(--bg-surface); border-radius: 8px; padding: 0.75rem; display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
                                <input type="radio" name="custom_fulfillment_type" value="pickup" style="accent-color: var(--success-color);">
                                <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-success);">
                                    <i class="fa-solid fa-store"></i> Store Pick-up
                                </div>
                            </label>
                            <label style="border: 1.5px solid var(--info-border); background: var(--accent-soft); border-radius: 8px; padding: 0.75rem; display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
                                <input type="radio" name="custom_fulfillment_type" value="delivery" checked style="accent-color: var(--accent-color);">
                                <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-accent);">
                                    <i class="fa-solid fa-truck-fast"></i> Courier Delivery
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Custom Payment Plan Selection (50% Downpayment vs 100% Full) -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-size: 0.85rem; font-weight: 700; color: var(--text-accent); display: block; margin-bottom: 0.4rem;">
                            <i class="fa-solid fa-percent"></i> Custom Items Payment Term:
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                            <label style="border: 1.5px solid var(--accent-color); background: var(--accent-soft); border-radius: 8px; padding: 0.75rem 1rem; cursor: pointer; display: block;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.2rem;">
                                    <input type="radio" name="custom_payment_plan" value="downpayment" checked onchange="updateMixedSummaryPlan('downpayment')" style="accent-color: var(--accent-color);">
                                    <strong style="font-size: 0.88rem; color: var(--text-accent);">50% Downpayment</strong>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    Pay ₱{{ number_format($downpaymentAmount, 2) }} now to start production.
                                </div>
                            </label>
                            <label style="border: 1.5px solid var(--border-color); background: var(--bg-surface); border-radius: 8px; padding: 0.75rem 1rem; cursor: pointer; display: block;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.2rem;">
                                    <input type="radio" name="custom_payment_plan" value="full" onchange="updateMixedSummaryPlan('full')" style="accent-color: var(--accent-color);">
                                    <strong style="font-size: 0.88rem; color: var(--text-main);">100% Full Payment</strong>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    Pay full ₱{{ number_format($subtotalCustom, 2) }} upfront.
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Custom Payment Methods (Digital Only) -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-size: 0.85rem; font-weight: 700; color: var(--text-accent); display: block; margin-bottom: 0.4rem;">
                            <i class="fa-solid fa-qrcode"></i> Digital Payment Method (Custom Items):
                        </label>
                        <div class="p-3 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-xl font-medium mb-3" style="background: var(--warning-soft); border: 1px solid var(--warning-border); color: var(--text-warning); border-radius: 12px; padding: 0.75rem; font-size: 0.75rem; font-weight: 500; margin-bottom: 0.75rem;">
                            ⚠️ Custom items require online payment/downpayment before printing starts. Cash payment is disabled for this order.
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                            @foreach($digitalPaymentMethods as $index => $pm)
                                <label class="payment-method-item" data-is-online="1" data-is-cash="0" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.75rem 1rem; display: block; cursor: pointer; transition: all 0.15s ease;">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                                            <input type="radio" name="custom_payment_method" value="{{ $pm->name }}" {{ $index === 0 ? 'checked' : '' }} onchange="validateCheckoutState(false)" style="accent-color: var(--accent-color);">
                                            <div>
                                                <strong style="font-size: 0.9rem; color: var(--text-main);">{{ $pm->name }}</strong>
                                                @if($pm->account_name || $pm->account_number)
                                                    <div class="text-xs text-[var(--text-muted)] font-medium mt-0.5 leading-relaxed">
                                                        @if($pm->account_name)
                                                            <div>Account Name: <strong class="text-[var(--text-main)]">{{ $pm->account_name }}</strong></div>
                                                        @endif
                                                        @if($pm->account_number)
                                                            <div>Account Number: <strong class="text-sky-600 font-bold select-all">{{ $pm->account_number }}</strong></div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="role-badge" style="background: var(--accent-soft); color: var(--text-accent); font-size: 0.72rem; font-weight: 700;">
                                            DIGITAL TRANSFER
                                        </span>
                                    </div>
                                    @include('customer.partials.payment-qr', ['pm' => $pm])
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- VISUAL DIVIDER BETWEEN PAYMENT METHODS AND PROOF -->
                    <div style="border-top: 1.5px dashed var(--info-border); margin: 1.75rem 0;"></div>

                    <!-- Proof of Payment Upload for Custom Items -->
                    <div style="margin-top: 0.5rem;">
                        {{-- Split-Cart Payment Notice removed by design: the sticky Order
                             Summary sidebar already shows every per-group amount and the
                             combined due-now / due-later totals — the exact breakdown
                             remains available in the confirmation modal on submit. --}}
                        {{-- Payment upload: only shown for digital payment methods, hidden for cash --}}
                        <div id="custom_proof_upload_section">
                            <!-- UPLOAD PROOF BOX -->
                            <div id="custom_proof_inner_box" style="background: var(--bg-surface); border: 1.5px solid var(--info-border); border-radius: 12px; padding: 1.25rem 1.35rem; margin-bottom: 0.5rem; transition: all 0.2s ease;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                                    <label style="font-size: 0.88rem; font-weight: 800; color: var(--text-accent); margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload Payment Screenshot (Online Payments) @if(!$isAdminOrSuper)<span style="color: var(--text-danger);">*</span>@endif
                                    </label>
                                    @if(!$isAdminOrSuper)
                                        <span style="font-size: 0.75rem; font-weight: 700; background: var(--danger-soft); color: var(--text-danger); padding: 0.15rem 0.5rem; border-radius: 4px;">
                                            Required to Place Order
                                        </span>
                                    @else
                                        {{-- Walk-in POS: counter payment is verified by staff, so the
                                             screenshot is optional — never block the order button. --}}
                                        <span style="font-size: 0.75rem; font-weight: 700; background: var(--bg-subtle); color: var(--text-muted); padding: 0.15rem 0.5rem; border-radius: 4px;">
                                            Optional for Walk-in
                                        </span>
                                    @endif
                                </div>
                                <div class="artwork-dropzone" id="proof-dropzone" onclick="document.getElementById('proof_of_payment_input').click()" style="border: 2px dashed var(--accent-color); border-radius: 10px; background: var(--accent-soft); padding: 1.5rem 1rem; cursor: pointer; text-align: center; transition: all 0.2s ease;">
                                    <input type="file" id="proof_of_payment_input" name="proof_of_payment" accept="image/jpeg,image/jpg,image/jfif,image/png,image/webp,image/gif" style="display: none;" onchange="window.handleProofFileSelect(this)">
                                    <div id="proof-empty-state">
                                        <i class="fa-solid fa-file-arrow-up" style="font-size: 1.75rem; color: var(--text-accent); margin-bottom: 0.35rem;"></i>
                                        <div style="font-size: 0.9rem; font-weight: 800; color: var(--text-main);">Click or drag payment receipt image here</div>
                                        <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 0.2rem;">PNG, JPG, JPEG, WEBP (Max 10MB)</div>
                                    </div>
                                    <div id="proof-file-selected" style="display: none; align-items: center; justify-content: space-between; background: var(--bg-surface); padding: 0.65rem 0.85rem; border-radius: 8px; border: 1.5px solid var(--info-border);">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <img id="proof-thumbnail-img" src="" style="width: 44px; height: 44px; border-radius: 6px; object-fit: cover;">
                                            <div style="text-align: left;">
                                                <div id="proof-filename" style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);"></div>
                                                <div id="proof-filesize" style="font-size: 0.74rem; color: var(--text-muted);"></div>
                                            </div>
                                        </div>
                                        <button type="button" onclick="event.stopPropagation(); clearProofFile();" style="background: var(--danger-soft); border: 1px solid var(--danger-border); color: var(--text-danger); font-size: 0.78rem; font-weight: 700; padding: 0.3rem 0.65rem; border-radius: 6px; cursor: pointer;">Remove</button>
                                    </div>
                                </div>
                            </div>
                            <div id="proof_missing_warning" style="display: none; align-items: flex-start; gap: 0.5rem; background: var(--danger-soft); border: 1.5px solid var(--danger-border); border-left: 4px solid var(--danger-color); border-radius: 8px; padding: 0.7rem 0.9rem; margin-top: 0.5rem; font-size: 0.82rem; color: var(--text-danger); font-weight: 700;">
                                <i class="fa-solid fa-triangle-exclamation" style="color: var(--text-danger); margin-top: 1px;"></i>
                                <span>Please upload your <strong>payment screenshot / proof of payment</strong> before placing your order.</span>
                            </div>
                        </div>

                        {{-- Cash payment notice (custom group) — retired: the Cash card's own instruction text is enough; JS toggle kept harmless --}}
                        <div id="custom_cash_payment_notice" style="display:none !important; background: var(--success-soft); border: 1.5px solid var(--success-border); border-left: 4px solid var(--success-color); border-radius: 12px; padding: 1.1rem 1.25rem;">
                            <div style="display: flex; align-items: center; gap: 0.6rem;">
                                <i class="fa-solid fa-money-bill-wave" style="color: var(--text-success); font-size: 1.2rem;"></i>
                                <div>
                                    <div style="font-size: 0.9rem; font-weight: 800; color: var(--text-success);">Mode of Payment: Cash</div>
                                    <div style="font-size: 0.8rem; color: var(--text-success); margin-top: 0.15rem;">
                                        Please pay in cash upon store pickup. No payment screenshot is required.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shared Order Notes card removed by design — the controller still
                     accepts an optional notes field, it is just no longer prompted here. --}}

            @else
                <!-- ══════════════════════════════════════════════════════════════ -->
                <!-- SINGLE CART: CLEAN UNIFIED FLOW FOR PURE REGULAR OR PURE CUSTOM -->
                <!-- ══════════════════════════════════════════════════════════════ -->
                <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.35rem 0.75rem;">
                        <span>
                            <i class="fa-solid fa-file-image" style="color: var(--accent-color);"></i>
                            Order Items
                        </span>
                    </h3>

                    @foreach($cart as $key => $item)
                        <div style="background: var(--bg-page); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.25rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0;">
                                    @if(!empty($item['image_path']))
                                        <img src="{{ asset('storage/' . $item['image_path']) }}" alt="{{ $item['name'] }}" style="width: 52px; height: 52px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-surface); flex-shrink: 0;">
                                    @endif
                                    <strong style="font-size: 1.05rem; color: var(--text-main);">{{ $item['name'] }}</strong>
                                </div>
                                <span style="font-weight: 800; color: var(--text-main);">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                            </div>

                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                                Quantity: <strong>{{ $item['quantity'] }}</strong> | Unit Price: ₱{{ number_format($item['price'], 2) }}
                            </div>

                            @php
                                $itemIsCustom = !empty($item['design_file_path']) || !empty($item['requires_design_file']) || !empty($item['requires_custom_terms']);
                                $singleProdTime = $item['formatted_production_time'] ?? null;
                            @endphp
                            @if($itemIsCustom && $singleProdTime)
                                <div style="font-size: 0.74rem; color: var(--text-magenta); font-weight: 700; margin-bottom: 0.65rem; display: inline-flex; align-items: center; gap: 0.4rem; background: var(--brand-magenta-soft); border: 1px solid var(--brand-magenta-soft); padding: 0.25rem 0.65rem; border-radius: 9999px;">
                                    <i class="fa-solid fa-stopwatch" style="color: var(--text-magenta); font-size: 0.7rem;"></i> Est. Production: {{ $singleProdTime }}
                                </div>
                            @endif


                            @if(!empty($item['options']))
                                @php
                                    $optionImages = !empty($item['product_id'])
                                        ? \App\Models\ProductOption::imageMapFor((int) $item['product_id'])
                                        : [];
                                @endphp
                                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                                    @foreach($item['options'] as $optKey => $optVal)
                                        @php $optImg = $optionImages[\App\Models\ProductOption::imageMapKey($optKey, $optVal)] ?? null; @endphp
                                        <span style="display:inline-flex; align-items:center; gap:0.3rem; background: var(--text-disabled); color: var(--text-muted); font-size: 0.75rem; font-weight: 600; padding: 0.15rem 0.45rem; border-radius: 4px;">
                                            @if($optImg)
                                                <img src="{{ $optImg }}" alt="" loading="lazy" style="width:17px; height:17px; border-radius:4px; object-fit:cover; border:1px solid var(--border-color); background:var(--bg-surface); flex-shrink:0;">
                                            @endif
                                            {{ $optKey }}: {{ $optVal }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Scenario A/B: catalog-attached artwork satisfies the requirement, so
                                 only render the uploader when no design file exists yet --}}
                            @if(!$isAdminOrSuper && !empty($item['requires_design_file']) && empty($item['design_file_path']))
                            <!-- ARTWORK DRAG & DROP BOX -->
                            <div>
                                <label style="font-size: 0.86rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 0.4rem;">
                                    <i class="fa-solid fa-cloud-arrow-up" style="color: var(--brand-cyan-fg);"></i> Upload Artwork File(s) for {{ $item['name'] }} <span style="color: var(--text-danger);">*</span>
                                    <span style="font-size: 0.68rem; font-weight: 700; color: var(--text-danger); background: var(--danger-soft); padding: 0.1rem 0.4rem; border-radius: 4px; margin-left: 0.25rem;">REQUIRED</span>
                                </label>

                                <div class="artwork-dropzone" data-item-key="{{ $key }}" data-item-name="{{ $item['name'] }}" style="border: 2px dashed var(--border-strong); background: var(--bg-surface); border-radius: 10px; padding: 1.5rem 1rem; text-align: center; cursor: pointer; transition: all 0.2s ease;">
                                    <input type="file" class="artwork-file-input" id="item_files_input_{{ $key }}" name="item_files[{{ $key }}][]" multiple accept=".png,.jpg,.jpeg,.pdf,.ai,.psd,.svg,.zip" style="display: none;">
                                    <div class="dropzone-prompt">
                                        <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--accent-soft); color: var(--brand-cyan-fg); display: flex; align-items: center; justify-content: center; margin: 0 auto 0.6rem; font-size: 1.4rem;">
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                        </div>
                                        <div style="font-size: 0.92rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">
                                            Drag &amp; drop artwork image(s) or file(s) here
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                                            or <span style="color: var(--brand-cyan-fg); font-weight: 700; text-decoration: underline;">browse from your computer</span>
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-subtle); margin-top: 0.5rem;">
                                            Supports PNG, JPG, JPEG, PDF, AI, PSD, SVG, ZIP (Multiple files allowed, up to 20MB each)
                                        </div>
                                    </div>
                                </div>
                                <div class="artwork-preview-grid" id="preview_grid_{{ $key }}" style="display: none; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.75rem; margin-top: 0.75rem;"></div>
                                <div class="artwork-missing-warning" data-warn-key="{{ $key }}" style="display: none; margin-top: 0.5rem; padding: 0.45rem 0.65rem; background: var(--danger-soft); border: 1px solid var(--danger-border); border-radius: 6px; font-size: 0.8rem; color: var(--text-danger); font-weight: 600; align-items: center; gap: 0.4rem;">
                                    <i class="fa-solid fa-paperclip"></i> Artwork file is required for this custom item — please upload before ordering.
                                </div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($isAdminOrSuper)
                    <!-- PAYMENT TYPE (Staff POS walk-in — Cash vs Online/E-wallet category toggle) -->
                    <div class="card" style="padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; border: 1.5px solid var(--border-color); background: var(--bg-page);">
                        @php
                            // Staff POS records what the customer actually paid — always show
                            // ALL active methods (Cash included), even for custom-item carts.
                            $walkinMethods = $allPaymentMethods ?? $paymentMethods;
                            $walkinCashMethods = $walkinMethods->filter(fn($pm) => $pm->isCashOrOfflineMethod() || !(bool)$pm->is_online_payment);
                            $walkinOnlineMethods = $walkinMethods->filter(fn($pm) => $pm->is_online_payment && !$pm->isCashOrOfflineMethod());
                            $walkinHasCash = $walkinCashMethods->isNotEmpty();
                            $walkinDefaultIsCash = $walkinHasCash; // Cash stays the POS default
                            $walkinDefaultOnline = $walkinOnlineMethods->first();
                        @endphp

                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-credit-card" style="color: var(--brand-cyan-fg); font-size: 1rem;"></i>
                                <span style="font-size: 0.92rem; font-weight: 800; color: var(--text-main);">Payment Type:</span>
                            </div>

                            {{-- Category segmented control: Online / E-wallet vs Cash --}}
                            <div class="walkin-seg-track" role="group" aria-label="Payment type category">
                                <label class="walkin-pay-cat {{ !$walkinDefaultIsCash ? 'cat-active-online' : '' }}" data-cat="online">
                                    <input type="radio" name="walkin_pay_cat" value="online" {{ !$walkinDefaultIsCash ? 'checked' : '' }} style="display: none;">
                                    <i class="fa-solid fa-mobile-screen" style="font-size: 0.78rem;"></i>
                                    Online / E-wallet
                                </label>
                                <label class="walkin-pay-cat {{ $walkinDefaultIsCash ? 'cat-active-cash' : '' }}" data-cat="cash" data-is-cash="1" data-is-online="0">
                                    <input type="radio" name="payment_method" value="{{ $walkinCashMethods->first()?->name ?? 'Cash' }}" {{ $walkinDefaultIsCash ? 'checked' : '' }} style="display: none;">
                                    <i class="fa-solid fa-money-bill-wave" style="font-size: 0.78rem;"></i>
                                    Cash
                                </label>
                            </div>
                        </div>

                        {{-- Sub-method chips: shown only when Online / E-wallet is active --}}
                        <div id="walkin_online_methods" class="walkin-sub-chips" style="display: {{ !$walkinDefaultIsCash ? 'flex' : 'none' }}; gap: 0.5rem; margin-top: 0.85rem; flex-wrap: wrap; align-items: center;">
                            <span class="walkin-sub-label"><i class="fa-solid fa-arrow-down"></i>Select e-wallet:</span>
                            @foreach($walkinOnlineMethods as $pm)
                                @php $isChecked = !$walkinDefaultIsCash && $walkinDefaultOnline && $pm->id === $walkinDefaultOnline->id; @endphp
                                <label class="walkin-pay-toggle {{ $isChecked ? 'chip-active' : '' }}" data-is-online="1" data-is-cash="0">
                                    <input type="radio" name="payment_method" value="{{ $pm->name }}" {{ $isChecked ? 'checked' : '' }} style="display: none;">
                                    <i class="fa-solid fa-mobile-screen" style="font-size: 0.78rem;"></i>
                                    {{ $pm->name }}
                                </label>
                            @endforeach
                        </div>

                        {{-- Proof upload (only visible for online methods, optional for walk-in) --}}
                        <div id="proof_upload_box" style="display: {{ (!$walkinDefaultIsCash && $walkinDefaultOnline) ? 'block' : 'none' }}; margin-top: 1rem; background: var(--accent-soft); border: 1.5px solid var(--info-border); border-radius: 12px; padding: 0.9rem 1.1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label style="font-weight: 700; font-size: 0.82rem; color: var(--text-main); margin: 0;">
                                    <i class="fa-solid fa-cloud-arrow-up" style="color: var(--brand-cyan-fg);"></i> Payment Screenshot
                                </label>
                                <span style="font-size: 0.7rem; font-weight: 700; background: var(--bg-subtle); color: var(--text-muted); padding: 0.1rem 0.45rem; border-radius: 4px;">Optional</span>
                            </div>
                            <div class="artwork-dropzone" id="proof-dropzone" onclick="document.getElementById('proof_of_payment_input').click()" style="border: 2px dashed var(--accent-color); border-radius: 10px; background: var(--accent-soft); padding: 0.85rem; cursor: pointer; text-align: center;">
                                <input type="file" id="proof_of_payment_input" name="proof_of_payment" accept="image/jpeg,image/jpg,image/jfif,image/png,image/webp,image/gif" style="display: none;" onchange="window.handleProofFileSelect(this)">
                                <div id="proof-empty-state">
                                    <i class="fa-solid fa-file-arrow-up" style="font-size: 1.25rem; color: var(--brand-cyan-fg); margin-bottom: 0.2rem;"></i>
                                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-main);">Click or drag receipt here</div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">PNG, JPG, WEBP (Max 10MB)</div>
                                </div>
                                <div id="proof-file-selected" style="display: none; align-items: center; justify-content: space-between; background: var(--bg-surface); padding: 0.5rem 0.75rem; border-radius: 8px; border: 1.5px solid var(--info-border);">
                                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                                        <img id="proof-thumbnail-img" src="" style="width: 36px; height: 36px; border-radius: 5px; object-fit: cover;">
                                        <div style="text-align: left;">
                                            <div id="proof-filename" style="font-size: 0.78rem; font-weight: 700; color: var(--text-main);"></div>
                                            <div id="proof-filesize" style="font-size: 0.68rem; color: var(--text-muted);"></div>
                                        </div>
                                    </div>
                                    <button type="button" onclick="event.stopPropagation(); clearProofFile();" style="background: var(--danger-soft); border: 1px solid var(--danger-border); color: var(--text-danger); font-size: 0.72rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 5px; cursor: pointer;">Remove</button>
                                </div>
                            </div>
                        </div>

                        {{-- POS walk-in orders are settled at the counter — always full payment --}}
                        <input type="hidden" name="payment_plan" value="full">
                    </div>
                @else
                    @if($hasCustomItems)
                        <!-- PAYMENT PLAN SELECTION FOR CUSTOM ORDER -->
                        <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem; border: 1.5px solid var(--accent-color); background: var(--bg-page);">
                            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                                <i class="fa-solid fa-wallet" style="color: var(--brand-cyan-fg);"></i> Payment Term: Downpayment or Full Payment
                            </h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                                <label id="plan_card_downpayment" style="border: 2px solid var(--accent-color); background: var(--accent-soft); border-radius: var(--radius-md); padding: 1.1rem 1.25rem; display: block; cursor: pointer;">
                                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                        <input type="radio" name="payment_plan" value="downpayment" id="plan_downpayment" checked onchange="selectPaymentPlan('downpayment')" style="accent-color: var(--accent-color); margin-top: 3px;">
                                        <div>
                                            <strong style="font-size: 1rem; color: var(--text-accent);">50% Downpayment</strong>
                                            <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.25rem;">
                                                Pay <strong>₱{{ number_format($downpaymentAmount, 2) }}</strong> now. Balance of <strong>₱{{ number_format($remainingBalance, 2) }}</strong> on delivery/pickup.
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label id="plan_card_full" style="border: 1.5px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-md); padding: 1.1rem 1.25rem; display: block; cursor: pointer;">
                                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                        <input type="radio" name="payment_plan" value="full" id="plan_full" onchange="selectPaymentPlan('full')" style="accent-color: var(--accent-color); margin-top: 3px;">
                                        <div>
                                            <strong style="font-size: 1rem; color: var(--text-main);">100% Full Payment</strong>
                                            <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.25rem;">
                                                Pay full <strong>₱{{ number_format($subtotal, 2) }}</strong> now.
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="payment_plan" value="full">
                    @endif

                    <!-- PAYMENT METHODS LIST -->
                    <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                            <i class="fa-solid fa-credit-card" style="color: var(--brand-cyan-fg);"></i> Mode of Payment &amp; Proof of Payment
                        </h3>

                        @if($hasCustomItems)
                            <div class="p-3 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-xl font-medium mb-3" style="background: var(--warning-soft); border: 1px solid var(--warning-border); color: var(--text-warning); border-radius: 12px; padding: 0.75rem; font-size: 0.75rem; font-weight: 500; margin-bottom: 0.75rem;">
                                ⚠️ Custom items require online payment/downpayment before printing starts. Cash payment is disabled for this order.
                            </div>
                        @endif

                        {{-- CHANNELS availability status bar removed by design: the method cards
                             below already show their own ONLINE / DIGITAL vs CASH / PHYSICAL badges --}}

                        <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 1.25rem;">
                            @php $checkedAny = false; @endphp
                            @foreach($paymentMethods as $index => $pm)
                                @php
                                    $isCash = $pm->isCashOrOfflineMethod() || !(bool)$pm->is_online_payment;
                                    if ($hasCustomItems && $isCash) {
                                        continue;
                                    }
                                    $isChecked = !$checkedAny;
                                    $checkedAny = true;
                                    $pmNameLower = strtolower($pm->name);
                                    $isPickupOnly = (str_contains($pmNameLower, 'pickup') || str_contains($pmNameLower, 'cop') || str_contains($pmNameLower, 'pay at print shop'));
                                @endphp
                                <label class="payment-method-item" data-method-name="{{ $pmNameLower }}" data-is-online="{{ $pm->is_online_payment ? '1' : '0' }}" data-is-cash="{{ $isCash ? '1' : '0' }}" style="background: var(--bg-page); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem; display: block; cursor: pointer;">
                                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                        <input type="radio" name="payment_method" value="{{ $pm->name }}" {{ $isChecked ? 'checked' : '' }} onchange="validateCheckoutState(false)" style="accent-color: var(--cmyk-cyan); margin-top: 3px;">
                                        <div style="flex: 1;">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <strong style="font-size: 0.98rem; color: var(--text-main);">{{ $pm->name }}</strong>
                                                <span class="role-badge" style="background: {{ $pm->is_online_payment ? 'var(--accent-soft)' : 'var(--warning-soft)' }}; color: {{ $pm->is_online_payment ? 'var(--text-accent)' : 'var(--text-warning)' }}; font-size: 0.72rem; font-weight: 700;">
                                                    {{ $pm->is_online_payment ? 'ONLINE / DIGITAL' : 'CASH / PHYSICAL' }}
                                                </span>
                                            </div>
                                            @if($pm->account_name || $pm->account_number)
                                                <div class="text-xs text-[var(--text-muted)] font-medium mt-1 leading-relaxed">
                                                    @if($pm->account_name)
                                                        <div>Account Name: <strong class="text-[var(--text-main)]">{{ $pm->account_name }}</strong></div>
                                                    @endif
                                                    @if($pm->account_number)
                                                        <div>Account Number: <strong class="text-sky-600 font-bold select-all">{{ $pm->account_number }}</strong></div>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($pm->instructions)
                                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">{{ $pm->instructions }}</div>
                                            @endif
                                            @include('customer.partials.payment-qr', ['pm' => $pm])
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <!-- PROOF OF PAYMENT UPLOAD -->
                        <div id="proof_upload_box" class="mb-4" style="background: var(--accent-soft); border: 1.5px solid var(--info-border); border-radius: 14px; padding: 1.1rem 1.25rem;">
                            <label style="font-weight: 700; font-size: 0.88rem; color: var(--text-main); display: block; margin-bottom: 0.5rem;">
                                <i class="fa-solid fa-cloud-arrow-up" style="color: var(--brand-cyan-fg);"></i> Upload Payment Screenshot / Proof of Payment <span id="proof_required_badge" style="display: none; background: var(--danger-soft); color: var(--text-danger); font-size: 0.72rem; padding: 0.1rem 0.45rem; border-radius: 4px;">Required</span>
                            </label>
                            <div class="artwork-dropzone" id="proof-dropzone" onclick="document.getElementById('proof_of_payment_input').click()" style="border: 2px dashed var(--accent-color); border-radius: 12px; background: var(--accent-soft); padding: 1rem; cursor: pointer; text-align: center;">
                                <input type="file" id="proof_of_payment_input" name="proof_of_payment" accept="image/jpeg,image/jpg,image/jfif,image/png,image/webp,image/gif" style="display: none;" onchange="window.handleProofFileSelect(this)">
                                <div id="proof-empty-state">
                                    <i class="fa-solid fa-file-arrow-up" style="font-size: 1.5rem; color: var(--brand-cyan-fg); margin-bottom: 0.25rem;"></i>
                                    <div style="font-size: 0.86rem; font-weight: 700; color: var(--text-main);">Click or drag payment receipt image here</div>
                                    <div style="font-size: 0.74rem; color: var(--text-muted);">PNG, JPG, JPEG, WEBP (Max 10MB)</div>
                                </div>
                                <div id="proof-file-selected" style="display: none; align-items: center; justify-content: space-between; background: var(--bg-surface); padding: 0.6rem 0.85rem; border-radius: 8px; border: 1.5px solid var(--info-border);">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <img id="proof-thumbnail-img" src="" style="width: 44px; height: 44px; border-radius: 6px; object-fit: cover;">
                                        <div style="text-align: left;">
                                            <div id="proof-filename" style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);"></div>
                                            <div id="proof-filesize" style="font-size: 0.73rem; color: var(--text-muted);"></div>
                                        </div>
                                    </div>
                                    <button type="button" onclick="event.stopPropagation(); clearProofFile();" style="background: var(--danger-soft); border: 1px solid var(--danger-border); color: var(--text-danger); font-size: 0.8rem; font-weight: 700; padding: 0.3rem 0.6rem; border-radius: 6px; cursor: pointer;">Remove</button>
                                </div>
                            </div>
                            <div id="proof_missing_warning" style="display: none; align-items: flex-start; gap: 0.5rem; background: var(--danger-soft); border: 1.5px solid var(--danger-border); border-left: 4px solid var(--danger-color); border-radius: 8px; padding: 0.7rem 0.9rem; margin-top: 0.5rem; font-size: 0.82rem; color: var(--text-danger); font-weight: 700;">
                                <i class="fa-solid fa-triangle-exclamation" style="color: var(--text-danger); margin-top: 1px;"></i>
                                <span>Please upload your <strong>payment screenshot / proof of payment</strong> before placing your order.</span>
                            </div>
                        </div>

                        {{-- Cash notice for single cart — retired: the Cash card's own instruction text is enough; JS toggle kept harmless --}}
                        <div id="cash_payment_notice" style="display:none !important; background: var(--success-soft); border: 1.5px solid var(--success-border); border-left: 4px solid var(--success-color); border-radius: 12px; padding: 1.1rem 1.25rem; margin-bottom: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.6rem;">
                                <i class="fa-solid fa-money-bill-wave" style="color: var(--text-success); font-size: 1.2rem;"></i>
                                <div>
                                    <div style="font-size: 0.9rem; font-weight: 800; color: var(--text-success);">Mode of Payment: Cash</div>
                                    <div style="font-size: 0.8rem; color: var(--text-success); margin-top: 0.15rem;">
                                        Please pay in cash upon store pickup. No payment screenshot is required.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <!-- RIGHT COLUMN: STICKY ORDER SUMMARY -->
        <div class="card checkout-summary-sidebar" style="padding: 1.5rem 1.6rem;">
            <h3 style="font-size: 1.18rem; font-weight: 800; color: var(--text-main); margin-bottom: 1.15rem; border-bottom: 1.5px solid var(--border-color); padding-bottom: 0.65rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-receipt" style="color: var(--accent-color);"></i> Order Summary
            </h3>

            @if($isMixed)
                <!-- MIXED CART SUMMARY -->
                <div style="display: flex; flex-direction: column; gap: 0.65rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.92rem; color: var(--text-muted);">
                        <span><i class="fa-solid fa-box" style="color: var(--text-success); margin-right: 4px;"></i> Ready-Made Items:</span>
                        <strong style="color: var(--text-main);">₱{{ number_format($subtotalRegular, 2) }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 0.92rem; color: var(--text-muted);">
                        <span><i class="fa-solid fa-wand-magic-sparkles" style="color: var(--text-accent); margin-right: 4px;"></i> Custom Items:</span>
                        <strong style="color: var(--text-main);">₱{{ number_format($subtotalCustom, 2) }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 0.88rem; color: var(--text-muted); padding-top: 0.4rem; border-top: 1px dashed var(--border-color);">
                        <span>Total Combined Value:</span>
                        <strong style="color: var(--text-main);">₱{{ number_format($subtotal, 2) }}</strong>
                    </div>
                </div>

                <!-- HIGHLIGHTED SPLIT DUE AMOUNTS (strictly separate per group) -->
                <div style="background: var(--accent-soft); border: 1.5px solid var(--info-border); border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.35rem;">
                        <span style="font-weight: 800; font-size: 0.9rem; color: var(--text-accent);">Custom Items Due Today:</span>
                        <span id="mixed_due_now" style="font-size: 1.35rem; font-weight: 900; color: var(--accent-color);">₱{{ number_format($downpaymentAmount, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline; padding-top: 0.4rem; border-top: 1px dashed var(--info-border); margin-bottom: 0.4rem;">
                        <span id="mixed_reg_label" style="font-weight: 700; font-size: 0.85rem; color: var(--text-success);">Ready-Made {{ $regMethodOnlineInitial ? '(' . $regMethodNameInitial . ') — Due Today' : '— Pay on Pickup / Delivery (' . $regMethodNameInitial . ')' }}:</span>
                        <strong id="mixed_reg_due" style="font-size: 1rem; color: var(--text-success);">₱{{ number_format($subtotalRegular, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline; font-size: 0.82rem; color: var(--text-accent); padding-top: 0.35rem; border-top: 1px dashed var(--info-border);">
                        <span>Custom Balance on Delivery:</span>
                        <strong id="mixed_remaining_balance" style="font-size: 0.9rem;">₱{{ number_format($remainingBalance, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline; padding-top: 0.5rem; margin-top: 0.45rem; border-top: 1.5px solid var(--accent-color);">
                        <span style="font-weight: 900; font-size: 0.9rem; color: var(--text-accent);">Total Due Now (Online):</span>
                        <strong id="mixed_total_now" style="font-size: 1.2rem; font-weight: 900; color: var(--text-accent);">₱{{ number_format($initialTotalDueNow, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 0.25rem;">
                        <span style="font-weight: 800; font-size: 0.82rem; color: var(--text-success);">Total on Pickup / Delivery (Cash):</span>
                        <strong id="mixed_total_later" style="font-size: 0.95rem; color: var(--text-success);">₱{{ number_format($initialTotalDueLater, 2) }}</strong>
                    </div>
                </div>
            @elseif($hasCustomItems && !$isAdminOrSuper)
                <!-- CUSTOM SINGLE CART SUMMARY -->
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--text-muted);">
                    <span>Subtotal:</span>
                    <strong style="color: var(--text-main);">₱{{ number_format($subtotal, 2) }}</strong>
                </div>
                <div style="background: var(--accent-soft); border: 1.5px solid var(--info-border); border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.35rem;">
                        <span style="font-weight: 800; font-size: 0.9rem; color: var(--text-accent);">Amount Due Now:</span>
                        <span id="summary_due_now" style="font-size: 1.35rem; font-weight: 900; color: var(--accent-color);">₱{{ number_format($downpaymentAmount, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline; font-size: 0.82rem; color: var(--text-accent); padding-top: 0.35rem; border-top: 1px dashed var(--info-border);">
                        <span>Remaining Balance:</span>
                        <strong id="summary_remaining_balance" style="font-size: 0.9rem;">₱{{ number_format($remainingBalance, 2) }}</strong>
                    </div>
                </div>
            @else
                <!-- REGULAR SINGLE CART SUMMARY -->
                <div style="border-top: 1px solid var(--border-color); padding-top: 0.85rem; margin-bottom: {{ $isAdminOrSuper ? '0.6rem' : '1.15rem' }}; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; font-size: 1.05rem; color: var(--text-main);">Total Amount:</span>
                    <span style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);">₱{{ number_format($subtotal, 2) }}</span>
                </div>
                @if($isAdminOrSuper)
                {{-- Walk-in POS: shows the payment type the staff selected above --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.15rem;">
                    <span style="font-weight: 700; font-size: 0.9rem; color: var(--text-muted);">Payment:</span>
                    <strong id="walkin_pay_label" style="font-size: 0.9rem; color: var(--text-warning);">{{ $walkinDefaultName ?? 'Cash' }} (Cash)</strong>
                </div>
                @endif
            @endif

            @if($hasCustomItems && !empty($formattedCustomProdTime))
                <!-- ESTIMATED PRODUCTION TURNAROUND (CUSTOM ORDERS ONLY) -->
                <div style="background: var(--brand-magenta-soft); border: 1px solid var(--brand-magenta-soft); border-radius: 12px; padding: 0.8rem 0.95rem; margin-bottom: 1rem; display: flex; align-items: flex-start; gap: 0.7rem;">
                    <span style="flex: none; width: 34px; height: 34px; border-radius: 50%; background: var(--brand-magenta-soft); color: var(--text-magenta); display: inline-flex; align-items: center; justify-content: center; font-size: 1rem;">
                        <i class="fa-solid fa-stopwatch"></i>
                    </span>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <span style="font-weight: 800; font-size: 0.82rem; color: var(--text-magenta);">Est. Production Time</span>
                            <span style="font-weight: 800; font-size: 0.8rem; color: var(--text-magenta); background: var(--brand-magenta-soft); padding: 0.12rem 0.6rem; border-radius: 9999px;">{{ $formattedCustomProdTime }}</span>
                        </div>
                        <div style="font-size: 0.72rem; color: var(--text-magenta); margin-top: 0.2rem; line-height: 1.35;">
                            Custom item turnaround. Work starts once your artwork files are verified.
                        </div>
                    </div>
                </div>
            @endif

            @if($requiresCustomTerms && !$isAdminOrSuper)
                <div x-data="{ openTermsModal: false }">
                    <!-- CUSTOM TERMS AGREEMENT CHECKBOX -->
                    <div id="custom_terms_box" style="background: var(--accent-soft); border: 1.5px solid var(--accent-color); border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1rem; transition: all 0.2s ease;">
                        <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer;" for="custom_terms_agreed_cb">
                            <input type="checkbox" id="custom_terms_agreed_cb" name="custom_terms_agreed" value="1" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--accent-color); margin-top: 2px;" onchange="validateCheckoutState(false)">
                            <div style="font-size: 0.82rem; color: var(--text-main); line-height: 1.45;">
                                <strong>I agree to the Customized Order Terms &amp; Conditions</strong> (Digital proofs, non-refundable custom production once approved).
                                <button type="button" @click.stop.prevent="openTermsModal = true" style="color: var(--text-accent); font-weight: 700; text-decoration: underline; background: none; border: none; padding: 0; margin-left: 0.25rem; cursor: pointer; font-size: inherit; display: inline;">
                                    Read More
                                </button>
                            </div>
                        </label>
                    </div>

                    <template x-teleport="body">
                        <!-- Fixed Modal Backdrop -->
                        <div x-show="openTermsModal" 
                             x-transition.opacity
                             class="fixed inset-0 z-[100] bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6" 
                             style="display: none; z-index: 10005;"
                             x-cloak>
                            
                            <!-- Perfectly Centered Modal Card Container -->
                            <div class="bg-[var(--bg-surface)] rounded-2xl w-full max-w-lg max-h-[90vh] flex flex-col relative shadow-2xl border border-[var(--border-color)] overflow-hidden" 
                                 @click.away="openTermsModal = false">

                                <!-- 1. Fixed Modal Header -->
                                <div class="flex items-center justify-between px-6 py-4 border-b border-[var(--border-color)] shrink-0 bg-[var(--bg-surface)]">
                                    <h3 class="font-bold text-[var(--text-main)] text-base sm:text-lg flex items-center gap-2">
                                        📋 Customized Order Policy
                                    </h3>
                                    <button type="button" 
                                            @click="openTermsModal = false" 
                                            class="w-8 h-8 rounded-full bg-[var(--bg-subtle)] hover:bg-[var(--bg-stripe)] text-[var(--text-muted)] hover:text-[var(--text-main)] flex items-center justify-center transition-colors border-0 outline-hidden"
                                            style="border: none !important; outline: none !important; cursor: pointer;">
                                        <svg class="w-4 h-4" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- 2. Scrollable Body Content -->
                                <div class="p-6 overflow-y-auto text-xs sm:text-sm text-[var(--text-muted)] space-y-3 flex-1">
                                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 font-medium">
                                        ⚠️ <strong>Strict Policy on Custom Orders:</strong> Orders cannot be canceled or modified once artwork is approved and production begins.
                                    </div>
                                    
                                    <div>
                                        <strong class="text-[var(--text-main)]">Why are custom orders non-refundable?</strong>
                                        <p class="mt-1">
                                            Custom printing allocates and consumes specialized raw materials (inks, fabrics, paper stock, vinyl) specifically tailored to your design. Once production starts, these materials cannot be reused or resold, resulting in shop loss.
                                        </p>
                                    </div>

                                    <div>
                                        <strong class="text-[var(--text-main)]">Proofing &amp; Approvals:</strong>
                                        <p class="mt-1">
                                            Please thoroughly review your digital proof/artwork before placing your order. Ensure all text, spelling, colors, and dimensions are correct.
                                        </p>
                                    </div>

                                    <div class="p-3 bg-sky-50 border border-sky-100 rounded-lg text-sky-800">
                                        <strong>Need modifications or have special instructions?</strong><br>
                                        Contact our design team before confirming your order.
                                    </div>
                                </div>

                                <!-- 3. Fixed Footer Actions -->
                                <div class="px-6 py-4 border-t border-[var(--border-color)] bg-[var(--bg-subtle)] flex items-center justify-end gap-3 shrink-0">
                                    <button type="button" 
                                            @click="openTermsModal = false; if (typeof $wire !== 'undefined') { $wire.set('agree_terms', true); } const cb = document.getElementById('custom_terms_agreed_cb'); if (cb) { cb.checked = true; } if (typeof validateCheckoutState === 'function') { validateCheckoutState(false); }" 
                                            class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-lg transition-colors shadow-2xs cursor-pointer">
                                        I Understand &amp; Agree
                                    </button>
                                </div>

                            </div>
                        </div>
                    </template>
                </div>
            @endif

            <div style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                @if($isAdminOrSuper)
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.95rem; font-size: 1.05rem; font-weight: 800;">
                        <i class="fa-solid fa-receipt"></i> Complete Walk-in Order
                    </button>
                @else
                    @php
                        $initiallyDisabled = $hasCustomItems && !$isAdminOrSuper;
                    @endphp
                    <button type="submit" id="submitOrderBtn" class="btn btn-primary" {{ $initiallyDisabled ? 'disabled' : '' }} style="width: 100%; padding: 0.95rem; font-size: 1.05rem; font-weight: 800; transition: all 0.2s ease; {{ $initiallyDisabled ? 'opacity: 0.5; cursor: not-allowed; filter: grayscale(60%); pointer-events: none;' : '' }}">
                        <i class="fa-solid fa-cart-shopping"></i> {{ $isMixed ? 'Place Order (2 Sub-Orders)' : 'Place Order & Pay' }}
                    </button>
                    <div id="checkout_validation_hint" style="{{ $initiallyDisabled ? 'display: block;' : 'display: none;' }} margin-top: 0.65rem; padding: 0.5rem 0.75rem; background: var(--danger-soft); border: 1px solid var(--danger-border); border-radius: 6px; font-size: 0.76rem; color: var(--text-danger); text-align: center; line-height: 1.4; font-weight: 600;">
                        <span id="checkout_validation_hint_text">
                            <i class="fa-solid fa-cloud-arrow-up" style="margin-right: 3px;"></i> Please <strong>upload your payment screenshot</strong> to activate the order button.
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</form>

@if($isMixed)
<!-- SPLIT-CART PAYMENT BREAKDOWN CONFIRMATION MODAL -->
<div id="splitPaymentModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: var(--bg-surface); border-radius: 16px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); overflow: hidden;">
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, var(--text-main) 0%, var(--text-main) 100%); color: var(--text-inverse); padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(0, 153, 255, 0.2); color: var(--text-accent); display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-code-fork"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0; color: var(--text-inverse);">Split Payment Summary Confirmation</h3>
                    <p style="font-size: 0.76rem; color: var(--text-subtle); margin: 2px 0 0 0;">Review what you pay online now vs. in cash later</p>
                </div>
            </div>
            <button type="button" onclick="closeSplitPaymentModal()" style="background: none; border: none; color: var(--text-subtle); font-size: 1.3rem; cursor: pointer; padding: 4px; line-height: 1;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-subtle)'">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div style="padding: 1.5rem;">
            <!-- Guidance Alert Notice -->
            <div style="background: var(--warning-soft); border: 1px solid var(--warning-border); border-left: 4px solid var(--warning-color); border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
                <div style="font-size: 0.84rem; font-weight: 800; color: var(--text-warning); display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Clear Payment Guidance
                </div>
                <div style="font-size: 0.79rem; color: var(--text-warning); margin-top: 0.25rem; line-height: 1.45;">
                    Your cart contains both <strong>Ready-Made Products</strong> and <strong>Customized Items</strong>. Please ensure you only transfer the <strong>Online Amount</strong> right now to avoid confusion.
                </div>
            </div>

            <!-- Amount 1: Online Payment Due Today -->
            <div style="background: var(--accent-soft); border: 2px solid var(--accent-color); border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1rem;">
                <div style="font-size: 0.80rem; font-weight: 800; color: var(--text-accent); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.35rem;">
                    <i class="fa-solid fa-mobile-screen-button"></i> Amount Due Today (Online Payment / Downpayment)
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div id="modal_custom_plan_label" style="font-size: 0.85rem; color: var(--text-accent); font-weight: 700;">
                            Custom Item Downpayment (50%)
                        </div>
                        <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 2px;">
                            Pay now via <strong id="modal_online_method_name" style="color: var(--text-accent);">GCash</strong> &amp; attach one receipt for all online payments
                        </div>
                    </div>
                    <div id="modal_amount_due_today" style="font-size: 1.65rem; font-weight: 900; color: var(--text-accent); font-family: monospace;">
                        ₱{{ number_format($downpaymentAmount, 2) }}
                    </div>
                </div>
                <div id="modal_reg_due_today_row" style="display: none; border-top: 1px dashed var(--info-border); margin-top: 0.65rem; padding-top: 0.55rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 0.85rem; color: var(--text-accent); font-weight: 700;">
                                Ready-Made Items (<span id="modal_reg_online_name">GCash</span>)
                            </div>
                            <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 2px;">
                                Ready-made portion of this online total
                            </div>
                        </div>
                        <div id="modal_reg_due_today" style="font-size: 1.2rem; font-weight: 900; color: var(--text-accent); font-family: monospace;">₱0.00</div>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1.5px solid var(--accent-color); margin-top: 0.65rem; padding-top: 0.55rem;">
                    <div style="font-size: 0.85rem; font-weight: 900; color: var(--text-accent);">Total Due Today (Online)</div>
                    <div id="modal_total_today" style="font-size: 1.35rem; font-weight: 900; color: var(--text-accent); font-family: monospace;">₱0.00</div>
                </div>
            </div>

            <!-- Amount 2: Cash Payment Due Later -->
            <div style="background: var(--success-soft); border: 2px solid var(--success-color); border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
                <div style="font-size: 0.80rem; font-weight: 800; color: var(--text-success); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.35rem;">
                    <i class="fa-solid fa-money-bill-wave"></i> Amount Due Upon Pickup / Delivery (Cash)
                </div>
                <div id="modal_reg_due_later_row" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div id="modal_cash_plan_label" style="font-size: 0.85rem; color: var(--text-success); font-weight: 700;">
                            Ready-Made Items (Cash on Pickup / Delivery)
                        </div>
                        <div id="modal_cash_details_label" style="font-size: 0.76rem; color: var(--text-muted); margin-top: 2px;">
                            Pay in cash upon store collection or courier arrival — separate from the custom payment
                        </div>
                    </div>
                    <div id="modal_amount_due_later" style="font-size: 1.65rem; font-weight: 900; color: var(--text-success); font-family: monospace;">
                        ₱{{ number_format($subtotalRegular, 2) }}
                    </div>
                </div>
                <div id="modal_custom_due_later_row" style="display: none; border-top: 1px dashed var(--success-border); margin-top: 0.65rem; padding-top: 0.55rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 0.85rem; color: var(--text-success); font-weight: 700;">Custom Items Remaining Balance</div>
                            <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 2px;">Settle in cash once the custom items are released</div>
                        </div>
                        <div id="modal_custom_due_later" style="font-size: 1.2rem; font-weight: 900; color: var(--text-success); font-family: monospace;">₱0.00</div>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1.5px solid var(--success-color); margin-top: 0.65rem; padding-top: 0.55rem;">
                    <div style="font-size: 0.85rem; font-weight: 900; color: var(--text-success);">Total Payable Later (Cash)</div>
                    <div id="modal_total_later" style="font-size: 1.35rem; font-weight: 900; color: var(--text-success); font-family: monospace;">₱0.00</div>
                </div>
            </div>

            <!-- Summary Guidance Points -->
            <div style="background: var(--bg-page); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.85rem 1rem; font-size: 0.78rem; color: var(--text-muted);">
                <div style="font-weight: 700; color: var(--text-muted); margin-bottom: 0.3rem;">Summary Breakdown Guidance:</div>
                <ul style="margin: 0; padding-left: 1.2rem; line-height: 1.45;">
                    <li><strong>Online Transfer:</strong> Transfer only the <strong>Amount Due Today</strong> to the official QR / Account number.</li>
                    <li><strong>Cash Upon Collection / Delivery:</strong> Ready-made items and any remaining balance will be settled directly in cash upon handover.</li>
                </ul>
            </div>
        </div>

        <!-- Modal Footer Actions -->
        <div style="background: var(--bg-page); border-top: 1px solid var(--border-color); padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" onclick="closeSplitPaymentModal()" class="btn btn-outline" style="padding: 0.6rem 1.1rem; font-size: 0.88rem;">
                Back to Review
            </button>
            <button type="button" id="modalConfirmBtn" onclick="confirmAndPlaceSplitOrder()" class="btn btn-primary" style="padding: 0.6rem 1.3rem; font-size: 0.88rem; background: var(--accent-color); border-color: var(--accent-color);">
                <i class="fa-solid fa-check"></i> I Understand, Place Order
            </button>
        </div>
    </div>
</div>
@endif

<script>
    const IS_MIXED_CART = @json($isMixed);
    const HAS_CUSTOM_ITEMS = @json($hasCustomItems);
    const IS_ADMIN_OR_SUPER = @json($isAdminOrSuper);
    const SUBTOTAL_REGULAR = @json($subtotalRegular ?? 0);
    const SUBTOTAL_CUSTOM = @json($subtotalCustom ?? 0);
    const SUBTOTAL_TOTAL = @json($subtotal ?? 0);

    function getSplitBreakdownData() {
        const customPlanInput = document.querySelector('input[name="custom_payment_plan"]:checked');
        const customPlan = customPlanInput ? customPlanInput.value : 'downpayment';

        let customDueNow = 0;
        let customDueLater = 0;
        if (customPlan === 'downpayment') {
            customDueNow = Math.round((SUBTOTAL_CUSTOM * 0.5) * 100) / 100;
            customDueLater = Math.round((SUBTOTAL_CUSTOM - customDueNow) * 100) / 100;
        } else {
            customDueNow = SUBTOTAL_CUSTOM;
            customDueLater = 0;
        }

        const regMethodInput = document.querySelector('input[name="regular_payment_method"]:checked');
        const regMethodName = regMethodInput ? regMethodInput.value : 'Cash on Store Pickup';
        const regLabel = regMethodInput ? regMethodInput.closest('label') : null;
        const isRegOnline = !!(regLabel && regLabel.dataset && regLabel.dataset.isOnline === '1');

        const regularDueNow = isRegOnline ? SUBTOTAL_REGULAR : 0;
        const regularDueLater = isRegOnline ? 0 : SUBTOTAL_REGULAR;

        const custMethodInput = document.querySelector('input[name="custom_payment_method"]:checked');
        const custMethodName = custMethodInput ? custMethodInput.value : 'GCash';

        const totalDueToday = customDueNow + regularDueNow;
        const totalDueLater = customDueLater + regularDueLater;

        return {
            customPlan,
            customDueNow,
            customDueLater,
            regMethodName,
            isRegOnline,
            regularDueNow,
            regularDueLater,
            custMethodName,
            totalDueToday,
            totalDueLater
        };
    }

    function fmtPeso(value) {
        return '₱' + Number(value).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function setText(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    // Walk-in POS: Cash vs Online/E-wallet segmented control — syncs the
    // payment_method radio, shows the e-wallet sub-chips, toggles CSS classes.
    function updateWalkinPayNotif(el) {
        const catLabel = el.closest('label');
        if (!catLabel) return;

        if (catLabel.dataset.cat === 'online') {
            // Show the e-wallet sub-chips and make the first one the submitted value,
            // so the category click alone already records a real payment method.
            const subBox = document.getElementById('walkin_online_methods');
            const subRadios = subBox ? Array.from(subBox.querySelectorAll('input[type="radio"]')) : [];
            if (subRadios.length) {
                if (subBox.style.display === 'none' || subBox.style.display === '') {
                    subBox.style.display = 'flex';
                }
                if (!subRadios.some(r => r.checked)) subRadios[0].checked = true;
            } else if (el.checked) {
                // No online methods configured — fall back to cash so the UI and the
                // submitted payment_method never disagree.
                el.checked = false;
                const cashCat = document.querySelector('.walkin-pay-cat[data-cat="cash"] input');
                if (cashCat) cashCat.checked = true;
            }
        } else if (catLabel.dataset.cat === 'cash') {
            const subBox = document.getElementById('walkin_online_methods');
            if (subBox) subBox.style.display = 'none';
        }

        refreshWalkinPayStyles();
        // Trigger existing validation logic (proof visibility, sidebar, submit)
        validateCheckoutState(false);
    }

    // Category is active from the real payment_method selection, so the segmented
    // control can never disagree with what gets submitted.
    function refreshWalkinPayStyles() {
        const onlineChipChecked = !!document.querySelector('.walkin-pay-toggle input[type="radio"]:checked');

        // Keep the standalone "online" category radio in sync. It lives in its own
        // group (walkin_pay_cat), so selecting Cash never unchecks it automatically —
        // if it stayed checked, re-clicking "Online / E-wallet" would fire no change
        // event and the segment would feel dead. Uncheck it whenever Cash is active.
        const onlineCatRadio = document.querySelector('input[name="walkin_pay_cat"]');
        if (onlineCatRadio) onlineCatRadio.checked = onlineChipChecked;

        document.querySelectorAll('.walkin-pay-cat').forEach(function (btn) {
            const input = btn.querySelector('input[type="radio"]');
            const activeOnline = btn.dataset.cat === 'online' && onlineChipChecked;
            const activeCash = btn.dataset.cat === 'cash' && input && input.checked && !onlineChipChecked;
            btn.classList.toggle('cat-active-online', activeOnline);
            btn.classList.toggle('cat-active-cash', activeCash);
        });
        document.querySelectorAll('.walkin-pay-toggle').forEach(function (btn) {
            const input = btn.querySelector('input[type="radio"]');
            btn.classList.toggle('chip-active', !!(input && input.checked));
        });

        // Walk-in proof visibility lives here, not in validateCheckoutState: the walk-in
        // submit button has no #submitOrderBtn, so that function early-returns for staff
        // POS and never hides the box. Screenshot shows only when an e-wallet is active.
        const walkinProofBox = document.getElementById('proof_upload_box');
        if (walkinProofBox) {
            walkinProofBox.style.display = onlineChipChecked ? 'block' : 'none';
        }

        // Keep the Order Summary payment label in sync with the real selection.
        // The server-rendered value is a static fallback, so refresh it here too.
        const walkinPayLabel = document.getElementById('walkin_pay_label');
        if (walkinPayLabel) {
            const selected = document.querySelector('input[name="payment_method"]:checked');
            const name = selected ? selected.value : 'Cash';
            walkinPayLabel.textContent = name + (onlineChipChecked ? ' (Online)' : ' (Cash)');
            walkinPayLabel.style.color = onlineChipChecked ? '#0284C7' : '#92400E';
        }
    }

    // Single-cart walk-in POS: react to category / e-wallet radio changes.
    document.addEventListener('change', function (e) {
        const t = e.target;
        if (t && t.name && (t.name === 'walkin_pay_cat' || (t.name === 'payment_method' && t.closest && t.closest('.walkin-pay-toggle')))) {
            updateWalkinPayNotif(t);
        } else if (t && t.name === 'payment_method' && t.closest && t.closest('.walkin-pay-cat')) {
            // Cash category radio clicked: hide sub-chips, then resync styles.
            const subBox = document.getElementById('walkin_online_methods');
            if (subBox) subBox.style.display = 'none';
            refreshWalkinPayStyles();
            validateCheckoutState(false);
        }
    });

    // Safety net: if a segment is clicked while its radio is already checked (no
    // change event fires), force the toggle so the control is never unresponsive.
    document.querySelectorAll('.walkin-pay-cat').forEach(function (lbl) {
        lbl.addEventListener('click', function () {
            const radio = lbl.querySelector('input[type="radio"]');
            if (!radio) return;
            if (lbl.dataset.cat === 'online') {
                const subBox = document.getElementById('walkin_online_methods');
                const subRadios = subBox ? Array.from(subBox.querySelectorAll('input[type="radio"]')) : [];
                // Already on online but chips hidden (edge) → reopen them.
                if (radio.checked && subRadios.some(r => r.checked) && subBox && subBox.style.display === 'none') {
                    subBox.style.display = 'flex';
                }
            }
        });
    });

    // Initial state: sync segment styles + proof box visibility to the server-rendered default.
    (function () {
        refreshWalkinPayStyles();
    })();

    // Refreshes every split amount so the ready-made payment and the custom
    // payment are always shown (and charged) separately — never combined.
    function updateSplitPaymentAlertAmounts() {
        if (!IS_MIXED_CART) return;
        const data = getSplitBreakdownData();

        // Custom group — digital only (GCash / downpayment)
        // (The old split payment alert box was removed — sidebar rows carry these amounts)

        // Ready-made group — its own method, its own due timing

        // Combined totals: everything online now vs. everything cash later

        // Sidebar summary rows
        setText('mixed_due_now', fmtPeso(data.customDueNow));
        setText('mixed_remaining_balance', fmtPeso(data.customDueLater));
        setText('mixed_reg_due', fmtPeso(data.isRegOnline ? data.regularDueNow : data.regularDueLater));
        setText('mixed_reg_label', 'Ready-Made ' + (data.isRegOnline
            ? '(' + data.regMethodName + ') — Due Today:'
            : '— Pay on Pickup / Delivery (' + data.regMethodName + '):'));
        setText('mixed_total_now', fmtPeso(data.totalDueToday));
        setText('mixed_total_later', fmtPeso(data.totalDueLater));
    }

    function openSplitPaymentModal(mode = 'submit') {
        const modal = document.getElementById('splitPaymentModal');
        if (!modal) return;

        const data = getSplitBreakdownData();

        const dueTodayEl = document.getElementById('modal_amount_due_today');
        const dueLaterEl = document.getElementById('modal_amount_due_later');
        const customPlanLabel = document.getElementById('modal_custom_plan_label');
        const onlineMethodName = document.getElementById('modal_online_method_name');
        const cashPlanLabel = document.getElementById('modal_cash_plan_label');
        const cashDetailsLabel = document.getElementById('modal_cash_details_label');
        const confirmBtn = document.getElementById('modalConfirmBtn');

        // Custom group only — never includes the ready-made amount
        if (dueTodayEl) {
            dueTodayEl.textContent = fmtPeso(data.customDueNow);
        }
        // Ready-made cash group only — never includes the custom amount
        if (dueLaterEl) {
            dueLaterEl.textContent = fmtPeso(data.regularDueLater);
        }
        if (customPlanLabel) {
            customPlanLabel.textContent = data.customPlan === 'downpayment'
                ? 'Custom Item Downpayment (50%): ' + fmtPeso(data.customDueNow)
                : 'Custom Item Full Payment (100%): ' + fmtPeso(data.customDueNow);
        }
        if (onlineMethodName) {
            onlineMethodName.textContent = data.custMethodName;
        }
        if (cashPlanLabel) {
            cashPlanLabel.textContent = 'Ready-Made Items (' + data.regMethodName + '): ' + fmtPeso(data.regularDueLater);
        }
        if (cashDetailsLabel) {
            cashDetailsLabel.textContent = data.regularDueLater > 0
                ? 'Pay in cash upon store collection or courier arrival — separate from the custom payment'
                : 'Ready-made items are paid online (' + data.regMethodName + ')';
        }

        // Ready-made online payment row (only when a digital method is selected)
        const regTodayRow = document.getElementById('modal_reg_due_today_row');
        if (regTodayRow) {
            if (data.isRegOnline && data.regularDueNow > 0) {
                regTodayRow.style.display = 'block';
                setText('modal_reg_due_today', fmtPeso(data.regularDueNow));
                setText('modal_reg_online_name', data.regMethodName);
            } else {
                regTodayRow.style.display = 'none';
            }
        }

        // Hide the ready-made cash row when there is no cash due
        const regLaterRow = document.getElementById('modal_reg_due_later_row');
        if (regLaterRow) {
            regLaterRow.style.display = data.regularDueLater > 0 ? 'flex' : 'none';
        }

        // Custom remaining balance row (cash on delivery, separate line)
        const custLaterRow = document.getElementById('modal_custom_due_later_row');
        if (custLaterRow) {
            if (data.customDueLater > 0) {
                custLaterRow.style.display = 'block';
                setText('modal_custom_due_later', fmtPeso(data.customDueLater));
            } else {
                custLaterRow.style.display = 'none';
            }
        }

        // Combined totals across both groups
        setText('modal_total_today', fmtPeso(data.totalDueToday));
        setText('modal_total_later', fmtPeso(data.totalDueLater));

        if (confirmBtn) {
            if (mode === 'preview') {
                confirmBtn.innerHTML = '<i class="fa-solid fa-arrow-down"></i> Got It, Continue to Upload';
                confirmBtn.onclick = function() {
                    closeSplitPaymentModal();
                    const proofBox = document.getElementById('proof-dropzone');
                    if (proofBox) {
                        proofBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                };
            } else {
                confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> I Understand, Place Order';
                confirmBtn.onclick = confirmAndPlaceSplitOrder;
            }
        }

        modal.style.display = 'flex';
    }

    function closeSplitPaymentModal() {
        const modal = document.getElementById('splitPaymentModal');
        if (modal) modal.style.display = 'none';
    }

    let splitPaymentConfirmed = false;
    function confirmAndPlaceSplitOrder() {
        splitPaymentConfirmed = true;
        closeSplitPaymentModal();
        const form = document.getElementById('checkoutForm');
        if (form) {
            form.submit();
        }
    }

    function updateMixedSummaryPlan(plan) {
        // The custom plan only affects the custom group's amounts — the
        // ready-made amount is always rendered on its own separate row.
        updateSplitPaymentAlertAmounts();
    }

    function selectPaymentPlan(plan) {
        const cardDownpayment = document.getElementById('plan_card_downpayment');
        const cardFull = document.getElementById('plan_card_full');
        const dueNow = document.getElementById('summary_due_now');
        const remaining = document.getElementById('summary_remaining_balance');

        if (plan === 'downpayment') {
            if (cardDownpayment) { cardDownpayment.style.borderColor = 'var(--accent-color)'; cardDownpayment.style.background = 'var(--accent-soft)'; }
            if (cardFull) { cardFull.style.borderColor = 'var(--border-color)'; cardFull.style.background = 'var(--bg-surface)'; }
            if (dueNow) dueNow.textContent = '₱{{ number_format($downpaymentAmount, 2) }}';
            if (remaining) remaining.textContent = '₱{{ number_format($remainingBalance, 2) }}';
        } else {
            if (cardFull) { cardFull.style.borderColor = 'var(--accent-color)'; cardFull.style.background = 'var(--accent-soft)'; }
            if (cardDownpayment) { cardDownpayment.style.borderColor = 'var(--border-color)'; cardDownpayment.style.background = 'var(--bg-surface)'; }
            if (dueNow) dueNow.textContent = '₱{{ number_format($subtotal, 2) }}';
            if (remaining) remaining.textContent = '₱0.00';
        }
    }

    function toggleFulfillmentType(type) {
        const pickupCard = document.getElementById('option_pickup_card');
        const deliveryCard = document.getElementById('option_delivery_card');
        const pickupNotice = document.getElementById('store_pickup_notice');
        const courierNotice = document.getElementById('courier_shipping_notice');

        if (type === 'pickup') {
            if (pickupCard) { pickupCard.style.border = '2px solid var(--success-color)'; pickupCard.style.background = 'var(--success-soft)'; }
            if (deliveryCard) { deliveryCard.style.border = '1.5px solid var(--border-color)'; deliveryCard.style.background = 'var(--bg-surface)'; }
            if (pickupNotice) pickupNotice.style.display = 'flex';
            if (courierNotice) courierNotice.style.display = 'none';
        } else {
            if (deliveryCard) { deliveryCard.style.border = '2px solid var(--accent-color)'; deliveryCard.style.background = 'var(--accent-soft)'; }
            if (pickupCard) { pickupCard.style.border = '1.5px solid var(--border-color)'; pickupCard.style.background = 'var(--bg-surface)'; }
            if (pickupNotice) pickupNotice.style.display = 'none';
            if (courierNotice) courierNotice.style.display = 'flex';
        }

        // Update walk-in field labels to reflect required vs optional based on fulfillment type
        const isDelivery = (type === 'delivery');
        const reqStyle = 'color: #DC2626; font-weight: 700;';
        const optStyle = 'color: var(--text-muted); font-weight: 600;';
        const reqText = (label) => `<span style="${reqStyle}">* Required for delivery</span>`;
        const optText = (label) => `<span style="${optStyle}">(optional for pickup)</span>`;

        const lastNameReq = document.getElementById('walkin_last_name_req');
        const phoneReq    = document.getElementById('walkin_phone_req');
        const emailReq    = document.getElementById('walkin_email_req');
        const addressReq  = document.getElementById('walkin_address_req');

        if (lastNameReq) lastNameReq.outerHTML = `<span id="walkin_last_name_req" style="${isDelivery ? reqStyle : optStyle}">${isDelivery ? '* Required for delivery' : '(optional for pickup)'}</span>`;
        if (phoneReq)    phoneReq.outerHTML    = `<span id="walkin_phone_req" style="${isDelivery ? reqStyle : optStyle}">${isDelivery ? '* Required for delivery' : '(optional for pickup)'}</span>`;
        if (emailReq)    emailReq.outerHTML    = `<span id="walkin_email_req" style="${isDelivery ? reqStyle : optStyle}">${isDelivery ? '* Required for delivery' : '(optional for pickup)'}</span>`;
        if (addressReq)  addressReq.outerHTML  = `<span id="walkin_address_req" style="${isDelivery ? reqStyle : optStyle}">${isDelivery ? '* Required for delivery' : '(required for delivery)'}</span>`;

        if (!isDelivery) {
            ['walkin_last_name', 'walkin_contact_number', 'walkin_email', 'walkin_address'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.classList.remove('field-error-border');
                    el.classList.remove('input-error-shake');
                    const parent = el.parentElement;
                    if (parent) {
                        const msg = parent.querySelector('.field-error-msg');
                        if (msg) msg.remove();
                    }
                }
            });
        }
    }

    let currentProofUrl = null;
    window.handleProofFileSelect = function (input) {
        const file = input && input.files && input.files[0];
        const emptyState = document.getElementById('proof-empty-state');
        const selectedState = document.getElementById('proof-file-selected');
        const filenameEl = document.getElementById('proof-filename');
        const filesizeEl = document.getElementById('proof-filesize');
        const thumbEl = document.getElementById('proof-thumbnail-img');

        if (file) {
            if (emptyState) emptyState.style.display = 'none';
            if (selectedState) selectedState.style.display = 'flex';
            if (filenameEl) filenameEl.textContent = file.name;
            if (filesizeEl) filesizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
            if (currentProofUrl) URL.revokeObjectURL(currentProofUrl);
            currentProofUrl = URL.createObjectURL(file);
            if (thumbEl) thumbEl.src = currentProofUrl;
        } else {
            window.clearProofFile();
        }
        validateCheckoutState(true);
    };

    // ── Split carts use a single shared screenshot upload (proof_of_payment)
    // that is attached to every online sub-order payment — no per-group box. ──

    window.clearProofFile = function () {
        const input = document.getElementById('proof_of_payment_input');
        if (input) input.value = '';
        if (currentProofUrl) { URL.revokeObjectURL(currentProofUrl); currentProofUrl = null; }
        const emptyState = document.getElementById('proof-empty-state');
        const selectedState = document.getElementById('proof-file-selected');
        if (emptyState) emptyState.style.display = 'block';
        if (selectedState) selectedState.style.display = 'none';
        validateCheckoutState(false);
    };

    // ══════════════════════════════════════════════════════════
    // ARTWORK UPLOADER: browse, drag & drop, preview grid, remove
    // (vanilla JS replacement for the Livewire removeArtwork() flow —
    // files are kept in a per-item store and re-synced into the hidden
    // file input via DataTransfer so the server sees item_files[key][])
    // ══════════════════════════════════════════════════════════
    const artworkFiles = {}; // item key -> File[]

    function artworkSyncInput(key) {
        const input = document.getElementById('item_files_input_' + key);
        if (!input) return;
        const dt = new DataTransfer();
        (artworkFiles[key] || []).forEach(f => dt.items.add(f));
        input.files = dt.files;
    }

    window.removeArtworkFile = function (key, index) {
        const store = artworkFiles[key];
        if (store && store[index] && store[index]._previewUrl) URL.revokeObjectURL(store[index]._previewUrl);
        if (store) store.splice(index, 1);
        artworkSyncInput(key);
        renderArtworkPreviews(key);
    };

    window.addArtworkFiles = function (key, fileList) {
        const store = artworkFiles[key] || (artworkFiles[key] = []);
        let oversizedName = null;
        Array.from(fileList).forEach(f => {
            if (f.size > 20 * 1024 * 1024) { oversizedName = f.name; return; }
            // skip duplicates (same name + size)
            if (!store.some(s => s.name === f.name && s.size === f.size)) store.push(f);
        });
        artworkSyncInput(key);
        renderArtworkPreviews(key, oversizedName);
    };

    function renderArtworkPreviews(key, oversizedName) {
        const grid = document.getElementById('preview_grid_' + key);
        if (!grid) return;
        const store = artworkFiles[key] || [];

        // release old object URLs before rebuilding the grid
        grid.querySelectorAll('img[data-object-url]').forEach(img => URL.revokeObjectURL(img.dataset.objectUrl));
        grid.innerHTML = '';
        grid.style.display = store.length || oversizedName ? 'grid' : 'none';

        if (oversizedName) {
            const err = document.createElement('p');
            err.style.cssText = 'grid-column: 1 / -1; font-size: 0.75rem; color: #DC2626; font-weight: 600; margin: 0;';
            err.textContent = '"' + oversizedName + '" was skipped — files must be 20MB or smaller.';
            grid.appendChild(err);
        }

        store.forEach((file, index) => {
            const card = document.createElement('div');
            card.className = 'relative group bg-[var(--bg-subtle)] border border-[var(--border-color)] rounded-xl p-2 flex items-center gap-2 overflow-hidden shadow-2xs';

            let thumb;
            if ((file.type || '').startsWith('image/')) {
                thumb = document.createElement('img');
                const url = URL.createObjectURL(file);
                thumb.src = url;
                thumb.dataset.objectUrl = url;
                thumb.className = 'w-10 h-10 object-cover rounded-lg shrink-0 border border-[var(--border-color)]';
            } else {
                thumb = document.createElement('div');
                thumb.className = 'w-10 h-10 bg-sky-100 text-sky-600 rounded-lg flex items-center justify-center font-bold text-xs shrink-0';
                thumb.innerHTML = '<i class="fa-solid fa-file-lines"></i>';
            }
            card.appendChild(thumb);

            const info = document.createElement('div');
            info.className = 'flex-1 min-w-0';
            const name = document.createElement('p');
            name.className = 'text-xs font-semibold text-[var(--text-main)] truncate';
            name.textContent = file.name;
            name.title = file.name;
            const size = document.createElement('p');
            size.className = 'text-[10px] text-[var(--text-subtle)]';
            size.textContent = (file.size / 1024).toFixed(1) + ' KB';
            info.appendChild(name);
            info.appendChild(size);
            card.appendChild(info);

            const del = document.createElement('button');
            del.type = 'button';
            del.title = 'Remove file';
            del.className = 'w-6 h-6 rounded-full bg-red-50 hover:bg-red-500 text-red-500 hover:text-white flex items-center justify-center text-xs font-bold transition-colors shrink-0';
            del.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            del.addEventListener('click', function (e) {
                e.stopPropagation();
                window.removeArtworkFile(key, index);
            });
            card.appendChild(del);

            grid.appendChild(card);
        });

        validateCheckoutState(false);
    }

    function initArtworkDropzones() {
        document.querySelectorAll('.artwork-dropzone[data-item-key]').forEach(zone => {
            const key = zone.dataset.itemKey;
            const input = zone.querySelector('.artwork-file-input');
            if (!input) return;

            // Click to browse (ignore clicks bubbling from the hidden input itself)
            zone.addEventListener('click', function (e) {
                if (e.target === input) return;
                input.click();
            });

            // Drag & drop feedback
            ['dragenter', 'dragover'].forEach(evt => zone.addEventListener(evt, e => {
                e.preventDefault();
                e.stopPropagation();
                zone.style.borderColor = '#0099FF';
                zone.style.background = 'var(--accent-soft)';
            }));
            ['dragleave', 'drop'].forEach(evt => zone.addEventListener(evt, e => {
                e.preventDefault();
                e.stopPropagation();
                const missing = !input.files || input.files.length === 0;
                zone.style.borderColor = (missing && zone.dataset.artworkMissing === '1' && zone.dataset.showerror === '1') ? '#DC2626' : '#94A3B8';
                zone.style.background = (missing && zone.dataset.artworkMissing === '1' && zone.dataset.showerror === '1') ? 'var(--danger-soft)' : 'var(--bg-surface)';
            }));
            zone.addEventListener('drop', e => {
                if (e.dataTransfer && e.dataTransfer.files.length) {
                    window.addArtworkFiles(key, e.dataTransfer.files);
                }
            });

            input.addEventListener('change', () => {
                if (input.files.length) window.addArtworkFiles(key, input.files);
            });
        });
    }

    function validateCheckoutState(trigger = false) {
        const submitBtn = document.getElementById('submitOrderBtn');
        if (!submitBtn) return true;

        const termsCb = document.getElementById('custom_terms_agreed_cb');
        const termsRequired = !!termsCb;
        const termsAgreed = !termsRequired || termsCb.checked;

        // Detect selected payment method type
        const isMixed = IS_MIXED_CART;

        // For mixed cart: check custom_payment_method
        // For single cart: check payment_method
        let selectedMethodEl = null;
        if (isMixed) {
            selectedMethodEl = document.querySelector('input[name="custom_payment_method"]:checked');
        } else {
            selectedMethodEl = document.querySelector('input[name="payment_method"]:checked');
        }

        const selectedMethodLabel = selectedMethodEl ? selectedMethodEl.closest('label') : null;
        const isCashSelected = selectedMethodLabel
            ? (selectedMethodLabel.dataset.isCash === '1' || selectedMethodLabel.dataset.isOnline === '0')
            : false;

        // Walk-in POS sidebar: reflect the payment type the staff picked.
        const walkinPayLabel = document.getElementById('walkin_pay_label');
        if (walkinPayLabel && !isMixed && selectedMethodEl) {
            walkinPayLabel.textContent = selectedMethodEl.value + (isCashSelected ? ' (Cash)' : ' (Online)');
            walkinPayLabel.style.color = isCashSelected ? '#92400E' : '#0284C7';
        }

        // A ready-made group on a digital method shares the single screenshot
        // upload below — its selection alone can make the proof mandatory.
        let isRegOnlineSelected = false;
        if (isMixed) {
            const regMethodEl = document.querySelector('input[name="regular_payment_method"]:checked');
            const regMethodLabel = regMethodEl ? regMethodEl.closest('label') : null;
            isRegOnlineSelected = !!(regMethodLabel && regMethodLabel.dataset.isOnline === '1');
        }

        // Update proof upload visibility
        if (isMixed) {
            const proofSection = document.getElementById('custom_proof_upload_section');
            const cashNotice = document.getElementById('custom_cash_payment_notice');
            if (proofSection && cashNotice) {
                proofSection.style.display = (isCashSelected && !isRegOnlineSelected) ? 'none' : 'block';
                cashNotice.style.display = 'none'; // retired green cash banner — card text is enough
            }
        } else {
            const proofBox = document.getElementById('proof_upload_box');
            const cashNotice = document.getElementById('cash_payment_notice');
            // Hide the screenshot box whenever Cash is the chosen method. Do not gate
            // this on #cash_payment_notice existing — the walk-in POS card has no such
            // element, which previously left the proof box stuck visible on Cash.
            if (proofBox) {
                proofBox.style.display = isCashSelected ? 'none' : 'block';
            }
            if (cashNotice) {
                cashNotice.style.display = 'none'; // retired green cash banner — card text is enough
            }
        }

        // Proof is required whenever any selected payment method is online/digital
        // (regular or custom orders) — cash never requires proof. Split carts use
        // one shared screenshot for all online sub-orders.
        const proofInput = document.getElementById('proof_of_payment_input');
        const proofRequired = !IS_ADMIN_OR_SUPER && (!isCashSelected || isRegOnlineSelected);
        const hasProof = proofInput && proofInput.files && proofInput.files.length > 0;

        const hintEl = document.getElementById('checkout_validation_hint');
        const hintTextEl = document.getElementById('checkout_validation_hint_text');

        let canSubmit = true;
        let missingReasons = [];

        if (proofRequired && !hasProof) {
            canSubmit = false;
            missingReasons.push('Payment receipt screenshot');
        }

        if (termsRequired && !termsAgreed) {
            canSubmit = false;
            missingReasons.push('Terms & Conditions agreement');
        }

        // ── Custom item artwork requirement (each rendered artwork dropzone
        // belongs to an item with requires_design_file and no attached file) ──
        let artworkMissing = false;
        document.querySelectorAll('.artwork-file-input').forEach(input => {
            const key = input.id.replace('item_files_input_', '');
            const zone = document.querySelector('.artwork-dropzone[data-item-key="' + key + '"]');
            const warn = document.querySelector('.artwork-missing-warning[data-warn-key="' + key + '"]');
            const missing = !input.files || input.files.length === 0;
            if (missing) artworkMissing = true;
            if (zone) {
                zone.dataset.artworkMissing = missing ? '1' : '0';
                zone.dataset.showerror = (missing && trigger) ? '1' : '0';
                if (missing && trigger) {
                    zone.style.border = '2px dashed #DC2626';
                    zone.style.background = 'var(--danger-soft)';
                } else {
                    zone.style.border = '2px dashed #94A3B8';
                    zone.style.background = 'var(--bg-surface)';
                }
            }
            if (warn) warn.style.display = (missing && trigger) ? 'flex' : 'none';
        });
        if (artworkMissing) {
            canSubmit = false;
            missingReasons.push('Custom item artwork');
        }

        // ── Red error indicator on the proof upload section ──
        const proofBoxOuter = IS_MIXED_CART
            ? document.getElementById('custom_proof_inner_box')
            : document.getElementById('proof_upload_box');
        const proofDropzone = document.getElementById('proof-dropzone');
        const proofWarning  = document.getElementById('proof_missing_warning');
        const proofBadge    = document.getElementById('proof_required_badge');
        const proofMissing  = proofRequired && !hasProof;

        if (proofMissing && trigger) {
            if (proofBoxOuter) {
                proofBoxOuter.style.border = '1.5px solid #F87171';
                proofBoxOuter.style.background = 'var(--danger-soft)';
                proofBoxOuter.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.10)';
            }
            if (proofDropzone) {
                proofDropzone.style.border = '2px dashed #DC2626';
                proofDropzone.style.background = 'var(--danger-soft)';
            }
            if (proofWarning) proofWarning.style.display = 'flex';
            if (proofBadge) proofBadge.style.display = 'inline-block';
        } else {
            if (proofBoxOuter) {
                proofBoxOuter.style.border = '1.5px solid #BAE6FD';
                proofBoxOuter.style.background = IS_MIXED_CART ? 'var(--bg-surface)' : 'var(--accent-soft)';
                proofBoxOuter.style.boxShadow = 'none';
            }
            if (proofDropzone) {
                proofDropzone.style.border = '2px dashed #93C5FD';
                proofDropzone.style.background = 'var(--accent-soft)';
            }
            if (proofWarning) proofWarning.style.display = 'none';
            if (proofBadge) proofBadge.style.display = proofRequired ? 'inline-block' : 'none';
        }

        if (!canSubmit) {
            // Keep the submit button clickable so the submit handler can guide
            // the user (red indicator + auto-scroll) to the missing requirement.
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            submitBtn.style.filter = 'none';
            submitBtn.style.pointerEvents = 'auto';
            submitBtn.title = 'Please complete: ' + missingReasons.join(' and ');

            if (hintEl && hintTextEl) {
                hintEl.style.display = 'block';
                const proofMissingNow = proofRequired && !hasProof;

                if (artworkMissing) {
                    hintTextEl.innerHTML = '<i class="fa-solid fa-paperclip" style="margin-right: 4px; color: var(--text-danger);"></i> <strong>Artwork file(s) are required</strong> for your custom item(s) — click the order button to be guided to the upload box.';
                } else if (proofMissingNow && !termsAgreed) {
                    hintTextEl.innerHTML = '<i class="fa-solid fa-lock" style="margin-right: 4px;"></i> Upload the <strong>payment screenshot</strong> and <strong>agree to terms</strong> to place order.';
                } else if (proofMissingNow) {
                    hintTextEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="margin-right: 4px; color: var(--text-danger);"></i> <strong>Proof of payment required</strong> — upload your payment screenshot.';
                } else if (!termsAgreed) {
                    hintTextEl.innerHTML = '<i class="fa-solid fa-square-check" style="margin-right: 4px;"></i> Check the <strong>Terms & Conditions</strong> box to place order.';
                }
            }
            return false;
        }

        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
        submitBtn.style.filter = 'none';
        submitBtn.style.pointerEvents = 'auto';
        submitBtn.title = '';

        if (hintEl) {
            hintEl.style.display = 'none';
        }
        return true;
    }

    function validateWalkinFields() {
        if (!IS_ADMIN_OR_SUPER) return true;

        let isValid = true;
        let firstInvalid = null;

        function markError(input, message) {
            if (!input) return;
            input.classList.add('field-error-border');
            
            const parent = input.parentElement;
            let existingMsg = parent ? parent.querySelector('.field-error-msg') : null;
            if (!existingMsg) {
                const msg = document.createElement('span');
                msg.className = 'field-error-msg';
                msg.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${message}`;
                if (parent) parent.appendChild(msg);
            } else {
                existingMsg.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${message}`;
                existingMsg.style.display = 'flex';
            }

            const onClear = function() {
                input.classList.remove('field-error-border');
                input.classList.remove('input-error-shake');
                if (parent) {
                    const m = parent.querySelector('.field-error-msg');
                    if (m) m.remove();
                }
                input.removeEventListener('input', onClear);
                input.removeEventListener('change', onClear);
            };
            input.addEventListener('input', onClear);
            input.addEventListener('change', onClear);

            if (!firstInvalid) {
                firstInvalid = input;
            }
            isValid = false;
        }

        const firstNameInput = document.getElementById('walkin_first_name') || document.querySelector('input[name="walkin_first_name"]');
        if (firstNameInput && !firstNameInput.value.trim()) {
            markError(firstNameInput, 'Customer first name is required.');
        }

        const fulfillmentInput = document.querySelector('input[name="fulfillment_type"]:checked');
        const isDelivery = fulfillmentInput ? (fulfillmentInput.value === 'delivery') : false;

        if (isDelivery) {
            const lastNameInput = document.getElementById('walkin_last_name') || document.querySelector('input[name="walkin_last_name"]');
            if (lastNameInput && !lastNameInput.value.trim()) {
                markError(lastNameInput, 'Customer last name is required for delivery.');
            }

            const addressInput = document.getElementById('walkin_address') || document.querySelector('input[name="walkin_address"]');
            if (addressInput && !addressInput.value.trim()) {
                markError(addressInput, 'Customer delivery address is required for delivery.');
            }
        }

        // Contact number: required for delivery, and whenever filled must be a
        // valid PH mobile — starts with 09, exactly 11 digits (same rule as
        // the portal/profile phone enforcement).
        const phoneInput = document.getElementById('walkin_contact_number') || document.querySelector('input[name="walkin_contact_number"]');
        if (phoneInput) {
            const phoneVal = phoneInput.value.trim();
            if (isDelivery && !phoneVal) {
                markError(phoneInput, 'Customer contact number is required for delivery.');
            } else if (phoneVal && !/^09\d{9}$/.test(phoneVal)) {
                markError(phoneInput, 'Please enter a valid 11-digit mobile number starting with 09.');
            }
        }

        // Email: required for delivery, and whenever filled must contain a
        // valid name@domain format.
        const emailInput = document.getElementById('walkin_email') || document.querySelector('input[name="walkin_email"]');
        if (emailInput) {
            const emailVal = emailInput.value.trim();
            if (isDelivery && !emailVal) {
                markError(emailInput, 'Customer email address is required for delivery.');
            } else if (emailVal && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
                markError(emailInput, 'Please enter a valid email address (name@example.com).');
            }
        }

        if (!isValid && firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.classList.remove('input-error-shake');
            void firstInvalid.offsetWidth; // Trigger reflow for animation restart
            firstInvalid.classList.add('input-error-shake');
            setTimeout(() => {
                firstInvalid.focus();
            }, 300);
        }

        return isValid;
    }

    document.addEventListener('DOMContentLoaded', () => {
        validateCheckoutState(false);
        updateSplitPaymentAlertAmounts();

        // Handle server-side error redirects: auto-scroll to first invalid field and shake
        const initialErrorField = document.querySelector('.field-error-border');
        if (initialErrorField) {
            setTimeout(() => {
                initialErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                initialErrorField.classList.remove('input-error-shake');
                void initialErrorField.offsetWidth;
                initialErrorField.classList.add('input-error-shake');
                if (typeof initialErrorField.focus === 'function') {
                    initialErrorField.focus();
                }
            }, 300);

            document.querySelectorAll('.field-error-border').forEach(field => {
                const clearServerErr = function() {
                    field.classList.remove('field-error-border');
                    field.classList.remove('input-error-shake');
                    const parent = field.parentElement;
                    if (parent) {
                        const m = parent.querySelector('.field-error-msg');
                        if (m) m.remove();
                    }
                    field.removeEventListener('input', clearServerErr);
                    field.removeEventListener('change', clearServerErr);
                };
                field.addEventListener('input', clearServerErr);
                field.addEventListener('change', clearServerErr);
            });
        }

        document.querySelectorAll('input[name="regular_payment_method"], input[name="custom_payment_method"]').forEach(el => {
            el.addEventListener('change', () => {
                updateSplitPaymentAlertAmounts();
                validateCheckoutState(false);
            });
        });

        // Single cart: re-validate when payment method changes
        document.querySelectorAll('input[name="payment_method"]').forEach(el => {
            el.addEventListener('change', () => validateCheckoutState(false));
        });

        // Wire up artwork dropzones (browse, drag & drop, previews, removal)
        initArtworkDropzones();

        const checkoutForm = document.getElementById('checkoutForm');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                // 1. Walk-in POS mode validation
                if (IS_ADMIN_OR_SUPER) {
                    if (!validateWalkinFields()) {
                        e.preventDefault();
                        return false;
                    }
                }

                // 2. Customer checkout state (proof of payment & custom terms)
                if (!splitPaymentConfirmed && !validateCheckoutState(true)) {
                    e.preventDefault();
                    const proofInputNow = document.getElementById('proof_of_payment_input');
                    const proofStillMissing = !proofInputNow || !proofInputNow.files || proofInputNow.files.length === 0;
                    const termsCb = document.getElementById('custom_terms_agreed_cb');
                    const termsMissing = termsCb && !termsCb.checked;

                    const missingArtworkZone = document.querySelector('.artwork-dropzone[data-artwork-missing="1"]');

                    let targetToScroll = null;

                    if (missingArtworkZone) {
                        targetToScroll = missingArtworkZone;
                        missingArtworkZone.classList.remove('input-error-shake');
                        void missingArtworkZone.offsetWidth;
                        missingArtworkZone.classList.add('input-error-shake');
                    } else if (proofStillMissing) {
                        targetToScroll = document.getElementById(IS_MIXED_CART ? 'custom_proof_upload_section' : 'proof_upload_box');
                        const dropzone = document.getElementById('proof-dropzone');
                        if (dropzone) {
                            dropzone.classList.remove('input-error-shake');
                            void dropzone.offsetWidth;
                            dropzone.classList.add('input-error-shake');
                        }
                    } else if (termsMissing) {
                        targetToScroll = document.getElementById('custom_terms_box') || (termsCb ? termsCb.closest('div') : null);
                        if (targetToScroll) {
                            targetToScroll.classList.add('field-error-border');
                            targetToScroll.classList.remove('input-error-shake');
                            void targetToScroll.offsetWidth;
                            targetToScroll.classList.add('input-error-shake');
                            termsCb.addEventListener('change', function onTermsChange() {
                                targetToScroll.classList.remove('field-error-border');
                                targetToScroll.classList.remove('input-error-shake');
                                termsCb.removeEventListener('change', onTermsChange);
                            });
                        }
                    }

                    if (targetToScroll) {
                        targetToScroll.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return false;
                }

                // 3. Mixed cart split confirmation modal
                if (IS_MIXED_CART && !splitPaymentConfirmed) {
                    e.preventDefault();
                    openSplitPaymentModal('submit');
                    return false;
                }
            });
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSplitPaymentModal();
    });
</script>
@endsection
