<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscal_receipts', function (Blueprint $table) {
            $table->string('receipt_global_no')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('fiscal_receipts', function (Blueprint $table) {
            $table->string('receipt_global_no')->nullable(false)->change();
        });
    }
};
