<?php

/**
 * Filters and actions to improve 3rd party plugin compatibility
 */
// disable WordPress' big image size threshold
add_filter('big_image_size_threshold', '__return_false');

/**
 * Don't autoptimize on our post types
 *
 * @param boolean $active
 * @return void
 */
function ph_disable_autoptimize($active)
{
	global $post;
	if ($post && in_array(get_post_type($post), ph_get_post_types())) {
		return true;
	}

	return $active;
}
add_filter('autoptimize_filter_noptimize', 'ph_disable_autoptimize', 10, 1);

/**
 * Don't add redirect when we create our custom url slug for mockups
 *
 * @return bool True doesn't change slug
 */
function ph_wpseo_premium_post_redirect_slug_change()
{
	global $post;

	if (get_post_type($post) == 'ph-project') {
		return true;
	}

	return false;
}

add_filter('wpseo_premium_post_redirect_slug_change', 'ph_wpseo_premium_post_redirect_slug_change');

/**
 * Allow users that have "Custom Role" to  view the Dashboard
 *
 * @param $access
 *
 * @return bool
 */
function ph_show_admin($access)
{
	if (current_user_can('project_editor') || current_user_can('project_collaborator') || current_user_can('project_admin')) {
		$access = false;
	}

	return $access;
}

add_filter('woocommerce_prevent_admin_access', 'ph_show_admin');
add_filter('woocommerce_disable_admin_bar', 'ph_show_admin');

/**
 * After login or registration, redirect users with ProjectHuddle
 * Roles to the Dashboard instead of WooCommerce's My Account
 *
 * @param $redirect
 *
 * @return string
 */
function ph_admin_redirect($redirect)
{
	if (current_user_can('project_editor') || current_user_can('project_collaborator') || current_user_can('project_admin')) {
		$user     = get_current_user_id();
		$redirect = get_dashboard_url($user);
	}

	return $redirect;
}

add_filter('woocommerce_login_redirect', 'ph_admin_redirect');
add_filter('woocommerce_registration_redirect', 'ph_admin_redirect');

/**
 * Check that permalinks are enabled
 */
function ph_check_api_permalinks()
{
	if (!get_option('permalink_structure')) {
		$class   = 'notice notice-error';
		$message = __('ProjectHuddle requires permalinks to be enabled in order to access the WordPress API. Please enable permalinks under Settings > Permalinks.', 'project-huddle');

		printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
	}
}

add_action('admin_notices', 'ph_check_api_permalinks');

/**
 * Really simple ssl compatibility
 */
function ph_really_simple_ssl()
{
	$options = get_option('rlrsssl_options', false);

	if ($options && is_array($options)) {
		if (isset($options['javascript_redirect']) && $options['javascript_redirect']) {
			$class   = 'notice notice-error';
			$message = __('You must turn off Really Simple SSL\'s javascript redirection feature or ProjectHuddle won\'t work on non-ssl sites.', 'project-huddle');
			$link    = '<a href="' . esc_url(admin_url('options-general.php?page=rlrsssl_really_simple_ssl&tab=settings')) . '">' . __('Change It', 'project-huddle') . '</a>';
			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message) . ' ' . $link);
		}
	}
}

add_action('admin_notices', 'ph_really_simple_ssl');

/**
 * Remove SelectWoo from our pages
 */
function ph_remove_select_woo()
{
	global $post;

	if (get_post_type($post) === 'ph-website' || get_post_type($post) === 'ph-project') {
		wp_deregister_script('selectWoo');
		wp_dequeue_script('selectWoo');
	}
}

add_action('admin_enqueue_scripts', 'ph_remove_select_woo', 11);

/**
 * Fix Astra orphan ob_start()
 */
function ph_custom_fix_astra_ob_start()
{
	global $post;

	if (get_post_type($post) === 'ph-website') {
		remove_filter('astra_dynamic_css', 'astra_typography_dynamic_css');
	}
}

add_action('wp_enqueue_scripts', 'ph_custom_fix_astra_ob_start');

/**
 * Check for naughty themes or plugins disabling canonical redirects
 *
 * @return void
 */
