<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class SalesLogger
{
    /**
     * Log a sale transaction
     */
    public static function logSale($amount, $details = null, $metadata = [])
    {
        $userId = Auth::id();
        
        $formattedDetails = $details ?: "Sale completed";
        $formattedDetails .= " - Amount: ₱" . number_format($amount, 2);
        
        Log::create([
            'user_id' => $userId,
            'action' => 'sale',
            'details' => $formattedDetails,
            'amount' => $amount, 
            'metadata' => json_encode(array_merge($metadata, [
                'amount' => $amount,
                'timestamp' => now()->toISOString(),
            ])),
        ]);
    }
    
    /**
     * Log a stock in transaction
     */
    public static function logStockIn($productName, $quantity, $details = null)
    {
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'stock_in',
            'details' => $details ?: "Stock added: {$productName} (Qty: {$quantity})",
            'metadata' => json_encode([
                'product' => $productName,
                'quantity' => $quantity,
                'type' => 'stock_in',
            ]),
        ]);
    }
    
    /**
     * Log a stock out transaction
     */
    public static function logStockOut($productName, $quantity, $details = null)
    {
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'stock_out',
            'details' => $details ?: "Stock removed: {$productName} (Qty: {$quantity})",
            'metadata' => json_encode([
                'product' => $productName,
                'quantity' => $quantity,
                'type' => 'stock_out',
            ]),
        ]);
    }
    
    /**
     * Log user authentication
     */
    public static function logAuth($action = 'login')
    {
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'auth',
            'details' => ucfirst($action) . ' successful',
            'metadata' => json_encode([
                'auth_action' => $action,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]),
        ]);
    }
}

