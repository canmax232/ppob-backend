<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deposit;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\CoreApi;

class DepositController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi data yang dikirim dari Flutter
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'payment_type' => 'required|string', // Contoh: 'qris', 'bca_va', 'alfamart'
            'payment_name' => 'required|string'  // Contoh: 'QRIS', 'Bank BCA'
        ]);

        $user = $request->user();
        $amount = $request->amount;
        $paymentType = $request->payment_type;

        // 2. Hitung Biaya Admin (Sesuai Standar Aplikasi Anda)
        $adminFee = 0;
        if ($paymentType == 'qris') {
            $adminFee = ceil($amount * 0.007); // Biaya QRIS 0.7%
        } else {
            $adminFee = 4000; // Biaya Virtual Account / Retail (Flat)
        }

        $grossAmount = $amount + $adminFee;
        
        // Buat ID Transaksi Unik (Contoh: DEP-1712345678-UID1)
        $orderId = 'DEP-' . time() . '-' . $user->id;

        // 3. Simpan ke Database Anda dengan status 'pending'
        $deposit = Deposit::create([
            'user_id' => $user->id,
            'reference_number' => $orderId,
            'amount' => $amount,
            'admin_fee' => $adminFee,
            'payment_method' => $request->payment_name,
            'payment_type' => $paymentType,
            'status' => 'pending',
        ]);

        // 4. SETUP KONFIGURASI MIDTRANS
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // 5. RAKIT PAYLOAD (PAKET DATA) UNTUK DIKIRIM KE MIDTRANS
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
        ];

        // 6. DETEKSI METODE PEMBAYARAN & SESUAIKAN PAYLOAD
        if ($paymentType == 'qris') {
            
            $payload['payment_type'] = 'qris';
            
        } elseif (str_contains($paymentType, '_va')) { // Jika itu bca_va, bri_va, bni_va
            
            $bank = str_replace('_va', '', $paymentType); // Membuang tulisan '_va' untuk mendapat nama bank
            $payload['payment_type'] = 'bank_transfer';
            $payload['bank_transfer'] = [
                'bank' => $bank
            ];
            
        } elseif ($paymentType == 'echannel') { // Khusus Mandiri Bill / E-Channel
            
            $payload['payment_type'] = 'echannel';
            $payload['echannel'] = [
                'bill_info1' => 'Topup Saldo',
                'bill_info2' => 'PPOB NIKOS STORE'
            ];
            
        } elseif ($paymentType == 'alfamart') { // Khusus Gerai Retail Alfamart
            
            $payload['payment_type'] = 'cstore';
            $payload['cstore'] = [
                'store' => 'alfamart',
                'message' => 'Topup Saldo NIKOS STORE'
            ];
        }

        // 7. TEMBAK API MIDTRANS!
        try {
            $midtransResponse = CoreApi::charge($payload);

            // Simpan Data Midtrans ke Flutter (Flutter akan meracik QRIS/VA-nya dari data ini)
            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendapatkan akses pembayaran',
                'data' => [
                    'deposit' => $deposit,
                    'midtrans' => $midtransResponse
                ]
            ]);

        } catch (\Exception $e) {
            // Jika Midtrans Error / RTO (Gagal)
            $deposit->update(['status' => 'failed']);
            Log::error('Midtrans Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke Server Pembayaran.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // --- TAMBAHKAN FUNGSI INI BOS ---
    public function history(Request $request)
    {
        try {
            $deposits = \App\Models\Deposit::where('user_id', $request->user()->id)
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $deposits
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()], 500);
        }
    }
}