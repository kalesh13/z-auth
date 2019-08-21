<?php

namespace Zauth\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param array $roles
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $request->user();

        foreach ($roles as $role) {
            // If the user don't have a role as defined in the $roles,
            // redirect the user to login
            if (!$user->hasRole($role)) {
                return $request->expectsJson()
                ? response()->json(['message' => 'Not authorized to access this section'], 403)
                : abort(403);
            }
        }
        return $next($request);
    }
}