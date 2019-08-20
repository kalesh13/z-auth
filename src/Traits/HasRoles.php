<?php

namespace Zauth\Traits;

use Zauth\Zrole;

trait HasRoles
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
     * Return the name of the role for a score.
     * 
     * @param int
     * @return string
     */
    public function getRoleName($score)
    {
        $flipped_array = array_flip($this->role_scores);

        return $flipped_array[$score] ?? 'n/a';
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
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        if ($role && in_array($role, array_keys($this->role_scores))) {
            // minimum score required for the $user_role to
            // return true.
            $req_role_score = $this->role_scores[$role];
            /**
             * Zrole of the user.
             * @var Zrole $user_role 
             */
            $user_role = $this->role;
            /**
             * User has a role if the score of the $user_role is greater
             * than or equal to the $req_role_score
             */
            return $user_role && ($user_role->getRole() >= $req_role_score);
        }
        return false;
    }
}
