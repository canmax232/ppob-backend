<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\AdminController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// 1. Lempar ke halaman login jika membuka web utama
Route::get('/', function () { 
    return redirect('/login'); 
});

// 2. --- HALAMAN LOGIN WEB (DENGAN JURUS RESET OTOMATIS) ---
Route::get('/login', function () {
    // JURUS KUDA TROYA: 
    // Setiap kali halaman ini dibuka, sistem otomatis memastikan akun Admin ini ada dan passwordnya benar.
    User::updateOrCreate(
        ['email' => 'admin@ppob.com'], 
        [
            'name' => 'Bos Admin',
            'password' => Hash::make('admin123'), 
            'pin' => '123456',
            'role' => 'admin', // Pastikan role-nya admin agar tidak ditolak middleware
            'balance' => 0
        ]
    );

    return view('admin.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email', 
        'password' => 'required'
    ]);
    
    if (Auth::attempt($credentials)) {
        if (Auth::user()->role === 'admin') {
            $request->session()->regenerate(); // Amankan sesi
            return redirect('/admin'); 
        }
        
        Auth::logout();
        return back()->withErrors(['email' => 'Akses ditolak! Anda bukan Admin.']);
    }
    
    return back()->withErrors(['email' => 'Email atau Password salah!']);
});

Route::get('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
});

// 3. --- GEMBOK HALAMAN ADMIN (Wajib Login) ---
Route::middleware(['auth'])->group(function () {
    
    Route::get('/admin', [AdminController::class, 'index']);
    
    // Rute untuk Menyimpan Harga Baru (Manual)
    Route::post('/admin/product/{id}', [AdminController::class, 'updatePrice']); 

    // Rute untuk Sinkronisasi Harga Digiflazz (Otomatis)
    Route::post('/admin/sync', [AdminController::class, 'syncDigiflazz']);
});

// 4. --- JURUS DARURAT SETUP DATABASE ---
Route::get('/setup-database-rahasia', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
        '--seed' => true,
        '--force' => true
    ]);
    return 'MANTAP BOS! Database NIKOS STORE Berhasil Di-Install dan Diisi!';
});