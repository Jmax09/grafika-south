<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_option_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained('product_options')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->decimal('quantity_used', 10, 4)->default(1.0000);
            $table->timestamps();

            $table->unique(['product_option_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_option_materials');
    }
};