function ph_check_canonical_redirect_removal()
{
	if (!has_filter('template_redirect', 'redirect_canonical')) {
		$class   = 'notice notice-error';
		$message = __('A theme or plugin on this installation has disabled canonical redirects. You\'ll need to enable them, for ProjectHuddle to work properly.', 'project-huddle');
		printf('<div class="%1$s"><p><strong>%2$s</strong>: %3$s</p></div>', esc_attr($class), __('ProjectHuddle', 'project-huddle'), esc_html($message));
	}
}
add_action('admin_notices', 'ph_check_canonical_redirect_removal');

/**
 * Make sure disable comments plugin isn't active
 */
function ph_check_disable_comments()
{
	if (is_plugin_active('disable-comments/disable-comments.php') || is_plugin_active('disable-comments-rb/disable-comments-rb.php')) {
		// dismissed notice
		if (get_site_option('dismissed-ph-disabled-comments-notice', false)) {
			return;
		}
		$class   = 'notice notice-error is-dismissible ph-notice';
		$message = __('Notice! You must turn off your Disable Comments plugin or ProjectHuddle won\'t be able to save any comments!', 'project-huddle');
		$link    = '<a href="' . esc_url(admin_url('plugins.php')) . '">' . __('Plugins Page', 'project-huddle') . '</a>';
		printf('<div class="%1$s" data-notice="ph-disabled-comments-notice"><p>%2$s</p></div>', esc_attr($class), esc_html($message) . ' ' . $link);
		ph_dismiss_js();
	}
}

add_action('admin_notices', 'ph_check_disable_comments');

function ph_wpengine_exclusions_notice()
{
	if (get_option('ph_setup_completed')) {
		return;
	}

	// on wp engine
	if (!defined('WPE_APIKEY')) {
		return;
	}

	// dismissed notice
	if (get_site_option('dismissed-ph-wp-engine', false)) {
		return;
	}

	echo '<div class="notice notice-info is-dismissible ph-notice" data-notice="ph-wp-engine">
					<p>' . esc_html(sprintf(__('WPEngine hosting detected!  You\'ll need to request a cache exclusion in order for ProjectHuddle access links to work properly.', 'project-huddle'))) . '</p>
					<p><a href="#" data-beacon-article-modal="5ac38282042863794fbed8c0">Learn More</a></p>
				</div>';
	ph_dismiss_js();
}
add_action('admin_notices', 'ph_wpengine_exclusions_notice');


function ph_flywheel_exclusions_notice()
{
	if (get_option('ph_setup_completed')) {
		return;
	}

	// on wp engine
	if (!defined('FLYWHEEL_CONFIG_DIR')) {
		return;
	}

	// dismissed notice
	if (get_site_option('dismissed-ph-flywheel', false)) {
		return;
	}

	echo '<div class="notice notice-info is-dismissible ph-notice" data-notice="ph-flywheel">
					<p>' . esc_html(sprintf(__('Flywheel hosting detected!  You\'ll need to request a cache exclusion in order for ProjectHuddle to work properly.', 'project-huddle'))) . '</p>
					<p><a href="#" data-beacon-article-modal="5da748b02c7d3a7e9ae29db8">Learn More</a></p>
				</div>';
	ph_dismiss_js();
}
add_action('admin_notices', 'ph_flywheel_exclusions_notice');

/**
 * Fix missing comments with WPML
 */
function ph_fix_wpml_missing_comments($filtered, $post_id)
{
	// if the post is one of our post types, don't filter
	if (in_array(get_post_type($post_id), ph_get_child_post_types())) {
		$filtered = false;
	}

	return $filtered;
}

add_filter('wpml_is_comment_query_filtered', 'ph_fix_wpml_missing_comments', 10, 2);

/**
 * Remove WordPress seo meta box.
 */
function ph_remove_yoast_metabox()
{
	if (in_array(get_post_type(), ph_get_post_types())) {
		remove_meta_box('wpseo_meta', get_post_type(), 'normal');

		// remove astra settings mb from mockup post types
		remove_meta_box('astra_settings_meta_box', get_post_type(), 'side');
	}
}
add_action('add_meta_boxes', 'ph_remove_yoast_metabox', 11);

