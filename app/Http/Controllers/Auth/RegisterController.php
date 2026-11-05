<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Show the customer registration form.
     */
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    /**
     * Handle customer registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->toDateString()],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^09\d{9}$/'],
            'contact' => ['nullable', 'string', 'max:25'],
            'region' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:500'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'phone.regex' => 'Please enter a valid 11-digit mobile number starting with 09.',
            'date_of_birth.required' => 'Please provide your date of birth.',
            'date_of_birth.before_or_equal' => 'You must be at least 18 years old to create an account.',
        ]);

        $contactNumber = $request->contact ?: $request->phone;

        $fullAddressParts = array_filter([
            $request->street,
            $request->barangay,
            $request->city,
            $request->province,
            $request->region,
        ]);
        $fullAddress = implode(', ', $fullAddressParts);

        $user = User::create([
            'name' => trim($request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name),
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'email' => $request->email,
            'phone' => $contactNumber,
            'contact' => $contactNumber,
            'region' => $request->region,
            'province' => $request->province,
            'city' => $request->city,
            'barangay' => $request->barangay,
            'street' => $request->street,
            'address' => $fullAddress,
            'password' => $request->password,
            'role' => 'customer',
            'is_active' => true,
        ]);

        $user->sendEmailVerificationNotification();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')
            ->with('success', 'Welcome to Grafika South! A verification email has been sent to ' . $user->email . ' via grafikasouth69@gmail.com.');
    }

    /**
     * Redirect user based on role if already logged in.
     */
    protected function redirectBasedOnRole($user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('customer.dashboard');
    }
}
