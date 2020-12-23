<?php

namespace Zauth;

use Zauth\Traits\IsOfUser;

class Zrole extends ZModels
{
    use IsOfUser;

    /**
     * Returns the role score of this model
     * 
     * @return int|null
     */
    public function score()
    {
        return $this->score;
    }

    /**
     * Returns the role name of this model
     * 
     * @return string
     */
    public function name()
    {
        return $this->role;
    }
}
