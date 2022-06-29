<?php

/**
 * Website Scripts
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       2.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}


/**
 * Admin scripts
 *
 * @param string $hook Page hook called.
 *
 * @return bool
 */
function ph_website_admin_scripts($hook)
{

	// get post type.
	global $post_type, $post;

	// bail out early if we are not on a project add/edit screen.
	if ('ph-website' !== $post_type || ('post.php' !== $hook && 'post-new.php' !== $hook)) {
		return false;
	}

	if (apply_filters('ph_disable_select2', true)) {
		wp_deregister_script('yoast-seo-select2');
		wp_dequeue_script('yoast-seo-select2');
		wp_deregister_style('select2-css');
		wp_deregister_script('select2-js');
		wp_dequeue_style('select2');
		wp_deregister_style('select2');
		wp_dequeue_script('select2');
		wp_deregister_script('select2');
	}

	// store css and javascript directories.
	$css_dir = PH_WEBSITE_PLUGIN_URL . 'assets/css/';
	$js_dir  = PH_WEBSITE_PLUGIN_URL . 'assets/js/';

	$main_css_dir = PH_PLUGIN_URL . 'assets/css/';
	$main_js_dir  = PH_PLUGIN_URL . 'assets/js/';

	// register reusable scripts.
	wp_register_script('ph-select2-admin', $main_js_dir . 'select2-admin.js', array('jquery', 'select2'), PH_VERSION);
	wp_register_script('select2', $main_js_dir . 'includes/select2.full.min.js', array('jquery'), '4.0.2');

	wp_register_style('select2', $main_css_dir . 'includes/select2.min.css', array(), '4.0.2');
	wp_register_style('ph-select2-admin', $main_css_dir . 'project-huddle-select2-admin.css', array('select2'), '4.0.2');

	// enqueue website admin js.
	wp_enqueue_script(
		'ph-website-admin-js',
		$main_js_dir . 'dist/ph-website-comments-admin.js',
		array(
			'underscore',
			'select2',
			'ph-select2-admin',
			'ph.components'
		),
		PH_VERSION
	);

	wp_add_inline_script('ph-website-admin-js', '
		jQuery(document).ready(function() {
			ph.api.start(' . json_encode(PH()->website->rest->get($post->ID)) . ');
		});
	');

	// enqueue main style.
	wp_enqueue_style('ph-website-admin-css', $main_css_dir . 'dist/ph-website-comments-admin.css', array('ph-select2-admin'), PH_VERSION);

	$get_progress_status = get_option('ph_progress_status_enable');
	$get_review_status = get_option('ph_review_status_enable');
	$get_progress_status_text = get_option('ph_progress_status_name', 'In Progress');
	$get_review_status_text = get_option('ph_review_status_name', 'In Review');
	
	$status_name = array(
		'active' => get_option('ph_active_status_name', 'Active'),
		'in_progress' => $get_progress_status !== 'on' ? $get_progress_status_text : 'off',
		'in_review' => $get_review_status !== 'on' ? $get_review_status_text : 'off',
		'resolved' => get_option('ph_resolve_status_name', 'Resolved'),
	);

	$status_colors = array(
		'active' => empty(get_option('ph_active_status_color')) ? get_option('ph_highlight_color', '#4353ff') : get_option('ph_active_status_color', '#4353ff'),
		'in_progress' => $get_progress_status !== 'on' ? get_option('ph_progress_status_color', '#ffc107') : '#ffc107',
		'in_review' => $get_review_status !== 'on' ? get_option('ph_review_status_color', '#ff9800') : '#ff9800',
		'resolved' =>  get_option('ph_resolve_status_color', '#48bb78'),
	);
	
	$comment_status_access_roles = get_option('ph_comment_status_access', false);
	$pc_access_roles = get_option('ph_private_comment_access', false);
	// Add nonce.
	wp_localize_script(
		'ph-website-admin-js',
		'PH',
		apply_filters(
			'ph_website_admin_js_data',
			array(
				'debug'        => defined('SCRIPT_DEBUG') ? SCRIPT_DEBUG : false,
				'translations' => ph_website_translation_strings(),
				'installed' => get_post_meta(get_the_ID(), 'ph_installed', true),
				'comments_access' => apply_filters('ph_private_comments_access', false, get_the_ID()),
				'comment_status_names'   => apply_filters('ph_comment_status_names', $status_name),
				'get_comments_status_access' => get_option('ph_set_comment_status_access', false),
				'comments_status_role_access' => apply_filters('ph_comments_status_role_access', false, get_the_ID()),
				'comments_status_access' => $comment_status_access_roles,
				'comment_status_colors'   => apply_filters('ph_comment_status_custom_colors', $status_colors),
				'private_comment_access' => $pc_access_roles,
				'ph_check_private_comment_access' => apply_filters('ph_check_private_comments_access', false, get_the_ID()),
			),
			get_the_ID()
		)
	);

	wp_localize_script(
		'ph-select2-admin',
		'PH_Select2',
		array(
			'options' => array(
				'anon_users' => false,
			),
		)
	);

	ph_localize_schema('ph-website-admin-js');
}

add_action('admin_enqueue_scripts', 'ph_website_admin_scripts', 30);

function ph_change_version_string_admin($settings)
{
	global $post;
	$settings['versionString'] = 'projecthuddle/v2/';
	return $settings;
}
add_filter('rest_js_client_settings', 'ph_change_version_string_admin');
