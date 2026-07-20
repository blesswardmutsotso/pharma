<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eft_transactions', function (Blueprint $table) {
            // Paynow poll URL returned on initiation
            $table->string('poll_url')->nullable()->after('payment_url');

            // Payment sub-method: card, ecocash, onemoney, telecash
            $table->string('payment_method', 30)->nullable()->after('payment_channel');
        });

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Extend the payment_channel enum to include paynow
        DB::statement("ALTER TABLE eft_transactions MODIFY COLUMN payment_channel ENUM('terminal','online','paynow') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('eft_transactions', function (Blueprint $table) {
            $table->dropColumn(['poll_url', 'payment_method']);
        });

        DB::statement("ALTER TABLE eft_transactions MODIFY COLUMN payment_channel ENUM('terminal','online') NOT NULL");
    }
};
