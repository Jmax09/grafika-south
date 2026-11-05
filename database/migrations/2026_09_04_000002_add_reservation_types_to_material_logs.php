<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Expand the material_logs.type enum to include 'reservation' and 'reservation_release'
     * for the BOM stock reservation system.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE material_logs MODIFY COLUMN type ENUM('auto_deduction', 'restock', 'manual_adjustment', 'order_reversal', 'reservation', 'reservation_release') DEFAULT 'auto_deduction'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE material_logs MODIFY COLUMN type ENUM('auto_deduction', 'restock', 'manual_adjustment', 'order_reversal') DEFAULT 'auto_deduction'");
        }
    }
};