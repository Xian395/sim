<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'acquisition_price',
        'unit_cost',
        'remaining_quantity',
        'batch_allocations',
        'reason',
        'transaction_date'
    ];

    protected $casts = [
        'batch_allocations' => 'array',
        'acquisition_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}