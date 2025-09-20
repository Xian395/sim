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

        // For local development, ensure the URL works with different local server setups
        if (app()->environment('local')) {
            $baseUrl = config('app.url');
            // Handle cases where APP_URL might not be set correctly for local development
            if (empty($baseUrl) || $baseUrl === 'http://localhost') {
                $baseUrl = request()->getSchemeAndHttpHost();
            }
            return $baseUrl . '/storage/' . $imagePath;
        }

        // For production, use the configured storage URL
        return Storage::disk('public')->url($imagePath);
    }
}