<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminCheckMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = Auth::user();
            if (!$user->isAdmin()) {
                return response('not an admin', Response::HTTP_UNAUTHORIZED);
            }

            $request->offsetSet('user', $user);

            if (session('admin-check-dry-run') === true) {
                return response('passes admin check middleware', 202);
            }

            return $next($request);
        } catch (Exception $e) {
            return response(null, Response::HTTP_UNAUTHENTICATED);
        }
    }
}
