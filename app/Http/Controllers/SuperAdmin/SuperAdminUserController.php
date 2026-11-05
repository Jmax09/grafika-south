<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\StaffAccountCreatedMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SuperAdminUserController extends Controller
{
    /**
     * Display listing of users (Separated Admin & Customer panels).
     */
    public function index(Request $request)
    {
        // Staff admins only — super admin accounts are intentionally hidden from this list.
        $adminUsers = User::where('role', 'admin')->latest()->get();

        $customerQuery = User::where('role', 'customer')->latest();
        if ($request->filled('search')) {
            $search = $request->search;
            $customerQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('contact', 'like', "%{$search}%");
            });
        }
        $customerUsers = $customerQuery->paginate(15);

        return view('super_admin.users.index', compact('adminUsers', 'customerUsers'));
    }

    /**
     * Store new Admin or Staff user account.
     */
    public function storeAdmin(Request $request)
    {
        // Disallow creating additional Super Admin accounts
        if ($request->role === 'super_admin') {
            return back()->withInput()->with('error', 'Only one exclusive Super Admin is permitted in the system. New accounts must be created as Staff Admins.');
        }

        // Support 'staff' as an alias for 'admin'
        if ($request->role === 'staff') {
            $request->merge(['role' => 'admin']);
        }

        // All newly created administrative accounts are strictly Staff Admin
        $role = 'admin';

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['nullable', 'in:admin,staff'],
            'phone' => ['nullable', 'string', 'max:25'],
            'contact' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:8'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', Rule::in(array_keys(User::availablePermissions()))],
        ];

        $request->validate($rules, [
            'contact.required' => 'A contact phone number is required.',
            'contact.regex' => 'The contact number must be 11 digits and start with 09.',
            'permissions.required' => 'Please select at least one sidebar menu permission for this staff admin account.',
            'permissions.min' => 'Please select at least one sidebar menu permission for this staff admin account.',
            'permissions.*.in' => 'One or more selected permissions are invalid.',
        ]);

        $contact = $request->contact ?: $request->phone;

        try {
            // Generate a unique 8-character temporary password using only uppercase
            // letters and digits (at least one of each). The staff member is forced
            // to set their own password on first login.
            if ($request->filled('password')) {
                $initialPassword = $request->password;
            } else {
                $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $poolMax = strlen($pool) - 1;
                do {
                    $generated = '';
                    for ($i = 0; $i < 8; $i++) {
                        $generated .= $pool[random_int(0, $poolMax)];
                    }
                } while (!preg_match('/[A-Z]/', $generated) || !preg_match('/[0-9]/', $generated));
                $initialPassword = $generated;
            }

            // Username is no longer entered by the super admin — derive a unique
            // handle from the email's local part (staff can also log in via email).
            $emailLocal = strtolower(trim((string) Str::before($request->email, '@')));
            $baseUsername = preg_replace('/[^a-z0-9._-]/', '', $emailLocal);
            if ($baseUsername === '' || $baseUsername === null) {
                $baseUsername = 'staff';
            }
            $username = $baseUsername;
            $suffix = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . (++$suffix);
            }

            $user = User::create([
                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'username' => $username,
                'email_verified_at' => now(),
                'role' => $role,
                'permissions' => array_values(array_intersect((array) $request->permissions, array_keys(User::availablePermissions()))),
                'phone' => $contact,
                'contact' => $contact,
                'address' => $request->address,
                'password' => $initialPassword,
                'is_active' => true,
                'must_change_password' => true,
            ]);

            // Dispatch welcome email with the generated credentials.
            try {
                Mail::to($user->email)->sendNow(new StaffAccountCreatedMail($user, $initialPassword));
            } catch (\Throwable $mailEx) {
                Log::warning('Staff account creation email dispatch encountered an issue: ' . $mailEx->getMessage(), [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                ]);
            }

            return back()->with('success', "Staff Admin account '{$user->name}' created successfully! Login credentials were emailed to {$user->email}, and they will be required to set a new password on first login.");
        } catch (\Throwable $e) {
            Log::error('Staff account creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Failed to create staff account: ' . $e->getMessage());
        }
    }

    /**
     * Update user details or active status.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // System rule: The primary Super Admin account is protected from modification through the staff panel
        if ($user->role === 'super_admin') {
            return back()->with('error', 'The primary Super Administrator account is system-protected and cannot be modified through the staff management panel.');
        }

        // System rule: Enforce single super admin exclusivity
        if ($request->role === 'super_admin') {
            return back()->with('error', 'The system allows only one exclusive Super Admin. Accounts cannot be escalated to Super Admin.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['nullable', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'in:customer,admin,super_admin'],
            'phone' => ['nullable', 'string', 'max:25'],
            'contact' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
        ];

        if ($request->role === 'admin' || ($user->role === 'admin' && $request->role !== 'super_admin')) {
            $rules['permissions'] = ['required', 'array', 'min:1'];
            $rules['permissions.*'] = ['string', Rule::in(array_keys(User::availablePermissions()))];
        }

        $request->validate($rules, [
            'username.unique' => 'This username is already taken. Please choose another.',
            'username.alpha_dash' => 'Username may only contain letters, numbers, dashes, and underscores.',
            'permissions.required' => 'Please select at least one sidebar menu permission for this staff admin account.',
            'permissions.min' => 'Please select at least one sidebar menu permission for this staff admin account.',
            'permissions.*.in' => 'One or more selected permissions are invalid.',
        ]);

        if ($user->role === 'super_admin' && !$request->is_active) {
            return back()->with('error', 'The Super Admin account cannot be disabled.');
        }

        if ($user->role === 'customer' && $request->role !== 'customer') {
            return back()->with('error', 'Customer accounts cannot be escalated to Admin or Super Admin roles.');
        }

        $contact = $request->contact ?: ($request->phone ?: ($user->contact ?: $user->phone));

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $user->role === 'super_admin' ? 'super_admin' : $request->role,
            'phone' => $contact,
            'contact' => $contact,
            'address' => $request->input('address', $user->address),
            'is_active' => $request->is_active,
        ];

        if ($request->has('username')) {
            $updateData['username'] = $request->filled('username') ? strtolower(trim($request->username)) : null;
        }

        if ($request->role === 'admin') {
            $updateData['permissions'] = array_values(array_intersect((array) $request->permissions, array_keys(User::availablePermissions())));
        } elseif ($request->role === 'super_admin') {
            $updateData['permissions'] = null;
        }

        if ($request->filled('password')) {
            $request->validate(['password' => ['string', 'min:8']]);
            $updateData['password'] = $request->password;
        }

        $user->update($updateData);

        return back()->with('success', "Account '{$user->name}' updated successfully.");
    }

    /**
     * Toggle a staff account's active status directly from the table.
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        // System rule: The primary Super Admin account is protected and cannot be deactivated.
        if ($user->role === 'super_admin') {
            return back()->with('error', 'The primary Super Administrator account is system-protected and cannot be deactivated.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $state = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Account '{$user->name}' has been {$state}.");
    }

    /**
     * Delete user account (Super Admin only).
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'super_admin') {
            return back()->with('error', 'The exclusive Super Admin account cannot be deleted.');
        }

        $user->delete();
        return back()->with('success', "User account '{$user->name}' deleted.");
    }
}
