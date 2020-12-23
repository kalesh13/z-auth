<?php

namespace Zauth\Guards\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Zauth\Zclient;
use Zauth\Ztoken;

trait GrantsToken
{
    /**
     * Validates the client credentials and issues a token
     * 
     * @param array $request
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return Ztoken|null
     */
    public function grantToken($request, Authenticatable $user = null)
    {
        $client = $this->getClientFromRequest($request = collect($request));

        // A token can be issued in two ways --
        // [1] By submitted email, password, and a client id.
        // [2] By submitted client_id and client_secret.
        //
        // To obtain a token using email and password, the $user parameter should
        // not be null ie, function calling this function should retreive the user
        // for the given credentials and send it to this function.
        //
        // To obtain a token using client_id and client_secret, keep the second argument
        // of this function as null. We will retreive the client_id and client_secret from
        // the request and retreive the user linked to the client.
        //
        // In short, if a user instance has been passed to this function generate a token 
        // for that user. A user instance will be passed only if the login was authorized 
        // with email and password by the guard, or if a $user value is explicitely submitted.
        //
        // If no user instance is passed, check if the client has any user associated to it. 
        // Only external clients will have user associated to it.
        if (!isset($user)) {
            $client_secret = $this->getClientSecretFromRequest($request);

            if ($client_secret !== $client->getClientSecret()) {
                return abort(403, 'Invalid API client credentials.');
            }
            $user = $client->user;
        }
        // generatesToken is a function defined in the trait UserHasZtokens.
        // If any project uses this library, the \App\User model should use this
        // trait.
        if ($user) {
            return $user->generateToken($request, $client);
        }
    }

    /**
     * Gets the client from the request. All authorization requests will (should) have 
     * `client_id` in the request.
     * 
     * @param \Illuminate\Support\Collection $request
     * @return \Zauth\Zclient
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function getClientFromRequest(Collection $request)
    {
        if (!$request->has('client_id')) {
            return abort(403, 'Request missing client id');
        }
        $client = Zclient::where('client_id', $request->get('client_id'))->first();

        if (!$client) {
            return abort(403, 'Invalid client id');
        }
        return $client;
    }

    /**
     * Gets the client_secret from the request
     * 
     * @param \Illuminate\Support\Collection $request
     * @return string|null
     */
    private function getClientSecretFromRequest(Collection $request)
    {
        return $request->get('client_secret');
    }
}
