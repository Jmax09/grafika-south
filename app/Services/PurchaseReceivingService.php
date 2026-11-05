<?php

namespace App\Services;

use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseReceivingService
{
    /**
     * Mark a purchase order as received and restock every ordered catalog package into
     * the shop's inventory, converting supplier packages to base inventory units
     * (ordered quantity × conversion_qty).
     *
     * The operation is idempotent: receiving an already-received PO does not
     * duplicate stock movements.
     *
     * @return array{already_received: bool, restocked: array<int, array<string, mixed>>}
     */
    public function receive(Purchase $purchase): array
    {
        return DB::transaction(function () use ($purchase) {
            // Lock the PO row so two concurrent "receive" clicks cannot race.
            $purchase = Purchase::whereKey($purchase->id)->lockForUpdate()->firstOrFail();

            if ($purchase->status === Purchase::STATUS_RECEIVED) {
                return ['already_received' => true, 'restocked' => []];
            }

            $purchase->load('items.supplierMaterial.inventoryMaterial');

            $restocked = [];

            foreach ($purchase->items as $item) {
                $catalogItem = $item->supplierMaterial;
                $linkedMaterial = $catalogItem?->inventoryMaterial;

                if (!$linkedMaterial) {
                    continue; // Catalog entry's inventory material was removed → skip.
                }

                $packages = (int) $item->quantity;

                if ($packages <= 0) {
                    continue;
                }

                // Ordered in supplier packages, stocked in base inventory units.
                $quantity = $catalogItem->toBaseUnits($packages);

                $material = Material::whereKey($linkedMaterial->id)->lockForUpdate()->first();

                if (!$material) {
                    continue;
                }

                $stockBefore = (float) $material->current_stock;
                $stockAfter = round($stockBefore + $quantity, 3);

                $material->update(['current_stock' => $stockAfter]);

                $baseUnit = $material->unit_label;

                MaterialLog::create([
                    'material_id' => $material->id,
                    'quantity_deducted' => -$quantity, // negative = stock addition (restock)
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'type' => 'restock',
                    'notes' => "PO receipt {$purchase->reference_no}: +{$packages} "
                        . $catalogItem->supplier_unit_name . " of {$item->item_name}"
                        . " ({$catalogItem->package_label}) = +{$quantity} {$baseUnit}",
                ]);

                $restocked[] = [
                    'material' => $material->name,
                    'unit' => $baseUnit,
                    'packages' => $packages,
                    'package_unit' => $catalogItem->supplier_unit_name,
                    'quantity' => $quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                ];
            }

            $purchase->update(['status' => Purchase::STATUS_RECEIVED]);

            Log::info("PO {$purchase->reference_no} received; " . count($restocked) . ' inventory material(s) restocked.');

            return ['already_received' => false, 'restocked' => $restocked];
        });
    }
}
