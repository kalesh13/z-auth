<?php

namespace Zauth\Traits;

use Exception;
use Zauth\Zrole;

trait UserHasZrole
{
    /**
     * Holds the role score for each role. Administrator is
     * given the highest score of 100, so that 99 lower roles
     * can be defined depending on the application needs.
     * 
     * @var array
     */
    public $role_scores = [
        'Customer' => 0,
        'Administrator' => 100,
    ];

    /**
     * Return the role of the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function role()
    {
        return $this->hasOne(Zrole::class);
    }

    /**
     * Assign a role to this user
     * 
     * @param string $role_name
     * @return bool
     */
    public function assignRole($role_name)
    {
        if (empty($this->role_scores)) {
            throw new Exception('No roles defined. Define some roles before assigning one to user.');
        }

        $role = $this->role;

        if (!$role) {
            $role = new Zrole();
        }
        // If the specified role_name is defined, use the role_name
        // and its corresponding score.
        if (isset($this->role_scores[$role_name])) {
            $role->role = $role_name;
            $role->score = $this->role_scores[$role_name];
        }
        // If the specified role_name is not found in the
        // $role_scores array, we will find the smallest score
        // and use that key and value for the new zrole.
        else {
            $min_key = array_keys($this->role_scores, min($this->role_scores))[0];

            $role->role = $min_key;
            $role->score = $this->role_scores[$min_key];
        }

        return $this->role()->save($role);
    }

    /**
     * Replaces the current role scores with new one.
     * 
     * @param array $role_scores
     * @return $this
     */
    public function setRole(array $role_scores)
    {
        $this->role_scores = $role_scores;

        return $this;
    }

    /**
     * Adds new set of roles for the user.
     * 
     * @param array $role_scores
     * @return $this
     */
    public function addRoles(array $role_scores)
    {
        $this->role_scores = array_merge($this->role_scores, $role_scores);

        return $this;
    }

    /**
     * Checks whether the user is an administrator or not.
     * 
     * @return bool
     */
    public function isAdministrator()
    {
        return $this->hasRole('Administrator');
    }

    /**
     * Check whether the user has a role
     * 
     * @param string $role_name
     * @return bool
     */
    public function hasRole($role_name)
    {
        if ($role_name && in_array($role_name, array_keys($this->role_scores))) {
            // minimum score required for the $user_role to
            // return true.
            $req_role_score = $this->role_scores[$role_name];
            /**
             * Zrole of the user.
             * @var Zrole $user_role 
             */
            $user_role = $this->role;
            /**
             * User has a role if the score of the $user_role is greater
             * than or equal to the $req_role_score
             */
            return $user_role && ($user_role->score() >= $req_role_score);
        }
        return false;
    }
}
