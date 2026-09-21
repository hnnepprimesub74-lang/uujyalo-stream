<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignDelivery extends Model
{
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'campaign_id', 'user_id', 'contact_id', 'channel', 'status', 'response',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(MarketingContact::class, 'contact_id');
    }

    /**
     * The recipient's display name, whichever type this delivery is for.
     */
    public function recipientName(): string
    {
        return $this->user?->name ?? $this->contact?->name ?? $this->contact?->email ?? $this->contact?->phone ?? '—';
    }
}
