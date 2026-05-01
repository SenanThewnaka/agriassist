<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\CropPlannerController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\ListingController as SellerListingController;
use App\Http\Controllers\Seller\LeadController as SellerLeadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DiseaseController::class , 'index'])->name('home');
Route::get('/detect', [DiseaseController::class , 'detect'])->name('detect');
Route::post('/analyze', [DiseaseController::class , 'analyze'])->name('analyze');
Route::get('/diagnosis/{diagnosis}', [DiseaseController::class, 'getDiagnosisHtml'])->name('diagnosis.html');
Route::get('/privacy', function() { return view('privacy'); })->name('privacy');

// Authentication Routes
Route::middleware(['guest', 'throttle:60,1'])->group(function () {
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
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Farm Management
    Route::post('/farms', [\App\Http\Controllers\FarmController::class, 'store'])->name('farms.store');
    Route::put('/farms/{farm}', [\App\Http\Controllers\FarmController::class, 'update'])->name('farms.update');
    Route::delete('/farms/{farm}', [\App\Http\Controllers\FarmController::class, 'destroy'])->name('farms.destroy');
    
    // Proxy Routes (Session Auth)
    Route::get('/proxy/geocode', [\App\Http\Controllers\FarmController::class, 'proxyGeocode'])->name('proxy.geocode');
    Route::get('/proxy/search', [\App\Http\Controllers\FarmController::class, 'proxySearch'])->name('proxy.search');
    Route::post('/proxy/soil-analysis', [\App\Http\Controllers\FarmController::class, 'uploadSoilReport'])->name('proxy.soil-analysis');
    
    Route::post('/farms/{farm}/soil-report', [\App\Http\Controllers\FarmController::class, 'uploadSoilReport'])->name('farms.soil-report');

    // API routes for the frontend (within auth middleware)
    Route::prefix('api')->group(function() {
        Route::post('/crop-plan', [CropPlannerController::class , 'apiCalculate']);
        Route::get('/planner/status/{jobId}', [CropPlannerController::class, 'apiCheckStatus']);
        Route::post('/planner/suggest-varieties', [CropPlannerController::class, 'apiSuggestVarieties']);
        Route::post('/planner/recommend-date', [CropPlannerController::class, 'apiRecommendDate']);
        Route::post('/save-crop-plan', [CropPlannerController::class , 'savePlan']);
        Route::post('/crop-tasks/{task}/toggle', [CropPlannerController::class , 'toggleTask']);
        Route::post('/soil-type', [CropPlannerController::class , 'getSoilType']);
        Route::post('/smart-suggestions', [CropPlannerController::class , 'getSmartSuggestions']);
        Route::get('/soil-by-district', [CropPlannerController::class , 'getSoilByDistrict']);
    });
});

// Non-auth routes can stay or move, but usually better together for consistency
Route::get('/api/crops/{crop}/varieties', [\App\Http\Controllers\CropController::class , 'getVarieties']);

// Seller Portal Routes
Route::prefix('seller')->name('seller.')->middleware(['auth', 'role:farmer,seller'])->group(function () {
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');
    Route::resource('listings', SellerListingController::class);
    Route::get('/leads', [SellerLeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{message}', [SellerLeadController::class, 'show'])->name('leads.show');
});

// Marketplace Public Routes
Route::prefix('marketplace')->name('marketplace.')->group(function () {
    Route::get('/', [\App\Http\Controllers\MarketplaceController::class, 'index'])->name('index');
    
    // Logged-in buyer routes
    Route::middleware('auth')->group(function() {
        Route::get('/messages', [\App\Http\Controllers\ChatController::class, 'index'])->name('messages.index');
        Route::post('/{listing}/inquire', [\App\Http\Controllers\MarketplaceController::class, 'inquire'])->name('inquire');
        Route::post('/{listing}/order', [\App\Http\Controllers\Buyer\OrderController::class, 'store'])->name('order.store');
        Route::get('/orders/{order}/chat', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat');
        Route::post('/orders/{order}/messages', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
        Route::get('/orders/{order}/messages', [\App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
        Route::post('/{listing}/review', [\App\Http\Controllers\ReviewController::class, 'store'])->name('review.store');
    });

    Route::get('/{listing}', [\App\Http\Controllers\MarketplaceController::class, 'show'])->name('show');
});

// Seller Portal Order Management
Route::prefix('seller')->name('seller.')->middleware(['auth', 'role:farmer,seller'])->group(function () {
    Route::post('/orders/{order}/accept', [\App\Http\Controllers\Seller\OrderManagementController::class, 'accept'])->name('orders.accept');
    Route::post('/orders/{order}/reject', [\App\Http\Controllers\Seller\OrderManagementController::class, 'reject'])->name('orders.reject');
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
Route::post('/planner/calculate', [CropPlannerController::class , 'apiCalculate'])->name('planner.calculate');
Route::post('/api/smart-suggestions', [CropPlannerController::class , 'getSmartSuggestions']);
