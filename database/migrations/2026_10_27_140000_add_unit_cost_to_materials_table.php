<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Raw materials now carry an optional per-base-unit cost (e.g. ₱41.67 per motor),
 * so the inventory dashboard can show total stock value at a glance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (!Schema::hasColumn('materials', 'unit_cost')) {
                $table->decimal('unit_cost', 12, 2)->nullable()->after('low_stock_threshold');
            }
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (Schema::hasColumn('materials', 'unit_cost')) {
                $table->dropColumn('unit_cost');
            }
        });
    }
};
