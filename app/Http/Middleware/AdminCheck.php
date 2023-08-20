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
            if (!$user->admin) {
                return response(Response::HTTP_UNAUTHORIZED);
            }
        } catch (Exception $e) {
            return response(Response::HTTP_UNAUTHENTICATED);
        }

        return $next($request);
    }
}
