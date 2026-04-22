<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Review Model
 * 
 * Stores verified buyer ratings and text reviews for marketplace listings.
 */
class Review extends Model
{
    protected $fillable = [
        'listing_id',
        'user_id',
        'rating',
        'comment'
    ];

    /**
     * The listing being reviewed.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * The buyer who wrote the review.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
