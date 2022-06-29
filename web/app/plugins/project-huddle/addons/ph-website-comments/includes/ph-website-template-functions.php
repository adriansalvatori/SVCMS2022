<?php

/**
 * Template Functions
 *
 * Functions for the templating system.
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Reads css file and appends custom css
 * 
 *
 * @param string $file Directory of CSS file
 * @param string $hook Action to fire to add additional css.
 * @return void
 */
function ph_website_script($file, $hooks)
{
	$css = file_exists($file) ? file_get_contents($file) : '';
	ob_start();
	$hooks = (array) $hooks;
	foreach ($hooks as $hook) {
		do_action($hook);
	}
	// hook for all website css
	do_action('ph_website_css');
	// add customizer css
	echo apply_filters('ph_include_customizer_css', true) ? wp_get_custom_css() : '';
	$custom = ob_get_clean();
	$css = $css . $custom;
	return $css;
}

/**
 * Checks for template and includes
 * To edit this, simply place single-project.php in your theme or child theme.
 *
 * @since 1.2.0
 * @return string
 */
function ph_website_set_template($template)
{
	global $wp;
	// Check if the single post type is being viewed and check if template already exists
	if (is_singular('ph-website') && !ph_is_template($template)) {
		if (isset($wp->query_vars['ph_apikey']) && $wp->query_vars['ph_apikey']) {

			// maybe auto login
			global $post;
			$email     = isset($_GET['ph_user_email']) ? $_GET['ph_user_email'] : '';
			$signature = isset($_GET['ph_signature']) ? $_GET['ph_signature'] : '';
			ph_auto_login($post->ID, $signature, $email);

			$template = PH_WEBSITE_PLUGIN_DIR . 'templates/single-website-script.php';
		} else {
			if (isset($_GET['ph_query_test']) && $_GET['ph_query_test']) {
				$template = PH_WEBSITE_PLUGIN_DIR . 'templates/ph-website-iframe.php';
			} else {
				$template = PH_WEBSITE_PLUGIN_DIR . 'templates/single-website.php';
			}
		}
	}

	if (isset($wp->query_vars['ph_screenshot']) && $wp->query_vars['ph_screenshot']) {
		$template = PH_WEBSITE_PLUGIN_DIR . 'templates/website-screenshot-proxy.php';
	}

	if (is_singular('phw_comment_loc') && !ph_is_template($template)) {
		$template = PH_WEBSITE_PLUGIN_DIR . 'templates/single-comment-thread.php';
	}

	if (is_singular('ph-webpage') && !ph_is_template($template)) {
		$template = PH_WEBSITE_PLUGIN_DIR . 'templates/single-website-page.php';
	}

	if (isset($wp->query_vars['ph_safari_cookie']) && $wp->query_vars['ph_safari_cookie']) {
		$template = PH_WEBSITE_PLUGIN_DIR . 'templates/safari-fix.php';
	}

	// if ($template) {
	// 	include $template;
	// 	exit;
	// }

	if (isset($_GET['ph_query_test']) && $_GET['ph_query_test']) {
		$template = PH_WEBSITE_PLUGIN_DIR . 'templates/ph-website-iframe.php';
	}

	if ($template) {
		include $template;
		exit;
	}
}

add_filter('template_redirect', 'ph_website_set_template');

/**
 * Output website style options
 */
function ph_website_output_style_options()
{
	echo ph_website_style_options();
}
add_action('ph_website_thread_css', 'ph_website_output_style_options', 20);
