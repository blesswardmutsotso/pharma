<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscal_receipts', function (Blueprint $table) {
            $table->unsignedInteger('fiscal_day_no')->nullable()->after('receipt_counter')->index();
        });

        // Best-effort backfill: assign each receipt the fiscal day whose window
        // contains receipt_date.  Imperfect for overlapping days but covers the
        // common case and leaves NULL only where truly ambiguous.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                UPDATE fiscal_receipts r
                JOIN (
                    SELECT
                        fd.fiscalDayNo,
                        fd.fiscalDayOpened AS day_start,
                        COALESCE(
                            (SELECT MIN(fd2.fiscalDayOpened)
                             FROM fiscal_days fd2
                             WHERE fd2.fiscalDayNo > fd.fiscalDayNo),
                            NOW()
                        ) AS day_end
                    FROM fiscal_days fd
                ) day_map
                    ON r.receipt_date >= day_map.day_start
                   AND r.receipt_date <  day_map.day_end
                SET r.fiscal_day_no = day_map.fiscalDayNo
                WHERE r.fiscal_day_no IS NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('fiscal_receipts', function (Blueprint $table) {
            $table->dropColumn('fiscal_day_no');
        });
    }
};
