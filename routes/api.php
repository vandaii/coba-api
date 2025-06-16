<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DirectPurchaseController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Resources\UserResource;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::post('/person', [PersonController::class, 'store']);

Route::post('register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function () {
        return new UserResource(Auth::user());
    });
    Route::post('/user/update', [AuthController::class, 'update']);
});

Route::prefix('direct-purchase')->group(function () {
    Route::post('/add', [DirectPurchaseController::class, 'store']);
});
