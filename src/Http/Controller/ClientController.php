<?php

namespace Zauth\Http\Controller;

use Zauth\Zclient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Zauth\Http\Exceptions\ClientCreateException;

class ClientController
{
    /**
     * Creates a client and returns it. The client object won't be saved
     * by this function. Returned client object has to be saved by the 
     * function caller. This makes sure that this controller can be used
     * by the application.
     * 
     * @param string $name
     * @param bool $needs_secret
     * @return Zclient
     */
    public function create(string $name, $needs_secret = true)
    {
        if ($errors = $this->validationFailed($name)) {
            throw new ClientCreateException($errors['name'][0]);
        }
        $client = new Zclient();
        $client->name = $name;
        $client->client_id = Str::random(30);
        $client->client_secret = $needs_secret ? Str::random(60) : null;

        return $client;
    }

    private function validationFailed($name)
    {
        $validator = Validator::make(['name' => $name], ['name' => 'required|unique:zclients']);

        return $validator->fails() ? $validator->errors()->messages() : [];
    }
}
