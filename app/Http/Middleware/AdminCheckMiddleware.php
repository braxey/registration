<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

/**
 * @see AdminCheckMiddlewareTest
 */
class AdminCheckMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $sessionUser = Auth::user();
            if (!$sessionUser->isAdmin()) {
                return response('not an admin', Response::HTTP_UNAUTHORIZED);
            }

            $request->offsetSet('sessionUser', $sessionUser);

            if (session('admin-check-dry-run') === true) {
                return response('passes admin check middleware', 202);
            }

            return $next($request);
        } catch (Exception $e) {
            return response(null, Response::HTTP_UNAUTHENTICATED);
        }
    }
}
