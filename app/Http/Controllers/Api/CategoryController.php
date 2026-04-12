<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category; // Pastikan model Kategori Anda sesuai
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Fungsi Update Icon URL
    public function updateIcon(Request $request, $id)
    {
        $request->validate([
            'icon_url' => 'required|string'
        ]);

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }

        $category->icon_url = $request->icon_url;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Icon kategori berhasil diperbarui!',
            'data' => $category
        ], 200);
    }
}