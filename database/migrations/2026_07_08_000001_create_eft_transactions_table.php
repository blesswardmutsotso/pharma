<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eft_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_ref', 40)->unique();     // EFT-YYYYMMDD-XXXXXXXX
            $table->foreignId('fiscal_receipt_id')->nullable()->constrained('fiscal_receipts')->nullOnDelete();
            $table->foreignId('initiated_by')->constrained('users');
            $table->decimal('amount', 15, 2);
            $table->char('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'approved', 'declined', 'cancelled', 'timeout', 'error'])->default('pending');
            $table->enum('payment_channel', ['terminal', 'online']);

            // Terminal-specific
            $table->string('terminal_ip',   45)->nullable();
            $table->unsignedSmallInteger('terminal_port')->nullable();

            // Bank / terminal response
            $table->string('authorization_code', 20)->nullable();
            $table->string('response_code',      10)->nullable();
            $table->string('response_message',  255)->nullable();
            $table->string('card_scheme',        30)->nullable();  // Visa, Mastercard, ZimSwitch Debit
            $table->string('masked_pan',         20)->nullable();  // ****1234
            $table->string('rrn',                30)->nullable();  // Retrieval Reference Number
            $table->string('stan',               20)->nullable();  // System Trace Audit Number
            $table->string('bank_ref',           60)->nullable();  // Bank's own reference
            $table->string('host_txn_id',        60)->nullable();  // Terminal host transaction ID

            // Online payment
            $table->string('payment_url')->nullable();
            $table->timestamp('payment_url_expires_at')->nullable();

            // Raw packets (stored as base64 for binary safety)
            $table->text('raw_request')->nullable();
            $table->text('raw_response')->nullable();

            $table->timestamp('initiated_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('payment_channel');
            $table->index('fiscal_receipt_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eft_transactions');
    }
};
