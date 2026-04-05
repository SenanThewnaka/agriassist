<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class CropSeason extends Model {
    protected $fillable = ['farm_id', 'crop_name', 'crop_variety', 'planting_date', 'expected_harvest_date', 'crop_stage', 'notes'];
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function activities(): HasMany { return $this->hasMany(FarmActivity::class); }
    public function tasks(): HasMany { return $this->hasMany(CropTask::class); }
}