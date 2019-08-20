<?php

namespace Zauth\Guards\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Zauth\Zclient;

trait GrantsToken
{
    /**
     * Validates the client credentials and issues a token
     * 
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return Ztoken|null
     */
    public function grantToken(Request $request, Authenticatable $user = null)
    {
        $client = $this->getClientFromRequest($request);

        $client_secret = $this->getClientSecretFromRequest($request);

        if ($client_secret !== $client->getClientSecret()) {
            abort(403, 'Invalid API client credentials.');
        }
        // If a user instance has been passed to this function
        // generate a token for that user. A user instance will be
        // passed only if the login was authorized with email and password
        // by the guard.
        //
        // If no user instance is passed, check if the client has any user 
        // associated to it. Only external clients will have user associated to it.
        if (!isset($user)) {
            $user = $client->user;
        }
        // generatesToken is a function defined in the trait UserHasZtokens.
        // If any project uses this library, the \App\User model should use this
        // trait.
        if ($user && method_exists($user, 'generateToken')) {
            return $user->generateToken($request, $client);
        }
    }

    /**
     * Gets the client from the request. All authorization
     * requests should have client_id in the request.
     * 
     * @param \Illuminate\Http\Request $request
     * @return Zclient
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getClientFromRequest(Request $request)
    {
        if (!$request->has('client_id')) {
            abort(403, 'Request missing client id');
        }
        $client = Zclient::where('client_id', $request->input('client_id'))->first();

        if (!$client) {
            abort(403, 'Invalid client id');
        }
        return $client;
    }

    /**
     * Gets the client_secret from the request
     * 
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    private function getClientSecretFromRequest(Request $request)
    {
        return $request->input('client_secret');
    }
}
