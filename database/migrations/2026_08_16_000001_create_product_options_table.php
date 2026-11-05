<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('option_name'); // e.g. Size, Color, Paper Type, Finish
            $table->string('option_value'); // e.g. Small, Medium, Large, Black, Glossy
            $table->decimal('price_modifier', 10, 2)->default(0.00); // e.g. +50.00
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_options');
    }
};
