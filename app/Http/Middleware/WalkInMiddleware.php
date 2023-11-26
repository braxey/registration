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

        if (session('dry-run') === true) {
            return response(null, 202);
        }

        return $next($request);
    }
}
