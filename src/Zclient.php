<?php

namespace Zauth;

use Zauth\Traits\IsOfUser;

class Zclient extends ZModels
{
    use IsOfUser;

    public function accessTokens()
    {
        return $this->hasMany(Ztoken::class);
    }

    /**
     * Gets the client id of this client
     * 
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Gets the client secret of this client
     * 
     * @return string
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }
}
