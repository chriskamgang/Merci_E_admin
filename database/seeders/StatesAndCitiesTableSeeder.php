<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesAndCitiesTableSeeder extends Seeder {
	/**
	 * Regions du Cameroun avec leurs principales villes.
	 *
	 * @var array
	 */
	protected $statesWithCities = [
		"Centre" => [
			'Yaounde',
			'Mbalmayo',
			'Obala',
			'Nanga Eboko',
			'Monatele',
			'Akonolinga',
			'Eseka',
		],
		"Littoral" => [
			'Douala',
			'Nkongsamba',
			'Edea',
			'Loum',
			'Manjo',
			'Mbanga',
			'Dizangue',
		],
		"Ouest" => [
			'Bafoussam',
			'Dschang',
			'Mbouda',
			'Bafang',
			'Bandjoun',
			'Foumban',
			'Foumbot',
			'Bangangte',
		],
		"Nord-Ouest" => [
			'Bamenda',
			'Kumbo',
			'Wum',
			'Ndop',
			'Fundong',
			'Mbengwi',
		],
		"Sud-Ouest" => [
			'Buea',
			'Limbe',
			'Kumba',
			'Tiko',
			'Mamfe',
			'Mutengene',
			'Muyuka',
		],
		"Sud" => [
			'Ebolowa',
			'Kribi',
			'Sangmelima',
			'Ambam',
			'Lolodorf',
		],
		"Est" => [
			'Bertoua',
			'Abong-Mbang',
			'Yokadouma',
			'Batouri',
			'Belabo',
		],
		"Adamaoua" => [
			'Ngaoundere',
			'Meiganga',
			'Tibati',
			'Banyo',
			'Tignere',
		],
		"Nord" => [
			'Garoua',
			'Guider',
			'Poli',
			'Figuil',
			'Pitoa',
		],
		"Extreme-Nord" => [
			'Maroua',
			'Kousseri',
			'Mokolo',
			'Mora',
			'Yagoua',
			'Kaele',
		],
	];

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		foreach ($this->statesWithCities as $stateName => $cities) {
			$state = State::firstOrCreate([
				'name' => $stateName,
				'country_id' => DB::table('countries')->where('code', 'CM')->value('id'),
			]);

			foreach ($cities as $cityName) {
				City::firstOrCreate([
					'name' => $cityName,
					'state_id' => $state->id,
				]);
			}
		}
	}
}
