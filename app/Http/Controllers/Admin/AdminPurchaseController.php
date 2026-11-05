<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierMaterial;
use App\Models\SupplierPayment;
use App\Services\PurchaseReceivingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPurchaseController extends Controller
{
    public function __construct(private PurchaseReceivingService $receiving)
    {
    }

    /**
     * All purchase orders with status / supplier / reference filters.
     */
    public function index(Request $request)
    {
        $query = Purchase::query()
            ->with('supplier')
            ->withCount('items')
            ->withSum('payments as total_paid', 'amount_paid');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $purchases = $query->latest()->get();
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        $totalPurchased = (float) Purchase::where('status', '!=', Purchase::STATUS_CANCELLED)->sum('total_amount');
        $totalPaid = (float) SupplierPayment::sum('amount_paid');

        $metrics = [
            'pending' => Purchase::where('status', Purchase::STATUS_PENDING)->count(),
            'received' => Purchase::where('status', Purchase::STATUS_RECEIVED)->count(),
            'cancelled' => Purchase::where('status', Purchase::STATUS_CANCELLED)->count(),
            'payables' => round($totalPurchased - $totalPaid, 2),
        ];

        return view('admin.purchases.index', compact('purchases', 'suppliers', 'metrics'));
    }

    /**
     * Smart PO creation form. Each supplier's catalog is exported as JSON so
     * Alpine can instantly filter item dropdowns and package conversions client-side.
     */
    public function create()
    {
        $suppliers = Supplier::with(['materials' => fn ($q) => $q->with('inventoryMaterial')])
            ->orderBy('name')
            ->get();

        $catalog = $suppliers->mapWithKeys(function (Supplier $supplier) {
            return [
                $supplier->id => $supplier->materials
                    ->sortBy(fn (SupplierMaterial $item) => mb_strtolower($item->item_name))
                    ->values()
                    ->map(fn (SupplierMaterial $item) => [
                        'id' => $item->id,
                        'item_name' => $item->item_name,
                        'supplier_unit' => $item->supplier_unit_name,
                        'base_unit' => $item->base_unit,
                        'conversion_qty' => (float) $item->conversion_qty,
                        'package_label' => $item->package_label,
                        'unit_cost' => (float) $item->unit_cost,
                        'base_unit_cost' => $item->base_unit_cost,
                        // Live stock so the buyer can judge how much to order.
                        'current_stock' => (float) ($item->inventoryMaterial?->current_stock ?? 0),
                        'is_low_stock' => (bool) ($item->inventoryMaterial?->isLowStock() ?? false),
                    ]),
            ];
        });

        return view('admin.purchases.create', compact('suppliers', 'catalog'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.supplier_material_id' => ['required', 'exists:supplier_materials,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $supplier = Supplier::findOrFail($data['supplier_id']);

        $requestedIds = collect($data['items'])->pluck('supplier_material_id')->unique();

        // Catalog items must belong to the selected supplier (prevents tampering).
        $catalogItems = SupplierMaterial::whereIn('id', $requestedIds)
            ->where('supplier_id', $supplier->id)
            ->with('inventoryMaterial')
            ->get()
            ->keyBy('id');

        if ($catalogItems->count() !== $requestedIds->count()) {
            return back()->withInput()->withErrors([
                'items' => 'One or more selected items do not belong to the chosen supplier.',
            ]);
        }

        $purchase = DB::transaction(function () use ($supplier, $data, $catalogItems) {
            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'reference_no' => Purchase::generateReferenceNo(),
                'total_amount' => 0,
                'status' => Purchase::STATUS_PENDING,
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $total = 0.0;

            foreach ($data['items'] as $row) {
                $item = $catalogItems->get($row['supplier_material_id']);

                if (!$item) {
                    continue;
                }

                $quantity = (int) $row['quantity'];
                $unitCost = (float) $item->unit_cost;
                $subtotal = round($quantity * $unitCost, 2);
                $total += $subtotal;

                $purchase->items()->create([
                    'supplier_material_id' => $item->id,
                    'item_name' => $item->item_name,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);
            }

            $purchase->update(['total_amount' => round($total, 2)]);

            return $purchase;
        });

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with('success', "Purchase order {$purchase->reference_no} created successfully.");
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.supplierMaterial.inventoryMaterial', 'payments']);

        $paymentMethods = ['Cash', 'Bank Transfer', 'GCash', 'Maya', 'Check'];

        return view('admin.purchases.show', compact('purchase', 'paymentMethods'));
    }

    /**
     * Transition a PO status. Pending → received restocks linked inventory
     * materials (idempotent). Received POs are locked to protect stock integrity.
     */
    public function updateStatus(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,received,cancelled'],
        ]);

        if ($purchase->status === Purchase::STATUS_RECEIVED && $data['status'] !== Purchase::STATUS_RECEIVED) {
            return back()->with('error', 'A received purchase order cannot be changed because its stock has already been added to inventory.');
        }

        if ($data['status'] === Purchase::STATUS_RECEIVED) {
            $result = $this->receiving->receive($purchase);
            $count = count($result['restocked'] ?? []);

            $message = 'Purchase order marked as received.';
            if ($result['already_received'] ?? false) {
                $message = 'This purchase order was already received.';
            } elseif ($count > 0) {
                $message .= " Restocked {$count} inventory material(s).";
            } else {
                $message .= ' No catalog items are linked to inventory, so nothing was restocked.';
            }

            return back()->with('success', $message);
        }

        $purchase->update(['status' => $data['status']]);

        return back()->with('success', "Purchase order status updated to {$data['status']}.");
    }

    public function destroy(Purchase $purchase)
    {
        $reference = $purchase->reference_no;
        $purchase->delete();

        return redirect()
            ->route('admin.purchases.index')
            ->with('success', "Purchase order {$reference} deleted.");
    }
}
