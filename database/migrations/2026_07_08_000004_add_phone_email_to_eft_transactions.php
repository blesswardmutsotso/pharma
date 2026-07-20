<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eft_transactions', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('raw_response');
            $table->string('customer_email')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('eft_transactions', function (Blueprint $table) {
            $table->dropColumn(['phone', 'customer_email']);
        });
    }
};
