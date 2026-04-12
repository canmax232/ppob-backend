<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\AdminController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// =================================================================
// 1. HALAMAN UTAMA (Otomatis lempar ke Login)
// =================================================================
Route::get('/', function () { 
    return redirect('/login'); 
});

// =================================================================
// 2. HALAMAN LOGIN WEB (DENGAN JURUS KUDA TROYA)
// =================================================================
Route::get('/login', function () {
    // PERBAIKAN: Tambahkan 'phone' dan 'is_verified' agar tidak terkena blokir OTP!
    User::updateOrCreate(
        ['email' => 'admin@ppob.com'], 
        [
            'name' => 'Bos Admin',
            'phone' => '081234567890',          // Wajib ada untuk user baru
            'password' => Hash::make('admin123'), 
            'pin' => '123456',
            'role' => 'admin', 
            'balance' => 1000000,               // Sekalian kasih saldo 1 Juta Bos!
            'is_verified' => 1                  // SANGAT PENTING: Lolos verifikasi otomatis
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

// =================================================================
// 3. GEMBOK HALAMAN ADMIN (Wajib Login)
// =================================================================
Route::middleware(['auth'])->group(function () {
    
    Route::get('/admin', [AdminController::class, 'index']);
    
    // Rute untuk Menyimpan Harga Baru (Manual)
    Route::post('/admin/product/{id}', [AdminController::class, 'updatePrice']); 

    // Rute untuk Sinkronisasi Harga Digiflazz (Otomatis)
    Route::post('/admin/sync', [AdminController::class, 'syncDigiflazz']);

    // Rute manajemen kategori admin
    Route::get('/admin/categories', [AdminController::class, 'categories']);
    Route::post('/admin/categories/{id}', [AdminController::class, 'updateCategory']);
});


// =================================================================
// 4. JURUS DARURAT & TROUBLESHOOTING RAILWAY (HATI-HATI)
// =================================================================

// PERHATIAN: Jika aplikasi sudah rilis ke publik (Production), 
// tambahkan // (komentar) di depan rute-rute ini agar tidak di-klik orang iseng!

Route::get('/setup-database-rahasia', function () {
    Artisan::call('migrate:fresh', [
        '--seed' => true,
        '--force' => true
    ]);
    return 'MANTAP BOS! Database NIKOS STORE Berhasil Di-Install dan Diisi! (PERINGATAN: Semua data lama terhapus)';
});

Route::get('/migrate-sakti', function() {
    try {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('migrate', ['--force' => true]);
        
        $driver = DB::connection()->getDriverName();
        $dbName = DB::connection()->getDatabaseName();
        
        return "<h1>SUKSES BERAT BOS!</h1>
                <p>Laravel terhubung ke Driver: <b>{$driver}</b></p>
                <p>Nama Database: <b>{$dbName}</b></p>
                <p>Silakan cek tab Data di kotak MySQL Railway.</p>";
    } catch (\Exception $e) {
        return "Waduh error Bos: " . $e->getMessage();
    }
});

Route::get('/ciptakan-admin-pro', function() {
    User::where('email', 'admin@ppob.com')->delete();
    
    $user = new User();
    $user->name = 'Super Admin';
    $user->email = 'admin@ppob.com';
    $user->phone = '081234567890';
    $user->password = Hash::make('admin123');
    $user->role = 'admin';
    $user->balance = 1000000; 
    $user->is_verified = 1; 
    $user->save(); 
    
    return "BERHASIL TOTAL BOS! Akun admin@ppob.com dengan password 'admin123' lahir kembali. Silakan Login!";
});

Route::get('/buat-storage-link', function () {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    return 'SUKSES BOS! Jembatan gambar sudah dibangun. Gambar sekarang bisa dilihat publik!';
});

// =================================================================
// 5. JURUS SAKTI BYPASS CORS (UNTUK FLUTTER WEB)
// =================================================================
Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    $path = storage_path('app/public/' . $folder . '/' . $filename);

    // Cek apakah gambarnya ada
    if (!file_exists($path)) {
        abort(404);
    }

    // Ambil file dan jenisnya (PNG/JPG)
    $file = \Illuminate\Support\Facades\File::get($path);
    $type = \Illuminate\Support\Facades\File::mimeType($path);

    // Kembalikan gambar lengkap dengan surat izin bebas hambatan (CORS)
    return response($file, 200)
        ->header('Content-Type', $type)
        ->header('Access-Control-Allow-Origin', '*'); // KUNCI UTAMA CORS
});