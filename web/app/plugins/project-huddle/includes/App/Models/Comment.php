<?php

namespace PH\Models;

use PH\Models\Model;
use PH\Traits\HasParents;
use PH\Traits\HasRestRequests;

if (!defined('ABSPATH')) {
    exit;
}

class Comment extends Model
{
    use HasParents, HasRestRequests;

    /**
     * The post object that we're 'extending'
     *
     * @link	https://codex.wordpress.org/Class_Reference/WP_Comment
     *
     * @param 	WP_Comment
     * @since 	1.0.0
     */
    var $comment;

    /**
     * The ID of the post
     *
     * @param 	int
     * @since 	1.0.0
     */
    var $ID = 0;

    /**
     * The post meta, (i.e. custom fields) for the post, if any have been loaded
     *
     * An option exists in __construct() to disable postmeta from being loaded automatically when creating
     * a new object (for better control over DB performance)
     *
     * @link 	https://codex.wordpress.org/Custom_Fields
     * @see		$this->get_post_meta()
     *
     * @param 	array
     * @since 	1.0.0
     */
    var $fields;

    /**
     * Construct a new instance
     *
     * @param 	(int|string|array|WP_Comment) 	$args	The ID or WP_Comment we are extending, or an array with
     *												data to get the comment
     *
     * @param 	bool	$autoload_comment				Whether to automatically get the WP_Comment if $args is not a WP_Comment
     * @param 	bool 	$autoload_comment_meta			Whether to automatically load custom fields for the comment
     *
     * @since	1.0.0
     */
    function __construct($args, $autoload_comment = true, $autoload_comment_meta = true)
    {

        # if we're passed a WP_Comment object
        if (is_a($args, 'WP_Comment')) {
            $this->comment = $args;
            $this->ID = $this->comment->comment_ID;
        } # end if: $args is an WP_Comment
        /**
         * If we're given an array, load the array values into $this->ID, $this->post_type, $this->fields as needed
         * Get the WP_Comment using $args['ID'] given and $autoload_comment is true
         */
        elseif (is_array($args)) {

            $this->fields = array();

            foreach ($args as $k => $v) {

                # if we have in ID in $args
                if ('ID' == $k) {

                    if (!is_string($v) && !is_int($v)) continue;
                    $this->ID = intval($v);

                    # load the comment if applicable
                    if ($autoload_comment && $this->ID) $this->comment = get_comment($this->ID);
                } else {
                    $this->fields[$k] = $v;
                }
            } # end foreach: $args

        } # end elseif: $args is an array

        # if we're given an int or a string
        elseif (is_int($args) || is_string($args)) {

            $this->ID = intval($args);
            if ($autoload_comment) {
                $this->comment = get_comment($this->ID);
            }
        }

        # if we don't have an ID at this point, do nothing further
        if (!$this->ID) return;

        # load the custom fields for the post
        # Adds to any fields that may already have been loaded via $args
        if ($autoload_comment_meta) {
            $this->get_comment_meta(true);
        }

        # if we don't have a post at this point, do nothing further
        if (!$this->comment)    return;

        # load the post type
        $this->comment_type = $this->comment->comment_type;

        if (method_exists($this, 'hooks')) {
            $this->hooks();
        }
    } # end: construct()

    public static function get($id = 0)
    {
        return new self($id);
    }

    public function get_comment_meta($force)
    {
        return $this->get_meta($force);
    }

    /**
     * Set/get the post meta for this object
     *
     * The $force parameter is in place to prevent hitting the database each time the method is called
     * when we already have what we need in $this->fields
     *
     * @link 	https://developer.wordpress.org/reference/functions/get_comment_meta
     *
     * @param 	$force 		Whether to force load the post meta (helpful if $this->fields is already an array).
     *
     * @return 	array
     * @since 	1.0.0
     */
    function get_meta($force = false)
    {

        # make sure we have an ID
        if (!$this->ID) return array();

        # if $this->fields is already an array
        if (is_array($this->fields)) {

            # return the array if we're not forcing the post meta to load
            if (!$force) return $this->fields;
        }

        # if $this->fields isn't an array yet, initialize it as one
        else $this->fields = array();

        # get all post meta for the post
        $fields = get_comment_meta($this->ID);

        # if we found nothing
        if (!$fields) {
            return $this->fields;
        }

        # loop through and clean up singleton arrays
        foreach ($fields as $k => $v) {

            # need to grab the first item if it's a single value
            if (count($v) == 1)
                $this->fields[$k] = maybe_unserialize($v[0]);

            # or store them all if there are multiple
            else $this->fields[$k] = $v;
        }

        return $this->fields;
    } # end: get_comment_meta(
}
