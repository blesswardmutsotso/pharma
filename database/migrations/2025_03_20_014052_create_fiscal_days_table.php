<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('fiscal_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deviceID'); 
            $table->integer('fiscalDayNo')->unique();
            $table->timestamp('fiscalDayOpened');
            $table->enum('status', ['FiscalDayClosed', 'FiscalDayOpened'])->nullable();
            $table->timestamps();

            
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('fiscal_days');
    }
};
