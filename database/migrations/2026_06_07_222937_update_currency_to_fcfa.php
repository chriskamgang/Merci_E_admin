<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('name', 'currency_code')
            ->update(['value' => 'XAF']);

        DB::table('settings')
            ->where('name', 'currency_symbol')
            ->update(['value' => 'FCFA']);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('name', 'currency_code')
            ->update(['value' => 'INR']);

        DB::table('settings')
            ->where('name', 'currency_symbol')
            ->update(['value' => '₹']);
    }
};
