<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    protected $fillable = [
        'name', 'name_si', 'name_ta',
        'category',
        'description', 'description_si', 'description_ta',
        'ideal_months', 'climate_zone'
    ];

    protected $casts = [
        'ideal_months' => 'array',
    ];

    public function varieties()
    {
        return $this->hasMany(CropVariety::class);
    }
}
