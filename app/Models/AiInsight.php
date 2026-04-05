<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AiInsight extends Model {
    protected $fillable = ['user_id', 'farm_id', 'insight_type', 'insight_text'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
}