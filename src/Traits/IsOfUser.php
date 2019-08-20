<?php

namespace Zauth\Traits;

trait IsOfUser
{
    /**
     * The user to which this token belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo($this->model);
    }    

    /**
     * Returns the primary id of the user
     * 
     * @return int|mixed
     */
    public function getUserId()
    {
        return $this->{$this->user()->getForeignKeyName()};
    }
}