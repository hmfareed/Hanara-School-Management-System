<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Users with must_change_password=true are forced to the change-password page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->must_change_password) {
            // Allow access to the change-password route and logout
            if ($request->routeIs('password.change', 'password.change.update', 'logout')) {
                return $next($request);
            }

            return redirect()->route('password.change')
                ->with('warning', 'You must change your password before continuing.');
        }

        return $next($request);
    }
}
