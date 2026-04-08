<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;

class FrontEndController extends Controller
{
    public function getHomeData()
    {
        // 1. Ambil data User (Sementara kita hardcode pakai User ID 2 yaitu 'Canmax83' dari seeder)
        // Nanti kalau sistem login sudah jalan, kita ganti pakai Auth::user()
        $user = User::find(2);

        // 2. Ambil semua Kategori beserta Produk di dalamnya yang berstatus 'active'
        $categories = Category::with(['products' => function($query) {
            $query->where('status', 'active');
        }])->where('is_active', true)->get();

        // 3. Gabungkan dan kirim sebagai format JSON ke Flutter
        return response()->json([
            'success' => true,
            'message' => 'Data Home Berhasil Diambil',
            'data' => [
                'user_info' => [
                    'name' => $user->name,
                    'balance' => $user->balance,
                ],
                'categories' => $categories
            ]
        ], 200);
    }
}