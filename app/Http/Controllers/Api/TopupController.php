<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TopupController extends Controller
{
    public function requestMidtrans(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'payment_type' => 'required|string'
        ]);

        // 1. Siapkan Kunci Rahasia Midtrans Anda (Masukkan Server Key Sandbox Midtrans Anda di sini)
        // Sebaiknya taruh di file .env dengan nama MIDTRANS_SERVER_KEY
        $serverKey = env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-xxxxxxxxxxxxxxxxx'); 

        // 2. Buat ID Pesanan Unik
        $orderId = 'TOPUP-' . time() . '-' . auth()->id();
        $amount = $request->amount;

        try {
            // 3. Tembak API Midtrans Snap
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->post('https://app.sandbox.midtrans.com/snap/v1/transactions', [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email'      => auth()->user()->email,
                ],
                // Opsional: Langsung arahkan ke metode pembayaran yang dipilih di Flutter
                'enabled_payments' => [$request->payment_type] 
            ]);

            $result = $response->json();

            // 4. Kembalikan Link Pembayaran ke Flutter
            if (isset($result['redirect_url'])) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Berhasil membuat tagihan',
                    'redirect_url' => $result['redirect_url'] // <--- INI URL PEMBAYARANNYA
                ]);
            }

            return response()->json(['status' => 'error', 'message' => 'Gagal terhubung ke Midtrans'], 500);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}