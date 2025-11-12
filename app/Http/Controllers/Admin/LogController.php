<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Product;
use App\Models\Brand;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        $salesSummary = $this->getSalesSummary();

        return Inertia::render('Admin/Logs/Index', [
            'salesSummary' => $salesSummary
        ]);
    }

    public function getProductsList()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'item_code']);
        return response()->json($products);
    }

    public function activityLogs(Request $request)
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

        return Inertia::render('Admin/Logs/ActivityLogs', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'action', 'sort_by', 'sort_order', 'per_page'])
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

        // Build date range based on period
        $dateRange = $this->getDateRangeForReport($period, $date, $startDate, $endDate, $year, $month);

        $brands = Brand::with('products')->get();
        $brandSalesData = [];

        foreach ($brands as $brand) {
            $productNames = $brand->products->pluck('name')->toArray();

            $brandSales = $this->getBrandSalesForPeriod($productNames, $brand->name, $period, $date, $startDate, $endDate, $year, $month);

            // Only include brands with sales
            if ($brandSales['totalTransactions'] > 0) {
                $brandSalesData[] = $brandSales;
            }
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

    private function getDateRangeForReport($period, $date, $startDate, $endDate, $year, $month)
    {
        switch ($period) {
            case 'daily':
                return [
                    'start' => $date,
                    'end' => $date,
                ];
            case 'weekly':
                $start = Carbon::parse($date)->startOfWeek();
                $end = Carbon::parse($date)->endOfWeek();
                return [
                    'start' => $start,
                    'end' => $end,
                ];
            case 'monthly':
                $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                return [
                    'start' => $start,
                    'end' => $end,
                ];
            case 'range':
                return [
                    'start' => $startDate,
                    'end' => $endDate,
                ];
            default:
                return [
                    'start' => null,
                    'end' => null,
                ];
        }
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
        // Use simple date comparison instead of timezone conversion
        $sales = Log::where('action', 'sale')
            ->whereDate('created_at', $date)
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
    // Use simple date comparison instead of timezone conversion
    $sales = Log::where('action', 'sale')
        ->whereDate('created_at', $date)
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
    // Get all sales for this date
    $sales = Log::where('action', 'sale')
        ->whereDate('created_at', $date)
        ->get();

    $hourlyData = [];

    // Group sales by hour
    for ($hour = 0; $hour < 24; $hour++) {
        $hourlySales = $sales->filter(function ($sale) use ($hour) {
            return $sale->created_at->setTimezone('Asia/Manila')->hour == $hour;
        });

        $hour12 = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
        if ($hour < 12) {
            $hour12 = ($hour === 0 ? 12 : $hour) . ':00 AM';
        } else {
            $hour12 = ($hour === 12 ? 12 : $hour - 12) . ':00 PM';
        }

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

    public function incomeReport(Request $request)
    {
        $period = $request->get('period', 'daily');
        $date = $request->get('date', now()->toDateString());
        $startDate = $request->get('startDate', now()->toDateString());
        $endDate = $request->get('endDate', now()->toDateString());
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $productFilter = $request->get('product', '');

        $incomeData = [];

        switch ($period) {
            case 'daily':
                $incomeData = $this->getDailyIncome($date, $productFilter);
                break;
            case 'weekly':
                $incomeData = $this->getWeeklyIncome($date, $productFilter);
                break;
            case 'monthly':
                $incomeData = $this->getMonthlyIncome($year, $month, $productFilter);
                break;
            case 'range':
                $incomeData = $this->getRangeIncome($startDate, $endDate, $productFilter);
                break;
        }

        return response()->json([
            'incomeData' => $incomeData,
            'period' => $period,
            'date' => $date,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'year' => $year,
            'month' => $month,
            'productFilter' => $productFilter,
        ]);
    }

    private function getDailyIncome($date, $productFilter = '')
    {
        $startDate = Carbon::parse($date)->setTimezone('Asia/Manila')->startOfDay()->utc();
        $endDate = Carbon::parse($date)->setTimezone('Asia/Manila')->endOfDay()->utc();

        return $this->getIncomeForPeriod($startDate, $endDate, $productFilter);
    }

    private function getWeeklyIncome($date, $productFilter = '')
    {
        $startDate = Carbon::parse($date)->startOfWeek();
        $endDate = Carbon::parse($date)->endOfWeek();

        return $this->getIncomeForPeriod($startDate, $endDate, $productFilter);
    }

    private function getMonthlyIncome($year, $month, $productFilter = '')
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        return $this->getIncomeForPeriod($startDate, $endDate, $productFilter);
    }

    private function getRangeIncome($startDate, $endDate, $productFilter = '')
    {
        $start = Carbon::parse($startDate)->setTimezone('Asia/Manila')->startOfDay()->utc();
        $end = Carbon::parse($endDate)->setTimezone('Asia/Manila')->endOfDay()->utc();

        return $this->getIncomeForPeriod($start, $end, $productFilter);
    }

    private function getIncomeForPeriod($startDate, $endDate, $productFilter = '')
    {
        // Get all stock-out transactions (sales) in the period
        $stockOutQuery = StockTransaction::with(['product', 'user'])
            ->where('type', 'out')
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($productFilter) {
            $stockOutQuery->where('product_id', $productFilter);
        }

        $stockOuts = $stockOutQuery->get();

        $productSales = [];
        $totalRevenue = 0;
        $totalCost = 0;
        $totalProfit = 0;

        foreach ($stockOuts as $stockOut) {
            if (!$stockOut->product) continue;

            $product = $stockOut->product;
            $productId = $product->id;

            if (!isset($productSales[$productId])) {
                $productSales[$productId] = [
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'item_code' => $product->item_code,
                    'current_selling_price' => $product->price,
                    'total_quantity_sold' => 0,
                    'total_revenue' => 0,
                    'total_cost' => 0,
                    'total_profit' => 0,
                    'profit_margin' => 0,
                    'batches' => [],
                    'transactions' => [],
                ];
            }

            // Get selling price at time of sale (use current price as fallback)
            $sellingPrice = $product->price;
            $quantity = $stockOut->quantity;
            $unitCost = $stockOut->unit_cost ?? 0;
            $revenue = $sellingPrice * $quantity;
            $cost = $unitCost * $quantity;
            $profit = $revenue - $cost;

            $productSales[$productId]['total_quantity_sold'] += $quantity;
            $productSales[$productId]['total_revenue'] += $revenue;
            $productSales[$productId]['total_cost'] += $cost;
            $productSales[$productId]['total_profit'] += $profit;

            // Store batch allocation details
            if ($stockOut->batch_allocations) {
                foreach ($stockOut->batch_allocations as $batch) {
                    $productSales[$productId]['batches'][] = [
                        'batch_id' => $batch['batch_id'] ?? null,
                        'transaction_date' => $batch['transaction_date'] ?? null,
                        'quantity' => $batch['quantity'] ?? 0,
                        'unit_cost' => $batch['unit_cost'] ?? 0,
                        'subtotal' => $batch['subtotal'] ?? 0,
                    ];
                }
            }

            $productSales[$productId]['transactions'][] = [
                'id' => $stockOut->id,
                'date' => $stockOut->transaction_date,
                'quantity' => $quantity,
                'selling_price' => $sellingPrice,
                'unit_cost' => $unitCost,
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $profit,
                'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
                'user' => $stockOut->user ? $stockOut->user->name : 'Unknown',
                'reason' => $stockOut->reason,
            ];

            $totalRevenue += $revenue;
            $totalCost += $cost;
            $totalProfit += $profit;
        }

        // Calculate profit margin for each product
        foreach ($productSales as &$productSale) {
            if ($productSale['total_revenue'] > 0) {
                $productSale['profit_margin'] = ($productSale['total_profit'] / $productSale['total_revenue']) * 100;
            }

            // Get acquisition price history for this product
            $productSale['acquisition_history'] = $this->getAcquisitionPriceHistory($productSale['product_id'], $startDate, $endDate);
        }

        return [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'total_profit' => $totalProfit,
                'profit_margin' => $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0,
                'total_transactions' => $stockOuts->count(),
            ],
            'products' => array_values($productSales),
        ];
    }

    private function getAcquisitionPriceHistory($productId, $startDate, $endDate)
    {
        $stockIns = StockTransaction::where('product_id', $productId)
            ->where('type', 'in')
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('transaction_date', 'asc')
            ->get();

        $history = [];
        foreach ($stockIns as $stockIn) {
            $history[] = [
                'date' => $stockIn->transaction_date,
                'quantity' => $stockIn->quantity,
                'acquisition_price' => $stockIn->acquisition_price ?? 0,
                'remaining_quantity' => $stockIn->remaining_quantity ?? 0,
                'total_cost' => ($stockIn->acquisition_price ?? 0) * $stockIn->quantity,
            ];
        }

        return $history;
    }

    public function staffSalesReport(Request $request)
    {
        $period = $request->get('period', 'daily');
        $date = $request->get('date', now()->toDateString());
        $startDate = $request->get('startDate', now()->toDateString());
        $endDate = $request->get('endDate', now()->toDateString());
        $monthYear = $request->get('monthYear', now()->format('Y-m'));

        $staffSalesData = [];

        switch ($period) {
            case 'daily':
                $staffSalesData = $this->getStaffSalesByPeriod($date, null, 'daily');
                break;
            case 'weekly':
                $staffSalesData = $this->getStaffSalesByPeriod($date, null, 'weekly');
                break;
            case 'monthly':
                list($year, $month) = explode('-', $monthYear);
                $staffSalesData = $this->getStaffSalesByPeriod(null, ['year' => $year, 'month' => $month], 'monthly');
                break;
            case 'range':
                $staffSalesData = $this->getStaffSalesByDateRange($startDate, $endDate);
                break;
        }

        return response()->json([
            'staffSales' => $staffSalesData,
            'summary' => $this->calculateStaffSalesSummary($staffSalesData),
            'period' => $period,
        ]);
    }

    private function getStaffSalesByPeriod($date = null, $monthData = null, $type = 'daily')
    {
        if ($type === 'daily') {
            // Query by date string instead of timezone conversion
            $sales = Log::where('action', 'sale')
                ->whereDate('created_at', $date)
                ->with('user')
                ->get();
        } elseif ($type === 'weekly') {
            $startDate = Carbon::parse($date)->startOfWeek();
            $endDate = Carbon::parse($date)->endOfWeek();

            $sales = Log::where('action', 'sale')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->with('user')
                ->get();
        } elseif ($type === 'monthly') {
            $startDate = Carbon::createFromDate($monthData['year'], $monthData['month'], 1)->startOfMonth();
            $endDate = Carbon::createFromDate($monthData['year'], $monthData['month'], 1)->endOfMonth();

            $sales = Log::where('action', 'sale')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->with('user')
                ->get();
        }

        return $this->groupSalesByStaff($sales);
    }

    private function getStaffSalesByDateRange($startDate, $endDate)
    {
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $sales = Log::where('action', 'sale')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->with('user')
            ->get();

        return $this->groupSalesByStaff($sales);
    }

    private function groupSalesByStaff($sales)
    {
        $staffSales = [];

        foreach ($sales as $sale) {
            if (!$sale->user) {
                continue; // Skip sales without associated user
            }

            $staffId = $sale->user_id;
            $staffName = $sale->user->name ?? 'Unknown';
            $staffRole = $sale->user->role ?? 'staff';
            $amount = $this->extractAmountFromDetails($sale->details);

            if (!isset($staffSales[$staffId])) {
                $staffSales[$staffId] = [
                    'id' => $staffId,
                    'name' => $staffName,
                    'role' => $staffRole,
                    'totalSales' => 0,
                    'totalTransactions' => 0,
                    'percentage' => 0,
                ];
            }

            $staffSales[$staffId]['totalSales'] += $amount;
            $staffSales[$staffId]['totalTransactions']++;
        }

        // Calculate averages and percentages
        $totalSales = array_sum(array_column($staffSales, 'totalSales'));

        foreach ($staffSales as &$staff) {
            $staff['averageTransaction'] = $staff['totalTransactions'] > 0 ? $staff['totalSales'] / $staff['totalTransactions'] : 0;
            $staff['percentage'] = $totalSales > 0 ? ($staff['totalSales'] / $totalSales) * 100 : 0;
        }

        // Sort by total sales descending
        usort($staffSales, function ($a, $b) {
            return $b['totalSales'] <=> $a['totalSales'];
        });

        return array_values($staffSales);
    }

    private function calculateStaffSalesSummary($staffSalesData)
    {
        $totalSales = 0;
        $totalTransactions = 0;

        foreach ($staffSalesData as $staff) {
            $totalSales += $staff['totalSales'];
            $totalTransactions += $staff['totalTransactions'];
        }

        $staffCount = count($staffSalesData);
        $averagePerStaff = $staffCount > 0 ? $totalSales / $staffCount : 0;

        return [
            'totalSales' => $totalSales,
            'totalTransactions' => $totalTransactions,
            'averagePerStaff' => $averagePerStaff,
            'staffCount' => $staffCount,
        ];
    }

    public function getStaffTransactions($userId)
    {
        $sales = Log::where('action', 'sale')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($sale) {
                $details = $sale->details;

                // Parse the string format: "Processed sale: Product [Qty: X], Product [Qty: Y] - Total: PHP XXX - Payment: PHP XXX - Change: PHP XXX"
                $products = [];
                $totalAmount = 0;
                $paymentAmount = 0;
                $changeAmount = 0;

                // Extract products with quantities using regex
                // Matches: "Product Name [Qty: 2]"
                if (preg_match_all('/(.+?)\s*\[Qty:\s*(\d+)\]/', $details, $matches)) {
                    for ($i = 0; $i < count($matches[0]); $i++) {
                        $products[] = [
                            'name' => trim($matches[1][$i]),
                            'qty' => (int)$matches[2][$i],
                        ];
                    }
                }

                // Extract Total amount
                if (preg_match('/Total:\s*PHP\s*([\d,]+(?:\.\d{2})?)/i', $details, $match)) {
                    $totalAmount = (float)str_replace(',', '', $match[1]);
                }

                // Extract Payment amount
                if (preg_match('/Payment:\s*PHP\s*([\d,]+(?:\.\d{2})?)/i', $details, $match)) {
                    $paymentAmount = (float)str_replace(',', '', $match[1]);
                } else {
                    $paymentAmount = $totalAmount;
                }

                // Extract Change amount
                if (preg_match('/Change:\s*PHP\s*([\d,]+(?:\.\d{2})?)/i', $details, $match)) {
                    $changeAmount = (float)str_replace(',', '', $match[1]);
                }

                return [
                    'id' => $sale->id,
                    'products' => $products,
                    'total_amount' => $totalAmount,
                    'payment_amount' => $paymentAmount,
                    'change_amount' => $changeAmount,
                    'created_at' => $sale->created_at,
                ];
            });

        return response()->json($sales);
    }

    public function getStockTransactionLogs(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $type = $request->input('type', '');
            $perPage = intval($request->input('per_page')) ?: 10;
            $page = intval($request->input('page')) ?: 1;

            $query = Log::query();

            // Filter by type
            if ($type === 'in') {
                $query->where('action', 'stock_in');
            } elseif ($type === 'out') {
                $query->where('action', 'stock_out');
            } else {
                $query->whereIn('action', ['stock_in', 'stock_out']);
            }

            // Search filter
            if ($search) {
                $query->where('details', 'like', "%{$search}%");
            }

            // Get total count before pagination
            $total = $query->count();

            // Paginate
            $items = $query->with('user')
                ->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            $logs = [];
            foreach ($items as $log) {
                $logs[] = [
                    'id' => $log->id,
                    'action' => $log->action,
                    'details' => $log->details,
                    'user' => $log->user ? $log->user->name : 'Unknown User',
                    'created_at' => $log->created_at,
                ];
            }

            $lastPage = ceil($total / $perPage);
            $from = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
            $to = min($page * $perPage, $total);

            return response()->json([
                'data' => $logs,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteMultipleLogs(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'required|integer|exists:logs,id'
            ]);

            $deletedCount = count($validated['ids']);
            Log::whereIn('id', $validated['ids'])->delete();

            return redirect()->back()->with('success', "{$deletedCount} log(s) deleted successfully");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete logs: ' . $e->getMessage());
        }
    }

}