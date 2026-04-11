<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference_number',
        'amount',
        'admin_fee',
        'payment_method',
        'payment_type',
        'status',
        'snap_token'
    ];

    // Relasi: 1 Deposit milik 1 User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}