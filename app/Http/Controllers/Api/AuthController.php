<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Fungsi Mendaftar Akun Baru
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'member',
            'balance' => 50000, // Bonus saldo awal 50rb untuk pengguna baru!
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['success' => true, 'message' => 'Register Berhasil', 'data' => $user, 'token' => $token], 200);
    }

    // Fungsi Login
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['success' => false, 'message' => 'Email atau Password salah'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
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