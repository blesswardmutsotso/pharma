<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('buying_price',  15, 5)->change();
            $table->decimal('selling_price', 15, 5)->change();
        });

        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->decimal('buying_price',  15, 5)->default(0)->change();
            $table->decimal('selling_price', 15, 5)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('buying_price',  10, 2)->change();
            $table->decimal('selling_price', 10, 2)->change();
        });

        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->decimal('buying_price',  10, 2)->default(0)->change();
            $table->decimal('selling_price', 10, 2)->default(0)->change();
        });
    }
};
