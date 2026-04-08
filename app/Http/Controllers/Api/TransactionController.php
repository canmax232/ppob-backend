<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http; // ALAT UNTUK MENEMBAK API VENDOR

class TransactionController extends Controller
{
    public function purchase(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'target_number' => 'required|string',
            'pin' => 'required|digits:6' 
        ]);

        $user = $request->user();

        // 2. Cek Keamanan PIN
        if ($user->pin !== $request->pin) {
            return response()->json(['success' => false, 'message' => 'PIN yang Anda masukkan salah!'], 400);
        }

        $product = Product::findOrFail($request->product_id);

        // 3. Cek Saldo User
        if ($user->balance < $product->price) {
            return response()->json(['success' => false, 'message' => 'Saldo Anda tidak mencukupi!'], 400);
        }

        // ====================================================================
        // MULAI PROSES TEMBAK KE API DIGIFLAZZ SUNGGUHAN
        // ====================================================================
        
        // Ambil Sandi (Beri string kosong jika gagal terbaca)
        $username = env('DIGIFLAZZ_USERNAME', '');
        $apiKey = env('DIGIFLAZZ_API_KEY', '');
        
        // Buat ID Transaksi Unik
        $refId = 'NIKOS-' . time() . '-U' . $user->id; 
        
        // Rumus Wajib Digiflazz
        $signature = md5($username . $apiKey . $refId);

        try {
            // Tembak API Digiflazz
            $response = \Illuminate\Support\Facades\Http::post('https://api.digiflazz.com/v1/transaction', [
                'username' => $username,
                'buyer_sku_code' => $product->product_code, 
                'customer_no' => $request->target_number,
                'ref_id' => $refId,
                'sign' => $signature,
                
                // BARIS AJAIB: Wajib 'true' jika menggunakan API Key Development!
                'testing' => true 
            ]);

            $apiResult = $response->json();

            // Cek apakah Digiflazz menolak request kita
            if (isset($apiResult['data']['status']) && $apiResult['data']['status'] == 'Gagal') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Dibatalkan Vendor: ' . ($apiResult['data']['message'] ?? 'Gangguan Sistem')
                ], 400);
            }

            // ====================================================================
            // JIKA DIGIFLAZZ MENERIMA (Status: Pending / Sukses)
            // ====================================================================
            
            // 4. Potong Saldo User
            $user->balance -= $product->price;
            $user->save();

            // 5. Catat Transaksi ke Database Anda
            Transaction::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'target_number' => $request->target_number,
                'amount' => $product->price,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembelian ' . $product->name . ' berhasil diproses oleh sistem!',
                'new_balance' => $user->balance
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal terhubung ke Server Pusat PPOB.'], 500);
        }
    }
}