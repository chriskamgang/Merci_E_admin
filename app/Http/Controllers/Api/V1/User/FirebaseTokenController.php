<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Base\Constants\Auth\Role;
use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FirebaseTokenController extends ApiController
{
    /**
     * Mint a Firebase custom token for the logged in account.
     *
     * The Realtime Database rules require an authenticated Firebase client
     * (auth != null). The apps log in through the Laravel API (password or
     * Nexah SMS OTP), so they exchange their Sanctum token for this custom
     * token and call signInWithCustomToken() before touching the database.
     *
     * uid matches the RTDB node key: driver_<drivers.id>, user_<users.id>,
     * owner_<owners.id>.
     *
     * @group User-Management
     *
     * @return JsonResponse
     */
    public function token()
    {
        $user = auth()->user();

        if ($user->hasRole(Role::DRIVER) && $user->driver) {
            $uid = 'driver_'.$user->driver->id;
            $role = 'driver';
        } elseif ($user->hasRole(Role::OWNER) && $user->owner) {
            $uid = 'owner_'.$user->owner->id;
            $role = 'owner';
        } else {
            $uid = 'user_'.$user->id;
            $role = 'user';
        }

        try {
            $token = app('firebase.auth')->createCustomToken($uid, ['role' => $role]);
        } catch (\Throwable $e) {
            Log::error('Firebase custom token failed: '.$e->getMessage());

            return $this->respondFailed('Unable to create firebase token');
        }

        return $this->respondSuccess(['firebase_token' => $token->toString(), 'uid' => $uid]);
    }
}
