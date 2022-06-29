<?php

use PH\Models\Project;

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

abstract class PH_Project extends PH_Rest_Object
{
    public function __construct()
    {
        parent::__construct();

        // add schema.
        $this->schema = $this->schema();

        // add fields.
        $this->register_fields_from_schema();

        // standardize actions.
        $this->add_actions();
        $this->add_filters();

        // register fields.
        add_action('rest_api_init', array($this, 'rest_fields_from_schema'));

        // add access link login option
        add_action('rest_api_init', array($this, 'access_link_login_field'));

        // add token field.
        add_action('rest_api_init', array($this, 'token_field'));

        // members
        add_action('rest_api_init', array($this, 'members_field'));

        // Comment resolve status
        add_filter('rest_api_init', array($this, 'resolve_status'));

        // Items approval status
        add_filter('rest_api_init', array($this, 'items_status'));

        // project approved field
        add_action('rest_api_init', array($this, 'approved_field'));

        // add the author as the member when a project is created
        add_action("save_post_{$this->post_type}", array($this, 'add_author_as_member'), 10, 3);

        // only return a persons projects they are members of
        add_filter("rest_{$this->post_type}_query", array($this, 'filter_member_projects'), 10, 2);

        // let user edit their own comments, no matter the role
        add_filter('map_meta_cap', [$this, 'read_caps'], 10, 4);

        // add_filter('user_has_cap', [$this, 'read_cap'], 10, 4);
    }

    /**
     * author_cap_filter()
     *
     * Filter on the current_user_can() function.
     * This function is used to explicitly allow users 
     *
     * @param array $allcaps All the capabilities of the user
     * @param array $cap     [0] Required capability
     * @param array $args    [0] Requested capability
     *                       [1] User ID
     *                       [2] Associated object ID
     */
    public function read_cap($allcaps, $cap, $args, $user)
    {
        if ("read_{$this->post_type}" !== $args[0]) {
            return $allcaps;
        }

        // Load the post data:
        $post = get_post($args[2]);

        // bail if it's not one of our post types
        if (!$post) {
            return $allcaps;
        }

        // Bail out if post author can edit posts.
        if ($user->has_cap('edit_' . $post->post_type . 's') || $user->has_cap('edit_posts')) {
            $allcaps[$args[0]] = true;
            return $allcaps;
        }

        // they can read if they are a member
        if (ph_user_is_member($post, $user->ID)) {
            $allcaps[$args[0]] = true;
            return $allcaps;
        }

        // they can read if they have a valid project access token
        $tokens = PH()->session->get('project_access');
        $token = isset($tokens[$post->ID]) ? $tokens[$post->ID] : '';
        $project = new Project($post->ID);
        if ($token && $project->getToken() === $token) {
            $allcaps[$args[0]] = true;
        }

        return $allcaps;
    }

    /**
     * Filter on the current_user_can() function.
     * This function is used to explicitly allow users to edit their own comments
     * Regardless of their capabilities or roles.
     *
     * @param array  $caps All the capabilities of the user
     * @param array  $cap     [0] Required capability
     * @param array  $args    [0] Requested capability
     *                        [1] User ID
     *                        [2] Associated object ID
     * @param object $user    User object
     *
     * @return array
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

        // allow read if you can edit
        if (user_can($user_id, 'edit_ph-projects') || $post->post_author == $user_id) {
            return $caps;
        }

        // if we have a user id and object id, and they are a member,
        // they have read permissions
        if (!ph_is_user_subscribed($user_id, $post)) {
            // check token as a fallback
            $tokens = PH()->session->get('project_access');
            $token = isset($tokens[$post->ID]) ? $tokens[$post->ID] : '';
            $project = new Project($post->ID);
            if (!$token || $project->getToken() !== $token) {
                $caps[] = 'do_not_allow';
            }
        }

        return $caps;
    }

    /**
     * This needs to be a bit more dynamic in case the project url changes
     *
     * @return void
     */
    public function approved_field()
    {
        register_rest_field(
            $this->post_type,
            'approved',
            array(
                'update_callback' => function ($value, $post, $attr, $request, $object_type) {
                    // add permissions filter for granular control.
                    if (!apply_filters("ph_website_update_{$this->action_slug}_{$attr}_allowed", true, $post, $value)) {
                        return new WP_Error('rest_forbidden_context', __('Sorry, you are not allowed to do this.', 'project-huddle'), array('status' => rest_authorization_required_code()));
                    }

                    $site = new Project($post->ID);
                    $site->saveApprovalStatus($value);

                    // run action on update.
                    do_action("ph_website_rest_update_{$this->action_slug}_attribute", $attr, $value, $post);

                    // Schema handles sanitization.
                    return $value;
                },
                'get_callback'    => function ($post, $attr, $request, $object_type) {
                    return (bool) (new Project($post['id']))->isApproved();
                },
                'schema'          => array(
                    'description' => esc_html__('Is this approved?', 'project-huddle'),
                    'type'        => 'boolean',
                ),
            )
        );
    }

