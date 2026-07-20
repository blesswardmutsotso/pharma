<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('fiscal_receipts', function (Blueprint $table) {
        // Only add the column if it doesn't already exist
        if (!Schema::hasColumn('fiscal_receipts', 'user_id')) {
            $table->foreignId('user_id')
                ->nullable() // Optional: allow null for existing rows
                ->constrained()
                ->onDelete('cascade');
        }
    });
}


    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('fiscal_receipts', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
    });
}
};
