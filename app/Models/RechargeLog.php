<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RechargeLog extends Model
{
    protected $fillable = [
        'subscription_id', 'amount', 'days_added', 'recharged_at', 'recharged_by', 'note',
    ];

    protected $casts = [
        'recharged_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function recharger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recharged_by');
    }
}
