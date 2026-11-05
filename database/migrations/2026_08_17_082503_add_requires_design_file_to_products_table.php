<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'requires_design_file')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('requires_design_file')->default(false)->after('is_featured');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'requires_design_file')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('requires_design_file');
            });
        }
    }
};
