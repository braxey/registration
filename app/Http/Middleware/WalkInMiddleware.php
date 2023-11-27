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
            return response('walk-in not found', 404);
        }

        $request->offsetSet('walk-in', $walkIn);

        if (session('walk-in-dry-run') === true) {
            return response('passes walk-in middleware', 202);
        }

        return $next($request);
    }
}
