<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('product_code')
                ->constrained('branches')->nullOnDelete();
        });

        Schema::table('goods_received_notes', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('supplier_id')
                ->constrained('branches')->nullOnDelete();
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('client_id')
                ->constrained('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('goods_received_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
