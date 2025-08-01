<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    use HasFactory;
    protected $table = 'books';

    protected $fillable = [
        'uuid',
        'kode_buku',
        'kategori',
        'nama_buku',
        'harga',
        'stok',
        'penerbit_id',
        'created_at',
        'updated_at',
    ];

    public function penerbit()
    {
        return $this->belongsTo(Penerbit::class);
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriBook::class, 'kategori');
    }
}
