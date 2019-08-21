<?php

namespace Zauth\Guards\Traits;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Zauth\Ztoken;

trait HasZtokens
{
    /**
     * Gets the Ztoken model from the request token, if
     * a token exists or null.
     * 
     * @param \Illuminate\Http\Request $request
     * @return Ztoken|null
     */
    public function getZtokenFromRequest(Request $request)
    {
        $token = $this->getTokenFromRequest($request);

        if (!empty($token)) {
            return Ztoken::where(Ztoken::TOKEN_COLUMN, $token)->first();
        }
    }

    /**
     * Get the token from the current request. Tokens are searched
     * in the cookies first, followed by GET parameter, form input,
     * authorization header and pasword header.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    public function getTokenFromRequest(Request $request)
    {
        // This package is designed to send a token to 
        // cookie, most of the time. So we check for token 
        // in the cookie first.
        $token = $this->hasToken($request);

        // Check for the presence of token in GET parameters
        if (empty($token)) {
            $token = $request->query($this->getRequestTokenName());
        }
        // Check for the presence of token in request input
        if (empty($token)) {
            $token = $request->input($this->getRequestTokenName());
        }
        // Check for the presence of token in authorization header
        if (empty($token)) {
            $token = $request->bearerToken();
        }
        // Check for the presence of token in PHP_AUTH_PWD header
        if (empty($token)) {
            $token = $request->getPassword();
        }

        return $token;
    }

    /**
     * Gets the user from the token.
     * 
     * Uses array cache to store and retreive the user (if available), 
     * so that multiple calls during the same request won't hit the 
     * database.
     * 
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @param string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function userFromToken(UserProvider $provider, $token)
    {
        if ($user = $this->retreiveFromCache($token)) {
            return $user;
        }
        /**
         * If the cache does not contain an entry for the token,
         * get the token details from the database.
         * @var Ztoken $accessToken
         */
        $accessToken = Ztoken::where(Ztoken::TOKEN_COLUMN, $token)->first();

        if (!empty($accessToken) && !$accessToken->hasExpired()) {
            // Gets the user_id of the token. getUserId function
            // is part of a trait IsOfUser, which Ztoken must use.
            $id = $accessToken->getUserId();

            // If a value is set for $id, store the access_token
            // in the cache along with the user.
            if (isset($id)) {
                return $this->storeInCache($token, $provider->retrieveById($id));
            }
        }
        return null;
    }

    /**
     * Retreives the user stored in the cache for the token
     * $key
     * 
     * @param string $key
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    private function retreiveFromCache($key)
    {
        return cache()->store('array')->get($key);
    }

    /**
     * Stores the user in the cache with the token as key.
     * 
     * @param string $key
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $value
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    private function storeInCache($key, $value)
    {
        cache()->store('array')->forever($key, $value);

        return $value;
    }

    /**
     * Get the decrypted token cookie for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function hasToken(Request $request)
    {
        if (is_null($request)) {
            return null;
        }
        return $request->cookies->get($this->getTokenName());
    }

    /**
     * Get the name of the cookie used to store the "token".
     *
     * @return string
     */
    public function getTokenName()
    {
        return 'token_' . sha1(static::class);
    }

    /**
     * Get the name of the GET or POST field used to store 
     * the "token".
     *
     * @return string
     */
    public function getRequestTokenName()
    {
        return 'access_token';
    }
}
