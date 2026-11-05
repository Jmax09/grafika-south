<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add stock_before and stock_after audit snapshot columns to material_logs.
     */
    public function up(): void
    {
        Schema::table('material_logs', function (Blueprint $table) {
            $table->decimal('stock_before', 10, 4)->nullable()->after('quantity_deducted');
            $table->decimal('stock_after', 10, 4)->nullable()->after('stock_before');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_logs', function (Blueprint $table) {
            $table->dropColumn(['stock_before', 'stock_after']);
        });
    }
};
