<?php

namespace Zauth\Http\Middleware;

use Closure;
use Zauth\Guards\Traits\HasZtokens;

class CheckClient
{
    use HasZtokens;

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
        $token = $this->getZtokenFromRequest($request);

        if ($token && !$token->hasExpired() && $client = $token->client) {
            // If request has a valid token, get the client
            // that issued the token. Iterate through all the 
            // $clients to check if the token client name matches.
            foreach ($clients as $client_name) {
                // If client name matches with token client
                // name, proceed with the next operation in
                // the pipeline
                if ($client_name === $client->getClientName()) {
                    return $next($request);
                };
            }
            // User is authenticated, but was not able to resolve
            // to a specified client. Return a forbidden response in this case.
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
