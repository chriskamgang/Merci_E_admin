<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use App\Models\Admin\Setting;
use Illuminate\Http\Request;
use App\Models\ThirdPartySetting;

class SmsGatewayController  extends Controller
{
    public function index() 
    {
        $settings = ThirdPartySetting::where('module', 'sms')->pluck('value', 'name')->toArray();

        $settings['enable_nexah'] = filter_var($settings['enable_nexah'] ?? false, FILTER_VALIDATE_BOOLEAN);
        // Firebase phone auth: when on, both apps (and the web portal) verify the phone with
        // Firebase and send a Firebase ID token, verified server-side (FirebasePhoneVerifier).
        $settings['enable_firebase_otp'] = filter_var($settings['enable_firebase_otp'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return Inertia::render('pages/sms_gateway/index', [
            'app_for'=>env('APP_FOR'),
            'settings' => $settings,
        ]);
    }
    
    public function update(Request $request)
    {
// dd($request->all());
    $settings = $request->only([
            'enable_nexah','nexah_user','nexah_password','nexah_sender_id','enable_firebase_otp',
        ]);

        // Only one OTP channel can be active: get_active_sms_settings() returns the first
        // row with value "1", and the apps read enable_firebase_otp directly.
        if (filter_var($settings['enable_firebase_otp'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $settings['enable_nexah'] = '0';
            $settings['enable_firebase_otp'] = '1';
        } else {
            $settings['enable_firebase_otp'] = '0';
        }


        ThirdPartySetting::where('module', 'sms')->delete(); // corrected delete command


        foreach ($settings as $key => $setting) 
        {
            // dd($setting);

            ThirdPartySetting::create(['name' => $key, 'value' => $setting, 'module' => 'sms']);                 
        }

        return response()->json(['message' => 'Sms  Destails updated successfully'], 201);

    }
}
