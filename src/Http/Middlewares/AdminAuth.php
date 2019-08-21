<?php

namespace Zauth\Http\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        
        if (!$user || !$user->isAdministrator()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'User not authenticated'], 401)
                : redirect()->guest(route('admin.login', ['redirectTo' => url()->current()]));
        }
        return $next($request);
    }
}