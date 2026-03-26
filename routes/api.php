<?php

use App\Http\Controllers\CropPlannerController;
use Illuminate\Support\Facades\Route;

Route::post('/crop-plan', [CropPlannerController::class , 'apiCalculate']);
Route::post('/soil-type', [CropPlannerController::class , 'getSoilType']);
Route::post('/smart-suggestions', [CropPlannerController::class , 'getSmartSuggestions']);
Route::get('/soil-by-district', [CropPlannerController::class , 'getSoilByDistrict']);