<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortfolioMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $setting = \App\Models\SystemSetting::first();
        if ($setting && $setting->portfolio_maintenance) {
            if (!auth()->check()) {
                return response()->view('errors.maintenance', [], 503);
            }
        }

        return $next($request);
    }
}
