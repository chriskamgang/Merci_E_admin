<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MerciELandingSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // === SINGLE_LANDING_PAGE ===
        DB::table('single_landing_page')->truncate();
        DB::table('single_landing_page')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_para' => 'Your ride in Bafoussam, fast and safe. Merci E — the smart taxi that takes you everywhere in the city.',
  'hero_img_1' => 'hero3.png',
  'hero_img_2' => 'herosec4.png',
  'hero_img_3' => 'mockuper.png',
  'hero_img_4' => 'hero4.png',
  'hero_img_5' => 'hero2.png',
  'adv_title' => 'Why Choose Merci E?',
  'adv_para' => 'A modern app designed for Bafoussam residents. Book, track, and arrive with confidence.',
  'adv_box1_title' => 'Taxi & Ride',
  'adv_box1_para' => 'Book a taxi in seconds. Our certified drivers take you anywhere in Bafoussam and surroundings safely.',
  'adv_box1_img' => 'vehicletype.mp4',
  'adv_box2_title' => 'Express Delivery',
  'adv_box2_para' => 'Send and receive packages quickly. Our delivery service covers the entire city for your daily errands.',
  'adv_box2_img' => 'live-tracking.mp4',
  'adv_box3_title' => 'Carpooling',
  'adv_box3_para' => 'Share rides and reduce costs. Merci E carpooling connects drivers and passengers on the same routes.',
  'adv_box3_img' => 'payment.mp4',
  'adv_box4_title' => 'Vehicle Rental',
  'adv_box4_para' => 'Rent a vehicle with or without a driver for your occasional needs: events, business trips or travel.',
  'adv_box4_img' => 'bidding.mp4',
  'adv_box5_title' => 'Mobile Money Payment',
  'adv_box5_para' => 'Pay easily via MTN Mobile Money or Orange Money. No need for cash, everything is done from your phone.',
  'adv_box5_img' => 'language.mp4',
  'app_works_title' => 'How Does Merci E Work?',
  'app_works_para' => 'Whether you are a passenger or a driver, our app is designed to simplify your life.',
  'app_works_user_title' => 'Passenger',
  'app_works_driver_title' => 'Driver',
  'user_box1_title' => 'Download the App',
  'user_box1_para' => 'Install Merci E on your Android or iPhone. Quick and free registration.',
  'user_box2_title' => 'Choose Your Destination',
  'user_box2_para' => 'Enter your pickup and destination anywhere in Bafoussam.',
  'user_box3_title' => 'Find a Driver',
  'user_box3_para' => 'A driver near you accepts your request in a few seconds.',
  'user_box4_title' => 'Ride and Pay',
  'user_box4_para' => 'Enjoy your ride and pay easily upon arrival via mobile money or cash.',
  'driver_box1_title' => 'Become a Partner',
  'driver_box1_para' => 'Sign up as a Merci E driver and start earning at your own pace.',
  'driver_box2_title' => 'Receive Trips',
  'driver_box2_para' => 'Requests come directly to your app. Accept based on your availability.',
  'driver_box3_title' => 'Navigate Easily',
  'driver_box3_para' => 'Follow the guided route to the passenger and destination in real time.',
  'driver_box4_title' => 'Get Paid',
  'driver_box4_para' => 'Receive payments directly to your mobile money account every week.',
  'app_user_img' => 'user-img.png',
  'app_driver_img' => 'driverimg1.png',
  'why_choose_title' => 'Why Merci E is the Best Choice?',
  'why_choose_box1_title' => 'All your rides in one tap',
  'why_choose_box1_para' => 'Taxi, delivery, carpooling and rental — all your transport needs in a single app.',
  'why_choose_box2_title' => 'Fair and transparent pricing',
  'why_choose_box2_para' => 'Prices are calculated upfront. No surprises, no negotiation. You know how much you pay before booking.',
  'why_choose_box3_title' => 'Verified drivers',
  'why_choose_box3_para' => 'All our drivers are trained and verified. Track your ride in real time and share your position with loved ones.',
  'why_choose_box4_title' => 'Available 24/7',
  'why_choose_box4_para' => 'Merci E is at your service day and night, 7 days a week. Need a taxi at 3am? We are here.',
  'why_choose_img' => 'useimagefinal.png',
  'about_title_1' => 'About',
  'about_title_2' => 'Merci E',
  'about_img' => 'aboutus1.png',
  'about_para' => 'Merci E is a Cameroonian startup born in Bafoussam with a simple vision: to make urban mobility accessible, safe, and affordable for everyone.',
  'ceo_title_1' => 'Our',
  'ceo_title_2' => 'Team',
  'ceo_para' => 'Driven by a young, dynamic team passionate about technology and transport, Merci E is committed to transforming the way people in Bafoussam get around.',
  'ceo_img' => 'aboutcompany4.png',
  'download_title' => 'Download Merci E Now',
  'download_para' => 'Available on Android and iOS. Join thousands of users in Bafoussam who trust Merci E for their daily commutes.',
  'download_user_link_android' => 'https://play.google.com/store/apps/details?id=cm.mercie.user',
  'download_user_link_apple' => 'https://apps.apple.com/app/merci-e/id0000000000',
  'download_driver_link_android' => 'https://play.google.com/store/apps/details?id=cm.mercie.driver',
  'download_driver_link_apple' => 'https://apps.apple.com/app/merci-e-driver/id0000000000',
  'download_img1' => 'mercie-user.png',
  'download_img2' => 'mercie-driver.png',
  'contact_heading' => 'Contact Us',
  'contact_para' => 'A question, suggestion, or need assistance? Our team is here for you.',
  'contact_img' => 'contactvideo.mp4',
  'contact_address_title' => 'Address',
  'contact_address' => 'Bafoussam, West Region, Cameroon',
  'contact_phone_title' => 'Phone',
  'contact_phone' => '+237 6XX XXX XXX',
  'contact_mail_title' => 'Email',
  'contact_mail' => 'contact@mercie.cm',
  'contact_web_title' => 'Website',
  'contact_web' => 'https://mercie.cm',
  'form_name' => 'Your Name',
  'form_mail' => 'Your Email',
  'form_subject' => 'Subject',
  'form_message' => 'Your Message',
  'form_btn' => 'Send',
  'locale' => 'en',
  'language' => 'English',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => '2026-06-09 13:53:56',
)));
        DB::table('single_landing_page')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_para' => 'Votre trajet à Bafoussam, rapide et en toute sécurité. Merci E — le taxi intelligent qui vous accompagne partout dans la ville.',
  'hero_img_1' => 'hero3.png',
  'hero_img_2' => 'herosec4.png',
  'hero_img_3' => 'mockuper.png',
  'hero_img_4' => 'hero4.png',
  'hero_img_5' => 'hero2.png',
  'adv_title' => 'Pourquoi Choisir Merci E ?',
  'adv_para' => 'Une application moderne conçue pour les habitants de Bafoussam et des environs. Réservez, suivez et arrivez en toute confiance.',
  'adv_box1_title' => 'Taxi & VTC',
  'adv_box1_para' => 'Commandez un taxi en quelques secondes. Nos chauffeurs certifiés vous conduisent partout à Bafoussam et dans les environs en toute sécurité.',
  'adv_box1_img' => 'vehicletype.mp4',
  'adv_box2_title' => 'Livraison Express',
  'adv_box2_para' => 'Envoyez et recevez vos colis rapidement. Notre service de livraison couvre toute la ville pour vos courses du quotidien.',
  'adv_box2_img' => 'live-tracking.mp4',
  'adv_box3_title' => 'Covoiturage',
  'adv_box3_para' => 'Partagez vos trajets et réduisez vos coûts. Le covoiturage Merci E connecte conducteurs et passagers sur les mêmes itinéraires.',
  'adv_box3_img' => 'payment.mp4',
  'adv_box4_title' => 'Location de Véhicule',
  'adv_box4_para' => 'Louez un véhicule avec ou sans chauffeur pour vos besoins ponctuels : événements, déplacements professionnels ou voyages.',
  'adv_box4_img' => 'bidding.mp4',
  'adv_box5_title' => 'Paiement Mobile Money',
  'adv_box5_para' => 'Payez facilement via MTN Mobile Money ou Orange Money. Plus besoin de monnaie, tout se fait depuis votre téléphone.',
  'adv_box5_img' => 'language.mp4',
  'app_works_title' => 'Comment Fonctionne Merci E ?',
  'app_works_para' => 'Que vous soyez passager ou chauffeur, notre application est conçue pour vous simplifier la vie.',
  'app_works_user_title' => 'Passager',
  'app_works_driver_title' => 'Chauffeur',
  'user_box1_title' => 'Téléchargez l\'Application',
  'user_box1_para' => 'Installez Merci E sur votre téléphone Android ou iPhone. Inscription rapide et gratuite.',
  'user_box2_title' => 'Choisissez Votre Destination',
  'user_box2_para' => 'Entrez votre point de départ et votre destination n\'importe où à Bafoussam.',
  'user_box3_title' => 'Trouvez un Chauffeur',
  'user_box3_para' => 'Un chauffeur proche de vous accepte votre demande en quelques secondes.',
  'user_box4_title' => 'Voyagez et Payez',
  'user_box4_para' => 'Profitez de votre trajet et payez facilement à l\'arrivée via mobile money ou espèces.',
  'driver_box1_title' => 'Devenez Partenaire',
  'driver_box1_para' => 'Inscrivez-vous comme chauffeur Merci E et commencez à gagner à votre rythme.',
  'driver_box2_title' => 'Recevez des Courses',
  'driver_box2_para' => 'Les demandes arrivent directement sur votre application. Acceptez selon votre disponibilité.',
  'driver_box3_title' => 'Naviguez Facilement',
  'driver_box3_para' => 'Suivez l\'itinéraire guidé vers le passager et à destination en temps réel.',
  'driver_box4_title' => 'Encaissez Vos Gains',
  'driver_box4_para' => 'Recevez vos paiements directement sur votre compte mobile money chaque semaine.',
  'app_user_img' => 'user-img.png',
  'app_driver_img' => 'driverimg1.png',
  'why_choose_title' => 'Pourquoi Merci E est le Meilleur Choix ?',
  'why_choose_box1_title' => 'Tous vos trajets en un clic',
  'why_choose_box1_para' => 'Taxi, livraison, covoiturage et location — tous vos besoins de transport réunis dans une seule application.',
  'why_choose_box2_title' => 'Tarifs justes et transparents',
  'why_choose_box2_para' => 'Les prix sont calculés à l\'avance. Pas de surprise, pas de négociation. Vous savez combien vous payez avant de réserver.',
  'why_choose_box3_title' => 'Chauffeurs vérifiés',
  'why_choose_box3_para' => 'Tous nos chauffeurs sont formés et vérifiés. Suivez votre trajet en temps réel et partagez votre position avec vos proches.',
  'why_choose_box4_title' => 'Disponible 24h/24',
  'why_choose_box4_para' => 'Merci E est à votre service jour et nuit, 7 jours sur 7. Besoin d\'un taxi à 3h du matin ? On est là.',
  'why_choose_img' => 'useimagefinal.png',
  'about_title_1' => 'À Propos de',
  'about_title_2' => 'Merci E',
  'about_img' => 'aboutus1.png',
  'about_para' => 'Merci E est une startup camerounaise née à Bafoussam avec une vision simple : rendre la mobilité urbaine accessible, sûre et abordable pour tous. Notre plateforme connecte les passagers aux meilleurs chauffeurs de la région de l\'Ouest Cameroun.',
  'ceo_title_1' => 'Notre',
  'ceo_title_2' => 'Équipe',
  'ceo_para' => 'Portée par une équipe jeune, dynamique et passionnée par la technologie et le transport, Merci E s\'engage à transformer la manière dont les Bafoussaméens se déplacent.',
  'ceo_img' => 'aboutcompany4.png',
  'download_title' => 'Téléchargez Merci E Maintenant',
  'download_para' => 'Disponible sur Android et iOS. Rejoignez des milliers d\'utilisateurs à Bafoussam qui font confiance à Merci E.',
  'download_user_link_android' => 'https://play.google.com/store/apps/details?id=cm.mercie.user',
  'download_user_link_apple' => 'https://apps.apple.com/app/merci-e/id0000000000',
  'download_driver_link_android' => 'https://play.google.com/store/apps/details?id=cm.mercie.driver',
  'download_driver_link_apple' => 'https://apps.apple.com/app/merci-e-driver/id0000000000',
  'download_img1' => 'mercie-user.png',
  'download_img2' => 'mercie-driver.png',
  'contact_heading' => 'Contactez-Nous',
  'contact_para' => 'Une question, une suggestion ou besoin d\'assistance ? Notre équipe est là pour vous.',
  'contact_img' => 'contactvideo.mp4',
  'contact_address_title' => 'Adresse',
  'contact_address' => 'Bafoussam, Région de l\'Ouest, Cameroun',
  'contact_phone_title' => 'Téléphone',
  'contact_phone' => '+237 6XX XXX XXX',
  'contact_mail_title' => 'Email',
  'contact_mail' => 'contact@mercie.cm',
  'contact_web_title' => 'Site Web',
  'contact_web' => 'https://mercie.cm',
  'form_name' => 'Votre Nom',
  'form_mail' => 'Votre Email',
  'form_subject' => 'Objet',
  'form_message' => 'Votre Message',
  'form_btn' => 'Envoyer',
  'locale' => 'fr',
  'language' => 'French',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => '2026-06-09 13:53:41',
)));

        // === SINGLE_LANDING_HEADERS ===
        DB::table('single_landing_headers')->truncate();
        DB::table('single_landing_headers')->insert(array_merge(['id' => Str::uuid()], array (
  'header_logo' => 'rest.png',
  'home' => 'Home',
  'aboutus' => 'About Us',
  'apps' => 'Apps',
  'contact' => 'Contact',
  'book_now_btn' => 'Book Now',
  'footer_logo' => 'rest.png',
  'footer_para' => 'Merci E is your smart taxi service in Bafoussam, Cameroon. Book easily, travel safely, pay stress-free.',
  'quick_links' => 'Quick Links',
  'compliance' => 'Compliance',
  'privacy' => 'Privacy Policy',
  'terms' => 'Terms of Use',
  'dmv' => 'Partners',
  'user_app' => 'Passenger App',
  'user_play' => 'Google Play',
  'user_play_link' => 'https://play.google.com/store/apps/details?id=cm.mercie.user',
  'user_apple' => 'App Store',
  'user_apple_link' => 'https://apps.apple.com/app/merci-e/id0000000000',
  'driver_app' => 'Driver App',
  'driver_play' => 'Google Play',
  'driver_play_link' => 'https://play.google.com/store/apps/details?id=cm.mercie.driver',
  'driver_apple' => 'App Store',
  'driver_apple_link' => 'https://apps.apple.com/app/merci-e-driver/id0000000000',
  'copy_rights' => '2025 © Merci E — Bafoussam, Cameroon',
  'fb_link' => 'https://www.facebook.com/',
  'linkdin_link' => 'https://in.linkedin.com/',
  'x_link' => 'https://x.com/',
  'insta_link' => 'https://www.instagram.com/',
  'locale' => 'en',
  'language' => 'English',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));
        DB::table('single_landing_headers')->insert(array_merge(['id' => Str::uuid()], array (
  'header_logo' => 'rest.png',
  'home' => 'Accueil',
  'aboutus' => 'À Propos',
  'apps' => 'Applications',
  'contact' => 'Contact',
  'book_now_btn' => 'Commander',
  'footer_logo' => 'rest.png',
  'footer_para' => 'Merci E est votre service de taxi intelligent à Bafoussam, Cameroun. Réservez facilement, voyagez en sécurité et payez sans stress.',
  'quick_links' => 'Liens Rapides',
  'compliance' => 'Conformité',
  'privacy' => 'Confidentialité',
  'terms' => 'Conditions',
  'dmv' => 'Partenaires',
  'user_app' => 'App Passager',
  'user_play' => 'Google Play',
  'user_play_link' => 'https://play.google.com/store/apps/details?id=cm.mercie.user',
  'user_apple' => 'App Store',
  'user_apple_link' => 'https://apps.apple.com/app/merci-e/id0000000000',
  'driver_app' => 'App Chauffeur',
  'driver_play' => 'Google Play',
  'driver_play_link' => 'https://play.google.com/store/apps/details?id=cm.mercie.driver',
  'driver_apple' => 'App Store',
  'driver_apple_link' => 'https://apps.apple.com/app/merci-e-driver/id0000000000',
  'copy_rights' => '2025 © Merci E — Bafoussam, Cameroun',
  'fb_link' => 'https://www.facebook.com/',
  'linkdin_link' => 'https://in.linkedin.com/',
  'x_link' => 'https://x.com/',
  'insta_link' => 'https://www.instagram.com/',
  'locale' => 'fr',
  'language' => 'French',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));

        // === LANDING_HOMES ===
        DB::table('landing_homes')->truncate();
        DB::table('landing_homes')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_title' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout',
  'hero_user_link_android' => 'https://play.google.com/store/apps/details?id=cm.mercie.user',
  'hero_user_link_apple' => 'https://apps.apple.com/app/merci-e/id0000000000',
  'hero_driver_link_android' => 'https://play.google.com/store/apps/details?id=cm.mercie.driver',
  'hero_driver_link_apple' => 'https://apps.apple.com/app/merci-e-driver/id0000000000',
  'feature_heading' => 'مزايا استخدام تطبيقاتنا',
  'feature_para' => 'Découvrez les fonctionnalités qui font de Merci E le meilleur choix pour vos déplacements à Bafoussam et dans tout le Cameroun.',
  'feature_sub_heading_1' => 'اضغط زرًا، واحصل على رحلة',
  'feature_sub_para_1' => 'Commandez un taxi en quelques secondes depuis votre smartphone. Indiquez votre destination et un chauffeur arrive rapidement.',
  'feature_sub_heading_2' => 'متوفر دائمًا',
  'feature_sub_para_2' => 'Payez facilement par Mobile Money (MTN, Orange) ou en espèces. Pas besoin de carte bancaire.',
  'feature_sub_heading_3' => 'اركب وادفع',
  'feature_sub_para_3' => 'Suivez votre chauffeur en temps réel sur la carte. Partagez votre trajet avec vos proches pour plus de sécurité.',
  'feature_sub_heading_4' => 'قيم رحلتك، نحن نستمع',
  'feature_sub_para_4' => 'Des tarifs clairs affichés avant la course. Aucun frais caché ni surprise à l\'arrivée.',
  'service_heading_1' => 'الخدمات الرقمية',
  'service_heading_2' => 'حل شامل لخدمة التاكسي الخاصة بك.',
  'service_para' => 'reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.',
  'services' => 'حماية البيانات,دعم العملاء,ضمان الجودة,خدمات رائعة',
  'service_img' => 'service.png',
  'about_title_1' => 'حول',
  'about_title_2' => 'الشركة',
  'about_img' => 'company.png',
  'about_para' => 'Merci E est une plateforme de mobilité née à Bafoussam, conçue pour faciliter les déplacements quotidiens des Camerounais. Notre mission est de connecter passagers et chauffeurs via une technologie simple, accessible et adaptée aux réalités locales.',
  'about_lists' => 'فريق متميز,خدمات رائعة,دعم العملاء,ضمان الجودة',
  'box_img_1' => '1.png',
  'box_para_1' => 'Nos chauffeurs sont vérifiés et formés pour vous offrir un service de qualité. Chaque trajet est suivi en temps réel.',
  'box_img_2' => '2.jpg',
  'box_para_2' => 'Des tarifs adaptés au pouvoir d\'achat local, calculés de manière transparente avant chaque course. Pas de mauvaises surprises.',
  'box_img_3' => '3.jpg',
  'box_para_3' => 'Un support client disponible 7j/7 en français et en langues locales pour répondre à toutes vos questions.',
  'drive_heading' => 'لماذا القيادة مع Merci E!',
  'drive_title_1' => 'من نحن',
  'drive_para_1' => 'Gagnez de l\'argent en conduisant avec Merci E. Choisissez vos horaires et soyez votre propre patron.',
  'drive_title_2' => 'مهمتنا',
  'drive_para_2' => 'Recevez vos paiements rapidement sur votre compte Mobile Money. Des revenus transparents chaque semaine.',
  'drive_title_3' => 'التزامنا',
  'drive_para_3' => 'Rejoignez une communauté de chauffeurs professionnels et bénéficiez d\'un support dédié 7j/7.',
  'service_area_img' => 'locations.png',
  'service_area_title' => 'مواقع الخدمة',
  'service_area_para' => 'Merci E est disponible à Bafoussam et ses environs : Djeleng, Tamdja, Kamkop, Banengo et bien plus. Notre couverture s\'étend progressivement dans tout le Cameroun.',
  'locale' => 'ar',
  'language' => 'Arabic',
  'direction' => 'rtl',
  'created_at' => NULL,
  'updated_at' => NULL,
)));
        DB::table('landing_homes')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_title' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout!',
  'hero_user_link_android' => 'https://play.google.com/store/apps/details?id=cm.mercie.user',
  'hero_user_link_apple' => 'https://apps.apple.com/app/merci-e/id0000000000',
  'hero_driver_link_android' => 'https://play.google.com/store/apps/details?id=cm.mercie.driver',
  'hero_driver_link_apple' => 'https://apps.apple.com/app/merci-e-driver/id0000000000',
  'feature_heading' => 'Avantages de nos applications',
  'feature_para' => 'Découvrez les fonctionnalités qui font de Merci E le meilleur choix pour vos déplacements à Bafoussam et dans tout le Cameroun.',
  'feature_sub_heading_1' => 'Appuyez sur un bouton, obtenez un trajet',
  'feature_sub_para_1' => 'Commandez un taxi en quelques secondes depuis votre smartphone. Indiquez votre destination et un chauffeur arrive rapidement.',
  'feature_sub_heading_2' => 'Toujours disponible',
  'feature_sub_para_2' => 'Payez facilement par Mobile Money (MTN, Orange) ou en espèces. Pas besoin de carte bancaire.',
  'feature_sub_heading_3' => 'Roulez et payez',
  'feature_sub_para_3' => 'Suivez votre chauffeur en temps réel sur la carte. Partagez votre trajet avec vos proches pour plus de sécurité.',
  'feature_sub_heading_4' => 'Évaluez, nous écoutons',
  'feature_sub_para_4' => 'Des tarifs clairs affichés avant la course. Aucun frais caché ni surprise à l\'arrivée.',
  'service_heading_1' => 'Services numériques',
  'service_heading_2' => 'Une solution complète pour votre service de taxi.',
  'service_para' => 'reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.',
  'services' => 'Protection des données,Soutien client,Assurance qualité,Services impressionnants',
  'service_img' => 'service.png',
  'about_title_1' => 'À propos',
  'about_title_2' => 'L\'entreprise',
  'about_img' => 'company.png',
  'about_para' => 'Merci E est une plateforme de mobilité née à Bafoussam, conçue pour faciliter les déplacements quotidiens des Camerounais. Notre mission est de connecter passagers et chauffeurs via une technologie simple, accessible et adaptée aux réalités locales.',
  'about_lists' => 'Équipe dédiée,Services impressionnants,Soutien client,Assurance qualité',
  'box_img_1' => '1.png',
  'box_para_1' => 'Nos chauffeurs sont vérifiés et formés pour vous offrir un service de qualité. Chaque trajet est suivi en temps réel.',
  'box_img_2' => '2.jpg',
  'box_para_2' => 'Des tarifs adaptés au pouvoir d\'achat local, calculés de manière transparente avant chaque course. Pas de mauvaises surprises.',
  'box_img_3' => '3.jpg',
  'box_para_3' => 'Un support client disponible 7j/7 en français et en langues locales pour répondre à toutes vos questions.',
  'drive_heading' => 'Pourquoi conduire avec Merci E!',
  'drive_title_1' => 'À propos de nous',
  'drive_para_1' => 'Gagnez de l\'argent en conduisant avec Merci E. Choisissez vos horaires et soyez votre propre patron.',
  'drive_title_2' => 'Notre mission',
  'drive_para_2' => 'Recevez vos paiements rapidement sur votre compte Mobile Money. Des revenus transparents chaque semaine.',
  'drive_title_3' => 'Engagement conducteur',
  'drive_para_3' => 'Rejoignez une communauté de chauffeurs professionnels et bénéficiez d\'un support dédié 7j/7.',
  'service_area_img' => 'locations.png',
  'service_area_title' => 'Zones de service',
  'service_area_para' => 'Merci E est disponible à Bafoussam et ses environs : Djeleng, Tamdja, Kamkop, Banengo et bien plus. Notre couverture s\'étend progressivement dans tout le Cameroun.',
  'locale' => 'fr',
  'language' => 'French',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));
        DB::table('landing_homes')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_title' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout!',
  'hero_user_link_android' => 'https://play.google.com/store/apps/details?id=cm.mercie.user',
  'hero_user_link_apple' => 'https://apps.apple.com/app/merci-e/id0000000000',
  'hero_driver_link_android' => 'https://play.google.com/store/apps/details?id=cm.mercie.driver',
  'hero_driver_link_apple' => 'https://apps.apple.com/app/merci-e-driver/id0000000000',
  'feature_heading' => 'Advantage of using our Apps',
  'feature_para' => 'Découvrez les fonctionnalités qui font de Merci E le meilleur choix pour vos déplacements à Bafoussam et dans tout le Cameroun.',
  'feature_sub_heading_1' => 'Tap a button, get a ride',
  'feature_sub_para_1' => 'Commandez un taxi en quelques secondes depuis votre smartphone. Indiquez votre destination et un chauffeur arrive rapidement.',
  'feature_sub_heading_2' => 'Always on, always available',
  'feature_sub_para_2' => 'Payez facilement par Mobile Money (MTN, Orange) ou en espèces. Pas besoin de carte bancaire.',
  'feature_sub_heading_3' => 'Ride and Pay',
  'feature_sub_para_3' => 'Suivez votre chauffeur en temps réel sur la carte. Partagez votre trajet avec vos proches pour plus de sécurité.',
  'feature_sub_heading_4' => 'You rate, we listen',
  'feature_sub_para_4' => 'Des tarifs clairs affichés avant la course. Aucun frais caché ni surprise à l\'arrivée.',
  'service_heading_1' => 'DIGITAL SERVICES',
  'service_heading_2' => 'A complete solution for your Taxi Service.',
  'service_para' => 'reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.',
  'services' => 'Data Protection,Customer Support,Quality Assurance,Awesome Services',
  'service_img' => 'service.png',
  'about_title_1' => 'ABOUT',
  'about_title_2' => 'The Company',
  'about_img' => 'company.png',
  'about_para' => 'Merci E est une plateforme de mobilité née à Bafoussam, conçue pour faciliter les déplacements quotidiens des Camerounais. Notre mission est de connecter passagers et chauffeurs via une technologie simple, accessible et adaptée aux réalités locales.',
  'about_lists' => 'Dedicated Team Members,Awesome Services,Customer Support,Quality Assurance',
  'box_img_1' => '1.png',
  'box_para_1' => 'Nos chauffeurs sont vérifiés et formés pour vous offrir un service de qualité. Chaque trajet est suivi en temps réel.',
  'box_img_2' => '2.jpg',
  'box_para_2' => 'Des tarifs adaptés au pouvoir d\'achat local, calculés de manière transparente avant chaque course. Pas de mauvaises surprises.',
  'box_img_3' => '3.jpg',
  'box_para_3' => 'Un support client disponible 7j/7 en français et en langues locales pour répondre à toutes vos questions.',
  'drive_heading' => 'Why Drive with Merci E!',
  'drive_title_1' => 'About Us',
  'drive_para_1' => 'Gagnez de l\'argent en conduisant avec Merci E. Choisissez vos horaires et soyez votre propre patron.',
  'drive_title_2' => 'Our Mission',
  'drive_para_2' => 'Recevez vos paiements rapidement sur votre compte Mobile Money. Des revenus transparents chaque semaine.',
  'drive_title_3' => 'Driver Commitment',
  'drive_para_3' => 'Rejoignez une communauté de chauffeurs professionnels et bénéficiez d\'un support dédié 7j/7.',
  'service_area_img' => 'locations.png',
  'service_area_title' => 'Service Locations',
  'service_area_para' => 'Merci E est disponible à Bafoussam et ses environs : Djeleng, Tamdja, Kamkop, Banengo et bien plus. Notre couverture s\'étend progressivement dans tout le Cameroun.',
  'locale' => 'en',
  'language' => 'English',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));
        DB::table('landing_homes')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_title' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout',
  'hero_user_link_android' => 'https://play.google.com/store/apps/details?id=cm.mercie.user',
  'hero_user_link_apple' => 'https://apps.apple.com/app/merci-e/id0000000000',
  'hero_driver_link_android' => 'https://play.google.com/store/apps/details?id=cm.mercie.driver',
  'hero_driver_link_apple' => 'https://apps.apple.com/app/merci-e-driver/id0000000000',
  'feature_heading' => 'Beneficios de nuestras aplicaciones',
  'feature_para' => 'Découvrez les fonctionnalités qui font de Merci E le meilleur choix pour vos déplacements à Bafoussam et dans tout le Cameroun.',
  'feature_sub_heading_1' => 'Presiona un botón, consigue un viaje',
  'feature_sub_para_1' => 'Commandez un taxi en quelques secondes depuis votre smartphone. Indiquez votre destination et un chauffeur arrive rapidement.',
  'feature_sub_heading_2' => 'Siempre disponible',
  'feature_sub_para_2' => 'Payez facilement par Mobile Money (MTN, Orange) ou en espèces. Pas besoin de carte bancaire.',
  'feature_sub_heading_3' => 'Viaja y paga',
  'feature_sub_para_3' => 'Suivez votre chauffeur en temps réel sur la carte. Partagez votre trajet avec vos proches pour plus de sécurité.',
  'feature_sub_heading_4' => 'Evalúa, escuchamos',
  'feature_sub_para_4' => 'Des tarifs clairs affichés avant la course. Aucun frais caché ni surprise à l\'arrivée.',
  'service_heading_1' => 'Servicios digitales',
  'service_heading_2' => 'Una solución integral para tu servicio de taxi.',
  'service_para' => 'reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.',
  'services' => 'Protección de datos,Atención al cliente,Aseguramiento de calidad,Servicios impresionantes',
  'service_img' => 'service.png',
  'about_title_1' => 'Sobre',
  'about_title_2' => 'la empresa',
  'about_img' => 'company.png',
  'about_para' => 'Merci E est une plateforme de mobilité née à Bafoussam, conçue pour faciliter les déplacements quotidiens des Camerounais. Notre mission est de connecter passagers et chauffeurs via une technologie simple, accessible et adaptée aux réalités locales.',
  'about_lists' => 'Equipo dedicado,Servicios impresionantes,Atención al cliente,Aseguramiento de calidad',
  'box_img_1' => '1.png',
  'box_para_1' => 'Nos chauffeurs sont vérifiés et formés pour vous offrir un service de qualité. Chaque trajet est suivi en temps réel.',
  'box_img_2' => '2.jpg',
  'box_para_2' => 'Des tarifs adaptés au pouvoir d\'achat local, calculés de manière transparente avant chaque course. Pas de mauvaises surprises.',
  'box_img_3' => '3.jpg',
  'box_para_3' => 'Un support client disponible 7j/7 en français et en langues locales pour répondre à toutes vos questions.',
  'drive_heading' => '¡Por qué conducir con Merci E!',
  'drive_title_1' => 'Quiénes somos',
  'drive_para_1' => 'Gagnez de l\'argent en conduisant avec Merci E. Choisissez vos horaires et soyez votre propre patron.',
  'drive_title_2' => 'Nuestra misión',
  'drive_para_2' => 'Recevez vos paiements rapidement sur votre compte Mobile Money. Des revenus transparents chaque semaine.',
  'drive_title_3' => 'Nuestro compromiso',
  'drive_para_3' => 'Rejoignez une communauté de chauffeurs professionnels et bénéficiez d\'un support dédié 7j/7.',
  'service_area_img' => 'locations.png',
  'service_area_title' => 'Áreas de servicio',
  'service_area_para' => 'Merci E est disponible à Bafoussam et ses environs : Djeleng, Tamdja, Kamkop, Banengo et bien plus. Notre couverture s\'étend progressivement dans tout le Cameroun.',
  'locale' => 'es',
  'language' => 'Spanish',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));

        // === LANDING_DRIVERS ===
        DB::table('landing_drivers')->truncate();
        DB::table('landing_drivers')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_title' => 'Driver',
  'driver_heading_1' => 'Be Your Own Boss in Bafoussam',
  'driver_para' => 'Join Merci E and earn money driving on your own schedule. No boss, no constraints.',
  'driver_img_1' => 'app-download.png',
  'driver_title_1' => 'Flexible Earnings',
  'driver_para_1' => 'Earn more by driving during peak hours. You choose when and how much you work.',
  'driver_img_2' => 'upload.png',
  'driver_title_2' => 'Fast Payments',
  'driver_para_2' => 'Receive your earnings directly to your MTN or Orange mobile money account every week.',
  'driver_img_3' => 'drive.png',
  'driver_title_3' => 'Dedicated Support',
  'driver_para_3' => 'A Merci E team is available 7 days a week to support you and resolve any issues.',
  'how_it_work_heading' => 'How to Become a Merci E Driver?',
  'how_it_work_title_1' => 'Download',
  'how_it_work_para_1' => 'Download the Merci E Driver app on your Android or iPhone smartphone.',
  'how_it_work_img_1' => 'd1.png',
  'how_it_work_title_2' => 'Sign Up',
  'how_it_work_para_2' => 'Create your account and submit your documents (license, ID, registration).',
  'how_it_work_img_2' => 'd2.png',
  'how_it_work_title_3' => 'Drive',
  'how_it_work_para_3' => 'Once approved, start receiving trips and earning your living.',
  'how_it_work_img_3' => 'd3.png',
  'how_it_work_title_4' => 'Open App',
  'how_it_work_para_4' => 'Open the Merci E Driver app and switch to online mode to start receiving ride requests.',
  'how_it_work_img_4' => 'd4.png',
  'how_it_work_title_5' => 'Accept',
  'how_it_work_para_5' => 'Accept incoming ride requests on your screen and head to the pickup location.',
  'how_it_work_img_5' => 'd5.png',
  'how_it_work_title_6' => 'Pickup',
  'how_it_work_para_6' => 'Pick up the passenger at the designated meeting point shown on the map and confirm pickup.',
  'how_it_work_img_6' => 'd6.png',
  'how_it_work_title_7' => 'Drop off',
  'how_it_work_para_7' => 'Drop off the passenger at their destination and receive your payment automatically.',
  'how_it_work_img_7' => 'd7.png',
  'req_heading' => 'Requirements',
  'req_title' => 'Personal Requirements',
  'req_lists' => '["Be 21 years or older","Valid Cameroonian driver\'s license","Valid national identity card","Clean criminal record"]',
  'req_img' => 'drive-apply.png',
  'vechile_req_title' => 'Vehicle Requirements',
  'vechile_req_lists' => '["Vehicle in good mechanical condition","Valid insurance","Up-to-date technical inspection","Vehicle less than 10 years old"]',
  'vechile_req_img' => 'taxi-req.png',
  'doc_req_title' => 'Required Documents',
  'doc_req_lists' => '["Driver\'s license","National identity card","Vehicle registration","Insurance certificate"]',
  'doc_req_img' => 'document.png',
  'locale' => 'en',
  'language' => 'English',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));
        DB::table('landing_drivers')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_title' => 'Chauffeur',
  'driver_heading_1' => 'Soyez votre propre patron à Bafoussam',
  'driver_para' => 'Rejoignez Merci E et gagnez de l\'argent en conduisant selon vos propres horaires. Pas de patron, pas de contraintes.',
  'driver_img_1' => 'app-download.png',
  'driver_title_1' => 'Revenus Flexibles',
  'driver_para_1' => 'Gagnez plus en conduisant aux heures de pointe. Vous choisissez quand et combien vous travaillez.',
  'driver_img_2' => 'upload.png',
  'driver_title_2' => 'Paiements Rapides',
  'driver_para_2' => 'Recevez vos gains directement sur votre compte mobile money MTN ou Orange chaque semaine.',
  'driver_img_3' => 'drive.png',
  'driver_title_3' => 'Support Dédié',
  'driver_para_3' => 'Une équipe Merci E est disponible 7j/7 pour vous accompagner et résoudre vos problèmes.',
  'how_it_work_heading' => 'Comment Devenir Chauffeur Merci E ?',
  'how_it_work_title_1' => 'Télécharger',
  'how_it_work_para_1' => 'Téléchargez l\'application Merci E Driver sur votre smartphone Android ou iPhone.',
  'how_it_work_img_1' => 'd1.png',
  'how_it_work_title_2' => 'S\'inscrire',
  'how_it_work_para_2' => 'Créez votre compte et soumettez vos documents (permis, CNI, carte grise).',
  'how_it_work_img_2' => 'd2.png',
  'how_it_work_title_3' => 'Conduire',
  'how_it_work_para_3' => 'Une fois approuvé, commencez à recevoir des courses et à gagner votre vie.',
  'how_it_work_img_3' => 'd3.png',
  'how_it_work_title_4' => 'Ouvrez l\'application',
  'how_it_work_para_4' => 'Ouvrez l\'application Merci E Driver et activez le mode en ligne pour recevoir des demandes de course.',
  'how_it_work_img_4' => 'd4.png',
  'how_it_work_title_5' => 'Acceptez',
  'how_it_work_para_5' => 'Acceptez les demandes de course qui apparaissent sur votre écran et dirigez-vous vers le point de prise en charge.',
  'how_it_work_img_5' => 'd5.png',
  'how_it_work_title_6' => 'Récupérez',
  'how_it_work_para_6' => 'Récupérez le passager au point de rendez-vous indiqué sur la carte et confirmez la prise en charge.',
  'how_it_work_img_6' => 'd6.png',
  'how_it_work_title_7' => 'Déposez',
  'how_it_work_para_7' => 'Déposez le passager à sa destination et recevez votre paiement automatiquement.',
  'how_it_work_img_7' => 'd7.png',
  'req_heading' => 'Conditions Requises',
  'req_title' => 'Exigences Personnelles',
  'req_lists' => '["Avoir 21 ans ou plus","Permis de conduire camerounais valide","Carte nationale d\'identité valide","Casier judiciaire vierge"]',
  'req_img' => 'drive-apply.png',
  'vechile_req_title' => 'Exigences du Véhicule',
  'vechile_req_lists' => '["Véhicule en bon état mécanique","Assurance valide","Visite technique à jour","Véhicule de moins de 10 ans"]',
  'vechile_req_img' => 'taxi-req.png',
  'doc_req_title' => 'Documents Requis',
  'doc_req_lists' => '["Permis de conduire","Carte nationale d\'identité","Carte grise du véhicule","Attestation d\'assurance"]',
  'doc_req_img' => 'document.png',
  'locale' => 'fr',
  'language' => 'French',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));

        // === LANDING_ABOUTS ===
        DB::table('landing_abouts')->truncate();
        DB::table('landing_abouts')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_title' => 'About Merci E',
  'about_heading' => 'ABOUT US',
  'about_title' => 'Our Mission in Bafoussam',
  'about_para' => 'Merci E is a Cameroonian startup born in Bafoussam with a simple vision: to make urban mobility accessible, safe and affordable for everyone. Our platform connects passengers with the best drivers in the West Cameroon region. We believe every resident deserves dignified, punctual and modern transport.',
  'about_lists' => '["Dedicated team members","Quality services","Responsive customer support","Guaranteed safety"]',
  'about_img' => 'company.png',
  'ceo_name' => 'The CEO',
  'ceo_title' => 'Founder & CEO',
  'signature' => 'signatures.png',
  'ceo_para' => 'Driven by a young, dynamic team passionate about technology and transport, Merci E is committed to transforming the way people in Bafoussam get around every day.',
  'ceo_img' => 'avatar-4.jpg',
  'vision_mision_heading' => 'Our Vision & Mission',
  'vision_title' => 'Our Vision',
  'vision_para' => 'To become the leading mobility platform in Bafoussam and across Cameroon, offering a reliable, accessible and innovative transport experience.',
  'mission_title' => 'Our Mission',
  'mission_para' => 'To connect passengers and drivers through simple, efficient technology for fast, safe and affordable travel throughout the city of Bafoussam.',
  'team_title' => 'Our Team',
  'team_para' => 'Local experts passionate about urban mobility and the development of Cameroon.',
  'team_members' => '[{"team_member_name":"Nancy Mart","team_member_posision":"Driver","team_member_image":"avatar-2.jpg"},{"team_member_name":"John Doe","team_member_posision":"Driver","team_member_image":"avatar-1.jpg"},{"team_member_name":"Jane Smith","team_member_posision":"Driver","team_member_image":"avatar-3.jpg"},{"team_member_name":"Nancy Mart","team_member_posision":"Driver","team_member_image":"avatar-4.jpg"}]',
  'testimonial_heading' => 'Ce que disent nos utilisateurs',
  'testimonial_content' => '[{"testimonial_para":"<p>Merci E a changé ma façon de me déplacer à Bafoussam. Plus besoin d\'attendre au bord de la route, je commande mon taxi en quelques secondes et le chauffeur arrive rapidement. Les prix sont transparents et très abordables.<\\/p>","testimonial_title_1":"Chris Dev","testimonial_title_2":"Passager régulier, Bafoussam"},{"testimonial_para":"<p>Depuis que je conduis avec Merci E, mes revenus ont augmenté considérablement. L\'application est facile à utiliser et je reçois des courses tout au long de la journée. Le support client est réactif et toujours disponible.<\\/p>","testimonial_title_1":"Michel Stephane","testimonial_title_2":"Chauffeur VIP, Bafoussam"},{"testimonial_para":"<p>Je recommande Merci E à tous mes proches. Le service est fiable, les chauffeurs sont professionnels et ponctuels. Le paiement par Mobile Money est un vrai plus pour nous ici au Cameroun. Bravo à toute l\'équipe !<\\/p>","testimonial_title_1":"Boussa Kira","testimonial_title_2":"Utilisateur depuis 2026"},{"testimonial_para":"<p>En tant que commerçante, j\'utilise souvent le service de livraison de Merci E. C\'est rapide, sécurisé et le suivi en temps réel me rassure. C\'est devenu un outil indispensable pour mon activité.<\\/p>","testimonial_title_1":"Aïcha Moussa","testimonial_title_2":"Commerçante, Marché A"}]',
  'locale' => 'en',
  'language' => 'English',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => '2026-06-09 11:07:52',
)));
        DB::table('landing_abouts')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_title' => 'À Propos de Merci E',
  'about_heading' => 'À PROPOS',
  'about_title' => 'Notre Mission à Bafoussam',
  'about_para' => 'Merci E est une startup camerounaise née à Bafoussam avec une vision simple : rendre la mobilité urbaine accessible, sûre et abordable pour tous. Notre plateforme connecte les passagers aux meilleurs chauffeurs de la région de l\'Ouest Cameroun. Nous croyons que chaque habitant mérite un transport digne, ponctuel et moderne.',
  'about_lists' => '["Membres d\'équipe dédiés","Services de qualité","Support client réactif","Sécurité garantie"]',
  'about_img' => 'company.png',
  'ceo_name' => 'Le CEO',
  'ceo_title' => 'Fondateur & CEO',
  'signature' => 'signatures.png',
  'ceo_para' => 'Portée par une équipe jeune, dynamique et passionnée par la technologie et le transport, Merci E s\'engage à transformer la manière dont les Bafoussaméens se déplacent chaque jour.',
  'ceo_img' => 'avatar-4.jpg',
  'vision_mision_heading' => 'Notre Vision & Mission',
  'vision_title' => 'Notre Vision',
  'vision_para' => 'Devenir la plateforme de mobilité de référence à Bafoussam et dans tout le Cameroun, en offrant une expérience de transport fiable, accessible et innovante.',
  'mission_title' => 'Notre Mission',
  'mission_para' => 'Connecter passagers et chauffeurs via une technologie simple et efficace, pour des déplacements rapides, sûrs et abordables dans toute la ville de Bafoussam.',
  'team_title' => 'Notre Équipe',
  'team_para' => 'Des experts locaux passionnés par la mobilité urbaine et le développement du Cameroun.',
  'team_members' => '[{"team_member_name":"Nancy Mart","team_member_posision":"Conducteur","team_member_image":"avatar-2.jpg"},{"team_member_name":"John Doe","team_member_posision":"Conducteur","team_member_image":"avatar-1.jpg"},{"team_member_name":"Jane Smith","team_member_posision":"Conducteur","team_member_image":"avatar-3.jpg"},{"team_member_name":"Nancy Mart","team_member_posision":"Conducteur","team_member_image":"avatar-4.jpg"}]',
  'testimonial_heading' => 'Section Témoignages',
  'testimonial_content' => '[{"testimonial_para":"<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Sed perspiciatis commodi voluptas possimus rerum alias eum necessitatibus reiciendis dolorum praesentium aliquid deserunt, consequatur autem delectus eligendi doloribus, eius quos doloremque.<\\/p>","testimonial_title_1":"gregoriusus","testimonial_title_2":"exemple"},{"testimonial_para":"<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Sed perspiciatis commodi voluptas possimus rerum alias eum necessitatibus reiciendis dolorum praesentium aliquid deserunt, consequatur autem delectus eligendi doloribus, eius quos doloremque.<\\/p>","testimonial_title_1":"gregoriusus","testimonial_title_2":"exemple"},{"testimonial_para":"<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Sed perspiciatis commodi voluptas possimus rerum alias eum necessitatibus reiciendis dolorum praesentium aliquid deserunt, consequatur autem delectus eligendi doloribus, eius quos doloremque.<\\/p>","testimonial_title_1":"gregoriusus","testimonial_title_2":"exemple"}]',
  'locale' => 'fr',
  'language' => 'French',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));

        // === LANDING_HEADERS ===
        DB::table('landing_headers')->truncate();
        DB::table('landing_headers')->insert(array_merge(['id' => Str::uuid()], array (
  'header_logo' => 'rest.png',
  'home' => 'Home',
  'aboutus' => 'About Us',
  'driver' => 'Driver',
  'user' => 'User',
  'contact' => 'Contact',
  'book_now_btn' => 'Book Now',
  'footer_logo' => 'rest.png',
  'footer_para' => 'Merci E is your smart taxi service in Bafoussam, Cameroon. Book easily, travel safely, pay stress-free.',
  'quick_links' => 'Quick Links',
  'compliance' => 'Compliance',
  'privacy' => 'Privacy Policy',
  'terms' => 'Terms & Conditions',
  'dmv' => 'DMV Check',
  'user_app' => 'User Apps',
  'user_play' => 'Play Store',
  'user_play_link' => 'https://play.google.com/store/apps/details?id=cm.mercie.user',
  'user_apple' => 'App Store',
  'user_apple_link' => 'https://apps.apple.com/app/merci-e/id0000000000',
  'driver_app' => 'Driver Apps',
  'driver_play' => 'Play Store',
  'driver_play_link' => 'https://play.google.com/store/apps/details?id=cm.mercie.driver',
  'driver_apple' => 'App Store',
  'driver_apple_link' => 'https://apps.apple.com/app/merci-e-driver/id0000000000',
  'copy_rights' => '2025 © Merci E — Bafoussam, Cameroon',
  'fb_link' => 'https://www.facebook.com/',
  'linkdin_link' => 'https://in.linkedin.com/',
  'x_link' => 'https://x.com/',
  'insta_link' => 'https://www.instagram.com/',
  'locale' => 'en',
  'language' => 'English',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));
        DB::table('landing_headers')->insert(array_merge(['id' => Str::uuid()], array (
  'header_logo' => 'rest.png',
  'home' => 'Accueil',
  'aboutus' => 'À Propos',
  'driver' => 'Chauffeur',
  'user' => 'Utilisateur',
  'contact' => 'Contact',
  'book_now_btn' => 'Commander',
  'footer_logo' => 'rest.png',
  'footer_para' => 'Merci E est votre service de taxi intelligent à Bafoussam, Cameroun. Réservez facilement, voyagez en sécurité et payez sans stress.',
  'quick_links' => 'Liens Rapides',
  'compliance' => 'Conformité',
  'privacy' => 'Politique de Confidentialité',
  'terms' => 'Conditions Générales',
  'dmv' => 'Vérification DMV',
  'user_app' => 'Applications Utilisateurs',
  'user_play' => 'Play Store',
  'user_play_link' => 'https://play.google.com/store/apps/details?id=cm.mercie.user',
  'user_apple' => 'App Store',
  'user_apple_link' => 'https://apps.apple.com/app/merci-e/id0000000000',
  'driver_app' => 'Applications Chauffeur',
  'driver_play' => 'Play Store',
  'driver_play_link' => 'https://play.google.com/store/apps/details?id=cm.mercie.driver',
  'driver_apple' => 'App Store',
  'driver_apple_link' => 'https://apps.apple.com/app/merci-e-driver/id0000000000',
  'copy_rights' => '2025 © Merci E — Bafoussam, Cameroun',
  'fb_link' => 'https://www.facebook.com/',
  'linkdin_link' => 'https://in.linkedin.com/',
  'x_link' => 'https://x.com/',
  'insta_link' => 'https://www.instagram.com/',
  'locale' => 'fr',
  'language' => 'French',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));

        // === LANDING_CONTACTS ===
        DB::table('landing_contacts')->truncate();
        DB::table('landing_contacts')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_title' => 'Contact',
  'contact_heading' => 'Contact Us',
  'contact_para' => 'Have a question or need help? Send us a message and we will respond quickly.',
  'contact_address_title' => 'OFFICE ADDRESS',
  'contact_address' => 'Bafoussam, West Region, Cameroon',
  'contact_phone_title' => 'PHONE',
  'contact_phone' => '+237 6XX XXX XXX',
  'contact_mail_title' => 'EMAIL',
  'contact_mail' => 'contact@mercie.cm',
  'contact_web_title' => 'WEBSITE',
  'contact_web' => 'https://mercie.cm',
  'form_name' => 'Name',
  'form_mail' => 'Email',
  'form_subject' => 'Subject',
  'form_message' => 'Message',
  'form_btn' => 'Send Message',
  'locale' => 'en',
  'language' => 'English',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));
        DB::table('landing_contacts')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_title' => 'Contact',
  'contact_heading' => 'Contactez-Nous',
  'contact_para' => 'Vous avez une question ou besoin d\'aide ? Envoyez-nous un message et nous vous répondrons rapidement.',
  'contact_address_title' => 'ADRESSE DU BUREAU',
  'contact_address' => 'Bafoussam, Région de l\'Ouest, Cameroun',
  'contact_phone_title' => 'TÉLÉPHONE',
  'contact_phone' => '+237 6XX XXX XXX',
  'contact_mail_title' => 'MAIL',
  'contact_mail' => 'contact@mercie.cm',
  'contact_web_title' => 'SITE WEB',
  'contact_web' => 'https://mercie.cm',
  'form_name' => 'Nom',
  'form_mail' => 'Email',
  'form_subject' => 'Sujet',
  'form_message' => 'Message',
  'form_btn' => 'Envoyer le message',
  'locale' => 'fr',
  'language' => 'French',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));

        // === LANDING_USERS ===
        DB::table('landing_users')->truncate();
        DB::table('landing_users')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_title' => 'Utilisateur',
  'user_heading_1' => 'Votre Trajet, Votre Façon',
  'user_para' => 'Avec Merci E, réservez un taxi à Bafoussam en quelques secondes. Simple, rapide et sécurisé.',
  'user_img_1' => 'app-download.png',
  'user_title_1' => 'Réservation Rapide',
  'user_para_1' => 'Commandez un taxi depuis l\'application en moins de 30 secondes, partout à Bafoussam.',
  'user_img_2' => '2.jpg',
  'user_title_2' => 'Suivi en Direct',
  'user_para_2' => 'Suivez votre chauffeur en temps réel sur la carte et partagez votre position avec vos proches.',
  'user_img_3' => 'drive.png',
  'user_title_3' => 'Paiement Facile',
  'user_para_3' => 'Payez via Mobile Money (MTN, Orange) ou en espèces. Tarifs clairs, sans surprise.',
  'how_it_work_heading' => 'Comment Utiliser Merci E ?',
  'how_it_work_title_1' => 'Télécharger',
  'how_it_work_para_1' => 'Téléchargez l\'application Merci E sur votre smartphone.',
  'how_it_work_img_1' => 'u1.png',
  'how_it_work_title_2' => 'Se Connecter',
  'how_it_work_para_2' => 'Inscrivez-vous avec votre numéro de téléphone. C\'est rapide et gratuit.',
  'how_it_work_img_2' => 'u2.png',
  'how_it_work_title_3' => 'Réserver',
  'how_it_work_para_3' => 'Entrez votre destination et trouvez un chauffeur proche de vous.',
  'how_it_work_img_3' => 'u3.png',
  'how_it_work_title_4' => 'Arriver',
  'how_it_work_para_4' => 'Profitez de votre trajet et arrivez à destination en toute sécurité.',
  'how_it_work_img_4' => 'u4.png',
  'how_it_work_title_5' => 'Payer',
  'how_it_work_para_5' => 'Payez facilement via mobile money ou espèces à la fin du trajet.',
  'how_it_work_img_5' => 'u5.png',
  'how_it_work_title_6' => 'Évaluer',
  'how_it_work_para_6' => 'Donnez une note à votre chauffeur pour aider la communauté Merci E.',
  'how_it_work_img_6' => 'u6.png',
  'locale' => 'fr',
  'language' => 'French',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));
        DB::table('landing_users')->insert(array_merge(['id' => Str::uuid()], array (
  'hero_title' => 'User',
  'user_heading_1' => 'Your Ride, Your Way',
  'user_para' => 'With Merci E, book a taxi in Bafoussam in seconds. Simple, fast and secure.',
  'user_img_1' => 'app-download.png',
  'user_title_1' => 'Quick Booking',
  'user_para_1' => 'Order a taxi from the app in less than 30 seconds, anywhere in Bafoussam.',
  'user_img_2' => '2.jpg',
  'user_title_2' => 'Live Tracking',
  'user_para_2' => 'Track your driver in real time on the map and share your location with loved ones.',
  'user_img_3' => 'drive.png',
  'user_title_3' => 'Easy Payment',
  'user_para_3' => 'Pay via Mobile Money (MTN, Orange) or cash. Clear rates, no surprises.',
  'how_it_work_heading' => 'How to Use Merci E?',
  'how_it_work_title_1' => 'Download',
  'how_it_work_para_1' => 'Download the Merci E app on your smartphone.',
  'how_it_work_img_1' => 'u1.png',
  'how_it_work_title_2' => 'Sign Up',
  'how_it_work_para_2' => 'Register with your phone number. It\'s quick and free.',
  'how_it_work_img_2' => 'u2.png',
  'how_it_work_title_3' => 'Book',
  'how_it_work_para_3' => 'Enter your destination and find a driver near you.',
  'how_it_work_img_3' => 'u3.png',
  'how_it_work_title_4' => 'Arrive',
  'how_it_work_para_4' => 'Enjoy your ride and arrive at your destination safely.',
  'how_it_work_img_4' => 'u4.png',
  'how_it_work_title_5' => 'Pay',
  'how_it_work_para_5' => 'Pay easily via mobile money or cash at the end of the trip.',
  'how_it_work_img_5' => 'u5.png',
  'how_it_work_title_6' => 'Rate',
  'how_it_work_para_6' => 'Rate your driver to help the Merci E community.',
  'how_it_work_img_6' => 'u6.png',
  'locale' => 'en',
  'language' => 'English',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));

        // === LANDING_QUICKLINKS ===
        DB::table('landing_quicklinks')->truncate();
        DB::table('landing_quicklinks')->insert(array_merge(['id' => Str::uuid()], array (
  'privacy_title' => 'Privacy Policy',
  'privacy' => '<h2>Privacy Policy</h2>
<p><strong>Effective Date:</strong> January 1, 2025</p>
<h3>1. Scope of This Policy</h3>
<p>This Privacy Policy applies to all users of Merci E, including passengers and drivers, across all our platforms and services — including our mobile applications and website.</p>
<h3>2. Information We Collect</h3>
<p>When you use the Merci E platform, we collect the following types of information:</p>
<p><strong>A. Information You Provide</strong></p>
<p>Account registration: Name, email address, phone number, and payment information. Drivers additionally provide: national ID, driver\'s license number, vehicle information, and profile photo.</p>
<p><strong>B. Information Collected Automatically</strong></p>
<p>Location data (GPS) during trips, device information, usage data, and trip history.</p>
<h3>3. How We Use Your Information</h3>
<p>We use your data to: connect you with drivers or passengers, process payments, improve our services, ensure safety on the platform, and comply with Cameroonian law.</p>
<h3>4. Data Sharing</h3>
<p>We do not sell your personal data. We may share data with trusted partners (e.g., payment processors) strictly for service delivery purposes.</p>
<h3>5. Data Security</h3>
<p>We implement industry-standard security measures to protect your personal data. All data is stored securely in compliance with applicable regulations.</p>
<h3>6. Contact</h3>
<p>For any privacy-related questions, contact us at: <strong>contact@mercie.cm</strong></p>',
  'terms_title' => 'Terms & Conditions',
  'terms' => '<h2>Terms & Conditions</h2>
<p><strong>Last updated:</strong> January 1, 2025</p>
<h3>1. Acceptance of Terms</h3>
<p>By downloading or using the Merci E application, you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our services.</p>
<h3>2. The Merci E Platform</h3>
<p>Merci E is a Cameroonian ride-hailing platform that connects passengers with independent drivers in Bafoussam and surrounding areas. Merci E acts as a technology intermediary and is not itself a transportation company.</p>
<h3>3. User Accounts</h3>
<p>You must be at least 18 years old to create an account. You are responsible for maintaining the confidentiality of your account credentials and for all activity under your account.</p>
<h3>4. Payments</h3>
<p>All fares are calculated automatically based on distance and time. Payments can be made via mobile money (MTN, Orange) or cash. Merci E charges a service fee per completed trip.</p>
<h3>5. Cancellations</h3>
<p>Excessive or abusive cancellations may result in account suspension. Drivers and passengers are expected to honor confirmed bookings.</p>
<h3>6. Driver Responsibilities</h3>
<p>Drivers must possess a valid Cameroonian driver\'s license, maintain their vehicle in roadworthy condition, and comply with all applicable traffic laws.</p>
<h3>7. Limitation of Liability</h3>
<p>Merci E is not liable for damages resulting from use of the platform beyond what is required by Cameroonian law.</p>
<h3>8. Governing Law</h3>
<p>These Terms are governed by the laws of the Republic of Cameroon. Any disputes shall be resolved in competent courts in Bafoussam.</p>
<h3>9. Contact</h3>
<p>Questions about these Terms? Contact us at <strong>contact@mercie.cm</strong></p>',
  'compliance_title' => 'Compliance & Non-Discrimination Policy',
  'compliance' => '<h2>Equal Opportunity and Non-Discrimination Policy</h2>
<h3>i. Scope</h3>
<p>Merci E, headquartered in Bafoussam, West Region, Cameroon, is committed to providing equal access to its transportation platform for all users, regardless of ethnicity, religion, gender, disability, age, or any other protected characteristic.</p>
<p>This policy applies to all passengers, drivers, and employees of Merci E.</p>
<h3>ii. Non-Discrimination</h3>
<p>Merci E strictly prohibits any form of discrimination in the provision of transportation services. Drivers on the platform must serve all eligible passengers without discrimination. Any driver found to have violated this policy will be permanently removed from the platform.</p>
<h3>iii. Reporting</h3>
<p>If you believe you have been discriminated against while using Merci E, please contact us immediately at contact@mercie.cm. We take all complaints seriously and will investigate promptly.</p>
<h3>iv. Driver Conduct</h3>
<p>All Merci E drivers must comply with applicable Cameroonian laws and regulations. Drivers are required to treat all passengers with respect and dignity at all times.</p>',
  'dmv_title' => 'Driver Verification',
  'dmv' => '<h2>Driver Background Check & Verification</h2>
<p>At Merci E, the safety of our passengers is our top priority. All drivers who apply to join the Merci E platform must undergo a thorough verification process before being approved.</p>
<h3>1. Identity Verification</h3>
<p>All applicants must provide a valid national identity card or passport issued by the Republic of Cameroon. The identity document must be current and not expired.</p>
<h3>2. Driver\'s License Check</h3>
<p>Applicants must hold a valid Cameroonian driver\'s license appropriate for the category of vehicle they intend to drive. Merci E reserves the right to verify the authenticity of the license with relevant authorities.</p>
<h3>3. Vehicle Inspection</h3>
<p>All vehicles used on the Merci E platform must pass a safety inspection. Vehicles must be in good mechanical condition, have valid insurance, and carry all required documents.</p>
<h3>4. Criminal Background Check</h3>
<p>Merci E conducts background checks through official Cameroonian channels. Applicants with convictions for violent crimes, sexual offenses, or driving under the influence will not be approved.</p>
<h3>5. Ongoing Monitoring</h3>
<p>Driver accounts are subject to ongoing review based on passenger ratings and reported incidents. Drivers who fall below our minimum standards will be suspended or removed from the platform.</p>
<h3>6. Consent</h3>
<p>By applying to become a Merci E driver, you consent to the collection and verification of the information described above. For questions, contact us at <strong>contact@mercie.cm</strong>.</p>',
  'locale' => 'en',
  'language' => 'English',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));
        DB::table('landing_quicklinks')->insert(array_merge(['id' => Str::uuid()], array (
  'privacy_title' => 'Politique de Confidentialité',
  'privacy' => '<h2>Politique de Confidentialité</h2>
<p><strong>Date d\'entrée en vigueur :</strong> 1er janvier 2025</p>
<h3>1. Portée de cette Politique</h3>
<p>La présente Politique de confidentialité s\'applique à tous les utilisateurs de Merci E, y compris les passagers et les chauffeurs, sur toutes nos plateformes et services — y compris nos applications mobiles et notre site web.</p>
<h3>2. Informations Collectées</h3>
<p>Lors de l\'utilisation de la plateforme Merci E, nous collectons les types d\'informations suivants :</p>
<p><strong>A. Informations que vous fournissez</strong></p>
<p>Inscription au compte : nom, adresse e-mail, numéro de téléphone et informations de paiement. Les chauffeurs fournissent également : carte nationale d\'identité, numéro de permis de conduire, informations sur le véhicule et photo de profil.</p>
<p><strong>B. Informations collectées automatiquement</strong></p>
<p>Données de localisation (GPS) pendant les trajets, informations sur l\'appareil, données d\'utilisation et historique des courses.</p>
<h3>3. Utilisation de vos Informations</h3>
<p>Nous utilisons vos données pour : vous mettre en relation avec des chauffeurs ou des passagers, traiter les paiements, améliorer nos services, assurer la sécurité sur la plateforme et respecter la législation camerounaise.</p>
<h3>4. Partage des Données</h3>
<p>Nous ne vendons pas vos données personnelles. Nous pouvons partager des données avec des partenaires de confiance (ex. processeurs de paiement) uniquement pour la prestation de services.</p>
<h3>5. Sécurité des Données</h3>
<p>Nous mettons en œuvre des mesures de sécurité conformes aux normes du secteur pour protéger vos données personnelles.</p>
<h3>6. Contact</h3>
<p>Pour toute question relative à la confidentialité, contactez-nous à : <strong>contact@mercie.cm</strong></p>',
  'terms_title' => 'Conditions Générales d\'Utilisation',
  'terms' => '<h2>Conditions Générales d\'Utilisation</h2>
<p><strong>Dernière mise à jour :</strong> 1er janvier 2025</p>
<h3>1. Acceptation des Conditions</h3>
<p>En téléchargeant ou en utilisant l\'application Merci E, vous acceptez d\'être lié par les présentes Conditions Générales d\'Utilisation. Si vous n\'acceptez pas ces conditions, veuillez ne pas utiliser nos services.</p>
<h3>2. La Plateforme Merci E</h3>
<p>Merci E est une plateforme camerounaise de réservation de taxis qui met en relation les passagers avec des chauffeurs indépendants à Bafoussam et dans les environs. Merci E agit en tant qu\'intermédiaire technologique et n\'est pas elle-même une société de transport.</p>
<h3>3. Comptes Utilisateurs</h3>
<p>Vous devez avoir au moins 18 ans pour créer un compte. Vous êtes responsable de la confidentialité de vos identifiants et de toute activité effectuée sous votre compte.</p>
<h3>4. Paiements</h3>
<p>Tous les tarifs sont calculés automatiquement en fonction de la distance et du temps. Les paiements peuvent être effectués via mobile money (MTN, Orange) ou en espèces. Merci E prélève des frais de service par course complétée.</p>
<h3>5. Annulations</h3>
<p>Les annulations excessives ou abusives peuvent entraîner la suspension du compte. Les chauffeurs et les passagers sont tenus de respecter les réservations confirmées.</p>
<h3>6. Responsabilités des Chauffeurs</h3>
<p>Les chauffeurs doivent posséder un permis de conduire camerounais valide, maintenir leur véhicule en bon état et respecter toutes les lois de la circulation applicables.</p>
<h3>7. Limitation de Responsabilité</h3>
<p>Merci E n\'est pas responsable des dommages résultant de l\'utilisation de la plateforme au-delà de ce qu\'exige la loi camerounaise.</p>
<h3>8. Droit Applicable</h3>
<p>Les présentes Conditions sont régies par les lois de la République du Cameroun. Tout litige sera résolu devant les tribunaux compétents de Bafoussam.</p>
<h3>9. Contact</h3>
<p>Des questions sur ces Conditions ? Contactez-nous à <strong>contact@mercie.cm</strong></p>',
  'compliance_title' => 'Politique de Conformité et Non-Discrimination',
  'compliance' => '<h2>Égalité des Chances et Non-Discrimination</h2>
<h3>i. Portée</h3>
<p>Merci E, dont le siège est à Bafoussam, Région de l\'Ouest, Cameroun, s\'engage à offrir un accès égal à sa plateforme de transport à tous les utilisateurs, sans distinction d\'ethnie, de religion, de genre, de handicap, d\'âge ou de toute autre caractéristique protégée.</p>
<p>Cette politique s\'applique à tous les passagers, chauffeurs et employés de Merci E.</p>
<h3>ii. Non-Discrimination</h3>
<p>Merci E interdit strictement toute forme de discrimination dans la prestation des services de transport. Les chauffeurs inscrits sur la plateforme doivent servir tous les passagers éligibles sans discrimination. Tout chauffeur reconnu coupable d\'une telle violation sera définitivement retiré de la plateforme.</p>
<h3>iii. Signalement</h3>
<p>Si vous pensez avoir été victime de discrimination lors de l\'utilisation de Merci E, veuillez nous contacter immédiatement à contact@mercie.cm. Nous prenons toutes les plaintes au sérieux et enquêtons rapidement.</p>
<h3>iv. Conduite des Chauffeurs</h3>
<p>Tous les chauffeurs Merci E doivent se conformer aux lois et réglementations camerounaises applicables. Les chauffeurs sont tenus de traiter tous les passagers avec respect et dignité en toutes circonstances.</p>',
  'dmv_title' => 'Vérification des Chauffeurs',
  'dmv' => '<h2>Vérification des Antécédents et Certification des Chauffeurs</h2>
<p>Chez Merci E, la sécurité de nos passagers est notre priorité absolue. Tous les chauffeurs qui souhaitent rejoindre la plateforme Merci E doivent passer par un processus de vérification rigoureux avant d\'être approuvés.</p>
<h3>1. Vérification de l\'Identité</h3>
<p>Tous les candidats doivent fournir une carte nationale d\'identité ou un passeport valide délivré par la République du Cameroun. Le document d\'identité doit être en cours de validité.</p>
<h3>2. Vérification du Permis de Conduire</h3>
<p>Les candidats doivent être titulaires d\'un permis de conduire camerounais valide correspondant à la catégorie de véhicule qu\'ils souhaitent utiliser. Merci E se réserve le droit de vérifier l\'authenticité du permis auprès des autorités compétentes.</p>
<h3>3. Inspection du Véhicule</h3>
<p>Tous les véhicules utilisés sur la plateforme Merci E doivent passer une inspection de sécurité. Les véhicules doivent être en bon état mécanique, avoir une assurance valide et disposer de tous les documents requis.</p>
<h3>4. Vérification du Casier Judiciaire</h3>
<p>Merci E effectue des vérifications d\'antécédents via les canaux officiels camerounais. Les candidats ayant des condamnations pour crimes violents, infractions sexuelles ou conduite sous l\'emprise de l\'alcool ne seront pas approuvés.</p>
<h3>5. Suivi Continu</h3>
<p>Les comptes des chauffeurs font l\'objet d\'un suivi continu basé sur les évaluations des passagers et les incidents signalés. Les chauffeurs qui ne respectent pas nos standards minimaux seront suspendus ou retirés de la plateforme.</p>
<h3>6. Consentement</h3>
<p>En postulant pour devenir chauffeur Merci E, vous consentez à la collecte et à la vérification des informations décrites ci-dessus. Pour toute question, contactez-nous à <strong>contact@mercie.cm</strong>.</p>',
  'locale' => 'fr',
  'language' => 'French',
  'direction' => 'ltr',
  'created_at' => NULL,
  'updated_at' => NULL,
)));

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
