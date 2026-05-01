<?php

use App\Http\Controllers\Api\FarmerProfileController;
use App\Http\Controllers\Api\FarmController;
use App\Http\Controllers\Api\CropSeasonController;
use App\Http\Controllers\Api\MerchantProfileController;
use App\Http\Controllers\Api\ListingController;
use Illuminate\Support\Facades\Route;

// Internal Crop Planner routes have been moved to web.php to support session-based auth

// Foundation Routes (Public or Third-Party API access)
Route::apiResource('farmer-profiles', FarmerProfileController::class);
Route::apiResource('farms', FarmController::class);
Route::apiResource('crop-seasons', CropSeasonController::class);
Route::apiResource('merchant-profiles', MerchantProfileController::class);
Route::apiResource('listings', ListingController::class);
