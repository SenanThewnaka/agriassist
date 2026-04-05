<?php

use App\Http\Controllers\Api\FarmerProfileController;
use App\Http\Controllers\Api\FarmController;
use App\Http\Controllers\Api\CropSeasonController;
use App\Http\Controllers\Api\MerchantProfileController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\CropPlannerController;
use Illuminate\Support\Facades\Route;

// Existing Crop Planner Routes
Route::post('/crop-plan', [CropPlannerController::class , 'apiCalculate']);
Route::post('/soil-type', [CropPlannerController::class , 'getSoilType']);
Route::post('/smart-suggestions', [CropPlannerController::class , 'getSmartSuggestions']);
Route::get('/soil-by-district', [CropPlannerController::class , 'getSoilByDistrict']);

// New Foundation Routes
Route::apiResource('farmer-profiles', FarmerProfileController::class);
Route::apiResource('farms', FarmController::class);
Route::apiResource('crop-seasons', CropSeasonController::class);
Route::apiResource('merchant-profiles', MerchantProfileController::class);
Route::apiResource('listings', ListingController::class);