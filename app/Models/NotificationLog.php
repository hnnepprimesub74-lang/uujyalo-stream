<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory;

    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';

    public const TYPE_EXPIRING_SOON = 'expiring_soon';
    public const TYPE_EXPIRED = 'expired';

    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id', 'subscription_id', 'channel', 'type', 'status', 'message', 'response',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
