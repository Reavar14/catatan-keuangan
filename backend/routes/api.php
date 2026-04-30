<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

// ─── Public Routes (tanpa autentikasi) ───────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ─── Protected Routes (memerlukan Sanctum token) ─────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Kategori
    Route::apiResource('categories', CategoryController::class);

    // Transaksi
    Route::apiResource('transactions', TransactionController::class);
});