/* Exclude Multiple Content Types From Yoast SEO Sitemap */
function ph_sitemap_exclude_post_type($value, $post_type)
{
	if (in_array($post_type, ph_get_all_post_types())) return true;
}
add_filter('wpseo_sitemap_exclude_post_type', 'ph_sitemap_exclude_post_type', 10, 2);

/**
 * Update message
 *
 * @param [type] $data
 * @param [type] $response
 * @return void
 */
function ph_file_uploads_update_message($data, $response)
{
	printf(
		'</br></br><strong>%s</strong>',
		__('You need to update your File Uploads Extension to 2.0 for compatibility with this version of ProjectHuddle.', 'project-huddle')
	);
}
add_action('in_plugin_update_message-ph-file-uploads/ph-file-uploads.php', 'ph_file_uploads_update_message', 10, 2);

/**
 * Add compatibility message
 *
 * @return void
 */
function ph_file_uploads_compatibility_message()
{
	if (!ph_file_uploads_updated()) {
		echo '<div class="notice notice-warning is-dismissible ph-notice">';
			echo '<p>';
				printf( __('%1$sProjectHuddle:%2$s You need to update your %1$sFile Uploads Extension%2$s to 2.0 for compatibility with this version of ProjectHuddle.', 'project-huddle'),
				'<strong>',
				'</strong>'
				);
			echo '</p><p><a href="' . esc_url(admin_url('plugins.php')) . '" class="button button-primary">' . esc_html__('Update Now', 'project-huddle') . '</a></p>';
		echo '</div>';
	}
}
add_action('admin_notices', 'ph_file_uploads_compatibility_message');

function ph_pdf_mockups_compatibility_message()
{
	if (!ph_pdf_mockups_updated()) {
		echo '<div class="notice notice-warning is-dismissible ph-notice">';
			echo '<p>';
				printf( __('%1$sProjectHuddle:%2$s You need to update your %1$sPDF Mockups Extension%2$s to 2.0 for compatibility with this version of ProjectHuddle.', 'project-huddle'),
				'<strong>',
				'</strong>'
				);
			echo '</p><p><a href="' . esc_url(admin_url('plugins.php')) . '" class="button button-primary">' . esc_html__('Update Now', 'project-huddle') . '</a></p>';
		echo '</div>';
	}
}
add_action('admin_notices', 'ph_pdf_mockups_compatibility_message');

/**
 * Check if file uploads is up to date
 *
 * @return void
 */
function ph_file_uploads_updated()
{
	if (!defined('PH_UPLOADS_PLUGIN_VERSION')) {
		return true;
	}
	return defined('PH_UPLOADS_PLUGIN_VERSION') && !version_compare(PH_UPLOADS_PLUGIN_VERSION, '2.1.0', '<');
}

/**
 * Check if file uploads is up to date
 *
 * @return void
 */
function ph_pdf_mockups_updated()
{
	if (!defined('PH_PDF_PLUGIN_VERSION')) {
		return true;
	}
	return defined('PH_PDF_PLUGIN_VERSION') && !version_compare(PH_PDF_PLUGIN_VERSION, '2.1.0', '<');
}

/**
 * Maybe dequeue website script if not
 *
 * @param array $scripts
 * @return void
 */
function ph_maybe_dequeue_file_uploads($scripts)
{
	if (!ph_file_uploads_updated()) {
		if (($key = array_search('ph-file-uploads-websites', $scripts)) !== false) {
			unset($scripts[$key]);
		}
	}
	return $scripts;
}
// add_action('ph_allowed_website_scripts', 'ph_maybe_dequeue_file_uploads', 30);

// make sure woocommerce doesn't change logged out nonce.
add_action('rest_api_init', 'ph_woo_disable_logged_out_nonce');
function ph_woo_disable_logged_out_nonce()
{
	if (defined('REST_REQUEST') && class_exists('WC_Session_Handler')) {
		remove_filter('nonce_user_logged_out', array('WC_Session_Handler', 'nonce_user_logged_out'));
	}
}

