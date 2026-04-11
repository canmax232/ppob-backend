<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reference_number')->unique(); // Contoh: DEP-12345678
            $table->decimal('amount', 15, 2);
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->string('payment_method')->nullable(); // Nama metode (Contoh: BCA VA)
            $table->string('payment_type')->nullable(); // Kode API (Contoh: bca_va)
            $table->string('status')->default('pending'); // pending, success, failed, expired
            $table->string('snap_token')->nullable(); // Kode unik dari Midtrans
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deposits');
    }
};