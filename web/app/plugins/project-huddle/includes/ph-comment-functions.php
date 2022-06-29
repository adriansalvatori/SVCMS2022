<?php

/**
 * Comment Functions
 *
 * Functions related to comments
 *
 * @package     Project Huddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Custom function for Human Time Diff
 *
 * Allows translations to be possible
 *
 * @param        $from
 * @param string $to
 *
 * @return string|void
 */
function ph_human_time_diff($from, $to = '')
{

	// If to is empty, get current time
	if (empty($to)) {
		$to = time();
	}

	$since = '';

	// get difference
	$diff = (int) abs($to - $from);

	// check hours, minutes days
	if ($diff <= HOUR_IN_SECONDS) {
		$mins = round($diff / MINUTE_IN_SECONDS);

		// if it's less than one minute
		if ($mins <= 1) {
			$since = __('Now', 'project-huddle');
		} else {
			/* translators: min=minute */
			$since = sprintf(_n('%sm', '%sm', $mins), $mins);
		}
	} elseif (($diff <= DAY_IN_SECONDS) && ($diff > HOUR_IN_SECONDS)) {
		$hours = round($diff / HOUR_IN_SECONDS);
		if ($hours <= 1) {
			$hours = 1;
		}
		$since = sprintf(_n('%sh', '%sh', $hours), $hours);
	} elseif ($diff >= DAY_IN_SECONDS) {
		$days = round($diff / DAY_IN_SECONDS);
		if ($days <= 1) {
			$days = 1;
		}
		$since = sprintf(_n('%sd', '%sd', $days), $days);
	}

	return $since;
}

/**
 * Exclude notes (comments) on edd_payment post type from showing in Recent
 * Comments widgets
 *
 * @since 1.4.1
 * @param obj $query WordPress Comment Query Object
 * @return void
 */
function ph_hide_ph_comments($query)
{
	global $wp_version, $pagenow;

	if (is_admin() && $pagenow === 'edit-comments.php' && isset($query->query_vars['post_id']) && $query->query_vars['post_id']) {
		return $query;
	}

	if (version_compare(floatval($wp_version), '4.1', '>=')) {
		$types = isset($query->query_vars['type__not_in']) ? $query->query_vars['type__not_in'] : array();
		if (!is_array($types)) {
			$types = array($types);
		}

		foreach (ph_get_comment_types() as $type) {
			$types[] = $type;
		}

		$query->query_vars['type__not_in'] = $types;
	}
}

add_action('pre_get_comments', 'ph_hide_ph_comments', 10);

/**
 * Exclude notes (comments) on edd_payment post type from showing in comment feeds
 *
 * @since 1.5.1
 * @param array $where
 * @param obj $wp_comment_query WordPress Comment Query Object
 * @return array $where
 */
function ph_hide_comments_from_feeds($where, $wp_comment_query)
{
	global $wpdb;

	$types = ph_get_comment_types();
	foreach ($types as $type) {
		$where .= $wpdb->prepare(" AND comment_type != %s", sanitize_text_field($type));
	}
	return $where;
}
add_filter('comment_feed_where', 'ph_hide_comments_from_feeds', 10, 2);

function ph_comment_data_meta_box()
{
	add_meta_box('ph-comment-data', __('Comment Debug Data', 'project-huddle'), 'ph_comment_data_meta_box_display', 'comment', 'normal');
}
add_action('add_meta_boxes', 'ph_comment_data_meta_box');

function ph_comment_data_meta_box_display($comment)
{
	$data         = get_comment_meta($comment->comment_ID);
	$data['post'] = $comment->comment_post_ID;

	if (is_array($data)) {
		echo '<table border=0 cellspacing=0 cellpadding=8 width=100%>';
		foreach ($data as $k => $v) {
			echo '<tr><td valign="top" style="width:40px;background-color:#F0F0F0;">';
			echo '<strong>' . $k . '</strong></td><td>';
			if (is_array($v)) {
				$v = $v[0];
			}
			$v = maybe_unserialize($v);

			if (is_array($v)) {
				$items = '';
				foreach ($v as $item) {
					$items = print_r($item . ',', 1);
				}
				print_r(rtrim($items, ','));
			} else {
				print_r($v);
			}
			echo '</td></tr>';
		}
		echo '</table>';

		return;
	}
	echo $data;
}

