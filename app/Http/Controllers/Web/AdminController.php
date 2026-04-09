<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 

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

        // --- DATA UNTUK GRAFIK (Pendapatan 7 Hari Terakhir) ---
        $chartData = Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->take(7)
            ->get();
            
        // Mengubah data menjadi format yang dibaca oleh Javascript (Chart.js)
        $chartDates = $chartData->pluck('date')->toJson();
        $chartTotals = $chartData->pluck('total')->toJson();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalProducts', 'totalTransactions', 
            'recentTransactions', 'products', 
            'chartDates', 'chartTotals'
        ));
    }

    // Fungsi Menyimpan Harga & Kode Baru (Manual)
    public function updatePrice(Request $request, $id)
    {
        $request->validate([
            'price' => 'required|numeric',
            'product_code' => 'required|string' 
        ]);
        
        $product = Product::findOrFail($id);
        $product->price = $request->price;
        $product->product_code = $request->product_code; 
        $product->save();

        return back()->with('success', 'Produk ' . $product->name . ' berhasil diperbarui!');
    }

    // --- INI UPDATE FINAL SINKRONISASI BOS ---
    public function syncDigiflazz()
    {// --- INI UPDATE FINAL SINKRONISASI BOS ---
    public function syncDigiflazz()
    {
        // 1. Ambil sandi
        $username = env('DIGIFLAZZ_USERNAME', '');
        $apiKey = env('DIGIFLAZZ_API_KEY', '');
        
        // 2. Cek apakah sandi sudah diisi
        if (empty($username) || empty($apiKey)) {
            return back()->with('error', 'Username atau API Key Digiflazz belum diatur di file .env!');
        }

        // 3. Rumus Wajib Digiflazz
        $sign = md5($username . $apiKey . "pricelist");

        try {
            $response = Http::post('https://api.digiflazz.com/v1/price-list', [
                'cmd'  => 'prepaid',
                'username' => $username,
                'sign' => $sign
            ]);

            $apiResult = $response->json();

            // 4. Jika sukses dan data yang dikembalikan adalah array
            if (isset($apiResult['data']) && is_array($apiResult['data'])) {
                
                // Jika isinya pesan error dari Digiflazz
                if (isset($apiResult['data']['message'])) {
                    return back()->with('error', 'Ditolak Digiflazz: ' . $apiResult['data']['message']);
                }

                $products = $apiResult['data'];
                $syncedCount = 0;

                // KITA TIDAK LAGI MEMBUAT 1 KERANJANG DEFAULT DI SINI.
                // KITA AKAN BUAT OTOMATIS DI DALAM LOOP SESUAI JENISNYA.

                foreach ($products as $item) {
                    if (isset($item['buyer_sku_code']) && isset($item['price'])) {
                        
                        // KUNCI JAWABAN: Ambil nama kategori asli dari Digiflazz (Misal: "Pulsa", "Data", "Games")
                        $kategoriAsli = $item['category'] ?? 'Lainnya';

                        // Sistem akan mencari kategori bernama "Pulsa", kalau belum ada, dia buatkan otomatis
                        $category = Category::firstOrCreate([
                            'name' => $kategoriAsli
                        ]);

                        // Cari dan Update, atau Buat Baru jika belum ada
                        $product = Product::updateOrCreate(
                            ['product_code' => $item['buyer_sku_code']], 
                            [
                                'name' => $item['product_name'] ?? 'Produk ' . $item['buyer_sku_code'],
                                'original_price' => $item['price'], 
                                'price' => $item['price'] + 2000,
                                'category_id' => $category->id // <--- Produk masuk ke kategori yang tepat!
                            ]
                        );

                        if ($product) {
                            $syncedCount++;
                        }
                    }
                }
                return back()->with('success', 'Berhasil memilah dan sinkronisasi ' . $syncedCount . ' produk dari Digiflazz!');
            }

            // Jika format balasan aneh
            return back()->with('error', 'Gagal mengambil data. Pesan dari sistem: ' . json_encode($apiResult));

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan jaringan ke server Digiflazz: ' . $e->getMessage());
        }
    }
}