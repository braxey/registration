<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TimeoutMiddleware
{
    public function handle(Request $request, Closure $next, $timeout = null): Response
    {
        // Set the timeout in seconds
        set_time_limit($timeout);

        return $next($request);
    }
}
