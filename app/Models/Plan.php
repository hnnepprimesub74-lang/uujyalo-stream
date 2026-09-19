<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'type', 'badge_label', 'name', 'slug', 'description', 'duration_days', 'device_slots',
        'device_slots_max', 'device_label', 'quality', 'supported_devices', 'price', 'monthly_cost', 'features',
        'usage_rules', 'is_active', 'sort_order',
    ];

    protected $appends = ['full_name'];

    protected $casts = [
        'features' => 'array',
        'usage_rules' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'monthly_cost' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Plan $plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->type ? "{$this->type} - {$this->name}" : $this->name;
    }

    public function hasFixedDeviceSlots(): bool
    {
        return ! is_null($this->device_slots);
    }
}
