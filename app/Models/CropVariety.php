<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CropVariety extends Model
{
    protected $fillable = [
        'crop_id', 'variety_name', 'growth_days', 'season', 'notes',
        'soil_types', 'min_temp', 'max_temp', 'min_rainfall', 'water_requirement'
    ];

    protected $casts = [
        'soil_types' => 'array',
    ];

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }
}