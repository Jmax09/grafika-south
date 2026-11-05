<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('cancellation_status')->default('none')->after('status');
            $table->text('cancellation_reason')->nullable()->after('cancellation_status');
            $table->timestamp('cancellation_requested_at')->nullable()->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_requested_at');
            $table->text('cancellation_admin_notes')->nullable()->after('cancelled_at');
            $table->string('refund_status')->default('none')->after('payment_status');
            $table->string('refund_method')->nullable()->after('refund_status');
            $table->string('refund_reference')->nullable()->after('refund_method');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('refund_reference');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'cancellation_status',
                'cancellation_reason',
                'cancellation_requested_at',
                'cancelled_at',
                'cancellation_admin_notes',
                'refund_status',
                'refund_method',
                'refund_reference',
                'refund_amount',
                'refunded_at',
            ]);
        });
    }
};