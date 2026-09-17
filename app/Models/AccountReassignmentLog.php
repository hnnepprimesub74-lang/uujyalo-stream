<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountReassignmentLog extends Model
{
    protected $fillable = ['subscription_id', 'old_shared_account_id', 'new_shared_account_id', 'customer_notified'];

    protected $casts = [
        'customer_notified' => 'boolean',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function oldSharedAccount(): BelongsTo
    {
        return $this->belongsTo(SharedAccount::class, 'old_shared_account_id');
    }

    public function newSharedAccount(): BelongsTo
    {
        return $this->belongsTo(SharedAccount::class, 'new_shared_account_id');
    }
}
