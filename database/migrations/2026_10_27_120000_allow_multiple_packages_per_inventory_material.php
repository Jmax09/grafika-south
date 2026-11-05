<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A supplier frequently ships the same inventory item in more than one package size —
 * ink in a Box *and* in a Gallon. The old key allowed only one package per material,
 * which forced the Add Catalog Item form to hide materials already catalogued.
 *
 * Uniqueness now covers (supplier, material, package name): the same package name for
 * the same item is still a duplicate, but additional package sizes are welcome.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Create the replacement key first: MySQL refuses to drop an index that a
        // foreign key still relies on for its column prefix.
        Schema::table('supplier_materials', function (Blueprint $table) {
            $table->unique(
                ['supplier_id', 'inventory_material_id', 'supplier_unit_name'],
                'supplier_materials_supplier_material_package_unique'
            );
        });

        Schema::table('supplier_materials', function (Blueprint $table) {
            $table->dropUnique('supplier_materials_supplier_material_unique');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_materials', function (Blueprint $table) {
            $table->unique(
                ['supplier_id', 'inventory_material_id'],
                'supplier_materials_supplier_material_unique'
            );
        });

        Schema::table('supplier_materials', function (Blueprint $table) {
            $table->dropUnique('supplier_materials_supplier_material_package_unique');
        });
    }
};
