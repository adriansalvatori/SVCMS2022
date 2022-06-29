<?php

/**
 * Controls checking of login access
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

class PH_Login_Checker extends PH_Permission_Checker
{
	/**
	 * Validate Login
	 *
	 * @return boolean
	 */
	public function validate()
	{
		$post = get_post($this->data->id);
		$post_type = get_post_type_object($post->post_type);
		$user = wp_get_current_user();

		// must be logged in
		if (!is_user_logged_in()) {
			return false;
		}

		// allow access if user can edit
		if (current_user_can($post_type->cap->edit_post, $post->ID)) {
			return true;
		}

		// otherwise must be a member
		return ph_is_user_subscribed($user, $post);
	}
}
