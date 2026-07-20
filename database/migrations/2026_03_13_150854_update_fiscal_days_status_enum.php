<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (\DB::getDriverName() === 'sqlite') {
            return;
        }

        \DB::statement("ALTER TABLE fiscal_days MODIFY COLUMN status ENUM(
            'FiscalDayOpened',
            'FiscalDayClosed',
            'FiscalDayCloseFailed',
            'FiscalDayOpenFailed'
        ) NOT NULL DEFAULT 'FiscalDayOpened'");
    }

    public function down(): void
    {
        if (\DB::getDriverName() === 'sqlite') {
            return;
        }

        \DB::statement("ALTER TABLE fiscal_days MODIFY COLUMN status ENUM(
            'FiscalDayOpened',
            'FiscalDayClosed'
        ) NOT NULL DEFAULT 'FiscalDayOpened'");
    }
};