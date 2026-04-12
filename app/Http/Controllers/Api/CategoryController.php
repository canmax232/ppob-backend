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
        // Validasi bahwa yang dikirim harus file gambar (maksimal 2MB)
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }

        // Proses penyimpanan file
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // Buat nama file unik (Contoh: 171289123.png)
            $imageName = time() . '.' . $image->extension();
            
            // Simpan gambar ke folder storage/app/public/categories
            $image->storeAs('public/categories', $imageName);

            // Buat URL yang bisa diakses Flutter
            // Gunakan env('APP_URL') agar otomatis mengikuti URL Railway Anda
            $imageUrl = url('storage/categories/' . $imageName);

            // Simpan link-nya ke database
            $category->icon_url = $imageUrl;
            $category->save();

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil diupload!',
                'data' => $category
            ], 200);
        }

        return response()->json(['success' => false, 'message' => 'Gagal menerima file gambar'], 400);
    }
}