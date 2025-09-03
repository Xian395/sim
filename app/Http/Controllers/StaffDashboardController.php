<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Log;
use Carbon\Carbon;

class StaffDashboardController extends Controller
{
    public function index()
    {
        $todaysTransactions = Log::where('user_id', auth()->id())
                                 ->where('action', 'sale')
                                 ->whereDate('created_at', Carbon::today())
                                 ->count();

        $todaySales = Log::where('user_id', auth()->id())
                         ->where('action', 'sale')
                         ->whereDate('created_at', Carbon::today())
                         ->get();
        
        $todaysSalesTotal = $this->extractAmountFromSales($todaySales);

        return Inertia::render('StaffDashboard', [
            'todaysActivity' => [
                'transactions' => $todaysTransactions,
                'total_sales' => $todaysSalesTotal,
            ],
        ]);
    }

    /**
     */
    private function extractAmountFromSales($sales)
    {
        $totalAmount = 0;
        
        foreach ($sales as $sale) {
            $amount = $this->extractAmountFromDetails($sale->details);
            $totalAmount += $amount;
        }

        return $totalAmount;
    }

    /**
     */
    private function extractAmountFromDetails($details)
    {
        if (!$details) {
            return 0;
        }

        $patterns = [
            '/₱\s*([\d,]+\.?\d*)/',
            '/PHP\s*([\d,]+\.?\d*)/',
            '/peso\s*([\d,]+\.?\d*)/i',
            '/total[:\s]*([\d,]+\.?\d*)/i',
            '/amount[:\s]*([\d,]+\.?\d*)/i',
            '/sale[:\s]*([\d,]+\.?\d*)/i',
            '/price[:\s]*([\d,]+\.?\d*)/i',
            '/cost[:\s]*([\d,]+\.?\d*)/i',
            '/"total"[:\s]*([\d,]+\.?\d*)/',
            '/"amount"[:\s]*([\d,]+\.?\d*)/',
            '/"price"[:\s]*([\d,]+\.?\d*)/',
            '/(?:^|\s)([\d,]+\.?\d+)(?:\s|$)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $details, $matches)) {
                $amount = str_replace(',', '', $matches[1]);
                return (float) $amount;
            }
        }

        if (preg_match_all('/\d{1,3}(?:,\d{3})*\.?\d*/', $details, $matches)) {
            $numbers = array_map(function($match) {
                return (float) str_replace(',', '', $match);
            }, $matches[0]);
            
            if (!empty($numbers)) {
                return max($numbers);
            }
        }

        return 0;
    }
}