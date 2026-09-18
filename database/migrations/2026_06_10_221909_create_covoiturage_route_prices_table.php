<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('covoiturage_route_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('origin_name');
            $table->double('origin_lat', 15, 8);
            $table->double('origin_lng', 15, 8);
            $table->string('destination_name');
            $table->double('destination_lat', 15, 8);
            $table->double('destination_lng', 15, 8);
            $table->double('radius_km')->default(30);
            $table->double('fixed_price');
            $table->boolean('active')->default(true);
            $table->boolean('bidirectional')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('covoiturage_route_prices');
    }
};
