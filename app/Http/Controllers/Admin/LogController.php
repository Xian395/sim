<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Product;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class LogController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Log::with('user');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%');
                })
                ->orWhere('action', 'like', '%' . $search . '%')
                ->orWhere('details', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->get('action'));
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'user.name') {
            $query->join('users', 'logs.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortOrder)
                  ->select('logs.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        if ($request->get('export') === 'all') {
            $logs = $query->limit(10000)->get(); 
            return response()->json([
                'data' => $logs,
                'total' => $logs->count()
            ]);
        }

        $perPage = $request->get('per_page', 10); 
        
        $allowedPerPage = [10, 50, 100, 500, 1000];
        if (!in_array((int)$perPage, $allowedPerPage)) {
            $perPage = 10; 
        }

        $logs = $query->paginate($perPage);

        $salesSummary = $this->getSalesSummary();

        return Inertia::render('Admin/Logs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'action', 'sort_by', 'sort_order', 'per_page']),
            'salesSummary' => $salesSummary
        ]);
    }

    public function salesReport(Request $request)
    {
        $period = $request->get('period', 'daily');
        $date = $request->get('date', now()->toDateString());
        $startDate = $request->get('startDate', now()->toDateString());
        $endDate = $request->get('endDate', now()->toDateString());
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $salesData = [];
        $chartData = [];

        switch ($period) {
            case 'daily':
                $salesData = $this->getDailySales($date);
                $chartData = $this->getDailyChartData($date);
                break;
            case 'weekly':
                $salesData = $this->getWeeklySales($date);
                $chartData = $this->getWeeklyChartData($date);
                break;
            case 'monthly':
                $salesData = $this->getMonthlySales($year, $month);
                $chartData = $this->getMonthlyChartData($year, $month);
                break;
            case 'range':
                $salesData = $this->getRangeSales($startDate, $endDate);
                $chartData = $this->getRangeChartData($startDate, $endDate);
                break;
        }

        return response()->json([
            'salesData' => $salesData,
            'chartData' => $chartData,
            'period' => $period,
            'date' => $date,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'year' => $year,
            'month' => $month
        ]);
    }


    public function brandSalesReport(Request $request)
    {
        $period = $request->get('period', 'daily');
        $date = $request->get('date', now()->toDateString());
        $startDate = $request->get('startDate', now()->toDateString());
        $endDate = $request->get('endDate', now()->toDateString());
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $brands = Brand::with('products')->get();
        $brandSalesData = [];

        foreach ($brands as $brand) {
            $productNames = $brand->products->pluck('name')->toArray();

            $brandSales = $this->getBrandSalesForPeriod($productNames, $brand->name, $period, $date, $startDate, $endDate, $year, $month);
            $brandSalesData[] = $brandSales;
        }

        return response()->json([
            'brandSales' => $brandSalesData,
            'period' => $period,
            'date' => $date,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'year' => $year,
            'month' => $month
        ]);
    }

    private function getBrandSalesForPeriod($productNames, $brandName, $period, $date, $startDate, $endDate, $year, $month)
    {
        switch ($period) {
            case 'daily':
                return $this->getDailyBrandSales($productNames, $brandName, $date);
            case 'weekly':
                return $this->getWeeklyBrandSales($productNames, $brandName, $date);
            case 'monthly':
                return $this->getMonthlyBrandSales($productNames, $brandName, $year, $month);
            case 'range':
                return $this->getRangeBrandSales($productNames, $brandName, $startDate, $endDate);
            default:
                return [];
        }
    }

    private function getDailyBrandSales($productNames, $brandName, $date)
    {
        $startDate = Carbon::parse($date)->setTimezone('Asia/Manila')->startOfDay()->utc();
        $endDate = Carbon::parse($date)->setTimezone('Asia/Manila')->endOfDay()->utc();

        $sales = Log::where('action', 'sale')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $brandSales = $sales->filter(function ($sale) use ($productNames) {
            return $this->saleContainsProducts($sale->details, $productNames);
        });

        $totalAmount = $this->extractAmountFromSales($brandSales);

        return [
            'brand' => $brandName,
            'totalAmount' => $totalAmount,
            'totalTransactions' => $brandSales->count(),
            'date' => $date,
        ];
    }

    private function getWeeklyBrandSales($productNames, $brandName, $date)
    {
        $startDate = Carbon::parse($date)->startOfWeek();
        $endDate = Carbon::parse($date)->endOfWeek();

        $sales = Log::where('action', 'sale')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $brandSales = $sales->filter(function ($sale) use ($productNames) {
            return $this->saleContainsProducts($sale->details, $productNames);
        });

        $dailyBreakdown = [];
        for ($i = 0; $i < 7; $i++) {
            $currentDay = $startDate->copy()->addDays($i);
            $daySales = $brandSales->filter(function ($sale) use ($currentDay) {
                return $sale->created_at->isSameDay($currentDay);
            });

            $dailyBreakdown[] = [
                'date' => $currentDay->toDateString(),
                'dayName' => $currentDay->format('l'),
                'amount' => $this->extractAmountFromSales($daySales),
                'transactions' => $daySales->count(),
            ];
        }

        $totalAmount = $this->extractAmountFromSales($brandSales);

        return [
            'brand' => $brandName,
            'totalAmount' => $totalAmount,
            'totalTransactions' => $brandSales->count(),
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'dailyBreakdown' => $dailyBreakdown,
        ];
    }

    private function getMonthlyBrandSales($productNames, $brandName, $year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $sales = Log::where('action', 'sale')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $brandSales = $sales->filter(function ($sale) use ($productNames) {
            return $this->saleContainsProducts($sale->details, $productNames);
        });

        $dailyBreakdown = [];
        $daysInMonth = $endDate->day;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDay = Carbon::createFromDate($year, $month, $day);
            $daySales = $brandSales->filter(function ($sale) use ($currentDay) {
                return $sale->created_at->isSameDay($currentDay);
            });

            $dailyBreakdown[] = [
                'date' => $currentDay->toDateString(),
                'day' => $day,
                'amount' => $this->extractAmountFromSales($daySales),
                'transactions' => $daySales->count(),
            ];
        }

        $totalAmount = $this->extractAmountFromSales($brandSales);

        return [
            'brand' => $brandName,
            'totalAmount' => $totalAmount,
            'totalTransactions' => $brandSales->count(),
            'year' => $year,
            'month' => $month,
            'monthName' => $startDate->format('F'),
            'dailyBreakdown' => $dailyBreakdown,
        ];
    }

    private function getRangeBrandSales($productNames, $brandName, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->setTimezone('Asia/Manila')->startOfDay()->utc();
        $end = Carbon::parse($endDate)->setTimezone('Asia/Manila')->endOfDay()->utc();

        $sales = Log::where('action', 'sale')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $brandSales = $sales->filter(function ($sale) use ($productNames) {
            return $this->saleContainsProducts($sale->details, $productNames);
        });

        $dailyBreakdown = [];
        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        while ($currentDate->lte($endDateCarbon)) {
            $daySales = $brandSales->filter(function ($sale) use ($currentDate) {
                return $sale->created_at->setTimezone('Asia/Manila')->isSameDay($currentDate);
            });

            $dailyBreakdown[] = [
                'date' => $currentDate->toDateString(),
                'dayName' => $currentDate->format('l'),
                'amount' => $this->extractAmountFromSales($daySales),
                'transactions' => $daySales->count(),
            ];

            $currentDate->addDay();
        }

        $totalAmount = $this->extractAmountFromSales($brandSales);

        return [
            'brand' => $brandName,
            'totalAmount' => $totalAmount,
            'totalTransactions' => $brandSales->count(),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dailyBreakdown' => $dailyBreakdown,
        ];
    }

    private function saleContainsProducts($details, $productNames)
    {
        if (empty($productNames) || !$details) {
            return false;
        }

        foreach ($productNames as $productName) {
            // Look for product name in the sales details
            // Format: "Processed sale: Test [Qty: 1], SkyFlakes Craker [Qty: 1]"
            if (stripos($details, $productName) !== false) {
                return true;
            }
        }

        return false;
    }

    private function getSalesSummary()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeekStart = Carbon::now()->startOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();
        $thisMonthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        return [
            'today' => $this->calculateSalesAmount($today->toDateString(), $today->toDateString()),
            'thisWeek' => $this->calculateSalesAmount($thisWeekStart->toDateString(), $today->toDateString()),
            'thisMonth' => $this->calculateSalesAmount($thisMonthStart->toDateString(), $today->toDateString()),
            'yesterday' => $this->calculateSalesAmount($yesterday->toDateString(), $yesterday->toDateString()),
            'lastWeek' => $this->calculateSalesAmount($lastWeekStart->toDateString(), $lastWeekEnd->toDateString()),
            'lastMonth' => $this->calculateSalesAmount($lastMonthStart->toDateString(), $lastMonthEnd->toDateString()),
        ];
    }

