<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Supplier catalogs no longer hold free-typed item names: every catalog entry is now a
 * *package* of an existing inventory material (e.g. 1 Box = 5 Liters of ink).
 *
 * This lets a purchase order be counted in the units a supplier actually sells in,
 * while stock is restocked in the shop's own base inventory units.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Add the new shape as nullable so existing rows survive the transition.
        Schema::table('supplier_materials', function (Blueprint $table) {
            if (!Schema::hasColumn('supplier_materials', 'inventory_material_id')) {
                $table->foreignId('inventory_material_id')->nullable()->after('supplier_id');
            }

            if (!Schema::hasColumn('supplier_materials', 'supplier_unit_name')) {
                $table->string('supplier_unit_name')->nullable()->after('inventory_material_id');
            }

            if (!Schema::hasColumn('supplier_materials', 'conversion_qty')) {
                $table->decimal('conversion_qty', 10, 3)->default(1)->after('supplier_unit_name');
            }
        });

        // 2. Carry over whatever inventory links / package labels already existed.
        if (Schema::hasColumn('supplier_materials', 'material_id')) {
            DB::table('supplier_materials')
                ->select('id', 'material_id', 'unit')
                ->whereNotNull('material_id')
                ->whereNull('inventory_material_id')
                ->orderBy('id')
                ->chunk(200, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('supplier_materials')
                            ->where('id', $row->id)
                            ->update([
                                'inventory_material_id' => $row->material_id,
                                'supplier_unit_name' => $row->unit ?: 'Unit',
                            ]);
                    }
                });
        }

        // 3. Entries without an inventory material cannot exist under the new design.
        DB::table('supplier_materials')->whereNull('inventory_material_id')->delete();

        Schema::table('supplier_materials', function (Blueprint $table) {
            $table->string('supplier_unit_name')->nullable(false)->change();
        });

        // 4. Add the mandatory link plus the per-supplier uniqueness *before* dropping the
        //    legacy index: its (supplier_id, ...) prefix is what keeps the supplier FK valid.
        Schema::table('supplier_materials', function (Blueprint $table) {
            $table->foreign('inventory_material_id')
                ->references('id')
                ->on('materials')
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // PO line history survives (purchase_items nullOnDelete).

            $table->unique(['supplier_id', 'inventory_material_id'], 'supplier_materials_supplier_material_unique');
        });

        // 5. Drop the legacy manual-entry columns with their index / foreign key.
        Schema::table('supplier_materials', function (Blueprint $table) {
            if (Schema::hasColumn('supplier_materials', 'material_id')) {
                $table->dropForeign(['material_id']);
                $table->dropColumn('material_id');
            }

            if (Schema::hasColumn('supplier_materials', 'item_name')) {
                $table->dropIndex(['supplier_id', 'item_name']);
            }

            $legacy = array_values(array_filter(
                ['item_name', 'sku', 'unit'],
                fn ($column) => Schema::hasColumn('supplier_materials', $column)
            ));

            if ($legacy) {
                $table->dropColumn($legacy);
            }
        });
    }

    public function down(): void
    {
        $materials = $this->materialNames();

        Schema::table('supplier_materials', function (Blueprint $table) {
            $table->dropUnique('supplier_materials_supplier_material_unique');
            $table->dropForeign(['inventory_material_id']);
        });

        Schema::table('supplier_materials', function (Blueprint $table) {
            $table->string('item_name')->nullable()->after('supplier_id');
            $table->string('sku')->nullable()->after('item_name');
            $table->string('unit')->nullable()->after('sku');
            $table->foreignId('material_id')->nullable()->after('supplier_id');
        });

        // Restore the legacy free-typed shape from the linked material's name.
        DB::table('supplier_materials')
            ->whereNotNull('inventory_material_id')
            ->orderBy('id')
            ->chunk(200, function ($rows) use ($materials) {
                foreach ($rows as $row) {
                    DB::table('supplier_materials')
                        ->where('id', $row->id)
                        ->update([
                            'material_id' => $row->inventory_material_id,
                            'item_name' => $materials[$row->inventory_material_id] ?? 'Unlinked item',
                            'unit' => $row->supplier_unit_name,
                        ]);
                }
            });

        Schema::table('supplier_materials', function (Blueprint $table) {
            $table->dropColumn(['inventory_material_id', 'supplier_unit_name', 'conversion_qty']);
        });

        Schema::table('supplier_materials', function (Blueprint $table) {
            $table->string('item_name')->nullable(false)->change();
            $table->foreign('material_id')->references('id')->on('materials')->nullOnDelete();
            $table->index(['supplier_id', 'item_name']);
        });
    }

    /**
     * Material names keyed by id, used only by the rollback path.
     */
    private function materialNames(): array
    {
        return Schema::hasTable('materials')
            ? DB::table('materials')->pluck('name', 'id')->all()
            : [];
    }
};
