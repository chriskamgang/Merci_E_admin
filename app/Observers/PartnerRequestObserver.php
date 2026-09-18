<?php

namespace App\Observers;

use App\Jobs\SendPartnerWebhook;
use App\Models\Request\Request;
use App\Models\Request\RequestBill;
use App\Services\Partners\PartnerRegistry;

/**
 * Emits outbound webhooks for requests owned by integration partner accounts.
 *
 * Every write path that changes the watched columns in this codebase goes through
 * Eloquent model updates ($request->update([...])), which fire this observer. The one
 * mass update (auto-cancel of a user's previous waiting request) now goes through
 * WaitingRequestCanceller, which uses a model update and never runs for partners.
 * Any NEW code that changes these columns with a query-builder ->update() would bypass
 * this observer.
 */
class PartnerRequestObserver
{
    public function updated(Request $request): void
    {
        if (! PartnerRegistry::isPartner($request->user_id)) {
            return;
        }

        // Order matters when several flags flip in one save.
        if ($request->wasChanged('driver_id') && $request->driver_id && $request->driver_id != $request->getOriginal('driver_id')) {
            $this->emit($request, SendPartnerWebhook::DRIVER_ASSIGNED);
        }
        if ($this->becameTrue($request, 'is_driver_arrived')) {
            $this->emit($request, SendPartnerWebhook::DRIVER_ARRIVED);
        }
        if ($this->becameTrue($request, 'is_trip_start')) {
            $this->emit($request, SendPartnerWebhook::TRIP_STARTED);
        }
        if ($this->becameTrue($request, 'is_completed')) {
            $this->emit($request, SendPartnerWebhook::COMPLETED);
        }
        if ($this->becameTrue($request, 'is_cancelled')) {
            $this->emit($request, SendPartnerWebhook::CANCELLED);
        }
    }

    public function billCreated(RequestBill $bill): void
    {
        if (empty(config('partners.partners'))) {
            return;
        }

        $request = Request::find($bill->request_id);

        if ($request && PartnerRegistry::isPartner($request->user_id)) {
            $this->emit($request, SendPartnerWebhook::BILL_AVAILABLE);
        }
    }

    private function becameTrue(Request $request, string $column): bool
    {
        return $request->wasChanged($column) && (bool) $request->{$column} && ! (bool) $request->getOriginal($column);
    }

    private function emit(Request $request, string $event): void
    {
        SendPartnerWebhook::dispatch($request->id, $event, now()->toIso8601String())->afterCommit();
    }
}
