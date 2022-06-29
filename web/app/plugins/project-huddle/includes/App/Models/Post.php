<?php

namespace PH\Models;

use PH\Models\Model;
use PH\Traits\HasParents;
use PH\Traits\IsPostType;
use PH\Traits\HasPostType;
use PH\Traits\HasAccessToken;

if (!defined('ABSPATH')) {
    exit;
}

/**
         * Extending WP_Post
 * Based on https://resoundingechoes.net/development/extending-the-wp-post-class/
 */
class Post extends Model
{
    use HasPostType, HasParents, HasAccessToken, IsPostType;

    /**
     * The post object that we're 'extending'
     *
     * @link	https://codex.wordpress.org/Class_Reference/WP_Post
     *
     * @param 	WP_Post
     * @since 	1.0.0
     */
    var $post;

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
     * @see		$this->get_meta()
     *
     * @param 	array
     * @since 	1.0.0
     */
    var $fields;

    /**
     * Class methods
     *
     * - __construct()
     * - get_meta()
     * - get()
     * - get_by()
     */

    /**
     * Construct a new instance
     *
     * @param 	(int|string|array|WP_Post) 	$args	The ID or WP_Post we are extending, or an array with
     *												data to get the post
     *
     * @param 	bool	$autoload_post				Whether to automatically get the WP_Post if $args is not a WP_Post
     * @param 	bool 	$autoload_post_meta			Whether to automatically load custom fields for the post
     *
     * @since	1.0.0
     */
    function __construct($args, $autoload_post = true, $autoload_post_meta = true)
    {
        # if we're passed a WP_Post object
        if (is_a($args, 'WP_Post')) {
            $this->post = $args;
            $this->ID = $this->post->ID;
        } # end if: $args is an WP_Post
        /**
         * If we're given an array, load the array values into $this->ID, $this->post_type, $this->fields as needed
         * Get the WP_Post using $args['ID'] given and $autoload_post is true
         */
        elseif (is_array($args)) {

            $this->fields = array();

            foreach ($args as $k => $v) {

                # if we have in ID in $args
                if ('ID' == $k) {

                    if (!is_string($v) && !is_int($v)) continue;
                    $this->ID = intval($v);

                    # load the post if applicable
                    if ($autoload_post && $this->ID) $this->post = get_post($this->ID);
                } elseif ('post_type' == $k) {

                    $this->post_type = $v;
                }

                # treat everything else as a custom field
                else {
                    $this->fields[$k] = $v;
                }
            } # end foreach: $args

        } # end elseif: $args is an array

        # if we're given an int or a string
        elseif (is_int($args) || is_string($args)) {

            $this->ID = intval($args);
            if ($autoload_post) {
                $this->post = get_post($this->ID);
            }
        }

        # if we don't have an ID at this point, do nothing further
        if (!$this->ID) return $this;

        # load the custom fields for the post
        # Adds to any fields that may already have been loaded via $args
        if ($autoload_post_meta) {
            $this->get_meta(true);
        }

        # if we don't have a post at this point, do nothing further
        if (!$this->post)    return;

        # load the post type
        $this->post_type = $this->post->post_type;

        if (method_exists($this, 'hooks')) {
            $this->hooks();
        }
    } # end: construct()

    public static function get($id = 0)
    {
        return new static($id);
    }

    /**
     * Set/get the post meta for this object
     *
     * The $force parameter is in place to prevent hitting the database each time the method is called
     * when we already have what we need in $this->fields
     *
     * @link 	https://developer.wordpress.org/reference/functions/get_meta
     *
     * @param 	$force 		Whether to force load the post meta (helpful if $this->fields is already an array).
     *
     * @return 	array
     * @since 	1.0.0
     */
    public function get_meta($force = false)
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
        $fields = get_post_meta($this->ID);

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
    } # end: get_meta()

    /**
     * Create item
     *
     * @param [type] $args
     * @return Post
     */
    public function create($args)
    {
        // defaults
        $args = wp_parse_args($args, [
            'post_type' => $this->post_type,
            'post_author' => get_current_user_id(),
            'post_status' => 'publish',
            'comment_status' => 'open'
        ]);

        // sanitize
        $args = $this->prepareArgsForDatabase($args);
        if (is_wp_error($args)) {
            return $args;
        }

        // insert
        $post_id = wp_insert_post($args);

        // return a new instance of itself
        return new static($post_id);
    }
}
