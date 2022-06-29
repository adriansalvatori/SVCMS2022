<?php

/**
 * Project functions
 *
 * Project functions for ProjectHuddle
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Get default project members
 *
 * @param integer $id
 * @return void
 */
function ph_default_project_members($id = 0)
{
	global $post;

	if (!$id && is_a($post, 'WP_POST')) {
		$id = $post->ID;
	}

	switch (get_post_meta($id, 'thread_subscribers', true)) {
		case 'author':
			$members = [(int) get_post_field('post_author', $id)];
			break;
		case 'none':
			$members = [];
			break;
		default:
			$members = ph_get_project_member_ids($id);
			break;
	}

	return apply_filters(
		'ph_project_default_thread_members',
		$members,
		$id
	);
}

/**
 * Generate unique 5 digit hash
 *
 * @param int $id     ID to use for hash.
 * @param int $length Length of hash.
 *
 * @return string
 */
function ph_generate_unique_hash($id, $length = 6)
{
	$hex = md5($id . uniqid('', true));

	$pack = pack('H*', $hex);
	$tmp  = base64_encode($pack);

	$uid = preg_replace('#(*UTF8)[^A-Za-z0-9]#', '', $tmp);

	$length = max(4, min(128, $length));

	while (strlen($uid) < $length) {
		$uid .= ph_gen_uuid(22);
	}

	return strtolower(substr($uid, 0, $length));
}

/**
 * Clear mockup transients by id
 *
 * @param int $post_id Post ID.
 */
function ph_mockup_clear_resolved_transients($post_id)
{
	if ('ph_comment_location' !== get_post_type($post_id)) {
		return;
	}

	$project = get_post_meta($post_id, 'mockup_id', true);

	delete_transient('ph_approval_status_' . $project);
}

/**
 * Query users subscribed projects
 *
 * @uses WP_Query
 *
 * @param array $args Arguments.
 *
 * @return array array of post type objects
 */
function ph_query_users_threads($args)
{
	$defaults = array(
		'types'       => array('all'),
		'numberposts' => 10,
		'paged'       => 1,
		'user_id'     => get_current_user_id(),
	);

	$args = wp_parse_args($args, $defaults);

	$thread_ids = ph_get_users_thread_ids($args['user_id']);

	// must include post__in
	$args['post__in'] = (array) $thread_ids;
	if (empty($args['post__in'])) {
		$args['post__in'] = array(0);
	}

	foreach ($args['types'] as $key => $type) {
		if ('mockup' === $type) {
			$args['types'][$key] = 'ph_comment_location';
		}
		if ('website' === $type) {
			$args['types'][$key] = 'phw_comment_loc';
		}
		if ('all' === $type) {
			unset($args['types'][$key]);
			$args['types'][] = 'ph_comment_location';
			$args['types'][] = 'phw_comment_loc';
		}
	}

	$args['post_type'] = $args['types'];
	unset($args['types']);

	// only project members.
	if (!isset($args['meta_query'])) {
		$args['meta_query'] = array();
	}

	// allow filters.
	$args['suppress_filters'] = false;

	// force status.
	if (empty($args['post_status'])) {
		$args['post_status'] = 'publish';
	}

	// number of posts.
	if (!empty($args['numberposts']) && empty($args['posts_per_page'])) {
		$args['posts_per_page'] = $args['numberposts'];
	}

	// include post ids.
	if (!empty($args['include'])) {
		$incposts               = wp_parse_id_list($args['include']);
		$args['posts_per_page'] = count($incposts);  // only the number of posts included.
		$args['post__in']       = $incposts;
	} elseif (!empty($args['exclude'])) {
		$args['post__not_in'] = wp_parse_id_list($args['exclude']);
	}

	$args['ignore_sticky_posts'] = true;
	$args['no_found_rows']       = true;

	wp_reset_postdata();
	$query = new WP_Query(apply_filters(__FUNCTION__ . '_args', $args));
	wp_reset_postdata();
	wp_reset_query();
	return $query;
}

/**
 * Query users subscribed projects
 *
 * @uses WP_Query
 *
 * @param array $args Arguments.
 *
 * @return array array of post type objects
 */
function ph_query_users_projects($args)
{
	$defaults = array(
		'types'       => array('all'),
		'numberposts' => 10,
		'paged'       => 1,
		'user_id'     => get_current_user_id(),
	);

	$args = wp_parse_args($args, $defaults);

	$project_ids = ph_get_users_project_ids($args['user_id']);

	// must include post__in
	$args['post__in'] = (array) $project_ids;
	if (empty($args['post__in'])) {
		$args['post__in'] = array(0);
	}

	foreach ($args['types'] as $key => $type) {
		if ('mockup' === $type) {
			$args['types'][$key] = 'ph-project';
		}
		if ('website' === $type) {
			$args['types'][$key] = 'ph-website';
		}
		if ('all' === $type) {
			unset($args['types'][$key]);
			$args['types'][] = 'ph-website';
			$args['types'][] = 'ph-project';
		}
	}

	$args['post_type'] = $args['types'];
	unset($args['types']);

	// only project members.
	if (!isset($args['meta_query'])) {
		$args['meta_query'] = array();
	}

	// allow filters.
	$args['suppress_filters'] = false;

	// force status.
	if (empty($args['post_status'])) {
		$args['post_status'] = 'publish';
	}

	// number of posts.
	if (!empty($args['numberposts']) && empty($args['posts_per_page'])) {
		$args['posts_per_page'] = $args['numberposts'];
	}

	// include post ids.
	if (!empty($args['include'])) {
		$incposts               = wp_parse_id_list($args['include']);
		$args['posts_per_page'] = count($incposts);  // only the number of posts included.
		$args['post__in']       = $incposts;
	} elseif (!empty($args['exclude'])) {
		$args['post__not_in'] = wp_parse_id_list($args['exclude']);
	}

	$args['ignore_sticky_posts'] = true;
	$args['no_found_rows']       = true;

	wp_reset_postdata();
	$query = new WP_Query(apply_filters(__FUNCTION__ . '_args', $args));
	wp_reset_postdata();
	wp_reset_query();
	return $query;
}

