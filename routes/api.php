<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public user list
Route::get('/users', [UserController::class, 'index']);

// Admin user management
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/users/bulk-delete', [UserController::class, 'bulkDestroy']);

    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
