<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class FarmActivity extends Model {
    protected $fillable = ['farm_id', 'crop_season_id', 'activity_type', 'description', 'quantity', 'activity_date'];
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function cropSeason(): BelongsTo { return $this->belongsTo(CropSeason::class); }
}