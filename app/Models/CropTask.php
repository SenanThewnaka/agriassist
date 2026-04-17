<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CropTask extends Model {
    protected $fillable = [
        'crop_season_id', 'task_name', 'task_name_si', 'task_name_ta', 
        'description', 'description_si', 'description_ta', 
        'stage', 'due_date', 'completed'
    ];
    public function cropSeason(): BelongsTo { return $this->belongsTo(CropSeason::class); }
}