<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fiscalization (ZIMRA FDMS) and EFT/card-payment integration has been
     * removed from the application. These tables backed that removed layer.
     *
     * FK checks are disabled for the duration of the drops — on real
     * foreign-key-enforcing engines (MySQL/InnoDB), eft_transactions has a
     * FK to fiscal_receipts, so dropping in any fixed order risks failing;
     * every one of these tables is being removed anyway.
     */
    public function up(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        if (!$isSqlite) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        Schema::dropIfExists('submitted_receipts');
        Schema::dropIfExists('fiscal_receipts');
        Schema::dropIfExists('fiscal_days');
        Schema::dropIfExists('fiscal_schedule_settings');
        Schema::dropIfExists('fiscal_devices');
        Schema::dropIfExists('eft_transactions');
        Schema::dropIfExists('eft_terminals');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('debit_notes');
        Schema::dropIfExists('invoice_details');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('printers');
        Schema::dropIfExists('bank_details');

        if (!$isSqlite) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function down(): void
    {
        // Intentionally irreversible — the fiscal/EFT feature set was removed.
    }
};
