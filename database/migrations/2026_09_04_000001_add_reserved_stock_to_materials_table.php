<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add reserved_stock to materials for the BOM reservation system.
     *
     * Pending orders reserve stock (reserved_stock) without touching
     * physical stock (current_stock). Effective available stock is
     * current_stock - reserved_stock. On approval, the reservation is
     * converted into an actual deduction.
     */
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('reserved_stock', 12, 2)->default(0)->after('current_stock');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('reserved_stock');
        });
    }
};