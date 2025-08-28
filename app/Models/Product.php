<?php

namespace App\Models;

use App\Models\Category;
use App\Models\StockTransaction;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 
        'barcode', 
        'price', 
        'description', 
        'stock_quantity', 
        'item_code',
        'image_path',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }
}