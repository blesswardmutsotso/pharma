<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eft_terminals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name', 60);              // e.g. "Main Till", "Till 2"
            $table->string('terminal_model', 50)->nullable(); // PAX A920, Verifone VX520, etc.
            $table->string('terminal_ip', 45);
            $table->unsignedSmallInteger('terminal_port')->default(10009);
            $table->boolean('is_active')->default(true);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('eft_transactions', function (Blueprint $table) {
            $table->foreignId('eft_terminal_id')->nullable()->after('payment_channel')
                ->constrained('eft_terminals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('eft_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('eft_terminal_id');
        });
        Schema::dropIfExists('eft_terminals');
    }
};
