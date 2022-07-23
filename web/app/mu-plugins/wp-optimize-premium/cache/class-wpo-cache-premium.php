<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (!class_exists('WPO_Cache_Premium')) :

class WPO_Cache_Premium {
	/**
	 * Construct WPO_Cache_Premium - setup filters and actions, initiate class variables
	 */
	public function __construct() {
		// option defaults filter for premium features.
		add_filter('wpo_cache_defaults', array($this, 'get_defaults'));

		$this->config = WP_Optimize()->get_page_cache()->config->get();

		add_action('woocommerce_init', array($this, 'maybe_set_tax_country_cookie'), 40);

		add_action('wpo_page_cache_settings_after', array($this, 'add_woocommerce_template_to_cache_settings'), 20, 1);
		add_filter('wpo_cache_update_config', array($this, 'add_default_values_to_cache_config'), 20);
		add_filter('wpo_cache_cookies', array($this, 'add_cache_cookies'), 20, 2);
		add_filter('wpo_cache_query_variables', array($this, 'add_cache_query_variables'), 20, 2);
		add_filter('display_post_states', array($this, 'add_post_state'), 30, 2);
		add_action('add_meta_boxes', array($this, 'add_cache_control_metabox'));

		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_styles'));

		add_action('wpo_premium_scripts_styles', array($this, 'wpo_page_only_scripts'), 20, 3);

		add_action('wpo_after_cache_exception_urls', array($this, 'output_excluded_posts'));

		add_action('wpo_after_cache_exception_urls_fieldgroup', array($this, 'output_always_purge_posts'));
		add_action('save_post', array($this, 'always_purge_post_on_update'), 10, 1);
		add_action('add_attachment', array($this, 'always_purge_post_on_update'), 10, 1);
		add_action('edit_attachment', array($this, 'always_purge_post_on_update'), 10, 1);
		add_action('delete_attachment', array($this, 'always_purge_post_on_update'), 10, 1);

		// Cloudflare initialisation.
		include_once(WPO_PLUGIN_MAIN_PATH.'/cache/class-wp-optimize-cloudflare-api.php');
		include_once(WPO_PLUGIN_MAIN_PATH.'/cache/class-wp-optimize-cloudflare.php');
		$this->cloudflare = new WP_Optimize_Cloudflare($this->config);

		// Add action for disable caching for selected posts.
		add_action('wp', array($this, 'disable_posts_caching'));

		// Add user per role cache option
		add_action('wpo_after_cache_settings', array($this, 'output_user_per_role_cache_option'), 9);
		
		// Add user specific cache option
		add_action('wpo_after_cache_settings', array($this, 'output_user_specific_cache_option'), 10);
		
	}
	
	/**
	 * Default options values.
	 */
	public function get_defaults($defaults) {
		$defaults['enable_per_role_cache'] = false;
		$defaults['enable_user_specific_cache'] = false;
		return $defaults;
	}

	/**
	 * Disable caching for posts those have _wpo_disable_caching meta field.
	 */
	public function disable_posts_caching() {
		global $post;
		if (!defined('DONOTCACHEPAGE') && $post && get_post_meta($post->ID, '_wpo_disable_caching') && (is_page() || is_single())) {
			define('DONOTCACHEPAGE', true);
		}
	}

	/**
	 * Output excluded posts list.
	 */
	public function output_excluded_posts() {
		$wpo_cache_excluded_posts = $this->get_excluded_posts();

		WP_Optimize()->include_template('cache/page-cache-excluded-posts.php', false, array(
			'wpo_cache_excluded_posts' => $wpo_cache_excluded_posts,
		));
	}

	/**
	 * Output always purge posts section.
	 */
	public function output_always_purge_posts() {
		$always_purge_post_type = isset($this->config['always_purge_post_type']) ? $this->config['always_purge_post_type'] : array();
		$always_purge_post_id = isset($this->config['always_purge_post_id']) ? $this->config['always_purge_post_id'] : array();
		$post_types = $this->get_post_types();
		$always_purge_post_type_str = array();

		foreach ($always_purge_post_type as $i => $post_type) {
			$post_types_arr = explode(',', $post_type);
			$post_types_str = '';
			
			foreach ($post_types_arr as $single_pt) {
				$single_pt = trim($single_pt);
				$post_types_str .= ('' != $post_types_str) ? ', ' : '';
				$post_types_str .= isset($post_types[$single_pt]) ? $post_types[$single_pt] : $single_pt;
			}

			$always_purge_post_type_str[$i] = $post_types_str;
		}

		WP_Optimize()->include_template('cache/page-cache-always-purge-posts.php', false, array(
			'always_purge_post_type' => $always_purge_post_type,
			'always_purge_post_type_str' => $always_purge_post_type_str,
			'always_purge_post_id' => $always_purge_post_id,
			'post_types' => $post_types,
		));
	}

