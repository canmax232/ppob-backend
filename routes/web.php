<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\AdminController;

// Lempar ke halaman login jika membuka web utama
Route::get('/', function () { return redirect('/login'); });

// --- HALAMAN LOGIN WEB ---
Route::get('/login', function () {
    return view('admin.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate(['email' => 'required', 'password' => 'required']);
    
    if (Auth::attempt($credentials)) {
        if (Auth::user()->role === 'admin') {
            return redirect('/admin'); // Jika Admin, masuk!
        }
        Auth::logout();
        return back()->withErrors(['email' => 'Akses ditolak! Anda bukan Admin.']);
    }
    return back()->withErrors(['email' => 'Email atau Password salah!']);
});

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
});

// --- GEMBOK HALAMAN ADMIN (Wajib Login) ---
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
    
    // Rute untuk Menyimpan Harga Baru (Manual)
    Route::post('/admin/product/{id}', [AdminController::class, 'updatePrice']); 

    // Rute untuk Sinkronisasi Harga Digiflazz (Otomatis)
    Route::post('/admin/sync', [AdminController::class, 'syncDigiflazz']);
});

Route::get('/setup-database-rahasia', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
        '--seed' => true,
        '--force' => true
    ]);
    return 'MANTAP BOS! Database NIKOS STORE Berhasil Di-Install dan Diisi!';
});
Route::get('/buka-gembok-admin', function () {
    \App\Models\User::updateOrCreate(
        ['email' => 'admin@ppob.com'], // Cari email ini
        [
            'name' => 'Bos Admin',
            'password' => bcrypt('admin123'), // Paksa password jadi admin123
            'pin' => '123456', // Beri PIN default
        ]
    );
    return 'KUNCI MASTER BERHASIL! Silakan login dengan Email: admin@ppob.com | Password: admin123';
});