function ph_get_comment_types()
{
	return apply_filters('ph_get_comment_types', array(
		'ph_image_comment',
		'ph_website_comment',
		'ph_comment',
		'ph_assign',
		'ph_approval',
		'ph_status',
	));
}

function ph_get_timeline_comment_types()
{
	return apply_filters('ph_get_timeline_comment_types', array(
		'ph_image_comment',
		'ph_website_comment',
		'ph_comment',
		'ph_approval',
		'ph_status',
	));
}

function ph_comment_type_name($type)
{
	switch ($type) {
		case 'ph_website_comment':
		case 'ph_image_comment':
		case 'ph_comment':
			return 'comment';
		case 'ph_approval':
			return 'approval';
		case 'ph_status':
			return 'comment_status';
		default:
			return apply_filters('ph_comment_type_name', $type);
	}
}

/**
 * Gets the comment project type
 *
 * @param int $id
 *
 * @return string
 */
function ph_get_comment_project_type($id)
{
	// get parent ids
	$parent_ids = ph_get_parents_ids($id, 'comment');

	// get endpoint type based on parent
	$type = isset($parent_ids['website']) ? 'website' : 'mockup';

	// get comment project type
	return apply_filters('ph_get_comment_project_type', $type, $id);
}

/**
 * Remove EDD Comments from the wp_count_comments function
 *
 * @access public
 * @since 1.5.2
 * @param array $stats (empty from core filter)
 * @param int $post_id Post ID
 * @return array Array of comment counts
 */
function ph_remove_comments_in_comment_counts($stats, $post_id)
{
	global $wpdb, $pagenow;

	$array_excluded_pages = array('index.php', 'edit-comments.php');
	if (!in_array($pagenow, $array_excluded_pages)) {
		return $stats;
	}

	$post_id = (int) $post_id;

	if (apply_filters('ph_count_in_comments', false)) {
		return $stats;
	}

	$stats = wp_cache_get("comments-{$post_id}", 'counts');

	if (false !== $stats) {
		return $stats;
	}

	$types   = apply_filters('ph_get_comment_types_count', ph_get_comment_types());
	$types[] = 'order_note';
	$types[] = 'action_log';

	$where = 'WHERE ';
	$count = count($types);
	$i     = 0;

	foreach ($types as $type) {
		$where .= $wpdb->prepare('comment_type != "%s"', $type);
		if (++$i !== $count) {
			$where .= ' AND ';
		}
	}

	if ($post_id > 0) {
		$where .= $wpdb->prepare(' AND comment_post_ID = %d', $post_id);
	}

	$count = $wpdb->get_results("SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} {$where} GROUP BY comment_approved", ARRAY_A);
	$total = 0;

	$approved = array(
		'0'            => 'moderated',
		'1'            => 'approved',
		'spam'         => 'spam',
		'trash'        => 'trash',
		'post-trashed' => 'post-trashed',
	);

	foreach ((array) $count as $row) {
		// don't count project-huddle toward totals
		if ($row['comment_approved'] == 'project-huddle') {
			continue;
		}
		// Don't count post-trashed toward totals
		if ('post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved']) {
			$total += $row['num_comments'];
		}
		if (isset($approved[$row['comment_approved']])) {
			$stats[$approved[$row['comment_approved']]] = $row['num_comments'];
		}
	}

	$stats['total_comments'] = $total;
	$stats['all']            = $total;

	foreach ($approved as $key) {
		if (empty($stats[$key])) {
			$stats[$key] = 0;
		}
	}

	$stats = (object) $stats;
	wp_cache_set("comments-{$post_id}", $stats, 'counts');

	return $stats;
}

add_filter('wp_count_comments', 'ph_remove_comments_in_comment_counts', 10, 2);

if (!function_exists('ph_insert_comment')) :
	function ph_insert_comment($args = array())
	{
		$args['comment_type'] = 'ph_comment';
		return wp_insert_comment($args);
	}
endif;

/**
 * Only count text comments in comment counts for ProjectHuddle post types
 *
 * @param int $new     The new comment count. Default null.
 * @param int $old     The old comment count.
 * @param int $post_id Post ID.
 * @return null|int
 */
