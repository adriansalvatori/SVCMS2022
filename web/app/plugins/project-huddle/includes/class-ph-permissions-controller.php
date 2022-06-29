<?php

/**
 * Permissions class
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       3.6.0
 *
 * Uses the chain of resposibiltiy design pattern
 */

require_once PH_PLUGIN_DIR . 'includes/permissions/class-ph-project-permissions-checker.php';
require_once PH_PLUGIN_DIR . 'includes/permissions/class-ph-permissions-data.php';

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

class PH_Permissions_Controller
{
	/**
	 * @var PH_Permissions_Data
	 */
	public $data;

	/**
	 * Need to set permissions data in class on construct
	 *
	 * @param integer $post_id
	 * @param string $signature
	 * @param string $token
	 * @param string $email
	 * @param string $username
	 */
	public function __construct($post_id, $signature = '', $token = '', $email = 'guest', $username = '')
	{
		$guests  = filter_var(get_post_meta($post_id, 'allow_guests', true), FILTER_VALIDATE_BOOLEAN);
		// backwards compat
		if (!$guests) {
			$guests = get_post_meta($post_id, 'project_access', true) === 'public';
		}
		$this->data = new PH_Permissions_Data($post_id, $guests, $signature, $token, $email, $username);
	}

	/**
	 * Can the visitor access the project?
	 *
	 * @param integer $post_id
	 * @param string $signature
	 * @param string $email
	 * @param string $token
	 * @return bool|WP_Error
	 */
	public function visitor_can_access()
	{
		// must have a post ID
		if (!$this->data->id) {
			return new WP_Error('post_invalid_id', __('Invalid post ID.', 'project-huddle'));
		}

		// newup checker
		$checker = new PH_Project_Permissions_Checker($this->data);

		$valid = $checker->check();

		// get access
		return apply_filters('ph_visitor_can_access', $valid, $this->data);
	}

	/**
	 * Identify the current visitor and login if necessary
	 *
	 * @return void
	 */
	public function validate_access_and_identify()
	{
		// check accesss
		$access = $this->visitor_can_access();

		// no access
		if (is_wp_error($access) || !$access) {
			return $access;
		}

		// create or login user
		$this->create_or_login_user();

		// add member to project
		if ($user = wp_get_current_user()) {
			ph_add_member_to_project(
				array(
					'user_id'    => $user->ID,
					'project_id' => $this->data->id,
				)
			);
		}

		// return original access
		return $access;
	}

	/**
	 * This sends an internal rest API request to create or
	 * login a user with a project client role
	 *
	 * @return void
	 */
	public function create_or_login_user()
	{
		// must not already be logged in and email must be an email
		if (!filter_var($this->data->email, FILTER_VALIDATE_EMAIL)) {
			return true;
		}

		$user = wp_get_current_user();

		// if the user is logged in already
		if (is_user_logged_in()) {
			// only if they are trying to change
			if ($user->user_email == $this->data->email) {
				return true;
			}
			// if they are not a project client, let's use their credentials already
			// otherwise let's switch accounts
			if (!current_user_can('project_client')) {
				return true;
			}
		}

		// this will create or login if they have access and project settings dictate that
		// otherwise it will log them in
		$response = PH()->user->create_item(
			array(
				'email'        => sanitize_email($this->data->email),
				'username'     => sanitize_text_field($this->data->username),
				'access_token' => wp_kses_post($this->data->token),
				'project_id'   => $this->data->id,
			),
			array(
				'_signature' => $this->data->signature,
			)
		);

		return $response;
	}
}
