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
        Schema::create('fiscal_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_type');
            $table->string('receipt_currency');
            $table->string('receipt_global_no')->unique();
            $table->string('buyer_name');
            $table->string('vat_number')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('email')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('invoice_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscal_receipts');
    }
};
