<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PestAlert extends Model {
    protected $fillable = ['farm_id', 'pest_name', 'risk_level', 'message', 'recommended_action'];
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
}