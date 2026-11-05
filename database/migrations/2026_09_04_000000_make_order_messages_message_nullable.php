<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make 'message' nullable on order_messages so staff can send
     * attachment-only (image) messages in the order support chat.
     */
    public function up(): void
    {
        Schema::table('order_messages', function (Blueprint $table) {
            $table->text('message')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_messages', function (Blueprint $table) {
            $table->text('message')->change();
        });
    }
};