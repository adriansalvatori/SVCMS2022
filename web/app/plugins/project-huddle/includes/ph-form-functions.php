<?php

/**
 * Cache Functions
 *
 * Functions for disabling cache and minification
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Change the password form html output
 *
 * @param $output string html output for form
 *
 * @return string New HTML output for form
 * @since 1.0
 */
function ph_password_form($output)
{
	global $post;

	// only apply it on our post type
	if (!is_singular('ph-project')) {
		return $output;
	}

	$url =  "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

	$label = 'pwbox-' . (empty($post->ID) ? rand() : $post->ID);
	$o     = '<form class="protected-post-form" action="' . esc_url(site_url('wp-login.php?action=postpass', 'login_post')) . '&redirect=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" method="post">
	<input name="post_password" id="' . $label . '" type="password" placeholder="' . __('Password', 'project-huddle') . '" size="20" />
	<input type="submit" name="Submit" value="' . esc_attr__("Submit") . '" />
	</form>
	';

	return $o;
}

// add_filter( 'the_password_form', 'ph_password_form' );
