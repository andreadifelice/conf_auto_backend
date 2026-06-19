<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarColorController;
use App\Http\Controllers\CarModelController;
use App\Http\Controllers\CarModelEngineController;
use App\Http\Controllers\CarModelOptionalController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\EngineController;
use App\Http\Controllers\OptionalController;
use App\Http\Controllers\OptionalCompatibilityController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'welcome']);
});


Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/admin/login', 'adminLogin');
});


Route::controller(PasswordResetController::class)->group(function(){
    Route::post('/forgot-password', 'forgotPassword');
    Route::post('/reset-password', 'resetPassword');
});


Route::get('/car-models', [CarModelController::class, 'index']);
Route::get('/car-models/{id}', [CarModelController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/engines', [EngineController::class, 'index']);
Route::get('/optionals', [OptionalController::class, 'index']);


Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('admin')->group(function (){
    Route::get('/colors', [ColorController::class, 'index']);
    Route::get('/colors/{color}', [ColorController::class, 'show']);
    Route::get('/car-models', [CarModelController::class, 'index']);
    Route::get('/optionals/{optional}', [OptionalController::class, 'show']);
    Route::get('/car-model-engines', [CarModelEngineController::class, 'index']);
    Route::get('/car-model-engines/{carModel}/{engine}', [CarModelEngineController::class, 'show']);
    Route::get('/car-model-optionals', [CarModelOptionalController::class, 'index']);
    Route::get('/car-model-optionals/{carModel}/{optional}', [CarModelOptionalController::class, 'show']);
    Route::get('/car-colors', [CarColorController::class, 'index']);
    Route::get('/car-colors/{carModel}/{color}', [CarColorController::class, 'show']);
    Route::get('/optional-compatibilities', [OptionalCompatibilityController::class, 'index']);
    Route::get('/optional-compatibilities/{optional}/{type}/{relatedOptional}', [OptionalCompatibilityController::class, 'show'])
        ->whereIn('type', ['requires', 'excludes']);


    Route::post('/colors', [ColorController::class, 'store']);
    Route::post('/engines', [EngineController::class, 'store']);
    Route::post('/car-model-engines', [CarModelEngineController::class, 'store']);
    Route::post('/car-model-optionals', [CarModelOptionalController::class, 'store']);
    Route::post('/optionals', [OptionalController::class, 'store']);
    Route::post('/car-colors', [CarColorController::class, 'store']);
    Route::post('/optional-compatibilities', [OptionalCompatibilityController::class, 'store']);
    Route::post('/car-models', [CarModelController::class, 'store']);
    Route::post('/categories', [CategoryController::class, 'store']);


    Route::put('/optionals/{optional}', [OptionalController::class, 'update']);
    Route::put('/car-models/{carModel}', [CarModelController::class, 'update']);
    Route::put('/engines/{engine}', [EngineController::class, 'update']);
    Route::put('/colors/{color}', [ColorController::class, 'update']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::put('/car-model-engines/{carModel}/{engine}', [CarModelEngineController::class, 'update']);
    Route::put('/car-model-optionals/{carModel}/{optional}', [CarModelOptionalController::class, 'update']);
    Route::put('/car-colors/{carModel}/{color}', [CarColorController::class, 'update']);
    Route::put('/optional-compatibilities/{optional}/{type}/{relatedOptional}', [OptionalCompatibilityController::class, 'update'])
        ->whereIn('type', ['requires', 'excludes']);
    
    
    Route::delete('/car-models/{carModel}', [CarModelController::class, 'destroy']);
    Route::delete('/engines/{engine}', [EngineController::class, 'destroy']);
    Route::delete('/optionals/{optional}', [OptionalController::class, 'destroy']);
    Route::delete('/colors/{color}', [ColorController::class, 'destroy']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    Route::delete('/car-model-engines/{carModel}/{engine}', [CarModelEngineController::class, 'destroy']);
    Route::delete('/car-model-optionals/{carModel}/{optional}', [CarModelOptionalController::class, 'destroy']);
    Route::delete('/car-colors/{carModel}/{color}', [CarColorController::class, 'destroy']);
    Route::delete('/optional-compatibilities/{optional}/{type}/{relatedOptional}', [OptionalCompatibilityController::class, 'destroy'])
        ->whereIn('type', ['requires', 'excludes']);
});


Route::middleware('auth:sanctum')->group(function() {
    Route::post('/email/send-otp', [EmailVerificationController::class, 'resend']);
    Route::post('/email/verify-otp', [EmailVerificationController::class, 'verify']);
    Route::get('/configurations', [ConfigurationController::class, 'index']);

    
    Route::middleware(['verified'])->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::apiResource('users', UserController::class)->only(['index', 'update']);


        Route::get('/configurations/{id}/pdf', [ConfigurationController::class, 'downloadReceipt']);
        Route::get('/configurations', [ConfigurationController::class, 'index']);


        Route::post('/configurations', [ConfigurationController::class, 'store']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});