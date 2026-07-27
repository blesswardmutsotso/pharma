<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('mcaz_licensed_person')->nullable()->after('accreditation_body');
            $table->string('wholesale_license_number')->nullable()->after('mcaz_licensed_person');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['mcaz_licensed_person', 'wholesale_license_number']);
        });
    }
};
