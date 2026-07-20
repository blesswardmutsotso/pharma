<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no', 30)->unique();          // TRF-20260609-0001
            $table->string('transfer_type', 20);                  // OUTGOING | INCOMING
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('status', 20)->default('DRAFT');       // DRAFT|PENDING|APPROVED|REJECTED|CANCELLED
            $table->text('notes')->nullable();
            $table->string('reference_doc', 100)->nullable();     // e.g. waybill / delivery note number
            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('total_qty')->default(0);
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reject_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
