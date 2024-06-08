<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

/**
 * @see UserMiddlewareTest
 */
class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::fromId($request->route('userId'));
        if ($user === null) {
            return response('user not found', 404);
        }

        $request->offsetSet('user', $user);

        if (session('user-dry-run') === true) {
            return response('passes user middleware', 202);
        }

        return $next($request);
    }
}
