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
        Schema::create('hero_settings', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text')->default('Commercial CMYK Printing Solutions & Automated BOM Engine');
            $table->string('title')->default('Custom Print Shop CMYK E-Commerce & Ordering');
            $table->string('highlight_text_1')->nullable()->default('CMYK E-Commerce');
            $table->string('highlight_text_2')->nullable()->default('Ordering');
            $table->text('subtitle')->nullable();
            $table->string('button_text')->default('Browse Catalog & Order Now');
            $table->string('button_url')->default('/products');
            $table->string('secondary_button_text')->nullable()->default('Create Customer Account');
            $table->string('secondary_button_url')->nullable()->default('/register');
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_settings');
    }
};
