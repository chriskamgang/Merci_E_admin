<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\Setting;
use App\Models\ThirdPartySetting;

/**
 * Merci E — Configuration spécifique au Cameroun.
 * Ce seeder s'assure que toutes les données sont correctes pour le déploiement.
 */
class MercieSeeder extends Seeder
{
    public function run()
    {
        // ── 1. Pays : Cameroun uniquement ──────────────────────────────────────
        // Désactiver tous les autres pays
        Country::where('code', '!=', 'CM')->update(['active' => false]);

        // S'assurer que le Cameroun existe et est actif
        Country::updateOrCreate(
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

        // ── 2. Paramètres par défaut ───────────────────────────────────────────
        $defaults = [
            'default_country_code_for_mobile_app'  => 'CM',
            'default_currency_code_for_mobile_app' => 'XAF',
            'enable_single_landing_page'           => '1',
        ];

        foreach ($defaults as $name => $value) {
            Setting::where('name', $name)->update(['value' => $value]);
        }

        // ── 3. SMS Gateway : Nexah activé, tous les autres désactivés ─────────
        $smsGateways = [
            'enable_firebase_otp',
            'enable_twilio',
            'enable_sms_ala',
            'enable_msg91',
            'enable_sparrow',
            'enable_sms_india_hub',
            'enable_kudi_sms_api_key',
            'enable_infobip',
        ];

        ThirdPartySetting::where('module', 'sms')
            ->whereIn('name', $smsGateways)
            ->update(['value' => '0']);

        ThirdPartySetting::updateOrCreate(
            ['module' => 'sms', 'name' => 'enable_nexah'],
            ['value' => '1']
        );

        ThirdPartySetting::updateOrCreate(
            ['module' => 'sms', 'name' => 'nexah_sender_id'],
            ['value' => 'MerciE']
        );
    }
}
