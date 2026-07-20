<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->string('category')->nullable()->after('product_description');
            $table->string('generic_name')->nullable()->after('category');
            $table->string('dosage_form')->nullable()->after('generic_name');
            $table->string('strength')->nullable()->after('dosage_form');
            $table->string('pack_size')->nullable()->after('strength');
            $table->string('unit_of_measure')->nullable()->after('pack_size');
            $table->string('storage_condition')->nullable()->after('unit_of_measure');
            $table->unsignedInteger('reorder_point')->default(0)->after('storage_condition');
            $table->unsignedInteger('reorder_qty')->default(0)->after('reorder_point');
            $table->boolean('requires_batch_tracking')->default(true)->after('reorder_qty');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'generic_name',
                'dosage_form',
                'strength',
                'pack_size',
                'unit_of_measure',
                'storage_condition',
                'reorder_point',
                'reorder_qty',
                'requires_batch_tracking',
            ]);
        });
    }
};