/**
 * Query project images.
 *
 * @param array $args Arguments.
 * @return bool|array
 */
function ph_query_mockup_images($args = array())
{
	return ph_query_project_subcollection($args);
}

/**
 * Get project images as array
 *
 * @param array $args Arguments.
 * @return array Array of Post Objects
 */
function ph_get_mockup_images($args)
{
	$query = ph_query_mockup_images($args);
	$posts = $query->posts;
	wp_reset_postdata();
	return $posts;
}

/**
 * Query project images.
 *
 * @param array $args Arguments.
 * @return bool|WP_Query
 */
function ph_query_website_pages($args = array())
{
	return ph_query_project_subcollection($args);
}

/**
 * Get project images as array
 *
 * @param array $args Arguments.
 * @return array Array of Post Objects
 */
function ph_get_website_pages($args)
{
	$query = ph_query_website_pages($args);
	$posts = $query->posts;
	wp_reset_postdata();
	return $posts;
}

/**
 * Query project images.
 *
 * @param array  $args Arguments.
 * @return bool|WP_Query
 */
function ph_query_project_subcollection($args = array())
{
	global $post;

	$defaults = array(
		'id' => is_a($post, 'WP_Post') ? $post->ID : 0,
	);

	$args = wp_parse_args($args, $defaults);

	if (!$args['id']) {
		return false;
	}

	$type = 'all';

	if (!isset($args['post_type'])) {
		switch (get_post_type($args['id'])) {
			case 'ph-project':
				$type = 'project_image';
				break;
			case 'ph-website':
				$type = 'ph-webpage';
				break;
			case 'project_image':
				$type = 'ph_comment_location';
				break;
			case 'ph-webpage':
				$type = 'phw_comment_loc';
				break;
			default:
				$type = 'all';
				break;
		}
	}

	if (!isset($args['meta_query'])) {
		$args['meta_query'] = array();
	}

	$args['meta_query'][] = array(
		'key'   => 'parent_id',
		'value' => $args['id'],
	);
	unset($args['id']);

	$args['post_type'] = isset($args['post_type']) ? $args['post_type'] : array($type);

	// order by menu order by default
	if (!isset($args['orderby'])) {
		$args['orderby'] = 'menu_order';
		$args['order']   = 'ASC';
	}

	wp_reset_postdata();
	$query = new WP_Query(apply_filters(__FUNCTION__ . '_args', $args));
	return $query;
}

/**
 * Wrapper for get_post_meta in case of key changes.
 */
function ph_get_data($post_id, $key = '', $single = false)
{
	return apply_filters("ph_get_{$key}_data", get_post_meta($post_id, $key, $single), $post_id, $key, $single);
}

/**
 * Wrapper for update_post_meta in case of key changes.
 */
function ph_update_data($post_id, $key = '', $data = '' )
{
	$data = apply_filters("ph_update_{$key}_data", $data, $post_id, $key);

	return update_post_meta($post_id, $key, $data);
}

/**
 * Backwards compatibilty for get_post_meta project members
 *
 * @param $null
 * @param $object_id
 * @param $meta_key
 * @param $single
 *
 * @return null|array Array of project members
 */
function ph_fallback_project_members_get_post_meta($null, $object_id, $meta_key, $single)
{
	if ('1.0' == get_site_option('ph_members_db_version')) {
		if ('project_members' === $meta_key && in_array(get_post_type($object_id), ph_get_post_types())) {
			return array(ph_get_project_member_ids($object_id));
		}
	}

	return $null;
}

add_filter('get_post_metadata', 'ph_fallback_project_members_get_post_meta', 10, 4);

/**
 * Backwards compatibilty for get_post_meta project members
 *
 * @param $null
 * @param $object_id
 * @param $meta_key
 * @param $meta_value
 * @param $prev_value
 *
 * @return null|array Array of project members
 */
function ph_fallback_project_members_update_post_meta($null, $object_id, $meta_key, $meta_value, $prev_value)
{
	if ('1.0' == get_site_option('ph_members_db_version')) {
		if ('project_members' === $meta_key && in_array(get_post_type($object_id), ph_get_post_types())) {
			return ph_update_project_members($object_id, (array) $meta_value);
		}
	}

	return $null;
}

add_filter('update_post_metadata', 'ph_fallback_project_members_update_post_meta', 10, 5);
