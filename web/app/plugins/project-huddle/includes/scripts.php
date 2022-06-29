<?php

use PH\Models\User;
use PH\Models\Mockup;

/**
 * Scripts
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}
function ph_enqueue_mockup_script($handle, $url, $deps = [], $ver = '', $in_footer = true)
{
	add_action('wp_enqueue_scripts', function () use ($handle, $url, $deps, $ver, $in_footer) {
		if (!is_singular('ph-project')) return;
		array_push($deps, 'project-huddle');
		wp_enqueue_script($handle, $url, $deps, $ver, $in_footer);
	});

	// add to mockups
	add_filter('ph_allowed_scripts', function ($scripts) use ($handle) {
		$scripts[] = $handle;
		return $scripts;
	});
}

function ph_enqueue_website_script($handle, $url, $deps = [], $ver = '', $in_footer = true)
{
	add_action('wp_enqueue_scripts', function () use ($handle, $url, $deps, $ver, $in_footer) {
		if (!is_singular('ph-website')) return;
		array_push($deps, 'ph-website-comments');
		wp_enqueue_script($handle, $url, array_unique($deps), $ver, $in_footer);
	});

	// add to websites
	add_filter('ph_allowed_website_scripts', function ($scripts) use ($handle) {
		$scripts[] = $handle;
		return $scripts;
	});
}

/**
 * Register scripts we can use throughout the plugin
 */
function ph_global_scripts()
{
	global $post_type;

	$js_dir  = PH_PLUGIN_URL . 'assets/js/';
	$css_dir = PH_PLUGIN_URL . 'assets/css/';

	$get_progress_status = get_option('ph_progress_status_enable');
	$get_review_status = get_option('ph_review_status_enable');
	$get_progress_status_text = !empty(get_option('ph_progress_status_name')) ? get_option('ph_progress_status_name', 'In Progress') : __('In Progress', 'project-huddle');
	$get_review_status_text = !empty(get_option('ph_review_status_name')) ? get_option('ph_review_status_name', 'In Review') : __('In Review', 'project-huddle');
	
	$status_name = array(
		'active' => empty(get_option('ph_active_status_name')) ? esc_attr('Active', 'project-huddle') : get_option('ph_active_status_name', 'Active'),
		'in_progress' => $get_progress_status !== 'on' ? $get_progress_status_text : 'off',
		'in_review' => $get_review_status !== 'on' ? $get_review_status_text : 'off',
		'resolved' => empty(get_option('ph_resolve_status_name')) ? esc_attr('Resolved', 'project-huddle') : get_option('ph_resolve_status_name', 'Resolved'),
	);

	$status_colors = array(
		'active' => empty(get_option('ph_active_status_color')) ? get_option('ph_highlight_color', '#4353ff') : get_option('ph_active_status_color', '#4353ff'),
		'in_progress' => $get_progress_status !== 'on' ? get_option('ph_progress_status_color', '#ffc107') : '#ffc107',
		'in_review' => $get_review_status !== 'on' ? get_option('ph_review_status_color', '#ff9800') : '#ff9800',
		'resolved' =>  get_option('ph_resolve_status_color','#48bb78'),
	);

	$pc_access_roles = get_option('ph_private_comment_access', false);
	wp_register_script('ph.components', $js_dir . 'dist/ph-components.js', ['underscore'], PH_VERSION);

	$comment_status_access_roles = get_option('ph_comment_status_access', false);
	wp_localize_script(
		'ph.components',
		'PH_Settings',
		array(
			'comments_access'  => apply_filters('ph_private_comments_access', false, get_the_ID()),
			'comment_status_names'   => apply_filters('ph_comment_status_names', $status_name),
			'comment_status_colors'   => apply_filters('ph_comment_status_custom_colors', $status_colors),
			'get_comments_status_access' => get_option('ph_set_comment_status_access', false),
			'comments_status_role_access' => apply_filters('ph_comments_status_role_access', false, get_the_ID()),
			'comments_status_access' => $comment_status_access_roles,
			'custom_pin_color' 		 => get_option('ph_pin_color_enable', false),
			'private_comment_access' => $pc_access_roles, 
			'ph_check_private_comment_access' => apply_filters('ph_check_private_comments_access', false, get_the_ID())
		)
	);

	if (!in_array($post_type, ph_get_post_types())) {
		return false;
	}

	// global register scripts
	wp_register_script('ph-select2-admin', $js_dir . 'select2-admin.js', array('jquery', 'select2'), PH_VERSION);
	wp_register_script('select2', $js_dir . 'includes/select2.full.min.js', array('jquery'), '4.0.2');
	wp_register_script('autosize', $js_dir . 'includes/autosize.min.js', array(), '3.0.2');
	wp_register_script('jquery-placecursoratend', $js_dir . 'includes/jquery.placecursoratend.js', array('jquery'), '1.0', true);
	wp_register_style('select2', $css_dir . 'includes/select2.min.css', array(), '4.0.2');
	wp_register_style('ph-select2-admin', $css_dir . 'project-huddle-select2-admin.css', array('select2'), '4.0.2');

	wp_localize_script(
		'ph-select2-admin',
		'phSelect2',
		array(
			'defaultAvatar' => get_option('avatar_default'),
		)
	);
}
add_action('wp_enqueue_scripts', 'ph_global_scripts', 999999999);
add_action('admin_enqueue_scripts', 'ph_global_scripts', 9999999);

