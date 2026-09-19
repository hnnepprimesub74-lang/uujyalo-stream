<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'image', 'category', 'description', 'highlight_note', 'plan_guidance', 'faqs',
        'external_link_url', 'external_link_label', 'trending', 'accent_color', 'perks',
        'is_active', 'is_shared_account', 'default_shared_slots',
    ];

    protected $appends = ['image_url', 'trending_items'];

    protected $hidden = ['trending'];

    protected $casts = [
        'faqs' => 'array',
        'trending' => 'array',
        'perks' => 'array',
        'is_active' => 'boolean',
        'is_shared_account' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    public function sharedAccounts(): HasMany
    {
        return $this->hasMany(SharedAccount::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function getTrendingItemsAttribute(): array
    {
        return collect($this->trending ?? [])
            ->map(fn (array $item) => [
                'title' => $item['title'] ?? null,
                'image_url' => ! empty($item['image']) ? Storage::disk('public')->url($item['image']) : null,
            ])
            ->values()
            ->all();
    }
}
