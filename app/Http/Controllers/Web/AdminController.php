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
        if (auth()->user()->role !== 'admin') abort(403);

        $totalUsers = User::count();
        $totalProducts = Product::count();
        $totalTransactions = Transaction::count();
        
        $recentTransactions = Transaction::with('product')->orderBy('created_at', 'desc')->take(5)->get();
        $products = Product::orderBy('category_id')->get();

        // Data Statistik
        $balance = 1000000; // Bisa dihubungkan ke API saldo Digiflazz nanti
        $totalRevenue = Transaction::sum('amount');

        $chartData = Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')->orderBy('date', 'asc')->take(7)->get();
            
        $chartDates = $chartData->pluck('date')->toJson();
        $chartTotals = $chartData->pluck('total')->toJson();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalProducts', 'totalTransactions', 
            'recentTransactions', 'products', 'balance', 'totalRevenue',
            'chartDates', 'chartTotals'
        ));
    }

    // --- FUNGSI UPDATE HARGA & LOGO PRODUK ---
    public function updatePrice(Request $request, $id)
    {
        $request->validate([
            'harga_jual' => 'required|numeric',
            'product_code' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        $product = Product::findOrFail($id);
        $product->price = $request->harga_jual;
        $product->product_code = $request->product_code;

        if ($request->hasFile('image')) {
            if ($product->image_url) {
                $oldPath = str_replace(url('storage/'), 'public/', $product->image_url);
                Storage::delete($oldPath);
            }
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/produk', $filename);
            $product->image_url = url('storage/produk/' . $filename);
        }

        $product->save();
        return back()->with('success', 'Produk ' . $product->name . ' berhasil diperbarui!');
    }

    // --- FUNGSI SINKRONISASI DIGIFLAZZ (SUDAH KEMBALI BOS!) ---
    public function syncDigiflazz()
    {
        $username = env('DIGIFLAZZ_USERNAME', '');
        $apiKey = env('DIGIFLAZZ_API_KEY', '');
        
        if (empty($username) || empty($apiKey)) {
            return back()->with('error', 'Cek .env! Username/API Key kosong.');
        }

        $sign = md5($username . $apiKey . "pricelist");

        try {
            $response = Http::post('https://api.digiflazz.com/v1/price-list', [
                'cmd'  => 'prepaid',
                'username' => $username,
                'sign' => $sign
            ]);

            $apiResult = $response->json();

            if (isset($apiResult['data']) && is_array($apiResult['data'])) {
                $products = $apiResult['data'];
                $syncedCount = 0;

                foreach ($products as $item) {
                    if (isset($item['buyer_sku_code'])) {
                        $katLower = strtolower($item['category'] ?? '');
                        if (str_contains($katLower, 'pulsa')) $namaKategori = 'Pulsa Nasional';
                        elseif (str_contains($katLower, 'data')) $namaKategori = 'Paket Data';
                        elseif (str_contains($katLower, 'game')) $namaKategori = 'Voucher Game';
                        elseif (str_contains($katLower, 'pln')) $namaKategori = 'Token PLN';
                        elseif (str_contains($katLower, 'wallet')) $namaKategori = 'e-Wallet';
                        else $namaKategori = 'Lainnya';

                        $category = Category::firstOrCreate(['name' => $namaKategori]);

                        Product::updateOrCreate(
                            ['product_code' => $item['buyer_sku_code']], 
                            [
                                'name' => $item['product_name'] ?? 'Produk',
                                'brand' => $item['brand'] ?? 'Lainnya',
                                'original_price' => $item['price'], 
                                'price' => $item['price'] + 2000, // Margin Bos
                                'category_id' => $category->id 
                            ]
                        );
                        $syncedCount++;
                    }
                }
                return back()->with('success', 'Berhasil sinkron ' . $syncedCount . ' produk!');
            }
            return back()->with('error', 'Gagal: ' . json_encode($apiResult));
        } catch (\Exception $e) {
            return back()->with('error', 'Error Jaringan: ' . $e->getMessage());
        }
    }
}