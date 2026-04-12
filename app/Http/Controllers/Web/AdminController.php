<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Storage; 

class AdminController extends Controller
{
    public function index()
    {
        // Pengaman Ganda
        if (auth()->user()->role !== 'admin') abort(403);

        $totalUsers = User::count();
        $totalProducts = Product::count();
        $totalTransactions = Transaction::count();
        
        $recentTransactions = Transaction::with('product')->orderBy('created_at', 'desc')->take(5)->get();
        $products = Product::orderBy('category_id')->get();

        // Ambil data saldo server (asumsi Anda pakai Digiflazz atau sejenisnya)
        $balance = 0;
        try {
            // Logika ambil saldo Bos di sini jika ada API-nya
            $balance = 1000000; // Dummy saldo
        } catch (\Exception $e) {}

        $totalRevenue = Transaction::sum('amount');

        // --- DATA UNTUK GRAFIK (Pendapatan 7 Hari Terakhir) ---
        $chartData = Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->take(7)
            ->get();
            
        $chartDates = $chartData->pluck('date')->toJson();
        $chartTotals = $chartData->pluck('total')->toJson();

        return view('admin.dashboard', compact(
            'balance', 'totalRevenue', 'recentTransactions', 'products', 
            'chartDates', 'chartTotals'
        ));
    }

    // --- FUNGSI UPDATE HARGA & LOGO PRODUK (PEMBARUAN SAKTI) ---
    public function updatePrice(Request $request, $id)
    {
        $request->validate([
            'harga_jual' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validasi gambar
        ]);

        try {
            $product = Product::findOrFail($id);
            $product->harga_jual = $request->harga_jual;

            // Jika admin mengupload gambar baru
            if ($request->hasFile('image')) {
                // 1. Hapus gambar lama jika ada (biar storage tidak penuh)
                if ($product->image_url) {
                    $oldPath = str_replace(url('storage/'), 'public/', $product->image_url);
                    Storage::delete($oldPath);
                }

                // 2. Simpan gambar baru
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/produk', $filename);

                // 3. Simpan URL baru ke database
                $product->image_url = url('storage/produk/' . $filename);
            }

            $product->save();

            return back()->with('success', 'Produk ' . $product->product_name . ' berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    // Fungsi untuk update Icon Kategori (dari Flutter Admin)
    public function updateKategoriIcon(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            $file->storeAs('public/kategori', $filename);

            $category->icon_url = url('storage/kategori/' . $filename);
            $category->save();

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil diperbarui',
                'icon_url' => $category->icon_url
            ], 200);
        }

        return response()->json(['success' => false, 'message' => 'Gagal menerima file'], 400);
    }
}