<?php

/**
 * Custom membership functions
 * @since 3.0.12
 */

if (!defined('ABSPATH')) {
	exit;
}

// require generics
require_once 'generic-membership-functions.php';

/**
 * Adds a single user to project
 *
 * @param $args 'user_id' integer User ID
 *              'project_id' integer Project Post ID
 *
 * @return int|false|WP_Error Number of rows affected/selected or false on error
 */
function ph_add_member_to_project($args)
{
	if (isset($args['post_id'])) {
		$args['project_id'] = $args['post_id'];
	}
	return ph_add_member_to_post(
		[
			'user_id' => isset($args['user_id']) ? $args['user_id'] : 0,
			'post_id' => isset($args['project_id']) ? $args['project_id'] : 0,
		],
		'ph_members',
		'project_id'
	);
}

/**
 * Add an email to a project programmatically
 *
 * @param integer $project_id ID for the project.
 * @param WP_User $user       User to add.
 */
function ph_add_project_member($project_id, $user)
{
	$added = ph_add_member_to_project(
		array(
			'user_id'    => $user->ID,
			'project_id' => $project_id,
		)
	);
	if (is_wp_error($added)) {
		return $added;
	}
	// if user isn't member yet, trigger subscribe email
	if ($added) {
		do_action('ph_members_added', $user->ID, $project_id);
	}
}

/**
 * Adds a single user to thread
 *
 * @param $args 'user_id' integer User ID
 *              'project_id' integer Project Post ID
 *
 * @return int|false|WP_Error Number of rows affected/selected or false on error
 */
function ph_add_member_to_thread($args)
{
	return ph_add_member_to_post(
		array(
			'user_id' => isset($args['user_id']) ? $args['user_id'] : 0,
			'post_id' => isset($args['post_id']) ? $args['post_id'] : 0,
		),
		'ph_thread_members',
		'post_id'
	);
}

/**
 * Get a list of all project members for all projects
 *
 * @return array User IDs
 */
function ph_get_all_project_users()
{
	return ph_get_table_users('ph_members');
}

/**
 * Get project members IDs
 * Returns a list of member ids for a project
 *
 * @param int $project_id Project Post ID
 *
 * @return array Array of user objects
 */
function ph_get_project_member_ids($project_id = 0)
{
	return ph_get_post_member_ids($project_id, 'ph_members', 'project_id');
}

/**
 * Get thread members IDs
 * Returns a list of member ids for a thread
 *
 * @param int $post_id Post ID
 *
 * @return array Array of user objects
 */
function ph_get_thread_member_ids($post_id = 0)
{
	return ph_get_post_member_ids($post_id, 'ph_thread_members', 'post_id');
}

/**
 * Get project members
 * Similar to ph_get_project_member_ids except it returns user objects
 *
 * @param int $project_id Project Post ID.
 *
 * @return array Array of user objects
 */
function ph_get_project_members($project_id = 0)
{
	return ph_get_post_members($project_id, 'ph_members', 'project_id');
}

/**
 * Get thread members
 * Similar to ph_get_thread_member_ids except it returns user objects
 *
 * @param int $post_id Post ID.
 *
 * @return array Array of user objects
 */
function ph_get_thread_members($post_id = 0)
{
	return ph_get_post_members($post_id, 'ph_thread_members', 'post_id');
}

/**
 * Update project members in bulk
 *
 * @param integer $project_id  ID of project post.
 * @param array $users_array  Array of user IDs
 *
 * @return array User array
 */
function ph_update_project_members($project_id, $users_array)
{
	return ph_update_post_members($project_id, $users_array, 'ph_members', 'project_id');
}

/**
 * Update project members in bulk
 *
 * @param $post_id integer ID of project post.
 * @param $users_array array Array of user IDs
 *
 * @return array User array
 */
function ph_update_thread_members($post_id, $users_array)
{
	return ph_update_post_members($post_id, $users_array, 'ph_thread_members', 'post_id');
}

/**
 * Remove a single project member
 *
 * @param $args 'user_id' integer User ID to remove
 *              'project_id' integer Project Post ID
 *
 * @return false|int|WP_Error
 */
function ph_remove_project_member($args)
{
	if (isset($args['post_id'])) {
		$args['project_id'] = $args['post_id'];
	}
	return ph_remove_post_member(
		array(
			'user_id' => isset($args['user_id']) ? $args['user_id'] : 0,
			'post_id' => isset($args['project_id']) ? $args['project_id'] : 0,
		),
		'ph_members',
		'project_id'
	);
}

/**
 * Remove a single project member
 *
 * @param $args 'user_id' integer User ID to remove
 *              'project_id' integer Project Post ID
 *
 * @return false|int|WP_Error
 */
function ph_remove_thread_member($args)
{
	return ph_remove_post_member(
		array(
			'user_id' => isset($args['user_id']) ? $args['user_id'] : 0,
			'post_id' => isset($args['post_id']) ? $args['post_id'] : 0,
		),
		'ph_thread_members',
		'post_id'
	);
}


/**
 * Gets a users project ids
 *
 * @param $user_id
 *
 * @return array
 */
function ph_get_users_project_ids($user_id = 0)
{
	return ph_get_users_post_ids(
		$user_id,
		'ph_members',
		'project_id'
	);
}

/**
 * Gets a users thread ids
 *
 * @param $user_id
 *
 * @return array
 */
function ph_get_users_thread_ids($user_id = 0)
{
	return ph_get_users_post_ids(
		$user_id,
		'ph_thread_members',
		'post_id'
	);
}

/**
 * Get users projects
 *
 * @param array $args Arguments.
 * @return array Array of Post Objects
 */
function ph_get_users_threads($args)
{
	$query = ph_query_users_threads($args);
	$posts = $query->posts;
	wp_reset_postdata();
	return $posts;
}

/**
 * Get users projects
 *
 * @param array $args Arguments.
 * @return array Array of Post Objects
 */
function ph_get_users_projects($args)
{
	$query = ph_query_users_projects($args);
	$posts = $query->posts;
	wp_reset_postdata();
	return $posts;
}


/**
 * Get project members by old storage method
 *
 * @param integer $project_id Project post id.
 *
 * @return array Array of ids.
 */
function ph_backwards_compat_get_project_members($project_id)
{
	if (!$project_id) {
		global $post;
		if ($post) {
			$project_id = $post->ID;
		} else {
			return false;
		}
	}

	$emails  = (array) get_post_meta((int) $project_id, 'ph_project_emails_enable', true);
	$emails  = array_unique($emails);
	$members = array();

	// Store user ids in ids array.
	foreach ($emails as $key => $email) {
		if (!$email) {
			continue;
		}

		// Get user by email.
		$user = get_user_by('email', $email);

		// users only.
		if ($user && is_a($user, 'WP_User')) {
			// store user in array.
			$members[] = $user;
		}
	}

	// update in new post meta.
	if (!empty($members)) {
		$ids = array();
		foreach ($members as $member) {
			if (is_a($member, 'WP_User')) {
				$ids[] = $member->ID;
			}
		}
	}

	return $members;
}

/**
 * Get project member ids backwards compat
 *
 * @param integer $project_id Project post id.
 *
 * @return array
 */
function ph_backwards_compat_get_project_member_ids($project_id)
{
	$members = ph_backwards_compat_get_project_members($project_id);

	$ids = array();

	// update in new post meta.
	if (!empty($members)) {
		foreach ($members as $member) {
			if (is_a($member, 'WP_User')) {
				$ids[] = $member->ID;
			}
		}
	}

	return $ids;
}
