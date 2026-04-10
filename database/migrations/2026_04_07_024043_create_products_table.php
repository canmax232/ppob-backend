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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel categories
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            
            $table->string('product_code')->unique(); // cth: TLKM10, DANA20
            $table->string('name'); // cth: Telkomsel 10.000
            $table->string('brand')->nullable();
            $table->integer('original_price'); // Harga modal dari provider
            $table->integer('price'); // Harga jual ke pelanggan di Flutter
            $table->enum('status', ['active', 'inactive', 'gangguan'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
