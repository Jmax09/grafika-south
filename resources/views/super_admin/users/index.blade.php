@extends('layouts.admin')

@section('title', 'User & Admin Accounts Control - Super Admin')

@section('content')
<style>
    .account-panel-card {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .panel-section-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.75rem;
    }

    /* Interactive Tab Bar Styling */
    .account-tabs-bar {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1.75rem;
        background: var(--bg-surface);
        padding: 0.5rem;
        border-radius: 14px;
        border: 1px solid var(--border-color);
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    }

    .account-tab-btn {
        flex: 1;
        padding: 0.8rem 1.25rem;
        border-radius: 10px;
        border: none;
        background: transparent;
        color: var(--text-muted);
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
    }

    .account-tab-btn:hover {
        background: var(--bg-page);
        color: var(--text-main);
    }

    .account-tab-btn.active-admin {
        background: var(--accent-color);
        color: var(--text-inverse);
        box-shadow: 0 4px 12px rgba(0, 153, 255, 0.25);
    }

    .account-tab-btn.active-customers {
        background: var(--brand-magenta-fg);
        color: var(--text-inverse);
        box-shadow: 0 4px 12px rgba(236, 0, 140, 0.25);
    }

    /* Suppress native browser password reveal button */
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear,
    input[type="password"]::-webkit-contacts-auto-fill-button,
    input[type="password"]::-webkit-credentials-auto-fill-button {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    /* Delete confirmation modal */
    #deleteConfirmModal{display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:9999;align-items:center;justify-content:center;padding:1rem;}
    #deleteConfirmModal .dcm-card{background:var(--bg-surface);border-radius:16px;max-width:420px;width:100%;padding:1.75rem 1.5rem 1.5rem;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,.25);}
    #deleteConfirmModal .dcm-icon{width:56px;height:56px;border-radius:50%;background:var(--danger-soft);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;}
    #deleteConfirmModal .dcm-icon i{font-size:1.5rem;color:var(--text-danger);}
    #deleteConfirmModal .dcm-title{font-size:1.2rem;font-weight:800;color:var(--text-main);margin:0 0 .5rem;}
    #deleteConfirmModal .dcm-text{font-size:.9rem;color:var(--text-muted);margin:0 0 1.5rem;line-height:1.5;}
    #deleteConfirmModal .dcm-text strong{color:var(--text-main);}
    #deleteConfirmModal .dcm-actions{display:flex;gap:.75rem;justify-content:center;}
    #deleteConfirmModal .dcm-cancel{flex:1;padding:.65rem 1rem;border-radius:10px;border:1px solid var(--border-color);background:var(--bg-surface);color:var(--text-main);font-weight:700;cursor:pointer;}
    #deleteConfirmModal .dcm-cancel:hover{background:var(--bg-subtle);}
    #deleteConfirmModal .dcm-confirm{flex:1;padding:.65rem 1rem;border-radius:10px;border:none;background:var(--danger-color);color:var(--text-inverse);font-weight:700;cursor:pointer;}
    #deleteConfirmModal .dcm-confirm:hover{background:var(--danger-color);}

    /* Staff permission cards */
    .perm-card{display:flex;align-items:flex-start;gap:.6rem;background:var(--bg-surface);border:1px solid var(--border-color);border-radius:8px;padding:.55rem .7rem;cursor:pointer;transition:border-color .15s,background .15s,box-shadow .15s;}
    .perm-card:hover{border-color:var(--border-color);background:var(--bg-page);}
    .perm-card:has(input:checked){border-color:var(--cmyk-cyan);background:var(--accent-soft);box-shadow:inset 0 0 0 1px var(--cmyk-cyan);}
    .perm-card input[type=checkbox]{margin-top:.15rem;cursor:pointer;accent-color:var(--cmyk-cyan);width:16px;height:16px;flex-shrink:0;}
    .perm-card .perm-name{font-size:.82rem;font-weight:700;color:var(--text-main);display:flex;align-items:center;gap:.35rem;}
    .perm-card .perm-name i{color:var(--brand-cyan-fg);font-size:.75rem;}
    .perm-card .perm-desc{font-size:.72rem;color:var(--text-muted);line-height:1.25;margin-top:.1rem;}
    /* Clickable table rows — same hover style as the Orders table (.order-row); click opens the Edit modal */
    tr.row-clickable { cursor: pointer; transition: all 0.2s ease !important; }
    tr.row-clickable:hover {
        background-color: var(--accent-soft) !important;
        box-shadow: inset 0 0 0 2px var(--accent-color) !important;
        position: relative;
        z-index: 5;
    }
</style>

@push('scripts')
<script>
    function openRowEditModal(event, modalId) {
        // Ignore clicks on buttons, forms, links, and their children inside the row
        if (event.target.closest('button, form, a, input, label, select, textarea')) return;
        openEditModal(modalId);
    }
</script>
@endpush

<!-- ========================================== -->
<!-- INTERACTIVE TAB SELECTION BAR              -->
<!-- ========================================== -->
<div class="account-tabs-bar">
    <button type="button" class="account-tab-btn {{ (request('search') || request('page')) ? '' : 'active-admin' }}" id="tab-btn-admins" onclick="switchAccountTab('admins')">
        <i class="fa-solid fa-user-shield"></i> Admin Accounts
    </button>
    <button type="button" class="account-tab-btn {{ (request('search') || request('page')) ? 'active-customers' : '' }}" id="tab-btn-customers" onclick="switchAccountTab('customers')">
        <i class="fa-solid fa-users"></i> Customer Accounts
    </button>
</div>

<!-- ========================================== -->
<!-- TAB CONTENT 1: ADMIN & SYSTEM ACCOUNTS      -->
<!-- ========================================== -->
<div id="tab-pane-admins" style="display: {{ (request('search') || request('page')) ? 'none' : 'block' }}; margin-bottom: 2.5rem;">
    <!-- ADMINS TABLE CARD (FULL WIDTH) -->
    <div class="account-panel-card" style="margin-bottom: 0;">
        <div class="panel-section-title" style="justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-user-shield" style="color: var(--brand-cyan-fg);"></i>
                Admin
            </div>
            <!-- CREATE ACCOUNT BUTTON BESIDE HEADER -->
            <button type="button" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.45rem 1rem;" onclick="openEditModal('modal-create-staff')">
                <i class="fa-solid fa-user-plus"></i> Create Staff Admin
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                <thead>
                    <tr style="background: var(--bg-page); text-align: left; color: var(--text-muted);">
                        <th style="padding: 0.75rem 0.85rem;">Name & Email</th>
                        <th style="padding: 0.75rem 0.85rem;">Permissions</th>
                        <th style="padding: 0.75rem 0.85rem;">Contact</th>
                        <th style="padding: 0.75rem 0.85rem;">Status</th>
                        <th style="padding: 0.75rem 0.85rem; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($adminUsers as $adm)
                        <tr class="{{ $adm->role !== 'super_admin' ? 'row-clickable' : '' }}" style="border-bottom: 1px solid var(--border-color);"@if($adm->role !== 'super_admin') onclick="openRowEditModal(event, 'modal-edit-{{ $adm->id }}')"@endif>
                            <td style="padding: 0.85rem;">
                                <strong style="color: var(--text-main); display: block;">
                                    {{ $adm->name }}

                                </strong>
                                <span style="font-size: 0.78rem; color: var(--text-muted);">{{ $adm->email }}</span>
                            </td>
                            <td style="padding: 0.85rem;">
                                @if($adm->role === 'admin')
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                        @php
                                            $userPerms = $adm->permissions;
                                            $allPerms = \App\Models\User::availablePermissions();
                                        @endphp
                                        @if(is_array($userPerms) && count($userPerms) > 0)
                                            @if(count($userPerms) === count($allPerms))
                                                <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-accent); background: var(--accent-soft); padding: 0.15rem 0.45rem; border-radius: 4px;">
                                                    <i class="fa-solid fa-layer-group"></i> Full Access
                                                </span>
                                            @else
                                                @foreach($userPerms as $p)
                                                    @if(isset($allPerms[$p]))
                                                        <span style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted); background: var(--bg-subtle); border: 1px solid var(--border-color); padding: 0.12rem 0.4rem; border-radius: 4px;" title="{{ $allPerms[$p]['description'] }}">
                                                            <i class="fa-solid {{ $allPerms[$p]['icon'] }}" style="font-size: 0.65rem; color: var(--text-muted);"></i>
                                                            {{ $allPerms[$p]['label'] }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @elseif($adm->permissions === null)
                                            <span style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted); background: var(--bg-page); padding: 0.15rem 0.45rem; border-radius: 4px;">
                                                Standard Access
                                            </span>
                                        @else
                                            <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-danger); background: var(--danger-soft); padding: 0.15rem 0.45rem; border-radius: 4px;">
                                                No Permissions
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <div>
                                        <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-violet); background: var(--brand-violet-soft); padding: 0.15rem 0.45rem; border-radius: 4px;">
                                            <i class="fa-solid fa-crown"></i> Full Access
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 0.85rem;">{{ $adm->contact ?: ($adm->phone ?: 'N/A') }}</td>
                            <td style="padding: 0.85rem;">
                                @if($adm->role === 'super_admin')
                                    <span style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 700; font-size: 0.78rem; color: var(--text-success); background: var(--success-soft); border: 1px solid var(--success-border); padding: 0.25rem 0.7rem; border-radius: 999px; cursor: default;" title="Protected Super Admin account">
                                        <i class="fa-solid fa-circle-check"></i> Active
                                    </span>
                                @else
                                    <form action="{{ route('super-admin.users.toggle-status', $adm->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit"
                                            title="{{ $adm->is_active ? 'Click to deactivate this account' : 'Click to activate this account' }}"
                                            style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 700; font-size: 0.78rem; cursor: pointer; border-radius: 999px; padding: 0.25rem 0.7rem; {{ $adm->is_active ? 'color:var(--text-success); background:var(--success-soft); border:1px solid var(--success-border);' : 'color:var(--text-danger); background:var(--danger-soft); border:1px solid var(--danger-border);' }}">
                                            <i class="fa-solid {{ $adm->is_active ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                            {{ $adm->is_active ? 'Active' : 'Deactivated' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td style="padding: 0.85rem; text-align: right;">
                                <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                                    @if($adm->role === 'super_admin')
                                        <!-- PROTECTED BADGE FOR SUPER ADMIN (CANNOT BE EDITED OR DELETED) -->
                                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-subtle); background: var(--bg-subtle); border: 1px solid var(--border-color); padding: 0.3rem 0.65rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem;" title="Super Administrator account is protected and cannot be edited or deleted">
                                            <i class="fa-solid fa-shield-halved" style="color: var(--text-muted);"></i> Protected
                                        </span>
                                    @else
                                        <!-- EDIT BUTTON (OPENS MODAL) -->
                                        <button type="button" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;" onclick="openEditModal('modal-edit-{{ $adm->id }}')">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>

                                        <!-- DELETE BUTTON -->
                                        <form id="del-form-{{ $adm->id }}" action="{{ route('super-admin.users.destroy', $adm->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline" onclick="openDeleteConfirm(this)" data-form="del-form-{{ $adm->id }}" data-name="{{ $adm->name }}" style="font-size: 0.75rem; padding: 0.3rem 0.6rem; color: var(--text-danger); border-color: var(--danger-border);">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- EDIT MODALS FOR ADMIN USERS -->
    @foreach($adminUsers as $adm)
        @if($adm->role !== 'super_admin')
            <div class="edit-modal-backdrop" id="modal-edit-{{ $adm->id }}">
                <div class="edit-modal-box" style="max-width: 640px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-user-pen" style="color: var(--brand-cyan-fg);"></i> Edit Admin Account
                        </h3>
                        <button type="button" onclick="closeEditModal('modal-edit-{{ $adm->id }}')" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-subtle);">&times;</button>
                    </div>

                <form action="{{ route('super-admin.users.update', $adm->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Full Name *</label>
                        <input type="text" name="name" value="{{ $adm->name }}" required placeholder="e.g. Maria Santos" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid {{ $errors->has('name') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: var(--radius-md); font-size: 0.9rem;">
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.85rem; margin-bottom: 0.85rem;">
                        <div>
                            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Email Address *</label>
                            <input type="email" name="email" value="{{ $adm->email }}" required placeholder="e.g. staff@grafika.com" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid {{ $errors->has('email') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: var(--radius-md); font-size: 0.9rem;">
                        </div>
                        <div>
                            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Contact Phone *</label>
                            <input type="text" name="contact" value="{{ $adm->contact ?: $adm->phone }}" required maxlength="11" inputmode="numeric" pattern="09[0-9]{9}" placeholder="e.g. 09170000003" oninput="this.value=this.value.replace(/[^0-9]/g,'')" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid {{ $errors->has('contact') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: var(--radius-md); font-size: 0.9rem;">
                            @error('contact')
                                <span style="color: var(--text-danger); font-size: 0.72rem; margin-top: 0.2rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Account Status</label>
                        <select name="is_active" required style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.9rem; background: var(--bg-surface);">
                            <option value="1" {{ $adm->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$adm->is_active ? 'selected' : '' }}>Deactivated</option>
                        </select>
                    </div>

                    {{-- Username is managed automatically; role is locked to this account's current role. --}}
                    <input type="hidden" name="role" value="{{ $adm->role }}">

                    @if($adm->role === 'admin')
                        <div style="margin-bottom: 0.85rem; border: 1px solid var(--border-color); border-radius: 12px; padding: 0.85rem; background: var(--bg-page);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                                <label style="font-size: 0.78rem; font-weight: 800; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fa-solid fa-list-check" style="color: var(--brand-cyan-fg);"></i> Sidebar Menu Access *
                                </label>
                                <div style="display: flex; gap: 0.35rem;">
                                    <button type="button" class="btn btn-outline" style="font-size: 0.7rem; padding: 0.15rem 0.5rem; height: auto;" onclick="setAllCheckboxes('edit-staff-permissions-{{ $adm->id }}', true)">
                                        Select All
                                    </button>
                                    <button type="button" class="btn btn-outline" style="font-size: 0.7rem; padding: 0.15rem 0.5rem; height: auto;" onclick="setAllCheckboxes('edit-staff-permissions-{{ $adm->id }}', false)">
                                        Clear All
                                    </button>
                                </div>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.6rem;">
                                Configure which sidebar sections this staff account can view and access:
                            </p>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 0.5rem;" class="edit-staff-permissions-{{ $adm->id }}">
                                @foreach(\App\Models\User::availablePermissions() as $permKey => $permMeta)
                                    <label style="display: flex; align-items: flex-start; gap: 0.6rem; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.5rem 0.7rem; cursor: pointer;">
                                        <input type="checkbox" name="permissions[]" value="{{ $permKey }}"
                                            style="margin-top: 0.2rem; cursor: pointer; accent-color: var(--cmyk-cyan); width: 16px; height: 16px;"
                                            {{ $adm->hasPermission($permKey) ? 'checked' : '' }}>
                                        <div style="flex: 1;">
                                            <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 0.35rem;">
                                                <i class="fa-solid {{ $permMeta['icon'] }}" style="color: var(--text-muted); font-size: 0.75rem;"></i>
                                                {{ $permMeta['label'] }}
                                            </div>
                                            <div style="font-size: 0.72rem; color: var(--text-muted); line-height: 1.25; margin-top: 0.1rem;">
                                                {{ $permMeta['description'] }}
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div style="margin-bottom: 0.85rem; border: 1px solid var(--info-border); border-radius: 10px; padding: 0.65rem 0.85rem; background: var(--accent-soft); color: var(--text-accent); font-size: 0.8rem;">
                            <i class="fa-solid fa-circle-info"></i> Super Admin accounts have full unrestricted access to all modules and configurations.
                        </div>
                    @endif

                    <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; align-items: center; gap: 0.6rem;">
                        <button type="button" class="btn btn-outline" onclick="closeEditModal('modal-edit-{{ $adm->id }}')" style="padding: 0.55rem 1.15rem; font-size: 0.88rem;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="padding: 0.55rem 1.25rem; font-size: 0.88rem;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    @endforeach
</div>

<!-- ========================================== -->
<!-- MODAL: CREATE STAFF ADMIN ACCOUNT          -->
<!-- ========================================== -->
<div class="edit-modal-backdrop {{ ($errors->any() && old('form_type') === 'create_staff') ? 'active' : '' }}" id="modal-create-staff">
    <div class="edit-modal-box" style="max-width: 640px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-user-plus" style="color: var(--brand-cyan-fg);"></i> Create Staff Admin Account
            </h3>
            <button type="button" onclick="closeEditModal('modal-create-staff')" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-subtle);">&times;</button>
        </div>

        @if($errors->any() && old('form_type') === 'create_staff')
            <div style="background: var(--danger-soft); color: var(--text-danger); border: 1px solid var(--danger-border); padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1rem;">
                <div style="font-weight: 700; margin-bottom: 0.25rem;"><i class="fa-solid fa-circle-exclamation"></i> Error creating account:</div>
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('super-admin.users.admin.store') }}" method="POST">
            @csrf
            <input type="hidden" name="form_type" value="create_staff">
            <input type="hidden" name="role" value="admin">

            <div style="margin-bottom: 0.85rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Maria Santos" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid {{ $errors->has('name') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: var(--radius-md); font-size: 0.9rem;">
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.85rem; margin-bottom: 0.85rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. staff@grafika.com" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid {{ $errors->has('email') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: var(--radius-md); font-size: 0.9rem;">
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Contact Phone *</label>
                    <input type="text" name="contact" value="{{ old('contact') }}" required maxlength="11" inputmode="numeric" pattern="09[0-9]{9}" placeholder="e.g. 09170000003" oninput="this.value=this.value.replace(/[^0-9]/g,'')" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid {{ $errors->has('contact') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: var(--radius-md); font-size: 0.9rem;">
                    @error('contact')
                        <span style="color: var(--text-danger); font-size: 0.72rem; margin-top: 0.2rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Username is auto-generated from the email; role is fixed to Staff Admin (see hidden input above). --}}

            <!-- PERMISSIONS CHECKLIST SECTION -->
            <div id="permissions-section-create-staff" style="margin-bottom: 1rem; border: 1px solid var(--border-color); border-radius: 12px; padding: 0.85rem; background: var(--bg-page);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <label style="font-size: 0.78rem; font-weight: 800; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-list-check" style="color: var(--brand-cyan-fg);"></i> Sidebar Menu Access *
                    </label>
                    <div style="display: flex; gap: 0.35rem;">
                        <button type="button" class="btn btn-outline" style="font-size: 0.7rem; padding: 0.15rem 0.5rem; height: auto;" onclick="setAllCheckboxes('create-staff-permissions', true)">
                            Select All
                        </button>
                        <button type="button" class="btn btn-outline" style="font-size: 0.7rem; padding: 0.15rem 0.5rem; height: auto;" onclick="setAllCheckboxes('create-staff-permissions', false)">
                            Clear All
                        </button>
                    </div>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.6rem;">
                    Select the sidebar sections this staff account can view and access:
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 0.5rem;" class="create-staff-permissions">
                    @foreach(\App\Models\User::availablePermissions() as $permKey => $permMeta)
                        <label class="perm-card">
                            <input type="checkbox" name="permissions[]" value="{{ $permKey }}"
                                {{ (old('form_type') === 'create_staff') ? (is_array(old('permissions')) && in_array($permKey, old('permissions')) ? 'checked' : '') : 'checked' }}>
                            <div style="flex: 1;">
                                <div class="perm-name">
                                    <i class="fa-solid {{ $permMeta['icon'] }}"></i>
                                    {{ $permMeta['label'] }}
                                </div>
                                <div class="perm-desc">
                                    {{ $permMeta['description'] }}
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Auto-Generated Password Banner -->
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-2xl mb-4 flex items-start gap-2.5">
                <span class="text-blue-600 text-base"><i class="fa-solid fa-envelope"></i></span>
                <div>
                    <p class="text-xs font-bold text-blue-900">Unique Password Auto-Generated</p>
                    <p class="text-[11px] text-blue-700 leading-relaxed mt-0.5">
                        A secure temporary password will be generated and emailed to the staff member. They must log in at <strong>/admin/login</strong> and <strong>set a new password on first login</strong>.
                    </p>
                </div>
            </div>

            <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; align-items: center; gap: 0.6rem;">
                <button type="button" class="btn btn-outline" onclick="closeEditModal('modal-create-staff')" style="padding: 0.55rem 1.15rem; font-size: 0.88rem;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.55rem 1.25rem; font-size: 0.88rem;">
                    <i class="fa-solid fa-user-plus"></i> Create Staff Admin
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- TAB CONTENT 2: CUSTOMER ACCOUNTS PANEL     -->
<!-- ========================================== -->
<div id="tab-pane-customers" style="display: {{ (request('search') || request('page')) ? 'block' : 'none' }};" class="account-panel-card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
        <div class="panel-section-title" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">
            <i class="fa-solid fa-users" style="color: var(--brand-yellow-fg);"></i>
            Customer
        </div>

        <form action="{{ route('super-admin.users.index') }}" method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
            <div style="display: flex; align-items: center;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone..."
                    style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-right: none; border-radius: 10px 0 0 10px; font-size: 0.9rem; outline: none; background: var(--bg-surface); color: var(--text-main); width: 240px; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='#0099FF';" onblur="this.style.borderColor='#CBD5E1';">
                <button type="submit"
                    style="padding: 0.6rem 1.1rem; background: var(--accent-color); color: var(--text-inverse); border: 1px solid var(--accent-color); border-radius: 0 10px 10px 0; font-size: 0.88rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; transition: background 0.15s;"
                    onmouseover="this.style.background='#0080FF'" onmouseout="this.style.background='#0099FF'">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
            </div>
            @if(request('search'))
                <a href="{{ route('super-admin.users.index') }}" class="btn btn-outline" style="padding: 0.45rem 0.75rem; font-size: 0.88rem; color: var(--text-muted);">Clear</a>
            @endif
        </form>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
            <thead>
                <tr style="background: var(--bg-page); text-align: left; color: var(--text-muted);">
                    <th style="padding: 0.75rem 0.85rem;">Customer Name & Email</th>
                    <th style="padding: 0.75rem 0.85rem;">Contact</th>
                    <th style="padding: 0.75rem 0.85rem;">Address</th>
                    <th style="padding: 0.75rem 0.85rem;">Status</th>
                    <th style="padding: 0.75rem 0.85rem; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customerUsers as $cust)
                    <tr class="row-clickable" style="border-bottom: 1px solid var(--border-color);" onclick="openRowEditModal(event, 'modal-edit-{{ $cust->id }}')">
                        <td style="padding: 0.85rem;">
                            <strong style="color: var(--text-main); display: block;">{{ $cust->name }}</strong>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">{{ $cust->email }}</span>
                        </td>
                        <td style="padding: 0.85rem;">{{ $cust->contact ?: ($cust->phone ?: 'N/A') }}</td>
                        <td style="padding: 0.85rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $cust->address ?: 'No address specified' }}
                        </td>
                        <td style="padding: 0.85rem;">
                            <form action="{{ route('super-admin.users.toggle-status', $cust->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit"
                                    title="{{ $cust->is_active ? 'Click to deactivate this account' : 'Click to activate this account' }}"
                                    style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 700; font-size: 0.78rem; cursor: pointer; border-radius: 999px; padding: 0.25rem 0.7rem; {{ $cust->is_active ? 'color:var(--text-success); background:var(--success-soft); border:1px solid var(--success-border);' : 'color:var(--text-danger); background:var(--danger-soft); border:1px solid var(--danger-border);' }}">
                                    <i class="fa-solid {{ $cust->is_active ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                    {{ $cust->is_active ? 'Active' : 'Deactivated' }}
                                </button>
                            </form>
                        </td>
                        <td style="padding: 0.85rem; text-align: right;">
                            <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                                <!-- EDIT CUSTOMER BUTTON -->
                                <button type="button" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;" onclick="openEditModal('modal-edit-{{ $cust->id }}')">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>

                                <form id="del-cust-form-{{ $cust->id }}" action="{{ route('super-admin.users.destroy', $cust->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-outline" onclick="openDeleteConfirm(this)" data-form="del-cust-form-{{ $cust->id }}" data-name="{{ $cust->name }}" style="font-size: 0.75rem; padding: 0.3rem 0.6rem; color: var(--text-danger); border-color: var(--danger-border);">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-subtle);">
                            No customer accounts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1rem;">
        {{ $customerUsers->appends(request()->query())->links() }}
    </div>

    <!-- EDIT MODALS FOR CUSTOMER USERS -->
    @foreach($customerUsers as $cust)
        <div class="edit-modal-backdrop" id="modal-edit-{{ $cust->id }}">
            <div class="edit-modal-box" style="max-width: 640px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-user-pen" style="color: var(--brand-cyan-fg);"></i> Edit Customer Profile
                    </h3>
                    <button type="button" onclick="closeEditModal('modal-edit-{{ $cust->id }}')" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-subtle);">&times;</button>
                </div>

                <form action="{{ route('super-admin.users.update', $cust->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- FIXED ROLE CUSTOMER TO PREVENT ROLE ESCALATION -->
                    <input type="hidden" name="role" value="customer">

                    <div style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Full Name *</label>
                        <input type="text" name="name" value="{{ $cust->name }}" required placeholder="e.g. Juan Dela Cruz" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid {{ $errors->has('name') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: var(--radius-md); font-size: 0.9rem;">
                    </div>

                    <div style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Email Address *</label>
                        <input type="email" name="email" value="{{ $cust->email }}" required placeholder="e.g. customer@email.com" style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid {{ $errors->has('email') ? 'var(--danger-color)' : 'var(--border-color)' }}; border-radius: var(--radius-md); font-size: 0.9rem;">
                    </div>

                    <div style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Account Status</label>
                        <select name="is_active" required style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.9rem; background: var(--bg-surface);">
                            <option value="1" {{ $cust->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$cust->is_active ? 'selected' : '' }}>Deactivated</option>
                        </select>
                    </div>

                    <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; align-items: center; gap: 0.6rem;">
                        <button type="button" class="btn btn-outline" onclick="closeEditModal('modal-edit-{{ $cust->id }}')" style="padding: 0.55rem 1.15rem; font-size: 0.88rem;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="padding: 0.55rem 1.25rem; font-size: 0.88rem;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="dcm-backdrop" onclick="if(event.target===this) closeDeleteConfirm()">
    <div class="dcm-card">
        <div class="dcm-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3 class="dcm-title">Delete account?</h3>
        <p class="dcm-text">Are you sure you want to permanently delete <strong id="deleteTargetName">this account</strong>? This action cannot be undone.</p>
        <div class="dcm-actions">
            <button type="button" class="dcm-cancel" onclick="closeDeleteConfirm()">Cancel</button>
            <button type="button" class="dcm-confirm" onclick="confirmDelete()"><i class="fa-solid fa-trash"></i> Yes, Delete</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchAccountTab(tabName) {
        const adminPane = document.getElementById('tab-pane-admins');
        const customerPane = document.getElementById('tab-pane-customers');
        const adminBtn = document.getElementById('tab-btn-admins');
        const customerBtn = document.getElementById('tab-btn-customers');

        if (tabName === 'admins') {
            adminPane.style.display = 'block';
            customerPane.style.display = 'none';
            adminBtn.className = 'account-tab-btn active-admin';
            customerBtn.className = 'account-tab-btn';
        } else {
            adminPane.style.display = 'none';
            customerPane.style.display = 'block';
            adminBtn.className = 'account-tab-btn';
            customerBtn.className = 'account-tab-btn active-customers';
        }
    }

    function setAllCheckboxes(containerClass, isChecked) {
        const container = document.querySelector('.' + containerClass);
        if (container) {
            container.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.checked = isChecked;
            });
        }
    }


    // Delete confirmation modal
    var pendingDeleteFormId = null;
    function openDeleteConfirm(btn){
        var modal = document.getElementById('deleteConfirmModal');
        if(!modal) return;
        pendingDeleteFormId = btn.getAttribute('data-form');
        var name = btn.getAttribute('data-name');
        var target = document.getElementById('deleteTargetName');
        if(target) target.textContent = name ? ('"' + name + '"') : 'this account';
        modal.style.display = 'flex';
    }
    function closeDeleteConfirm(){
        var modal = document.getElementById('deleteConfirmModal');
        if(modal) modal.style.display = 'none';
        pendingDeleteFormId = null;
    }
    function confirmDelete(){
        if(pendingDeleteFormId){
            var f = document.getElementById(pendingDeleteFormId);
            if(f) f.submit();
        }
    }
</script>
@endpush
@endsection
