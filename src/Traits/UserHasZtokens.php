<?php

namespace Zauth\Traits;

use Zauth\Ztoken;
use Zauth\Zclient;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

trait UserHasZtokens
{
    /**
     * Token expiry in hours.
     *
     * @var int|null
     */
    protected $token_expiry;

    /**
     * Current user token from the request
     * 
     * @var Ztoken
     */
    public $current_token;

    /**
     * Returns the access_token relation.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accessTokens()
    {
        return $this->hasMany(Ztoken::class);
    }

    /**
     * Set the token expiry in hours
     * 
     * @param int $value
     */
    public function setTokenExpiry($value)
    {
        if (is_int($value)) {
            $this->token_expiry = $value;
        }
    }

    /**
     * Gets the token expiry set by the user. Override this function
     * on \App\User to have custom expiry duration.
     * 
     * @return int
     */
    public function getTokenExpiry()
    {
        return $this->token_expiry;
    }

    /**
     * Expire all the tokens of this user. This is used
     * when user wants to log out of all sessions. In all other 
     * cases expireToken has to be called which expires only the
     * current token
     */
    public function expireAll()
    {
        $tokens = $this->accessTokens;

        foreach ($tokens ?: [] as $token) {
            $this->expireToken($token);
        }
    }

    /**
     * Expires the given token
     * 
     * @param Ztoken $token
     * @return void
     */
    public function expireToken(Ztoken $token = null)
    {
        if (is_null($token)) {
            return;
        }
        $token->expire();

        if ($this->current_token === $token) {
            $this->setCurrentToken();
        }
    }

    /**
     * Sets the current token of this user.
     * 
     * @param Ztoken $token
     * @return Ztoken
     */
    public function setCurrentToken(Ztoken $token = null)
    {
        return $this->current_token = $token;
    }

    /**
     * Gets the current token of this user.
     * 
     * @return Ztoken
     */
    public function activeToken()
    {
        return $this->current_token;
    }

    /**
     * Check whether the active token is authorized via
     * the given client.
     * 
     * @param string $client_name
     * @return bool
     */
    public function authorizedViaClient($client_name)
    {
        if ($token = $this->activeToken()) {
            $client = $token->client;

            return $client && ($client_name === $client->getClientName());
        }
        return false;
    }

    /**
     * Generates a token for this user and the specified client.
     * 
     * @param \Illuminate\Support\Collection $request
     * @param \Zauth\Zclient $client
     * @return \Zauth\Ztoken|bool
     */
    public function generateToken(Collection $request, Zclient $client)
    {
        $token = new Ztoken();
        $token->ip = $request->get('ip');
        $token->token(Str::random(60));
        $token->setExpiry($this->getTokenExpiry());

        $token->client()->associate($client);

        return $this->assignToken($token);
    }

    /**
     * Sets the token to this user.
     * 
     * @param Ztoken $token
     * @return Ztoken|bool
     */
    public function assignToken(Ztoken $token)
    {
        if ($this->accessTokens()->save($token)) {
            return $this->setCurrentToken($token);
        }
        return false;
    }

    /**
     * Returns the current access/api token of this user.
     * 
     * @return \Zauth\Ztoken|null
     */
    public function getAccessTokenAttribute()
    {
        return $this->activeToken()->makeHidden(['client_id', 'user_id', 'ip']);
    }
}
