<?php

use PH\Models\User;
use PH\Models\Thread;
use PH\Models\Post;
require_once PH_PLUGIN_DIR . 'includes/slack/ph-slack.php';

/**
 * Comment Data
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       2.6.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Website Comment Class
 *
 * This class handles all comment data
 *
 * @since 2.6.0
 */
class PH_Comment extends PH_REST_Comments_Controller
{
	/**
	 * Model to use for getting model data
	 *
	 * @var string
	 */
	protected $model = '\PH\Models\Comment';
	
	/**
	 * PH_Website_Comment_New constructor.
	 *
	 * @param array $args
	 */
	public function __construct(array $args = array())
	{
		// parent constructor
		parent::__construct($args);

		// set rest base
		$this->rest_base = 'comments';

		// register this comment route on construct
		add_action('rest_api_init', array($this, 'register_routes'));

		// comment actions for all registered ph comments
		add_action('wp_insert_comment', array($this, 'new_comment'), 10, 2);
		add_action('edit_comment', array($this, 'edit_comment'), 10, 2);
		add_action('trashed_comment', array($this, 'trash_comment'), 10, 2);

		// set item id if it exists in schema
		add_action('ph_website_publish_comment', array($this, 'set_parent_ids'), 10, 2);
		add_action('ph_website_publish_approval', array($this, 'set_parent_ids'), 10, 2);
		add_action('ph_mockup_publish_comment', array($this, 'set_parent_ids'), 10, 2);
		add_action('ph_mockup_publish_approval', array($this, 'set_parent_ids'), 10, 2);

		// if a comment or action is performed, add as a thread member
		add_action('ph_website_publish_comment', array($this, 'add_thread_member'), 10, 2);
		add_action('ph_website_publish_approval', array($this, 'add_thread_member'), 10, 2);
		add_action('ph_mockup_publish_comment', array($this, 'add_thread_member'), 10, 2);
		add_action('ph_mockup_publish_approval', array($this, 'add_thread_member'), 10, 2);

		add_action('rest_api_init', array($this, 'project_id'),1);

		add_action('rest_api_init', array($this, 'item_id'));
		add_action('rest_api_init', array($this, 'post_type'));
		add_action('rest_api_init', array($this, 'approval_data'));
		add_action('rest_api_init', array($this, 'private_comment') );
		add_action('rest_api_init', array($this, 'ph_comment_status') );
		add_action('init', array($this, 'ph_comment_status_check') );

		$this->rest = new PH_Rest_Request($this->rest_base);

		add_action('ph_new_comment_meta_saved', array($this, 'maybe_mention_members'), 10, 2);

		// add_action('ph_website_publish_comment', array($this, 'maybe_mention_members'), 10, 2);
		// add_action('ph_mockup_publish_comment', array($this, 'maybe_mention_members'), 10, 2);

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
		if ('read_comment' !== $cap) {
			return $caps;
		}

		// bail if no post
		if (!$comment = get_comment($args[0])) {
			return $caps;
		}

		// bail if not our comment type
		if (!in_array($comment->comment_type, ph_get_comment_types())) {
			return $caps;
		}

		if (!$comment->comment_post_ID) {
			return $caps;
		}

		if (!user_can($user_id, $cap, $comment->comment_post_ID)) {
			$caps[] = 'do_not_allow';
		}
		
		return $caps;
	}

	/**
	 * Get model by id
	 */
	public function get($id = 0, $autoload_comment = true, $autoload_comment_meta = true)
	{
		return new $this->model($id, $autoload_comment, $autoload_comment_meta);
	}

