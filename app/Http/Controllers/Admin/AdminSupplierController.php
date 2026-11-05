<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierMaterial;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminSupplierController extends Controller
{
    /**
     * Supplier directory + accounts-payable summary dashboard.
     */
    public function index(Request $request)
    {
        $query = Supplier::query()
            ->withCount('purchases')
            ->withSum([
                'purchases as total_purchases' => fn ($q) => $q->where('status', '!=', Purchase::STATUS_CANCELLED),
            ], 'total_amount')
            ->withSum('payments as total_paid', 'amount_paid');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    // Also match items the supplier offers in their catalog
                    ->orWhereHas('materials.inventoryMaterial', function ($mq) use ($search) {
                        $mq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category')) {
            $category = $request->category;
            $query->whereHas('materials.inventoryMaterial', function ($mq) use ($category) {
                $mq->where('category', $category);
            });
        }

        $suppliers = $query->orderBy('name')->get();

        if ($request->filled('balance_status')) {
            $status = $request->balance_status;
            if ($status === 'with_balance') {
                $suppliers = $suppliers->filter(fn ($s) => $s->balance > 0)->values();
            } elseif ($status === 'settled') {
                $suppliers = $suppliers->filter(fn ($s) => $s->balance <= 0 && $s->purchases_count > 0)->values();
            } elseif ($status === 'no_purchases') {
                $suppliers = $suppliers->filter(fn ($s) => $s->purchases_count == 0)->values();
            }
        }

        $categories = Material::distinct()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('category')
            ->pluck('category');

        $totalPurchased = (float) Purchase::where('status', '!=', Purchase::STATUS_CANCELLED)->sum('total_amount');
        $totalPaid = (float) SupplierPayment::sum('amount_paid');

        $metrics = [
            'total_suppliers' => Supplier::count(),
            'total_purchased' => $totalPurchased,
            'total_paid' => $totalPaid,
            'total_payables' => round($totalPurchased - $totalPaid, 2),
            'pending_pos' => Purchase::where('status', Purchase::STATUS_PENDING)->count(),
        ];

        $recentPurchases = Purchase::with('supplier')->latest()->take(6)->get();

        return view('admin.suppliers.index', compact('suppliers', 'metrics', 'recentPurchases', 'categories'));
    }

    /**
     * Export suppliers & purchasing summary report as an Excel-compatible CSV.
     */
    public function export(Request $request)
    {
        $query = Supplier::query()
            ->withCount(['purchases', 'materials'])
            ->withSum([
                'purchases as total_purchases' => fn ($q) => $q->where('status', '!=', Purchase::STATUS_CANCELLED),
            ], 'total_amount')
            ->withSum('payments as total_paid', 'amount_paid');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    // Also match items the supplier offers in their catalog
                    ->orWhereHas('materials.inventoryMaterial', function ($mq) use ($search) {
                        $mq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category')) {
            $category = $request->category;
            $query->whereHas('materials.inventoryMaterial', function ($mq) use ($category) {
                $mq->where('category', $category);
            });
        }

        $suppliers = $query->orderBy('name')->get();

        if ($request->filled('balance_status')) {
            $status = $request->balance_status;
            if ($status === 'with_balance') {
                $suppliers = $suppliers->filter(fn ($s) => $s->balance > 0)->values();
            } elseif ($status === 'settled') {
                $suppliers = $suppliers->filter(fn ($s) => $s->balance <= 0 && $s->purchases_count > 0)->values();
            } elseif ($status === 'no_purchases') {
                $suppliers = $suppliers->filter(fn ($s) => $s->purchases_count == 0)->values();
            }
        }

        $filename = 'suppliers-purchasing-report-' . now()->format('Y-m-d') . '.xlsx';

        $exporter = new \App\Services\SupplierReportExporter();
        $xlsxContent = $exporter->generateXlsx($suppliers);

        return response($xlsxContent, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($xlsxContent),
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Supplier profile, catalog and full accounts-payable ledger.
     */
    public function show(Supplier $supplier)
    {
        $purchases = $supplier->purchases()->withCount('items')->latest()->get();
        $payments = $supplier->payments()->with('purchase')->latest()->get();

        // Catalog names come straight from inventory, so order by the material's name.
        $catalogItems = $supplier->materials()
            ->with('inventoryMaterial')
            ->get()
            ->sortBy(fn (SupplierMaterial $item) => mb_strtolower($item->item_name))
            ->values();

        // Catalog rows are packages of real stock, so the full inventory list is offered with each
        // item's live level — including items already catalogued, which may take more package sizes.
        $materials = Material::orderBy('name')
            ->get(['id', 'name', 'unit', 'current_stock', 'low_stock_threshold']);

        $totalPurchases = (float) $supplier->purchases()
            ->where('status', '!=', Purchase::STATUS_CANCELLED)
            ->sum('total_amount');
        $totalPaid = (float) $supplier->payments()->sum('amount_paid');
        $balance = round($totalPurchases - $totalPaid, 2);

        // Unified transaction timeline: purchases + payments, newest first.
        $timeline = collect();

        foreach ($purchases as $purchase) {
            $timeline->push([
                'type' => 'purchase',
                'date' => $purchase->created_at,
                'title' => "Purchase Order {$purchase->reference_no}",
                'amount' => (float) $purchase->total_amount,
                'status' => $purchase->status,
                'url' => route('admin.purchases.show', $purchase),
                'meta' => $purchase->items_count . ' item(s)',
            ]);
        }

        foreach ($payments as $payment) {
            $timeline->push([
                'type' => 'payment',
                'date' => $payment->paid_at ?? $payment->created_at,
                'title' => 'Payment via ' . $payment->payment_method,
                'amount' => (float) $payment->amount_paid,
                'status' => 'paid',
                'url' => $payment->purchase ? route('admin.purchases.show', $payment->purchase) : null,
                'meta' => $payment->reference_no ?: ($payment->purchase?->reference_no),
            ]);
        }

        $timeline = $timeline->sortByDesc('date')->values();

        return view('admin.suppliers.show', compact(
            'supplier',
            'purchases',
            'payments',
            'catalogItems',
            'materials',
            'totalPurchases',
            'totalPaid',
            'balance',
            'timeline'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateSupplier($request);

        $supplier = Supplier::create($data);

        return redirect()
            ->route('admin.suppliers.show', $supplier)
            ->with('success', "Supplier '{$supplier->name}' has been added.");
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $this->validateSupplier($request);

        $supplier->update($data);

        return back()->with('success', "Supplier '{$supplier->name}' updated successfully.");
    }

    public function destroy(Supplier $supplier)
    {
        $name = $supplier->name;
        $supplier->delete();

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', "Supplier '{$name}' has been removed.");
    }

    /**
     * Add a package of an existing inventory material to this supplier's catalog.
     */
    public function storeCatalog(Request $request, Supplier $supplier)
    {
        $this->normalizePackageName($request);

        $data = $request->validate($this->catalogRules($request, $supplier), $this->catalogMessages());

        $supplier->materials()->create($data);

        return back()->with('success', "Catalog entry for '{$data['supplier_unit_name']}' added to {$supplier->name}.");
    }

    public function updateCatalog(Request $request, SupplierMaterial $item)
    {
        $this->normalizePackageName($request);

        $data = $request->validate($this->catalogRules($request, $item->supplier, $item), $this->catalogMessages());

        $item->update($data);

        return back()->with('success', "Catalog entry for '{$item->item_name}' updated.");
    }

    public function destroyCatalog(SupplierMaterial $item)
    {
        $name = $item->item_name;
        $item->delete();

        return back()->with('success', "Catalog entry for '{$name}' removed.");
    }

    /**
     * A supplier catalog row is a package of an inventory material: the material is mandatory and
     * each package name may be mapped only once. The same material can still be catalogued again
     * under a different package (1 Box = 5 L, 1 Gallon = 3.785 L), which is why the Add form is
     * allowed to list inventory items that are already in the catalog.
     */
    private function catalogRules(Request $request, ?Supplier $supplier, ?SupplierMaterial $item = null): array
    {
        return [
            'inventory_material_id' => ['required', 'integer', Rule::exists('materials', 'id')],
            'supplier_unit_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('supplier_materials', 'supplier_unit_name')
                    ->where(function ($query) use ($supplier, $item, $request) {
                        $query->where('supplier_id', $supplier?->id ?? $item?->supplier_id)
                            ->where('inventory_material_id', $request->input('inventory_material_id'));
                    })
                    ->ignore($item?->id),
            ],
            'conversion_qty' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Plain-language guidance, because "has already been taken" is not helpful here — the admin is
     * usually allowed to add the same inventory item again, only under a different package name.
     */
    private function catalogMessages(): array
    {
        return [
            'inventory_material_id.required' => 'Pick the inventory item this package contains.',
            'supplier_unit_name.unique' => 'This supplier already sells that inventory item in a package with that name. '
                .'Use another package name (for example Gallon) or edit the existing entry.',
        ];
    }

    /**
     * Package names are matched case-insensitively, so they are stored in a single shape
     * ("Box", "Gallon") — "box" can never slip past the unique key as a second package.
     */
    private function normalizePackageName(Request $request): void
    {
        if ($request->filled('supplier_unit_name')) {
            $request->merge([
                'supplier_unit_name' => Str::ucfirst(mb_strtolower(trim((string) $request->input('supplier_unit_name')))),
            ]);
        }
    }

    /**
     * Record a payment (accounts payable settlement) to a supplier.
     */
    public function storePayment(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'max:100'],
            'purchase_id' => ['nullable', 'exists:purchases,id'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'paid_at' => ['nullable', 'date'],
        ]);

        // A purchase-linked payment must reference a PO belonging to this supplier.
        if (!empty($data['purchase_id'])) {
            $belongs = Purchase::whereKey($data['purchase_id'])
                ->where('supplier_id', $supplier->id)
                ->exists();

            if (!$belongs) {
                return back()->withInput()->withErrors([
                    'purchase_id' => 'The selected purchase order does not belong to this supplier.',
                ]);
            }
        }

        $data['supplier_id'] = $supplier->id;
        $data['paid_at'] = $data['paid_at'] ?? now()->toDateString();

        SupplierPayment::create($data);

        return back()->with('success', 'Payment of ₱' . number_format((float) $data['amount_paid'], 2) . ' recorded.');
    }

    public function destroyPayment(SupplierPayment $payment)
    {
        $amount = (float) $payment->amount_paid;
        $payment->delete();

        return back()->with('success', 'Payment of ₱' . number_format($amount, 2) . ' was deleted.');
    }

    private function validateSupplier(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
