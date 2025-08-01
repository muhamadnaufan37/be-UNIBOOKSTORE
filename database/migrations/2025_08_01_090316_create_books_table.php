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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('kode_buku')->unique();
            $table->foreignId('kategori')->constrained('kategori_books')->onDelete('cascade');
            $table->string('nama_buku');
            $table->decimal('harga', 10);
            $table->integer('stok');
            $table->foreignId('penerbit_id')->constrained('penerbits')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
