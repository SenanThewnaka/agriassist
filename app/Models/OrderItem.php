<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OrderItem extends Model {
    protected $fillable = ['order_id', 'listing_id', 'quantity', 'price'];
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
}