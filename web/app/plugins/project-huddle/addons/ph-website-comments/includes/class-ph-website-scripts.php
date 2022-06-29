<?php

use PH\Models\User;

/**
 * Class to output website scripts and styles to the
 * website toolbar frontend.
 *
 * We use this instead of wp_enqueue_scripts as to not
 * interfere with theme functions or output unnecessary
 * styles an scripts.
 *
 * @package ProjectHuddle
 * @since 2.5.0
 */

/**
 * Website Scripts class
 */
class PH_Website_Scripts
{
	/**
	 * Store javascript directory
	 *
	 * @var string
	 */
	public $js_dir = '';

	/**
	 * Javascript dist directory
	 *
	 * @var string
	 */
	public $js_dist = '';

	/**
	 * Store CSS directory
	 *
	 * @var string
	 */
	public $css_dir = '';

	/**
	 * Get things going.
	 */
	public function __construct()
	{
		$this->css_dir = PH_WEBSITE_PLUGIN_URL . 'assets/css/';
		$this->js_dir  = PH_WEBSITE_PLUGIN_URL . 'assets/js/';
		$this->js_dist  = PH_PLUGIN_URL . 'assets/js/dist/';

		if (defined('PH_HMR') && PH_HMR) {
			$this->js_dist = 'https://127.0.0.1:8081/assets/js/dist/';
		}

		// include script enqueues.
		add_action('ph_website_header', 'wp_enqueue_scripts');

		// include customizer css.
		add_action('ph_website_header', 'wp_custom_css_cb', 101);

		// add scripts and styles.
		add_action('ph_website_header', array($this, 'header_styles'));
		add_action('ph_website_header', array($this, 'header_scripts'));
		add_action('ph_website_footer', array($this, 'footer_scripts'), 20);

		// register.
		add_action('wp_enqueue_scripts', array($this, 'register_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'register_styles'));
	}

	/**
	 * Register our PH styles
	 */
	public function register_styles()
	{
		// return for other pages.
		if (!is_singular('ph-website') && !is_singular('phw_comment_loc')) {
			return;
		}

		wp_register_style(
			'ph-website-comments',
			$this->css_dir . 'ph-website-comments.css',
			PH_VERSION
		);

		wp_add_inline_style('ph-website-comments', apply_filters('ph_inline_styles', ph_website_style_options()));
	}

	/**
	 * Register our PH Scripts
	 */
	public function register_scripts()
	{
		global $post;

		// return for other pages.
		if (!is_singular('ph-website') && !is_singular('phw_comment_loc') || !is_a($post, 'WP_Post')) {
			return;
		}

		wp_register_script('x-domain', $this->js_dir . 'includes/xdomain.min.js', array(), '0.8.3', false);

		$handler = esc_url(get_home_url());

		// get components
		$url  = parse_url($handler);
		$port = isset($url['port']) ? ':' . $url['port'] : '';
		$path = isset($url['path']) ? ':' . $url['path'] : '';

		// allow ssl and non ssl urls
		$url_ssl = 'https://' . $url['host'] . $port;
		$url     = 'http://' . $url['host'] . $port;

		wp_add_inline_script(
			'x-domain',
			"
		xdomain.slaves({
            '" . esc_url($url) . "' : '/?ph_handler=" . (int) $post->ID . "',
			'" . esc_url($url_ssl) . "' : '/?ph_handler=" . (int) $post->ID . "',
			'" . esc_url($url) . "' : '" . $path . '/?ph_handler=' . (int) $post->ID . "',
			'" . esc_url($url_ssl) . "' : '" . $path . '/?ph_handler=' . (int) $post->ID . "',
		});
		"
		);

		wp_register_script(
			'ph-website-comments',
			$this->js_dist . 'ph-website-comments.js',
			array(
				'x-domain',
				'underscore',
				'jquery',
				'ph.components',
			),
			PH_VERSION,
			true
		);

		$logo_image = false;
		$logo       = apply_filters('ph_website_control_logo_id', get_option('ph_control_logo'));

		if ($logo) {
			$logo_image = wp_get_attachment_image_src($logo, 'full');

			// check retina option
			if (apply_filters('ph_website_control_logo_retina', get_option('ph_control_logo_retina'))) :
				$logo_image[1] = $logo_image[1] / 2;
				$logo_image[2] = $logo_image[2] / 2;
			endif;
		}

		// create iframe
		$style_dir = PH_PLUGIN_DIR . 'assets/css/dist/';

		$get_progress_status = get_option('ph_progress_status_enable');
		$get_review_status = get_option('ph_review_status_enable');
		$get_progress_status_text = get_option('ph_progress_status_name', 'In Progress');
		$get_review_status_text = get_option('ph_review_status_name', 'In Review');

		$status_name = array(
			'active' => get_option('ph_active_status_name', 'Active'),
			'in_progress' => $get_progress_status !== 'on' ? $get_progress_status_text : 'off',
			'in_review' => $get_review_status !== 'on' ? $get_review_status_text : 'off',
			'resolved' =>get_option('ph_resolve_status_name', 'Resolved'),
		);

		$status_colors = array(
			'active' => empty(get_option('ph_active_status_color')) ? get_option('ph_highlight_color', '#4353ff') : get_option('ph_active_status_color', '#4353ff'),
			'in_progress' => $get_progress_status !== 'on' ? get_option('ph_progress_status_color', '#ffc107') : '#ffc107',
			'in_review' => $get_review_status !== 'on' ? get_option('ph_review_status_color', '#ff9800') : '#ff9800',
			'resolved' =>  get_option('ph_resolve_status_color','#48bb78'),
		);

		$comment_status_access_roles = get_option('ph_comment_status_access', false);

		$pc_access_roles = get_option('ph_private_comment_access', false);
		// localize our origin.
		wp_localize_script(
			'ph-website-comments',
			'PH_Settings',
			array(
				'initial_refresh'        => User::current()->getRefreshToken(),
				'debug'                  => defined('SCRIPT_DEBUG') ? SCRIPT_DEBUG : false,
				'home_url'               => get_home_url(),
				'screenshots'            => apply_filters('ph_screenshots_enable', true),
				'screenshot_quality'     => apply_filters('ph_screenshot_quality', 0.1),
				'comments_per_page'      => apply_filters('ph_comments_per_page', 10),
				'options'                => PH()->website->get_project_options(get_the_ID()),
				'simple_toolbar'         => apply_filters('ph_simple_toolbar', false, get_the_ID()),
				'comments_access'        => apply_filters('ph_private_comments_access', false, get_the_ID()),
				'get_comments_status_access' => get_option('ph_set_comment_status_access', false),
				'comments_status_role_access' => apply_filters('ph_comments_status_role_access', false, get_the_ID()),
				'comments_status_access' => $comment_status_access_roles,
				'comment_status_names'   => apply_filters('ph_comment_status_names', $status_name),
				'comment_status_colors'   => apply_filters('ph_comment_status_custom_colors', $status_colors),
				'private_comment_access' => $pc_access_roles,
				'ph_check_private_comment_access' => apply_filters('ph_check_private_comments_access', false, get_the_ID()),
				'login_without_password' => ph_project_allow_login_without_password(get_the_ID()),
				'help_link'              => esc_url(get_option('ph_help_link', '')),
				'login_link'             => esc_url(wp_login_url(get_post_meta($post->ID, 'website_url', true))),
				'logout_link'            => esc_url(wp_logout_url(get_post_meta($post->ID, 'website_url', true))),
				'edit_link'              => esc_url(admin_url(sprintf(get_post_type_object('ph-website')->_edit_link . '&action=edit', get_the_ID()))),
				'highlight_element'      => apply_filters('ph_website_screenshots_highlight_element', true, get_the_ID()),
				'ph_highlight_color' => get_option('ph_highlight_color', '#4353ff'),
				'default_thread_members' => ph_default_project_members($post->ID),
				'logo'                   => array(
					'url'    => isset( $logo_image[0] ) ? esc_url($logo_image[0]) : '',
					'width'  => isset( $logo_image[1] ) ? (float) $logo_image[1] : '',
					'height' => isset( $logo_image[2] ) ? (float) $logo_image[2] : '',
				),
				'styles'                 => array(
					'custom' => ph_style_options(),
					'main' => ph_website_script($style_dir . 'ph-website-comments.css', [
						'ph_website_comments_css',
						'ph_website_thread_css',
						'ph_website_toolbar_css',
						'ph_website_panel_css',
						'ph_website_notifications_css'
					]),
				),
				'approval' => [
					'require_terms' => (bool) get_option('ph_require_terms', false),
					'checkbox_text' => sanitize_text_field(get_option('ph_approve_terms_checkbox_text', sprintf(__('I, %1s, read and agree with the %2s.', 'project-huddle'), '{{user_name}}', '{{terms}}'))),
					'link_text' =>  sanitize_text_field(get_option('ph_approve_terms_link_text', __('Terms', 'project-huddle'))),
					'terms' => wp_kses_post(wpautop(get_option('ph_approve_terms', ''))),
					'approval_type' => get_option('ph_approval_type', false),
				]
			)
		);

		wp_localize_script(
			'ph-website-comments',
			'projectHuddleJSL10n',
			array(
				'project-huddle' => ph_get_json_translations('ph-website-comments'),
			)
		);

		wp_add_inline_script('ph-website-comments', "
		  jQuery(document).ready(function() {
			ph.start(" . json_encode(PH()->website->rest->get(
			$post->ID,
			array(
				'_expand'    => array(
					'pages' => 'all',
				),
				'_signature' => isset($_GET['ph_signature']) ? $_GET['ph_signature'] : '',
				'_access_token' => isset($_GET['ph_access_token']) ? $_GET['ph_access_token'] : '',
			)
		)) . ");
		  });
		");

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

		// Localize the plugin settings and schema.
		$settings = array(
			'root'          => esc_url_raw(get_rest_url()),
			'site'			=> esc_url_raw(get_site_url()),
			'nonce'         => wp_create_nonce('wp_rest'),
			'ajaxurl' 		=> admin_url('admin-ajax.php'),
			'versionString' => 'projecthuddle/v2/',
			'schema'        => $schema,
			'cacheSchema'   => true,
		);

		/**
		 * Filter the JavaScript Client settings before localizing.
		 *
		 * Enables modifying the config values sent to the JS client.
		 *
		 * @param array  $settings The JS Client settings.
		 */
		$settings = apply_filters('rest_js_client_settings', $settings);
		wp_localize_script('ph-website-comments', 'wpApiSettings', $settings);
	}

	/**
	 * Header scripts
	 */
	public function header_scripts()
	{
		global $wp_scripts;

		$scripts = array(
			'x-domain',
			'jquery',
		);

		$wp_scripts->do_items(apply_filters('ph_website_header_scripts', $scripts));
	}

	/**
	 * Header styles
	 */
	public function header_styles()
	{
		global $wp_styles;

		$styles = array();

		$allowed = apply_filters('ph_allowed_website_styles', array());
		if (!empty($allowed)) {
			foreach ($allowed as $style) {
				$styles[] = $style;
			}
		}

		$wp_styles->do_items(apply_filters('ph_website_footer_scripts', $styles));
	}

	/**
	 * Footer scripts
	 */
	public function footer_scripts()
	{
		global $wp_scripts;

		$ph_scripts = array(
			'ph-website-comments',
		);

		$allowed = apply_filters('ph_allowed_website_scripts', array());
		if (!empty($allowed)) {
			foreach ($allowed as $script) {
				$ph_scripts[] = $script;
			}
		}

		$wp_scripts->do_items(apply_filters('ph_website_footer_scripts', $ph_scripts));
	}
}
