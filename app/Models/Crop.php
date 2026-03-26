<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    protected $fillable = ['name', 'category', 'description', 'ideal_months', 'climate_zone'];

    protected $casts = [
        'ideal_months' => 'array',
    ];

    public function varieties()
    {
        return $this->hasMany(CropVariety::class);
    }
}