<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CropStage extends Model
{
    protected $fillable = [
        'crop_variety_id',
        'name', 'name_si', 'name_ta',
        'days_offset', 'icon',
        'advice', 'advice_si', 'advice_ta',
        'description', 'description_si', 'description_ta',
        'urea_per_acre_kg', 'tsp_per_acre_kg', 'mop_per_acre_kg'
    ];

    public function variety()
    {
        return $this->belongsTo(CropVariety::class);
    }
}
