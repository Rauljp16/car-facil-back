<?php

use App\Http\Controllers\CocheController;
use Illuminate\Support\Facades\Route;


Route::get('/coches', [CocheController::class, 'index']);
Route::post('/coches', [CocheController::class, 'store']);
Route::get('/coches/{id}', [CocheController::class, 'show']);
Route::put('/coches/{id}', [CocheController::class, 'update']);
Route::delete('/coches/{id}', [CocheController::class, 'destroy']);

