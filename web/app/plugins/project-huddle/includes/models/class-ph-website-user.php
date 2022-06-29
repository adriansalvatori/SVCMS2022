<?php

/**
 * User data
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       2.6.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

class PH_Website_User extends PH_Rest_Object
{
	/**
	 * Model to use for getting model data
	 *
	 * @var string
	 */
	protected $model = '\PH\Models\User';

	/**
	 * Meta type
	 * @var string
	 */
	protected $meta_type = 'user';

	/**
	 * Page route for internal requests
	 * @var string
	 */
	public $route = '/projecthuddle/v2/users';

	/**
	 * Users type
	 * @var string
	 */
	public $post_type = 'user';

	/**
	 * Parent post type for our specific user data
	 *
	 * @var string
	 */
	public $parent_post_type = 'ph-website';

	/**
	 * For website commenting
	 *
	 * @var string
	 */
	public $endpoint_type = 'website';

	/**
	 * Overwrite route
	 * @return string
	 */
	public function route()
	{
		return $this->route;
	}

	public function __construct()
	{
		parent::__construct();
		// add schema
		$this->schema = $this->schema();

		// add fields
		$this->register_fields_from_schema();

		// add me field to parent post type
		add_action('rest_api_init', array($this, 'user_me'));
	}

	/**
	 * Add current user data to project model
	 */
	public function user_me()
	{
		register_rest_field(
			$this->parent_post_type,
			'me',
			array(
				'get_callback'    => function () {
					return $this->rest->get(
						get_current_user_id(),
						array(),
						array('context' => 'edit'),
						$this->route . '/me'
					);
				},
				'update_callback' => null,
				'schema'          => array(
					'description' => __('Current user data.'),
					'type'        => 'object',
				),
			)
		);
	}

	public function force_avatars($response, $user, $request)
	{
		$response->data['avatar_urls'] = rest_get_avatar_urls($user->user_email);
		return $response;
	}

	/**
	 * Schema for post meta
	 * @return array
	 */
	public function schema()
	{
		return apply_filters(
			'ph_website_user_meta',
			array(
				'members' => array(
					'description' => esc_html__('Array of user objects who are members of the projects.', 'project-huddle'),
					'type'        => 'array',
					'items'       => array(
						'description' => esc_html__('User object.', 'project-huddle'),
						'type'        => 'object',
						'readonly'    => true,
					),
					'readonly'    => true,
				),
			)
		);
	}
}

new PH_Website_User();
