<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class PPOBController extends Controller
{
    public function getCategoriesAndProducts(Request $request)
    {
        $user = $request->user(); // Ambil data user yang sedang login

        $categories = Category::with('products')->where('is_active', true)->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Data Home Berhasil Diambil',
            'data' => [
                'user_info' => [
                    'name' => $user->name,       // DARI DATABASE
                    'balance' => $user->balance  // DARI DATABASE
                ],
                'categories' => $categories
            ]
        ], 200);
    }
}