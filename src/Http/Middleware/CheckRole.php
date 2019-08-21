<?php

namespace Zauth\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();

        if ($user) {
            foreach ($roles as $role) {
                // If the user have a role as defined in the $roles,
                // proceed to the next closure.
                if ($user->hasRole($role)) {
                    return $next($request);
                }
            }
        }
        return $request->expectsJson()
            ? response()->json(['message' => 'Not authorized to access this section'], 403)
            : abort(403);
    }
}
