<?php

namespace Zauth\Traits;

use Illuminate\Http\Request;
use Zauth\Laravel\AuthenticatesUsers as BaseAuthenticateUsers;

trait AuthenticatesUsers
{
    use BaseAuthenticateUsers;

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($request->redirectTo ?: $this->redirectPath());
    }

    /**
     * Return the token when the user is authenticated
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        return $user->makeVisible('access_token');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        return redirect('/');
    }
}
