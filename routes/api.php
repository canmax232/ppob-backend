<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import semua Controller yang dibutuhkan
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PPOBController;
use App\Http\Controllers\Api\TopupController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\DepositController; // <--- Import Controller Midtrans yang baru kita buat

// =========================================================
// RUTE PUBLIK (Bisa diakses tanpa login)
// =========================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// =========================================================
// RUTE TERLINDUNGI (Wajib bawa Token dari Flutter)
// =========================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- 1. Kelola Akun & Profil ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']); 
    Route::post('/update-password', [AuthController::class, 'updatePassword']); 
    Route::post('/set-pin', [AuthController::class, 'setPin']); 
    
    // --- 2. Data Aplikasi (Home) ---
    // Menggunakan PPOBController untuk mengambil kategori beserta produknya
    Route::get('/kategori', [PPOBController::class, 'getCategoriesAndProducts']);
    
    // --- 3. Transaksi Pembelian (PPOB) ---
    Route::post('/transaction', [TransactionController::class, 'purchase']);
    Route::get('/transaction/history', [TransactionController::class, 'history']);
    
    // --- 4. Top Up & Deposit ---
    Route::post('/topup', [TopupController::class, 'requestTopup']); // Top Up Manual (Lama)
    
    // Ini Rute Midtrans yang baru kita buat di langkah sebelumnya
    Route::post('/deposit', [DepositController::class, 'store']); 
    Route::get('/deposit/history', [\App\Http\Controllers\Api\DepositController::class, 'history']);
    
});