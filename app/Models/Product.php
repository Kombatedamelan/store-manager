<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        "categorie_id",
        "name",
        "price",
        "qte",
        "ref",
    ];

    public function category()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }
    public function productDetails()
    {
        return $this->hasMany(ProductDetail::class);
    }
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
    
}
