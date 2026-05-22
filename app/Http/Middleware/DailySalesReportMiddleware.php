<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SendDailySalesReport;


class DailySalesReportMiddleware
{
    public function handle($request, Closure $next)
    {
        // Only run once per day
        $key = 'daily_sales_report_sent_' . now()->toDateString();

        if (!Cache::has($key) && now()->hour >= 22) { // after 10 PM
            SendDailySalesReport::dispatch();
            Cache::put($key, true, now()->endOfDay());
        }

        return $next($request);
    }

}