	/**
	 * Maybe trigger a user mention on a comment
	 *
	 * @param integer $id
	 * @param WP_Comment $comment
	 * @return void
	 */
	public function maybe_mention_members($id, $comment)
	{
		$mentioned = [];
		$comment_text = '';
		$result = '';
		$link = '';
		// must have DOMDocument
	
		if (class_exists('DOMDocument')) {
			$dom = new DOMDocument;
			if( !$comment->comment_content ) {
				return;
			}
			$dom->loadHTML($comment->comment_content);

			// get all data-mention-id tags
			foreach ($dom->getElementsByTagName('span') as $tag) {
				foreach ($tag->attributes as $attribName => $attribNode) {
					if ('data-mention-id' === $attribName) {
						// must be a valid user
						if (!$user = get_user_by('ID', $attribNode->value)) {
							continue;
						}
						$mentioned[] = $user->ID;
						// do action
						do_action('ph_mention_user', $user->ID, $id, $comment);
					}
				}
			}
		}

		// only send mentioned comments to mentioned users
		if (!empty($mentioned)) {
			if (!apply_filters('ph_send_mentioned_comments_to_all_users', false)) {
				return;
			}
		}
		
		$comment_type = ph_get_comment_project_type($id);
	
		do_action("ph_{$comment_type}_publish_comment_after_mentions", $id, $comment, $mentioned);
		do_action("ph_project_publish_comment_after_mentions", $id, $comment, $mentioned);
		
		// Send slack notification when new comment is added on mockup or website
		$slack_terms = (bool)get_option( 'ph_slack_terms' );

		$comment_post_ID = get_comment_meta( $id, 'project_id', true );
        
		// get the type of the Project (mockup/website)
        $type = ( get_post_type( $comment_post_ID ) == 'ph-website' ) ? __('website', 'project-huddle') : __('mockup', 'project-hudde');

		if( $slack_terms ) {
			if( 'on' == get_option( 'ph_slack_comment' ) && !empty( get_option( 'ph_slack_comment' ) ) ) {

				$is_private_comment_allowed = get_option( 'ph_private_comment_check' );

				if( 'on' !== $is_private_comment_allowed ) {
					$comment_meta = get_comment_meta( $comment->comment_ID, 'is_private', true);
					$check_private = filter_var( $comment_meta, FILTER_VALIDATE_BOOLEAN );

					if( $check_private ) {
						return;
					}
				}
				
				$user = new User( $id );
				$comment_content = $comment->comment_content;
				$email = $comment->comment_author_email;
				$comment_link = get_comment_link($comment);
				$this->current_user = new User( get_current_user_id() );
				$client_name = ( $this->current_user->first_name ) ? $this->current_user->first_name : $this->current_user->display_name;
				$client_url = get_avatar_url($comment->comment_author_email, 32);
				$thread = new Thread( $comment->comment_post_ID );

				$project_title = ph_get_the_title (Post::get( $comment->comment_post_ID )->parentsIds()['project'] );
				
				//Return currently added comment.
				$get_comment_link = ph_email_link($thread->getAccessLink(), __('View Comment', 'project-huddle'));

				preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $get_comment_link, $result);

				if (!empty($result)) {
					# Found a link.
					$link =  $result['href'][0]; //return comment link
				}

				$comment = get_option( 'ph_comment_text' );
				
				$find = array( '/\{ph_commenter_name\}/','/\{ph_project_type\}/' ,'/\{ph_project_name\}/');
				
				$replacement = array( $client_name, $type, $project_title );

				if( isset( $client_name ) || isset( $project_title ) ) {
					$comment_text = preg_replace( $find, $replacement, $comment );
				}
				
				push_incoming_webhook( $id, $comment_text , $link, $client_name, $project_title, $client_url, $comment_content );
			}
		}
	}
	
	/**
	 * Add a user as a thread "member" when they add a comment
	 *
	 * @param integer $id
	 * @param WP_Comment $comment
	 * @return void
	 */
	public function add_thread_member($id, $comment)
	{
		// get parent ids
		$parents = ph_get_parents_ids($comment, 'comment');
		// set item id
		if ($parents['thread'] && $comment->user_id) {
			if (false === get_userdata($comment->user_id)) {
				return;
			}

			ph_add_member_to_thread(
				array(
					'user_id' => $comment->user_id,
					'post_id' => $parents['thread'],
				)
			);
		}
	}

	/**
	 * Always store item id for easier querying
	 * An Item is a generic name for either a website
	 * page or mockup image
	 *
	 * @param $comment WP_Comment
	 * @param $id Comment ID
	 */
	public function set_parent_ids($id, $comment)
	{
		// get parent ids
		$parents = ph_get_parents_ids($comment, 'comment');

		// set item id
		if ($parents['item']) {
			// update meta
			update_comment_meta($comment->comment_ID, 'item_id', (int) $parents['item']);
		}

		// set project id
		if ($parents['project']) {
			// update meta
			update_comment_meta($comment->comment_ID, 'project_id', (int) $parents['project']);
		}
	}

	public function approval_data()
	{
		register_rest_field(
			'comment',
			'approval',
			array(
				'update_callback' => null,
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					return (bool) get_comment_meta($post['id'], $attr, true);
				},
				'schema'          => array(
					'description' => esc_html__('Whether the approval comment was for approval or not.', 'project-huddle'),
					'type'        => 'integer',
					'default'     => 0,
					'readonly'    => true,
				),
			)
		);
	}

	public function private_comment()
	{
		register_rest_field(
			'comment',
			'is_private',
			array(
				'update_callback' =>  function ( $value, $comment, $attr, $request, $object_type ) {

					if ( ! apply_filters( "ph_website_update_comment_{$attr}_allowed", true, $comment, $value ) ) {
						return new WP_Error( 'rest_forbidden_meta', __( 'Sorry, you are not allowed to do this.', 'project-huddle' ), array( 'status' => rest_authorization_required_code() ) );
					}

					$comment_meta = '';

					if( "true" == $value ) {
						$comment_meta = update_comment_meta( $comment->comment_ID, $attr, $value );
					}
					
					do_action( "ph_new_comment_meta_saved", $comment->comment_ID, $comment );

					return ( "true" == $value ) ? $comment_meta : null;
				},
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					$comment_meta = get_comment_meta( $post['id'], 'is_private', true);

					return filter_var( $comment_meta, FILTER_VALIDATE_BOOLEAN );
				},
				'schema'          => array(
					'description' => esc_html__( 'Private Comment.', 'project-huddle' ),
					'type'        => 'boolean',
					'default'     => false,
				),
			)
		);
		
	}

	public function ph_comment_status_check()
    {

        $all_post_ids = get_posts(
            array(
                'numberposts' => -1,
                'post_type'      => array( 'ph_comment_location', 'phw_comment_loc' ),
                'post_status' => 'any',
                'fields'      => 'ids'
            )
        );

		$is_review_enabled = get_option('ph_review_status_enable');
		$is_progress_enabled = get_option('ph_progress_status_enable');
		
		foreach ( $all_post_ids as $post_id ) { 

			$is_resolved = get_post_meta( $post_id, 'resolved', true);
      		$is_status = get_post_meta( $post_id, 'is_status');

			if(empty($is_status[0]) && $is_resolved)
			{
				update_post_meta( $post_id, 'is_status', 'resolved' );
			}

			if(empty($is_status[0]) && !$is_resolved) {
				update_post_meta( $post_id, 'is_status', 'active' );
			}

			if(!empty($is_status))
			{
				if(!empty($is_review_enabled) && $is_status[0] == 'in_review' || !empty($is_progress_enabled) && $is_status[0] == 'in_progress') {
					update_post_meta( $post_id, 'is_status', 'active' );
				}
			}

		}

    }

	public function ph_comment_status()
	{
		register_rest_field(
			'comment',
			'is_status',
			array(
				'update_callback' =>  function ( $value, $comment, $attr, $request, $object_type ) {

					if ( ! apply_filters( "ph_website_update_comment_{$attr}_allowed", true, $comment, $value ) ) {
						return new WP_Error( 'rest_forbidden_meta', __( 'Sorry, you are not allowed to do this.', 'project-huddle' ), array( 'status' => rest_authorization_required_code() ) );
					}

					$comment_meta = '';
					if( isset($value) ) {
						update_comment_meta( $comment->comment_ID, $attr, $value );
					}
					
					do_action( "ph_new_comment_meta_saved", $comment->comment_ID, $comment );

					return;
				},
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					$comment_meta = get_comment_meta( $post['id'], 'is_status', true);

					return $comment_meta;
				},
				'schema'          => array(
					'description' => esc_html__( 'Comment Status.', 'project-huddle' ),
					'type'        => 'string',
					'default'     => 'active',
				),
			)
		);
		
	}

	public function project_id()
	{
		register_rest_field(
			'comment',
			'project_id',
			array(
				'update_callback' => null,
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					return (int) get_comment_meta($post['id'], $attr, true);
				},
				'schema'          => array(
					'description' => esc_html__('ID of the project.', 'project-huddle'),
					'type'        => 'integer',
					'default'     => 0,
					'readonly'    => true,
				),
			)
		);
	}

	public function item_id()
	{
		register_rest_field(
			'comment',
			'item_id',
			array(
				'update_callback' => null,
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					return (int) get_comment_meta($post['id'], $attr, true);
				},
				'schema'          => array(
					'description' => esc_html__('ID of the project.', 'project-huddle'),
					'type'        => 'integer',
					'default'     => 0,
					'readonly'    => true,
				),
			)
		);
	}

	public function post_type()
	{
		register_rest_field(
			'comment',
			'comment_post_type',
			array(
				'update_callback' => null,
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					$type = get_post_type($post['post']);

					switch ($type):
						case 'phw_comment_loc':
						case 'ph_comment_location':
							return 'thread';
							break;
						case 'project_image':
							return 'image';
							break;
						case 'website_page':
							return 'page';
							break;
						case 'ph-project':
						case 'ph-website':
							return 'project';
							break;
						default:
							return $type;
							break;
					endswitch;
				},
				'schema'          => array(
					'description' => esc_html__('Post type of the parent post.', 'project-huddle'),
					'type'        => 'string',
					'default'     => '',
					'readonly'    => true,
				),
			)
		);
	}

	/**
	 * Trigger the correct action
	 *
	 * @param $action  string Action to trigger
	 * @param $id      integer Comment ID
	 * @param $comment WP_Comment Comment Object
	 */
	function do_action($action, $id, $comment)
	{
		if (in_array($comment->comment_type, ph_get_comment_types())) {
			// get endpoint type based on parent
			$project_type = ph_get_comment_project_type($id);
			// comment type name
			$comment_type = ph_comment_type_name($comment->comment_type);
			// run action
			do_action("ph_{$project_type}_{$action}_{$comment_type}", $id, $comment);
		}
	}

	/**
	 * New comment notification
	 *
	 * @param            $id
	 * @param WP_Comment $comment
	 */
	function new_comment($id, $comment)
	{
		$this->do_action('publish', $id, $comment);
	}

	/**
	 * New comment notification
	 *
	 * @param            $id
	 * @param array $data Comment data
	 */
	function edit_comment($id, $data)
	{
		$comment = get_comment($id);
		$this->do_action('edit', $id, $comment);
	}

	/**
	 * New comment notification
	 *
	 * @param            $id
	 * @param WP_Comment $comment
	 */
	function trash_comment($id, $comment)
	{
		$this->do_action('delete', $id, $comment);
	}
}
