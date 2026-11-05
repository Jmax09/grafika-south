<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds 'cancelled' to the orders.payment_status enum so a cancelled order can
     * close out its payment state instead of being left on "unpaid".
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('unpaid', 'partially_paid', 'paid', 'refunded', 'cancelled') NOT NULL DEFAULT 'unpaid'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * Cancelled payment states fall back to "unpaid" before the enum is narrowed,
     * so MySQL never has to coerce invalid values.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE orders SET payment_status = 'unpaid' WHERE payment_status = 'cancelled'");
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('unpaid', 'partially_paid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid'");
        }
    }
};
