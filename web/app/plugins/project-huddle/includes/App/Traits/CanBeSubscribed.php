<?php

namespace PH\Traits;

use PH\Models\Thread;
use PH\Models\Project;

trait CanBeSubscribed
{
    private function getIdForSubscription($id = 0)
    {
        $post = get_post($id);
        return $post ? $post->ID : new \WP_Error("missing_id", "You must provide a valid post id.");
    }

    public function addToProject(Project $project)
    {
        if (!$project->ID) {
            return new \WP_Error("missing_id", "You must provide a project with an id.");
        }
        return ph_add_member_to_project(['user_id' => $this->ID, 'project_id' => $project->ID]);
    }

    public function removeFromProject(Project $project)
    {
        if (!$project->ID) {
            return new \WP_Error("missing_id", "You must provide a project with an id.");
        }
        return ph_remove_project_member(['user_id' => $this->ID, 'project_id' => $project->ID]);
    }


    public function isSubscribedToProject(Project $project)
    {
        $project_ids = ph_get_users_project_ids($this->ID);
        return in_Array($project->ID, $project_ids);
    }

    public function addToThread(Thread $thread)
    {
        if (!$thread->ID) {
            throw new \Error("You must provide a thread with an id.", 'missing_id');
        }
        ph_add_member_to_thread(['user_id' => $this->ID, 'post_id' => $thread->ID]);
    }

    public function removeFromThread(Thread $thread)
    {
        if (!$thread->ID) {
            return new \WP_Error("missing_id", "You must provide a thread with an id.");
        }
        return ph_remove_thread_member(['user_id' => $this->ID, 'post_id' => $thread->ID]);
    }

    public function isSubscribedToThread(Thread $thread)
    {
        $thread_ids = ph_get_users_thread_ids($this->ID);
        return in_Array($thread->ID, $thread_ids);
    }
}
