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
        Schema::create('penerbits', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('kode_penerbit')->unique();
            $table->string('nama');
            $table->text('alamat');
            $table->string('kota');
            $table->string('telepon');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerbits');
    }
};
