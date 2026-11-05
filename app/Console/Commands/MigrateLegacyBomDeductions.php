<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\MaterialLog;
use App\Services\InventoryAutomationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateLegacyBomDeductions extends Command
{
    /**
     * One-time cleanup for the reservation system rollout.
     *
     * Finds orders that were deducted at checkout (legacy behavior) but are
     * still pending/design_review — restores the deducted physical stock and
     * re-books it as a reservation so it is only deducted upon approval.
     */
    protected $signature = 'bom:migrate-legacy-deductions {--dry-run : Show what would change without modifying data}';

    protected $description = 'Convert legacy auto_deductions of still-pending orders into reservations (deduct only on approval)';

    public function handle(InventoryAutomationService $inventoryService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Orders that still have auto_deduction logs but are NOT yet in a
        // post-deduction status → these were deducted prematurely at checkout.
        $postDeductionStatuses = ['approved', 'printing', 'ready', 'shipped', 'completed', 'cancelled'];

        $orderIds = MaterialLog::query()
            ->where('type', 'auto_deduction')
            ->whereNotNull('order_id')
            ->distinct()
            ->pluck('order_id');

        $orders = Order::whereIn('id', $orderIds)
            ->whereNotIn('status', $postDeductionStatuses)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('✅ No legacy pending orders with premature deductions found. Nothing to do.');

            return self::SUCCESS;
        }

        $this->info("Found {$orders->count()} pending order(s) with premature BOM deductions:");

        foreach ($orders as $order) {
            $this->line("  • Order #{$order->order_number} (status: {$order->status})");
        }

        if ($dryRun) {
            $this->comment('Dry run — no changes were made.');

            return self::SUCCESS;
        }

        foreach ($orders as $order) {
            try {
                DB::transaction(function () use ($order, $inventoryService) {
                    // 1. Reverse the premature physical deduction (restores stock)
                    $reversed = $inventoryService->reverseOrderDeduction($order);

                    // 2. Re-book the same quantities as a reservation so the
                    //    stock is protected until the order gets approved.
                    $reserved = $inventoryService->reserveOrderMaterials($order);

                    $this->info("✅ Order #{$order->order_number}: reversed " . count($reversed) .
                        ' deduction(s) and re-booked ' . count($reserved) . ' reservation(s).');
                });

                Log::info("Legacy BOM deduction migrated to reservation for Order #{$order->order_number}.");
            } catch (\Throwable $e) {
                $this->error("❌ Order #{$order->order_number}: {$e->getMessage()}");
                Log::error("Failed migrating legacy BOM deduction for Order #{$order->order_number}: " . $e->getMessage());
            }
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}