<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_number', 'user_id', 'plan_id', 'sale_source_id', 'shared_account_id', 'slots_used', 'status', 'starts_at', 'expires_at',
        'account_email', 'account_password',
        'amount', 'coupon_code', 'discount_amount', 'credit_due', 'total_days', 'days_recharged', 'last_recharge_date', 'next_recharge_date',
        'admin_note', 'expiring_reminder_sent', 'expired_reminder_sent',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_recharge_date' => 'date',
        'next_recharge_date' => 'date',
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'credit_due' => 'decimal:2',
        'account_email' => 'encrypted',
        'account_password' => 'encrypted',
        'expiring_reminder_sent' => 'boolean',
        'expired_reminder_sent' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Subscription $subscription) {
            $subscription->order_number ??= static::generateOrderNumber();
        });

        static::saving(function (Subscription $subscription) {
            $plan = $subscription->plan ?? Plan::find($subscription->plan_id);

            if ($plan?->hasFixedDeviceSlots()) {
                $subscription->slots_used = $plan->device_slots;
            }
        });
    }

    /**
     * A short 5-digit order id, derived from the current date/time (plus a
     * random jitter to avoid collisions within the same second).
     */
    public static function generateOrderNumber(): string
    {
        do {
            $seed = now()->format('YmdHis').random_int(0, 999);
            $candidate = str_pad((string) (crc32($seed) % 100000), 5, '0', STR_PAD_LEFT);
        } while (static::where('order_number', $candidate)->exists());

        return $candidate;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function saleSource(): BelongsTo
    {
        return $this->belongsTo(SaleSource::class);
    }

    public function sharedAccount(): BelongsTo
    {
        return $this->belongsTo(SharedAccount::class);
    }

    public function displayAccountEmail(): ?string
    {
        return $this->sharedAccount?->email ?? $this->account_email;
    }

    public function displayAccountPassword(): ?string
    {
        return $this->sharedAccount?->password ?? $this->account_password;
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function rechargeLogs(): HasMany
    {
        return $this->hasMany(RechargeLog::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function originalAmount(): float
    {
        return (float) $this->amount + (float) $this->discount_amount;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }

    /**
     * Payment has been approved but no account (shared slot or private
     * credentials) has been assigned yet — the countdown hasn't started.
     */
    public function isAwaitingActivation(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->starts_at === null;
    }

    public function remainingDaysToCover(): int
    {
        return max(0, ($this->total_days ?? 0) - $this->days_recharged);
    }

    public function daysUntilExpiry(): int
    {
        if (! $this->expires_at || $this->expires_at->isPast()) {
            return 0;
        }

        return (int) Carbon::today()->diffInDays($this->expires_at);
    }

    /**
     * Days until the next recharge is due (a live countdown). Negative means
     * overdue by that many days. Null means fully covered — no recharge needed.
     */
    public function remainingRechargeDays(): ?int
    {
        if (! $this->next_recharge_date) {
            return null;
        }

        $today = Carbon::today();
        $due = $this->next_recharge_date->copy()->startOfDay();

        return $due->isPast() && ! $due->isToday()
            ? -$today->diffInDays($due)
            : $today->diffInDays($due);
    }

    public function needsRecharge(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->remainingDaysToCover() > 0
            && $this->next_recharge_date !== null
            && $this->next_recharge_date->lessThanOrEqualTo(Carbon::today());
    }

    public function scopeNeedsRecharge($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereColumn('days_recharged', '<', 'total_days')
            ->whereNotNull('next_recharge_date')
            ->where('next_recharge_date', '<=', Carbon::today());
    }
}
