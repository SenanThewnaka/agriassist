<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\CropPlannerController;
use App\Http\Controllers\DiseaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DiseaseController::class , 'index'])->name('home');
Route::get('/detect', [DiseaseController::class , 'detect'])->name('detect');
Route::post('/analyze', [DiseaseController::class , 'analyze'])->name('analyze');
Route::get('/diagnosis/{diagnosis}', [DiseaseController::class, 'getDiagnosisHtml'])->name('diagnosis.html');
Route::get('/privacy', function() { return view('privacy'); })->name('privacy');

// Authentication Routes
Route::middleware(['guest', 'throttle:6,1'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Google Auth
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Farm Management
    Route::post('/farms', [\App\Http\Controllers\FarmController::class, 'store'])->name('farms.store');
    Route::patch('/farms/{farm}', [\App\Http\Controllers\FarmController::class, 'update'])->name('farms.update');
    Route::delete('/farms/{farm}', [\App\Http\Controllers\FarmController::class, 'destroy'])->name('farms.destroy');

    // Privacy Proxy Routes
    Route::get('/proxy/geocode', [\App\Http\Controllers\FarmController::class, 'proxyGeocode']);
    Route::get('/proxy/search', [\App\Http\Controllers\FarmController::class, 'proxySearch']);
});

Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'si', 'ta'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    
    if (request()->query('json')) {
        return response()->json(['success' => true, 'locale' => $locale]);
    }
    
    return redirect()->back();
})->name('lang.switch');

// Crop Planner Routes
Route::get('/planner', [CropPlannerController::class , 'index'])->name('planner.index');
Route::post('/planner/calculate', [CropPlannerController::class , 'calculate'])->name('planner.calculate');
Route::get('/crops/{crop}/varieties', [CropController::class , 'getVarieties'])->name('crops.varieties');