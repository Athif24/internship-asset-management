<?php

use App\Http\Controllers\Api\AssetCategoryController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AssetLoanController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DivisionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthController::class, 'login']);

// Semua endpoint lain memerlukan token Sanctum.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/divisions', [DivisionController::class, 'index']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/asset-categories', [AssetCategoryController::class, 'index']);
    Route::get('/assets', [AssetController::class, 'index']);
    Route::get('/assets/{asset}', [AssetController::class, 'show']);
    Route::get('/asset-loans/history', [AssetLoanController::class, 'history']);

    // Asset Admin (Web)
    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/asset-categories', [AssetCategoryController::class, 'store']);
        Route::put('/asset-categories/{category}', [AssetCategoryController::class, 'update']);
        Route::delete('/asset-categories/{category}', [AssetCategoryController::class, 'destroy']);

        Route::post('/assets', [AssetController::class, 'store']);
        Route::put('/assets/{asset}', [AssetController::class, 'update']);
        Route::delete('/assets/{asset}', [AssetController::class, 'destroy']);

        Route::get('/dashboard/assets', [DashboardController::class, 'index']);
    });

    // Employee (Mobile)
    Route::middleware('role:USER')->group(function () {
        Route::post('/asset-loans', [AssetLoanController::class, 'borrow']);
        Route::post('/asset-loans/{id}/return', [AssetLoanController::class, 'returnLoan']);
    });
});
