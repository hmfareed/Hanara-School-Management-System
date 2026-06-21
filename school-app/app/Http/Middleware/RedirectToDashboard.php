<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToDashboard
{
    /**
     * Redirect authenticated users to their role-based dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $route = auth()->user()->getDashboardRoute();
            return redirect()->route($route);
        }

        return $next($request);
    }
}
