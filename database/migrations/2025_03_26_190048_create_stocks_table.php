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
        Schema::create('stocks', function (Blueprint $table) {
          $table->id();
            $table->string('product_code')->unique();
            $table->string('product_description');
            $table->decimal('buying_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->integer('quantity');
            $table->string('tax_code');
            $table->unsignedTinyInteger('tax_id');
            $table->decimal('tax_percentage', 5, 2)->nullable(); 
            $table->decimal('tax_amount', 10, 2)->nullable();
            $table->decimal('sales_amount_with_tax', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
