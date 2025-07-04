<?php

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GRPOController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\DirectPurchaseController;
use App\Http\Controllers\Api\MaterialRequestController;
use App\Http\Controllers\Api\StockOpnameController;
use App\Http\Controllers\Api\TransferInController;
use App\Http\Controllers\Api\TransferOutController;
use App\Http\Controllers\Api\WasteController;

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
        Route::delete('/{id}/reject', [DirectPurchaseController::class, 'rejectApprove']);
        Route::put('/{id}/revision', [DirectPurchaseController::class, 'revision']);
        Route::post('/{id}/update', [DirectPurchaseController::class, 'update']);
    });


    Route::prefix('purchase-order')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index']);
        Route::get('/{id}', [PurchaseOrderController::class, 'show']);
    });

    Route::prefix('grpo')->group(function () {
        Route::get('/', [GRPOController::class, 'index']);
        Route::post('/add', [GRPOController::class, 'store']);
        Route::get('/search', [GRPOController::class, 'search']);
        Route::get('/shipping', [GRPOController::class, 'shipping']);
        Route::get('/{id}', [GRPOController::class, 'show']);
        Route::get('/shipping/{id}', [GRPOController::class, 'showShipping']);
    });

    Route::prefix('transfer-out')->group(function () {
        Route::get('/', [TransferOutController::class, 'index']);
        Route::post('/add', [TransferOutController::class, 'store']);
        Route::get('/{id}', [TransferOutController::class, 'show']);
    });

    Route::prefix('transfer-in')->group(function () {
        Route::get('/', [TransferInController::class, 'index']);
        Route::get('/available-transfer-outs', [TransferInController::class, 'availableTransferOuts']);
        Route::post('/add', [TransferInController::class, 'store']);
        Route::get('/{id}', [TransferInController::class, 'show']);
    });

    Route::prefix('stock-opname')->group(function () {
        Route::get('/', [StockOpnameController::class, 'index']);
        Route::post('/add', [StockOpnameController::class, 'store']);
        Route::get('/{id}', [StockOpnameController::class, 'show']);
    });

    Route::prefix('material-request')->group(function () {
        Route::get('/', [MaterialRequestController::class, 'index']);
        Route::post('/add', [MaterialRequestController::class, 'store']);
        Route::get('/{id}', [MaterialRequestController::class, 'show']);
        Route::post('/{id}/approve-area-manager', [MaterialRequestController::class, 'approveAreaManager']);
        Route::post('/{id}/approve-accounting', [MaterialRequestController::class, 'approveAccounting']);
    });

    Route::prefix('waste')->group(function () {
        Route::get('/', [WasteController::class, 'index']);
        Route::post('/add', [WasteController::class, 'store']);
        Route::get('/{id}', [WasteController::class, 'show']);
        Route::put('/{id}/approve-area-manager', [WasteController::class, 'approveAreaManager']);
        Route::put('/{id}/approve-accounting', [WasteController::class, 'approveAccounting']);
    });
});
