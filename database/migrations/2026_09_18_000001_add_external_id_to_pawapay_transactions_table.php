<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * KPay/GFSolutions code writes `external_id` (our own reference sent to the gateway) but no
 * migration ever created it, and `transaction_id` was char(36) (UUID) although gateway ids
 * are arbitrary strings. Widen transaction_id and add external_id (idempotent).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pawapay_transactions', function (Blueprint $table) {
            $table->string('transaction_id', 100)->change();
        });

        if (! Schema::hasColumn('pawapay_transactions', 'external_id')) {
            Schema::table('pawapay_transactions', function (Blueprint $table) {
                $table->string('external_id', 100)->nullable()->after('transaction_id');
                $table->index('external_id');
            });
        }

        Schema::table('pawapay_transactions', function (Blueprint $table) {
            $table->index(['driver_id', 'type', 'status'], 'pawapay_tx_driver_type_status_idx');
            $table->index(['status', 'provider'], 'pawapay_tx_status_provider_idx');
        });
    }

    public function down(): void
    {
        Schema::table('pawapay_transactions', function (Blueprint $table) {
            $table->dropIndex('pawapay_tx_driver_type_status_idx');
            $table->dropIndex('pawapay_tx_status_provider_idx');
        });
    }
};
