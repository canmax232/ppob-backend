<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. BUAT AKUN ADMIN MASTER ---
        \App\Models\User::create([
            'name' => 'Bos Admin',
            'email' => 'admin@ppob.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
            'balance' => 0,
            'pin' => '123456'
        ]);
        
        // 1. DAFTAR KATEGORI LENGKAP
        $categories = [
            ['name' => 'Pulsa Nasional', 'icon' => 'https://cdn-icons-png.flaticon.com/512/3616/3616531.png'],
            ['name' => 'Paket Data', 'icon' => 'https://cdn-icons-png.flaticon.com/512/3063/3063822.png'],
            ['name' => 'e-Wallet', 'icon' => 'https://cdn-icons-png.flaticon.com/512/10149/10149458.png'],
            ['name' => 'Token PLN', 'icon' => 'https://cdn-icons-png.flaticon.com/512/616/616494.png'],
            ['name' => 'Tagihan PDAM', 'icon' => 'https://cdn-icons-png.flaticon.com/512/427/427112.png'],
            ['name' => 'BPJS', 'icon' => 'https://cdn-icons-png.flaticon.com/512/1152/1152924.png'],
            ['name' => 'Internet & TV', 'icon' => 'https://cdn-icons-png.flaticon.com/512/3159/3159385.png'],
            ['name' => 'Voucher Game', 'icon' => 'https://cdn-icons-png.flaticon.com/512/808/808476.png'],
        ];

        // Menyimpan ID kategori agar mudah dihubungkan dengan produk
        $catIds = [];
        foreach ($categories as $cat) {
            $newCat = Category::create([
                'name' => $cat['name'],
                'icon_url' => $cat['icon'],
                'is_active' => true,
            ]);
            $catIds[$cat['name']] = $newCat->id;
        }

        // 2. DAFTAR PRODUK UNTUK MASING-MASING KATEGORI
        
        // --- Produk Pulsa ---
        Product::create([
            'category_id' => $catIds['Pulsa Nasional'], 'product_code' => 'TSEL10',
            'name' => 'Telkomsel 10.000', 'price' => 11000, 'original_price' => 10200, 'status' => 'active'
        ]);
        Product::create([
            'category_id' => $catIds['Pulsa Nasional'], 'product_code' => 'XL25',
            'name' => 'XL 25.000', 'price' => 25500, 'original_price' => 24800, 'status' => 'active'
        ]);

        // --- Produk Paket Data ---
        Product::create([
            'category_id' => $catIds['Paket Data'], 'product_code' => 'ISATDATA',
            'name' => 'Indosat Freedom 3GB', 'price' => 15000, 'original_price' => 14000, 'status' => 'active'
        ]);

        // --- Produk E-Wallet ---
        Product::create([
            'category_id' => $catIds['e-Wallet'], 'product_code' => 'DANA20',
            'name' => 'DANA 20.000', 'price' => 21000, 'original_price' => 20000, 'status' => 'active'
        ]);
        Product::create([
            'category_id' => $catIds['e-Wallet'], 'product_code' => 'OVO50',
            'name' => 'OVO 50.000', 'price' => 51500, 'original_price' => 50000, 'status' => 'active'
        ]);

        // --- Produk Token PLN ---
        Product::create([
            'category_id' => $catIds['Token PLN'], 'product_code' => 'PLN20',
            'name' => 'Token PLN 20.000', 'price' => 22000, 'original_price' => 20000, 'status' => 'active'
        ]);
        Product::create([
            'category_id' => $catIds['Token PLN'], 'product_code' => 'PLN50',
            'name' => 'Token PLN 50.000', 'price' => 52000, 'original_price' => 50000, 'status' => 'active'
        ]);
        
        // --- Produk PDAM ---
        Product::create([
            'category_id' => $catIds['Tagihan PDAM'], 'product_code' => 'PDAM_JKT',
            'name' => 'Cek Tagihan PDAM Jakarta', 'price' => 2500, 'original_price' => 1000, 'status' => 'active'
        ]);
    }
}