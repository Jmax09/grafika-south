<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('quantity_deducted', 10, 4); // positive for deduction, negative or positive depending on type
            $table->enum('type', ['auto_deduction', 'restock', 'manual_adjustment', 'order_reversal', 'reservation', 'reservation_release'])->default('auto_deduction');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_logs');
    }
};
