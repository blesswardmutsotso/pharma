<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('license_number')->nullable()->after('tin');
            $table->date('license_expiry_date')->nullable()->after('license_number');
            $table->string('accreditation_body')->nullable()->after('license_expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['license_number', 'license_expiry_date', 'accreditation_body']);
        });
    }
};
