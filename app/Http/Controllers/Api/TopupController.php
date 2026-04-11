<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TopupController extends Controller
{
    public function requestTopup(Request $request)
    {
        // 1. Validasi Input dari Flutter
        $request->validate([
            'amount' => 'required|numeric|min:10000'
        ]);

        $user = auth()->user(); 
        $amount = $request->amount;
        
        // 2. Buat Nomor Order Unik (Contoh: TOPUP-1-171234567-ABCDE)
        $orderId = 'TOPUP-' . $user->id . '-' . time() . '-' . Str::random(5);

        // 3. Ambil Kunci Rahasia dari .env
        $serverKey = env('MIDTRANS_SERVER_KEY', '');
        $isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        
        // Pilih jalur: Uji Coba (Sandbox) atau Asli (Production)
        $baseUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        // Rumus Wajib Midtrans: ServerKey ditambah tanda titik dua (:), lalu di-Base64
        $authString = base64_encode($serverKey . ':');

        // 4. Susun Paket Data untuk dikirim ke Midtrans
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => 'TOPUP_SALDO',
                    'price' => (int) $amount,
                    'quantity' => 1,
                    'name' => 'Deposit Saldo NIKOS STORE'
                ]
            ]
        ];

        // 5. Tembak API Midtrans menggunakan Laravel HTTP Client
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $authString,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($baseUrl, $payload);

            $result = $response->json();

            // 6. Jika Midtrans berhasil merespons dengan Link Pembayaran
            if ($response->successful() && isset($result['redirect_url'])) {
                
                // TODO (Opsional): Di sini nanti Anda bisa menyimpan data transaksi ke tabel database dengan status "Pending"

                return response()->json([
                    'status' => 'success',
                    'message' => 'Tagihan berhasil dibuat',
                    'payment_url' => $result['redirect_url'] // <-- Ini yang ditangkap Flutter!
                ], 200);
            }

            // Jika Midtrans menolak (misal: Server Key salah)
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke payment gateway',
                'error' => $result
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}