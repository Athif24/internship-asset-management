<?php

use App\Http\Controllers\Web\AssetController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LoanController;
use App\Http\Controllers\Web\LoginController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — UI Admin Project B (Blade server-render)
|--------------------------------------------------------------------------
| Halaman dan data awal dirender server. Autentikasi Web Admin memakai
| session Laravel tanpa remember-me; Mobile tetap memakai Sanctum di /api.
| Route "/" hanya mengarahkan: guest ke /login, ADMIN ke /dashboard.
*/

Route::get('/', function () {
    if (Auth::check() && Auth::user()->isAdmin()) {
        return redirect('/dashboard');
    }

    return redirect('/login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::middleware(['auth:web', 'role:ADMIN'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/refresh', [DashboardController::class, 'refresh']);

    Route::get('/assets', [AssetController::class, 'index']);
    Route::get('/assets/refresh', [AssetController::class, 'refresh']);
    Route::get('/assets/{asset}/detail', [AssetController::class, 'show']);
    Route::post('/assets', [AssetController::class, 'store']);
    Route::put('/assets/{asset}', [AssetController::class, 'update']);
    Route::delete('/assets/{asset}', [AssetController::class, 'destroy']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/refresh', [CategoryController::class, 'refresh']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::get('/loans', [LoanController::class, 'index']);
    Route::get('/loans/refresh', [LoanController::class, 'refresh']);
    Route::get('/divisions/refresh', [LoanController::class, 'divisions']);

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
