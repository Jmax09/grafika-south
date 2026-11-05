@extends('layouts.admin')

@section('title', 'Payment Methods Management - Super Admin')

{{-- Validation errors are shown inside the Add/Edit modals, so opt out of the layout's
     generic $errors toast; session success/warning/error still surface as toasts. --}}
@section('custom_errors', '')

@section('content')

<style>
    /* ── Payment Methods modal polish ── */
    .pm-modal-card {
        padding: 0;
        margin: 0;
        border: none;
        max-height: 88vh;
    }

    .pm-head {
        padding: 1.05rem 1.5rem;
        background: var(--cmyk-key);
        color: var(--text-inverse);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-shrink: 0;
    }
    .pm-head h3 { font-size: 1.1rem; font-weight: 800; color: var(--text-inverse); margin: 0; display: flex; align-items: center; gap: 0.5rem; line-height: 1.3; }
    .pm-head .pm-sub { font-size: 0.73rem; color: var(--text-subtle); margin-top: 2px; }
    .pm-close {
        background: none; border: none; color: var(--text-subtle); font-size: 1.35rem;
        cursor: pointer; line-height: 1; padding: 0.3rem 0.45rem; border-radius: 8px;
        transition: color 0.15s ease, background 0.15s ease; flex-shrink: 0;
    }
    .pm-close:hover { color: var(--text-inverse); background: rgba(255, 255, 255, 0.12); }

    .pm-form { flex: 1; min-height: 0; display: flex; flex-direction: column; overflow: hidden; }

    .pm-body { flex: 1; min-height: 0; overflow-y: auto; overflow-x: hidden; padding: 1.35rem 1.5rem; }
    .pm-body::-webkit-scrollbar { width: 9px; }
    .pm-body::-webkit-scrollbar-track { background: var(--bg-subtle); }
    .pm-body::-webkit-scrollbar-thumb { background: var(--text-disabled); border-radius: 9999px; }
    .pm-body::-webkit-scrollbar-thumb:hover { background: var(--text-subtle); }

    .pm-footer {
        flex-shrink: 0;
        padding: 0.95rem 1.5rem;
        border-top: 1px solid var(--border-color);
        background: var(--bg-surface);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .pm-footer .pm-hint { font-size: 0.76rem; color: var(--text-muted); }
    .pm-footer .pm-hint .req { color: var(--text-danger); font-weight: 800; }

    .pm-step { display: flex; align-items: center; gap: 0.6rem; margin: 1.5rem 0 0.9rem; }
    .pm-step.first { margin-top: 0; }
    .pm-step .num {
        width: 20px; height: 20px; border-radius: 6px; background: var(--cmyk-key); color: var(--text-inverse);
        font-size: 0.7rem; font-weight: 800; display: inline-flex; align-items: center;
        justify-content: center; flex-shrink: 0;
    }
    .pm-step .txt { font-size: 0.73rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); white-space: nowrap; }
    .pm-step .line { flex: 1; height: 1px; background: var(--border-color); }

    .pm-field {
        width: 100%; padding: 0.7rem 0.9rem; border: 1px solid var(--border-color);
        border-radius: var(--radius-md); font-size: 0.9rem; font-family: 'Inter', sans-serif;
        color: var(--text-main); background: var(--bg-surface); transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .pm-field::placeholder { color: var(--text-subtle); }
    .pm-field:focus { outline: none; border-color: var(--cmyk-cyan); box-shadow: 0 0 0 3px rgba(0, 153, 255, 0.14); }

    .pm-label { font-size: 0.83rem; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 0.35rem; }
    .pm-label .req { color: var(--text-danger); }
    .pm-label .opt { color: var(--text-muted); font-weight: 600; font-size: 0.74rem; }

    .pm-upload {
        display: flex; flex-direction: column; align-items: center; gap: 0.3rem;
        padding: 1.05rem 1rem; border: 1.5px dashed var(--cmyk-cyan); border-radius: 10px;
        background: var(--accent-soft); cursor: pointer; text-align: center;
        transition: background 0.15s ease, border-color 0.15s ease;
    }
    .pm-upload:hover { background: var(--accent-soft); border-color: var(--cmyk-cyan-hover); }
    .pm-upload .up-icon { font-size: 1.35rem; color: var(--brand-cyan-fg); }
    .pm-upload .up-head { font-size: 0.85rem; font-weight: 700; color: var(--text-main); }
    .pm-upload .up-sub { font-size: 0.74rem; color: var(--text-muted); }
    .pm-upload .up-file { font-size: 0.76rem; font-weight: 700; color: var(--brand-cyan-fg); word-break: break-all; }

    .pm-error-box {
        background: var(--danger-soft); border: 1px solid var(--danger-border); color: var(--text-danger); border-radius: 10px;
        padding: 0.75rem 0.9rem; font-size: 0.83rem; font-weight: 600; margin-bottom: 1.1rem;
        display: flex; gap: 0.55rem; align-items: flex-start;
    }
    .pm-error-box i { margin-top: 2px; }

    /* ── Status toggle button (table) ── */
    .pm-status-btn {
        display: inline-flex; align-items: center; gap: 0.35rem;
        font-family: 'Inter', sans-serif; font-size: 0.78rem; font-weight: 800;
        letter-spacing: 0.03em; line-height: 1.2;
        padding: 0.42rem 0.9rem; border-radius: 9999px; border: 1.5px solid transparent;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, transform 0.1s ease;
    }
    .pm-status-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 12px -6px rgba(15, 23, 42, 0.45); }
    .pm-status-btn:active { transform: translateY(0); box-shadow: none; }
    .pm-status-btn:focus-visible { outline: 2px solid var(--cmyk-cyan); outline-offset: 2px; }
    .pm-status-btn.pm-on { background: var(--success-soft); border-color: var(--success-color); color: var(--text-success); }
    .pm-status-btn.pm-on:hover { background: var(--success-soft); border-color: var(--success-color); }
    .pm-status-btn.pm-off { background: var(--bg-surface); border-color: var(--danger-color); color: var(--text-danger); }
    .pm-status-btn.pm-off:hover { background: var(--danger-soft); border-color: var(--danger-color); }
    .pm-status-btn.is-loading { opacity: 0.75; cursor: wait; pointer-events: none; transform: none; box-shadow: none; }

    /* ── Toast feedback ── */
    .pm-toast {
        position: fixed; right: 1.25rem; bottom: 1.25rem; z-index: 2000;
        display: flex; align-items: center; gap: 0.55rem;
        padding: 0.8rem 1.1rem; border-radius: 10px;
        font-size: 0.86rem; font-weight: 700; line-height: 1.4;
        box-shadow: 0 12px 24px -10px rgba(15, 23, 42, 0.45);
        max-width: min(440px, calc(100vw - 2.5rem));
        opacity: 0; transform: translateY(10px);
        transition: opacity 0.25s ease, transform 0.25s ease;
    }
    .pm-toast.show { opacity: 1; transform: translateY(0); }
    .pm-toast.pm-toast-ok { background: var(--success-soft); border: 1px solid var(--success-color); color: var(--text-success); }
    .pm-toast.pm-toast-err { background: var(--danger-soft); border: 1px solid var(--danger-color); color: var(--text-danger); }

    @media (max-width: 640px) {
        .pm-modal-card { max-height: 94vh; }
        .pm-body { padding: 1.1rem; }
        .pm-grid-2 { grid-template-columns: 1fr !important; }
        .pm-footer { justify-content: flex-end; }
        .pm-footer .pm-hint { display: none; }
    }
