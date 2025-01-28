<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserTypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$types)
    {
        $user = $request->user();

        // Check if user is authenticated and their user_type is allowed
        if (!$user || !in_array((int) $user->user_type, $types)) {
            // Abort with a 403 Forbidden response
            return abort(Response::HTTP_FORBIDDEN, 'Unauthorized access.');
        }

        return $next($request);
    }
}
