<?php

use PH\Models\Thread;
use PH\Contracts\Model;
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

abstract class PH_Thread extends PH_Rest_Object
{
    
    public function __construct()
    {
        parent::__construct();

        // add schema.
        $this->schema = $this->schema();
        $slack_terms = (bool)get_option( 'ph_slack_terms' );
        // add fields.
        $this->register_fields_from_schema();

        // register fields.
        add_action('rest_api_init', array($this, 'rest_fields_from_schema'));

        // auto add comment data.
        add_action('rest_api_init', array($this, 'add_comment_data'));

        // store approval comment when approval is set.
        add_action(
            "ph_{$this->endpoint_type}_rest_update_{$this->action_slug}_attribute",
            array(
                $this,
                'store_resolved_comment',
            ),
            10,
            3
        );

        // Status comment when status is changed.
        add_action(
            "ph_{$this->endpoint_type}_rest_update_{$this->action_slug}_attribute",
            array(
                $this,
                'store_status_comment',
            ),
            10,
            3
        );
        
        // store approval comment when approval is set.
        add_action(
            "ph_{$this->endpoint_type}_rest_update_{$this->action_slug}_attribute",
            array(
                $this,
                'store_assign_comment',
            ),
            10,
            3
        );
        
        if( $slack_terms ) {
            if( !empty( get_option( 'ph_slack_thread_resolves' ) ) && 'on' == get_option( 'ph_slack_thread_resolves' ) ) {
                add_action(
                    "ph_{$this->endpoint_type}_rest_update_{$this->action_slug}_attribute",
                    array(
                        $this,
                        'push_incoming_webhook',
                    ),
                    10,
                    4
                );
            }
        }
        
        // dynamic comment text for approval comments to make sure user display name and translations are up to date.
        add_filter('comment_text', array($this, 'resolve_comment_text'), 8, 2);
        add_filter('comment_text', array($this, 'comment_status_text'), 8, 2);
        add_filter('comment_text', array($this, 'assign_comment_text'), 8, 2);

        // project members
        add_filter('rest_api_init', array($this, 'members'), 8, 2);

        // force comments open for this post type.
        add_filter('comments_open', array($this, 'force_comments_open'), 10, 2);

        // sort threads by menu order
        add_filter("ph_{$this->collection_name}_collection_data", array($this, 'sort_by_menu_order'));

        // let user edit their own comments, no matter the role
        add_filter('user_has_cap', [$this, 'caps'], 10, 4);
        add_filter('map_meta_cap', [$this, 'read_caps'], 10, 4);

        // dynamic thread title.
        add_filter('the_content', array($this, 'dynamic_thread_content'));

        // comments count field.
        add_filter('rest_api_init', array($this, 'comments_count'));
        add_filter('rest_api_init', array($this, 'activity_count'));

        // add filters.
        $this->add_filters();

        // standardize actions.
        $this->add_actions();

        // project ID attribute
        add_action('rest_api_init', array($this, 'project_id'));
        add_action('rest_api_init', array($this, 'project_name'));
        add_action('rest_api_init', array($this, 'item_name'));

        // maybe trash and untrash
        add_action('wp_trash_post', array($this, 'maybe_trash'));
        add_action('untrash_post', array($this, 'maybe_untrash'));

        // clear transients when post meta is updated
        add_action("added_post_meta", array($this, 'clear_transients_meta_update'), 10, 4);
        add_action("updated_post_meta", array($this, 'clear_transients_meta_update'), 10, 4);

        add_action('transition_post_status', array($this, 'clear_parent_transients_on_status_change'), 10, 3);
    }

