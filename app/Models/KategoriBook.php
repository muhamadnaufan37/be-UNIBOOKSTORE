<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBook extends Model
{
    use HasFactory;

    protected $table = 'kategori_books';

    protected $fillable = [
        'nama_kategori',
    ];

    // Relasi: Satu kategori memiliki banyak buku
    public function books()
    {
        return $this->hasMany(Buku::class, 'kategori');
    }
}
