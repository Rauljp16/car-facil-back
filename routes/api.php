<?php

use App\Http\Controllers\CocheController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


Route::post("login", [AuthController::class, 'login']);
Route::get('/coches', [CocheController::class, 'index']);
Route::get('/coches/{id}', [CocheController::class, 'show']);


Route::middleware('auth:api')->group(function () {

    Route::post('/coches', [CocheController::class, 'store']);
    Route::put('/coches/{id}', [CocheController::class, 'update']);
    Route::delete('/coches/{id}', [CocheController::class, 'destroy']);

    Route::post('/coches/{cocheId}/images', [ImageController::class, 'store']);

    Route::post("register", [AuthController::class, 'register']);
    Route::post("logout", [AuthController::class, 'logout']);
});
