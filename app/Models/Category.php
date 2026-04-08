<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom diisi secara massal (Mass Assignment)
    protected $guarded = ['id'];

    // Relasi: 1 Kategori punya Banyak Produk
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}