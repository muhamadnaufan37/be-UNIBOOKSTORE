<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penerbit extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'kode_penerbit',
        'nama',
        'alamat',
        'kota',
        'telepon',
        'created_at',
        'updated_at',
    ];

    public function book()
    {
        return $this->hasMany(Buku::class);
    }
}
