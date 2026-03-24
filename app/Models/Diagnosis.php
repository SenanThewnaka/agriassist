<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    protected $fillable = [
        'image_paths',
        'disease',
        'confidence',
        'treatment',
    ];

    protected $casts = [
        'image_paths' => 'array',
    ];
}