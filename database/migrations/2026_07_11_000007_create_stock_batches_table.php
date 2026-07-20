<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->string('product_code');
            $table->string('batch_number');
            $table->date('expiry_date');
            $table->unsignedInteger('qty_on_hand')->default(0);
            $table->unsignedInteger('qty_reserved')->default(0);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->string('status')->default('active'); // active, quarantine, expired, depleted
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();

            $table->index(['product_code', 'expiry_date']);
            $table->index(['product_code', 'batch_number']);
            $table->foreign('product_code')->references('product_code')->on('stocks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