    /**
     * Send slack notification when thread is resolved/unresolved on Mockup/Website
     *
     * @since 4.3.0
     *
     * @param  $attr Attribute
     * @param  $value Value
     * @param  $post Post
     *
     */
    function push_incoming_webhook( $attr, $value, $post, $is_batch ) {   

        // if not our meta info or post type, bail
        // To make sure that it is only for the resolved threads.
        if ($attr !== 'resolved' || get_post_type($post->ID) !== $this->post_type) {
            return;
        }
        // get the type of the Project (mockup/website)
        $type = ( $post->post_type == 'phw_comment_loc' ) ? __('website', 'project-huddle') : __('mockup', 'project-hudde');
        
       

        $comment_text = '';
        $link = '';
        $result = '';
        // Get $comment from the database
        $comment = ph_get_comments(
            array(
                'post_id'  => $post->ID,
                'order_by' => 'comment_date',
                'order'    => 'asc',
                'type__in' => array(
                    'ph_comment',
                ),
                'number'   => 1,
            )
        );

        $thread = new Thread( $comment[0]->comment_post_ID );
        
        // Get exact thread link
        $get_comment_link = ph_email_link($thread->getAccessLink(), __('View Comment', 'project-huddle'));
        // regex
        preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $get_comment_link, $result);

        $project_name = ph_get_the_title($thread->parentsIds()['project']);

        if (!empty($result)) {
            # Found a link.
            $link =  $result['href'][0]; //return exact comment thread link
        }

        $user = wp_get_current_user();

        // Client name
        $author = $user->display_name;

        if ($user && $user->first_name) {
            $author = $user->first_name;
        } 

        $comment_postid = get_comment_meta( $comment[0]->comment_ID, 'project_id', true );

        $specific_webhook_url = get_post_meta( $comment_postid, 'webhook', true);

        $webhook_url = get_option( 'ph_slack_webhook_default' );
        // Apply webhook URL.
        if( isset( $specific_webhook_url ) && '' !== $specific_webhook_url ) {
            $webhook_url = $specific_webhook_url;
        } 
      
        // Resolved/unresolved status
        $approval = get_post_meta( $comment[0]->comment_post_ID, 'resolved', true ) ? __('resolved', 'project-huddle') : __( 'unresolved', 'project-huddle' );
        
        $comment_text_ = get_option( 'ph_project_resolve_text' );
        
        // Shortcde logic start from here
        $find = array( '/\{ph_commenter_name\}/','/\{ph_project_type\}/', '/\{ph_action_status\}/');

        $replacement = array( $author, $type, $approval );
        $client_url = get_avatar_url($comment[0]->comment_author_email, 32);
        $comment_text = preg_replace( $find, $replacement, $comment_text_ );
       
        $approval = get_post_meta( $comment[0]->comment_post_ID, 'resolved', true) ? 1 : 0;
        
        $email = $comment[0]->comment_author_email;
        
        $color = $approval ? "#36a64f" : "#e44d50";
        $resolve_emoji = $approval ? " :thumbsup:" : " :thumbsdown:";
        // Resolved payload
        $body = array(
			
            "attachments" => array(
                array(
                    "color"=> $color,
                    "author_name"=> $author,
                    "author_link"=> $client_url,
                    "author_icon"=> $client_url,
                    "text" => $comment_text . $resolve_emoji,
                    "fields"=> array(
                        array(
                            "title"=> sprintf( __( "%s Name", 'project-huddle' ), ucwords($type)),
                            "value"=> $project_name,
                            "short"=> false
                        ),
                        array( 
                            "value"=> "*<$link|View Conversation>*",
                            "short"=> true
                        ),
                    ),
                )
            )
        );
        
        $result = wp_remote_post( $webhook_url, array(
            'headers' => array(
                'content-type' => 'application/json',
            ),
            'body' => wp_json_encode( $body ),
        ) );

