<?php

namespace Zauth;

use Zauth\Traits\IsOfUser;
use Zauth\Traits\HasExpiry;

class Ztoken extends ZModels
{
    use HasExpiry, IsOfUser;

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Column name of the token in the database
     * 
     * @var string
     */
    const TOKEN_COLUMN = 'api_token';

    /**
     * Returns the relation with zclient
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Zclient::class);
    }

    /**
     * Sets the token value of this model if a token is
     * submitted and returns the token value of this model.
     * 
     * @param string|null $token
     * @return string
     */
    public function token($token = null)
    {
        if (isset($token)) {
            $this->{static::TOKEN_COLUMN} = $token;
        }
        return $this->{static::TOKEN_COLUMN};
    }
}