/**
 * Un-enqueue theme and other plugin styles and loads our plugin styles.
 *
 * Included style handles can be passed via a hook.
 *
 * @since 1.0
 *
 * @return void
 */
function ph_load_styles()
{
	global $wp_styles;

	// store css directory
	$css_dir = PH_PLUGIN_URL . 'assets/css/';

	// return for other pages
	if (!in_array(get_post_type(), ph_get_all_post_types()) && !get_query_var('ph_user_settings')) {
		return;
	}

	if (!get_option('ph_script_shielding')) {
		// allowed stylesheets on page
		$allowed['styles_allowed'] = apply_filters('ph_allowed_styles', array());

		// un-enqueue all
		foreach ($wp_styles->queue as $handle) :
			if (!in_array($handle, $allowed['styles_allowed'])) {
				wp_dequeue_style($handle);
			}
		endforeach;
	}

	wp_enqueue_style('project-huddle', $css_dir . 'dist/project-huddle.css', array(), PH_VERSION);
	wp_add_inline_style('project-huddle', apply_filters('ph_inline_styles', ph_style_options()));
}

add_action('wp_enqueue_scripts', 'ph_load_styles', 99999999);

/**
 * Adds styles and scripts needed on the page
 *
 * @since 1.0
 */
function ph_mockup_admin_scripts($hook)
{
	// get post type
	global $post_type;

	// store css and javascript directories
	$css_dir = PH_PLUGIN_URL . 'assets/css/';
	$js_dir  = PH_PLUGIN_URL . 'assets/js/';

	$get_progress_status = get_option('ph_progress_status_enable');
	$get_review_status = get_option('ph_review_status_enable');
	$get_progress_status_text = !empty(get_option('ph_progress_status_name')) ? get_option('ph_progress_status_name', 'In Progress') : __('In Progress', 'project-huddle');
	$get_review_status_text = !empty(get_option('ph_review_status_name')) ? get_option('ph_review_status_name', 'In Review') : __('In Review', 'project-huddle');
	
	$status_name = array(
		'active' => empty(get_option('ph_active_status_name')) ? esc_attr('Active', 'project-huddle') : get_option('ph_active_status_name', 'Active'),
		'in_progress' => $get_progress_status !== 'on' ? $get_progress_status_text : 'off',
		'in_review' => $get_review_status !== 'on' ? $get_review_status_text : 'off',
		'resolved' => empty(get_option('ph_resolve_status_name')) ? esc_attr('Resolved', 'project-huddle') : get_option('ph_resolve_status_name', 'Resolved'),
	);

	$status_colors = array(
		'active' => empty(get_option('ph_active_status_color')) ? get_option('ph_highlight_color', '#4353ff') : get_option('ph_active_status_color', '#4353ff'),
		'in_progress' => $get_progress_status !== 'on' ? get_option('ph_progress_status_color', '#ffc107') : '#ffc107',
		'in_review' => $get_review_status !== 'on' ? get_option('ph_review_status_color', '#ff9800') : '#ff9800',
		'resolved' =>  empty(get_option('ph_active_status_color')) ? '#48bb78' : get_option('ph_resolve_status_color', '#48bb78'),
	);

	if (apply_filters('ph_disable_select2', true)) {
		if ($post_type == 'ph-project' || $post_type == 'ph-website') {
			wp_deregister_script('yoast-seo-select2');
			wp_dequeue_script('yoast-seo-select2');
			wp_deregister_style('select2-css');
			wp_deregister_script('select2-js');
			wp_dequeue_style('select2');
			wp_deregister_style('select2');
			wp_dequeue_script('select2');
			wp_deregister_script('select2');
		}
	}

	// bail out early if we are not on a project add/edit screen.
	if ('ph-project' != $post_type && 'ph-website' != $post_type) {
		return false;
	}

	// register reusable scripts
	wp_register_script('ph-select2-admin', $js_dir . 'select2-admin.js', array('jquery', 'select2'), PH_VERSION);
	wp_register_script('select2', $js_dir . 'includes/select2.full.min.js', array('jquery'), '4.0.2');
	wp_register_script('autosize', $js_dir . 'includes/autosize.min.js', array(), '3.0.2');
	wp_register_script('clipboard.js', $js_dir . 'includes/clipboard.js', array(), '1.7.1', false);

	wp_register_style('select2', $css_dir . 'includes/select2.min.css', array(), '4.0.2');
	wp_register_style('ph-select2-admin', $css_dir . 'project-huddle-select2-admin.css', array('select2'), '4.0.2');

	// enqueue media controls
	wp_enqueue_media();

	// enqueue main style
	wp_enqueue_style('project-huddle-admin', $css_dir . 'dist/project-huddle-admin.css', array('ph-select2-admin'), PH_VERSION);

	if ('ph-project' != $post_type) {
		return;
	}

	// color picker
	wp_enqueue_style('wp-color-picker');
	// A style available in WP
	wp_enqueue_style('wp-jquery-ui-dialog');

	// enqueue needed admin script
	if ('ph-project' != $post_type) {
		return false;
	}

	$js_dist = $js_dir . 'dist';
	if (defined('PH_HMR') && PH_HMR) {
		$js_dist = 'https://127.0.0.1:8081/assets/js/dist';
	}

	wp_enqueue_script(
		'project-huddle-admin-js',
		$js_dist . '/project-huddle.admin.js',
		array(
			'underscore',
			'jquery-ui-sortable',
			'ph-select2-admin',
			'ph.components'
		),
		PH_VERSION
	);

	if (function_exists('wp_set_script_translations')) {
		wp_set_script_translations('project-huddle-admin-js', 'project-huddle');
	}
	
	$comment_status_access_roles = get_option('ph_comment_status_access', false);
	$pc_access_roles = get_option('ph_private_comment_access', false);
	// Add nonce
	wp_localize_script(
		'project-huddle-admin-js',
		'PH',
		array(
			'nonce'          => wp_create_nonce('project-image-nonce'),
			'site_url'       => get_site_url(),
			'image_defaults' => apply_filters(
				'ph_mockup_image_defaults',
				array(
					'alignment'                 => 'center', // left, right or center
					'size'                      => 'scale', // normal or scale
					'background_color'          => '#15181C', // hex value
					'background_image'          => '', // url
					'background_image_position' => 'center', // repeat, repeat-x, cover
				)
			),
			'translations'   => ph_mockup_translation_strings(),
			'comments_access'        => apply_filters('ph_private_comments_access', false, get_the_ID()),
			'comments_status_access' => $comment_status_access_roles,
			'comment_status_names'   => apply_filters('ph_comment_status_names', $status_name),
			'comment_status_colors'   => apply_filters('ph_comment_status_custom_colors', $status_colors),
			'get_comments_status_access' => get_option('ph_set_comment_status_access', false),
			'comments_status_role_access' => apply_filters('ph_comments_status_role_access', false, get_the_ID()),
			'comments_status_access' => $comment_status_access_roles,
			'custom_pin_color' 		 => get_option('ph_pin_color_enable', false),
			'private_comment_access' => $pc_access_roles,
			'ph_check_private_comment_access' => apply_filters('ph_check_private_comments_access', false, get_the_ID()),
		)
	);

	wp_localize_script(
		'ph-select2-admin',
		'PH_Select2',
		array(
			'options' => array(
				'anon_users' => true,
			),
		)
	);

	wp_localize_script(
		'ph-select2-admin',
		'wpApiSettings',
		array(
			'root'          => esc_url_raw(get_rest_url()),
			'nonce'         => wp_create_nonce('wp_rest'),
			'ajaxurl' 		=> admin_url('admin-ajax.php'),
			'versionString' => 'projecthuddle/v2/',
		)
	);

	ph_localize_schema('project-huddle-admin-js');


	// /**
	//  * @var WP_REST_Server $wp_rest_server
	//  */
	// global $wp_rest_server;

	// // Ensure the rest server is intiialized.
	// if ( empty( $wp_rest_server ) ) {
	// 	/** This filter is documented in wp-includes/rest-api.php */
	// 	$wp_rest_server_class = apply_filters( 'wp_rest_server_class', 'WP_REST_Server' );
	// 	$wp_rest_server       = new $wp_rest_server_class();
	// 	/** This filter is documented in wp-includes/rest-api.php */
	// 	do_action( 'rest_api_init', $wp_rest_server );
	// }

	// // Load the schema.
	// $schema_request  = new WP_REST_Request( 'GET', '/projecthuddle/v2' );
	// $schema_response = $wp_rest_server->dispatch( $schema_request );
	// $schema          = null;
	// if ( ! $schema_response->is_error() ) {
	// 	$schema = $schema_response->get_data();
	// }

	// // Localize the plugin settings and schema.
	// $settings = array(
	// 	'root'          => esc_url_raw( get_rest_url() ),
	// 	'nonce'         => wp_create_nonce( 'wp_rest' ),
	// 	'versionString' => 'projecthuddle/v2/',
	// 	'schema'        => $schema,
	// 	'cacheSchema'   => true,
	// );

	// /**
	//  * Filter the JavaScript Client settings before localizing.
	//  *
	//  * Enables modifying the config values sent to the JS client.
	//  *
	//  * @param array  $settings The JS Client settings.
	//  */
	// $settings = apply_filters( 'rest_js_client_settings', $settings );
	// wp_localize_script( 'project-huddle-admin-js', 'wpApiSettings', $settings );

}
add_action('admin_enqueue_scripts', 'ph_mockup_admin_scripts', 99);

