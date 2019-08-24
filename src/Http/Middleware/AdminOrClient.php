<?php

namespace Zauth\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminOrClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param array $roles
     * @return mixed
     */
    public function handle($request, Closure $next, ...$clients)
    {
        $user = Auth::user();

        if ($user) {
            // Check if user is an admin. If true, we will proceed to 
            // the next pipeline operation.
            if ($user->isAdministrator()) {
                return $next($request);
            }
            // User is not an administrator at this point. Check
            // if user is authorized through any of the clients
            // mentioned in $clients. If true, next pripeline
            // operation is carried out.
            return (new CheckClient())->handle($request, $next, ...$clients);
        }
        // User is not authenticated. This middleware grants access
        // only to authenticated users. Send a user unathenticated
        // response or redirect to login page.
        return $request->expectsJson()
            ? response()->json(['message' => 'User not authenticated'], 401)
            : redirect()->guest(route('login', ['redirectTo' => url()->current()]));
    }
}
