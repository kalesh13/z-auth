<?php

namespace Zauth\Traits;

use Zauth\Zclient;

trait UserHasZclients
{
    public function apiClients()
    {
        return $this->hasMany(Zclient::class);
    }
}
