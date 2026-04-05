<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class WeatherAlert extends Model {
    protected $fillable = ['farm_id', 'alert_type', 'message', 'severity', 'alert_date'];
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
}