<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Listing extends Model {
    protected $fillable = ['seller_id', 'title', 'category', 'description', 'price', 'quantity', 'location'];
    public function seller(): BelongsTo { return $this->belongsTo(User::class, 'seller_id'); }
    public function orderItems(): HasMany { return $this->hasMany(OrderItem::class); }
}