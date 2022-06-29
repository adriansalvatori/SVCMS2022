<?php

/**
 * Generic item resolve functions
 */

/**
 * Gets resolve status of item threads
 *
 * @param integer $id Id of item
 * @param string $post_type Post type of item
 * @param string $thread_type Post type of thread
 */
function ph_get_item_resolve_status($id = 0, $thread_type = 'ph_comment_location')
{
	$defaults = array(
		'total'    => 0,
		'approved' => 0,
	);

	if (!$id) {
		global $post;
		$id = isset($post->ID) ? $post->ID : false;
	}

	if (!$id) {
		return $defaults;
	}

	$parent_type = get_post_type($id);

	$resolved_status = get_transient("ph_resolved_status_" . $id);

	// this code runs when there is no valid transient set.
	if (false === $resolved_status) {
		// get threads
		$threads = new WP_Query(
			array(
				'post_type'      => $thread_type,
				'posts_per_page' => -1,
				'meta_value'     => $id,
				'meta_key'       => 'parent_id',
				'fields'         => 'ids',
			)
		);

		$thread_ids        = $threads->posts;
		$approval_comments = array();
		$resolved          = 0;

		if (!empty($thread_ids)) {
			// count approved
			foreach ($thread_ids as $thread_id) {
				// if resolved
				if (get_post_meta($thread_id, 'resolved', true)) {
					$resolved++;
				}
			}

			// get last approval comment
			$approval_comments = ph_get_comments(
				array(
					'type'     => ph_approval_term_taxonomy(),
					'post__in' => $thread_ids,
					'number'   => 1,
				)
			);
		}

		$by = false;
		$on = false;

		if (!empty($approval_comments) && is_array($approval_comments)) {
			$comment = $approval_comments[0];

			if (is_a($comment, 'WP_Comment')) {
				$by = $comment->user_id ? get_userdata($comment->user_id)->display_name : $comment->comment_author;
				$on = $comment->comment_date;
			}
		}

		$resolved_status = array(
			'total'    => count($thread_ids),
			'resolved' => $resolved,
			'by'       => $by,
			'on'       => $on,
		);

		set_transient("ph_resolved_status_" . $id, $resolved_status, 30 * DAY_IN_SECONDS); // expires in 1 month
	}

	return wp_parse_args($resolved_status, $defaults);
}

/**
 * Gets resolve status of item threads
 *
 * @param integaer $id Id of item
 * @param string $post_type Post type of item
 * @param string $thread_type Post type of thread
 */
function ph_get_project_resolve_status($id = 0, $thread_type = 'ph_comment_location')
{
	$defaults = array(
		'total'    => 0,
		'resolved' => 0,
	);

	if (!$id) {
		global $post;
		$id = isset($post->ID) ? $post->ID : false;
	}

	if (!$id) {
		return $defaults;
	}

	$post_type = get_post_type($id);

	$resolved_status = get_transient("ph_resolved_status_" . $id);

	// this code runs when there is no valid transient set.
	if (false === $resolved_status) {
		// get threads
		$threads = new WP_Query(
			array(
				'post_type'      => $thread_type,
				'posts_per_page' => -1,
				'meta_value'     => $id,
				'meta_key'       => 'project_id',
				'fields'         => 'ids',
			)
		);

		$thread_ids        = $threads->posts;
		$approval_comments = array();
		$resolved          = 0;

		if (!empty($thread_ids)) {
			// count approved
			foreach ($thread_ids as $thread_id) {
				// if resolved
				if (get_post_meta($thread_id, 'resolved', true)) {
					$resolved++;
				}
			}

			// get last approval comment
			$approval_comments = ph_get_comments(
				array(
					'type'     => ph_approval_term_taxonomy(),
					'post__in' => $thread_ids,
					'number'   => 1,
				)
			);
		}

		$by = false;
		$on = false;

		if (!empty($approval_comments) && is_array($approval_comments)) {
			$comment = $approval_comments[0];

			if (is_a($comment, 'WP_Comment')) {
				$by = $comment->user_id ? get_userdata($comment->user_id)->display_name : $comment->comment_author;
				$on = $comment->comment_date;
			}
		}

		$resolved_status = array(
			'total'    => count($thread_ids),
			'resolved' => $resolved,
			'by'       => $by,
			'on'       => $on,
		);

		set_transient("ph_resolved_status_" . $id, $resolved_status, 30 * DAY_IN_SECONDS); // expires in 1 month
	}

	return wp_parse_args($resolved_status, $defaults);
}

/**
 * Clear approval transient when an approval comment is stored
 *
 * @param integer $comment_id Comment ID
 * @param WP_Comment $comment_object WP Comment Object
 */
function ph_clear_item_resolve_transients($comment_id, $comment_object)
{
	$parents_ids = ph_get_parents_ids($comment_object, 'comment');

	if (!$parents_ids['item']) {
		return;
	}

	// clear item transients
	delete_transient("ph_resolved_status_" . $parents_ids['item']);
	delete_transient("ph_resolved_status_" . $parents_ids['project']);
}

add_action('wp_insert_comment', 'ph_clear_item_resolve_transients', 10, 2);

function ph_thread_clear_item_resolve_transients($id)
{
	$parents_ids = ph_get_parents_ids($id);

	if (!$parents_ids['item']) {
		return;
	}

	// clear item transients
	delete_transient("ph_resolved_status_" . $parents_ids['item']);
	delete_transient("ph_resolved_status_" . $parents_ids['project']);
}
add_action('ph_mockup_delete_thread', 'ph_thread_clear_item_resolve_transients', 10, 2);
add_action('ph_website_delete_thread', 'ph_thread_clear_item_resolve_transients', 10, 2);

function ph_store_item_comments_batch_approval_info($item_id, $value)
{
	// get current user
	$user = wp_get_current_user();
	$term = $value ? __('resolved', 'project-huddle') : __('unresolved', 'project-huddle');

	// if we did a project vs item
	$text = sprintf(__('%1$s marked all conversations in %2$s as %3$s.', 'project-huddle'), $user->display_name, ph_get_the_title($item_id), strtolower($term));

	// Insert new comment and get the comment ID
	wp_insert_comment(
		array(
			'comment_post_ID'      => (int) $item_id,
			'comment_author'       => $user->display_name,
			'comment_author_email' => $user->user_email,
			'user_id'              => $user->ID,
			'comment_content'      => wp_kses_post($text),
			'comment_type'         => ph_approval_term_taxonomy(),
			'comment_approved'     => 1, // force approval
			'comment_meta'         => array(
				'approval' => (bool) $value,
			),
		)
	);
}
add_action('ph_rest_item_comments_resolved', 'ph_store_item_comments_batch_approval_info', 10, 3);
