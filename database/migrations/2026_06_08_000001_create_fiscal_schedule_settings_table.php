<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_schedule_settings', function (Blueprint $table) {
            $table->id();
            $table->string('fiscal_mode')->default('auto');      // auto | manual
            $table->string('auto_open_time')->default('05:30');  // HH:MM
            $table->string('auto_close_time')->default('00:10'); // HH:MM (12:10 AM)
            $table->boolean('close_on_last_tx')->default(true);
            $table->boolean('close_reminder')->default(true);
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_schedule_settings');
    }
};