    /**
     * Add images count/status to data object
     *
     * @return void
     */
    public function items_status()
    {
        register_rest_field(
            $this->post_type,
            'items_status',
            array(
                'update_callback' => null,
                'get_callback'    => function ($post, $attr, $request, $object_type) {
                    return PH()->{$this->endpoint_type}->get($post['id'])->getItemsApprovalStatus();
                },
                'type'        => 'array',
                'default'     => [
                    'total'    => 0,
                    'approved' => 0,
                ],
                'items'       => array(
                    'description' => esc_html__('Count.', 'project-huddle'),
                    'type'        => 'number',
                ),
            )
        );
    }

    /**
     * Add members field
     *
     * @return void
     */
    public function members_field()
    {
        register_rest_field(
            $this->post_type,
            'project_members',
            array(
                'get_callback'    => function ($post) {
                    return ph_get_project_member_ids((int) $post['id']);
                },
                'update_callback' => function ($value, $post, $attr, $request, $object_type) {
                    return ph_update_project_members($post->ID, (array) $value);
                },
                'schema'          => array(
                    'description' => esc_html__('A list of user IDs that have access to the project.', 'project-huddle'),
                    'type'        => 'array',
                    'default'     => array(),
                    'items'       => array(
                        'description' => esc_html__('User id.', 'project-huddle'),
                        'type'        => 'number',
                    ),
                ),
            )
        );
    }

    /**
     * Access Token Field
     */
    public function token_field()
    {
        register_rest_field(
            $this->post_type,
            'access_token',
            array(
                'get_callback'   => 'ph_get_access_token',
                'project_access' => array(
                    'description' => esc_html__('Access token for the project', 'project-huddle'),
                    'type'        => 'string',
                    'readonly'    => true,
                ),
            )
        );
    }

    /**
     * Access Token Field
     */
    public function access_link_login_field()
    {
        register_rest_field(
            $this->post_type,
            'access_link_login',
            array(
                'update_callback' => function ($value, $post, $attr, $request, $object_type) {
                    return update_post_meta($post->ID, $attr, filter_var($value, FILTER_VALIDATE_BOOLEAN));
                },
                'get_callback'    => null,
                'schema'          => array(
                    'description' => esc_html__('Whether to allow logging in through the access link.', 'project-huddle'),
                    'type'        => 'boolean',
                    'default'     => true
                ),
            )
        );
    }

    /**
     * Add author as member for new projects
     *
     * @param integer $id
     * @param WP_Post $post
     * @param boolean $update
     * @return void
     */
    public function add_author_as_member($id, $post, $update)
    {
        // only if being created
        if ($update) {
            return;
        }

        // double check our post type
        if (get_post_type($post) !== $this->post_type) {
            return;
        }

        if ($post->post_author) {
            ph_add_member_to_project(
                array(
                    'user_id'    => (int) $post->post_author,
                    'project_id' => $id,
                )
            );
        }
    }

    /**
     * Filter project requests to only get projects where the person is a member
     *
     * @param array           $args    Key value array of query var to query value.
     * @param WP_REST_Request $request The request used.
     * @return array Args.
     */
    public function filter_member_projects($args, $request)
    {
        // if it's an edit context, simply return query so permissions are used.
        if ('edit' === $request['context']) {
            return $args;
        }

        // bail if we don't specifically ask for a members projects
        if (!isset($request['project_member']) || !$request['project_member']) {
            return $args;
        }

        $project_ids      = (array) ph_get_users_project_ids();
        if (!empty($project_ids)) {
            $args['post__in'] = (array) $project_ids;
        } else {
            $args['post__in'] = array(-1);
        }

        return $args;
    }

    public function resolve_status()
    {
        register_rest_field(
            $this->post_type,
            'resolve_status',
            array(
                'update_callback' => null,
                'get_callback'    => function ($post, $attr, $request, $object_type) {
                    $function = "ph_get_{$this->endpoint_type}_resolve_status";
                    return (array) $function($post['id']);
                },
                'schema'          => array(
                    'description' => esc_html__('Array of comment data for the item.', 'project-huddle'),
                    'type'        => 'array',
                    // 'readonly'    => true,
                    'items'       => array(
                        'description' => esc_html__('Associated number.', 'project-huddle'),
                        'type'        => 'integer',
                    ),
                ),
            )
        );
    }
}
