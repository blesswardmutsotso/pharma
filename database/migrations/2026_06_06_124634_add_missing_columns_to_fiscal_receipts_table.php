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
        Schema::table('fiscal_receipts', function (Blueprint $table) {
            $table->unsignedInteger('receipt_counter')->nullable()->after('receipt_currency');
            $table->string('invoice_no')->nullable()->after('receipt_global_no');
            $table->json('buyer_data')->nullable()->after('invoice_no');
            $table->string('receipt_notes')->nullable()->after('buyer_data');
            $table->string('username')->nullable()->after('receipt_notes');
            $table->string('username_surname')->nullable()->after('username');
            $table->timestamp('receipt_date')->nullable()->after('username_surname');
            $table->json('credit_debit_note')->nullable()->after('receipt_date');
            $table->boolean('receipt_lines_tax_inclusive')->default(true)->after('credit_debit_note');
            $table->json('receipt_lines')->nullable()->after('receipt_lines_tax_inclusive');
            $table->json('receipt_taxes')->nullable()->after('receipt_lines');
            $table->json('receipt_payments')->nullable()->after('receipt_taxes');
            $table->json('receipt_device_signature')->nullable()->after('receipt_device_signature_hash');
            $table->string('receipt_print_form')->nullable()->after('receipt_device_signature');
            $table->decimal('receipt_total', 15, 2)->default(0)->after('receipt_print_form');
        });
    }

    public function down(): void
    {
        Schema::table('fiscal_receipts', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_counter', 'invoice_no', 'buyer_data', 'receipt_notes',
                'username', 'username_surname', 'receipt_date', 'credit_debit_note',
                'receipt_lines_tax_inclusive', 'receipt_lines', 'receipt_taxes',
                'receipt_payments', 'receipt_device_signature', 'receipt_print_form',
                'receipt_total',
            ]);
        });
    }
};