	/**
	 * Get list name => label with available public post types.
	 */
	private function get_post_types() {
		$result = array();
		$post_types = get_post_types(array('public' => true), 'objects');
	
		foreach ($post_types as $post_type_obj) {
			$result[$post_type_obj->name] = $post_type_obj->labels->singular_name;
		}

		return $result;
	}

	/**
	 * Get array with "always purge this page" settings.
	 *
	 * @return array
	 */
	public function get_always_purge_config() {
		$always_purge_post_type = isset($this->config['always_purge_post_type']) ? $this->config['always_purge_post_type'] : array();
		$always_purge_post_id = isset($this->config['always_purge_post_id']) ? $this->config['always_purge_post_id'] : array();

		$result = array();
		foreach ($always_purge_post_type as $i => $post_type) {
			$result[] = array(
				'post_id' => $always_purge_post_id[$i],
				'post_type' => $post_type,
			);
		}

		return $result;
	}

	/**
	 * Get always purge settings for selected post.
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_always_purge_post_config($post_id) {
		$always_purge_config = $this->get_always_purge_config();
		$post_type = array();

		foreach ($always_purge_config as $item) {
			if ($post_id != $item['post_id']) continue;

			$post_type = array_merge($post_type, explode(',', $item['post_type']));
		}

		return $post_type;
	}

	/**
	 * Handle post update and purge pages configured in "always purge this page" settings.
	 */
	public function always_purge_post_on_update($post_id) {
		$post_type = get_post_type($post_id);
		$post_type_object = get_post_type_object($post_type);

		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || 'revision' === $post_type || !$post_type_object->public) {
			return;
		}

		$always_purge_config = $this->get_always_purge_config();

