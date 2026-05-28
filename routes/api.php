<?php

use App\Http\Controllers\CarModelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'welcome']);
});

Route::get('/car-models', [CarModelController::class, 'index']);

Route::get('car-models/{id}', [CarModelController::class, 'show']);