<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->string('manufacturer')->nullable()->after('generic_name');
            $table->string('registration_number')->nullable()->after('manufacturer');
            $table->string('controlled_substance_schedule')->nullable()->after('registration_number');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['manufacturer', 'registration_number', 'controlled_substance_schedule']);
        });
    }
};
