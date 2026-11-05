<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');

            // Optional link to the shop's own inventory material. When present,
            // receiving a PO can automatically restock that Material record.
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();

            $table->string('item_name');            // e.g. Matte Sticker Paper, Pigment Ink
            $table->string('sku')->nullable();
            $table->string('unit')->nullable();     // e.g. sheets, ml, rolls
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['supplier_id', 'item_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_materials');
    }
};
