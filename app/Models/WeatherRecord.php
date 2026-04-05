<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class WeatherRecord extends Model {
    protected $fillable = ['farm_id', 'temperature', 'humidity', 'rainfall', 'wind_speed', 'weather_condition', 'recorded_at'];
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
}