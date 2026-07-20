<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->string('product_code');
            $table->string('product_description');
            $table->unsignedInteger('qty_requested');
            $table->unsignedInteger('qty_approved')->nullable();  // may differ from requested
            $table->decimal('buying_price',  10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->string('tax_code', 5)->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
    }
};
