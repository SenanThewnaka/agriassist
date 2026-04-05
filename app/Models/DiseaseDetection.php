<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DiseaseDetection extends Model {
    protected $fillable = ['user_id', 'farm_id', 'crop_name', 'disease_name', 'confidence_score', 'treatment_recommendation', 'image_path', 'ai_model_used'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
}