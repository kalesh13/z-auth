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
            // User is authenticated, but was not able to resolve
            // to a specified role. Return a forbidden response in this case.
            return $request->expectsJson()
                ? response()->json(['message' => 'Not authorized to access this section'], 403)
                : abort(403, 'Not authorized to access this section');
        }
        // User is not authenticated. This middleware grants access
        // only to authenticated users. Send a user unathenticated
        // response or redirect to login page.
        return $request->expectsJson()
            ? response()->json(['message' => 'User not authenticated'], 401)
            : redirect()->guest(route('login', ['redirectTo' => url()->current()]));
    }
}
