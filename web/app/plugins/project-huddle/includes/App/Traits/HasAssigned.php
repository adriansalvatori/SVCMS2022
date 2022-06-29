<?php

namespace PH\Traits;

use PH\Models\User;

trait HasAssigned
{
    /**
     * Get assigned user ID
     *
     * @return integer
     */
    public function getAssignedUserId()
    {
        return (int) get_post_meta($this->ID, 'assigned', true);
    }

    /**
     * Get assigned user
     *
     * @return \PH\Models\User
     */
    public function getAssignedUser()
    {
        return new User($this->getAssignedUserID());
    }
}
