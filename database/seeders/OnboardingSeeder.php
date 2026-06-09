<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\Onboarding;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OnboardingSeeder extends Seeder
{
    protected $screens = [
        // User screens
        [
            'sn_o' => 1, 'screen' => 'user', 'order' => 1,
            'onboarding_image' => 'onboard1.jpg', 'active' => 1,
            'en' => ['title' => 'Welcome to Merci E', 'description' => 'The trusted taxi service in Bafoussam and across Cameroon. Safety, comfort and speed.'],
            'fr' => ['title' => 'Bienvenue sur Merci E', 'description' => 'Le service de taxi de confiance a Bafoussam et partout au Cameroun. Securite, confort et rapidite.'],
        ],
        [
            'sn_o' => 2, 'screen' => 'user', 'order' => 2,
            'onboarding_image' => 'onboard2.jpg', 'active' => 1,
            'en' => ['title' => 'Transparent Pricing', 'description' => 'Fair pricing, crystal clear. No hidden fees, no surprises. You see your fare before you ride.'],
            'fr' => ['title' => 'Tarifs transparents', 'description' => 'Des prix justes et clairs. Pas de frais caches, pas de surprises. Vous voyez votre tarif avant de monter.'],
        ],
        [
            'sn_o' => 3, 'screen' => 'user', 'order' => 3,
            'onboarding_image' => 'onboard3.jpg', 'active' => 1,
            'en' => ['title' => 'Easy Booking', 'description' => 'Book a ride in seconds. Just tap, choose your destination, and a driver is on the way.'],
            'fr' => ['title' => 'Reservation facile', 'description' => 'Reservez un trajet en quelques secondes. Appuyez, choisissez votre destination, et un chauffeur arrive.'],
        ],
        [
            'sn_o' => 4, 'screen' => 'user', 'order' => 4,
            'onboarding_image' => 'onboard4.jpg', 'active' => 1,
            'en' => ['title' => '24/7 Support', 'description' => 'Our support team is available around the clock to assist you with any questions or concerns.'],
            'fr' => ['title' => 'Support 24h/24', 'description' => 'Notre equipe est disponible a tout moment pour vous aider pour toute question ou preoccupation.'],
        ],
        // Driver screens
        [
            'sn_o' => 5, 'screen' => 'driver', 'order' => 1,
            'onboarding_image' => 'onboard2.jpg', 'active' => 1,
            'en' => ['title' => 'Welcome to Merci E Driver', 'description' => 'Join the trusted driving platform in Cameroon. Earn money on your own schedule.'],
            'fr' => ['title' => 'Bienvenue sur Merci E Chauffeur', 'description' => 'Rejoignez la plateforme de conduite de confiance au Cameroun. Gagnez de l\'argent a votre rythme.'],
        ],
        [
            'sn_o' => 6, 'screen' => 'driver', 'order' => 2,
            'onboarding_image' => 'onboard4.jpg', 'active' => 1,
            'en' => ['title' => 'Fair Earnings', 'description' => 'Transparent fares with competitive commissions. You keep more of what you earn.'],
            'fr' => ['title' => 'Revenus justes', 'description' => 'Des tarifs transparents avec des commissions competitives. Vous gardez plus de ce que vous gagnez.'],
        ],
        [
            'sn_o' => 7, 'screen' => 'driver', 'order' => 3,
            'onboarding_image' => 'onboard2.jpg', 'active' => 1,
            'en' => ['title' => 'Easy to Start', 'description' => 'Register, upload your documents, and start accepting rides in no time.'],
            'fr' => ['title' => 'Facile a demarrer', 'description' => 'Inscrivez-vous, telechargez vos documents, et commencez a accepter des courses rapidement.'],
        ],
    ];

    public function run()
    {
        foreach ($this->screens as $screen) {
            $onboarding = Onboarding::where('sn_o', $screen['sn_o'])->first();

            $baseData = [
                'sn_o' => $screen['sn_o'],
                'screen' => $screen['screen'],
                'order' => $screen['order'],
                'title' => $screen['en']['title'],
                'onboarding_image' => $screen['onboarding_image'],
                'description' => $screen['en']['description'],
                'active' => $screen['active'],
            ];

            if ($onboarding) {
                $onboarding->update($baseData);
                $onboarding->onboardingTranslationWords()->delete();
            } else {
                $baseData['id'] = Str::uuid();
                $onboarding = Onboarding::create($baseData);
            }

            // Build translation dataset with both EN and FR
            $translations_data = [];

            foreach (['en', 'fr'] as $locale) {
                $translationData = [
                    'title' => $screen[$locale]['title'],
                    'description' => $screen[$locale]['description'],
                    'locale' => $locale,
                    'onboarding_screen_id' => $onboarding->id,
                ];

                $onboarding->onboardingTranslationWords()->create($translationData);

                $translations_data[$locale] = (object) [
                    'locale' => $locale,
                    'title' => $screen[$locale]['title'],
                    'description' => $screen[$locale]['description'],
                ];
            }

            $onboarding->translation_dataset = json_encode($translations_data);
            $onboarding->save();
        }
    }
}
