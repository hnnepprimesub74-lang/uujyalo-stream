<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class SharedAccount extends Model
{
    protected $fillable = [
        'product_id', 'email', 'password', 'max_slots',
        'purchased_at', 'duration_days', 'notes', 'is_active',
    ];

    protected $casts = [
        'email' => 'encrypted',
        'password' => 'encrypted',
        'is_active' => 'boolean',
        'purchased_at' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function usedSlots(): int
    {
        return (int) $this->subscriptions()
            ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_PENDING])
            ->sum('slots_used');
    }

    public function availableSlots(): int
    {
        return max(0, $this->max_slots - $this->usedSlots());
    }

    public function hasAvailableSlot(): bool
    {
        return $this->is_active && $this->availableSlots() > 0;
    }

    public function accountExpiresAt(): ?Carbon
    {
        if (! $this->purchased_at || ! $this->duration_days) {
            return null;
        }

        return $this->purchased_at->copy()->addDays($this->duration_days);
    }

    public function daysUntilAccountExpiry(): ?int
    {
        $expiresAt = $this->accountExpiresAt();

        if (! $expiresAt) {
            return null;
        }

        return Carbon::today()->diffInDays($expiresAt, false);
    }

    /**
     * True when this account is nearing/past its own paid duration while still
     * serving customers — a signal to rotate in fresh credentials.
     */
    public function needsRotation(int $warningDays = 5): bool
    {
        $daysLeft = $this->daysUntilAccountExpiry();

        return $daysLeft !== null && $daysLeft <= $warningDays && $this->usedSlots() > 0;
    }
}
