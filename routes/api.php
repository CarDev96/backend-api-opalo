<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\MenuSectionController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
	Route::get('/navigation', [MenuSectionController::class, 'navigation']);
    Route::post('/logout', [AuthController::class, 'logout']);

// Users Routes -----------------------------------------------

	Route::middleware('role:admin')->group(function () {
		Route::get('/users/dt', [\App\Http\Controllers\UserController::class, 'index']);
		Route::post('/users/store', [\App\Http\Controllers\UserController::class, 'store']);
		Route::post('/users/{id}/toggle-status', [\App\Http\Controllers\UserController::class, 'toggleStatus']);
		Route::delete('/users/{id}', [\App\Http\Controllers\UserController::class, 'destroy']);
	});

// --------------------------------------------------------------

    Route::middleware('role:admin,sales')->group(function () {
        Route::get('/clients', [ClientController::class, 'index']);
        Route::post('/clients/store', [ClientController::class, 'store']);
		Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
		Route::post('/clients/{id}/toggle-status', [ClientController::class, 'toggleStatus']);
    });
});
