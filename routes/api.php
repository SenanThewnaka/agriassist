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
Route::post('/planner/suggest-varieties', [CropPlannerController::class, 'apiSuggestVarieties']);
Route::post('/save-crop-plan', [CropPlannerController::class , 'savePlan'])->middleware('auth:sanctum');
Route::post('/crop-tasks/{task}/toggle', [CropPlannerController::class , 'toggleTask'])->middleware('auth:sanctum');
Route::post('/soil-type', [CropPlannerController::class , 'getSoilType']);
Route::post('/smart-suggestions', [CropPlannerController::class , 'getSmartSuggestions']);
Route::get('/soil-by-district', [CropPlannerController::class , 'getSoilByDistrict']);

// New Foundation Routes
Route::apiResource('farmer-profiles', FarmerProfileController::class);
Route::apiResource('farms', FarmController::class);
Route::apiResource('crop-seasons', CropSeasonController::class);
Route::apiResource('merchant-profiles', MerchantProfileController::class);
Route::apiResource('listings', ListingController::class);