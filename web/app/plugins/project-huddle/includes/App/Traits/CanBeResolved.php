<?php

namespace PH\Traits;

/**
 * Can be resolved
 */
trait CanBeResolved
{
    protected $meta_key = 'resolved';

    /**
     * Approve the item
     *
     * @return boolean
     */
    public function resolve()
    {
        return update_post_meta($this->ID, $this->meta_key, true);
    }

    /**
     * Reject the item
     *
     * @return boolean
     */
    public function unresolve()
    {
        return update_post_meta($this->ID, $this->meta_key, false);
    }

    /**
     * Clear the resolve status
     *
     * @return boolean
     */
    public function clearResolveStatus()
    {
        return delete_post_meta($this->ID, $this->meta_key);
    }

    /**
     * Get the approval status from the database
     *
     * @return object
     */
    public function isResolved()
    {
        return (bool) get_post_meta($this->ID, $this->meta_key, true);
    }

    /**
     * Get approval history for post
     *
     * @return array|false
     */
    public function getResolveHistory($args)
    {
        return ph_get_comments(wp_parse_args(
            $args,
            [
                'post_id'  => $this->ID,
                'type__in' => array(
                    'ph_approval',
                )
            ]
        ));
    }
}
