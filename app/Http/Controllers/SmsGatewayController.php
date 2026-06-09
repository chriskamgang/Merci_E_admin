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

        return Inertia::render('pages/sms_gateway/index', [
            'app_for'=>env('APP_FOR'),
            'settings' => $settings,
        ]);
    }
    
    public function update(Request $request)
    {
// dd($request->all());
    $settings = $request->only([
            'enable_nexah','nexah_user','nexah_password','nexah_sender_id',
        ]);


        ThirdPartySetting::where('module', 'sms')->delete(); // corrected delete command


        foreach ($settings as $key => $setting) 
        {
            // dd($setting);

            ThirdPartySetting::create(['name' => $key, 'value' => $setting, 'module' => 'sms']);                 
        }

        return response()->json(['message' => 'Sms  Destails updated successfully'], 201);

    }
}
