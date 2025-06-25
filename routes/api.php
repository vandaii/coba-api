<?php

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GRPOController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\Api\DirectPurchaseController;
use App\Http\Controllers\Api\TransferInController;
use App\Http\Controllers\Api\TransferOutController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::post('/person', [PersonController::class, 'store']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function () {
        return new UserResource(Auth::user());
    });
    Route::post('/user/update', [AuthController::class, 'update']);


    Route::prefix('direct-purchase')->group(function () {
        Route::get('/', [DirectPurchaseController::class, 'index']);
        Route::post('/add', [DirectPurchaseController::class, 'store']);
        Route::get('/{id}', [DirectPurchaseController::class, 'show']);
        Route::delete('/{id}/delete', [DirectPurchaseController::class, 'destroy']);
        Route::post('/{id}/approve-area-manager', [DirectPurchaseController::class, 'approveAreaManager']);
        Route::post('/{id}/approve-accounting', [DirectPurchaseController::class, 'approveAccounting']);
    });


    Route::prefix('purchase-order')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index']);
        Route::get('/{id}', [PurchaseOrderController::class, 'show']);
    });

    Route::prefix('grpo')->group(function () {
        Route::get('/', [GRPOController::class, 'index']);
        Route::get('/search', [GRPOController::class, 'search']);
        Route::get('/filter/shipping', [GRPOController::class, 'filterShipping']);
        Route::get('/filter/shipping/{id}', [GRPOController::class, 'showShipping']);
        Route::get('/filter/received', [GRPOController::class, 'filterReceived']);
        Route::get('/filter/received/{id}', [GRPOController::class, 'showreceived']);
        Route::post('/add', [GRPOController::class, 'store']);
    });

    Route::prefix('transfer-out')->group(function () {
        Route::get('/', [TransferOutController::class, 'index']);
        Route::post('/add', [TransferOutController::class, 'store']);
        Route::get('/{id}', [TransferOutController::class, 'show']);
    });

    Route::prefix('transfer-in')->group(function () {
        Route::get('/', [TransferInController::class, 'index']);
        Route::post('/add', [TransferInController::class, 'store']);
        Route::get('/{id}', [TransferInController::class, 'show']);
    });
});