		foreach ($always_purge_config as $item) {
			$post_types = explode(',', $item['post_type']);
			if (in_array($post_type, $post_types)) {
				WPO_Page_Cache::delete_single_post_cache($item['post_id']);
			}
		}
	}

	/**
	 * Enqueue scripts and styles for premium cache features.
	 */
	public function enqueue_scripts_styles() {
		$current_screen = get_current_screen();
		// Only enqueue If the screen is the post edit page
		if ('post' == $current_screen->base) {
			$enqueue_version = (defined('WP_DEBUG') && WP_DEBUG) ? WPO_VERSION.'.'.time() : WPO_VERSION;
			$min_or_not = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
			$min_or_not_internal = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '-'. str_replace('.', '-', WPO_VERSION). '.min';
			if (!wp_script_is('select2')) {
				wp_enqueue_script('select2', WPO_PLUGIN_URL . 'js/select2/select2' . $min_or_not . '.js', array('jquery'), $enqueue_version);
			}
			if (!wp_style_is('select2')) {
				wp_enqueue_style('select2', WPO_PLUGIN_URL . 'css/select2/select2' . $min_or_not . '.css', array(), $enqueue_version);
			}
			wp_enqueue_script('wp-optimize-cache-premium-admin', WPO_PLUGIN_URL . 'js/wpo-cache-premium-edit-post' . $min_or_not_internal . '.js', array('wp-optimize-send-command', 'select2'), $enqueue_version);
		}
	}

	/**
	 * Adds scripts to WP-Optimize's dashboard only
	 *
	 * @param string $min_or_not_internal - The .min-version-number suffix to use on internal assets
	 * @param string $min_or_not          - The .min suffix to use on third party assets
	 * @param string $enqueue_version     - The enqueued version
	 * @return void
	 */
	public function wpo_page_only_scripts($min_or_not_internal, $min_or_not, $enqueue_version) {
		wp_enqueue_script('wp-optimize-cache-premium', WPO_PLUGIN_URL . 'js/wpo-cache-premium' . $min_or_not_internal . '.js', array('wp-optimize-send-command'), $enqueue_version);
	}

	/**
	 * Add default values to use in cache extensions - filters the WPO config when updating it
	 *
	 * @param array $config - The original config array
	 * @return array
	 */
	public function add_default_values_to_cache_config($config) {
		if (!isset($config['default_values'])) {
			$config['default_values'] = array();
		}

		// Add WooCommerce's default currency
		if (function_exists('get_woocommerce_currency')) {

			/**
			 * Filters wether or not to add WooCommerce default currency to the cache config.
			 */
			if (apply_filters('wpo_add_woocommerce_default_currency_to_cache_config', true)) {
				// Save the default WC currency in our config.
				// Note: We use get_option('woocommerce_currency') as get_woocommerce_currency() is overriden by the currency selector plugin.
				$config['default_values']['woocommerce_currency'] = get_option('woocommerce_currency');
			}
		}

		// Set default woocommerce values
		if (defined('WC_ABSPATH') && function_exists('WC')) {
			$config['default_values']['WC_ABSPATH'] = wp_normalize_path(WC_ABSPATH);
			// If cache per country is enabled
			if (isset($config['enable_cache_per_country']) && $config['enable_cache_per_country']) {
				$wc_get_base_location = wc_get_base_location();
				$config['default_values']['wc_default_country'] = $wc_get_base_location['country'];

				// Get Geolite database path
				if (version_compare(WC()->version, '3.9.0', '>=')) {
					$integrations = WC()->integrations;
					if (is_callable(array($integrations, 'get_integration'))) {
						$integration = $integrations->get_integration('maxmind_geolocation');
						if (is_callable(array($integration, 'get_database_service'))) {
							$database = $integration->get_database_service()->get_database_path();
						}
					}
				} elseif (is_callable(array('WC_Geolocation', 'get_local_database_path'))) {
					$database = WC_Geolocation::get_local_database_path();
				}

				// We need the database, WooCommerce 3.4+ and PHP 5.4+ to use Geolite2
				$config['default_values']['wc_geolocate'] = isset($database) && file_exists($database) && version_compare(WC()->version, '3.4.0', '>=') && version_compare(PHP_VERSION, '5.4.0', '>=');
				if (file_exists($database)) {
					$config['default_values']['wc_geolocate_database'] = wp_normalize_path($database);
				}
			}
		}
		return $config;
	}

	/**
	 * Filter COOKIE variable names used for building cache file name.
	 *
	 * @param array $cookies - The array of cookies
	 * @param array $config  - The cache configuration
	 *
	 * @return array
	 */
	public function add_cache_cookies($cookies, $config) {

		// Aelia currency switcher
		if (class_exists('WC_Aelia_CurrencySwitcher')) {
			$cookies[] = 'aelia_cs_selected_currency';
		}

		if (isset($config['enable_cache_per_country']) && $config['enable_cache_per_country']) {
			$cookies[] = 'woocommerce_tax_country';
		}
	
		return $cookies;
	}

	/**
	 * Filter GET query variable names used for building cache file name.
	 *
	 * @param array $variables - The array of query variables
	 * @param array $config    - The cache configuration
	 *
	 * @return array
	 */
	public function add_cache_query_variables($variables, $config) {

		// WPML multi currency plugin.
		if (defined('WCML_VERSION')) {
			$variables[] = 'wcmlc';
		}

		// check if active WPML plugin.
		if (defined('ICL_SITEPRESS_VERSION')) {
			$variables[] = 'lang';
		}

		// Aelia Currency switcher
		if (class_exists('WC_Aelia_CurrencySwitcher')) {
			$variables[] = 'aelia_cs_currency';
		}

		// WooCommerce geolocation - Selecting 'geolocation_ajax' in WC will add v=location-key to every request.
		if ('geolocation_ajax' === get_option('woocommerce_default_customer_address') && isset($config['enable_cache_per_country']) && $config['enable_cache_per_country']) {
			$variables[] = 'v';
		}

		return $variables;
	}

	/**
	 * Include the extra template
	 *
	 * @param array $wpo_cache_options - The main page cache options
	 * @return void
	 */
	public function add_woocommerce_template_to_cache_settings($wpo_cache_options) {
		// Load this if WooCommerce is loaded
		if (!apply_filters('wpo_add_woocommerce_template_to_cache_settings', function_exists('WC'))) return;
		WP_Optimize()->include_template('cache/page-cache-woocommerce.php', false, array('wpo_cache_options' => $wpo_cache_options));
	}

	/**
	 * Maybe sets a cookie with the user's tax country
	 *
	 * @return void
	 */
	public function maybe_set_tax_country_cookie() {

		if ((defined('DOING_CRON') && DOING_CRON)
			|| (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX))
			|| (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)
		) return;

		if (!isset($this->config['enable_cache_per_country']) || !$this->config['enable_cache_per_country']) return;

		// Override current value when a known $_REQUEST changes it in WC
		if (isset($_POST['billing_country'])) {
			// Value from WooCommerce form
			setcookie('woocommerce_tax_country', $_POST['billing_country'], (time() + 30 * 86400), '/');
			$_COOKIE['woocommerce_tax_country'] = $_POST['billing_country'];
			return;
		} elseif (isset($_REQUEST['wc_country_preselect'])) {
			// Value from VAT complience form
			setcookie('woocommerce_tax_country', $_REQUEST['wc_country_preselect'], (time() + 30 * 86400), '/');
			$_COOKIE['woocommerce_tax_country'] = $_REQUEST['wc_country_preselect'];
			return;
		}

		$country = isset(WC()->customer) ? WC()->customer->get_billing_country() : '';

		if (!$country) return;

		// By default, set the country to WC()->customer->get_billing_country(), and override it if it was set to something else.
		if (!isset($_COOKIE['woocommerce_tax_country'])) {
			// $country if cookie was empty
			setcookie('woocommerce_tax_country', $country, (time() + 30 * 86400), '/');
			$_COOKIE['woocommerce_tax_country'] = $country;
		} elseif ($_COOKIE['woocommerce_tax_country'] != $country) {
			// Use $country if cookie was different than $country.
			setcookie('woocommerce_tax_country', $country, (time() + 30 * 86400), '/');
			$_COOKIE['woocommerce_tax_country'] = $country;
		}
	}

	/**
	 * Show the cache status in a "post state" in the Posts list table.
	 *
	 * @param array  $post_states
	 * @param object $post
	 * @return array
	 */
	public function add_post_state($post_states, $post) {
		$do_not_cache = get_post_meta($post->ID, '_wpo_disable_caching', true);
		if ($do_not_cache) $post_states['wpo-do-not-cache-state'] = __('Excluded from caching', 'wp-optimize');
		return $post_states;
	}

	/**
	 * Add metaboxes for disable cache functionality on single post screen.
	 */
	public function add_cache_control_metabox() {
		add_meta_box('wpo-cache-metabox', '<span title="'.__('by WP-Optimize', 'wp-optimize').'">'.__('Page caching', 'wp-optimize').'</span>', array($this, 'render_cache_control_metabox'), get_post_types(array('public' => true)), 'side');
	}

	/**
	 * Output metabox for disable cache functionality.
	 *
	 * @param WP_Post $post - post object that the current editing page is for
	 */
	public function render_cache_control_metabox($post) {
		$post_id = $post->ID;
		$meta_key = '_wpo_disable_caching';
		$disable_caching = get_post_meta($post_id, $meta_key, true);

		$post_type_obj = get_post_type_object(get_post_type($post_id));

		$extract = array(
			'disable_caching' => $disable_caching,
			'post_id' => $post_id,
			'post_type' => strtolower($post_type_obj->labels->singular_name),
			'post_types' => $this->get_post_types(),
			'always_purge_post_type' => $this->get_always_purge_post_config($post_id),
		);

		WP_Optimize()->include_template('cache/admin-metabox-cache-control.php', false, $extract);
	}

	/**
	 * Get list of excluded posts from cache.
	 *
	 * @return array
	 */
	public function get_excluded_posts() {
		global $wpdb;

		$excluded_posts = $wpdb->get_results("SELECT p.ID, p.post_title, p.post_type FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE pm.meta_key = '_wpo_disable_caching' AND p.post_status = 'publish' ORDER BY p.post_title;", ARRAY_A);

		return $excluded_posts;
	}

	/**
	 * Ouptut user specific cache option.
	 */
	public function output_user_specific_cache_option() {
		global $is_nginx;

		$extract = array(
			'wpo_cache_options' => $this->config,
			'is_nginx' => $is_nginx,
			'path_to_cache' => defined('WPO_CACHE_DIR') ? str_replace(ABSPATH, '/', WPO_CACHE_DIR) : '/path/to/cache',
		);

		WP_Optimize()->include_template('cache/page-cache-user-specific-cache.php', false, $extract);
	}

	/**
	 * Output cache per role option.
	 */
	public function output_user_per_role_cache_option() {
		$user_roles = array();
		
		$wp_roles = get_editable_roles();
		foreach ($wp_roles as $role => $info) {
			$user_roles[] = array(
				'role' => $role,
				'label' => translate_user_role($info['name']),
			);
		}

		$extract = array(
			'user_roles' => $user_roles,
			'wpo_cache_options' => $this->config,
		);

		WP_Optimize()->include_template('cache/page-cache-per-role-cache.php', false, $extract);
	}
}
endif;
