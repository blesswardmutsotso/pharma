<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('product_code');
            $table->string('product_description');
            $table->string('action', 30);       // STOCK_IN|MANUAL_EDIT|STOCK_DELETE|SALE|RETURN|TRANSFER_OUT|TRANSFER_IN|IMPORT
            $table->integer('qty_before');
            $table->integer('qty_after');
            $table->integer('qty_change');      // positive = added, negative = removed
            $table->string('reference_type', 80)->nullable();  // model class name
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_label')->nullable();      // human-readable ref (e.g. TRF-20260609-0001)
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('performed_by_name', 120);
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();      // immutable — no updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_audit_logs');
    }
};
