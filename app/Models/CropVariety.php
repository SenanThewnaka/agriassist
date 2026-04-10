<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CropVariety extends Model
{
    protected $fillable = [
        'crop_id',
        'variety_name', 'variety_name_si', 'variety_name_ta',
        'growth_days', 'season',
        'notes', 'notes_si', 'notes_ta',
        'soil_types', 'min_temp', 'max_temp', 'min_rainfall', 'water_requirement',
        'yield_per_acre_kg', 'seed_per_acre_kg', 'base_market_price_per_kg'
    ];

    protected $casts = [
        'soil_types' => 'array',
    ];

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    public function stages()
    {
        return $this->hasMany(CropStage::class);
    }
}
