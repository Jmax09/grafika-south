<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds a shared "position" column to product_images and product_videos so
     * the admin drag-to-reorder produces ONE unified order across both types
     * (previously the grid was re-grouped by type on every load, so the saved
     * order appeared to "revert" after refresh).
     */
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('sort_order');
        });

        Schema::table('product_videos', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('sort_order');
        });

        // Seed position from the existing per-type sort_order for continuity.
        DB::table('product_images')->update(['position' => DB::raw('sort_order')]);
        DB::table('product_videos')->update(['position' => DB::raw('sort_order')]);
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('product_videos', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
