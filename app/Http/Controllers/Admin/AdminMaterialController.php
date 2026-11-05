<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialLog;
use App\Services\InventoryAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMaterialController extends Controller
{
    protected InventoryAutomationService $inventoryService;

    public function __construct(InventoryAutomationService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display inventory stock levels & ink remaining estimations with Search & Filter support.
     */
    public function index(Request $request)
    {
        $query = Material::with(['productMaterials.product', 'logs.order']);

        // Search by material name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by stock status at the SQL level.
        // Availability = current_stock - reserved_stock (reserved units are spoken for).
        if ($request->stock_status === 'out_of_stock') {
            $query->whereRaw('(current_stock - reserved_stock) <= 0');
        } elseif ($request->stock_status === 'low_stock') {
            $query->whereRaw('(current_stock - reserved_stock) > 0')
                  ->whereRaw('(current_stock - reserved_stock) <= low_stock_threshold');
        } elseif ($request->stock_status === 'in_stock') {
            $query->whereRaw('(current_stock - reserved_stock) > low_stock_threshold');
        }

        // Sorting (whitelisted columns only, defaults to newest first).
        $sortable = ['name', 'category', 'current_stock', 'available_stock', 'low_stock_threshold', 'created_at'];
        $sort = in_array($request->get('sort'), $sortable, true) ? $request->get('sort') : null;
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';

        if ($sort === 'available_stock') {
            // Sellable stock = current − reserved
            $query->orderByRaw('(current_stock - reserved_stock) ' . $direction);
        } elseif ($sort) {
            $query->orderBy($sort, $direction);
        } else {
            $query->latest();
        }

        $materials = $query->get();

        // Dashboard metrics, reflecting the current search/category scope.
        $metrics = [
            'total' => $materials->count(),
            'low_stock' => $materials->filter(fn ($m) => $m->isLowStock() && (float) $m->available_stock > 0)->count(),
            'out_of_stock' => $materials->filter(fn ($m) => (float) $m->available_stock <= 0)->count(),
            'stock_value' => round($materials->sum(fn ($m) => (float) $m->current_stock * (float) $m->unit_cost), 2),
        ];

        // Calculate prints remaining estimations for ink materials
        foreach ($materials as $material) {
            if (strtolower($material->unit) === 'ml') {
                $estimated = [];
                foreach ($material->productMaterials as $pm) {
                    if ($pm->product && $pm->quantity_used > 0) {
                        $estimated[] = [
                            'product_name' => $pm->product->name,
                            'avg_ml_per_print' => (float) $pm->quantity_used,
                            'prints_remaining' => $this->inventoryService->getEstimatedPrintsRemaining($material, (float) $pm->quantity_used),
                        ];
                    }
                }
                $material->estimated_prints = $estimated;
            }
        }

        // Filter by stock status is applied in SQL above.

        // Fetch distinct categories for filter dropdown
        $categories = Material::select('category')->distinct()->pluck('category')->filter();

        // Distinct units in use, so the Add/Edit unit dropdowns can suggest existing ones.
        $existingUnits = Material::select('unit')->distinct()->pluck('unit')->filter();

        return view('admin.materials.index', compact(
            'materials', 'categories', 'existingUnits', 'metrics', 'sort', 'direction'
        ));
    }

    /**
     * Stock Movements module — searchable, filterable inventory activity ledger.
     */
    public function movements(Request $request)
    {
        $logSearch = trim((string) $request->get('log_search', ''));
        $logType = trim((string) $request->get('log_type', ''));

        // Strict date validation: format + real calendar date (rejects far-future years like 9231).
        $sanitizeDate = function (string $value): string {
            if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
                return '';
            }
            if (!checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
                return '';
            }
            return $value;
        };
        $logStart = $sanitizeDate(trim((string) $request->get('log_start', '')));
        $logEnd = $sanitizeDate(trim((string) $request->get('log_end', '')));

        $logQuery = MaterialLog::with(['material', 'order.items.product'])->latest();

        if ($logSearch !== '') {
            $logQuery->where(function ($q) use ($logSearch) {
                $q->whereHas('material', fn ($m) => $m->where('name', 'LIKE', "%{$logSearch}%"))
                  ->orWhere('notes', 'LIKE', "%{$logSearch}%")
                  ->orWhereHas('order', function ($o) use ($logSearch) {
                      $o->where('order_number', 'LIKE', "%{$logSearch}%")
                        ->orWhereHas('items.product', fn ($p) => $p->where('name', 'LIKE', "%{$logSearch}%"));
                  });
            });
        }
        if ($logType !== '') {
            $logQuery->where('type', $logType);
        }
        if ($logStart !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $logStart)) {
            $logQuery->where('created_at', '>=', $logStart . ' 00:00:00');
        }
        if ($logEnd !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $logEnd)) {
            $logQuery->where('created_at', '<=', $logEnd . ' 23:59:59');
        }

        $logs = $logQuery->paginate(30)->withQueryString();
        $logs->setAppends([]);

        return view('admin.materials.movements', compact(
            'logs', 'logSearch', 'logType', 'logStart', 'logEnd'
        ));
    }

    /**
     * Store new material stock entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'custom_category' => ['required_if:category,other', 'nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'custom_unit' => ['required_if:unit,other', 'nullable', 'string', 'max:50'],
            'current_stock' => ['required', 'numeric', 'min:0'],
            'low_stock_threshold' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'production_time_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $finalCategory = trim($request->category === 'other' ? $request->custom_category : $request->category);
        $finalUnit = trim($request->unit === 'other' ? $request->custom_unit : $request->unit);

        $material = Material::create([
            'name' => trim($request->name),
            'category' => $finalCategory,
            'unit' => $finalUnit,
            'current_stock' => $request->current_stock,
            'low_stock_threshold' => $request->low_stock_threshold,
            'unit_cost' => $request->filled('unit_cost') ? (float) $request->unit_cost : null,
            'production_time_minutes' => $request->filled('production_time_minutes') ? (int) $request->production_time_minutes : null,
        ]);

        MaterialLog::create([
            'material_id' => $material->id,
            'quantity_deducted' => -$request->current_stock, // negative deduction means addition
            'type' => 'restock',
            'notes' => "Initial stock created: {$request->current_stock} {$finalUnit}",
        ]);

        return back()->with('success', "Material '{$material->name}' added to inventory!");
    }

    /**
     * Restock existing raw material.
     */
    public function restock(Request $request, $id)
    {
        $request->validate([
            'restock_quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $material = DB::transaction(function () use ($id, $request) {
            $material = Material::whereKey($id)->lockForUpdate()->firstOrFail();

            $quantityAdded = (float) $request->restock_quantity;
            $stockBefore = (float) $material->current_stock;
            $newStock = round($stockBefore + $quantityAdded, 3);

            $material->update(['current_stock' => $newStock]);

            MaterialLog::create([
                'material_id' => $material->id,
                'quantity_deducted' => -$quantityAdded,
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'type' => 'restock',
                'notes' => "Restocked {$quantityAdded} {$material->unit}. " . ($request->notes ?? ''),
            ]);

            return $material;
        });

        $payload = [
            'success' => true,
            'new_stock' => (float) $material->current_stock,
            'unit' => $material->unit,
            'is_low' => $material->isLowStock(),
            'is_out' => (float) $material->available_stock <= 0,
            'available_stock' => (float) $material->available_stock,
            'reserved_stock' => (float) $material->reserved_stock,
            'threshold' => (float) $material->low_stock_threshold,
            'message' => "Restocked {$request->restock_quantity} {$material->unit}. New stock: {$material->current_stock} {$material->unit}.",
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return back()->with('success', $payload['message']);
    }

    /**
     * Update material low stock threshold & current stock levels.
     */
    public function update(Request $request, $id)
    {
        $material = Material::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'custom_category' => ['required_if:category,other', 'nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'custom_unit' => ['required_if:unit,other', 'nullable', 'string', 'max:50'],
            'current_stock' => ['required', 'numeric', 'min:0'],
            'low_stock_threshold' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'production_time_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $finalCategory = trim($request->category === 'other' ? $request->custom_category : $request->category);
        $finalUnit = trim($request->unit === 'other' ? $request->custom_unit : $request->unit);

        $oldStock = (float) $material->current_stock;
        $newStock = (float) $request->current_stock;

        $material->update([
            'name' => trim($request->name),
            'category' => $finalCategory,
            'unit' => $finalUnit,
            'current_stock' => $newStock,
            'low_stock_threshold' => $request->low_stock_threshold,
            'unit_cost' => $request->filled('unit_cost') ? (float) $request->unit_cost : null,
            'production_time_minutes' => $request->filled('production_time_minutes') ? (int) $request->production_time_minutes : null,
        ]);

        if (abs($newStock - $oldStock) > 0.0001) {
            $diff = $oldStock - $newStock; // positive if deducted/reduced, negative if added
            MaterialLog::create([
                'material_id' => $material->id,
                'quantity_deducted' => $diff,
                'type' => 'manual_adjustment',
                'notes' => "Manual stock adjustment: Changed from {$oldStock} {$finalUnit} to {$newStock} {$finalUnit}",
            ]);

            // Re-sync reserved stock to guarantee it never exceeds or drifts from active pending orders
            $this->inventoryService->syncReservedStock($material->id);
        }

        return back()->with('success', "Material '{$material->name}' updated successfully.");
    }

    /**
     * Delete a raw material stock item.
     */
    public function destroy($id)
    {
        $material = Material::findOrFail($id);

        // Guard: materials linked to product BOMs or holding active reservations keep
        // historical orders intact — deleting them would corrupt BOM tracking.
        if ($material->productMaterials()->exists()) {
            return back()->with('error', "Material '{$material->name}' is linked to one or more products (BOM) and cannot be deleted. Unlink it from Products & BOM Builder first.");
        }

        if ((float) $material->reserved_stock > 0) {
            return back()->with('error', "Material '{$material->name}' has active stock reservations from pending orders and cannot be deleted yet.");
        }

        $name = $material->name;
        $material->delete();

        return back()->with('success', "Material '{$name}' has been deleted from inventory.");
    }

    /**
     * Delete a category from suggestions (only if no materials use it).
     */
    public function deleteCategory(Request $request, string $category)
    {
        $category = trim(urldecode($category));
        $count = Material::where('category', $category)->count();

        if ($count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete '{$category}' — {$count} material(s) still use this category.",
            ], 422);
        }

        // Category is orphaned (no materials use it anymore) — nothing to delete from DB,
        // it will no longer appear in the suggestions list on next page load.
        return response()->json([
            'success' => true,
            'message' => "Category '{$category}' removed from suggestions.",
        ]);
    }
}
