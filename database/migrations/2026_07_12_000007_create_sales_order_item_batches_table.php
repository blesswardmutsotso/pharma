<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_item_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_item_id')->constrained('sales_order_items')->cascadeOnDelete();
            $table->foreignId('stock_batch_id')->constrained('stock_batches')->cascadeOnDelete();
            $table->unsignedInteger('qty_allocated');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_item_batches');
    }
};
