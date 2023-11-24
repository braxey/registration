<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = Auth::user();
            if (!$user->isAdmin()) {
                return response(null, Response::HTTP_UNAUTHORIZED);
            }

            $request->offsetSet('user', $user);

            return $next($request);
        } catch (Exception $e) {
            return response(null, Response::HTTP_UNAUTHENTICATED);
        }
    }
}
