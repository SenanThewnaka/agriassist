<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Listing Model
 * 
 * Represents a classified advertisement in the marketplace.
 * Supports localized harvest/tool listings with images and geolocation.
 */
class Listing extends Model
{
    protected $fillable = [
        'seller_id',
        'title',
        'category',
        'description',
        'images',
        'price',
        'status',
        'quantity',
        'unit',
        'location',
        'latitude',
        'longitude'
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'latitude' => 'float',
        'longitude' => 'float'
    ];

    // The seller who created this listing.
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Leads/Inquiries generated from this listing.
    public function inquiries(): HasMany
    {
        return $this->hasMany(Message::class, 'listing_id');
    }

    // Verified ratings and reviews for this product.
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Calculate average rating.
    public function getAverageRatingAttribute(): float
    {
        return (float) $this->reviews()->avg('rating') ?: 0;
    }

    // (Legacy/Optional) Transactional items if converted to full checkout model.
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
