<?php

namespace Zauth;

use Zauth\Traits\IsOfUser;

class Zrole extends ZModels
{
    use IsOfUser;

    protected $fillable = ['role'];

    /**
     * Returns the role score of this model
     * 
     * @return int|null
     */
    public function getRole()
    {
        return $this->role;
    }
}
