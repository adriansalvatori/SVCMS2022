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
 * Checks for template and includes
 * To edit this, simply place single-project.php in your theme or child theme.
 *
 * @since 1.0
 * @return string
 */
function ph_set_template($template)
{
	// Check if the single post type is being viewed and check if template already exists
	if (is_singular('ph-project') && !ph_is_template($template)) {
		$template = PH_PLUGIN_DIR . 'templates/single-project.php';
	}

	if (is_singular('project_image') && !ph_is_template($template)) {
		$template = PH_PLUGIN_DIR . 'templates/single-image.php';
	}

	if (is_singular('ph_comment_location') && !ph_is_template($template)) {
		$template = PH_PLUGIN_DIR . 'templates/single-comment.php';
	}

	return $template;
}

add_filter('template_include', 'ph_set_template', 999999999);

/**
 * Find if template file exists already
 *
 * @since 1.0
 * @return bool
 */
function ph_is_template($template_path)
{

	// Get template name
	$template = basename($template_path);

	// Check if template is single-project.php
	if (1 == preg_match('/^single-project.php/', $template)) {
		return true;
	}

	return false;
}

/**
 * Get template part (for templates like the single project).
 *
 * @access public
 *
 * @param mixed  $slug Template Slug
 * @param string $name (default: '') Template Name
 *
 * @return void
 */
function ph_get_template_part($slug, $name = '')
{

	// set default
	$template = '';

	// Look in yourtheme/project-huddle/slug-name.php
	if ($name) {
		$template = locate_template(array(PH_TEMPLATE_PATH . "{$slug}-{$name}.php"));
	}

	// Get default slug-name.php
	if (!$template && $name && file_exists(PH_PLUGIN_DIR . "/templates/{$slug}-{$name}.php")) {
		$template = PH_PLUGIN_DIR . "/templates/{$slug}-{$name}.php";
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/project-huddle/slug.php
	if (!$template) {
		$template = locate_template(array("{$slug}.php", PH_TEMPLATE_PATH . "{$slug}.php"));
	}

	// Allow 3rd party plugin filter template file from their plugin
	$template = apply_filters('ph_get_template_part', $template, $slug, $name);

	if ($template) {
		load_template($template, false);
	}
}

/**
 * Get other templates passing attributes and including the file.
 *
 * @access public
 *
 * @param string $template_name Template Name
 * @param array  $args          (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path  (default: '')
 *
 * @return void
 */
function ph_get_template($template_name, $args = array(), $template_path = '', $default_path = '')
{

	// extract args into regular variables
	if ($args && is_array($args)) {
		extract($args);
	}

	// locate template
	$located = ph_locate_template($template_name, $template_path, $default_path);

	// if we can't locate the file
	if (!file_exists($located)) {
		_doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $located), '1.0');

		return;
	}

	// Allow 3rd party plugin filter template file from their plugin
	$located = apply_filters('ph_get_template', $located, $template_name, $args, $template_path, $default_path);

	// add before including template part action
	do_action('ph_before_template_part', $template_name, $template_path, $located, $args);

	// include template
	include $located;

	// add after including template part action
	do_action('ph_after_template_part', $template_name, $template_path, $located, $args);
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *        yourtheme        /    $template_path    /    $template_name
 *        yourtheme        /    $template_name
 *        $default_path    /    $template_name
 *
 * @access public
 *
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path  (default: '')
 *
 * @return string
 */
function ph_locate_template($template_name, $template_path = '', $default_path = '')
{

	// set default template path if it doesn't exist
	if (!$template_path) {
		$template_path = PH()->template_path();
	}

	// use default plugin path if custom path isn't set
	if (!$default_path) {
		$default_path = PH_PLUGIN_DIR . 'templates/';
	}

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit($template_path) . $template_name,
			$template_name,
		)
	);

	// Get default template if no theme template
	if (!$template) {
		$template = $default_path . $template_name;
	}

	// Return what we found
	return apply_filters('ph_locate_template', $template, $template_name, $template_path);
}

function ph_get_the_password_form($post = 0)
{
	$post   = get_post($post);
	$label  = 'pwbox-' . (empty($post->ID) ? rand() : $post->ID);
	$url =  "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

	ob_start(); ?>

	<form action="<?php echo esc_url_raw(site_url('wp-login.php?action=postpass', 'login_post') . '&redirect=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8')); ?>" class="post-password-form" method="post">
		<p class="ph-flex ph-items-stretch ph-justify-center">
			<label for="<?php echo esc_attr($label); ?>" class="ph-sr-only"><?php _e('Password:', 'project-huddle'); ?></label>
			<input class="ph-appearance-none ph-bg-white ph-border-none ph-rounded ph-p-4 focus:ph-shadow-outline" placeholder="<?php echo esc_attr(__('Password', 'project-huddle')); ?>" name="post_password" id="<?php echo esc_attr($label); ?>" type="password" size="30" />
			<input class="ph-appearance-none ph-ml-3 ph-bg-primary ph-p-4 ph-text-white ph-text-xs ph-uppercase ph-tracking-widest ph-font-bold ph-rounded" type="submit" name="Submit" value="<?php echo esc_attr_x('Enter', 'post password form'); ?>" />
		</p>
	</form>

<?php
	/**
	 * Filters the HTML output for the protected post password form.
	 *
	 * If modifying the password field, please note that the core database schema
	 * limits the password field to 20 characters regardless of the value of the
	 * size attribute in the form input.
	 *
	 * @since 2.7.0
	 *
	 * @param string $output The password form HTML output.
	 */
	return apply_filters('the_password_form', ob_get_clean());
}
