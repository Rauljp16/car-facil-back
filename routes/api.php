<?php

use App\Http\Controllers\CocheController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Rutas públicas (no requieren autenticación)
Route::post("login", [AuthController::class, 'login']);

// Rutas protegidas por autenticación JWT
Route::middleware('auth:api')->group(function () {

    Route::get('/coches', [CocheController::class, 'index']);
    Route::post('/coches', [CocheController::class, 'store']);
    Route::get('/coches/{id}', [CocheController::class, 'show']);
    Route::put('/coches/{id}', [CocheController::class, 'update']);
    Route::delete('/coches/{id}', [CocheController::class, 'destroy']);


    Route::post("register", [AuthController::class, 'register']);
    Route::post("logout", [AuthController::class, 'logout']);
});
