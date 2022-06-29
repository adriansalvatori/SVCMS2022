<?php

/**
 * Functions for images
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Removes "Protected" and "Private" from the title
 *
 * @since 1.0
 */
function ph_get_the_title($id = 0)
{
	global $post;

	$findthese   = array(
		'#Protected:#',
		'#Private:#'
	);
	$replacewith = array(
		'', // What to replace "Protected:" with nothing
		'' // What to replace "Private:" with nothing
	);

	if (!$id && is_a($post, 'WP_Post')) {
		$id = $post->ID;
	}

	$title = get_the_title($id);
	$title = preg_replace($findthese, $replacewith, $title);

	// TODO: sanitation
	return sanitize_text_field(html_entity_decode(apply_filters('ph_get_the_title', $title)));
}

/**
 * Convenience function to echo the title
 *
 * @since 1.0
 */
function ph_the_title()
{
	echo ph_get_the_title();
}

/**
 * Hides admin bar on project page in place of our own UI.
 *
 * @return bool
 */
function ph_hide_admin_bar($bool)
{
	if (is_singular('ph-project')) {
		return false;
	}

	return $bool;
}
add_filter('show_admin_bar', 'ph_hide_admin_bar');

/**
 * Creates unique hash id for a specific post id
 *
 * Makes un-guessable unique urls possible.
 *
 * @param int $post_id ID of the post
 * @param int $len     Length of the hash
 *
 * @return string Unique hash for the post
 */
function ph_gen_uuid($post_id, $len = 8)
{

	$hex = md5($post_id . uniqid("", true));

	$pack = pack('H*', $hex);
	$tmp  = base64_encode($pack);

	$uid = preg_replace("#(*UTF8)[^A-Za-z0-9]#", "", $tmp);

	$len = max(4, min(128, $len));

	while (strlen($uid) < $len) {
		$uid .= ph_gen_uuid(22);
	}

	return substr($uid, 0, $len);
}

/**
 * Redirect ProjectHuddle attachment pages so they are not accessible
 */
function ph_attachment_redirect()
{
	global $post;

	if (is_attachment() && isset($post->post_parent) && is_numeric($post->post_parent) && ($post->post_parent != 0)) {
		// if attachment parent isn't one of our post types
		if (!in_array(get_post_type($post->post_parent), ph_get_child_post_types()) && !in_array(get_post_type($post->post_parent), ph_get_post_types())) {
			return;
		}

		$parent_post_in_trash = get_post_status($post->post_parent) === 'trash' ? true : false;

		if ($parent_post_in_trash) {
			wp_die(__('Page not found.', 'project-huddle'), __('404 - Page not found', 'project-huddle'), 404); // Prevent endless redirection loop in old WP releases and redirecting to trashed posts if an attachment page is visited when parent post is in trash
		}

		wp_safe_redirect(get_permalink($post->post_parent), apply_filters('ph_attachment_redirect_code', '301')); // Redirect to post/page from where attachment was uploaded
		exit;
	} elseif (is_attachment() && isset($post->post_parent) && is_numeric($post->post_parent) && ($post->post_parent < 1)) {
		wp_safe_redirect(get_bloginfo('wpurl'), apply_filters('ph_attachment_home_redirect_code', '302')); // Redirect to home for attachments not associated to any post/page
		exit;
	}
}

add_action('template_redirect', 'ph_attachment_redirect', 1);
