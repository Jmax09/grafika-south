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
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'custom_production_time_minutes')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('custom_production_time_minutes')->nullable()->after('is_active');
            });
        }

        if (Schema::hasTable('materials') && !Schema::hasColumn('materials', 'production_time_minutes')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->unsignedInteger('production_time_minutes')->nullable()->after('low_stock_threshold');
            });
        }

        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'estimated_production_minutes')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedInteger('estimated_production_minutes')->nullable()->after('fulfillment_type');
            });
        }

        if (Schema::hasTable('order_items') && !Schema::hasColumn('order_items', 'production_time_minutes')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->unsignedInteger('production_time_minutes')->nullable()->after('subtotal');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'custom_production_time_minutes')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('custom_production_time_minutes');
            });
        }

        if (Schema::hasTable('materials') && Schema::hasColumn('materials', 'production_time_minutes')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropColumn('production_time_minutes');
            });
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'estimated_production_minutes')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('estimated_production_minutes');
            });
        }

        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'production_time_minutes')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('production_time_minutes');
            });
        }
    }
};

