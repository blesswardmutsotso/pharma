<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->unsignedInteger('print_count')->default(0)->after('notes');
        });
        Schema::table('goods_received_notes', function (Blueprint $table) {
            $table->unsignedInteger('print_count')->default(0)->after('notes');
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->unsignedInteger('print_count')->default(0)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', fn (Blueprint $table) => $table->dropColumn('print_count'));
        Schema::table('goods_received_notes', fn (Blueprint $table) => $table->dropColumn('print_count'));
        Schema::table('quotations', fn (Blueprint $table) => $table->dropColumn('print_count'));
    }
};
