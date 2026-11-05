<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_used', 10, 4)->default(1.0000); // e.g. 5.0 ml ink, 1.0 sheet paper
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_materials');
    }
};
