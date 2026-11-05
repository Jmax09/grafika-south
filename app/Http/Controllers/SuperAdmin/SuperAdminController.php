<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class SuperAdminController extends Controller
{
    /**
     * Display the Super Admin Dashboard (Unified with Admin Dashboard).
     */
    public function dashboard()
    {
        return redirect()->route('admin.dashboard');
    }

    /**
     * Show the form to create a new Admin account.
     */
    public function createAdmin()
    {
        return view('super_admin.create-admin');
    }

    /**
     * Handle creation and registration of a new Admin account.
     */
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', "New Staff Admin account '{$request->name}' created successfully!");
    }

    /**
     * Remove / Delete a Staff Admin account (Super Admin privilege).
     */
    public function destroyAdmin(User $user)
    {
        // 1. Prevent Super Admin from deleting their own account
        if (Auth::id() === $user->id) {
            return redirect()->back()->with('error', 'You cannot remove your own Super Admin account!');
        }

        // 2. Prevent removing another Super Admin unless authorized
        if ($user->role === 'super_admin') {
            return redirect()->back()->with('error', 'Super Admin accounts cannot be deleted directly.');
        }

        $adminName = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "Staff Admin account '{$adminName}' has been removed successfully.");
    }
}
