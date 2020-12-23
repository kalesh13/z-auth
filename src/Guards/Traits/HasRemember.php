<?php

namespace Zauth\Guards\Traits;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Auth\Recaller;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

trait HasRemember
{
    /**
     * Indicates if the user was authenticated via a recaller cookie.
     *
     * @var bool
     */
    protected $viaRemember = false;

    /**
     * Indicates if a token user retrieval has been attempted.
     *
     * @var bool
     */
    protected $recallAttempted = false;


    public function getUserFromRemember(Request $request, UserProvider $provider)
    {
        $recaller = $this->recaller($request);

        if (!is_null($recaller)) {
            return $this->userFromRecaller($recaller, $provider);
        }
    }

    /**
     * Get the decrypted recaller cookie for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Auth\Recaller|null
     */
    protected function recaller(Request $request)
    {
        if (is_null($request)) {
            return null;
        }

        if ($recaller = $request->cookies->get($this->getRecallerName())) {
            return new Recaller($recaller);
        }
    }

    /**
     * Pull a user from the repository by its "remember me" cookie token.
     *
     * @param \Illuminate\Auth\Recaller $recaller
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @return mixed
     */
    protected function userFromRecaller($recaller, $provider)
    {
        if (!$recaller->valid() || $this->recallAttempted) {
            return null;
        }

        if ($user = $this->retreiveFromCache($recaller->token())) {
            return $user;
        }

        // If the user is null, but we decrypt a "recaller" cookie we can attempt to
        // pull the user data on that cookie which serves as a remember cookie on
        // the application. Once we have a user we can return it to the caller.
        $this->recallAttempted = true;

        $this->viaRemember = !is_null($user = $provider->retrieveByToken(
            $recaller->id(),
            $recaller->token()
        ));

        return $this->storeInCache($recaller->token(), $user);
    }

    /**
     * Create a new "remember me" token for the user if one doesn't already exist.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @return void
     */
    protected function ensureRememberTokenIsSet(Authenticatable $user, UserProvider $provider)
    {
        if (empty($user->getRememberToken())) {
            $this->cycleRememberToken($user, $provider);
        }
    }

    /**
     * Refresh the "remember me" token for the user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @return void
     */
    protected function cycleRememberToken(Authenticatable $user, UserProvider $provider)
    {
        $user->setRememberToken($token = Str::random(60));

        $provider->updateRememberToken($user, $token);
    }

    /**
     * Get the recaller cookie value
     * 
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return string
     */
    protected function getRecallerCookieValue(Authenticatable $user)
    {
        return $user->getAuthIdentifier() . '|' .
            $user->getRememberToken() . '|' .
            $user->getAuthPassword();
    }

    /**
     * Get the name of the cookie used to store the "recaller".
     *
     * @return string
     */
    public function getRecallerName()
    {
        return 'remember_' . sha1(static::class);
    }
}
