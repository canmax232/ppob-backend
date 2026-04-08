<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_id', 'target_number', 'amount', 'status'];

    // Relasi untuk mengambil detail produk (seperti nama dan icon)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}