<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi: 1 Produk milik 1 Kategori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}