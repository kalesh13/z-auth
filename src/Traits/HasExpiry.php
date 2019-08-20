<?php

namespace Zauth\Traits;

use Carbon\Carbon;

trait HasExpiry
{
    /**
     * Token expiry time in hours.
     *
     * @var int
     */
    protected $token_expiry = 4;

    /**
     * Expires the token.
     * 
     * @return void
     */
    public function expire()
    {
        $this->setExpiry(0);
    }

    /**
     * Sets the expiry of the token to $value hours from now. 
     * If no parameter value is specified, token expiry is set to 
     * now.
     * 
     * For eg, if parameter is 4, token expiry is set to 4 hours from now
     * 
     * @param int|null $value
     * @return void
     */
    public function setExpiry($value = null)
    {
        $expiry = $this->token_expiry;

        if (is_int($value)) {
            $expiry = $value;
        }
        $this->expiry = Carbon::now()->addHours($expiry);
    }

    /**
     * Returns the time (in minutes) left for the expiry of token
     * from current moment.
     * 
     * @return int
     */
    public function getExpiryMinutes()
    {
        $expiryInMinutes = Carbon::parse($this->expiry)->diffInRealMinutes(Carbon::now());

        if ($expiryInMinutes > 0) {
            return $expiryInMinutes;
        }
        return 0;
    }

    /**
     * Checks if the token has expired. Token expiry is
     * compared to now, accurate to seconds, to determine if
     * the token has expired.
     * 
     * @return bool
     */
    public function hasExpired()
    {
        $ttl_difference = Carbon::parse($this->expiry)->diffInRealSeconds(Carbon::now());

        return $ttl_difference <= 0;
    }
}
