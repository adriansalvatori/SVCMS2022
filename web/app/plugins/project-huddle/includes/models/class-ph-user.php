<?php

/**
 * User data
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       2.7.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

class PH_User extends PH_Rest_Object
{
	protected $rest_base = 'users';

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
	public $parent_post_type = 'ph-project';

	/**
	 * For website commenting
	 *
	 * @var string
	 */
	public $endpoint_type = 'mockup';

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
		add_action('rest_api_init', array($this, 'user_me'));
	}

	/**
	 * Add current user data to project model
	 */
	public function user_me()
	{
		register_rest_field($this->parent_post_type, 'me', array(
			'get_callback' => function () {
				return $this->rest->get(
					get_current_user_id(),
					array(),
					array('context' => 'edit'),
					$this->route . '/me'
				);
			},
			'update_callback' => null,
			'schema' => array(
				'description' => __('Current user data.'),
				'type'        => 'object'
			),
		));
	}
}
