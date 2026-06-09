<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountriesTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Merci E — Cameroun uniquement
        Country::firstOrCreate(
            ['code' => 'CM'],
            [
                'name'            => 'Cameroon',
                'dial_code'       => '+237',
                'flag'            => url('image/country/flags/CM.png'),
                'currency_name'   => 'Central African CFA franc',
                'currency_code'   => 'XAF',
                'currency_symbol' => 'Fr',
                'dial_min_length' => 9,
                'dial_max_length' => 9,
                'active'          => true,
            ]
        );
    }
}