// add_action('admin_enqueue_scripts', 'ph_dashboard_scripts');
// function ph_dashboard_scripts($hook)
// {
// 	// dashboard admin
// 	if ($hook != 'toplevel_page_project-huddle') {
// 		return;
// 	}
// 	wp_enqueue_style('ph-dashboard', PH_PLUGIN_URL . 'assets/css/dist/ph-dashboard.css', array(), PH_VERSION);
// }

/**
 * Un-enqueue theme and other plugin scripts and loads our plugin scripts
 *
 * @since 1.0
 *
 * @return void
 */
function ph_load_scripts()
{
	global $wp_scripts;

	// store javascript directory
	$js_dir = PH_PLUGIN_URL . 'assets/js/';
	$js_dist = PH_PLUGIN_URL . 'assets/js/dist';

	if (defined('PH_HMR') && PH_HMR) {
		$js_dist = 'https://127.0.0.1:8081/assets/js/dist';
	}

	// global register scripts
	wp_register_script('autosize', $js_dir . 'includes/autosize.min.js', array(), '3.0.2', true);
	wp_register_script('js.cookie', $js_dir . 'includes/js.cookie.js', array(), '2.1.4');

	// return for other pages
	if (!is_singular('ph-project')) {
		return;
	}

	if (!get_option('ph_script_shielding')) {
		$allowed = apply_filters('ph_allowed_scripts', array());

		foreach ($wp_scripts->queue as $handle) :
			if (!in_array($handle, $allowed)) {
				wp_dequeue_script($handle);
			}
		endforeach;
	}

	// enqueue main collaborate script
	wp_enqueue_script('project-huddle', $js_dist . '/project-huddle.js', array('jquery', 'underscore', 'ph.components', 'jquery-ui-sortable'), PH_VERSION);

	if (function_exists('wp_set_script_translations')) {
		wp_set_script_translations('project-huddle', 'project-huddle');
	}

	$comment_scroll = PH()->session->get('ph_comment_id');
	$logo       = apply_filters('ph_mockup_control_logo_id', get_option('ph_control_logo'));

	$logo_image = ['', '', ''];
	if ($logo) {
		$logo_image = wp_get_attachment_image_src($logo, 'full');

		// check retina option
		if (apply_filters('ph_website_control_logo_retina', get_option('ph_control_logo_retina'))) :
			$logo_image[1] = $logo_image[1] / 2;
			$logo_image[2] = $logo_image[2] / 2;
		endif;
	}

	$can_access_mockup = ( is_user_logged_in() && current_user_can( 'manage_options' ) ? true : false );

	$get_progress_status = get_option('ph_progress_status_enable');
	$get_review_status = get_option('ph_review_status_enable');
	$get_progress_status_text = !empty(get_option('ph_progress_status_name')) ? get_option('ph_progress_status_name', 'In Progress') : __('In Progress', 'project-huddle');
	$get_review_status_text = !empty(get_option('ph_review_status_name')) ? get_option('ph_review_status_name', 'In Review') : __('In Review', 'project-huddle');
	
	$status_name = array(
		'active' => empty(get_option('ph_active_status_name')) ? esc_attr('Active', 'project-huddle') : get_option('ph_active_status_name', 'Active'),
		'in_progress' => $get_progress_status !== 'on' ? $get_progress_status_text : 'off',
		'in_review' => $get_review_status !== 'on' ? $get_review_status_text : 'off',
		'resolved' => empty(get_option('ph_resolve_status_name')) ? esc_attr('Resolved', 'project-huddle') : get_option('ph_resolve_status_name', 'Resolved'),
	);

	$status_colors = array(
		'active' => empty(get_option('ph_active_status_color')) ? get_option('ph_highlight_color', '#4353ff') : get_option('ph_active_status_color', '#4353ff'),
		'in_progress' => $get_progress_status !== 'on' ? get_option('ph_progress_status_color', '#ffc107') : '#ffc107',
		'in_review' => $get_review_status !== 'on' ? get_option('ph_review_status_color', '#ff9800') : '#ff9800',
		'resolved' => get_option('ph_resolve_status_color', '#48bb78'),
	);
	
	$comment_status_access_roles = get_option('ph_comment_status_access', false);
	$pc_access_roles = get_option('ph_private_comment_access', false);
	// localize our origin
	wp_localize_script(
		'project-huddle',
		'PH_Settings',
		array(
			'plugin_dir' => PH_PLUGIN_DIR,
			'debug'                  => defined('SCRIPT_DEBUG') ? SCRIPT_DEBUG : false,
			'translations'           => ph_mockup_translation_strings(),
			'edit_link'              => esc_url(admin_url(sprintf(get_post_type_object('ph-website')->_edit_link . '&action=edit', get_the_ID()))),
			'logo'                   => array(
				'url'    => esc_url($logo_image[0]),
				'width'  => (float) $logo_image[1],
				'height' => (float) $logo_image[2],
			),
			'comments_per_page'      => apply_filters('ph_comments_per_page', 25),
			'default_thread_members' => ph_default_project_members(get_the_ID()),
			'options'                => apply_filters(
				'ph_mockup_global_settings',
				array(
					'auto_close'                => get_option('ph_auto_close', 'on') == 'on' ? true : false,
					'ph_image_background_color' => get_option('ph_image_bg', false),
					'require_terms'             => get_option('ph_require_terms', false),
					'approval_type'             => get_option('ph_approval_type', false),
				)
			),
			'approval' => [
				'require_terms' => (bool) get_option('ph_require_terms', false),
				'checkbox_text' => sanitize_text_field(get_option('ph_approve_terms_checkbox_text', sprintf(__('I, %1s, read and agree with the %2s.', 'project-huddle'), '{{user_name}}', '{{terms}}'))),
				'link_text' =>  sanitize_text_field(get_option('ph_approve_terms_link_text', __('Terms', 'project-huddle'))),
				'terms' => wp_kses_post(wpautop(get_option('ph_approve_terms', ''))),
				'approval_type' => get_option('ph_approval_type', false),
			],
			'comment_scroll'         => $comment_scroll,
			'check_access_dashboard' => $can_access_mockup,
			'comments_access'        => apply_filters('ph_private_comments_access', false, get_the_ID()),
			'get_comments_status_access' => get_option('ph_set_comment_status_access', false),
			'comments_status_role_access' => apply_filters('ph_comments_status_role_access', false, get_the_ID()),
			'comments_status_access' => $comment_status_access_roles,
			'custom_pin_color' 	 => get_option('ph_pin_color_enable', false),
			'comment_status_names'   => apply_filters('ph_comment_status_names', $status_name),
			'comment_status_colors'   => apply_filters('ph_comment_status_custom_colors', $status_colors),
			'private_comment_access' => $pc_access_roles,
			'ph_check_private_comment_access' => apply_filters('ph_check_private_comments_access', false, get_the_ID()),
		)
	);

	wp_localize_script(
		'project-huddle',
		'projectHuddleJSL10n',
		array(
			'project-huddle' => ph_get_json_translations('project-huddle'),
		)
	);

	ph_localize_schema('project-huddle');
}
add_action('wp_enqueue_scripts', 'ph_load_scripts', 999999);

