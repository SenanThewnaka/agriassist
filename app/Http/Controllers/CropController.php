<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use Illuminate\Http\Request;

class CropController extends Controller
{
    public function getVarieties($cropId)
    {
        $crop = Crop::with('varieties')->find($cropId);

        if (!$crop) {
            return response()->json([], 404);
        }

        return response()->json($crop->varieties);
    }
}