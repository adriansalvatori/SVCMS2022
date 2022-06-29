<?php

/**
 * Abstracted membership functions to interact with custom table
 */
if (!defined('ABSPATH')) {
	exit;
}


/**
 * Abstract function to add members to a post. Accepts the table name

 * @param array $args
 * @param string $table_name
 * @return void
 */
function ph_add_member_to_post($args, $table_name = '', $key_name = '')
{
	global $wpdb;

	if (!$table_name) {
		return new WP_Error('no_table_name', __('Table name not provided.', 'project-huddle'));
	}

	if (!$key_name) {
		return new WP_Error('no_key_name', __('Key name not provided.', 'project-huddle'));
	}

	// set defaults
	$defaults = array(
		'user_id' => 0,
		'post_id' => 0,
	);
	$args     = wp_parse_args($args, $defaults);

	// user id must be valid
	if (!$args['user_id']) {
		return new WP_Error('invalid_user_id', 'You need to provide a valid user id.');
	}
	$user = get_user_by('ID', $args['user_id']);

	// user must be a valid user
	if (!is_a($user, 'WP_User')) {
		return new WP_Error('invalid_user_id', 'You need to provide a valid user id.');
	}

	// must provide a project id
	if (!$args['post_id']) {
		return new WP_Error('invalid_post_id', 'You need to provide a valid post_id.');
	}

	// clear object cache
	$cache_key = md5('ph_get_post_member_ids_' . serialize($args['post_id']));
	wp_cache_delete($cache_key, 'post_member_ids');

	// add user into database
	$inserted = $wpdb->query(
		$wpdb->prepare(
			"INSERT ignore INTO `{$wpdb->prefix}{$table_name}` ( `user_id` ,`{$key_name}`)
          VALUES (%d,%d)",
			(int) $args['user_id'],
			(int) $args['post_id']
		)
	);
	return $inserted;
}

/**
 * Get all users from a table
 *
 * @param string $table_name
 * @return void
 */
function ph_get_table_users($table_name)
{
	global $wpdb;

	if (!$table_name) {
		return new WP_Error('no_table_name', __('Table name not provided.', 'project-huddle'));
	}

	// query project members
	$users = $wpdb->get_results(
		"SELECT DISTINCT user_id FROM {$wpdb->prefix}{$table_name}",
		ARRAY_N
	);

	$flattened = array_reduce($users, 'array_merge', array());

	return (array) $flattened;
}

/**
 * Getpost members IDs
 * Returns a list of member ids for a post
 *
 * @param integer $post_id
 * @param string $table_name
 * @return array
 */
function ph_get_post_member_ids($post_id = 0, $table_name = '', $key_name = '')
{
	global $wpdb;

	if (!$table_name) {
		return new WP_Error('no_table_name', __('Table name not provided.', 'project-huddle'));
	}

	if (!$key_name) {
		return new WP_Error('no_key_name', __('Key name not provided.', 'project-huddle'));
	}

	if (!$post_id) {
		global $post;
		if ($post) {
			$post_id = $post->ID;
		} else {
			return false;
		}
	}

	// check object cache
	$cache_key = md5('ph_get_post_member_ids_' . serialize($post_id));
	$ids       = wp_cache_get($cache_key, 'post_member_ids');

	// run query if not cached
	if (false === $ids) {
		// query project members
		$users = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
                  PU.user_id 
              FROM 
                  {$wpdb->posts} P
                  JOIN
                  {$wpdb->prefix}{$table_name} PU ON
                  (PU.{$key_name} = P.ID)
                  WHERE
              P.ID = %d",
				(int) $post_id
			),
			ARRAY_A
		);

		$ids = array();
		if (!empty($users)) {
			foreach ($users as $user) {
				if ($user['user_id']) {
					$ids[] = $user['user_id'];
				}
			}
		}

		// sanitize
		$ids = array_map('intval', $ids);

		// Backwards compatibility.
		if ($table_name === 'ph_members') {
			if (empty($ids)) {
				// get backwards compat project member ids.
				$ids = ph_backwards_compat_get_project_member_ids($post_id);
			}

			if (apply_filters('ph_include_author_as_project_member', true)) {
				$author = get_post_field('post_author', $post_id);

				if ($author && is_a($author, 'WP_User')) {
					$ids[] = $author->ID;
				}
			}
		}

		// store in cache
		wp_cache_set($cache_key, $ids, 'post_member_ids', 3600);
	}

	return array_unique(array_filter($ids));
}

/**
 * Update post members in bulk
 *
 * @param integer $post_id
 * @param array $users_array
 * @param string $table_name
 * @param string $key_name
 *
 * @return array User array
 */