        return $result;

    }

   
    /**
     * Clear saved transients on status change
     *
     * @param string $new
     * @param string $old
     * @param WP_Post $post
     * @return void
     */
    public function clear_parent_transients_on_status_change($new, $old, $post)
    {
        if ($post->post_type !== $this->post_type) {
            return;
        }
        $parent_id = get_post_meta($post->ID, 'parent_id', true);
        if ($parent_id) {
            delete_transient("ph_resolved_status_" . $parent_id);
        }
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
        delete_transient("ph_{$meta_key}_status_" . $parents['item']);
        delete_transient("ph_{$meta_key}_status_" . $parents['project']);
    }

    /**
     * Add comment data as rest field
     */
    public function add_comment_data()
    {
        // comments
        register_rest_field(
            $this->post_type,
            'comments',
            array(
                'update_callback' => function ($comments, $post, $attr, $request, $object_type) {
                    if (empty($comments)) {
                        return new WP_Error('rest_missing_comment', __('You must provide at least one comment to create a thread.', 'project-huddle'), array('status' => '400'));
                    }

                    // save comments to post
                    foreach ($comments as $comment) {
                        $comment['post'] = $post->ID; // set the post id

                        // new item
                        $comments_request = new WP_REST_Request('POST', '/' . PH()->comment->namespace . '/' . PH()->comment->rest_base);

                        // set new comment params
                        $comments_request->set_body_params(apply_filters("ph_new_{$this->endpoint_type}_thread_comment", $comment, $post, $comments_request));

                        // do request
                        $created = rest_do_request($comments_request);
                    }

                    if (is_wp_error($created)) {
                        return $created;
                    }

                    return $comments;
                },
                'get_callback'    => function ($post, $attr, $request, $object_type) {
                    // should ignore new posts
                    if ($request->get_method() != 'POST' && $request->get_method() != 'PUT') {
                        $expanded = (array) $request['_expand'];

                        if (empty($expanded)) {
                            return array();
                        }

                        if (!(array_key_exists('comments', $expanded) || array_key_exists('all', $expanded))) {
                            return array();
                        }
                    }

                    $url = '/' . PH()->comment->namespace . '/' . PH()->comment->rest_base;

                    $comments_request = new WP_REST_Request('GET', $url);

                    $comments_request->set_query_params(
                        array(
                            'post'       => array($post['id']),
                            'per_page'   => apply_filters('ph_comments_per_page', 10),
                            'order'      => 'desc',
                            '_signature' => $request['_signature'],
                        )
                    );

                    $response = rest_do_request($comments_request);

                    return $response->get_data();
                },
                'schema'          => array(
                    'description' => esc_html__('Array of comment objects in thread.', 'project-huddle'),
                    'type'        => 'array',
                    'items'       => array(
                        'description' => esc_html__('Comment object.', 'project-huddle'),
                        'type'        => 'object',
                    ),
                ),
            )
        );
    }

    /**
     * Add/remove thread members
     *
     * @return void
     */
    function members()
    {
        register_rest_field(
            $this->post_type,
            'members',
            array(
                'update_callback' => function ($members = array(), $post = '', $attr = '', $request = '', $object_type = '') {
                    return ph_update_thread_members($post->ID, $members);
                },
                'get_callback'    => function ($post, $attr, $request, $object_type) {
                    return ph_get_thread_member_ids($post['id']);
                },
                'schema'          => array(
                    'description' => esc_html__('Array of user ids who are subscribed to the thread.', 'project-huddle'),
                    'type'        => 'array',
                    'default'     => array(),
                    'items'       => array(
                        'description' => esc_html__('User ID.', 'project-huddle'),
                        'type'        => 'integer',
                    ),
                ),
            )
        );
    }

     /**
     * Store a comment when a thread is resolved
     *
     * @param string  $attr  Updated attribute
     * @param mixed   $value Attribute value
     * @param WP_Post $post  Post object
     */
    public function store_resolved_comment($attr, $value, $post)
    {
        // if not our meta info or post type, bail
        if ($attr !== 'resolved' || get_post_type($post->ID) !== $this->post_type) {
            return;
        }

        // don't add if there are no comments yet
        if (!ph_count_comments($post->ID)) {
            return;
        }

        // get current user
        $user = wp_get_current_user();

        // get parents
        $parents = ph_get_parents_ids($post->ID);

        // Insert new comment and get the comment ID
        // return wp_insert_comment(
        //     array(
        //         'comment_post_ID'      => $post->ID,
        //         'comment_author'       => $user->display_name,
        //         'comment_author_email' => $user->user_email,
        //         'user_id'              => $user->ID,
        //         'comment_content'      => $value ? __('Approved', 'project-huddle') : __('Unapproved', 'project-huddle'),
        //         'comment_type'         => 'ph_approval',
        //         'comment_approved'     => 1, // force approval
        //         'comment_meta'         => array(
        //             'project_id' => $parents['project'],
        //             'approval' => (bool) $value,
        //         ),
        //     )
        // );
    }

    /**
     * Store a comment when a thread status is changed
     *
     * @param string  $attr  Updated attribute
     * @param mixed   $value Attribute value
     * @param WP_Post $post  Post object
     */
    public function store_status_comment($attr, $value, $post)
    {
        // if not our meta info or post type, bail
        if ($attr !== 'is_status' || get_post_type($post->ID) !== $this->post_type) {
            return;
        }

        // don't add if there are no comments yet
        if (!ph_count_comments($post->ID)) {
            return;
        }

        // get current user
        $user = wp_get_current_user();

        // get parents
        $parents = ph_get_parents_ids($post->ID);

        $comment_status = '';

        $get_active_name = get_option('ph_active_status_enable');
        $get_progress_name = get_option('ph_progress_status_enable');
        $get_review_name = get_option('ph_review_status_enable');
        $get_resolve_name = get_option('ph_resolve_status_enable');
        $is_resolved = false;

        switch($value) {
            case 'active':
                $comment_status = empty(get_option('ph_active_status_name')) ? esc_attr('Active', 'project-huddle') : get_option('ph_active_status_name', 'Active');
                break;
            case 'in_progress':
                $comment_status = $get_progress_name !== 'on' ? get_option('ph_progress_status_name', 'In Progress') : esc_attr('In Progress', 'project-huddle');
                break;
            case 'in_review':
                $comment_status = $get_review_name !== 'on' ? get_option('ph_review_status_name', 'In Review') : esc_attr('In Review', 'project-huddle');
                break;
            case "resolved":
                $comment_status = empty(get_option('ph_resolve_status_name')) ? esc_attr('Resolved', 'project-huddle') : get_option('ph_resolve_status_name', 'Resolved');
                $is_resolved = true;
                break;
            default:
                $comment_status = '';
                break;
        }

        // Insert new comment and get the comment ID
        return wp_insert_comment(
            array(
                'comment_post_ID'      => $post->ID,
                'comment_author'       => $user->display_name,
                'comment_author_email' => $user->user_email,
                'user_id'              => $user->ID,
                'comment_content'      => $comment_status,
                'comment_type'         => 'ph_status',
                'comment_approved'     => 1, // force approval
                'comment_meta'         => array(
                    'project_id' => $parents['project'],
                    'approval' => $is_resolved,
                ),
            )
        );
    }


    /**
     * Store a comment when a thread is assigned
     *
     * @param string  $attr  Updated attribute
     * @param mixed   $value Attribute value
     * @param WP_Post $post  Post object
     */
    public function store_assign_comment($attr, $value, $post)
    {
        // if not our meta info or post type, bail
        if ($attr !== 'assigned' || get_post_type($post->ID) !== $this->post_type) {
            return;
        }

        // don't add if there are no comments yet
        if (!ph_count_comments($post->ID)) {
            return;
        }

        // get current user
        $user = wp_get_current_user();
        $assignee = get_user_by('ID',  $value);
        $text = $assignee ? sprintf(__('%1s assigned %2s.', 'project-huddle'), $user->display_name, $assignee->display_name) : sprintf(__('%s unassigned this conversation.', 'project-huddle'), $user->display_name);

        // get parents
        $parents = ph_get_parents_ids($post->ID);

        // Insert new comment and get the comment ID
        wp_insert_comment(
            array(
                'comment_post_ID'      => $post->ID,
                'comment_author'       => $user->display_name,
                'comment_author_email' => $user->user_email,
                'user_id'              => $user->ID,
                'comment_content'      => $text,
                'comment_type'         => 'ph_assign',
                'comment_approved'     => 1, // force approval
                'comment_meta'         => array(
                    'project_id' => (int) $parents['project'],
                    'assigned' => (int) $value,
                ),
            )
        );
    }

    /**
     * Make approval comment text dynamic
     *
     * @param string $comment_text
     * @param null   $comment
     *
     * @return string Comment text
     */
    function resolve_comment_text( $comment_text = '', $comment = null )
    {
        if ( isset( $comment ) && $comment->comment_type !== 'ph_approval' || get_post_type($comment->comment_post_ID) !== $this->post_type) {
            return $comment_text;
        }

        // get dynamic author and approval meta
        $user = get_userdata($comment->user_id);
        if ($user && $user->display_name) {
            $author = $user->display_name;
        } else {
            $author = $comment->comment_author;
        }

        if (is_user_logged_in() && $user === get_current_user_id()) {
            $author = __('You', 'project-huddle');
        }

        $approval = get_comment_meta($comment->comment_ID, 'approval', true) ? __('resolved', 'project-huddle') : __('unresolved', 'project-huddle');

        // create comment text
        $comment_text = apply_filters('ph_resolve_comment_text', sprintf(__('%1$s %2$s this conversation.', 'project-huddle'), esc_html($author), esc_html($approval)), $comment);
        return $comment_text;
    }


    /**
     * Make approval comment text dynamic
     *
     * @param string $comment_text
     * @param null   $comment
     *
     * @return string Comment text
     */
    function comment_status_text( $comment_text = '', $comment = null )
    {
        if ( isset( $comment ) && $comment->comment_type !== 'ph_status' || get_post_type($comment->comment_post_ID) !== $this->post_type) {
            return $comment_text;
        }

        // get dynamic author and approval meta
        $user = get_userdata($comment->user_id);
        if ($user && $user->display_name) {
            $author = $user->display_name;
        } else {
            $author = $comment->comment_author;
        }

        if (is_user_logged_in() && $user === get_current_user_id()) {
            $author = __('You', 'project-huddle');
        }

        $get_status = $comment->comment_content;

        // create comment text
        $comment_text = apply_filters('ph_comment_status_text', sprintf(__('%1$s changed conversation status to <strong>%2$s</strong>.', 'project-huddle'), esc_html($author), esc_html($get_status)), $comment);
        return $comment_text;
    }

    /**
     * Make approval comment text dynamic
     *
     * @param string $comment_text
     * @param null   $comment
     *
     * @return string Comment text
     */
    function assign_comment_text($comment_text = '', $comment = null)
    {
        if (isset( $comment ) && $comment->comment_type !== 'ph_assign' || get_post_type($comment->comment_post_ID) !== $this->post_type) {
            return $comment_text;
        }

        // get dynamic author and approval meta
        $user = get_userdata($comment->user_id);
        if ($user && $user->display_name) {
            $author = $user->display_name;
        } else {
            $author = $comment->comment_author;
        }

        $assignee_id = (int) get_comment_meta($comment->comment_ID, 'assigned', true);
        $assignee = get_user_by('ID', $assignee_id);
        $assignee = $assignee ? $assignee->display_name : false;

        if ($assignee) {
            if ($assignee_id === get_current_user_id()) {
                if ($user->ID === get_current_user_id()) {
                    $assigned_text = __('You assigned yourself.', 'project-huddle');
                } else {
                    $assigned_text = sprintf(__('%s assigned you.', 'project-huddle'), esc_html($author));
                }
            } else if ($user->ID === get_current_user_id()) {
                $assigned_text = sprintf(__('You assigned %s.', 'project-huddle'), esc_html($assignee));
            } else {
                $assigned_text = sprintf(__('%1$s assigned %2$s.', 'project-huddle'), esc_html($author), $assignee);
            }
        } else {
            if ($user->ID === get_current_user_id()) {
                $assigned_text = __('You unassigned this conversation.', 'project-huddle');
            } else {
                $assigned_text = sprintf(__('%1$s unassigned this conversation.', 'project-huddle'), esc_html($author));
            }
        }

        // create comment text
        $comment_text = apply_filters('ph_assign_comment_text', $assigned_text, $comment);

        return $comment_text;
    }

    /**
     * Make sure comments are always open on our parent post
     * type before comment is saved
     *     *
     * @return array
     */
    public function force_comments_open($open, $post_id)
    {
        if (get_post_type($post_id) === $this->post_type) {
            if (!$open) {
                $open = true;
            }
        }

        return $open;
    }

    /**
     * Sort images by menu order to keep order correct.
     * Uses date as a fallback
     *
     * @param array $args Arguments for get request.
     *
     * @return array
     */
    public function sort_by_menu_order($args)
    {
        $args['order_by'] = 'menu_order date';
        return $args;
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

        $thread = new Thread($args[0]);
        if (!user_can($user_id, $cap, $thread->projectId())) {
            $caps[] = 'do_not_allow';
        }
        return $caps;
    }

    /**
     * Filter on the current_user_can() function.
     * This function is used to explicitly allow users to edit their own comments
     * Regardless of their capabilities or roles.
     *
     * @param array  $allcaps All the capabilities of the user
     * @param array  $cap     [0] Required capability
     * @param array  $args    [0] Requested capability
     *                        [1] User ID
     *                        [2] Associated object ID
     * @param object $user    User object
     *
     * @return array
     */
    public function caps($allcaps, $cap, $args, $user)
    {
        // Bail out if we're not asking about a post:
        if ('edit_post' != $args[0]) {
            return $allcaps;
        }

        // Load the post data:
        $post = get_post($args[2]);

        if (!$post) {
            return $allcaps;
        }

        // bail if not our comment type
        if ($this->post_type !== $post->post_type) {
            return $allcaps;
        }

        // Bail out if the user is the post author:
        if ((int) $user->ID !== (int) $post->post_author) {
            return $allcaps;
        }

        // add required caps
        $allcaps['edit_comment']         = true;
        $allcaps['edit_published_posts'] = true;
        $allcaps[$cap[0]]              = true;

        return $allcaps;
    }

    function activity_count()
    {
        register_rest_field(
            $this->post_type,
            'activity_count',
            array(
                'update_callback' => null,
                'get_callback'    => function ($post, $attr, $request, $object_type) {
                    return (int) ph_count_comments($post['id'], 'all');
                },
                'schema'          => array(
                    'description' => esc_html__('The number of activity comments on a thread.', 'project-huddle'),
                    'type'        => 'integer',
                    'readonly'    => true,
                ),
            )
        );
    }


    function comments_count()
    {
        register_rest_field(
            $this->post_type,
            'comments_count',
            array(
                'update_callback' => null,
                'get_callback'    => function ($post, $attr, $request, $object_type) {
                    return (int) ph_count_comments($post['id']);
                },
                'schema'          => array(
                    'description' => esc_html__('The number of comments on a thread', 'project-huddle'),
                    'type'        => 'integer',
                    'readonly'    => true,
                ),
            )
        );
    }

    function dynamic_thread_content($content)
    {
        global $post;
        
        if (!$post || !is_a($post, 'WP_Post')) {
            return $content;
        }

        if ($post->post_type !== $this->post_type) {
            return $content;
        }

        $comments = ph_get_comments(
            array(
                'post_id'  => $post->ID,
                'order_by' => 'comment_date',
                'order'    => 'asc',
                'type__in' => array(
                    'ph_website_comment',
                    'ph_comment',
                ),
                'number'   => 1,
            )
        );

        if (!empty($comments)) {
            return $comments[0]->comment_content;
        } else {
            $comments = ph_get_comments(
                array(
                    'post_id'  => $post->ID,
                    'order_by' => 'comment_date',
                    'order'    => 'desc',
                    'type__in' => array(
                        'ph_approval',
                    ),
                    'number'   => 1,
                )
            );

            if (!empty($comments)) {
                return $comments[0]->comment_content;
            } else {
                return __('No comment text', 'project-huddle');
            }
        }
    }
}