function ph_only_count_text_comments($new, $old, $post_id)
{
	global $wpdb;

	// bail if not our post types
	if (!in_array(get_post_type($post_id), ph_get_all_post_types())) {
		return $new;
	}

	// only do our comment type
	if (is_null($new)) {
		$new = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1' AND comment_type = 'ph_comment'", $post_id));
	} else {
		$new = (int) $new;
	}
	return $new;
}
add_filter('pre_wp_update_comment_count_now', 'ph_only_count_text_comments', 10, 3);

/**
 * Retrieve all notes attached to a purchase
 *
 * @since 1.4
 * @param array $args Comment query args
 * @return array $comments ProjectHuddle comments
 */
function ph_get_comments($args)
{

	remove_action('pre_get_comments', 'ph_hide_ph_comments', 10);

	$comments = get_comments($args);

	add_action('pre_get_comments', 'ph_hide_ph_comments', 10);

	return $comments;
}

/**
 * Count ProjectHuddle comments
 * @param int $post_id
 *
 * @return array|object
 */
function ph_count_comments($post_id = 0, $type = 'comments')
{
	$types = $type == 'all' ? ph_get_comment_types() : [
		'ph_image_comment',
		'ph_website_comment',
		'ph_comment'
	];
	remove_action('pre_get_comments', 'ph_hide_ph_comments', 10);
	$comments_query = array(
		'count' => true,
		'type__in' => apply_filters('ph_count_comments_types', $types, $type),
		'post_id' => $post_id,
	);

	$accessible = apply_filters('ph_check_private_comments_access', false, get_the_ID()); 
	if( ! $accessible ) {
		// Exclude private comments.
		$comments_query['meta_query'][] = array(
			'key'   => 'is_private',
			'compare' => 'NOT EXISTS'
		);
	}

	$comments_count = get_comments( $comments_query );

	add_action('pre_get_comments', 'ph_hide_ph_comments', 10);

	return $comments_count;
}

// add additional html tags for mockup comments
function ph_set_allowed_tags($prepared_comment)
{
	global $allowedtags;

	$allowedtags['br']  = array();
	$allowedtags['p']   = array();
	$allowedtags['ol']  = array();
	$allowedtags['ul']  = array();
	$allowedtags['span']  = array(
		'class' => array(),
		'contenteditable' => array(),
		'data-mention-id' => array()
	);
	$allowedtags['li']  = array();
	$allowedtags['pre'] = array();
	$allowedtags['img'] = array(
		'src' => array(),
		'contenteditable' => array(),
		'draggable' => array()
	);

	return $prepared_comment;
}
add_action('rest_pre_insert_comment', 'ph_set_allowed_tags');
add_action('rest_pre_update_comment', 'ph_set_allowed_tags');

/**
 * Get comment project from comment id
 *
 * @param int $comment_id
 *
 * @return mixed
 */
function ph_get_project_id_from_comment_id($comment_id = 0)
{
	// get meta
	$post_id = get_comment_meta($comment_id, 'project_id', true);

	// fallback
	if (!$post_id) {
		$parents = ph_get_parents_ids($comment_id, 'comment');

		if ($parents['project']) {
			return $parents['project'];
		}
	} else {
		return $post_id;
	}
}

/**
 * Get comment project from comment id
 *
 * @param int $post_id
 *
 * @return mixed
 */
function ph_get_project_id_from_post_id($post_id = 0)
{
	if (in_array(get_post_type($post_id), ph_get_post_types())) {
		return $post_id;
	}

	// get meta
	$project_id = get_post_meta($post_id, 'project_id', true);

	// fallback
	if (!$project_id) {
		$parents = ph_get_parents_ids($post_id);

		if ($parents['project']) {
			return $parents['project'];
		}
	} else {
		return $project_id;
	}
}

/**
 * Remove hash from comment links
 */
function ph_remove_comment_hash($link, $comment)
{
	if (in_array($comment->comment_type, ph_get_comment_types())) {
		$link = strstr($link, '#', true);
	}
	return $link;
}
add_filter('get_comment_link', 'ph_remove_comment_hash', 10, 2);
