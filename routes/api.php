<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\Api\V1\ClientController;
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
    // Routes d'authentification
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::middleware('auth:api')->post('/auth/logout', [AuthController::class, 'logout']);

    // Routes des comptes avec authentification et autorisation
    Route::middleware(['auth:api'])->group(function() {
        Route::get('/comptes', [CompteController::class, 'index'])->middleware('role:admin,client');
        Route::get('/comptes/{compteId}', [CompteController::class, 'show'])->middleware('role:admin,client');
        Route::get('/comptes/numero/{numeroCompte}', [CompteController::class, 'showByNumero'])->middleware('role:admin,client');
        Route::post('/comptes', [CompteController::class, 'store'])->middleware('role:admin');
        Route::put('/comptes/{id}', [CompteController::class, 'update'])->middleware('role:admin');
        Route::delete('/comptes/{id}', [CompteController::class, 'destroy'])->middleware('role:admin');
        Route::patch('/comptes/{id}/restore', [CompteController::class, 'restore'])->middleware('role:admin');
    });

    // Routes des clients
    Route::middleware(['auth:api'])->group(function() {
        Route::patch('/clients/{compteId}', [ClientController::class, 'update']);
        Route::get('/clients/telephone/{telephone}', [ClientController::class, 'showByTelephone'])->middleware('role:admin');
        Route::get('/clients/nci/{nci}', [ClientController::class, 'showByNci'])->middleware('role:admin');
    });
});
