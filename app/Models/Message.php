<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Message Model
 * 
 * Handles P2P communication and marketplace lead generation.
 */
class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'listing_id',
        'order_id',
        'message'
    ];

    /**
     * The user who sent this message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * The user who received this message.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * (Optional) The marketplace listing this message is inquiring about.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    /**
     * (Optional) The specific order this message belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
