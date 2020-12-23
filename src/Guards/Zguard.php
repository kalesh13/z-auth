<?php

namespace Zauth\Guards;

use Illuminate\Http\Request;
use Illuminate\Auth\GuardHelpers;
use Zauth\Guards\Traits\HasZtokens;
use Zauth\Guards\Traits\HasCookies;
use Zauth\Guards\Traits\HasRemember;
use Zauth\Guards\Traits\GrantsToken;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;

class Zguard implements StatefulGuard
{
    use HasCookies;
    use HasZtokens;
    use HasRemember;
    use GrantsToken;
    use GuardHelpers;

    /**
     * The user we last attempted to retrieve.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $lastAttempted;

    /**
     * Indicates if the logout method has been called.
     *
     * @var bool
     */
    protected $loggedOut = false;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider $provider
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Contracts\Cookie\QueueingFactory $cookie
     * @param string $access_key
     */
    public function __construct(UserProvider $provider, Request $request = null, CookieJar $cookie = null)
    {
        $this->request = $request;
        $this->cookie = $cookie;
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if ($this->loggedOut) {
            return null;
        }
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }

        // If the user is null, but we decrypt a "recaller" cookie we can attempt to
        // pull the user data on that cookie which serves as a remember cookie on
        // the application. Once we have a user we can return it to the caller.
        $this->user = $this->getUserFromRemember($this->request, $this->provider);

        if ($this->user) {
            $this->setUser($this->user);
        }

        // At this point we have to find the user using the token
        // from the request
        if (is_null($this->user)) {
            $token = $this->getTokenFromRequest($this->request);

            if (!empty($token)) {
                $this->user = $this->userFromToken($this->provider, $token);
            }
        }
        return $this->user;
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;

        $this->loggedOut = false;

        return $this;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array  $credentials
     * @param  bool   $remember
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        // If an implementation of UserInterface was returned, we'll ask the provider
        // to validate the user against the given credentials, and if they are in
        // fact valid we'll log the users into the application and return true.
        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }
        return false;
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param  mixed $user
     * @param  array $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        return !is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function once(array $credentials = [])
    {
        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);

            return true;
        }
        return false;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        return $this->hasValidCredentials($user, $credentials);
    }

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(Authenticatable $user, $remember = false)
    {
        $token = $this->grantToken($this->request->all(), $user);

        if (!$token) {
            return;
        }
        // Queue the login cookie
        $this->queueCookie($this->getTokenName(), $token->token(), $token->getExpiryMinutes());

        // If the user should be permanently "remembered" by the application we will
        // queue a permanent cookie that contains the encrypted copy of the user
        // identifier. We will then decrypt this later to retrieve the users.
        if ($remember) {
            $this->ensureRememberTokenIsSet($user, $this->provider);

            $this->queueRecallerCookie(
                $this->getRecallerName(),
                $this->getRecallerCookieValue($user)
            );
        }
        $this->setUser($user);
    }

    /**
     * Log the given user ID into the application.
     *
     * @param  mixed  $id
     * @param  bool   $remember
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function loginUsingId($id, $remember = false)
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            $this->login($user, $remember);

            return $user;
        }
        return false;
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param  mixed  $id
     * @return bool
     */
    public function onceUsingId($id)
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            $this->setUser($user);

            return $user;
        }
        return false;
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember()
    {
        return $this->viaRemember;
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();

        // If we have an event dispatcher instance, we can fire off the logout event
        // so any further processing can be done. This allows the developer to be
        // listening for anytime a user signs out of this application manually.
        $this->clearUserDataFromStorage();

        if (!is_null($this->user)) {
            // Expire the current request token if the user has
            // function expireToken which is part of UserHasZtoken trait
            $user->expireToken($this->getZtokenFromRequest($this->request));

            $this->cycleRememberToken($user, $this->provider);
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;

        $this->loggedOut = true;
    }

    /**
     * Remove the user data from the cookies.
     *
     * @return void
     */
    protected function clearUserDataFromStorage()
    {
        if (!is_null($this->hasToken($this->request))) {
            $this->getCookieJar()->queue($this->getCookieJar()->forget($this->getTokenName()));
        }

        if (!is_null($this->recaller($this->request))) {
            $this->getCookieJar()->queue($this->getCookieJar()->forget($this->getRecallerName()));
        }
    }
}