</style>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.6rem;">
            <i class="fa-solid fa-credit-card" style="color: var(--brand-cyan-fg);"></i> Payment
        </h1>
    </div>

    <button type="button" class="btn btn-primary" onclick="openNewMethodModal()" style="padding: 0.75rem 1.25rem; font-size: 0.95rem;">
        <i class="fa-solid fa-plus"></i> Add New Payment Method
    </button>
</div>

<!-- PAYMENT METHODS LIST TABLE -->
<div class="card" style="padding: 1.5rem; overflow: hidden;">
    <div style="overflow-x: auto; width: 100%;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; min-width: 850px;">
            <thead>
                <tr style="background: var(--bg-page); text-align: left; color: var(--text-muted); border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 0.9rem 1.25rem;">Payment Method</th>
                    <th style="padding: 0.9rem 1.25rem;">Type</th>
                    <th style="padding: 0.9rem 1.25rem;">Account Name & Number</th>
                    <th style="padding: 0.9rem 1.25rem;">QR Code Image</th>
                    <th style="padding: 0.9rem 1.25rem;">Status</th>
                    <th style="padding: 0.9rem 1.25rem; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentMethods as $pm)
                    <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease; cursor: pointer;" 
                        onmouseover="this.style.background='var(--bg-subtle)'" 
                        onmouseout="this.style.background='transparent'"
                        onclick="if(!event.target.closest('button') && !event.target.closest('a') && !event.target.closest('form')) openEditMethodModal({{ $pm->id }})">
                        <!-- NAME & INSTRUCTIONS -->
                        <td style="padding: 1rem 1.25rem;">
                            <strong style="font-size: 1rem; color: var(--text-main); display: block; transition: color 0.15s ease;" 
                                    onmouseover="this.style.color='var(--cmyk-cyan)'" 
                                    onmouseout="this.style.color='var(--text-main)'">
                                {{ $pm->name }}
                            </strong>
                            @if($pm->instructions)
                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 3px; max-width: 260px; line-height: 1.4;">
                                    {{ Str::limit($pm->instructions, 90) }}
                                </div>
                            @endif
                        </td>

                        <!-- TYPE -->
                        <td style="padding: 1rem 1.25rem; white-space: nowrap;">
                            <span class="role-badge" style="background: {{ $pm->type === 'gateway' ? 'var(--brand-violet-soft)' : 'var(--bg-subtle)' }}; color: {{ $pm->type === 'gateway' ? 'var(--text-violet)' : 'var(--text-muted)' }}; font-weight: 700;">
                                <i class="fa-solid {{ $pm->type === 'gateway' ? 'fa-bolt' : 'fa-hand-holding-dollar' }}"></i> {{ strtoupper($pm->type) }}
                            </span>
                        </td>

                        <!-- ACCOUNT DETAILS -->
                        <td style="padding: 1rem 1.25rem;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.88rem;">
                                {{ $pm->account_name ?: 'N/A' }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                {{ $pm->account_number ?: 'No Account #' }}
                            </div>
                        </td>

                        <!-- QR CODE PREVIEW -->
                        <td style="padding: 1rem 1.25rem;">
                            @if($pm->qr_code_path)
                                <a href="{{ $pm->qr_code_url }}" target="_blank" title="Click to view full size QR Code">
                                    <img src="{{ $pm->qr_code_url }}" alt="QR Code" 
                                         style="width: 48px; height: 48px; object-fit: contain; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-surface); padding: 2px; transition: transform 0.2s ease, box-shadow 0.2s ease;"
                                         onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';" 
                                         onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                                </a>
                            @else
                                <span style="font-size: 0.78rem; color: var(--text-muted); font-style: italic;">No QR Code</span>
                            @endif
                        </td>

                        <!-- ACTIVE TOGGLE STATUS -->
                        <td style="padding: 1rem 1.25rem; white-space: nowrap;">
                            <form action="{{ route('super-admin.payment-methods.toggle', $pm->id) }}" method="POST" style="display: inline;" onsubmit="return handleStatusToggle(event, this);">
                                @csrf
                                <button type="submit" class="pm-status-btn js-status-btn {{ $pm->is_active ? 'pm-on' : 'pm-off' }}" title="Click to switch to {{ $pm->is_active ? 'Inactive (hidden at checkout)' : 'Active (shown at checkout)' }}">
                                    @if($pm->is_active)
                                        <i class="fa-solid fa-circle-check"></i> ACTIVE
                                    @else
                                        <i class="fa-solid fa-circle-xmark"></i> DISABLED
                                    @endif
                                </button>
                            </form>
                        </td>

                        <!-- ACTIONS -->
                        <td style="padding: 1rem 1.25rem; text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                                <button type="button" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.4rem 0.75rem;" onclick="openEditMethodModal({{ $pm->id }})">
                                    <i class="fa-solid fa-pen-to-square" style="color: var(--brand-cyan-fg);"></i> Edit
                                </button>

                                <form action="{{ route('super-admin.payment-methods.destroy', $pm->id) }}" method="POST" style="display: inline;" class="pm-delete-form" data-name="{{ $pm->name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: var(--text-danger); cursor: pointer; font-size: 1rem; padding: 0.2rem 0.4rem;" title="Delete Payment Method">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- EDIT METHOD MODAL -->
                            <div id="editMethodModal-{{ $pm->id }}" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 1000; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(4px); text-align: left;">
                                @php
                                    $editAttempt = $errors->any() && old('_form') === 'edit' && (string) old('_form_id') === (string) $pm->id;
                                    $editName = $editAttempt ? old('name', $pm->name) : $pm->name;
                                    $editAcctName = $editAttempt ? old('account_name', $pm->account_name) : $pm->account_name;
                                    $editAcctNum = $editAttempt ? old('account_number', $pm->account_number) : $pm->account_number;
                                    $editInstructions = $editAttempt ? old('instructions', $pm->instructions) : $pm->instructions;
                                    $editIsOnline = $editAttempt ? old('is_online_payment', $pm->is_online_payment ? '1' : '0') == '1' : (bool) $pm->is_online_payment;
                                @endphp
                                <div class="card pm-modal-card" style="width: 100%; max-width: 640px; background: var(--bg-surface); border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); overflow: hidden; display: flex; flex-direction: column;">
                                    <div class="pm-head">
                                        <div style="min-width: 0;">
                                            <h3><i class="fa-solid fa-pen-to-square" style="color: var(--brand-cyan-fg);"></i> Edit Payment Method</h3>
                                            <div class="pm-sub">{{ $pm->name }}</div>
                                        </div>
                                        <button type="button" class="pm-close" onclick="closeEditMethodModal({{ $pm->id }})" title="Close"><i class="fa-solid fa-xmark"></i></button>
                                    </div>

                                    <form action="{{ route('super-admin.payment-methods.update', $pm->id) }}" method="POST" enctype="multipart/form-data" class="pm-form">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="_form" value="edit">
                                        <input type="hidden" name="_form_id" value="{{ $pm->id }}">

                                        <div class="pm-body">
                                            @if($editAttempt)
                                                <div class="pm-error-box">
                                                    <i class="fa-solid fa-circle-exclamation"></i>
                                                    <div>
                                                        @foreach($errors->all() as $err)
                                                            <div>{{ $err }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <div style="margin-bottom: 0.9rem;">
                                                <label class="pm-label" for="edit_pm_name_{{ $pm->id }}">Payment Method Name <span class="req">*</span></label>
                                                <input type="text" id="edit_pm_name_{{ $pm->id }}" name="name" class="pm-field" value="{{ $editName }}" placeholder="e.g. InstaPay, GCash, Bank Transfer" required>
                                            </div>

                                            {{-- Type is always 'manual'; hidden to mirror the Add New modal --}}
                                            <input type="hidden" name="type" value="manual">

                                            <div class="pm-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.9rem; margin-bottom: 0.9rem;">
                                                <div>
                                                    <label class="pm-label" for="edit_pm_acct_name_{{ $pm->id }}">Account Name <span class="req">*</span></label>
                                                    <input type="text" id="edit_pm_acct_name_{{ $pm->id }}" name="account_name" class="pm-field" value="{{ $editAcctName }}" placeholder="e.g. Grafika South Shop" required>
                                                </div>
                                                <div>
                                                    <label class="pm-label" for="edit_pm_acct_num_{{ $pm->id }}">Account Number <span class="req">*</span></label>
                                                    <input type="text" id="edit_pm_acct_num_{{ $pm->id }}" name="account_number" class="pm-field" value="{{ $editAcctNum }}" placeholder="e.g. 0962-292-7736" required>
                                                </div>
                                            </div>

                                            {{-- All payment methods are online/digital; channel is fixed --}}
                                            <input type="hidden" name="is_online_payment" value="1">

                                            <div style="margin-bottom: 1rem;">
                                                <label class="pm-label" for="edit_pm_instructions_{{ $pm->id }}">Checkout Customer Instructions</label>
                                                <textarea id="edit_pm_instructions_{{ $pm->id }}" name="instructions" rows="3" class="pm-field" style="resize: vertical; min-height: 82px;" placeholder="Instructions shown to customer at checkout...">{{ $editInstructions }}</textarea>
                                                <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 0.3rem;">Shown to the customer during the payment step after checkout.</div>
                                            </div>

                                            <div>
                                                <label class="pm-label">QR Code Image <span class="req">*</span></label>
                                                <label class="pm-upload" for="edit_pm_qr_{{ $pm->id }}">
                                                    <i class="fa-solid fa-cloud-arrow-up up-icon"></i>
                                                    <span class="up-head">Click to replace QR Code image</span>
                                                    <span class="up-sub">PNG or JPG, up to 5 MB &mdash; for GCash / Maya / bank transfers</span>
                                                    <span class="up-file" id="edit_pm_qr_name_{{ $pm->id }}">No file chosen</span>
                                                    <input type="file" id="edit_pm_qr_{{ $pm->id }}" name="qr_code" accept="image/*" style="display: none;" onchange="showFileName(this, 'edit_pm_qr_name_{{ $pm->id }}')">
                                                </label>
                                                @if($pm->qr_code_path)
                                                    <div style="margin-top: 0.6rem; display: flex; align-items: center; gap: 0.65rem; background: var(--bg-page); border: 1px solid var(--border-color); border-radius: 10px; padding: 0.55rem 0.75rem;">
                                                        <img src="{{ $pm->qr_code_url }}" alt="Current QR Code" style="width: 46px; height: 46px; object-fit: contain; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px; padding: 2px; flex-shrink: 0;">
                                                        <div style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.45;">
                                                            Current QR Code on file.<br>
                                                            <a href="{{ $pm->qr_code_url }}" target="_blank" style="color: var(--brand-cyan-fg); font-weight: 700;">View full size</a>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                        </div>

                                        <div class="pm-footer">
                                            <span class="pm-hint"><span class="req">*</span> Required fields</span>
                                            <div style="display: flex; gap: 0.75rem;">
                                                <button type="button" onclick="closeEditMethodModal({{ $pm->id }})" class="btn btn-outline">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">No payment methods found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- NEW PAYMENT METHOD MODAL -->
<div id="newMethodModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 1000; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(4px);">
    @php
        $newAttempt = $errors->any() && old('_form', 'new') === 'new';
        $newIsOnline = $newAttempt ? old('is_online_payment', '1') == '1' : true;
    @endphp
    <div class="card pm-modal-card" style="width: 100%; max-width: 640px; background: var(--bg-surface); border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); overflow: hidden; display: flex; flex-direction: column;">
        <div class="pm-head">
            <div style="min-width: 0;">
                <h3><i class="fa-solid fa-plus-circle" style="color: var(--brand-cyan-fg);"></i> Add New Payment Method</h3>
                <div class="pm-sub">Set up a payment option customers can use at checkout</div>
            </div>
            <button type="button" class="pm-close" onclick="closeNewMethodModal()" title="Close"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form action="{{ route('super-admin.payment-methods.store') }}" method="POST" enctype="multipart/form-data" class="pm-form">
            @csrf
            <input type="hidden" name="_form" value="new">

            <div class="pm-body">
                @if($newAttempt)
                    <div class="pm-error-box">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <div>
                            @foreach($errors->all() as $err)
                                <div>{{ $err }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div style="margin-bottom: 1.1rem;">
                    <label class="pm-label" for="new_pm_name">Payment Method Name <span class="req">*</span></label>
                    <input type="text" id="new_pm_name" name="name" class="pm-field" value="{{ $newAttempt ? old('name') : '' }}" placeholder="e.g. InstaPay, GCash, Bank Transfer" required>
                </div>

                {{-- Type is always 'manual'; hidden to satisfy backend validation --}}
                <input type="hidden" name="type" value="manual">

                <div class="pm-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.9rem; margin-bottom: 0.35rem;">
                    <div>
                        <label class="pm-label" for="new_pm_acct_name">Account Name <span class="req">*</span></label>
                        <input type="text" id="new_pm_acct_name" name="account_name" class="pm-field" value="{{ $newAttempt ? old('account_name') : '' }}" placeholder="e.g. Grafika South Print Shop" required>
                    </div>
                    <div>
                        <label class="pm-label" for="new_pm_acct_num">Account Number <span class="req">*</span></label>
                        <input type="text" id="new_pm_acct_num" name="account_number" class="pm-field" value="{{ $newAttempt ? old('account_number') : '' }}" placeholder="e.g. 0962-292-7736" required>
                    </div>
                </div>

                {{-- All new payment methods are online/digital by default --}}
                <input type="hidden" name="is_online_payment" value="1">

                <div style="margin-bottom: 1rem; margin-top: 0.9rem;">
                    <label class="pm-label" for="new_pm_instructions">Checkout Customer Instructions</label>
                    <textarea id="new_pm_instructions" name="instructions" rows="3" class="pm-field" style="resize: vertical; min-height: 82px;" placeholder="Instructions shown to customer at checkout...">{{ $newAttempt ? old('instructions') : '' }}</textarea>
                    <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 0.3rem;">Shown to the customer during the payment step after checkout.</div>
                </div>

                <div>
                    <label class="pm-label">Upload QR Code Image <span class="req">*</span></label>
                    <label class="pm-upload" for="new_pm_qr">
                        <i class="fa-solid fa-cloud-arrow-up up-icon"></i>
                        <span class="up-head">Click to upload QR Code image</span>
                        <span class="up-sub">PNG or JPG, up to 5 MB &mdash; for GCash / Maya / bank transfers</span>
                        <span class="up-file" id="new_pm_qr_name">No file chosen</span>
                        <input type="file" id="new_pm_qr" name="qr_code" accept="image/*" style="display: none;" onchange="showFileName(this, 'new_pm_qr_name')" required>
                    </label>
                </div>

            </div>

            <div class="pm-footer">
                <span class="pm-hint"><span class="req">*</span> Required fields</span>
                <div style="display: flex; gap: 0.75rem;">
                    <button type="button" onclick="closeNewMethodModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Payment Method</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Custom Delete Confirmation Modal (replaces the native browser confirm) --}}
<div id="pm-delete-modal" onclick="if(event.target===this) closePmDeleteModal()"
    style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:1500; align-items:center; justify-content:center; padding:1rem;">
    <div style="background:var(--bg-surface); border-radius:16px; width:100%; max-width:420px; overflow:hidden; box-shadow:0 20px 50px rgba(15,23,42,0.3); animation:pmDelPop 0.18s ease;">
        <div style="padding:1.75rem 1.75rem 1.25rem; text-align:center;">
            <div style="width:56px; height:56px; border-radius:50%; background:var(--danger-soft); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                <i class="fa-solid fa-trash-can" style="font-size:1.4rem; color:var(--text-danger);"></i>
            </div>
            <h3 style="font-size:1.15rem; font-weight:800; color:var(--text-main); margin:0 0 0.4rem;">Delete this payment method?</h3>
            <p style="color:var(--text-muted); font-size:0.86rem; margin:0; line-height:1.5;">
                You are about to permanently delete <strong style="color:var(--text-main);" id="pm-delete-name">&mdash;</strong>. This action <strong style="color:var(--text-danger);">cannot be undone</strong>.
            </p>
        </div>
        <div style="padding:1rem 1.75rem 1.5rem; display:flex; gap:0.75rem; border-top:1px solid var(--border-color); background:var(--bg-page);">
            <button type="button" onclick="closePmDeleteModal()" style="flex:1; background:var(--bg-surface); border:1px solid var(--border-color); color:var(--text-muted); padding:0.6rem 1rem; border-radius:9px; font-size:0.88rem; font-weight:700; cursor:pointer; transition:background 0.15s;" onmouseover="this.style.background='var(--bg-subtle)'" onmouseout="this.style.background='var(--bg-surface)'">Cancel</button>
            <button type="button" onclick="confirmPmDelete()" style="flex:1; background:var(--danger-color); border:1px solid var(--danger-color); color:var(--text-inverse); padding:0.6rem 1rem; border-radius:9px; font-size:0.88rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; transition:background 0.15s;" onmouseover="this.style.background='#B91C1C'" onmouseout="this.style.background='#DC2626'"><i class="fa-solid fa-trash-can"></i> Yes, Delete</button>
        </div>
    </div>
</div>
<style>@@keyframes pmDelPop { from { transform: scale(0.95); opacity:0; } to { transform: scale(1); opacity:1; } }</style>

<script>
    const PM_MODAL_SELECTOR = '#newMethodModal, [id^="editMethodModal-"]';

    function pmScrollBodyToTop(modal) {
        const body = modal.querySelector('.pm-body');
        if (body) body.scrollTop = 0;
    }

    function restoreBodyScroll() {
        const anyOpen = Array.from(document.querySelectorAll(PM_MODAL_SELECTOR))
            .some(m => m.style.display === 'flex');
        if (!anyOpen) document.body.style.overflow = '';
    }

    function openNewMethodModal() {
        const modal = document.getElementById('newMethodModal');
        modal.style.display = 'flex';
        pmScrollBodyToTop(modal);
        document.body.style.overflow = 'hidden';
    }

    function closeNewMethodModal() {
        document.getElementById('newMethodModal').style.display = 'none';
        restoreBodyScroll();
    }

    function openEditMethodModal(id) {
        const modal = document.getElementById('editMethodModal-' + id);
        modal.style.display = 'flex';
        pmScrollBodyToTop(modal);
        document.body.style.overflow = 'hidden';
    }

    function closeEditMethodModal(id) {
        document.getElementById('editMethodModal-' + id).style.display = 'none';
        restoreBodyScroll();
    }

    function pmToast(message, type) {
        const existing = document.querySelector('.pm-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'pm-toast ' + (type === 'error' ? 'pm-toast-err' : 'pm-toast-ok');
        toast.innerHTML = '<i class="fa-solid ' + (type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check') + '"></i><span></span>';
        toast.querySelector('span').textContent = message;
        document.body.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('show'));
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 2800);
    }

    function handleStatusToggle(event, form) {
        event.preventDefault();

        const btn = form.querySelector('.js-status-btn');
        if (!btn || btn.classList.contains('is-loading')) return false;

        // 1. Loading animation while the request is in flight (rekta toggle, walang confirm)
        const row = form.closest('tr');

        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('is-loading');
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> SAVING...';

        const token = form.querySelector('input[name="_token"]');

        // 2. Submit via fetch so the page does NOT reload (no lag)
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token ? token.value : '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(new FormData(form))
        })
        .then(async (res) => {
            const data = await res.json().catch(() => null);
            if (!res.ok || !data || !data.success) {
                throw new Error((data && data.message) || 'Request failed (' + res.status + ')');
            }
            return data;
        })
        .then((data) => {
            // Update the status button in place
            btn.classList.remove('pm-on', 'pm-off');
            btn.classList.add(data.is_active ? 'pm-on' : 'pm-off');
            btn.disabled = false;
            btn.classList.remove('is-loading');
            btn.title = data.is_active
                ? 'Click to switch to Inactive (hidden at checkout)'
                : 'Click to switch to Active (shown at checkout)';
            btn.innerHTML = data.is_active
                ? '<i class="fa-solid fa-circle-check"></i> ACTIVE'
                : '<i class="fa-solid fa-circle-xmark"></i> DISABLED';

            // Update the Channel badge "(Inactive)" flag in the same row
            if (row) {
                let flag = row.querySelector('.js-inactive-flag');
                if (!data.is_active && !flag) {
                    const badge = row.querySelector('td:nth-child(3) .role-badge');
                    if (badge) {
                        flag = document.createElement('span');
                        flag.className = 'js-inactive-flag';
                        flag.textContent = '(Inactive)';
                        badge.appendChild(document.createTextNode(' '));
                        badge.appendChild(flag);
                    }
                } else if (data.is_active && flag) {
                    flag.remove();
                }
            }

            pmToast(data.message || 'Status updated.', 'success');
        })
        .catch((err) => {
            btn.disabled = false;
            btn.classList.remove('is-loading');
            btn.innerHTML = originalHtml;
            pmToast(err.message || 'Failed to update status.', 'error');
        });

        return false;
    }


    // Render 422 validation errors inside the modal's .pm-body (no page reload).
    function renderPmErrors(form, errors) {
        const body = form.querySelector('.pm-body');
        if (!body) return;
        let box = body.querySelector('.pm-error-box');
        if (!box) {
            box = document.createElement('div');
            box.className = 'pm-error-box';
            box.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i><div></div>';
            body.insertBefore(box, body.firstChild);
        }
        const list = box.querySelector('div');
        list.innerHTML = '';
        Object.keys(errors || {}).forEach(function (field) {
            const msgs = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
            msgs.forEach(function (m) {
                const line = document.createElement('div');
                line.textContent = m;
                list.appendChild(line);
            });
        });
        if (body.scrollHeight > body.clientHeight) {
            body.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    // Add/Edit modals submit via fetch so the success toast appears instantly
    // (no full-page reload / no manual refresh needed), then the table refreshes.
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || !form.classList || !form.classList.contains('pm-form')) return;
        e.preventDefault();

        const submitBtn = form.querySelector('.pm-footer button[type="submit"]');
        const originalHtml = submitBtn ? submitBtn.innerHTML : null;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> SAVING...';
        }

        const token = form.querySelector('input[name="_token"]');
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token ? token.value : '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form)
        })
        .then(async (res) => {
            const data = await res.json().catch(() => null);
            if (res.status === 422 && data && data.errors) {
                renderPmErrors(form, data.errors);
                if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalHtml; }
                return null; // handled; skip success path
            }
            if (!res.ok || !data || !data.success) {
                throw new Error((data && data.message) || 'Request failed (' + res.status + ')');
            }
            return data;
        })
        .then((data) => {
            if (!data) return; // validation error already shown
            const msg = data.message || 'Saved.';
            // Instant feedback, then persist so the toast survives the table refresh.
            pmToast(msg, 'success');
            try { sessionStorage.setItem('pm_flash', JSON.stringify({ message: msg, type: 'success' })); } catch (e) {}
            // Reload so the new/edited row appears; the toast re-shows on the fresh page.
            setTimeout(function () { window.location.reload(); }, 450);
        })
        .catch((err) => {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalHtml; }
            pmToast(err.message || 'Failed to save.', 'error');
        });
    });

    // Delete via a custom confirmation modal + fetch: remove the row in place (no native confirm).
    let __pmDeleteForm = null;
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || !form.classList || !form.classList.contains('pm-delete-form')) return;
        e.preventDefault();
        openPmDeleteModal(form);
    });

    function openPmDeleteModal(form) {
        __pmDeleteForm = form;
        const name = form.dataset.name || 'this payment method';
        const nameEl = document.getElementById('pm-delete-name');
        if (nameEl) nameEl.textContent = name;
        const m = document.getElementById('pm-delete-modal');
        if (m) m.style.display = 'flex';
    }

    function closePmDeleteModal() {
        const m = document.getElementById('pm-delete-modal');
        if (m) m.style.display = 'none';
        __pmDeleteForm = null;
    }

    function confirmPmDelete() {
        const form = __pmDeleteForm;
        if (!form) return;
        const btn = form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;
        const token = form.querySelector('input[name="_token"]');
        closePmDeleteModal();

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token ? token.value : '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form)
        })
        .then(async (res) => {
            const data = await res.json().catch(() => null);
            if (!res.ok || !data || !data.success) {
                throw new Error((data && data.message) || 'Request failed (' + res.status + ')');
            }
            return data;
        })
        .then((data) => {
            const row = form.closest('tr');
            if (row) row.remove();
            pmToast(data.message || 'Deleted.', 'success');
        })
        .catch((err) => {
            if (btn) btn.disabled = false;
            pmToast(err.message || 'Failed to delete.', 'error');
        });
    }

    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closePmDeleteModal(); });

    function showFileName(input, targetId) {
        const el = document.getElementById(targetId);
        if (!el) return;
        el.textContent = (input.files && input.files[0]) ? input.files[0].name : 'No file chosen';
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Replay the success toast that was deferred across the post-save reload,
        // so the notification is always visible without a manual refresh.
        try {
            const raw = sessionStorage.getItem('pm_flash');
            if (raw) {
                sessionStorage.removeItem('pm_flash');
                const flash = JSON.parse(raw);
                pmToast(flash.message, flash.type === 'error' ? 'error' : 'success');
            }
        } catch (e) {}

        // Click on the dark overlay area closes the modal
        document.querySelectorAll(PM_MODAL_SELECTOR).forEach(function (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    overlay.style.display = 'none';
                    restoreBodyScroll();
                }
            });
        });

        // ESC key closes any open modal
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll(PM_MODAL_SELECTOR).forEach(function (m) {
                    m.style.display = 'none';
                });
                restoreBodyScroll();
            }
        });

        @if($errors->any())
            // Re-open the modal that failed validation (errors are shown inside it)
            @if(old('_form') === 'edit' && old('_form_id'))
                openEditMethodModal({{ (int) old('_form_id') }});
            @else
                openNewMethodModal();
            @endif
        @endif
    });
</script>
@endsection
