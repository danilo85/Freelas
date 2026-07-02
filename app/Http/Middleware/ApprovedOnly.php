<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApprovedOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->is_approved) {
            if (!$request->routeIs('waiting-approval') && !$request->routeIs('logout')) {
                return redirect()->route('waiting-approval');
            }
        }

        return $next($request);
    }
}
