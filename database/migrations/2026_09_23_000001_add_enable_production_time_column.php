<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the enable_production_time toggle flag to products.
     * Defaults to true so existing/customized products keep their current behavior.
     */
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'enable_production_time')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('enable_production_time')->default(true)->after('custom_production_time_minutes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'enable_production_time')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('enable_production_time');
            });
        }
    }
};