function ph_page_builders_fix($load)
{
	$disabled = apply_filters('ph_disable_for_query_vars', array(
		// divi
		'et_fb',
		// elementor
		'elementor-preview',
		// beaver builder
		'fl_builder',
		'fl_builder_preview',
		// fusion
		'builder',
		'fb-edit',
	));

	// disable these
	if (!empty($_GET) && is_array($_GET)) {
		foreach ($_GET as $arg => $_) {
			if (in_array($arg, $disabled)) {
				return false;
			}
		}
	}

	// oxygen is... "special"
	if (isset($_GET['ct_builder'])) {
		return false; // TODO: remove once we can get pageX, pageY inside iframe.
		// bail if admin commenting is disabled
		if (!get_option('ph_child_admin', false)) {
			return false;
		}
		// bail if not in the iframe
		if (!isset($_GET['oxygen_iframe'])) {
			return false;
		}
	}

	return $load;
}
add_filter('ph_website_script_output_enable', 'ph_page_builders_fix');

function ph_check_caching_plugin_status()
{
	if (get_option('ph_setup_completed')) {
		return;
	}

	// dismissed notice
	if (get_site_option('dismissed-ph-caching-plugins-detection', false)) {
		return;
	}

	// check for advanced cache
	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
	$filesystem 		   = new WP_Filesystem_Direct(new StdClass());
	$cache_file_is_file    = $filesystem->is_file(WP_CONTENT_DIR . '/advanced-cache.php');

	// check for object caching
	$object_caching 	   = defined('ENABLE_CACHE') && true === ENABLE_CACHE;

	// check if cache directory is empty
	// $cache_directory_empty = ph_dir_is_empty(WP_CONTENT_DIR . '/cache');

	// if no cache file and no object caching, bail
	if (!$cache_file_is_file && !$object_caching) {
		return;
	}

	echo '<div class="notice notice-info is-dismissible ph-notice" data-notice="ph-caching-plugins-detection">
		<p>' . esc_html(sprintf(__('ProjectHuddle: Possible site caching detected!  You\'ll need to add some cache exclusions for ProjectHuddle to work properly.', 'project-huddle'))) . '</p>
		<p><a href="#" data-beacon-article-modal="5bec3bd604286304a71c4307">Learn More</a></p>
	</div>';
	ph_dismiss_js();
}
add_action('admin_notices', 'ph_check_caching_plugin_status');

if (!function_exists('ph_dir_is_empty')) :
	function ph_dir_is_empty($dir)
	{
		// it's empty if it does not exist.
		if (!file_exists($dir)) {
			return true;
		}

		$handle = opendir($dir);

		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				closedir($handle);
				return false;
			}
		}

		closedir($handle);

		return true;
	}
endif;

function ph_cp_client_roles($roles)
{
	$roles = array_merge($roles, array('project_client'));
	return $roles;
}
add_filter('leco_cp_client_roles', 'ph_cp_client_roles');

function ph_wordfence_compat()
{
	if (class_exists('wfConfig') && method_exists('wfConfig', 'get')) {
		if (wfConfig::get('loginSec_disableAuthorScan')) {
			// dismissed notice
			if (get_site_option('dismissed-ph-wordfence-author-scans', false)) {
				return;
			}
			echo '<div class="notice notice-info is-dismissible ph-notice" data-notice="ph-wordfence-author-scans">
				<p style="font-size: 16px"><strong>' . esc_html(__('ProjectHuddle: WordFence compatibility issue.', 'project-huddle')) . '</strong></p>
				<p>' . esc_html(__(' You must disable "Prevent discovery of usernames through \'/?author=N\' scans" or ProjectHuddle will not be able to save comments.', 'project-huddle')) . '</p>
				<p><a href="' . esc_url(admin_url('admin.php?page=WordfenceOptions')) . '">Change Setting</a></p>
			</div>';
			ph_dismiss_js();
		}
	}
}
add_action('admin_notices', 'ph_wordfence_compat');
