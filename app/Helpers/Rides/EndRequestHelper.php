<?php

namespace App\Helpers\Rides;

use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;
use App\Base\Constants\Masters\UnitType;


trait EndRequestHelper
{

    /**
     * Calculate and charge Ride fare
     * @param Request $request_detail with generated bill
     * 
     */
    //
    protected function calculateDistanceAndDuration($distance_in_unit,$request_detail)
    {

        $trip_duration = $this->calculateDurationOfTrip($request_detail->trip_start_time);

        // The app-reported distance is driver supplied: never negative, and capped to what is
        // physically plausible for the server-measured trip time (see capImplausibleTripDistance).
        $app_distance = $this->capImplausibleTripDistance(max(0, round((float) $distance_in_unit, 2)), $request_detail);

        $distance_in_unit = $app_distance;
        $duration = $trip_duration;

        if($request_detail->requestEtaDetail()->exists()){
           
            $eta_detail = $request_detail->requestEtaDetail;

            $distance_in_unit = $eta_detail->total_distance;  
            $duration = $eta_detail->total_time;  
            
            if(get_settings('enable_eta_total_update') != "1" || $request_detail->is_rental || $request_detail->is_without_destination){

                $distance_in_unit = $app_distance;
                $duration = $trip_duration;
                
            }

        }

        

        return $distance_and_duration = ['distance'=>$distance_in_unit,'duration'=>$duration];

    
    }


    /**
     * Sanity cap for the driver-reported trip distance.
     *
     * Uses only server-side data: the elapsed time since requests.trip_start_time (set by the
     * server when the trip starts). A distance above elapsed_hours * 150 (km or miles, whichever
     * unit the zone uses) + 2 cannot have been driven, so it is capped and logged. The bound is
     * deliberately generous so legitimate fares are never reduced. When the start time is
     * unknown or in the future the value is returned unchanged.
     */
    protected function capImplausibleTripDistance($distance, $request_detail)
    {
        $max_speed_per_hour = 150;
        $allowance = 2;

        if (!$request_detail->trip_start_time) {
            return $distance;
        }

        $start_timestamp = strtotime($request_detail->trip_start_time);

        if (!$start_timestamp) {
            return $distance;
        }

        $elapsed_hours = (time() - $start_timestamp) / 3600;

        if ($elapsed_hours <= 0) {
            return $distance;
        }

        $max_distance = round($elapsed_hours * $max_speed_per_hour + $allowance, 2);

        if ($distance > $max_distance) {
            Log::warning('Driver-reported distance capped', [
                'request_id' => $request_detail->id,
                'reported_distance' => $distance,
                'capped_distance' => $max_distance,
                'elapsed_hours' => round($elapsed_hours, 3),
            ]);

            return $max_distance;
        }

        return $distance;
    }

    /**
     * Calculate Duration
     * @return $totald_duration number in minutes
     */
    protected function calculateDurationOfTrip($start_time)
    {

        $current_time = date('Y-m-d H:i:s');

        $start_time = Carbon::parse($start_time);
        // Log::info($start_time);
        $end_time = Carbon::parse($current_time);
        // Log::info($end_time);
        // $totald_duration = $end_time->diffInMinutes($start_time);
        $totald_duration = $start_time->diffInMinutes($end_time);
        Log::info($totald_duration);

        return $totald_duration;
    }


}
