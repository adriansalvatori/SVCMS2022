<?php

/**
 * Output System Status
 */
class PH_System_Status
{
	public function __construct()
	{
		define('PH_STATUS_DIR', plugin_dir_path(__FILE__));

		add_action('admin_menu', array($this, 'register_submenu_page'), 100);
		add_action('wp_ajax_download_ph_system_status', array($this, 'download_info'));

		// test endpoint
		add_action('rest_api_init', array($this, 'test_api_route'));
	}

	/**
	 * Test route for system status
	 */
	public function test_api_route()
	{
		register_rest_route(
			'projecthuddle/v1',
			'/test',
			array(
				'methods'  => array('GET', 'POST', 'PATCH', 'PUT', 'DELETE'),
				'permission_callback' => '__return_true',
				'callback' => function ($data) {
					return true;
				},
			)
		);
	}

	public function register_submenu_page()
	{
		$page = add_submenu_page(
			'project-huddle',
			__('System Status', 'project-huddle'),
			__('System Status', 'project-huddle'),
			'manage_ph_settings',
			'ph-system-status',
			array($this, 'render_status')
		);
		add_action('admin_print_styles-' . $page, [$this, 'assets']);
	}

	public function assets()
	{
		$js_dir = PH_PLUGIN_URL . 'assets/js/dist/';
		$css_dir = PH_PLUGIN_URL . 'assets/css/dist/';

		// Image Upload
		wp_enqueue_media();

		wp_enqueue_script('project-huddle-setup', $js_dir . 'project-huddle-setup.js', ['underscore', 'ph.components', 'wp-color-picker'], PH_VERSION, true);
		wp_enqueue_style('project-huddle-setup', $css_dir . 'project-huddle-setup.css', ['wp-color-picker'], PH_VERSION);

		wp_localize_script('project-huddle-setup', 'phData', [
			'nonce' => wp_create_nonce('ph_setup_nonce'),
			'admin_url' => admin_url(),
			'rest_root'    => esc_url_raw(get_rest_url()),
		]);
	}

	public function render_status()
	{
		global $ph_active_tab;
		$ph_active_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'diagnose'; ?>
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $ph_active_tab == 'diagnose' || '' ? 'nav-tab-active' : ''; ?>" href="<?php echo admin_url('admin.php?page=ph-system-status&tab=diagnose'); ?>"><?php _e('Diagnose', 'project-huddle'); ?> </a>
			<a class="nav-tab <?php echo $ph_active_tab == 'status' || '' ? 'nav-tab-active' : ''; ?>" href="<?php echo admin_url('admin.php?page=ph-system-status&tab=status'); ?>"><?php _e('Status File', 'project-huddle'); ?> </a>
		</h2>
<?php
		if ($ph_active_tab == '' || $ph_active_tab == 'diagnose') {
			echo '<div id="diagnose"><div class="ph-relative ph-w-full ph-h-screen ph-flex ph-items-center ph-justify-center" style="z-index:9999999">
			<div class="spinner" style="visibility:visible"></div>
			</div></div>';
		}
		if ($ph_active_tab == 'status') {
			include PH_STATUS_DIR . 'system-status-output.php';
		}
	}

	public function display()
	{

		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;

		// Try to identify the hosting provider
		$host = false;
		if (defined('WPE_APIKEY')) {
			$host = 'WP Engine';
		} elseif (defined('PAGELYBIN')) {
			$host = 'Pagely';
		}

		$response = wp_remote_post(
			rest_url('projecthuddle/v1/test'),
			array(
				'method'    => 'PATCH',
				'sslverify' => false,
			)
		);

		if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300) {
			$patch = 'PATCH is working';
		} else {
			$patch = 'PATCH is not working. Please enable PATCH requests on your server.';
			if (is_wp_error($response)) {
				$patch .= $response->get_error_message();
			}
		}

		$response = wp_remote_post(
			rest_url('projecthuddle/v1/test'),
			array(
				'method'    => 'PUT',
				'sslverify' => false,
			)
		);

		if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300) {
			$put = 'PUT is working';
		} else {
			$put = 'PUT is not working. Please enable PUT requests on your server.';
			if (is_wp_error($response)) {
				$put .= $response->get_error_message();
			}
		}

		$response = wp_remote_post(
			rest_url('projecthuddle/v1/test'),
			array(
				'method'    => 'DELETE',
				'sslverify' => false,
			)
		);

		if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300) {
			$delete = 'DELETE is working';
		} else {
			$delete = 'DELETE is not working. Please enable DELETE requests on your server.';
			if (is_wp_error($response)) {
				$delete .= $response->get_error_message();
			}
		}

		stream_context_set_default(
			[
				'ssl' => [
					'verify_peer'      => false,
					'verify_peer_name' => false,
				],
			]
		);

		if ($id = (int) get_option('ph_site_post')) {
			$test_url = add_query_arg('ph_handler', $id, get_home_url());
		} else {
			$test_url = get_home_url();
		}

		$url_headers = get_headers(esc_url($test_url), 1);

		$headers = 'Security Headers Okay';
		foreach ($url_headers as $key => $value) {
			if ('X-Frame-Options' === $key) {
				$headers  = 'Not OK';
				$headers .= ' - currently set to ' . $value . '.';
				$headers .= ' You need to remove security headers to use ProjectHuddle on external sites.';
			}
		}

		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
		$filesystem 		  = new WP_Filesystem_Direct(new StdClass());
		$cache_file_is_file   = $filesystem->is_file(WP_CONTENT_DIR . '/advanced-cache.php');

		ob_start();
		include PH_STATUS_DIR . 'output.php';

		return ob_get_clean();
	}

	public function download_info()
	{
		if (!isset($_POST['ph-system-status-text']) || empty($_POST['ph-system-status-text'])) {
			return;
		}

		header('Content-type: text/plain');

		//Text file name marked with Unix timestamp
		header('Content-Disposition: attachment; filename=projecthuddle_system_status_' . time() . '.txt');

		echo $_POST['ph-system-status-text'];
		die();
	}

	public function hooks_reference($tag)
	{
		global $wp_filter;

		$output = '';
		if (isset($wp_filter[$tag]->callbacks) && is_array($wp_filter[$tag]->callbacks)) {
			foreach ($wp_filter[$tag]->callbacks as $priority => $action) {
				$output .= "Priority: $priority\n";
				if (isset($action) && is_array($action)) {
					foreach ($action as $key => $action) {
						foreach ($action as $type => $function_name) {
							$output .= $type . ': ' . $function_name . "\n";
						}
					}
				}
				$output .= "\n";
			}
		}

		return $output;
	}

	/**
	 * Size Conversions
	 *
	 * @author Chris Christoff
	 * @since  1.0
	 *
	 * @param  unknown $v
	 *
	 * @return int|string
	 */
	public function let_to_num($v)
	{
		$l   = substr($v, -1);
		$ret = substr($v, 0, -1);

		switch (strtoupper($l)) {
			case 'P': // fall-through
			case 'T': // fall-through
			case 'G': // fall-through
			case 'M': // fall-through
			case 'K': // fall-through
				$ret *= 1024;
				break;
			default:
				break;
		}

		return $ret;
	}
}
