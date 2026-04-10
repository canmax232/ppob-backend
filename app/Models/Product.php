<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Menggunakan fillable agar lebih aman saat menerima data dari API Digiflazz
    protected $fillable = [
        'category_id', 
        'product_code', 
        'name', 
        'brand',          // <--- Laci brand sudah siap!
        'original_price', 
        'price'
    ];

    // Relasi: 1 Produk milik 1 Kategori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}