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
        Schema::create('fiscal_devices', function (Blueprint $table) {
            $table->id();
            $table->string('deviceSerial')->unique();      // Device Serial Number
            $table->string('deviceModelVersion');                 // Device Model
            $table->string('deviceModelName');               // Device Version
            $table->string('taxpayerName');                // Taxpayer Name
            $table->integer('fiscalDay');                  // Fiscal Day
            $table->integer('totalReceiptsSubmitted');    // Total Receipts Submitted
            $table->integer('totalReceiptsPending');      // Total Receipts Pending
            $table->string('deviceID')->unique();         // Device ID
            $table->string('vat_number');                 // VAT Number
            $table->string('activation_key')->nullable()->unique();  // Activation Key
            $table->string('bp_number');                  // BP Number
            $table->timestamps();                         // Timestamps for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscal_devices');
    }
};
