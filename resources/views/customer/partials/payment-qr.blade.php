{{-- Scannable payment QR code (uploaded by Super Admin in Payment Methods) with an
     Alpine fullscreen lightbox. The .pm-qr-panel wrapper is hidden by default and only
     displayed while its parent .payment-method-item radio is checked (CSS :has() rule
     defined in the checkout page @push('styles') block). --}}
@if($pm->is_online_payment && $pm->qr_code_path)
    <div class="pm-qr-panel mt-3 p-3 bg-[var(--accent-soft)] border border-[var(--border-color)] rounded-xl flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4"
         x-data="{ openQrModal: false }">

        {{-- Scan hint --}}
        <p class="text-[11px] sm:text-xs text-[var(--text-muted)] flex items-start gap-2 min-w-0 m-0">
            <i class="fa-solid fa-mobile-screen-button text-sky-500 mt-0.5"></i>
            <span>Scan the QR code or send payment directly to the account above before uploading your receipt.</span>
        </p>

        {{-- QR Code Thumbnail (click to enlarge) --}}
        <div class="shrink-0 flex flex-row sm:flex-col items-center gap-2 sm:gap-0 sm:mx-auto">
            <div @click="openQrModal = true" title="Click to enlarge"
                 class="group relative cursor-pointer p-1.5 bg-white border border-gray-200 hover:border-sky-400 rounded-xl transition-all shadow-2xs">
                <img src="{{ $pm->qr_code_url }}" alt="{{ $pm->name }} QR Code"
                     class="w-20 h-20 sm:w-24 sm:h-24 object-contain rounded-lg">
                <div class="absolute inset-0 bg-black/40 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white text-[10px] font-bold pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass-plus"></i> Click to Enlarge
                </div>
            </div>
            <span class="text-[10px] text-[var(--text-subtle)] sm:mt-1 font-medium">Scan to Pay</span>
        </div>

        {{-- Fullscreen QR Lightbox Modal --}}
        <div x-show="openQrModal" x-cloak x-transition.opacity
             @keydown.escape.window="openQrModal = false"
             x-effect="document.body.style.overflow = openQrModal ? 'hidden' : ''"
             class="fixed inset-0 z-[10005] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl p-5 max-w-xs w-full text-center relative shadow-2xl" @click.away="openQrModal = false">
                <button type="button"
                        @click="openQrModal = false"
                        aria-label="Close"
                        class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-800 flex items-center justify-center transition-colors border-0 outline-none focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <h4 class="text-sm font-bold text-gray-900 mb-1">Scan QR Code</h4>
                <p class="text-xs text-gray-500 mb-3">{{ $pm->name }}{{ $pm->account_name ? ' — ' . $pm->account_name : '' }}</p>
                <img src="{{ $pm->qr_code_url }}" alt="Payment QR Code" class="w-full h-auto rounded-xl border border-gray-100">
                <p class="text-[11px] text-gray-400 mt-3 mb-0">Point your {{ $pm->name }} app at the screen to pay.</p>
            </div>
        </div>
    </div>
@endif