function ph_update_post_members($post_id, $users_array, $table_name, $key_name)
{
	global $wpdb;

	if (!$table_name) {
		return new WP_Error('no_table_name', __('Table name not provided.', 'project-huddle'));
	}

	if (!$key_name) {
		return new WP_Error('no_key_name', __('Key name not provided.', 'project-huddle'));
	}

	// delete removed
	$current_members = ph_get_post_member_ids($post_id, $table_name, $key_name);
	$difference      = array_diff($current_members, $users_array);

	$added   = ph_array_diff_once($users_array, $current_members);
	$removed = ph_array_diff_once($current_members, $users_array);

	if (!empty($removed)) {
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}{$table_name}
          WHERE {$key_name} = %d AND user_id IN (" . join(',', array_map('intval', $difference)) . ')',
				$post_id
			)
		);
		foreach ($removed as $id) {
			do_action("{$table_name}_removed", $id, $post_id);
		}

		// delete members id cache
		$cache_key = md5('ph_get_post_member_ids_' . serialize($post_id));
		wp_cache_delete($cache_key, 'post_member_ids');
	}

	// update or add
	if (empty($added)) {
		return $added;
	}

	$sql_query        = "INSERT ignore INTO `{$wpdb->prefix}{$table_name}` ( `user_id` ,`{$key_name}`) ";
	$sql_query_values = '';

	foreach ($added as $id) {
		$sql_query_values .= '( ' . (int) $id . ', ' . (int) $post_id . '),';
		do_action("{$table_name}_added", $id, $post_id);
	}

	// remove last comma
	$sql_query_values = rtrim($sql_query_values, ',');

	// save values
	$sql_query .= ' VALUES ' . $sql_query_values . ';';

	$wpdb->query($sql_query);

	// delete members id cache
	$cache_key = md5('ph_get_post_member_ids_' . serialize($post_id));
	wp_cache_delete($cache_key, 'post_member_ids');

	// sanitize response
	$users_array = array_map('intval', $users_array);

	return $users_array;
}

/**
 * Remove a single post member
 *
 * @param $args 'user_id' integer User ID to remove
 *              'post_id' integer Project Post ID
 *
 * @return false|int|WP_Error
 */
function ph_remove_post_member($args, $table_name, $key_name)
{
	global $wpdb;

	$args = wp_parse_args(
		$args,
		array(
			'user_id' => 0,
			'post_id' => 0,
		)
	);

	if (!$args['user_id']) {
		return new WP_Error('invalid_user_id', 'You need to provide a valid user id.');
	}

	$user = get_user_by('ID', $args['user_id']);

	if (!is_a($user, 'WP_User')) {
		return new WP_Error('invalid_user_id', 'You need to provide a valid user id.');
	}

	if (!$args['post_id']) {
		return new WP_Error('invalid_post_id', 'You need to provide a valid post_id.');
	}

	// member removed action
	do_action("{$key_name}_removed", $args['user_id'], $args['post_id']);

	$query = $wpdb->prepare(
		"DELETE FROM {$wpdb->prefix}{$table_name} WHERE {$key_name} = %d AND user_id = %d",
		$args['post_id'],
		$args['user_id']
	);

	$result = $wpdb->query($query);

	// delete members id cache
	$cache_key = md5('ph_get_post_member_ids_' . serialize($args['post_id']));
	$result    = wp_cache_delete($cache_key, 'post_member_ids');

	$cache_key = md5("ph_users_{$key_name}_" . serialize($args['user_id']));
	wp_cache_delete($cache_key, 'members');

	return $result;
}

/**
 * Get a users post ids
 *
 * @param integer $user_id
 * @param string $table_name
 * @param string $key_name
 * @return void
 */
function ph_get_users_post_ids($user_id, $table_name, $key_name)
{
	$user_id = $user_id ? $user_id : get_current_user_id();

	if (is_a($user_id, 'WP_User')) {
		$user_id = $user_id->ID;
	}

	if (!$user_id) {
		return array();
	}

	// check object cache
	$cache_key = md5("ph_users_{$key_name}_" . serialize($user_id));
	$ids       = wp_cache_get($cache_key, 'members');

	// run query if not cached
	if (false === $ids) {

		global $wpdb;

		// query project members
		$users = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				PU.{$key_name}
			FROM 
				{$wpdb->users} U
				JOIN
			    {$wpdb->prefix}{$table_name} PU ON
				(PU.user_id = U.ID)
				WHERE
			U.ID = %d",
				(int) $user_id
			),
			ARRAY_A
		);

		// users array
		$ids = array_column($users, $key_name);

		// sanitize
		$ids = array_map('intval', $ids);

		// store in cache
		wp_cache_set($cache_key, $ids, 'members', 3600);
	}

	// return ids
	return $ids;
}

/**
 * Get post members
 * Similar to ph_get_post_member_ids except it returns user objects
 *
 * @param int $post_id Post ID.
 *
 * @return array Array of user objects
 */
function ph_get_post_members($post_id = 0, $table_name = '', $key_name = '')
{
	if (!$post_id) {
		global $post;
		if ($post) {
			$post_id = $post->ID;
		} else {
			return false;
		}
	}

	// users array
	$ids = ph_get_post_member_ids($post_id, $table_name, $key_name);

	if (is_wp_error($ids)) {
		return $ids;
	}

	// Backwards compatibility.
	if (empty($ids) && $table_name == 'ph_members') {
		// get backwards compat project members.
		$members = ph_backwards_compat_get_project_members($post_id);
	} else {
		// Loop through and get emails.
		foreach ($ids as $key => $id) {
			$member = get_user_by('id', $id);

			if ($member) {
				$members[] = $member;
			}
		}
	}

	return $members;
}
