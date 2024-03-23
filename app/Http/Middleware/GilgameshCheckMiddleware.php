<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class GilgameshCheckMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = Auth::user();
            if (!$user->isGilgamesh()) {
                return response('you do not have access', Response::HTTP_UNAUTHORIZED);
            }

            $request->offsetSet('sessionUser', $user);

            if (session('gilgamesh-check-dry-run') === true) {
                return response('passes gilgamesh check middleware', 202);
            }

            return $next($request);
        } catch (Exception $e) {
            return response(null, Response::HTTP_UNAUTHENTICATED);
        }
    }
}
