<?php

namespace App\Services\Partners;

use App\Models\Request\Request;
use App\Models\Request\RequestMeta;

/**
 * When a passenger creates a new request while a previous one is still waiting
 * for a driver, the previous one is cancelled (historic behaviour of the create
 * endpoints). Integration partners create many deliveries concurrently from one
 * account, so for them nothing is cancelled.
 */
class WaitingRequestCanceller
{
    public static function cancelPreviousFor($user): void
    {
        if (PartnerRegistry::isPartner($user->id)) {
            return;
        }

        $metas = RequestMeta::where('user_id', $user->id);

        if (! $metas->exists()) {
            return;
        }

        $previousRequestId = (clone $metas)->pluck('request_id')->first();

        if ($previousRequestId) {
            // Model update (not a mass update) so model observers fire.
            Request::where('id', $previousRequestId)->first()?->update([
                'is_cancelled' => 1,
                'cancel_method' => 1,
                'cancelled_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $metas->delete();
    }
}
