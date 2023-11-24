<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\WalkIn;

class WalkInMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $walkIn = WalkIn::fromId($request->route('walkInId'));
        if ($walkIn === null) {
            return response(null, 404);
        }

        $request->offsetSet('walk-in', $walkIn);

        return $next($request);
    }
}
