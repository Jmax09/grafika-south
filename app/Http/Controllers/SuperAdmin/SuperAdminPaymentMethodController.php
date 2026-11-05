<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class SuperAdminPaymentMethodController extends Controller
{
    /**
     * Display all payment methods for Super Admin management.
     */
    public function index()
    {
        // Cash is hidden from the Payments list by request — the record stays in the
        // DB (walk-in POS still uses it). Remove this filter to surface it again.
        $paymentMethods = PaymentMethod::latest()->get()->reject(fn ($pm) => $pm->isCashOrOfflineMethod())->values();
        return view('super_admin.payment_methods.index', compact('paymentMethods'));
    }

    /**
     * Store a new custom payment method.
     */
    public function store(Request $request)
    {
        // Default type to 'manual' — the Add New modal always submits this value via hidden input.
        $request->mergeIfMissing(['type' => 'manual']);

        $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'type'             => ['nullable', 'string', 'in:manual,gateway'],
            'account_name'     => ['required', 'string', 'max:255'],
            'account_number'   => ['required', 'string', 'max:255'],
            'instructions'     => ['nullable', 'string', 'max:1000'],
            'qr_code'          => ['required', 'file', 'image', 'max:5120'],
            'api_keys'         => ['nullable', 'string', 'max:2000'],
            'is_online_payment'=> ['nullable', 'boolean'],
        ]);

        $qrCodePath = null;
        if ($request->hasFile('qr_code')) {
            $qrCodePath = $request->file('qr_code')->store('qr_codes', 'public');
        }

        PaymentMethod::create([
            'name'             => $request->name,
            'type'             => $request->type ?? 'manual',
            'account_name'     => $request->account_name,
            'account_number'   => $request->account_number,
            'instructions'     => $request->instructions,
            'qr_code_path'     => $qrCodePath,
            'api_keys'         => $request->api_keys,
            // All user-added payment methods are online/digital channels.
            'is_online_payment'=> true,
            // New methods start active by default (status is managed via the table's Status button).
            'is_active'        => $request->boolean('is_active', true),
        ]);

        // AJAX (modal fetch): respond with JSON so the toast shows instantly, no reload lag.
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment Method added successfully.',
            ]);
        }

        return back()->with('success', 'Payment Method added successfully.');
    }

    /**
     * Update an existing payment method details.
     */
    public function update(Request $request, $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        // Default type to 'manual' — the Edit modal always submits this value via hidden input (mirrors Add New).
        $request->mergeIfMissing(['type' => 'manual']);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:manual,gateway'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'qr_code' => ['nullable', 'file', 'image', 'max:5120'],
            'api_keys' => ['nullable', 'string', 'max:2000'],
            'is_online_payment' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('qr_code')) {
            $qrCodePath = $request->file('qr_code')->store('qr_codes', 'public');
            $paymentMethod->qr_code_path = $qrCodePath;
        }

        $paymentMethod->update([
            'name' => $request->name,
            'type' => $request->type ?? 'manual',
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'instructions' => $request->instructions,
            'is_online_payment' => true,
            // Keep the current status unless explicitly provided (status is managed via the table's Status button).
            'is_active' => $request->has('is_active') ? $request->boolean('is_active', true) : $paymentMethod->is_active,
        ]);

        // AJAX (modal fetch): respond with JSON so the toast shows instantly, no reload lag.
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Payment Method '{$paymentMethod->name}' updated successfully.",
            ]);
        }

        return back()->with('success', "Payment Method '{$paymentMethod->name}' updated successfully.");
    }

    /**
     * Fast toggle active/disabled status.
     */
    public function toggleStatus(Request $request, $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->is_active = !$paymentMethod->is_active;
        $paymentMethod->save();

        $statusStr = $paymentMethod->is_active ? 'Enabled' : 'Disabled';
        $message = "Payment Method '{$paymentMethod->name}' has been {$statusStr}.";

        // AJAX (in-page) toggle: respond with JSON so the page does not reload.
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => (bool) $paymentMethod->is_active,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Delete custom payment method.
     */
    public function destroy(Request $request, $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $name = $paymentMethod->name;
        $paymentMethod->delete();

        $message = "Payment Method '{$name}' deleted successfully.";

        // AJAX (in-page delete): respond with JSON so the row is removed instantly.
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