/**
 * Clear comment scroll session on shutdown
 */
function ph_remove_comment_scroll_session()
{
	//  PH()->session->clear( 'ph_comment_id' );
}
add_action('shutdown', 'ph_remove_comment_scroll_session');

/**
 * PH Shortcodes script
 */
function ph_shortcode_scripts()
{
	wp_register_script('project-huddle-shortcodes', PH_PLUGIN_URL . 'assets/js/dist/project-huddle-shortcodes.js', array('jquery', 'underscore', 'ph.components'), PH_VERSION);
	ph_localize_schema('project-huddle-shortcodes');
}

add_action('wp_enqueue_scripts', 'ph_shortcode_scripts');

function ph_localize_schema($handle)
{
	/**
	 * @var WP_REST_Server $wp_rest_server
	 */
	global $wp_rest_server;

	// Ensure the rest server is intiialized.
	if (empty($wp_rest_server)) {
		/** This filter is documented in wp-includes/rest-api.php */
		$wp_rest_server_class = apply_filters('wp_rest_server_class', 'WP_REST_Server');
		$wp_rest_server       = new $wp_rest_server_class();
		/** This filter is documented in wp-includes/rest-api.php */
		do_action('rest_api_init', $wp_rest_server);
	}

	// Load the schema.
	$schema_request  = new WP_REST_Request('GET', '/projecthuddle/v2');
	$schema_response = $wp_rest_server->dispatch($schema_request);
	$schema          = null;
	if (!$schema_response->is_error()) {
		$schema = $schema_response->get_data();
	}

	wp_localize_script(
		$handle,
		'wpApiSettings',
		array(
			'root'          => esc_url_raw(get_rest_url()),
			'nonce'         => wp_create_nonce('wp_rest'),
			'ajaxurl' 		=> admin_url('admin-ajax.php'),
			'versionString' => 'projecthuddle/v2/',
			'schema'        => $schema,
			'cacheSchema'   => true,
		)
	);

	wp_localize_script(
		$handle,
		'projectHuddleJSL10n',
		array(
			'project-huddle' => ph_get_json_translations($handle),
		)
	);

	global $wp_rest_server;
	$me = new WP_REST_Request('GET', '/projecthuddle/v2/users/me');
	$response = rest_do_request($me);

	$get_phf_plugin_dir = ABSPATH . 'wp-content/plugins/ph-pdf-mockups/ph-pdf-mockups.php'; 

	$is_phf_installed = file_exists($get_phf_plugin_dir);
	$is_phf_activated = is_plugin_active( 'ph-pdf-mockups/ph-pdf-mockups.php' ); 
	$check_file_upload_addon = '';

	if($is_phf_installed && $is_phf_activated == false) {
		$check_file_upload_addon = "installed";
	}
	if ($is_phf_installed == false) {
		$check_file_upload_addon = "not_installed";
	} 
	if ($is_phf_activated){
		$check_file_upload_addon = "activated";
	}
	 
	wp_localize_script(
		$handle,
		'phSettings',
		array(
			'data' => array(
				'access_unsubscribed' => (bool) current_user_can('edit_others_ph-projects'),
				'admin_url'           => esc_url_raw(get_admin_url()),
				'id'				  => get_the_ID(),
				'project_name'		  => ph_get_the_title(get_the_ID()),
				'me' 				  => $wp_rest_server->response_to_data($response, true),
				'slack_enable' => (bool)get_option( 'ph_slack_terms', false ),
				'check_file_upload_addon' => $check_file_upload_addon, 
			)
		)
	);
}

/**
 * Un-enqueue theme and other plugin styles and loads our plugin styles.
 *
 * Included style handles can be passed via a hook.
 *
 * @since 1.0
 */
function ph_shortcode_styles()
{
	// store javascript directory
	$css_dir = PH_PLUGIN_URL . 'assets/css/';

	wp_register_style('project-huddle-shortcodes', $css_dir . 'project-huddle-shortcodes.css', array(), PH_VERSION);
}

add_action('wp_enqueue_scripts', 'ph_shortcode_styles');
