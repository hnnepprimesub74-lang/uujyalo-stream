<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_ACTIVE_SUBSCRIBERS = 'active_subscribers';
    public const AUDIENCE_BY_PLAN = 'by_plan';

    protected $fillable = [
        'subject', 'email_body', 'sms_body', 'send_email', 'send_sms',
        'audience_filter', 'audience_plan_id', 'status', 'scheduled_at', 'sent_at',
        'total_recipients', 'sent_count', 'failed_count', 'batch_id', 'created_by',
    ];

    protected $casts = [
        'send_email' => 'boolean',
        'send_sms' => 'boolean',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(CampaignDelivery::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'audience_plan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The audience a campaign will be sent to, always excluding inactive,
     * non-customer, and opted-out users regardless of the chosen filter.
     */
    public function audienceQuery(): Builder
    {
        return User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->where('is_active', true)
            ->where('marketing_opt_out', false)
            ->when($this->audience_filter === self::AUDIENCE_ACTIVE_SUBSCRIBERS, function (Builder $query) {
                $query->whereHas('activeSubscriptions');
            })
            ->when($this->audience_filter === self::AUDIENCE_BY_PLAN && $this->audience_plan_id, function (Builder $query) {
                $query->whereHas('subscriptions', function (Builder $subscriptions) {
                    $subscriptions->where('plan_id', $this->audience_plan_id)
                        ->where('status', Subscription::STATUS_ACTIVE);
                });
            });
    }
}
