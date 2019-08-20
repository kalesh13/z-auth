<?php

namespace Zauth;

use Illuminate\Database\Eloquent\Model;

class ZModels extends Model
{
    /**
     * The Eloquent user model. 
     * 
     * @var string
     */
    public $model;

    /**
     * Creates the new model instance. On model instantiation
     * config is read for determining the app user provider 
     * details.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->model = $this->initializeUserModel();
    }

    /**
     * Retrieve the User Model details from the config file. Usually
     * the User model \App\User, but applications can change this for
     * their particular use case.
     * 
     * So we read the config file for the data and use it in the plugin
     *
     * @return Illuminate\Database\Eloquent\Model|string|mixed
     */
    private function initializeUserModel()
    {
        $guard_name = Config::get('auth.defaults.guard');

        if (!Config::has("auth.guards.{$guard_name}")) {
            return;
        }        
        $provider = Config::get("auth.guards.{$guard_name}.provider");

        // If provider exists define the relation
        if (Config::has("auth.providers.{$provider}")) {
            return Config::get("auth.providers.{$provider}.model");
        }
    }
}