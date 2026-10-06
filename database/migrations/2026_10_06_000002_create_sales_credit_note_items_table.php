<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_credit_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_credit_note_id')->constrained('sales_credit_notes')->cascadeOnDelete();
            $table->foreignId('sales_invoice_item_id')->nullable()->constrained('sales_invoice_items')->nullOnDelete();
            $table->string('product_code');
            $table->string('product_description');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('qty');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->foreignId('stock_batch_id')->nullable()->constrained('stock_batches')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_credit_note_items');
    }
};
