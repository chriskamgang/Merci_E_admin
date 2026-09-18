<?php

namespace App\Models\Admin;

use App\Base\Uuid\UuidModel;
use Illuminate\Database\Eloquent\Model;

class CovoiturageRoutePrice extends Model
{
    use UuidModel;

    protected $table = 'covoiturage_route_prices';

    protected $fillable = [
        'origin_name', 'origin_lat', 'origin_lng',
        'destination_name', 'destination_lat', 'destination_lng',
        'radius_km', 'fixed_price', 'active', 'bidirectional',
    ];

    protected $casts = [
        'active' => 'boolean',
        'bidirectional' => 'boolean',
    ];

    /**
     * Find fixed price for a route based on pickup/dropoff coordinates
     */
    public static function findRoutePrice($pickLat, $pickLng, $dropLat, $dropLng)
    {
        $routes = self::where('active', true)->get();

        foreach ($routes as $route) {
            $pickToOrigin = self::haversineDistance($pickLat, $pickLng, $route->origin_lat, $route->origin_lng);
            $dropToDest = self::haversineDistance($dropLat, $dropLng, $route->destination_lat, $route->destination_lng);

            if ($pickToOrigin <= $route->radius_km && $dropToDest <= $route->radius_km) {
                return $route->fixed_price;
            }

            // Check reverse direction if bidirectional
            if ($route->bidirectional) {
                $pickToDest = self::haversineDistance($pickLat, $pickLng, $route->destination_lat, $route->destination_lng);
                $dropToOrigin = self::haversineDistance($dropLat, $dropLng, $route->origin_lat, $route->origin_lng);

                if ($pickToDest <= $route->radius_km && $dropToOrigin <= $route->radius_km) {
                    return $route->fixed_price;
                }
            }
        }

        return null;
    }

    /**
     * Haversine distance in km
     */
    private static function haversineDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
              cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
              sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
