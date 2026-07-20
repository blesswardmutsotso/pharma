<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Least-privilege default — self-registration/OAuth sign-up must never
            // silently grant an elevated role. Admins are promoted explicitly.
            $table->string('role')->default('sales')->after('user_type');
            $table->timestamp('password_changed_at')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'password_changed_at']);
        });
    }
};
