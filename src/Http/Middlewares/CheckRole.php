<?php

namespace Zauth\Http\Middlewares;

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
            // redirect the user to homepage
            if (!$user->hasRole($role)) {
                return redirect('/');
            }
        }
        return $next($request);
    }
}