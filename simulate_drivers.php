<?php
/**
 * Simulate active drivers by updating their Firebase timestamps every 30 seconds.
 * Run with: php simulate_drivers.php
 * Stop with: Ctrl+C
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Sk\Geohash\Geohash;
use Kreait\Firebase\Contract\Database;

$database = app(Database::class);
$g = new Geohash();

$drivers = [
    'driver_1' => [3.866, 11.521],  // VIP - near Centre Administratif Yaoundé
    'driver_3' => [3.864, 11.518],  // Moto - near Centre Administratif Yaoundé
];

echo "Simulating active drivers near Yaoundé. Press Ctrl+C to stop.\n";

// Update positions + geohash + timestamp
foreach ($drivers as $driverId => $pos) {
    $geohash = $g->encode($pos[0], $pos[1], 12); // encode(lat, lng, precision)
    $database->getReference("drivers/$driverId")->update([
        'l' => $pos,
        'g' => $geohash,
        'updated_at' => round(microtime(true) * 1000),
    ]);
    echo "Moved $driverId to [{$pos[0]}, {$pos[1]}] geohash=$geohash\n";
}

// Loop: update timestamps every 30 seconds
while (true) {
    $now = round(microtime(true) * 1000);
    foreach (array_keys($drivers) as $driverId) {
        $database->getReference("drivers/$driverId/updated_at")->set($now);
    }
    echo date('H:i:s') . " - Updated timestamps\n";
    sleep(30);
}
