<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscal_schedule_settings', function (Blueprint $table) {
            $table->boolean('fiscalization_enabled')->default(true)->after('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('fiscal_schedule_settings', function (Blueprint $table) {
            $table->dropColumn('fiscalization_enabled');
        });
    }
};
