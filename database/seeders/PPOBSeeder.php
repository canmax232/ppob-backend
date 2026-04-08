<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PPOBSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Admin & Member Contoh
        User::create([
            'name' => 'Yosef Admin',
            'email' => 'admin@nikos.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'balance' => 1000000,
        ]);

        User::create([
            'name' => 'Canmax83',
            'email' => 'member@nikos.com',
            'password' => Hash::make('password123'),
            'role' => 'member',
            'balance' => 22728, // Sesuai saldo di mockup Flutter
        ]);

        // 2. Buat Kategori Produk
        $pulsa = Category::create([
            'name' => 'Pulsa Nasional',
            'icon_url' => 'https://cdn-icons-png.flaticon.com/512/3616/3616531.png',
            'is_active' => true,
        ]);

        $ewallet = Category::create([
            'name' => 'e-Wallet',
            'icon_url' => 'https://cdn-icons-png.flaticon.com/512/10149/10149458.png',
            'is_active' => true,
        ]);

        // 3. Buat Produk Contoh untuk Pulsa (Telkomsel)
        Product::create([
            'category_id' => $pulsa->id,
            'product_code' => 'TSEL5',
            'name' => 'Telkomsel 5.000',
            'original_price' => 5100,
            'price' => 7000,
            'status' => 'active',
        ]);

        Product::create([
            'category_id' => $pulsa->id,
            'product_code' => 'TSEL10',
            'name' => 'Telkomsel 10.000',
            'original_price' => 10100,
            'price' => 12000,
            'status' => 'active',
        ]);

        // 4. Buat Produk Contoh untuk E-Wallet (DANA)
        Product::create([
            'category_id' => $ewallet->id,
            'product_code' => 'DANA10',
            'name' => 'DANA 10.000',
            'original_price' => 10000,
            'price' => 11500,
            'status' => 'active',
        ]);

        Product::create([
            'category_id' => $ewallet->id,
            'product_code' => 'DANA20',
            'name' => 'DANA 20.000',
            'original_price' => 20000,
            'price' => 21500,
            'status' => 'active',
        ]);
    }
}