<?php

namespace Zauth;

use Illuminate\Database\Eloquent\SoftDeletes;
use Zauth\Traits\IsOfUser;

/**
 * Zclient uses soft-delete, so that we can track which api
 * accessed the page and made requests anytime in the future.
 * 
 * Deleting zclient should not delete the client from the database.
 * This is mandatory.
 */
class Zclient extends ZModels
{
    use IsOfUser, SoftDeletes;

    /**
     * All the tokens held by zclient has to be expired when
     * the zclient is soft-deleted.
     */
    protected static function boot()
    {
        static::deleted(function (Zclient $model) {
            // Expire the tokens before deleting
            $model->accessTokens->each(function (Ztoken $token) {
                $token->expire();
            });
        });
        parent::boot();
    }

    /**
     * Gets the relation between client and token
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accessTokens()
    {
        return $this->hasMany(Ztoken::class, 'client_id');
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

    /**
     * Gets the name of this client
     * 
     * @return string
     */
    public function getClientName()
    {
        return $this->name;
    }
}
