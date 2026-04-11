<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PPOBController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TopupController;
use App\Http\Controllers\Api\TransactionController;

// Rute Publik (Tidak perlu login)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rute Terlindungi (Hanya bisa diakses jika menyertakan Token Login dari Flutter)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']); // Baru
    Route::post('/update-password', [AuthController::class, 'updatePassword']); // Baru
    Route::post('/set-pin', [AuthController::class, 'setPin']); // Baru
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::get('/kategori', [CategoryController::class, 'index']);
    
    // Ambil Data Home (Sekarang butuh token agar tahu siapa yang login)
    Route::get('/kategori', [PPOBController::class, 'getCategoriesAndProducts']);
    
    // Rute Transaksi Beli
    Route::post('/transaction', [TransactionController::class, 'purchase']);

Route::get('/transaction/history', [TransactionController::class, 'history']);
    Route::post('/topup', [TopupController::class, 'requestTopup']);
});