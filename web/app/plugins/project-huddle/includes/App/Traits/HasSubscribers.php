<?php

namespace PH\Traits;

use PH\Models\User;
use PH\Models\Thread;

/**
 * Has email subscriptions
 */
trait HasSubscribers
{
    /**
     * Get subscribed user ids
     *
     * @return Array
     */
    public function subscribedIds()
    {
        $ids = [];
        switch ($this->getSimpleType()) {
            case 'thread':
                // get members
                $ids = ph_get_post_member_ids($this->ID, 'ph_thread_members', 'post_id');
                // add assigned people
                $thread = new Thread($this->ID);
                $ids = array_merge($ids, [$thread->getAssignedUserId()]);
                break;
            case 'project':
                $ids = ph_get_post_member_ids($this->ID, 'ph_members', 'project_id');
                break;
        }
        return (array) apply_filters("ph_{$this->getSimpleType()}_subscribed_user_ids", $ids);
    }

    /**
     * Get subscribed users
     *
     * @return Array
     */
    public function subscribedUsers()
    {
        $users = [];
        if (!empty($this->subscribedIds())) {
            foreach ($this->subscribedIds() as $id) {
                $user = new User($id);
                if ($user->ID) {
                    $users[] = new User($id);
                }
            }
        }

        return apply_filters("ph_{$this->getSimpleType()}_subscribed_users", $users);
    }

    public function addMember($user_id)
    {
        if (is_a($user_id, 'WP_User')) {
            $user_id = $user_id->ID;
        }

        $id_column = $this->getSimpleType() === 'project' ? 'project_id' : 'thread_id';
        $members_column = $this->getSimpleType() === 'project' ? 'ph_members' : 'ph_thread_members';

        return ph_add_member_to_post(
            [
                'user_id' => isset($user_id) ? $user_id : 0,
                'post_id' => $this->ID,
            ],
            $members_column,
            $id_column
        );
    }

    public function removeMember($user_id)
    {
        if (is_a($user_id, 'WP_User')) {
            $user_id = $user_id->ID;
        }

        $id_column = $this->getSimpleType() === 'project' ? 'project_id' : 'thread_id';
        $members_column = $this->getSimpleType() === 'project' ? 'ph_members' : 'ph_thread_members';

        return ph_remove_post_member(
            [
                'user_id' => isset($user_id) ? $user_id : 0,
                'post_id' => $this->ID,
            ],
            $members_column,
            $id_column
        );
    }
}
