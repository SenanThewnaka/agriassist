<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoilReport extends Model
{
    protected $fillable = [
        'farm_id',
        'file_path',
        'extracted_data',
        'analyzed_at'
    ];

    protected $casts = [
        'extracted_data' => 'array',
        'analyzed_at' => 'datetime'
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
