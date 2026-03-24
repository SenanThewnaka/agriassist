<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    protected $fillable = ['name', 'category', 'description'];

    public function varieties()
    {
        return $this->hasMany(CropVariety::class);
    }
}