<?php

use PH\Models\Item;

/**
 * Abstract thread class
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2017, Andre Gagnon
 * @since       3.8.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

abstract class PH_Item extends PH_Rest_Object
{
    public function __construct()
    {
        parent::__construct();
        // add schema.
        $this->schema = $this->schema();

        // add fields.
        $this->register_fields_from_schema();

        // register fields.
        add_action('rest_api_init', array($this, 'rest_fields_from_schema'));

        // standardize actions and filters
        $this->add_actions();
        $this->add_filters();

        // maybe trash and untrash
        add_action('wp_trash_post', array($this, 'maybe_trash'));
        add_action('untrash_post', array($this, 'maybe_untrash'));

        // clear parent transients when trashed
        add_action('wp_trash_post', [$this, 'clear_parent_approval_transient']);

        add_action("added_post_meta", array($this, 'clear_transients_meta_update'), 10, 4);
        add_action("updated_post_meta", array($this, 'clear_transients_meta_update'), 10, 4);

        add_filter('map_meta_cap', [$this, 'read_caps'], 10, 4);
    }

    /**
     * Filter on the current_user_can() function.
     * This function is used to explicitly allow users to edit their own comments
     * Regardless of their capabilities or roles.
     *
     * @param string[] $caps    Array of the user's capabilities.
     * @param string   $cap     Capability name.
     * @param int      $user_id The user ID.
     * @param array    $args    Adds the context to the cap. Typically the object ID.
     */
    public function read_caps($caps, $cap, $user_id, $args)
    {
        // Bail out if we're not asking about a post:
        if ('read_post' !== $cap) {
            return $caps;
        }

        // bail if no post
        if (!$post = get_post($args[0])) {
            return $caps;
        }

        // bail if not our comment type
        if ($this->post_type !== $post->post_type) {
            return $caps;
        }

        $thread = new Item($args[0]);
        if (!user_can($user_id, $cap, $thread->projectId())) {
            $caps[] = 'do_not_allow';
        }
        return $caps;
    }

    /**
     * Delete transients on meta update
     *
     * @param integer $meta_id
     * @param integer $object_id
     * @param string $meta_key
     * @param mixed $value
     * @return void
     */
    public function clear_transients_meta_update($meta_id, $object_id, $meta_key, $value)
    {
        if (!in_array($meta_key, ['resolved', 'approved'])) {
            return;
        }
        $parents = ph_get_parents_ids($object_id);
        delete_transient("ph_{$meta_key}_status_" . $parents['project']);
    }

    public function clear_parent_approval_transient($post_id)
    {
        // if it's not this post type, bail
        if (get_post_type($post_id) !== $this->post_type) {
            return;
        }

        $parents_ids = (new Item($post_id))->parentsIds();

        delete_transient("ph_approved_status_{$parents_ids['project']}");
        delete_transient("ph_resolved_status_{$parents_ids['project']}");
    }

    public function schema()
    {
    }
}