private function getDailySales($date)
{
    $startDate = Carbon::parse($date)->setTimezone('UTC')->startOfDay();
    $endDate = Carbon::parse($date)->setTimezone('UTC')->endOfDay();

    $startDate = Carbon::parse($date)->setTimezone('Asia/Manila')->startOfDay()->utc();
    $endDate = Carbon::parse($date)->setTimezone('Asia/Manila')->endOfDay()->utc();

    $sales = Log::where('action', 'sale')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->with('user')
        ->get();

    $totalAmount = $this->extractAmountFromSales($sales);
    $totalTransactions = $sales->count();

    return [
        'date' => $date,
        'totalAmount' => $totalAmount,
        'totalTransactions' => $totalTransactions,
        'averageTransaction' => $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0,
        'sales' => $sales->map(function ($sale) {
            return [
                'id' => $sale->id,
                'user' => $sale->user ? $sale->user->name : 'Unknown User',
                'details' => $sale->details,
                'amount' => $this->extractAmountFromDetails($sale->details),
                'created_at' => $sale->created_at->setTimezone('Asia/Manila')->format('g:i:s A'),
            ];
        })
    ];
}



    private function getWeeklySales($date)
    {
        $startDate = Carbon::parse($date)->startOfWeek();
        $endDate = Carbon::parse($date)->endOfWeek();

        $sales = Log::where('action', 'sale')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('user')
            ->get();

        $dailyBreakdown = [];
        for ($i = 0; $i < 7; $i++) {
            $currentDay = $startDate->copy()->addDays($i);
            $daySales = $sales->filter(function ($sale) use ($currentDay) {
                return $sale->created_at->isSameDay($currentDay);
            });

            $dailyBreakdown[] = [
                'date' => $currentDay->toDateString(),
                'dayName' => $currentDay->format('l'),
                'amount' => $this->extractAmountFromSales($daySales),
                'transactions' => $daySales->count(),
            ];
        }

        $totalAmount = $this->extractAmountFromSales($sales);

        return [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'totalAmount' => $totalAmount,
            'totalTransactions' => $sales->count(),
            'dailyBreakdown' => $dailyBreakdown,
        ];
    }

    private function getMonthlySales($year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $sales = Log::where('action', 'sale')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('user')
            ->get();

        $dailyBreakdown = [];
        $daysInMonth = $endDate->day;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDay = Carbon::createFromDate($year, $month, $day);
            $daySales = $sales->filter(function ($sale) use ($currentDay) {
                return $sale->created_at->isSameDay($currentDay);
            });

            $dailyBreakdown[] = [
                'date' => $currentDay->toDateString(),
                'day' => $day,
                'amount' => $this->extractAmountFromSales($daySales),
                'transactions' => $daySales->count(),
            ];
        }

        $totalAmount = $this->extractAmountFromSales($sales);

        return [
            'year' => $year,
            'month' => $month,
            'monthName' => $startDate->format('F'),
            'totalAmount' => $totalAmount,
            'totalTransactions' => $sales->count(),
            'dailyBreakdown' => $dailyBreakdown,
        ];
    }

    
