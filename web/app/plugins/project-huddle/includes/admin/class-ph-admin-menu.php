<?php

/**
 * Creates the admin menu
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       3.2.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

include 'setup-wizard/class-ph-setup-wizard.php';

class PH_Admin_Menu
{
	protected $setup;

	public function __construct()
	{
		add_action('admin_menu', array($this, 'menu'), 10);
		add_action('admin_menu', array($this, 'dashboard'), 9);
		add_action('admin_enqueue_scripts', array($this, 'dashboard_scripts'));

		add_action('admin_menu', array($this, 'websites'), 20);
		add_action('admin_menu', array($this, 'websites_add_new'), 30);

		add_action('admin_menu', array($this, 'mockups'), 40);
		add_action('admin_menu', array($this, 'mockups_add_new'), 50);

		add_action('admin_head', array($this, 'css_variables'));
		add_action('admin_head', array($this, 'ph_hide_website_submenu'));

		$this->setup = new PH_Setup_Wizard();
	}

	public function menu()
	{
		add_menu_page(
			__('ProjectHuddle', 'project-huddle'),
			__('ProjectHuddle', 'project-huddle'),
			'edit_ph-projects',
			'project-huddle',
			function () {
				echo '<div class="ph-dashboard"></div>';
			},
			'dashicons-testimonial',
			26
		);
	}

	public function dashboard()
	{
		add_submenu_page(
			'project-huddle',
			__('Overview', 'project-huddle'),
			'<span id="ph-my-projects">' . __('Overview', 'project-huddle') . '</span>',
			'edit_ph-projects',
			'project-huddle'
		);
	}

	public function css_variables()
	{
		$load   = false;
		$screen = get_current_screen();

		// AJAX? Not used here.
		if (defined('DOING_AJAX')) {
			return;
		}

		if ('toplevel_page_project-huddle' === $screen->id) {
			$load = true;
		}

		if (in_array($screen->post_type, ph_get_post_types())) {
			$load = true;
		}

		if ($load) {
			echo '
		<style>:root{
			--ph-accent-color: ' . esc_html(get_option('ph_highlight_color', '#4353ff')) . ';
			--ph-accent-color-10: ' . esc_html(get_option('ph_highlight_color', '#4353ff')) . '1A;
			--ph-accent-color-20: ' . esc_html(get_option('ph_highlight_color', '#4353ff')) . '33;
			--ph-accent-color-30: ' . esc_html(get_option('ph_highlight_color', '#4353ff')) . '4D;
			--ph-accent-color-40: ' . esc_html(get_option('ph_highlight_color', '#4353ff')) . '66;
			--ph-accent-color-50: ' . esc_html(get_option('ph_highlight_color', '#4353ff')) . '80;
		}
		@font-face {
			font-family: "element-icons";
			src: url("' . PH_PLUGIN_URL . 'assets/fonts/element-icons.woff") format("woff"), /* chrome, firefox */
				 url("' . PH_PLUGIN_URL . 'assets/fonts/element-icons.ttf") format("truetype"); /* chrome, firefox, opera, Safari, Android, iOS 4.2+*/
			font-weight: normal;
			font-style: normal
		  } 
 
		</style>';
		}
	}
	
	public function ph_hide_website_submenu() 
	{
		echo '<style>
			li#toplevel_page_project-huddle li:nth-child(4), 
			li#toplevel_page_project-huddle li:nth-child(6) { 
			display: none !important;
		 } 
		</style>';
	}

	public function dashboard_scripts($hook)
	{
		if ($hook != 'toplevel_page_project-huddle') {
			return;
		}

		$js_dist = PH_PLUGIN_URL . 'assets/js/dist';
		if (defined('PH_HMR') && PH_HMR) {
			$js_dist = 'https://127.0.0.1:8081/assets/js/dist';
		}

		wp_enqueue_script('project-huddle-dashboard', $js_dist . '/project-huddle-dashboard.js', array('jquery', 'underscore', 'ph.components'), PH_VERSION);
		ph_localize_schema('project-huddle-dashboard');

		wp_add_inline_style(
			'project-huddle-dashboard',
			':root{
				--accent-color: ' . esc_html(get_option('ph_highlight_color', '#4353ff')) . '
			}'
		);

		global $wp_rest_server;
		$me = new WP_REST_Request('GET', '/projecthuddle/v2/users/me');
		$response = rest_do_request($me);

		wp_localize_script(
			'project-huddle-dashboard',
			'phSettings',
			array(
				'data' => array(
					'access_unsubscribed' => (bool) current_user_can('edit_others_ph-projects'),
					'admin_url'           => esc_url_raw(get_admin_url()),
					'id'				  => get_the_ID(),
					'me' 				  => $wp_rest_server->response_to_data($response, true)
				)
			)
		);

		wp_localize_script(
			'project-huddle-dashboard',
			'projectHuddleJSL10n',
			array(
				'project-huddle' => ph_get_json_translations('project-huddle-dashboard'),
			)
		);
	}

	public function dashboard_traslations()
	{
		return array(
			'overview' => __('Overview', 'project-huddle'),
		);
	}

	public function mockups()
	{
		add_submenu_page(
			'project-huddle',
			__('Mockups', 'project-huddle'),
			'<span id="ph-new-mockup">' . __('Mockups', 'project-huddle') . '</span>',
			'edit_ph-projects',
			'edit.php?post_type=ph-project'
		);
	}

	public function mockups_add_new()
	{
		add_submenu_page('project-huddle', __('New Mockup', 'project-huddle'), '<span id="ph-new-mokckup">' . __('New Mockup', 'project-huddle') . '</span>', 'publish_ph-projects', 'post-new.php?post_type=ph-project');
	}

	public function websites()
	{
		add_submenu_page('project-huddle', __('Websites', 'project-huddle'), '<span id="ph-new-website">' . __('Websites', 'project-huddle') . '</span>', 'edit_ph-websites', 'edit.php?post_type=ph-website');
	}

	public function websites_add_new()
	{
		add_submenu_page('project-huddle', __('New Website', 'project-huddle'), '<span id="ph-new-website">' . __('New Website', 'project-huddle') . '</span>', 'publish_ph-websites', 'post-new.php?post_type=ph-website');
	}
}

new PH_Admin_Menu();