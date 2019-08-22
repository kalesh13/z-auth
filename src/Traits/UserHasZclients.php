<?php

namespace Zauth\Traits;

use Zauth\Zclient;

trait UserHasZclients
{
    /**
     * Gets the relation with user and zclient.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function apiClients()
    {
        return $this->hasMany(Zclient::class);
    }
}
