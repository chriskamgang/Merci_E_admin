<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Admin\Driver;

class PawaPayTransaction extends Model
{
    protected $table = 'pawapay_transactions';

    protected $fillable = [
        'transaction_id',   // UUID = PawaPay depositId / payoutId
        'type',             // 'deposit' | 'payout'
        'user_id',          // users.id
        'driver_id',        // drivers.id (nullable, for payouts)
        'amount',
        'currency',
        'phone',
        'provider',         // MTN_MOMO_CMR | ORANGE_CMR
        'status',           // pending | completed | failed
        'pawapay_status',   // raw status from PawaPay (ACCEPTED, COMPLETED, FAILED…)
        'failure_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
