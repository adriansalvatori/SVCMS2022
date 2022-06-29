<?php

namespace PH\Traits;

use PH\Models\Project;

/**
 * Has email subscriptions
 */
trait HasParents
{
    protected $parentsIds;
    protected $projectId;
    protected $project;

    public function parentId()
    {
        if (isset($this->comment)) {
            return (int) $this->comment->comment_post_ID;
        }
        return get_post_meta($this->ID, 'parent_id', true);
    }

    /**
     * Parents Ids
     *
     * @return void
     */
    public function parentsIds()
    {
        if ($this->parentsIds) {
            return $this->parentsIds;
        }

        if (isset($this->comment)) {
            return $this->parentsIds = ph_get_parents_ids($this->comment, 'comment');
        } else {
            return $this->parentsIds = ph_get_parents_ids($this->ID);
        }
    }

    /**
     * Project ID
     *
     * @return integer
     */
    public function projectId()
    {
        if ($this->projectId) {
            return $this->projectId;
        }
        return $this->projectId = (int) $this->parentsIds()['project'];
    }

    /**
     * Everything has a project
     *
     * @return \PH\Models\Project
     */
    public function project()
    {
        if ($this->project) {
            return $this->project;
        }
        return $this->project = new Project($this->projectId());
    }

    public function projectType()
    {
        $post_type = get_post_type($this->projectId());
        if ('ph-project' === $post_type) {
            return 'mockup';
        }
        if ('ph-website' === $post_type) {
            return 'website';
        }
        return false;
    }
}
