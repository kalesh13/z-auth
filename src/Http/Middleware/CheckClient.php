<?php

namespace Zauth\Http\Middleware;

use Illuminate\Support\Facades\Auth;

class CheckClient
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

        foreach ($clients as $client) {
            // If the user logged in through client $client,
            // proceed to the next closure.
            if ($user->authorizedViaClient($client)) {
                return $next($request);
            }
        }
        return $request->expectsJson()
            ? response()->json(['message' => 'Not authorized to access this section'], 403)
            : abort(403);
    }
}