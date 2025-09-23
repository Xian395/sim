<?php

namespace App\Models;

use App\Models\Category;
use App\Models\StockTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'brand_id',
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

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public static function getImageUrl($imagePath)
    {
        if (!$imagePath) {
            return null;
        }

        // Use public disk for local development, S3 for production
        $disk = config('app.env') === 'local' ? 'public' : 's3';
        return Storage::disk($disk)->url($imagePath);
    }
}