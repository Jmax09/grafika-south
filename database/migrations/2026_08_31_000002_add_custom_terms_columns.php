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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'requires_custom_terms')) {
                $table->boolean('requires_custom_terms')->default(false)->after('requires_design_file');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'custom_terms_agreed')) {
                $table->boolean('custom_terms_agreed')->default(false)->after('is_custom_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'requires_custom_terms')) {
                $table->dropColumn('requires_custom_terms');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'custom_terms_agreed')) {
                $table->dropColumn('custom_terms_agreed');
            }
        });
    }
};
