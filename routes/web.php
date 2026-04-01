<?php

use App\Http\Controllers\CropController;
use App\Http\Controllers\CropPlannerController;
use App\Http\Controllers\DiseaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DiseaseController::class , 'index'])->name('home');
Route::get('/detect', [DiseaseController::class , 'detect'])->name('detect');
Route::post('/analyze', [DiseaseController::class , 'analyze'])->name('analyze');
Route::get('/diagnosis/{diagnosis}', [DiseaseController::class, 'getDiagnosisHtml'])->name('diagnosis.html');

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