<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'stock', 'sold', 'category', 'description', 'image'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image
            ? url('storage/' . $this->image)
            : url('images/default.png');
    }
}
