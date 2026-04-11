<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    // ==========================================================
    // 1. FUNGSI MENDAFTAR (KIRIM OTP KE WA & EMAIL)
    // ==========================================================
    public function register(Request $request)
    {
        // 1. Validasi Input dari Flutter
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'phone' => 'required|string|unique:users', // WAJIB ADA NO HP SEKARANG
            'password' => 'required|string|min:6'
        ]);

        // 2. Cetak Kode OTP Acak (6 Digit)
        $otpCode = rand(100000, 999999);

        // 3. Simpan User Baru (Status is_verified = false)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'member',
            'balance' => 50000, // Bonus saldo awal 50rb tetap jalan!
            'otp' => $otpCode, // Simpan OTP di database
            'is_verified' => false, 
        ]);

        // 4. KIRIM EMAIL OTP
        try {
            Mail::raw("Halo {$user->name},\n\nKode OTP pendaftaran NIKOS STORE Anda adalah: $otpCode\n\nJangan berikan kode ini kepada siapapun.", function ($message) use ($user) {
                $message->to($user->email)->subject('Kode OTP Verifikasi NIKOS STORE');
            });
        } catch (\Exception $e) {
            // Abaikan jika gagal agar tidak merusak proses DB
        }

        // 5. KIRIM WHATSAPP OTP VIA FONNTE
        try {
            Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN')
            ])->post('https://api.fonnte.com/send', [
                'target' => $user->phone,
                'message' => "Halo *{$user->name}*,\n\nKode OTP NIKOS STORE Anda adalah: *$otpCode*\n\nRahasiakan kode ini dari siapapun."
            ]);
        } catch (\Exception $e) {
            // Abaikan jika gagal agar tidak merusak proses DB
        }

        // PENTING: Jangan kembalikan token di sini, karena user harus verifikasi dulu!
        return response()->json([
            'success' => true, 
            'message' => 'Registrasi Berhasil. Silakan cek OTP di WA/Email.'
        ], 200);
    }

    // ==========================================================
    // 2. FUNGSI CEK OTP (DARI FLUTTER)
    // ==========================================================
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
        }

        if ($user->otp == $request->otp) {
            // JIKA OTP BENAR
            $user->is_verified = true;
            $user->otp = null; // Hapus OTP yang sudah terpakai
            $user->save();

            // Baru buatkan token login
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi berhasil!',
                'token' => $token,
                'data' => $user
            ], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Kode OTP Salah!'], 400);
        }
    }


    // ==========================================================
    // 3. FUNGSI LOGIN (DITAMBAH PENGECEKAN VERIFIKASI)
    // ==========================================================
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['success' => false, 'message' => 'Email atau Password salah'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Cegah login jika belum verifikasi OTP
        if (!$user->is_verified) {
            return response()->json(['success' => false, 'message' => 'Akun belum diverifikasi. Silakan daftar ulang atau minta OTP.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['success' => true, 'message' => 'Login Berhasil', 'data' => $user, 'token' => $token], 200);
    }

    // Fungsi Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logout Berhasil'], 200);
    }
    
    // Ambil Profil User
    public function profile(Request $request)
    {
        return response()->json(['success' => true, 'data' => $request->user()], 200);
    }

    // Ganti Password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6'
        ]);

        $user = $request->user();
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Password lama salah!'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password berhasil diubah!'], 200);
    }

    // Buat/Ganti PIN
    public function setPin(Request $request)
    {
        $request->validate(['pin' => 'required|digits:6']);
        $user = $request->user();
        $user->pin = $request->pin;
        $user->save();

        return response()->json(['success' => true, 'message' => 'PIN berhasil disetel!'], 200);
    }
}