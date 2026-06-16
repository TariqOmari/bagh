<?php
// routes/api.php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductController;

Route::post('/register', [AdminController::class, 'register']);
Route::post('/login', [AdminController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Admin routes
    Route::get('/profile', [AdminController::class, 'profile']);
    Route::post('/logout', [AdminController::class, 'logout']);
    
    // Product routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{id}', [ProductController::class, 'show']);
    });

    // Stock entries routes
    Route::prefix('stock-in')->group(function () {
        Route::get('/', [ProductController::class, 'stockEntries']);
        Route::post('/add', [ProductController::class, 'addStock']);
    });

    // Factory loan routes
    Route::prefix('factory-loans')->group(function () {
        Route::get('/', [ProductController::class, 'getLoans']);
        Route::get('/summary', [ProductController::class, 'loanSummary']);
        Route::get('/{id}', [ProductController::class, 'getLoan']);
        Route::post('/{id}/pay', [ProductController::class, 'payLoan']);
    });
});