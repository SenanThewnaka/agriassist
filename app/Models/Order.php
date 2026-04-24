<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Order Model
 * 
 * Tracks requests, negotiations, and transaction states between buyers and sellers.
 */
class Order extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'total_price',
        'order_status'
    ];

    /**
     * The user who placed the request.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * The owner of the listing.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Specific items included in this request.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Messages associated with this specific order request.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
