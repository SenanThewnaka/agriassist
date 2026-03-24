<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CropVariety extends Model
{
    protected $fillable = ['crop_id', 'variety_name', 'growth_days', 'season', 'notes'];

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }
}