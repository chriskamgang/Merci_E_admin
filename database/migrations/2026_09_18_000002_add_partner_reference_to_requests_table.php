<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * External reference (e.g. EstuaireAchats order id) attached by an integration
 * partner when it creates a delivery request; echoed back in partner webhooks.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('requests', 'partner_reference')) {
            return;
        }

        Schema::table('requests', function (Blueprint $table) {
            $table->string('partner_reference', 191)->nullable()->after('user_id');
            $table->index(['user_id', 'partner_reference']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('requests', 'partner_reference')) {
            return;
        }

        Schema::table('requests', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'partner_reference']);
            $table->dropColumn('partner_reference');
        });
    }
};
