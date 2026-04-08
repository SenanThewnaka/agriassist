<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    protected $fillable = [
        'user_id',
        'farm_id',
        'image_paths',
        'disease',
        'confidence',
        'severity',
        'spread_risk',
        'engine_tier',
        'treatment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'image_paths' => 'array',
    ];
}