<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class FarmerProfile extends Model {
    protected $fillable = ['user_id', 'farm_size', 'farming_type', 'irrigation_type', 'experience_years', 'main_crops'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}