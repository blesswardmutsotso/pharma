<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_credit_notes', function (Blueprint $table) {
            $table->foreignId('sales_order_id')->nullable()->after('sales_invoice_id')->constrained('sales_orders')->nullOnDelete();
            $table->string('type')->default('adjustment')->after('reason'); // adjustment, return
        });
    }

    public function down(): void
    {
        Schema::table('sales_credit_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sales_order_id');
            $table->dropColumn('type');
        });
    }
};