private function getDailyChartData($date)
{
    $startDate = Carbon::parse($date)->setTimezone('Asia/Manila')->startOfDay()->utc();
    $endDate = Carbon::parse($date)->setTimezone('Asia/Manila')->endOfDay()->utc();

    $hourlyData = [];
    
    $philippinesDate = Carbon::parse($date)->setTimezone('Asia/Manila')->startOfDay();
    
    for ($hour = 0; $hour < 24; $hour++) {
        $hourStart = $philippinesDate->copy()->addHours($hour);
        $hourEnd = $hourStart->copy()->addHour();
        
        $hourStartUTC = $hourStart->copy()->utc();
        $hourEndUTC = $hourEnd->copy()->utc();

        $hourlySales = Log::where('action', 'sale')
            ->whereBetween('created_at', [$hourStartUTC, $hourEndUTC])
            ->get();

        $hour12 = $hourStart->format('g:00 A');

        $hourlyData[] = [
            'hour' => $hour12,
            'amount' => $this->extractAmountFromSales($hourlySales),
            'transactions' => $hourlySales->count(),
        ];
    }

    return $hourlyData;
}





    private function getWeeklyChartData($date)
    {
        $startDate = Carbon::parse($date)->startOfWeek();
        
        $weeklyData = [];
        for ($i = 0; $i < 7; $i++) {
            $currentDay = $startDate->copy()->addDays($i);
            $daySales = Log::where('action', 'sale')
                ->whereDate('created_at', $currentDay)
                ->get();

            $weeklyData[] = [
                'day' => $currentDay->format('l'),
                'date' => $currentDay->toDateString(),
                'amount' => $this->extractAmountFromSales($daySales),
                'transactions' => $daySales->count(),
            ];
        }

        return $weeklyData;
    }

    private function getMonthlyChartData($year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $startDate->daysInMonth;

        $monthlyData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDay = Carbon::createFromDate($year, $month, $day);
            $daySales = Log::where('action', 'sale')
                ->whereDate('created_at', $currentDay)
                ->get();

            $monthlyData[] = [
                'day' => $day,
                'date' => $currentDay->toDateString(),
                'amount' => $this->extractAmountFromSales($daySales),
                'transactions' => $daySales->count(),
            ];
        }

        return $monthlyData;
    }

    private function getRangeSales($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->setTimezone('Asia/Manila')->startOfDay()->utc();
        $end = Carbon::parse($endDate)->setTimezone('Asia/Manila')->endOfDay()->utc();

        $sales = Log::where('action', 'sale')
            ->whereBetween('created_at', [$start, $end])
            ->with('user')
            ->get();

        $dailyBreakdown = [];
        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        while ($currentDate->lte($endDateCarbon)) {
            $daySales = $sales->filter(function ($sale) use ($currentDate) {
                return $sale->created_at->setTimezone('Asia/Manila')->isSameDay($currentDate);
            });

            $dailyBreakdown[] = [
                'date' => $currentDate->toDateString(),
                'dayName' => $currentDate->format('l'),
                'amount' => $this->extractAmountFromSales($daySales),
                'transactions' => $daySales->count(),
            ];

            $currentDate->addDay();
        }

        $totalAmount = $this->extractAmountFromSales($sales);

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalAmount' => $totalAmount,
            'totalTransactions' => $sales->count(),
            'averageTransaction' => $sales->count() > 0 ? $totalAmount / $sales->count() : 0,
            'dailyBreakdown' => $dailyBreakdown,
        ];
    }

    private function getRangeChartData($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->setTimezone('Asia/Manila')->startOfDay()->utc();
        $end = Carbon::parse($endDate)->setTimezone('Asia/Manila')->endOfDay()->utc();

        $rangeData = [];
        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        while ($currentDate->lte($endDateCarbon)) {
            $daySales = Log::where('action', 'sale')
                ->whereDate('created_at', $currentDate->setTimezone('Asia/Manila'))
                ->get();

            $rangeData[] = [
                'day' => $currentDate->format('M j'),
                'date' => $currentDate->toDateString(),
                'amount' => $this->extractAmountFromSales($daySales),
                'transactions' => $daySales->count(),
            ];

            $currentDate->addDay();
        }

        return $rangeData;
    }

    private function calculateSalesAmount($startDate, $endDate)
    {
        $sales = Log::where('action', 'sale')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->get();

        return $this->extractAmountFromSales($sales);
    }

    private function extractAmountFromSales($sales)
    {
        $totalAmount = 0;
        
        foreach ($sales as $sale) {
            $amount = $this->extractAmountFromDetails($sale->details);
            $totalAmount += $amount;
        }

        return $totalAmount;
    }

    private function extractAmountFromDetails($details)
    {
        if (!$details) {
            return 0;
        }

        \Log::info('Extracting amount from details: ' . $details);

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
                $extractedAmount = (float) $amount;
                
                \Log::info('Extracted amount: ' . $extractedAmount . ' using pattern: ' . $pattern);
                
                return $extractedAmount;
            }
        }

        if (preg_match_all('/\d{1,3}(?:,\d{3})*\.?\d*/', $details, $matches)) {
            $numbers = array_map(function($match) {
                return (float) str_replace(',', '', $match);
            }, $matches[0]);
            
            if (!empty($numbers)) {
                $maxAmount = max($numbers);
                \Log::info('Using largest number found: ' . $maxAmount);
                return $maxAmount;
            }
        }

        \Log::warning('No amount found in details: ' . $details);
        return 0;
    }

    public function debugSalesData()
    {
        $recentSales = Log::where('action', 'sale')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'details', 'created_at']);

        foreach ($recentSales as $sale) {
            $extractedAmount = $this->extractAmountFromDetails($sale->details);
            echo "Sale ID: {$sale->id}<br>";
            echo "Details: {$sale->details}<br>";
            echo "Extracted Amount: {$extractedAmount}<br>";
            echo "Date: {$sale->created_at}<br>";
            echo "---<br>";
        }
    }
}