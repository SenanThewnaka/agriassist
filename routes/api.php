<?php

use App\Http\Controllers\CropPlannerController;
use Illuminate\Support\Facades\Route;

Route::post('/crop-plan', [CropPlannerController::class , 'apiCalculate']);