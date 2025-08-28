<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Log;

class LogActivity
{
    public function handle(Request $request, Closure $next, $action)
    {
        $response = $next($request);

        if (auth()->check()) {
            Log::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'details' => $this->getDetails($request, $action),
                'ip_address' => $request->ip(),
            ]);
        }

        return $response;
    }

    protected function getDetails(Request $request, $action)
    {
        $details = $request->session()->get('log_details', null);

        if ($details) {
            $request->session()->forget('log_details');
            return $details;
        }

        return match ($action) {
            'login' => 'User logged in.',
            'stock_in' => 'Stock added.',
            'stock_out' => 'Stock removed.',
            'sale' => 'Sale recorded.',
            default => null,
        };
    }
}