<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('submitted_receipts', function (Blueprint $table) {
            $table->id('receiptGlobalNo');
            $table->integer('receiptCounter');
            $table->Integer('FiscalDayNo');
            $table->foreign('FiscalDayNo')->references('fiscalDayNo')->on('fiscal_days')->onDelete('cascade');
            $table->integer('InvoiceNo')->unique();
            $table->integer('receiptID')->unique();
            $table->enum('receiptType', ['FISCALINVOICE', 'CREDITNOTE', 'DEBITNOTE'])->default('FISCALINVOICE');
            $table->date('receiptDate');
            $table->time('receiptTime');
            $table->decimal('receiptTotal', 10, 2);
            $table->string('taxCode')->nullable();
            $table->decimal('taxPercent', 5, 2)->nullable();
            $table->decimal('taxAmount', 10, 2)->nullable();
            $table->decimal('SalesAmountwithTax', 10, 2)->nullable();
            $table->text('receiptHash');
            $table->json('receiptJsonbody');
            $table->string('StatuestoFDMS')->default('pending');
            $table->string('qrurl')->nullable();
            $table->text('receiptServerSignature')->nullable();
            $table->json('submitReceiptServerResponseJSON')->nullable();
            $table->decimal('Total15VAT', 10, 2)->default(0);
            $table->decimal('TotalNonVAT', 10, 2)->default(0);
            $table->decimal('TotalExempt', 10, 2)->default(0);
            $table->decimal('TotalWT', 10, 2)->default(0);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('submitted_receipts');
    }
};
