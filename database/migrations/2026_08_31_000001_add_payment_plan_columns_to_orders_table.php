<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_plan')) {
                $table->string('payment_plan')->default('full')->after('payment_status'); // 'full' or 'downpayment'
            }
            if (!Schema::hasColumn('orders', 'downpayment_amount')) {
                $table->decimal('downpayment_amount', 10, 2)->default(0.00)->after('payment_plan');
            }
            if (!Schema::hasColumn('orders', 'remaining_balance')) {
                $table->decimal('remaining_balance', 10, 2)->default(0.00)->after('downpayment_amount');
            }
            if (!Schema::hasColumn('orders', 'is_custom_order')) {
                $table->boolean('is_custom_order')->default(false)->after('remaining_balance');
            }
        });

        // Modify payment_status enum on MySQL to include 'partially_paid'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('unpaid', 'partially_paid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid'");
        }

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payment_plan')) {
                $table->string('payment_plan')->default('full')->after('amount'); // 'full' or 'downpayment'
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_plan')) {
                $table->dropColumn('payment_plan');
            }
            if (Schema::hasColumn('orders', 'downpayment_amount')) {
                $table->dropColumn('downpayment_amount');
            }
            if (Schema::hasColumn('orders', 'remaining_balance')) {
                $table->dropColumn('remaining_balance');
            }
            if (Schema::hasColumn('orders', 'is_custom_order')) {
                $table->dropColumn('is_custom_order');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('unpaid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid'");
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_plan')) {
                $table->dropColumn('payment_plan');
            }
        });
    }
};
