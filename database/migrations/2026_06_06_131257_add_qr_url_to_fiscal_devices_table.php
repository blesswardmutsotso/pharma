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
        Schema::table('fiscal_devices', function (Blueprint $table) {
            $table->string('qr_url')->nullable()->after('activation_key');
        });
    }

    public function down(): void
    {
        Schema::table('fiscal_devices', function (Blueprint $table) {
            $table->dropColumn('qr_url');
        });
    }
};
