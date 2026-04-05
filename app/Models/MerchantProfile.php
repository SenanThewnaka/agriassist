<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class MerchantProfile extends Model {
    protected $fillable = ['user_id', 'store_name', 'store_logo', 'description', 'store_location', 'phone', 'website', 'delivery_available'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}