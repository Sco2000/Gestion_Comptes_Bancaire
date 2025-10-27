<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\Api\V1\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function(){
    // Routes d'authentification (non protégées)
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('/user', [AuthController::class, 'user'])->middleware('auth:api');

    // Routes des comptes (protégées par authentification)
    Route::middleware('auth:api')->group(function() {
        Route::get('/comptes', [CompteController::class, 'index']);
        Route::get('/comptes/{compteId}', [CompteController::class, 'show']);
        Route::post('/comptes', [CompteController::class, 'store']);
        Route::put('/comptes/{id}', [CompteController::class, 'update']);
        Route::delete('/comptes/{id}', [CompteController::class, 'destroy']);
    });
});
