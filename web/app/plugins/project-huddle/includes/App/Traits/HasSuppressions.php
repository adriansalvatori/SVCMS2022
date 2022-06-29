<?php

namespace PH\Traits;

/**
 * Has email suppressions
 */
trait HasSuppressions
{
    /**
     * Types of things that can be suppressed
     *
     * @var array
     */
    protected $types = [
        'activity',
        'daily',
        'weekly',
        'comments',
        'image_approvals',
        'project_approvals',
        'resolves',
        'assigns'
    ];

    /**
     * Check if a user has email suppressions for a specific segment
     * Works simliar to current_user_can, with optional ID to check for specific project requirement
     *
     * @param String $suppression Type of supression (see types list above)
     * @param Integer $id ID of post
     * @return boolean
     */
    public function isSuppressed($type, $id = 0)
    {
        // check project suppression
        if ($id) {
            $parents = ph_get_parents_ids($id);
            if ($this->projectSuppression($parents['project'])) {
                return true;
            }
        }

        if (in_array($type, $this->types)) {
            // check suppression type
            return $this->checkSuppression($type);
        }
    }

    /**
     * Add a suppression for a user
     *
     * @param string $type
     * @return boolean
     */
    public function addSuppression($type)
    {
        if (!in_array($type, $this->types)) {
            throw new \Exception('This email type does not exist.');
        }
        return $this->saveSuppression($type, true);
    }

    /**
     * Remove a suppression for a user
     *
     * @param string $type
     * @return boolean
     */
    public function removeSuppression($type)
    {
        if (!in_array($type, $this->types)) {
            throw new \Exception('This email type does not exist.');
        }
        return $this->saveSuppression($type, false);
    }

    /**
     * Save suppression
     *
     * @param string $type
     * @param boolean $value
     * @return void
     */
    protected function saveSuppression($type, $value)
    {
        $value = !filter_var($value, FILTER_VALIDATE_BOOLEAN);
        return update_user_meta($this->ID, "ph_${type}", $value);
    }

    /**
     * Check to see if something is suppressed
     *
     * @param string $type
     * @return boolean
     */
    protected function checkSuppression($type)
    {
        // if meta hasn't yet been set, default to unsupressed
        if (!metadata_exists('user', $this->ID, "ph_${type}")) {
            return false;
        }
        $value = get_user_meta($this->ID, "ph_${type}", true);
        $value = !filter_var($value, FILTER_VALIDATE_BOOLEAN);
        return (bool) apply_filters("ph_disable_${type}_emails", $value, $this->ID);
    }

    /**
     * Switched this to a filter as of 3.9.0 
     *
     * @return array
     */
    public function getProjectSuppressions()
    {
        return (array) apply_filters('ph_project_email_notifications_disable_all', [], $this->ID);
    }

    /**
     * Has a user suppressed emails for a project?
     *
     * @return boolean
     */
    public function hasProjectSuppression($id = 0)
    {
        return in_array($id, $this->getProjectSuppressions());
    }
}
