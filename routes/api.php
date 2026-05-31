<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarModelController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'welcome']);
});


Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});


Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('api.verification.verify');


Route::get('/car-models', [CarModelController::class, 'index']);
Route::get('car-models/{id}', [CarModelController::class, 'show']);


Route::middleware('auth:sanctum')->group(function() {
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend']);

    Route::get('/configurations', [ConfigurationController::class, 'index']);
    Route::post('/configurations', [ConfigurationController::class, 'store']);


    Route::middleware(['verified'])->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::apiResource('users', UserController::class)->only(['index', 'update']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});
    