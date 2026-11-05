<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryAutomationService
{
    // ──────────────────────────────────────────────
    //  1. RESERVE ORDER MATERIALS (pending stage)
    // ──────────────────────────────────────────────

    /**
     * Reserve BOM raw materials for a newly placed (pending) order.
     *
     * Physical stock (current_stock) is NOT touched. Instead, the required
     * quantities are added to reserved_stock so no other customer can claim
     * them. Effective available stock = current_stock - reserved_stock.
     *
     * Guards:
     *  - Idempotency: skips if material_logs already has reservation rows for this order.
     *  - Pessimistic locking: SELECT … FOR UPDATE prevents concurrent races.
     *  - Stock validation: throws InsufficientStockException if effective available stock is short.
     *  - Audit trail: logs a 'reservation' entry on every movement.
     *
     * @param  Order  $order
     * @return array  Summary of reservations performed.
     *
     * @throws InsufficientStockException
     */
    public function reserveOrderMaterials(Order $order): array
    {
        return DB::transaction(function () use ($order) {

            // ── Idempotency guard ─────────────────────
            $alreadyReserved = MaterialLog::where('order_id', $order->id)
                ->where('type', 'reservation')
                ->exists();

            if ($alreadyReserved) {
                Log::info("BOM reservation skipped (idempotent): Order #{$order->order_number} already reserved.");

                return $this->buildExistingReservationSummary($order);
            }

            // ── Eager-load BOM relationships ──────────
            $order->load(['items.product.materials', 'items.product.options.materials']);

            // ── Aggregate requirements per material ───
            $requirements = $this->aggregateRequirements($order);

            if (empty($requirements)) {
                return [];
            }

            // ── Pessimistic lock on material rows ─────
            $materialIds = array_keys($requirements);
            $materials = Material::whereIn('id', $materialIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // ── Validate EFFECTIVE availability (physical - reserved) ──
            $shortages = [];

            foreach ($requirements as $materialId => $info) {
                $material = $materials->get($materialId);

                if (!$material) {
                    $shortages[] = [
                        'material' => "Unknown (ID:{$materialId})",
                        'required' => $info['total_qty'],
                        'available' => 0,
                    ];
                    continue;
                }

                $available = (float) $material->current_stock - (float) $material->reserved_stock;
                if ($available < $info['total_qty']) {
                    $shortages[] = [
                        'material' => $material->name,
                        'required' => $info['total_qty'],
                        'available' => max(0, $available),
                    ];
                }
            }

            if (!empty($shortages)) {
                throw new InsufficientStockException($shortages);
            }

            // ── Perform reservations ──────────────────
            $reservationSummary = [];

            foreach ($requirements as $materialId => $info) {
                $material = $materials->get($materialId);
                $reservedBefore = (float) $material->reserved_stock;
                $reservedAfter = $reservedBefore + $info['total_qty'];

                $material->update([
                    'reserved_stock' => $reservedAfter,
                ]);

                MaterialLog::create([
                    'material_id' => $material->id,
                    'order_id' => $order->id,
                    'quantity_deducted' => $info['total_qty'],
                    'stock_before' => $reservedBefore,
                    'stock_after' => $reservedAfter,
                    'type' => 'reservation',
                    'notes' => "Reserved {$info['total_qty']} {$material->unit} for pending Order #{$order->order_number} ({$info['breakdown']})",
                ]);

                $reservationSummary[] = [
                    'material' => $material->name,
                    'unit' => $material->unit,
                    'quantity_reserved' => $info['total_qty'],
                    'reserved_before' => $reservedBefore,
                    'reserved_after' => $reservedAfter,
                ];
            }

            Log::info("BOM reservation completed: Order #{$order->order_number}, " . count($reservationSummary) . ' materials reserved.');

            return $reservationSummary;
        });
    }

    // ──────────────────────────────────────────────
    //  1b. DIRECT DEDUCTION (legacy / fallback path)
    // ──────────────────────────────────────────────

    /**
     * Process automated BOM raw-material deductions for an order immediately.
     *
     * Used as a fallback for legacy orders that have no reservation yet.
     * New pending orders should use reserveOrderMaterials() instead, and
     * convertReservationToDeduction() upon approval.
     *
     * Guards:
     *  - Idempotency: skips if material_logs already has auto_deduction rows for this order.
     *  - Pessimistic locking: SELECT … FOR UPDATE prevents concurrent races.
     *  - Stock validation: throws InsufficientStockException if any material is short.
     *  - Audit trail: logs stock_before/stock_after on every movement.
     *
     * @param  Order  $order
     * @return array  Summary of deductions performed.
     *
     * @throws InsufficientStockException
     */
    public function processOrderDeduction(Order $order): array
    {
        return DB::transaction(function () use ($order) {

            // ── Idempotency guard ─────────────────────
            $alreadyProcessed = MaterialLog::where('order_id', $order->id)
                ->where('type', 'auto_deduction')
                ->exists();

            if ($alreadyProcessed) {
                Log::info("BOM deduction skipped (idempotent): Order #{$order->order_number} already processed.");

                return $this->buildExistingSummary($order);
            }

            // ── Eager-load BOM relationships ──────────
            $order->load(['items.product.materials', 'items.product.options.materials']);

            // ── Aggregate requirements per material ───
            $requirements = $this->aggregateRequirements($order);

            if (empty($requirements)) {
                return [];
            }

            // ── Pessimistic lock on material rows ─────
            $materialIds = array_keys($requirements);
            $materials = Material::whereIn('id', $materialIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // ── Validate stock availability ───────────
            $shortages = [];

            foreach ($requirements as $materialId => $info) {
                $material = $materials->get($materialId);

                if (!$material) {
                    $shortages[] = [
                        'material' => "Unknown (ID:{$materialId})",
                        'required' => $info['total_qty'],
                        'available' => 0,
                    ];
                    continue;
                }

                $available = (float) $material->current_stock - (float) $material->reserved_stock;
                if ($available < $info['total_qty']) {
                    $shortages[] = [
                        'material' => $material->name,
                        'required' => $info['total_qty'],
                        'available' => max(0, $available),
                    ];
                }
            }

            if (!empty($shortages)) {
                throw new InsufficientStockException($shortages);
            }

            // ── Perform deductions ────────────────────
            $deductionsSummary = [];

            foreach ($requirements as $materialId => $info) {
                $material = $materials->get($materialId);
                $stockBefore = (float) $material->current_stock;
                $stockAfter = $stockBefore - $info['total_qty'];

                $material->update([
                    'current_stock' => $stockAfter,
                ]);

                MaterialLog::create([
                    'material_id' => $material->id,
                    'order_id' => $order->id,
                    'quantity_deducted' => $info['total_qty'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'type' => 'auto_deduction',
                    'notes' => "Auto-deducted {$info['total_qty']} {$material->unit} for Order #{$order->order_number} ({$info['breakdown']})",
                ]);

                $deductionsSummary[] = [
                    'material' => $material->name,
                    'unit' => $material->unit,
                    'quantity_deducted' => $info['total_qty'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'low_stock_triggered' => $stockAfter <= (float) $material->low_stock_threshold,
                ];

                // ── Low-stock alert ───────────────────
                if ($stockAfter <= (float) $material->low_stock_threshold) {
                    $this->triggerLowStockNotification($material, $stockAfter);
                }
            }

            Log::info("BOM deduction completed: Order #{$order->order_number}, " . count($deductionsSummary) . ' materials deducted.');

            return $deductionsSummary;
        });
    }

    // ──────────────────────────────────────────────
    //  2. CONVERT RESERVATION → DEDUCTION (approval)
    // ──────────────────────────────────────────────

    /**
     * Convert an order's stock reservation into an actual deduction.
     * Called when an order transitions into approved/printing.
     *
     * Physical stock is finally reduced here, and the reservation is lifted.
     * Falls back to a direct deduction when no reservation exists (legacy orders).
     *
     * @param  Order  $order
     * @return array  Summary of deductions performed.
     *
     * @throws InsufficientStockException
     */
    public function convertReservationToDeduction(Order $order): array
    {
        return DB::transaction(function () use ($order) {

            // ── Idempotency guard: already deducted? ──
            $alreadyProcessed = MaterialLog::where('order_id', $order->id)
                ->where('type', 'auto_deduction')
                ->exists();

            if ($alreadyProcessed) {
                Log::info("BOM deduction skipped (idempotent): Order #{$order->order_number} already processed.");

                return $this->buildExistingSummary($order);
            }

            // ── Load existing reservations ────────────
            $reservationLogs = MaterialLog::where('order_id', $order->id)
                ->where('type', 'reservation')
                ->lockForUpdate()
                ->get();

            if ($reservationLogs->isEmpty()) {
                // No reservation (e.g. legacy order created before this system)
                // → perform a direct deduction with full validation.
                return $this->processOrderDeduction($order);
            }

            // ── Lock affected material rows ───────────
            $materialIds = $reservationLogs->pluck('material_id')->unique()->toArray();
            $materials = Material::whereIn('id', $materialIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $deductionsSummary = [];

            foreach ($reservationLogs as $reservation) {
                $material = $materials->get($reservation->material_id);

                if (!$material) {
                    Log::warning("BOM conversion: Material ID {$reservation->material_id} not found, skipping.");
                    continue;
                }

                $quantityToDeduct = (float) $reservation->quantity_deducted;
                $stockBefore = (float) $material->current_stock;
                $stockAfter = $stockBefore - $quantityToDeduct;
                $reservedBefore = (float) $material->reserved_stock;
                $reservedAfter = max(0, $reservedBefore - $quantityToDeduct);

                // Guard: physical stock must still cover the deduction
                // (reservation should guarantee this, but restocks/adjustments may drift).
                if ($stockAfter < 0) {
                    throw new InsufficientStockException([
                        [
                            'material' => $material->name,
                            'required' => $quantityToDeduct,
                            'available' => $stockBefore,
                        ],
                    ]);
                }

                $material->update([
                    'current_stock' => $stockAfter,
                    'reserved_stock' => $reservedAfter,
                ]);

                // Mark the reservation row as converted (reuse notes column)
                $reservation->update([
                    'notes' => $reservation->notes . ' [CONVERTED TO DEDUCTION]',
                ]);

                MaterialLog::create([
                    'material_id' => $material->id,
                    'order_id' => $order->id,
                    'quantity_deducted' => $quantityToDeduct,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'type' => 'auto_deduction',
                    'notes' => "Auto-deducted {$quantityToDeduct} {$material->unit} for approved Order #{$order->order_number} (reservation converted)",
                ]);

                $deductionsSummary[] = [
                    'material' => $material->name,
                    'unit' => $material->unit,
                    'quantity_deducted' => $quantityToDeduct,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'low_stock_triggered' => $stockAfter <= (float) $material->low_stock_threshold,
                ];

                // ── Low-stock alert ───────────────────
                if ($stockAfter <= (float) $material->low_stock_threshold) {
                    $this->triggerLowStockNotification($material, $stockAfter);
                }
            }

            Log::info("BOM reservation converted to deduction: Order #{$order->order_number}, " . count($deductionsSummary) . ' materials deducted.');

            return $deductionsSummary;
        });
    }

    // ──────────────────────────────────────────────
    //  3. RELEASE RESERVATION (cancel while pending)
    // ──────────────────────────────────────────────

    /**
     * Release all reservations for a cancelled order that was never approved.
     * Physical stock is untouched — only reserved_stock is rolled back.
     *
     * @param  Order  $order
     * @return array  Summary of released reservations.
     */
    public function releaseReservation(Order $order): array
    {
        return DB::transaction(function () use ($order) {
            $reservationLogs = MaterialLog::where('order_id', $order->id)
                ->where('type', 'reservation')
                ->where('notes', 'not like', '%[CONVERTED TO DEDUCTION]%')
                ->lockForUpdate()
                ->get();

            if ($reservationLogs->isEmpty()) {
                Log::info("BOM reservation release skipped: No active reservations found for Order #{$order->order_number}.");

                return [];
            }

            // Idempotency guard
            $alreadyReleased = MaterialLog::where('order_id', $order->id)
                ->where('type', 'reservation_release')
                ->exists();

            if ($alreadyReleased) {
                Log::info("BOM reservation release skipped (idempotent): Order #{$order->order_number} already released.");

                return [];
            }

            $releaseSummary = [];
            $materialIds = $reservationLogs->pluck('material_id')->unique()->toArray();

            $materials = Material::whereIn('id', $materialIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($reservationLogs as $reservation) {
                $material = $materials->get($reservation->material_id);

                if (!$material) {
                    Log::warning("BOM reservation release: Material ID {$reservation->material_id} not found, skipping.");
                    continue;
                }

                $quantityToRelease = (float) $reservation->quantity_deducted;
                $reservedBefore = (float) $material->reserved_stock;
                $reservedAfter = max(0, $reservedBefore - $quantityToRelease);

                $material->update([
                    'reserved_stock' => $reservedAfter,
                ]);

                MaterialLog::create([
                    'material_id' => $material->id,
                    'order_id' => $order->id,
                    'quantity_deducted' => -$quantityToRelease, // negative = release
                    'stock_before' => $reservedBefore,
                    'stock_after' => $reservedAfter,
                    'type' => 'reservation_release',
                    'notes' => "Released reservation of {$quantityToRelease} {$material->unit} for cancelled Order #{$order->order_number}",
                ]);

                $releaseSummary[] = [
                    'material' => $material->name,
                    'unit' => $material->unit,
                    'quantity_released' => $quantityToRelease,
                    'reserved_before' => $reservedBefore,
                    'reserved_after' => $reservedAfter,
                ];
            }

            Log::info("BOM reservation release completed: Order #{$order->order_number}, " . count($releaseSummary) . ' reservations released.');

            return $releaseSummary;
        });
    }

    // ──────────────────────────────────────────────
    //  4. REVERSE ORDER DEDUCTION (cancel after approval)
    // ──────────────────────────────────────────────

    /**
     * Reverse all auto_deduction entries for a cancelled order,
     * restoring stock to the materials.
     *
     * @param  Order  $order
     * @return array  Summary of restorations performed.
     */
    public function reverseOrderDeduction(Order $order): array
    {
        return DB::transaction(function () use ($order) {
            $deductionLogs = MaterialLog::where('order_id', $order->id)
                ->where('type', 'auto_deduction')
                ->get();

            if ($deductionLogs->isEmpty()) {
                Log::info("BOM reversal skipped: No deductions found for Order #{$order->order_number}.");

                return [];
            }

            // Check if already reversed (idempotency)
            $alreadyReversed = MaterialLog::where('order_id', $order->id)
                ->where('type', 'order_reversal')
                ->exists();

            if ($alreadyReversed) {
                Log::info("BOM reversal skipped (idempotent): Order #{$order->order_number} already reversed.");

                return [];
            }

            $restorationSummary = [];
            $materialIds = $deductionLogs->pluck('material_id')->unique()->toArray();

            // Pessimistic lock on all affected materials
            $materials = Material::whereIn('id', $materialIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($deductionLogs as $log) {
                $material = $materials->get($log->material_id);

                if (!$material) {
                    Log::warning("BOM reversal: Material ID {$log->material_id} not found, skipping.");
                    continue;
                }

                $quantityToRestore = (float) $log->quantity_deducted;
                $stockBefore = (float) $material->current_stock;
                $stockAfter = $stockBefore + $quantityToRestore;

                $material->update([
                    'current_stock' => $stockAfter,
                ]);

                MaterialLog::create([
                    'material_id' => $material->id,
                    'order_id' => $order->id,
                    'quantity_deducted' => -$quantityToRestore, // negative = restoration
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'type' => 'order_reversal',
                    'notes' => "Reversed {$quantityToRestore} {$material->unit} for cancelled Order #{$order->order_number}",
                ]);

                $restorationSummary[] = [
                    'material' => $material->name,
                    'unit' => $material->unit,
                    'quantity_restored' => $quantityToRestore,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                ];
            }

            Log::info("BOM reversal completed: Order #{$order->order_number}, " . count($restorationSummary) . ' materials restored.');

            return $restorationSummary;
        });
    }

    // ──────────────────────────────────────────────
    //  5. READ-ONLY STOCK VALIDATION (pre-flight)
    // ──────────────────────────────────────────────

    /**
     * Check whether all BOM materials for an order have sufficient EFFECTIVE stock
     * (physical minus reserved) WITHOUT acquiring locks. Suitable for UI pre-flight checks.
     *
     * @param  Order  $order
     * @return array  List of shortages. Empty array = all clear.
     */
    public function validateStockAvailability(Order $order): array
    {
        $order->load(['items.product.materials', 'items.product.options.materials']);
        $requirements = $this->aggregateRequirements($order);

        if (empty($requirements)) {
            return [];
        }

        $materials = Material::whereIn('id', array_keys($requirements))
            ->get()
            ->keyBy('id');

        $shortages = [];

        foreach ($requirements as $materialId => $info) {
            $material = $materials->get($materialId);
            $available = $material
                ? (float) $material->current_stock - (float) $material->reserved_stock
                : 0;

            if ($available < $info['total_qty']) {
                $shortages[] = [
                    'material' => $material ? $material->name : "Unknown (ID:{$materialId})",
                    'required' => $info['total_qty'],
                    'available' => max(0, $available),
                ];
            }
        }

        return $shortages;
    }

    // ──────────────────────────────────────────────
    //  6. ESTIMATED PRINTS REMAINING (unchanged)
    // ──────────────────────────────────────────────

    /**
     * Calculate estimated prints remaining for ink/materials based on product BOM average.
     */
    public function getEstimatedPrintsRemaining(Material $material, float $avgQuantityPerPrint): int
    {
        if ($avgQuantityPerPrint <= 0) {
            return 0;
        }

        // Prints still producible = sellable stock (physical minus reserved).
        return (int) floor((float) $material->available_stock / $avgQuantityPerPrint);
    }

    // ──────────────────────────────────────────────
    //  7. DEDUCTION / RESERVATION LOGS FOR ORDER VIEWS
    // ──────────────────────────────────────────────

    /**
     * Build a summary of an order's material movements (reservations, deductions,
     * releases, reversals) for display in order detail views.
     */
    public function getOrderMaterialActivity(Order $order): array
    {
        return MaterialLog::where('order_id', $order->id)
            ->whereIn('type', ['auto_deduction', 'order_reversal', 'reservation', 'reservation_release'])
            ->with('material')
            ->latest()
            ->get()
            ->map(fn ($log) => [
                'type' => $log->type,
                'material' => $log->material?->name ?? 'Unknown',
                'unit' => $log->material?->unit ?? '',
                'quantity' => (float) $log->quantity_deducted,
                'stock_before' => (float) $log->stock_before,
                'stock_after' => (float) $log->stock_after,
                'notes' => $log->notes,
                'created_at' => $log->created_at,
            ])
            ->toArray();
    }

    /**
     * Audit and synchronize reserved_stock on all materials (or a specific material)
     * based on active pending/design_review orders that have not yet had their reservations converted.
     *
     * @param int|null $materialId
     * @return array<int, float> Map of material_id => new reserved_stock
     */
    public function syncReservedStock(?int $materialId = null): array
    {
        $materialsQuery = Material::query();
        if ($materialId) {
            $materialsQuery->where('id', $materialId);
        }
        $materials = $materialsQuery->get();

        $synced = [];
        foreach ($materials as $material) {
            $activeReserved = (float) MaterialLog::where('material_id', $material->id)
                ->where('type', 'reservation')
                ->where('notes', 'not like', '%[CONVERTED TO DEDUCTION]%')
                ->whereHas('order', function ($q) {
                    $q->whereIn('status', ['pending', 'design_review']);
                })
                ->sum('quantity_deducted');

            // Cap at physical stock
            $activeReserved = max(0, min((float) $material->current_stock, $activeReserved));

            if (abs((float) $material->reserved_stock - $activeReserved) > 0.0001) {
                $material->update(['reserved_stock' => $activeReserved]);
            }
            $synced[$material->id] = $activeReserved;
        }

        return $synced;
    }
    // ──────────────────────────────────────────────

    /**
     * Aggregate BOM requirements across all order items into a per-material map.
     *
     * @return array<int, array{total_qty: float, breakdown: string}>
     */
    private function aggregateRequirements(Order $order): array
    {
        $requirements = [];

        foreach ($order->items as $item) {
            $product = $item->product;

            if (!$product) {
                continue;
            }

            // Retrieve materials for product + options
            $itemRequirements = $product->getRequiredMaterials((int) $item->quantity, $item->options_selected);

            foreach ($itemRequirements as $req) {
                $material = $req['material'];
                $totalForItem = $req['quantity'];
                $materialId = $material->id;

                if (!isset($requirements[$materialId])) {
                    $requirements[$materialId] = [
                        'total_qty' => 0,
                        'breakdown' => '',
                    ];
                }

                $requirements[$materialId]['total_qty'] += $totalForItem;

                // Build human-readable breakdown for log notes
                $bomPerUnit = (int) $item->quantity > 0 ? ($totalForItem / (int) $item->quantity) : 0;
                $part = "{$item->quantity}× {$product->name} @ {$bomPerUnit}{$material->unit}";
                $requirements[$materialId]['breakdown'] .= ($requirements[$materialId]['breakdown'] ? ' + ' : '') . $part;
            }
        }

        return $requirements;
    }

    /**
     * Build a summary from existing material_log reservation entries (for idempotent re-calls).
     */
    private function buildExistingReservationSummary(Order $order): array
    {
        return MaterialLog::where('order_id', $order->id)
            ->where('type', 'reservation')
            ->with('material')
            ->get()
            ->map(fn ($log) => [
                'material' => $log->material?->name ?? 'Unknown',
                'unit' => $log->material?->unit ?? '',
                'quantity_reserved' => (float) $log->quantity_deducted,
                'reserved_before' => (float) $log->stock_before,
                'reserved_after' => (float) $log->stock_after,
            ])
            ->toArray();
    }

    /**
     * Build a summary from existing material_log entries (for idempotent re-calls).
     */
    private function buildExistingSummary(Order $order): array
    {
        return MaterialLog::where('order_id', $order->id)
            ->where('type', 'auto_deduction')
            ->with('material')
            ->get()
            ->map(fn ($log) => [
                'material' => $log->material?->name ?? 'Unknown',
                'unit' => $log->material?->unit ?? '',
                'quantity_deducted' => (float) $log->quantity_deducted,
                'stock_before' => (float) $log->stock_before,
                'stock_after' => (float) $log->stock_after,
                'low_stock_triggered' => false,
            ])
            ->toArray();
    }

    /**
     * Trigger low stock notifications to all Super Admin users.
     */
    private function triggerLowStockNotification(Material $material, float $currentStock): void
    {
        $superAdmins = User::where('role', 'super_admin')->where('is_active', true)->get();
        $message = "CRITICAL LOW STOCK ALERT: Material '{$material->name}' has dropped to {$currentStock} {$material->unit} (Threshold: {$material->low_stock_threshold} {$material->unit}). Please restock immediately.";

        foreach ($superAdmins as $superAdmin) {
            Notification::create([
                'user_id' => $superAdmin->id,
                'type' => 'low_stock_alert',
                'message' => $message,
                'is_read' => false,
                'link' => route('admin.materials.index'),
            ]);
        }

        Log::warning("Low Stock Alert triggered for {$material->name}: {$currentStock} {$material->unit} remaining.");
    }
}