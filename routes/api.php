<?php
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\StockController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Public ────────────────────────────────────────────────────────────────
    Route::post('auth/login', [AuthController::class, 'login']);

    // ── Authenticated ─────────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout',           [AuthController::class, 'logout']);
        Route::get('auth/me',                [AuthController::class, 'me']);
        Route::post('auth/change-password',  [AuthController::class, 'changePassword']);

        // Stock
        Route::get('stock',                    [StockController::class, 'index']);
        Route::get('stock/{productCode}',      [StockController::class, 'show']);

        // News
        Route::get('news',       [NewsController::class, 'index']);
        Route::get('news/{id}',  [NewsController::class, 'show']);
    });
});
