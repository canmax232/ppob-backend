<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // Relasi ke user (pembeli) dan product (yang dibeli)
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('product_id')->constrained('products');
            
            $table->string('target_number'); // Nomor HP atau meteran PLN tujuan
            $table->integer('amount'); // Harga total saat transaksi terjadi
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->text('sn')->nullable(); // Serial Number dari provider jika sukses
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
